<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin'])
                ->default('user')
                ->after('password');

            $table->enum('status', ['aktif', 'diblokir'])
                ->default('aktif')
                ->after('role');

            $table->string('profile_photo_path')
                ->nullable()
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'status',
                'profile_photo_path',
            ]);
        });
    }
};
