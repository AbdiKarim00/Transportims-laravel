<?php

namespace App\Http\Controllers;

use App\Models\UserRole;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return UserRole::with(['user', 'role'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $userRole = UserRole::create($request->all());
        return response()->json($userRole, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserRole $userRole)
    {
        return $userRole->load(['user', 'role']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserRole $userRole)
    {
        $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'role_id' => 'sometimes|required|exists:roles,id',
        ]);

        $userRole->update($request->all());
        return response()->json($userRole, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserRole $userRole)
    {
        $userRole->delete();
        return response()->json(null, 204);
    }
}