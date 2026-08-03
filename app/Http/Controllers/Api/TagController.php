<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\TelegramUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 15);
        $currentPage = $request->input('currentPage', 1);
        $telegramUser = $this->currentUser($request);

        $tags = Tag::query()
            ->when(!$telegramUser->isAdmin(), function ($query) use ($telegramUser) {
                $query->where(function ($query) use ($telegramUser) {
                    $query->whereNull('user_id')->orWhere('user_id', $telegramUser->id);
                });
            })
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $currentPage);

        return response()->json([
            'data' => $tags->items(),
            'status' => 'success',
            'meta' => [
                'currentPage' => $tags->currentPage(),
                'limit' => $tags->perPage(),
                'total' => $tags->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $telegramUser = $this->currentUser($request);
        $ownerId = $telegramUser->isAdmin() && $request->boolean('is_default') ? null : $telegramUser->id;

        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'name')->where(function ($query) use ($ownerId) {
                    $query->where('user_id', $ownerId);
                }),
            ],
        ]);

        $tag = Tag::create([
            'name' => $validatedData['name'],
            'user_id' => $ownerId,
        ]);

        return response()->json([
            'data' => $tag,
            'status' => 'success'
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json([
                'message' => 'Tag not found',
                'status' => 'error'
            ], 404);
        }

        if (!$this->canView($request, $tag)) {
            return $this->forbidden();
        }

        return response()->json([
            'data' => $tag,
            'status' => 'success'
        ]);
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

        if (!$this->canModify($request, $tag)) {
            return $this->forbidden();
        }

        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'name')->where(function ($query) use ($tag) {
                    $query->where('user_id', $tag->user_id);
                })->ignore($tag->id),
            ],
        ]);

        $tag->update($validatedData);

        return response()->json([
            'data' => $tag,
            'status' => 'success'
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json([
                'message' => 'Tag not found',
                'status' => 'error'
            ], 404);
        }

        if (!$this->canModify($request, $tag)) {
            return $this->forbidden();
        }

        $tag->delete();

        return response()->json([
            'message' => 'Tag deleted successfully',
            'status' => 'success'
        ]);
    }

    private function currentUser(Request $request): TelegramUser
    {
        return $request->attributes->get('telegramUser');
    }

    private function canView(Request $request, Tag $tag): bool
    {
        $telegramUser = $this->currentUser($request);

        return $telegramUser->isAdmin() || $tag->isDefault() || $tag->user_id === $telegramUser->id;
    }

    private function canModify(Request $request, Tag $tag): bool
    {
        $telegramUser = $this->currentUser($request);

        return $telegramUser->isAdmin() || $tag->user_id === $telegramUser->id;
    }

    private function forbidden(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Forbidden',
        ], 403);
    }
}
