<?php

namespace App\Services;

use App\Models\RiwayatScan;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ScanHistoryService
{
    public function save(int $userId, string $operationId, string $hash, string $path, array $candidates): RiwayatScan
    {
        $history = new RiwayatScan;
        $history->user_id = $userId;
        $history->operation_id = $operationId;
        $history->image_hash = $hash;
        $history->image_path = $path;
        $history->candidates = $candidates;
        if (! $history->save()) {
            throw new RuntimeException('Scan history was not persisted.');
        }

        return $history;
    }

    public function cleanup(string $path): void
    {
        try {
            if (! Storage::disk('local')->delete($path)) {
                report(new RuntimeException('Scan image cleanup failed.'));
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
