<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if ($argc !== 3 || ! extension_loaded('curl')) {
    fwrite(STDERR, "Usage (PHP CLI with cURL): php scripts/check-availability.php <base-url> <log.jsonl>\n");
    exit(2);
}

[, $baseUrl, $logPath] = $argv;
$urlParts = filter_var($baseUrl, FILTER_VALIDATE_URL) ? parse_url($baseUrl) : false;

if (! $urlParts || ! in_array($urlParts['scheme'], ['http', 'https'], true)
    || array_intersect(['user', 'pass', 'query', 'fragment'], array_keys($urlParts))) {
    fwrite(STDERR, "Supply an HTTP(S) base URL without credentials, a query, or a fragment.\n");
    exit(2);
}

function probe(string $url, string $expectedStatus): array
{
    $request = curl_init($url);
    curl_setopt_array($request, [
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Cache-Control: no-cache'],
    ]);

    $body = curl_exec($request);
    $code = curl_getinfo($request, CURLINFO_RESPONSE_CODE);

    return [
        'http_status' => $code ?: null,
        'ok' => $code === 200 && is_string($body) && json_decode($body, true) === ['status' => $expectedStatus],
    ];
}

$baseUrl = rtrim($baseUrl, '/');
$timestamp = gmdate('Y-m-d\TH:i:s\Z');
$live = probe($baseUrl.'/up', 'up');
$ready = probe($baseUrl.'/ready', 'ready');
$available = $live['ok'] && $ready['ok'];
$sample = json_encode([
    'timestamp' => $timestamp,
    'liveness' => $live,
    'readiness' => $ready,
    'available' => $available,
], JSON_THROW_ON_ERROR).PHP_EOL;

if (@file_put_contents($logPath, $sample, FILE_APPEND | LOCK_EX) !== strlen($sample)) {
    fwrite(STDERR, "Could not append the availability sample.\n");
    exit(2);
}

echo $sample;
exit($available ? 0 : 1);
