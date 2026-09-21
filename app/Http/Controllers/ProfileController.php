<?php

namespace App\Http\Controllers;

class ProfileController extends Controller
{
    public function index()
    {
        $user = (object) [
            'name' => 'Nayda Nur Kamal',
            'email' => 'user@example.com',
            'status' => 'Akun Aktif',
            'scan_count' => 34,
            'article_count' => 12,
            'approved_count' => 9,
            'photo' => null,
        ];

        return view('profile.index', compact('user'));
    }
}