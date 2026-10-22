<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Certification;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Part;
use App\Models\Section;
use App\Models\SectionProgress;
use App\Models\User;
use App\Services\Learning\LearningProgressService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_calculate_progress_returns_empty_array_when_no_enrollments(): void
    {
        $result = app(LearningProgressService::class)->batchCalculateProgress(new Collection);
        $this->assertSame([], $result);
    }

    public function test_summarize_returns_section_counts_and_completion_ratio(): void
    {
        $certification = Certification::factory()->create();
        $user = User::factory()->create();

        $enrollment = Enrollment::factory()->for($user)->for($certification)->create();
        $part = Part::factory()->published()->forCertification($certification)->create();
        $chapter = Chapter::factory()->published()->forPart($part)->create();
        $sections = Section::factory()->published()->forChapter($chapter)->count(4)->create();

        // 4件中3件完了
        foreach ($sections->take(3) as $section) {
            SectionProgress::factory()->forEnrollment($enrollment)->forSection($section)->create();
        }

        $summary = app(LearningProgressService::class)->summarize($enrollment);

        $this->assertSame(4, $summary->sectionsTotal);
        $this->assertSame(3, $summary->sectionsCompleted);
        $this->assertSame(0.75, $summary->sectionCompletionRatio);
        $this->assertSame(0.75, $summary->overallCompletionRatio);
    }

    public function test_summarize_counts_completed_chapters_and_parts(): void
    {
        $certification = Certification::factory()->create();
        $user = User::factory()->create();

        $enrollment = Enrollment::factory()->for($user)->for($certification)->create();
        $part = Part::factory()->published()->forCertification($certification)->create();
        $chapter = Chapter::factory()->published()->forPart($part)->create();
        $sections = Section::factory()->published()->forChapter($chapter)->count(2)->create();

        // Chapter・Part 配下の Section をすべて完了
        foreach ($sections as $section) {
            SectionProgress::factory()->forEnrollment($enrollment)->forSection($section)->create();
        }

        $summary = app(LearningProgressService::class)->summarize($enrollment);

        $this->assertSame(1, $summary->chaptersTotal);
        $this->assertSame(1, $summary->chaptersCompleted);
        $this->assertSame(1.0, $summary->chapterCompletionRatio);

        $this->assertSame(1, $summary->partsTotal);
        $this->assertSame(1, $summary->partsCompleted);
        $this->assertSame(1.0, $summary->partCompletionRatio);
    }

    public function test_batch_calculate_progress_returns_progress_ratio_per_enrollment(): void
    {
        $certification = Certification::factory()->create();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $enrollment1 = Enrollment::factory()->for($user1)->for($certification)->create();
        $enrollment2 = Enrollment::factory()->for($user2)->for($certification)->create();

        $part = Part::factory()->published()->forCertification($certification)->create();
        $chapter = Chapter::factory()->published()->forPart($part)->create();
        $sections = Section::factory()->published()->forChapter($chapter)->count(4)->create();

        // 1人目は4件中2件を完了
        foreach ($sections->take(2) as $section) {
            SectionProgress::factory()->forEnrollment($enrollment1)->forSection($section)->create();
        }

        // 2人目は4件中3件を完了
        foreach ($sections->take(3) as $section) {
            SectionProgress::factory()->forEnrollment($enrollment2)->forSection($section)->create();
        }

        $result = app(LearningProgressService::class)->batchCalculateProgress(
            new Collection([$enrollment1, $enrollment2]),
        );

        $this->assertSame(0.5, $result[$enrollment1->id]);
        $this->assertSame(0.75, $result[$enrollment2->id]);
    }
}
