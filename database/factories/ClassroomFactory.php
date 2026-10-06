<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'created_by' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => null,
            'status' => 'active',
        ];
    }
}
