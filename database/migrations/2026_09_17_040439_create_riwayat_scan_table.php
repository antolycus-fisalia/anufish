<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_scan', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');

            $table->string('gbif_species_key', 50)->nullable();
            $table->string('gambar_scan', 255);
            $table->string('hasil_identifikasi', 200);
            $table->decimal('confidence_score', 5, 2)->nullable();

            $table->timestamp('created_at')
                ->useCurrent();

            $table->index(
                'user_id',
                'riwayat_scan_user_id_index'
            );

            $table->index(
                'gbif_species_key',
                'riwayat_scan_gbif_species_key_index'
            );

            $table->foreign(
                'user_id',
                'riwayat_scan_user_id_foreign'
            )
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_scan');
    }
};
