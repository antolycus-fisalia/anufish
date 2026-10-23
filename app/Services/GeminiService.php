<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GeminiService
{
    /**
     * Identify the fish species from a stored image file.
     *
     * Returns a structured result:
     * [
     *   'success'    => bool,
     *   'candidates' => [
     *     ['scientific_name' => string, 'confidence' => float|null], ...
     *   ],
     *   'raw'        => array|null,   // full Gemini payload, useful for debugging
     *   'error'      => string|null,  // human-readable reason on failure
     * ]
     *
     * Callers (ScanController / FSH-15 matcher) should check 'success' first.
     * 'candidates' is always a list; empty on failure.
     *
     * @param  string  $storagePath  Path on the 'local' disk (e.g. "scans/uuid.jpg")
     */
    public function identify(string $storagePath): array
    {
        // ── 1. Read the stored file ─────────────────────────────────────────
        if (! Storage::disk('local')->exists($storagePath)) {
            return $this->failure('File gambar tidak ditemukan di storage.');
        }

        $imageBytes = Storage::disk('local')->get($storagePath);
        if ($imageBytes === null || $imageBytes === '') {
            return $this->failure('File gambar kosong atau tidak dapat dibaca.');
        }

        $mimeType  = Storage::disk('local')->mimeType($storagePath) ?: 'image/jpeg';
        $b64Image  = base64_encode($imageBytes);

        // ── 2. Build the Gemini request payload ─────────────────────────────
        $prompt = <<<'PROMPT'
You are a fish species identification assistant.
Analyze the image and identify the fish species shown.

Respond ONLY with a valid JSON object in this exact format:
{
  "candidates": [
    {"scientific_name": "Genus species", "confidence": 0.92},
    {"scientific_name": "Genus species2", "confidence": 0.05}
  ]
}

Rules:
- Use binomial scientific names only (e.g. "Channa striata", not "Snakehead fish").
- Include up to 3 candidates, ordered by confidence descending.
- Confidence is a float between 0 and 1. Omit the field entirely if you cannot estimate it.
- If no fish is visible or you cannot identify any species, return: {"candidates": []}
- Do not include any explanation or text outside the JSON object.
PROMPT;

        $requestBody = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data'      => $b64Image,
                            ],
                        ],
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'candidateCount'   => 1,
                'maxOutputTokens'  => 256,
                'temperature'      => 0.2,
            ],
        ];

        // ── 3. Call Gemini REST API ─────────────────────────────────────────
        $timeout  = $this->timeout();
        $url      = rtrim(config('services.gemini.base_url'), '/')
            . '/models/' . config('services.gemini.model')
            . ':generateContent?key=' . config('services.gemini.api_key');

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->connectTimeout(min(3, $timeout))
                ->timeout($timeout)
                ->post($url, $requestBody);
        } catch (ConnectionException) {
            return $this->failure('Koneksi ke Gemini API gagal (timeout atau jaringan).');
        }

        if (! $response->successful()) {
            return $this->failure(
                'Gemini API mengembalikan status ' . $response->status() . '.'
            );
        }

        $raw = $response->json();

        // ── 4. Parse the model output ───────────────────────────────────────
        $text = $raw['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (! is_string($text) || trim($text) === '') {
            return $this->failure('Respons Gemini tidak mengandung teks.', $raw);
        }

        $parsed = $this->parseJson($text);

        if ($parsed === null) {
            return $this->failure('Respons Gemini bukan JSON valid.', $raw);
        }

        $candidates = $this->normalizeCandidates($parsed['candidates'] ?? []);

        return [
            'success'    => true,
            'candidates' => $candidates,
            'raw'        => $raw,
            'error'      => null,
        ];
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Extract and decode the first JSON object found in $text.
     * Gemini sometimes wraps JSON in markdown code-fences.
     */
    private function parseJson(string $text): ?array
    {
        // Strip optional markdown code-fence (```json ... ```)
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $text, $m)) {
            $text = $m[1];
        }

        // Find first { ... } block
        $start = strpos($text, '{');
        $end   = strrpos($text, '}');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $json = substr($text, $start, $end - $start + 1);
        $data = json_decode($json, true);

        return is_array($data) ? $data : null;
    }

    /**
     * Normalise the raw candidates array into a consistent structure.
     * Filters out entries without a valid scientific name.
     */
    private function normalizeCandidates(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $results = [];

        foreach ($raw as $item) {
            if (! is_array($item)) {
                continue;
            }

            $name = trim((string) ($item['scientific_name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $confidence = isset($item['confidence']) && is_numeric($item['confidence'])
                ? (float) $item['confidence']
                : null;

            // Clamp confidence to [0, 1]
            if ($confidence !== null) {
                $confidence = max(0.0, min(1.0, $confidence));
            }

            $results[] = [
                'scientific_name' => $name,
                'confidence'      => $confidence,
            ];
        }

        return array_slice($results, 0, (int) config('services.gemini.max_candidates', 3));
    }

    /** Configured API timeout (seconds), clamped to [1, 10]. */
    private function timeout(): float
    {
        return max(1.0, min(10.0, (float) config('services.gemini.timeout', 10)));
    }

    /** Uniform failure response. */
    private function failure(string $message, ?array $raw = null): array
    {
        return [
            'success'    => false,
            'candidates' => [],
            'raw'        => $raw,
            'error'      => $message,
        ];
    }
}
