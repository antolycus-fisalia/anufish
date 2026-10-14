<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class AdminController extends Controller
{
    public function index(): Response
    {
        return response('Dashboard Admin');
    }
}
