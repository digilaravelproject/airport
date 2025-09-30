<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function index()
    {
        // No DB needed, just return static view
        return view('help.index');
    }
}
