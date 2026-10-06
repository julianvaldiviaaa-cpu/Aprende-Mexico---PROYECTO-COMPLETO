<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Carbon\CarbonImmutable;
use Database\Factories\InstitutionFactory;
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
 * @property int $owner_user_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $logo_path
 * @property bool $is_verified
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read User $owner
 * @property-read Collection<int, InstitutionMember> $members
 * @property-read Collection<int, Course> $courses
 * @property-read Collection<int, Classroom> $classrooms
 * @property-read Collection<int, User> $users
 */
#[Fillable(['owner_user_id', 'name', 'description', 'logo_path', 'is_verified'])]
class Institution extends Model
{
    /** @use HasFactory<InstitutionFactory> */
    use HasFactory;

    use HasSlug;
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
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
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * @return HasMany<InstitutionMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(InstitutionMember::class, 'institution_id');
    }

    /**
     * @return HasMany<Course, $this>
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'institution_id');
    }

    /**
     * @return HasMany<Classroom, $this>
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'institution_id');
    }

    /**
     * @return BelongsToMany<User, $this, InstitutionMember>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'institution_members', 'institution_id', 'user_id')->using(InstitutionMember::class)->withPivot(['id', 'role', 'joined_at'])->withTimestamps();
    }
}
