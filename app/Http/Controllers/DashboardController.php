<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (auth()->user()->role_id === 1) { // If user is admin
            return redirect()->route('admin.dashboard');
        }
        
        return view('dashboard');
    }
} 