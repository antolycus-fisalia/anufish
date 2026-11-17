<?php

namespace App\Events;

readonly class ArtikelModerated
{
    /**
     * @param  string  $action  approve|reject|delete
     */
    public function __construct(
        public string $action,
        public int $actorId,
        public int $artikelId,
        public int $ownerId,
        public string $status,
        public string $title,
    ) {}
}
