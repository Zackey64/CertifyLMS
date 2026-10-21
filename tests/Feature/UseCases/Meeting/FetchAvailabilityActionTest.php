<?php

declare(strict_types=1);

namespace Tests\Feature\UseCases\Meeting;

use App\Models\Enrollment;
use App\UseCases\Meeting\FetchAvailabilityAction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class FetchAvailabilityActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_availability(): void
    {
        // arrange
        $enrollment = Enrollment::factory()->create();
        $date = Carbon::parse(now());
        // act
        $result = app(FetchAvailabilityAction::class)(
            $enrollment,
            $date,
        );
        // assert
        $this->assertInstanceOf(Collection::class, $result);
    }
}
