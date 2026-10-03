<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Announcement;

use App\Enums\AnnouncementTargetType;
use App\Http\Requests\Announcement\StoreRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    // 作成時に正常にバリデーションを通過
    public function test_validation_passes(): void
    {
        // Arrange
        $data = [
            'title' => 'テストタイトル',
            'body' => 'テスト本文',
            'target_type' => AnnouncementTargetType::AllStudents->value,
            'target_certification_id' => null,
            'target_user_id' => null,

        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertTrue($validator->passes());
    }

    // 作成時にタイトルが空だとエラー
    public function test_validation_fails_title_required(): void
    {
        // Arrange
        $data = [
            'title' => null,
            'body' => 'テスト本文',
            'target_type' => AnnouncementTargetType::AllStudents->value,
            'target_certification_id' => null,
            'target_user_id' => null,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    // 作成時にタイトルが200文字を超えるとエラー
    public function test_validation_fails_title_max(): void
    {
        // Arrange
        $data = [
            'title' => str_repeat('a', 201),
            'body' => 'テスト本文',
            'target_type' => AnnouncementTargetType::AllStudents->value,
            'target_certification_id' => null,
            'target_user_id' => null,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    // 作成時に内容が空だとエラー
    public function test_validation_fails_body_required(): void
    {
        // Arrange
        $data = [
            'title' => 'テストタイトル',
            'body' => null,
            'target_type' => AnnouncementTargetType::AllStudents->value,
            'target_certification_id' => null,
            'target_user_id' => null,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('body', $validator->errors()->toArray());
    }

    // 作成時に内容が5000文字を超えるとエラー
    public function test_validation_fails_body_max(): void
    {
        // Arrange
        $data = [
            'title' => 'テストタイトル',
            'body' => str_repeat('a', 5001),
            'target_type' => AnnouncementTargetType::AllStudents->value,
            'target_certification_id' => null,
            'target_user_id' => null,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('body', $validator->errors()->toArray());
    }

    // タイプが対象資格指定のとき作成時に対象資格が空だとエラー
    public function test_validation_fails_target_certification_id_required(): void
    {
        // Arrange
        $data = [
            'title' => 'テストタイトル',
            'body' => 'テスト本文',
            'target_type' => AnnouncementTargetType::Certification->value,
            'target_certification_id' => null,
            'target_user_id' => null,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('target_certification_id', $validator->errors()->toArray());
    }

    // タイプが対象資格指定のとき作成時に存在しない対象資格IDだとエラー
    public function test_validation_fails_target_certification_id_exists(): void
    {
        // Arrange
        $data = [
            'title' => 'テストタイトル',
            'body' => 'テスト本文',
            'target_type' => AnnouncementTargetType::Certification->value,
            'target_certification_id' => 999999,
            'target_user_id' => null,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('target_certification_id', $validator->errors()->toArray());
    }

    // タイプが対象ユーザー指定のとき作成時に対象ユーザーが空だとエラー
    public function test_validation_fails_target_user_id_required(): void
    {
        // Arrange
        $data = [
            'title' => 'テストタイトル',
            'body' => 'テスト本文',
            'target_type' => AnnouncementTargetType::User->value,
            'target_certification_id' => null,
            'target_user_id' => null,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('target_user_id', $validator->errors()->toArray());
    }

    // タイプが対象ユーザー指定のとき作成時に存在しない対象ユーザーIDだとエラー
    public function test_validation_fails_target_user_id_exists(): void
    {
        // Arrange
        $data = [
            'title' => 'テストタイトル',
            'body' => 'テスト本文',
            'target_type' => AnnouncementTargetType::User->value,
            'target_certification_id' => null,
            'target_user_id' => 999999,
        ];
        // Act
        $validator = Validator::make($data, (new StoreRequest)->rules());
        // Assert
        $this->assertArrayHasKey('target_user_id', $validator->errors()->toArray());
    }
}
