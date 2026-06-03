<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 15);

        return response()->json([
            'data' => Tag::paginate($limit)->items(),
            'status' => 'success'
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tag = Tag::create($validatedData);

        return response()->json([
            'data' => $tag,
            'status' => 'success'
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $tag = Tag::find($id);

        if ($tag) {
            return response()->json([
                'data' => $tag,
                'status' => 'success'
            ]);
        }

        return response()->json([
            'message' => 'Tag not found',
            'status' => 'error'
        ], 404);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json([
                'message' => 'Tag not found',
                'status' => 'error'
            ], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tag->update($validatedData);

        return response()->json([
            'data' => $tag,
            'status' => 'success'
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $tag = Tag::find($id);

        if ($tag) {
            $tag->delete();

            return response()->json([
                'message' => 'Tag deleted successfully',
                'status' => 'success'
            ]);
        }

        return response()->json([
            'message' => 'Tag not found',
            'status' => 'error'
        ], 404);
    }
}
