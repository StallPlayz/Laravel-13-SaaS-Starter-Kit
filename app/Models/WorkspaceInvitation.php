<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\{Fillable, Casts};
use Illuminate\Database\Eloquent\Concerns\HasCastsAttribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['workspace_id', 'email', 'role', 'token', 'expires_at'])]
#[Casts([
    'expires_at' => 'datetime',
])]
class WorkspaceInvitation extends Model
{
    use HasCastsAttribute;

    /** @return BelongsTo<Workspace, $this> */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
