<?php

declare(strict_types=1);

namespace App\Http\Requests\Announcement;

use App\Enums\AnnouncementTargetType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:5000'],
            'target_type' => ['required', 'string', Rule::enum(AnnouncementTargetType::class)],

            'target_certification_id' => [
                'nullable',
                'required_if:target_type,'.AnnouncementTargetType::Certification->value,
                'exists:certifications,id',
            ],
            'target_user_id' => [
                'nullable',
                'required_if:target_type,'.AnnouncementTargetType::User->value,
                'exists:users,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'target_certification_id.required_if' => '対象資格は、配信対象が「対象資格指定」の場合に必須です。',
            'target_user_id.required_if' => '対象受講生は、配信対象が「対象受講生指定」の場合に必須です。',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'タイトル',
            'body' => '本文',
            'target_type' => '配信対象',
            'target_certification_id' => '対象資格',
            'target_user_id' => '対象受講生',
        ];
    }
}
