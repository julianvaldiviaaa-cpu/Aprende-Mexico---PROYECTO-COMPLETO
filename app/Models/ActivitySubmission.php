<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ActivitySubmissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $activity_id
 * @property int $enrollment_id
 * @property string|null $response
 * @property string|null $attachment_path
 * @property string $status
 * @property numeric-string|null $score
 * @property string|null $feedback
 * @property CarbonImmutable|null $submitted_at
 * @property int|null $graded_by
 * @property CarbonImmutable|null $graded_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Activity $activity
 * @property-read Enrollment $enrollment
 * @property-read User|null $grader
 */
#[Fillable(['activity_id', 'enrollment_id', 'response', 'attachment_path', 'status', 'score', 'feedback', 'submitted_at', 'graded_by', 'graded_at'])]
#[Hidden(['attachment_path'])]
class ActivitySubmission extends Model
{
    /** @use HasFactory<ActivitySubmissionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'submitted_at' => 'immutable_datetime',
            'graded_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    /**
     * @return BelongsTo<Enrollment, $this>
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
