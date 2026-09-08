<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { directory } from '@/routes';

const props = defineProps<{
    workspace: { id: number; name: string };
    members: Array<{ id: number; name: string; email: string; role: string }>;
    pendingInvitations: Array<{
        id: number;
        email: string;
        role: string;
        expires_at: string;
        is_expired: boolean;
    }>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Directory',
                href: directory(),
            },
        ],
    },
});

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

    <div class="mx-auto max-w-7xl space-y-8 py-10 sm:px-6 lg:px-8">
        <div
            class="overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm"
        >
            <div class="border-b px-6 py-5">
                <h3 class="text-lg font-medium">
                    Invite to {{ workspace.name }}
                </h3>
                <p class="text-sm text-muted-foreground">
                    Send a magic link to add a new admin, member, or client.
                </p>
            </div>
            <div class="p-6">
                <form
                    @submit.prevent="sendInvite"
                    class="flex flex-col items-start gap-4 md:flex-row"
                >
                    <div class="w-full flex-1">
                        <label
                            for="email"
                            class="block text-sm font-medium text-foreground"
                            >Email Address</label
                        >
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full rounded-md border-input bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                            placeholder="colleague@example.com"
                            required
                        />
                        <p
                            v-if="form.errors.email"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="w-full md:w-48">
                        <label
                            for="role"
                            class="block text-sm font-medium text-foreground"
                            >Role</label
                        >
                        <select
                            id="role"
                            v-model="form.role"
                            class="mt-1 block w-full rounded-md border-input bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                        >
                            <option value="admin">Admin</option>
                            <option value="member">Member</option>
                            <option value="client">Client</option>
                        </select>
                        <p
                            v-if="form.errors.role"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.role }}
                        </p>
                    </div>

                    <div class="mt-1 md:mt-6">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                        >
                            Send Invitation
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm"
        >
            <div class="border-b px-6 py-5">
                <h3 class="text-lg font-medium">Active Members</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="bg-muted text-xs text-muted-foreground uppercase"
                    >
                        <tr>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="member in members"
                            :key="member.id"
                            class="hover:bg-muted/50"
                        >
                            <td class="px-6 py-4 font-medium">
                                {{ member.name }}
                            </td>
                            <td class="px-6 py-4 text-muted-foreground">
                                {{ member.email }}
                            </td>
                            <td class="px-6 py-4 capitalize">
                                {{ member.role }}
                            </td>
                        </tr>
                        <tr v-if="members.length === 0">
                            <td
                                colspan="3"
                                class="px-6 py-4 text-center text-muted-foreground"
                            >
                                No active members found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm"
        >
            <div class="border-b px-6 py-5">
                <h3 class="text-lg font-medium">Pending Invitations</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="bg-muted text-xs text-muted-foreground uppercase"
                    >
                        <tr>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3">Expires</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="invite in pendingInvitations"
                            :key="invite.id"
                            class="hover:bg-muted/50"
                        >
                            <td class="px-6 py-4">{{ invite.email }}</td>
                            <td class="px-6 py-4 capitalize">
                                {{ invite.role }}
                            </td>
                            <td
                                class="px-6 py-4"
                                :class="{
                                    'font-medium text-red-500':
                                        invite.is_expired,
                                    'text-muted-foreground': !invite.is_expired,
                                }"
                            >
                                {{
                                    invite.is_expired
                                        ? 'Expired'
                                        : invite.expires_at
                                }}
                            </td>
                        </tr>
                        <tr v-if="pendingInvitations.length === 0">
                            <td
                                colspan="3"
                                class="px-6 py-4 text-center text-muted-foreground"
                            >
                                No pending invitations.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
