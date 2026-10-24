<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AiChatMessageRole;
use App\Enums\AiChatMessageStatus;
use App\Enums\UserRole;
use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;

class AiChatConversationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student = User::query()->where('role', UserRole::Student->value)->first();
        $section = Section::query()->first();

        // 教材なしの会話
        $NoSectionConversation = AiChatConversation::factory()->create([
            'title' => '教材なしの会話',
            'user_id' => $student->id,
        ]);

        // 教材ありの会話
        $SectionConversation = AiChatConversation::factory()->create([
            'title' => '教材ありの会話',
            'user_id' => $student->id,
            'section_id' => $section->id,
        ]);

        // エラー応答の会話
        $ErrorConversation = AiChatConversation::factory()->create([
            'title' => 'エラー応答の会話',
            'user_id' => $student->id,
        ]);

        // ユーザーメッセージ
        AiChatMessage::factory()
            ->for($NoSectionConversation, 'conversation')
            ->create([
                'role' => AiChatMessageRole::User,
                'content' => 'LaravelのModelについて教えてください。',
            ]);

        // AIの回答
        AiChatMessage::factory()
            ->for($NoSectionConversation, 'conversation')
            ->create([
                'role' => AiChatMessageRole::Assistant,
                'content' => 'Modelはデータベースのデータを扱うクラスです。',
            ]);

        // ユーザーメッセージ
        AiChatMessage::factory()
            ->for($ErrorConversation, 'conversation')
            ->create([
                'role' => AiChatMessageRole::User,
                'content' => 'LaravelのModelについて教えてください。',
            ]);
        // AIのエラー応答
        AiChatMessage::factory()
            ->for($ErrorConversation, 'conversation')
            ->create([
                'role' => AiChatMessageRole::Assistant,
                'status' => AiChatMessageStatus::Error,
                'content' => 'AIの回答を取得できませんでした。時間をおいて再度お試しください。',
                'error_detail' => 'Gemini API request failed.',
            ]);
    }
}
