<?php

namespace App\Http\Controllers;

use App\Models\UserActivityLog;
use Illuminate\Http\Request;

class UserActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return UserActivityLog::with(['user'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'activity_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ip_address' => 'nullable|ip',
            'user_agent' => 'nullable|string',
        ]);

        $userActivityLog = UserActivityLog::create($request->all());
        return response()->json($userActivityLog, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserActivityLog $userActivityLog)
    {
        return $userActivityLog->load(['user']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserActivityLog $userActivityLog)
    {
        $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'activity_type' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'ip_address' => 'nullable|ip',
            'user_agent' => 'nullable|string',
        ]);

        $userActivityLog->update($request->all());
        return response()->json($userActivityLog, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserActivityLog $userActivityLog)
    {
        $userActivityLog->delete();
        return response()->json(null, 204);
    }
}