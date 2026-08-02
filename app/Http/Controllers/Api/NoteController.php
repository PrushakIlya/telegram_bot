<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoteStoreRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use App\Models\TelegramUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $currentPage = $request->input('currentPage', 1);
        $telegramUser = $this->currentUser($request);

        $notes = Note::query()
            ->with('tag', 'telegramUser')
            ->when(!$telegramUser->isAdmin(), function ($query) use ($telegramUser) {
                $query->where('user_id', $telegramUser->id);
            })
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
            ->orderByDesc('created_at')
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
        $telegramUser = $this->currentUser($request);
        $data = $request->validated();

        if (!$telegramUser->isAdmin()) {
            $data['user_id'] = $telegramUser->id;
        }

        $note = Note::firstOrCreate($data);

        return response()->json([
            'data' => $note,
            'status' => 'success'
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $note = Note::with('tag', 'telegramUser')->find($id);

        if (!$note) {
            return response()->json([
                'data' => [],
                'status' => 'error',
                'message' => 'Note id is not found',
            ], 404);
        }

        if (!$this->canAccess($request, $note)) {
            return $this->forbidden();
        }

        return response()->json([
            'data' => $note,
            'status' => 'success'
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

        if (!$this->canAccess($request, $note)) {
            return $this->forbidden();
        }

        $data = $request->validated();

        if (!$this->currentUser($request)->isAdmin()) {
            $data['user_id'] = $note->user_id;
        }

        $note->update($data);

        return response()->json([
            'data' => $note,
            'status' => 'success',
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json([
                'data' => $note,
                'status' => 'error',
                'message' => 'Note id is not found',
            ], 404);
        }

        if (!$this->canAccess($request, $note)) {
            return $this->forbidden();
        }

        $note->delete();

        return response()->json([
            'data' => $note,
            'status' => 'success',
            'message' => 'Note deleted'
        ]);
    }

    private function currentUser(Request $request): TelegramUser
    {
        return $request->attributes->get('telegramUser');
    }

    private function canAccess(Request $request, Note $note): bool
    {
        $telegramUser = $this->currentUser($request);

        return $telegramUser->isAdmin() || $note->user_id === $telegramUser->id;
    }

    private function forbidden(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Forbidden',
        ], 403);
    }
}
