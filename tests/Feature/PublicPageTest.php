<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPageTest extends TestCase
{
    #[DataProvider('publicPages')]
    public function test_guest_can_view_public_page(string $path, string $view): void
    {
        $this->withoutVite();

        $response = $this->get($path);

        $response->assertOk();
        $response->assertViewIs($view);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function publicPages(): array
    {
        return [
            'about' => ['/about', 'about'],
            'localized about' => ['/tentang', 'about'],
            'feature' => ['/feature', 'feature'],
            'localized feature' => ['/fitur', 'feature'],
        ];
    }
}
