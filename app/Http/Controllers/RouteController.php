<?php

namespace App\Http\Controllers;

use App\Models\Route;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Route::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:routes',
            'start_location' => 'required|string',
            'end_location' => 'required|string',
            'distance' => 'nullable|numeric|min:0',
            'estimated_time' => 'nullable|integer|min:0',
        ]);

        $route = Route::create($request->all());
        return response()->json($route, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Route $route)
    {
        return $route;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Route $route)
    {
        $request->validate([
            'name' => 'sometimes|required|string|unique:routes,name,' . $route->id,
            'start_location' => 'sometimes|required|string',
            'end_location' => 'sometimes|required|string',
            'distance' => 'nullable|numeric|min:0',
            'estimated_time' => 'nullable|integer|min:0',
        ]);

        $route->update($request->all());
        return response()->json($route, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Route $route)
    {
        $route->delete();
        return response()->json(null, 204);
    }
}