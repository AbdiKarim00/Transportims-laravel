<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Media::with('uploadedBy')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string|max:255',
            'file_path' => 'required|string|max:255',
            'file_type' => 'required|string|max:255',
            'file_size' => 'required|integer|min:0',
            'uploaded_by' => 'required|exists:users,id',
            'associated_id' => 'nullable|integer',
            'associated_type' => 'nullable|string',
        ]);

        $media = Media::create($request->all());
        return response()->json($media, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Media $media)
    {
        return $media->load('uploadedBy');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Media $media)
    {
        $request->validate([
            'file_name' => 'sometimes|required|string|max:255',
            'file_path' => 'sometimes|required|string|max:255',
            'file_type' => 'sometimes|required|string|max:255',
            'file_size' => 'sometimes|required|integer|min:0',
            'uploaded_by' => 'sometimes|required|exists:users,id',
            'associated_id' => 'nullable|integer',
            'associated_type' => 'nullable|string',
        ]);

        $media->update($request->all());
        return response()->json($media, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Media $media)
    {
        $media->delete();
        return response()->json(null, 204);
    }
}