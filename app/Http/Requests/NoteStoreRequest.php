<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class NoteStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; //think
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $telegramUser = $this->attributes->get('telegramUser');
        $isAdmin = $telegramUser && $telegramUser->isAdmin();

        return [
            'message' => 'required|string',
            'user_id' => ($isAdmin ? 'required' : 'sometimes') . '|integer|exists:telegram_users,id',
            'tag_id' => [
                'required',
                'integer',
                $isAdmin
                    ? Rule::exists('tags', 'id')
                    : Rule::exists('tags', 'id')->where(function ($query) use ($telegramUser) {
                        $query->where(function ($query) use ($telegramUser) {
                            $query->whereNull('user_id')->orWhere('user_id', $telegramUser?->id);
                        });
                    }),
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
