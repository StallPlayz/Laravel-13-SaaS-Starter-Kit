<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory(100)->create();

        $owners = $users->random(30);
        $workspaces = collect();

        foreach ($owners as $owner) {
            $workspace = Workspace::factory()->create([
                'owner_id' => $owner->id,
                'tier' => fake()->randomElement(['free', 'pro']),
                'is_suspended' => fake()->boolean(5),
            ]);

            DB::table('workspace_user')->insert([
                'workspace_id' => $workspace->id,
                'user_id' => $owner->id,
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $workspaces->push($workspace);
        }

        $nonOwners = $users->diff($owners);
        $roles = ['admin', 'member', 'client'];

        foreach ($nonOwners as $user) {
            $assignedWorkspaces = $workspaces->random(rand(1, 3));

            foreach ($assignedWorkspaces as $ws) {
                DB::table('workspace_user')->insert([
                    'workspace_id' => $ws->id,
                    'user_id' => $user->id,
                    'role' => fake()->randomElement($roles),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        for ($i = 0; $i < 25; $i++) {
            DB::table('workspace_invitations')->insert([
                'workspace_id' => $workspaces->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'role' => fake()->randomElement($roles),
                'token' => Str::random(64),
                'expires_at' => now()->addDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        for ($i = 0; $i < 60; $i++) {
            DB::table('audit_logs')->insert([
                'user_id' => $users->random()->id,
                'impersonator_id' => $owners->first()->id,
                'method' => fake()->randomElement(['GET', 'POST', 'PUT', 'DELETE']),
                'url' => fake()->url(),
                'route_name' => 'simulated.route.' . fake()->word(),
                'payload' => json_encode(['simulated_action' => fake()->word()]),
                'ip_address' => fake()->ipv4(),
                'created_at' => now()->subMinutes(rand(1, 10000)),
                'updated_at' => now(),
            ]);
        }
    }
}