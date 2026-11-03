<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class UiStateComponentTest extends TestCase
{
    public function test_loading_state_has_polite_live_region_and_busy_status(): void
    {
        $html = Blade::render(
            '<x-ui.state state="loading" title="Memuat data" message="Mohon tunggu." />'
        );

        $this->assertStringContainsString('data-ui-state="loading"', $html);
        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('aria-live="polite"', $html);
        $this->assertStringContainsString('aria-busy="true"', $html);
    }

    public function test_failure_state_uses_alert_semantics(): void
    {
        $html = Blade::render(
            '<x-ui.state state="failure" title="Gagal" message="Silakan coba lagi." />'
        );

        $this->assertStringContainsString('data-ui-state="failure"', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('aria-live="assertive"', $html);
    }

    public function test_validation_state_uses_alert_semantics(): void
    {
        $html = Blade::render(
            '<x-ui.state state="validation" title="Input tidak valid" />'
        );

        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('aria-live="assertive"', $html);
    }

    public function test_partial_state_keeps_status_semantics(): void
    {
        $html = Blade::render(
            '<x-ui.state state="partial" title="Data sebagian tersedia" />'
        );

        $this->assertStringContainsString('data-ui-state="partial"', $html);
        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('aria-live="polite"', $html);
    }

    public function test_empty_and_idle_states_do_not_force_live_region(): void
    {
        foreach (['idle', 'empty'] as $state) {
            $html = Blade::render(
                '<x-ui.state state="'.$state.'" title="State" />'
            );

            $this->assertStringNotContainsString('aria-live=', $html);
            $this->assertStringNotContainsString('role="alert"', $html);
        }
    }

    public function test_invalid_state_falls_back_to_idle(): void
    {
        $html = Blade::render(
            '<x-ui.state state="invalid-state" title="State" />'
        );

        $this->assertStringContainsString('data-ui-state="idle"', $html);
    }
}
