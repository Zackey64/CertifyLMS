<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Models\User;
use App\Policies\PlanPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanPolicyTest extends TestCase
{
    use RefreshDatabase;

    // アドミンのみプラン一覧画面を閲覧できる
    public function test_admin_only_can_view_any_plan(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $policy = new PlanPolicy;
        // Assert
        $this->assertFalse($policy->viewAny($student));
        $this->assertFalse($policy->viewAny($coach));
        $this->assertTrue($policy->viewAny($admin));
    }

    // アドミンのみプランを作成できる
    public function test_admin_only_can_create_plan(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $policy = new PlanPolicy;
        // Assert
        $this->assertFalse($policy->create($student));
        $this->assertFalse($policy->create($coach));
        $this->assertTrue($policy->create($admin));
    }

    // アドミンのみプラン詳細画面を閲覧できる
    public function test_admin_only_can_view_plan(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create();
        $policy = new PlanPolicy;
        // Assert
        $this->assertFalse($policy->view($student, $plan));
        $this->assertFalse($policy->view($coach, $plan));
        $this->assertTrue($policy->view($admin, $plan));
    }

    // アドミンのみプランを更新できる
    public function test_admin_only_can_update_plan(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create();
        $policy = new PlanPolicy;
        // Assert
        $this->assertFalse($policy->update($student, $plan));
        $this->assertFalse($policy->update($coach, $plan));
        $this->assertTrue($policy->update($admin, $plan));
    }

    // アドミンのみプランを削除できる
    public function test_admin_only_can_delete_plan(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Draft,
        ]);
        $policy = new PlanPolicy;
        // Assert
        $this->assertFalse($policy->delete($student, $plan));
        $this->assertFalse($policy->delete($coach, $plan));
        $this->assertTrue($policy->delete($admin, $plan));
    }

    // 公開中のプランは削除できない
    public function test_admin_cannot_delete_plan_published(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Published,
        ]);
        $policy = new PlanPolicy;
        // Assert
        $this->assertFalse($policy->delete($admin, $plan));
    }

    // 受講者がいるプランは削除できない
    public function test_admin_cannot_delete_plan_with_users(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Draft,
        ]);
        User::factory()->student()->create([
            'plan_id' => $plan->id,
        ]);
        $policy = new PlanPolicy;
        // Assert
        $this->assertFalse($policy->delete($admin, $plan));
    }

    // アドミンのみプランを公開できる
    public function test_admin_only_can_publish_plan(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Draft,
        ]);
        $policy = new PlanPolicy;
        // Assert
        $this->assertFalse($policy->publish($student, $plan));
        $this->assertFalse($policy->publish($coach, $plan));
        $this->assertTrue($policy->publish($admin, $plan));
    }

    // アーカイブのプランは公開できない
    public function test_admin_cannot_publish_plan(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Archived,
        ]);
        $policy = new PlanPolicy;
        // Assert
        $this->assertFalse($policy->publish($admin, $plan));
    }

    // アドミンのみプランをアーカイブできる
    public function test_admin_only_can_archive_plan(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Published,
        ]);
        $policy = new PlanPolicy;
        // Assert
        $this->assertFalse($policy->archive($student, $plan));
        $this->assertFalse($policy->archive($coach, $plan));
        $this->assertTrue($policy->archive($admin, $plan));
    }

    // 下書きのプランはアーカイブできない
    public function test_admin_cannot_archive_plan(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Draft,
        ]);
        $policy = new PlanPolicy;
        // Assert
        $this->assertFalse($policy->archive($admin, $plan));
    }

    // アドミンのみプランを下書きに戻せる
    public function test_admin_only_can_unarchive_plan(): void
    {
        // Arrange
        $student = User::factory()->student()->create();
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Archived,
        ]);
        $policy = new PlanPolicy;
        // Assert
        $this->assertFalse($policy->unarchive($student, $plan));
        $this->assertFalse($policy->unarchive($coach, $plan));
        $this->assertTrue($policy->unarchive($admin, $plan));
    }

    // 公開中のプランは下書きにできない
    public function test_admin_cannot_unarchive_plan(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create([
            'status' => PlanStatus::Published,
        ]);
        $policy = new PlanPolicy;
        // Assert
        $this->assertFalse($policy->unarchive($admin, $plan));
    }
}
