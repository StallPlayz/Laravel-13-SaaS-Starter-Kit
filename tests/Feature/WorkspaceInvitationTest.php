<?php

use App\Mail\WorkspaceInvite;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use Illuminate\Support\Facades\Mail;

test('owner can invite a guest to workspace', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $owner->id]);

    $this->actingAs($owner)->post(route('workspaces.invitations.store', $workspace), [
        'email' => 'guest@example.com',
        'role' => 'member',
    ]);

    $this->assertDatabaseHas('workspace_invitations', [
        'email' => 'guest@example.com',
        'workspace_id' => $workspace->id,
    ]);

    Mail::assertSent(WorkspaceInvite::class);
});

test('existing user accepts invitation', function () {
    $invitation = WorkspaceInvitation::factory()->create([
        'email' => 'test@example.com',
        'token' => 'test-token',
        'expires_at' => now()->addHours(24),
    ]);

    $user = User::factory()->create(['email' => 'test@example.com']);

    $response = $this->actingAs($user)->get("/invitations/{$invitation->token}");

    $response->assertRedirect('/dashboard');
    $this->assertDatabaseHas('workspace_user', [
        'workspace_id' => $invitation->workspace_id,
        'user_id' => $user->id,
    ]);

    test('cannot accept an expired invitation', function () {
        $invitation = WorkspaceInvitation::factory()->expired()->create();

        $response = $this->get("/invitations/{$invitation->token}");

        $response->assertForbidden();
    });
});
