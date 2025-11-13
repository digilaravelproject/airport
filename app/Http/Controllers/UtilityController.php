<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UtilityController extends Controller
{
    public function index(Request $request)
    {
      return view('utility.index');
    }
}
