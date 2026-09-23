<?php

declare(strict_types=1);

namespace App\Http\Requests\QaThread;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 質問スレッド(QaThread) の新規作成リクエスト。
 */
class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'certification_id' => ['required', 'ulid', 'exists:certifications,id'],
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'certification_id' => '資格ID',
            'title' => 'タイトル',
            'body' => '本文',
        ];
    }
}
