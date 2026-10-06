<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonProgress>
 */
class LessonProgressFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'enrollment_id' => fn (array $attributes): int => Enrollment::factory()->create(['course_id' => Lesson::query()->whereKey($attributes['lesson_id'])->firstOrFail()->module->course_id])->id,
            'is_completed' => false,
            'last_position_seconds' => 0,
            'completed_at' => null,
        ];
    }
}
