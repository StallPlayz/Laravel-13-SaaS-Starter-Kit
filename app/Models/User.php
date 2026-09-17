<?php

namespace App\Models;

use App\Attributes\Casts;
use App\Concerns\HasCastsAttribute;
use App\Events\AdminDataUpdated;
use App\Notifications\CustomVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'phone_number', 'country', 'province', 'city', 'district', 'address', 'terms'])]
#[Hidden(['password', 'phone_number', 'address', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
#[Casts([
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'terms' => 'boolean',
    'two_factor_confirmed_at' => 'datetime',
    'support_pin_expires_at' => 'datetime',
])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasCastsAttribute, HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * The workspaces that belong to the user.
     *
     * @return BelongsToMany<Workspace, $this>
     */
    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the user's role in the given workspace.
     */
    public function workspaceRole(Workspace $workspace): ?string
    {
        if ($this->id === $workspace->owner_id) {
            return 'owner';
        }

        $workspaceUser = $this->workspaces()->where('workspace_id', $workspace->id)->first();

        /** @var string|null $role */
        $role = $workspaceUser?->pivot?->getAttribute('role');

        return $role;
    }

    /**
     * Check if the user has a specific role in the given workspace.
     */
    public function hasWorkspaceRole(Workspace $workspace, string $role): bool
    {
        return $this->workspaceRole($workspace) === $role;
    }

    /**
     * Check if the user has global platform administrator privileges.
     */
    public function isSuperAdmin(): bool
    {
        return $this->platform_role === 'super_admin';
    }

    /**
     * Override the default email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmail);
    }

    protected static function booted(): void
    {
        static::saved(function () {
            event(new AdminDataUpdated('user'));
        });

        static::deleted(function () {
            event(new AdminDataUpdated('user'));
        });
    }
}
