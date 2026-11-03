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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('operation_id');
            $table->char('image_hash', 64);
            $table->string('image_path');
            $table->json('candidates');
            $table->unsignedTinyInteger('selected_candidate_index')->nullable();
            $table->string('scientific_name', 200)->nullable();
            $table->double('confidence')->nullable();
            $table->unsignedBigInteger('inaturalist_taxon_id')->nullable()->index();
            $table->string('match_status')->default('unavailable');
            $table->timestamps();
            $table->unique(['user_id', 'operation_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_scan');
    }
};
