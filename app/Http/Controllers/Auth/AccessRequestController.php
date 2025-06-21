<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccessRequestReceived;

class AccessRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'department' => 'required|string|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        // Here you would typically:
        // 1. Store the request in the database
        // 2. Send notification emails
        // 3. Create a user account if approved
        
        // For now, we'll just send a confirmation email
        Mail::to($validated['email'])->send(new AccessRequestReceived($validated));

        return redirect()->route('login')
            ->with('success', 'Your access request has been submitted. We will review it and get back to you soon.');
    }
} 