<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QaReply>
 */
class QaReplyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'qa_thread_id' => QaThread::factory(),
            'body' => fake()->randomElement([
                'FormRequestを作成してrulesメソッドに定義すると管理しやすいです。',
                'git switch -c ブランチ名で新しいブランチを作成できます。',
                'Featureテストでは実際のHTTPリクエストを使って動作を確認できます。',
            ]),
        ];
    }
}
