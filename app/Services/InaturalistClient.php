<?php

namespace App\Services;

use GuzzleHttp\Exception\ConnectTimeoutException;
use GuzzleHttp\Exception\NetworkTimeoutException;
use GuzzleHttp\Exception\ResponseTimeoutException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class InaturalistClient
{
    public function request(string $path, array $query, ?float $deadline = null): array
    {
        $cacheKey = 'inaturalist:v2:'.hash('sha256', config('services.inaturalist.base_url').$path.http_build_query($query));
        if ($cached = Cache::get($cacheKey)) {
            return ['status' => 200, 'payload' => $cached];
        }
        $deadline ??= microtime(true) + $this->timeout();
        if ($deadline - microtime(true) < 0.05) {
            return ['status' => 502, 'payload' => null, 'reason' => 'timeout'];
        }
        if (! $this->reserveRequest()) {
            return ['status' => 429, 'payload' => null];
        }
        try {
            $remaining = max(0.01, $deadline - microtime(true));
            $response = Http::acceptJson()->withOptions(['verify' => config('services.inaturalist.ca_bundle') ?: true])
                ->connectTimeout(min(2, $remaining))->timeout($remaining)
                ->get(rtrim(config('services.inaturalist.base_url'), '/').$path, $query);
        } catch (ConnectionException $exception) {
            $previous = $exception->getPrevious();
            $timedOut = $previous instanceof ConnectTimeoutException
                || $previous instanceof NetworkTimeoutException
                || $previous instanceof ResponseTimeoutException;

            return ['status' => 502, 'payload' => null,
                'reason' => $timedOut ? 'timeout' : 'connection'];
        }
        if ($response->status() === 429) {
            $retry = $response->header('Retry-After');
            $seconds = is_numeric($retry) ? (int) $retry : (($date = strtotime($retry ?: '')) ? $date - time() : 60);
            Cache::put('inaturalist:cooldown', true, max(1, $seconds));

            return ['status' => 429, 'payload' => null];
        }
        if (! $response->successful()) {
            return ['status' => $response->status() === 404 ? 404 : 502, 'payload' => null];
        }
        $payload = $response->json();
        if (! is_array($payload) || ! is_array($payload['results'] ?? null) || ! array_is_list($payload['results'])) {
            return ['status' => 502, 'payload' => null];
        }
        Cache::put($cacheKey, $payload, now()->addMinutes(max(1, (int) config('services.inaturalist.cache_minutes', 15))));

        return ['status' => 200, 'payload' => $payload];
    }

    private function reserveRequest(): bool
    {
        // Atomic shared-cache budget applies across all users and workers.
        $lock = Cache::lock('inaturalist:budget-lock', 5);
        if (! $lock->get()) {
            return false;
        }
        try {
            if (Cache::has('inaturalist:cooldown')) {
                return false;
            }
            $minute = 'inaturalist:rolling-minute';
            $day = 'inaturalist:day:'.now()->format('Ymd');
            $recent = array_values(array_filter(Cache::get($minute, []), fn ($time) => $time > microtime(true) - 60));
            $dayCount = (int) Cache::get($day, 0);
            if (count($recent) >= 60 || $dayCount >= 9999) {
                return false;
            }
            $recent[] = microtime(true);
            Cache::put($minute, $recent, 120);
            Cache::put($day, $dayCount + 1, now()->endOfDay()->addMinute());

            return true;
        } finally {
            $lock->release();
        }
    }

    public function timeout(): float
    {
        return max(0.1, min(4, (float) config('services.inaturalist.timeout', 4)));
    }
}
