<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoteStoreRequest;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 15);

        return response()->json([
            'data' => Note::paginate($limit),
            'status' => 'success'
        ]);
    }

    public function store(NoteStoreRequest $request): JsonResponse
    {
        $note = Note::firstOrCreate($request->validated());

        return response()->json([
            'data' => $note,
            'status' => 'success',
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $note = Note::find($id);

        if ($note) {
            return response()->json([
                'data' => $note,
                'status' => 'success'
            ]);
        }

        return response()->json([
            'data' => $note,
            'status' => 'error',
            'message' => 'Note id is not found',
        ]);
    }

    public function update(NoteStoreRequest $request, int $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json([
                'status' => 'error',
                'message' => 'Note not found',
            ], 404);
        }

        $note->update($request->validated());

        return response()->json([
            'data' => $note,
            'status' => 'success',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $note = Note::find($id);

        if ($note) {
            $note->delete();

            return response()->json([
                'data' => $note,
                'status' => 'success',
                'message' => 'Note deleted'
            ]);
        }

        return response()->json([
            'data' => $note,
            'status' => 'error',
            'message' => 'Note id is not found',
        ]);
    }
}
