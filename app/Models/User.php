<?php

namespace App\Models;

use App\UserRole;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property string $status
 * @property UserRole $role
 * @property string|null $avatar_path
 * @property CarbonImmutable|null $deleted_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read InstructorProfile|null $instructorProfile
 * @property-read Collection<int, InstructorProfile> $reviewedInstructorProfiles
 * @property-read Collection<int, Institution> $ownedInstitutions
 * @property-read Collection<int, InstitutionMember> $institutionMemberships
 * @property-read Collection<int, Course> $taughtCourses
 * @property-read Collection<int, Enrollment> $enrollments
 * @property-read Collection<int, ActivitySubmission> $gradedActivitySubmissions
 * @property-read Collection<int, Classroom> $createdClassrooms
 * @property-read Collection<int, ClassroomMember> $classroomMemberships
 * @property-read Collection<int, CourseAssignment> $courseAssignments
 * @property-read Collection<int, Institution> $institutions
 * @property-read Collection<int, Classroom> $classrooms
 * @property-read Collection<int, Course> $enrolledCourses
 */
#[Fillable(['name', 'email', 'password', 'avatar_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'immutable_datetime',
            'role' => UserRole::class,
            'deleted_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function canCreateCourses(): bool
    {
        return $this->status === 'active' && ($this->isAdmin()
            || ($this->role === UserRole::Instructor && $this->instructorProfile?->status === 'approved'));
    }

    /**
     * @return HasOne<InstructorProfile, $this>
     */
    public function instructorProfile(): HasOne
    {
        return $this->hasOne(InstructorProfile::class, 'user_id');
    }

    /**
     * @return HasMany<InstructorProfile, $this>
     */
    public function reviewedInstructorProfiles(): HasMany
    {
        return $this->hasMany(InstructorProfile::class, 'reviewed_by');
    }

    /**
     * @return HasMany<Institution, $this>
     */
    public function ownedInstitutions(): HasMany
    {
        return $this->hasMany(Institution::class, 'owner_user_id');
    }

    /**
     * @return HasMany<InstitutionMember, $this>
     */
    public function institutionMemberships(): HasMany
    {
        return $this->hasMany(InstitutionMember::class, 'user_id');
    }

    /**
     * @return HasMany<Course, $this>
     */
    public function taughtCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'user_id');
    }

    /**
     * @return HasMany<ActivitySubmission, $this>
     */
    public function gradedActivitySubmissions(): HasMany
    {
        return $this->hasMany(ActivitySubmission::class, 'graded_by');
    }

    /**
     * @return HasMany<Classroom, $this>
     */
    public function createdClassrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'created_by');
    }

    /**
     * @return HasMany<ClassroomMember, $this>
     */
    public function classroomMemberships(): HasMany
    {
        return $this->hasMany(ClassroomMember::class, 'user_id');
    }

    /**
     * @return HasMany<CourseAssignment, $this>
     */
    public function courseAssignments(): HasMany
    {
        return $this->hasMany(CourseAssignment::class, 'assigned_by');
    }

    /**
     * @return BelongsToMany<Institution, $this, InstitutionMember>
     */
    public function institutions(): BelongsToMany
    {
        return $this->belongsToMany(Institution::class, 'institution_members', 'user_id', 'institution_id')->using(InstitutionMember::class)->withPivot(['id', 'role', 'joined_at'])->withTimestamps();
    }

    /**
     * @return BelongsToMany<Classroom, $this, ClassroomMember>
     */
    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_members', 'user_id', 'classroom_id')->using(ClassroomMember::class)->withPivot(['id', 'role', 'joined_at'])->withTimestamps();
    }

    /**
     * @return BelongsToMany<Course, $this>
     */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments', 'user_id', 'course_id')->withPivot(['id', 'status', 'enrolled_at', 'completed_at'])->withTimestamps();
    }
}
