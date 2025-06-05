<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransportOfficerController extends Controller
{
    public function index()
    {
        return view('transport_officer.dashboard');
    }
}
