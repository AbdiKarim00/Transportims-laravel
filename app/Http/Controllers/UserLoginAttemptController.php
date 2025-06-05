<?php

namespace App\Http\Controllers;

use App\Models\UserLoginAttempt;
use Illuminate\Http\Request;

class UserLoginAttemptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return UserLoginAttempt::with(['user'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'ip_address' => 'nullable|ip',
            'user_agent' => 'nullable|string',
            'success' => 'required|boolean',
        ]);

        $userLoginAttempt = UserLoginAttempt::create($request->all());
        return response()->json($userLoginAttempt, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserLoginAttempt $userLoginAttempt)
    {
        return $userLoginAttempt->load(['user']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserLoginAttempt $userLoginAttempt)
    {
        $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'ip_address' => 'nullable|ip',
            'user_agent' => 'nullable|string',
            'success' => 'sometimes|required|boolean',
        ]);

        $userLoginAttempt->update($request->all());
        return response()->json($userLoginAttempt, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserLoginAttempt $userLoginAttempt)
    {
        $userLoginAttempt->delete();
        return response()->json(null, 204);
    }
}