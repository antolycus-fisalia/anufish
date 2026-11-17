<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class HealthController extends Controller
{
    public function live(): JsonResponse
    {
        return $this->status('up', 200);
    }

    public function ready(): JsonResponse
    {
        try {
            if (app()->isDownForMaintenance()) {
                return $this->status('unavailable', 503);
            }

            DB::connection()->selectFromWriteConnection('SELECT 1');

            return $this->status('ready', 200);
        } catch (Throwable $exception) {
            rescue(fn () => Log::warning('health.readiness_failed', [
                'exception_type' => ($exception->getPrevious() ?? $exception)::class,
            ]), report: false);

            return $this->status('unavailable', 503);
        }
    }

    private function status(string $status, int $code): JsonResponse
    {
        return response()->json(['status' => $status], $code, ['Cache-Control' => 'no-store']);
    }
}
