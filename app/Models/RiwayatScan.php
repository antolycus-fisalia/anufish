<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatScan extends Model
{
    protected $table = 'riwayat_scan';

    protected $fillable = [
        'user_id', 'operation_id', 'image_hash', 'image_path', 'candidates',
        'selected_candidate_index', 'scientific_name', 'confidence',
        'inaturalist_taxon_id', 'match_status',
    ];

    protected $hidden = ['image_path', 'image_hash'];

    protected function casts(): array
    {
        return ['candidates' => 'array', 'confidence' => 'float', 'selected_candidate_index' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
