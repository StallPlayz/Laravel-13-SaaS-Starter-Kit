<?php

namespace App\Models;

use App\Attributes\Casts;
use App\Concerns\HasCastsAttribute;
use App\Events\AdminDataUpdated;
use Database\Factories\WorkspaceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @phpstan-consistent-constructor
 */
#[Fillable(['owner_id', 'name', 'slug', 'tier', 'settings'])]
#[Casts([
    'settings' => 'array',
])]
class Workspace extends Model
{
    /** @use HasFactory<WorkspaceFactory> */
    use HasCastsAttribute, HasFactory;

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /** @return HasMany<WorkspaceInvitation, $this> */
    public function invitations(): HasMany
    {
        return $this->hasMany(WorkspaceInvitation::class);
    }

    protected static function booted(): void
    {
        static::saved(function () {
            event(new AdminDataUpdated('workspace'));
        });

        static::deleted(function () {
            event(new AdminDataUpdated('workspace'));
        });
    }
}
