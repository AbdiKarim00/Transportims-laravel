<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::with('roles')->get()->map(function ($user) {
            return [
                'user_id' => $user->id,
                'role_id' => $user->role_id,
                'user' => $user,
                'role' => $user->roles
            ];
        });
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

        $user = User::findOrFail($request->user_id);
        $user->role_id = $request->role_id;
        $user->save();

        return response()->json([
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'user' => $user,
            'role' => $user->roles
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);
        return [
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'user' => $user,
            'role' => $user->roles
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($id);
        $user->role_id = $request->role_id;
        $user->save();

        return response()->json([
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'user' => $user,
            'role' => $user->roles
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->role_id = null;
        $user->save();
        return response()->json(null, 204);
    }
}
