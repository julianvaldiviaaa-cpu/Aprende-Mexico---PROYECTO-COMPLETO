<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'instructor_id' => User::factory(),
            'institution_id' => null,
            'title' => fake()->sentence(4),
            'summary' => null,
            'description' => null,
            'cover_path' => null,
            'level' => 'beginner',
            'status' => 'draft',
            'published_at' => null,
        ];
    }

    public function institutional(?Institution $institution = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'institution_id' => $institution ?? Institution::factory(),
        ]);
    }
}
