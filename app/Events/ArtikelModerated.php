<?php

namespace App\Events;

class ArtikelModerated
{
    /**
     * @param  string  $action  approve|reject|delete
     */
    public function __construct(
        public string $action,
        public int $actorId,
        public int $artikelId,
    ) {}
}
