<?php

declare(strict_types=1);

namespace App\Http\Requests\AiChatConversation;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_id' => ['nullable', 'ulid', 'exists:sections,id'],
            'title' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:2000'],
            'source' => ['required', 'string', 'in:widget,full-screen'],
        ];
    }
}
