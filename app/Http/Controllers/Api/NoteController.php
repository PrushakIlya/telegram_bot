<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoteStoreRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $currentPage = $request->input('currentPage', 1);

        $notes = Note::query()
            ->with('tag', 'telegramUser')
            ->when($request->filled('username'), function ($query) use ($request) {
                $query->whereHas('telegramUser', function ($query) use ($request) {
                    $query->where('username', $request->input('username'));
                });
            })
            ->when($request->filled('tag'), function ($query) use ($request) {
                $query->whereHas('tag', function ($query) use ($request) {
                    $query->where('name', $request->input('tag'));
                });
            })
            ->paginate($limit, ['*'], 'page', $currentPage);

        return response()->json([
            'data' => NoteResource::collection($notes),
            'status' => 'success',
            'meta' => [
                'currentPage' => $notes->currentPage(),
                'limit' => $notes->perPage(),
                'total' => $notes->total(),
            ],
        ]);
    }

    public function store(NoteStoreRequest $request): JsonResponse
    {
        $note = Note::firstOrCreate($request->validated());

        return response()->json([
            'data' => $note,
            'status' => 'success'
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $note = Note::with('tag', 'telegramUser')->find($id);

        if ($note) {
            return response()->json([
                'data' => $note,
                'status' => 'success'
            ]);
        }

        return response()->json([
            'data' => [],
            'status' => 'error',
            'message' => 'Note id is not found',
        ], 404);
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
        ], 404);
    }
}
