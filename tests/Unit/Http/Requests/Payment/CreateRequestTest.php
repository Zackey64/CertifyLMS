<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Payment;

use App\Http\Requests\Payment\CreateRequest;
use App\Models\MeetingPack;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateRequestTest extends TestCase
{
    use RefreshDatabase;

    // 更新時に正常にバリデーションを通過
    public function test_validation_passes(): void
    {
        // Arrange
        $data = [
            'meeting_pack_id' => MeetingPack::factory()->create()->id,
        ];
        // Act
        $validator = Validator::make($data, (new CreateRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 作成時にmeeting_pack_idが空だとエラー
    public function test_validation_fails_meeting_pack_id_required(): void
    {
        // Arrange
        $data = [
            'meeting_pack_id' => null,
        ];
        // Act
        $validator = Validator::make($data, (new CreateRequest)->rules());
        // Assert
        $this->assertArrayHasKey('meeting_pack_id', $validator->errors()->toArray());
    }

    // 作成時に存在しないmeeting_pack_idだとエラー
    public function test_validation_fails_meeting_exists(): void
    {
        // Arrange
        $data = [
            'meeting_pack_id' => 999,
        ];
        // Act
        $validator = Validator::make($data, (new CreateRequest)->rules());
        // Assert
        $this->assertArrayHasKey('meeting_pack_id', $validator->errors()->toArray());
    }
}
