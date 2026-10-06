<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ClassroomMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $classroom_id
 * @property int $user_id
 * @property string $role
 * @property CarbonImmutable $joined_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Classroom $classroom
 * @property-read User $user
 */
#[Fillable(['classroom_id', 'user_id', 'role', 'joined_at'])]
class ClassroomMember extends Pivot
{
    /** @use HasFactory<ClassroomMemberFactory> */
    use HasFactory;

    public $incrementing = true;

    protected $table = 'classroom_members';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joined_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<Classroom, $this>
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
