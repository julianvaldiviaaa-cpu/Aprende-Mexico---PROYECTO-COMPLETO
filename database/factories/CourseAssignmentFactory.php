<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseAssignment>
 */
class CourseAssignmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'classroom_id' => Classroom::factory(),
            'course_id' => Course::factory(),
            'assigned_by' => User::factory(),
            'due_at' => null,
        ];
    }
}
