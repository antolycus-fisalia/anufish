<?php

namespace App\Models;

use App\Services\ArticleHtmlSanitizer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Artikel extends Model
{
    use SoftDeletes;

    protected $table = 'artikel';

    protected $fillable = [
        'judul',
        'kategori',
        'isi',
        'gambar',
    ];

    protected $attributes = ['moderation_revision' => 1];

    protected $hidden = ['moderation_revision'];

    protected $casts = [
        'moderation_revision' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function isi(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => app(ArticleHtmlSanitizer::class)->sanitize((string) $value),
            set: fn ($value) => app(ArticleHtmlSanitizer::class)->sanitize((string) $value),
        );
    }
}
