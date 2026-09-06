<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { directory } from '@/routes';

const props = defineProps<{
    workspace: { id: number; name: string };
    members: Array<{ id: number; name: string; email: string; role: string }>;
    pendingInvitations: Array<{ id: number; email: string; role: string; expires_at: string; is_expired: boolean }>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Directory',
                href: directory(),
            }
        ]
    }
})

const form = useForm({
    email: '',
    role: 'client',
});

const sendInvite = () => {
    form.post(`/workspaces/${props.workspace.id}/invitations`, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Directory" />

    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 space-y-8">
        
        <div class="bg-card text-card-foreground border rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b">
                <h3 class="text-lg font-medium">Invite to {{ workspace.name }}</h3>
                <p class="text-sm text-muted-foreground">Send a magic link to add a new admin, member, or client.</p>
            </div>
            <div class="p-6">
                <form @submit.prevent="sendInvite" class="flex flex-col md:flex-row gap-4 items-start">
                    <div class="flex-1 w-full">
                        <label for="email" class="block text-sm font-medium text-foreground">Email Address</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="block w-full mt-1 border-input bg-background text-foreground rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                            placeholder="colleague@example.com"
                            required
                        />
                        <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div class="w-full md:w-48">
                        <label for="role" class="block text-sm font-medium text-foreground">Role</label>
                        <select
                            id="role"
                            v-model="form.role"
                            class="block w-full mt-1 border-input bg-background text-foreground rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                        >
                            <option value="admin">Admin</option>
                            <option value="member">Member</option>
                            <option value="client">Client</option>
                        </select>
                        <p v-if="form.errors.role" class="mt-2 text-sm text-red-600">{{ form.errors.role }}</p>
                    </div>

                    <div class="mt-1 md:mt-6">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center px-4 py-2 bg-primary text-primary-foreground rounded-md font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50"
                        >
                            Send Invitation
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-card text-card-foreground border rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b">
                <h3 class="text-lg font-medium">Active Members</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-muted text-muted-foreground uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="member in members" :key="member.id" class="hover:bg-muted/50">
                            <td class="px-6 py-4 font-medium">{{ member.name }}</td>
                            <td class="px-6 py-4 text-muted-foreground">{{ member.email }}</td>
                            <td class="px-6 py-4 capitalize">{{ member.role }}</td>
                        </tr>
                        <tr v-if="members.length === 0">
                            <td colspan="3" class="px-6 py-4 text-center text-muted-foreground">No active members found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-card text-card-foreground border rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b">
                <h3 class="text-lg font-medium">Pending Invitations</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-muted text-muted-foreground uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3">Expires</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="invite in pendingInvitations" :key="invite.id" class="hover:bg-muted/50">
                            <td class="px-6 py-4">{{ invite.email }}</td>
                            <td class="px-6 py-4 capitalize">{{ invite.role }}</td>
                            <td class="px-6 py-4" :class="{'text-red-500 font-medium': invite.is_expired, 'text-muted-foreground': !invite.is_expired}">
                                {{ invite.is_expired ? 'Expired' : invite.expires_at }}
                            </td>
                        </tr>
                        <tr v-if="pendingInvitations.length === 0">
                            <td colspan="3" class="px-6 py-4 text-center text-muted-foreground">No pending invitations.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>