<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return UserPreference::with(['user'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'preference_key' => 'required|string|max:255',
            'preference_value' => 'nullable|string',
        ]);

        $userPreference = UserPreference::create($request->all());
        return response()->json($userPreference, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserPreference $userPreference)
    {
        return $userPreference->load(['user']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserPreference $userPreference)
    {
        $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'preference_key' => 'sometimes|required|string|max:255',
            'preference_value' => 'nullable|string',
        ]);

        $userPreference->update($request->all());
        return response()->json($userPreference, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserPreference $userPreference)
    {
        $userPreference->delete();
        return response()->json(null, 204);
    }
}