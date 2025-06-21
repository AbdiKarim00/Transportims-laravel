<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AccessRequestController extends Controller
{
    public function showForm()
    {
        return view('auth.access-request');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company' => 'required|string|max:255',
            'reason' => 'required|string|max:1000',
        ]);

        // Send email to admin with the request details
        $adminEmail = config('mail.admin_email', 'admin@example.com');
        \Mail::to($adminEmail)->send(new \App\Mail\AccessRequestReceived($validated));

        return redirect()->route('login')->with('status', 'Your access request has been submitted. We will contact you shortly.');
    }
} 