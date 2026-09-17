<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatScan extends Model
{
    protected $table = 'riwayat_scan';

    protected $fillable = [
        'user_id',
        'gbif_species_key',
        'gambar_scan',
        'hasil_identifikasi',
        'confidence_score',
        'created_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
