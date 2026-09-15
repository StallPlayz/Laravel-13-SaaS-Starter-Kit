<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Casts;
use Illuminate\Database\Eloquent\Concerns\HasCastsAttribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Guarded([])]
#[Casts([
    'payload' => 'array',
])]
class AuditLog extends Model
{
    use HasCastsAttribute;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function impersonator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'impersonator_id');
    }
}
