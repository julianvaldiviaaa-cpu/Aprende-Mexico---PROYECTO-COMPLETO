<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivitySubmission>
 */
class ActivitySubmissionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'enrollment_id' => fn (array $attributes): int => Enrollment::factory()->create(['course_id' => Activity::query()->whereKey($attributes['activity_id'])->firstOrFail()->lesson->module->course_id])->id,
            'response' => null,
            'attachment_path' => null,
            'status' => 'draft',
            'score' => null,
            'feedback' => null,
            'submitted_at' => null,
            'graded_by' => null,
            'graded_at' => null,
        ];
    }
}
