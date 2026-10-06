<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Carbon\CarbonImmutable;
use Database\Factories\ClassroomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $created_by
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $status
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Institution $institution
 * @property-read User $creator
 * @property-read Collection<int, ClassroomMember> $members
 * @property-read Collection<int, CourseAssignment> $assignments
 * @property-read Collection<int, User> $users
 * @property-read Collection<int, Course> $courses
 */
#[Fillable(['institution_id', 'created_by', 'name', 'description', 'status'])]
class Classroom extends Model
{
    /** @use HasFactory<ClassroomFactory> */
    use HasFactory;

    use HasSlug;
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
        ];
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    /**
     * @return BelongsTo<Institution, $this>
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<ClassroomMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(ClassroomMember::class, 'classroom_id');
    }

    /**
     * @return HasMany<CourseAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(CourseAssignment::class, 'classroom_id');
    }

    /**
     * @return BelongsToMany<User, $this, ClassroomMember>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'classroom_members', 'classroom_id', 'user_id')->using(ClassroomMember::class)->withPivot(['id', 'role', 'joined_at'])->withTimestamps();
    }

    /**
     * @return BelongsToMany<Course, $this>
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_assignments', 'classroom_id', 'course_id')->withPivot(['id', 'assigned_by', 'due_at'])->withTimestamps();
    }
}
