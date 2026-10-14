<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $data = Validator::make([
            'name' => config('admin.name'),
            'email' => config('admin.email'),
            'password' => config('admin.password'),
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'password.required' => 'Atur ADMIN_PASSWORD di .env sebelum menjalankan AdminSeeder.',
        ])->validate();

        $admin = User::firstOrCreate(['email' => $data['email']], [
            'name' => $data['name'],
            'password' => $data['password'],
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        if (! $admin->isAdmin()) {
            throw new RuntimeException('ADMIN_EMAIL sudah digunakan akun non-admin. Gunakan email lain.');
        }
    }
}
