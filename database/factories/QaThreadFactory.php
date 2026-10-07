<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\QaThreadStatus;
use App\Models\Certification;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QaThread>
 */
class QaThreadFactory extends Factory
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
            'certification_id' => Certification::factory(),
            'title' => fake()->randomElement([
                'Laravelのバリデーションについて教えてください',
                'Gitでブランチを作成する方法を教えてください',
                'テストの書き方について質問です',
            ]),
            'body' => fake()->randomElement([
                'FormRequestを使う場合の基本的な書き方を教えてください。',
                '新しいブランチを作成して作業する方法を知りたいです。',
                'LaravelでFeatureテストを書く際のポイントを教えてください。',
            ]),
            'status' => QaThreadStatus::Open,
            'resolved_at' => null,
        ];
    }
}
