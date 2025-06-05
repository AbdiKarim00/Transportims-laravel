<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OperationalAdminController extends Controller
{
    public function index()
    {
        return view('operational_admin.dashboard');
    }
}
