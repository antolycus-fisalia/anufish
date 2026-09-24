<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class RegisterViewController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }
}