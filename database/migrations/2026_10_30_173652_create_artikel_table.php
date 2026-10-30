<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikel', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('judul', 255);
            $table->string('kategori', 100);
            $table->longText('isi');
            $table->string('gambar')->nullable();

            $table->enum('status', [
                'menunggu',
                'disetujui',
                'ditolak',
            ])->default('menunggu');

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel');
    }
};
