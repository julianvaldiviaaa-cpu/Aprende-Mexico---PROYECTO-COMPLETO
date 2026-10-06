<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAttempt>
 */
class QuizAttemptFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'enrollment_id' => fn (array $attributes): int => Enrollment::factory()->create(['course_id' => Quiz::query()->whereKey($attributes['quiz_id'])->firstOrFail()->lesson->module->course_id])->id,
            'attempt_number' => 1,
            'status' => 'in_progress',
            'score' => null,
            'max_score' => null,
            'is_passed' => null,
            'started_at' => now(),
            'submitted_at' => null,
        ];
    }
}
