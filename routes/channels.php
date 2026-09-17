<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('admin.health', function (User $user) {
    return $user->isSuperAdmin();
});
