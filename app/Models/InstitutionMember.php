<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\InstitutionMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $user_id
 * @property string $role
 * @property CarbonImmutable $joined_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Institution $institution
 * @property-read User $user
 */
#[Fillable(['institution_id', 'user_id', 'role', 'joined_at'])]
class InstitutionMember extends Pivot
{
    /** @use HasFactory<InstitutionMemberFactory> */
    use HasFactory;

    public $incrementing = true;

    protected $table = 'institution_members';

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
     * @return BelongsTo<Institution, $this>
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
