<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'title' => fake()->sentence(3),
            'instructions' => null,
            'type' => 'text',
            'max_points' => 100,
            'is_required' => true,
        ];
    }
}
