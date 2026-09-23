<?php

declare(strict_types=1);

namespace App\Http\Requests\QaThread;

use App\Enums\QaThreadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 質問スレッド(QaThread) の一覧取得リクエスト。
 */
class IndexRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::enum(QaThreadStatus::class)],
            'certification_id' => ['nullable', 'string', 'exists:certifications,id'],
            'keyword' => ['nullable', 'string', 'max:200'],
        ];
    }
}
