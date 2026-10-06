<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAnswer>
 */
class QuizAnswerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'quiz_attempt_id' => fn (array $attributes): int => QuizAttempt::factory()->create(['quiz_id' => Question::query()->whereKey($attributes['question_id'])->firstOrFail()->quiz_id])->id,
            'response' => null,
            'points_awarded' => null,
        ];
    }
}
