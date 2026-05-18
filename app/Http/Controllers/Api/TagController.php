<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Tag::query();

            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->input('search') . '%');
            }

            $tags = $query->get();

            \Log::info('Retrieved tags', ['count' => $tags->count()]);

            return response()->json($tags);
        } catch (\Exception $e) {
            \Log::error('Failed to retrieve tags', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to retrieve tags'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:tags,name',
            ]);

            \Log::info('Creating new tag', ['name' => $validated['name']]);

            $tag = Tag::create($validated);

            return response()->json($tag, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to create tag', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create tag'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Tag::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tag = Tag::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:tags,name,' . $id,
        ]);

        $tag->update($validated);

        return response()->json($tag);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();

        return response()->json(null, 204);
    }
}
