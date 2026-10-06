<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\QuizAttemptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $quiz_id
 * @property int $enrollment_id
 * @property int $attempt_number
 * @property string $status
 * @property numeric-string|null $score
 * @property numeric-string|null $max_score
 * @property bool|null $is_passed
 * @property CarbonImmutable $started_at
 * @property CarbonImmutable|null $submitted_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Quiz $quiz
 * @property-read Enrollment $enrollment
 * @property-read Collection<int, QuizAnswer> $answers
 */
#[Fillable(['quiz_id', 'enrollment_id', 'attempt_number', 'status', 'score', 'max_score', 'is_passed', 'started_at', 'submitted_at'])]
class QuizAttempt extends Model
{
    /** @use HasFactory<QuizAttemptFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'is_passed' => 'boolean',
            'started_at' => 'immutable_datetime',
            'submitted_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    /**
     * @return BelongsTo<Enrollment, $this>
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    /**
     * @return HasMany<QuizAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'quiz_attempt_id');
    }
}
