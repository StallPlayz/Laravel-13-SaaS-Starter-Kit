<script setup lang="ts">
import { Form, Head, usePage, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
    auditLogs: Array<{
        id: number;
        impersonator: { name: string };
        method: string;
        url: string;
        route_name: string | null;
        payload: Record<string, any> | null;
        created_at: string;
    }>;
}>();

const formatAction = (routeName: string | null, method: string): string => {
    const actionMap: Record<string, string> = {
        'workspaces.invitations.store': 'Sent Workspace Invitation',
        'workspaces.switch': 'Switched Active Workspace',
        'workspaces.settings.update': 'Updated Workspace Settings',
        'admin.users.impersonate': 'Initiated Impersonation Session',
        'workspaces.update': "Changed Workspace's Public Page",
    };

    if (routeName && actionMap[routeName]) {
        return actionMap[routeName];
    }

    return routeName ? `${method} Action` : 'Unknown Action';
};

const formatPayload = (payload: Record<string, any> | null): string => {
    if (!payload || Object.keys(payload).length === 0) {
return 'No data modified.';
}

    const workspaces = page.props.auth?.availableWorkspaces as Array<{ id: number, name: string }> || [];

    return Object.entries(payload)
        .map(([key, value]) => {

            if (typeof value === 'boolean') {
                const cleanKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                return `${cleanKey}: ${value ? 'Enabled' : 'Disabled'}`;
            }

            switch (key) {
                case 'workspace_id':
                    const targetWorkspace = workspaces.find(w => w.id === Number(value));

                    return `Workspace: ${targetWorkspace ? targetWorkspace.name : `ID ${value} (Removed)`}`;

                case 'slug':
                    return `Public URL: ${value}`;

                case 'settings':
                    if (!value || typeof value !== 'object') {
return 'Settings: Cleared';
}

                    const parsedSettings = Object.entries(value).map(([sKey, sVal]) => {
                        let cleanSKey = sKey.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        let cleanSVal = sVal || 'None';

                        if (sKey === 'theme_color') {
                            cleanSVal = sVal ? String(sVal).toUpperCase() : 'Default White';
                        }

                        if (sKey === 'website_url') {
                            cleanSKey = 'Website URL';
                        }

                        if (sKey === 'description' && typeof sVal === 'string' && sVal.length > 40) {
                            cleanSVal = sVal.substring(0, 40) + '...';
                        }

                        return `${cleanSKey}: ${cleanSVal}`;
                    }).join(', ');

                    return `Configurations: [ ${parsedSettings} ]`;

                default:
                    const cleanKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                    if (typeof value === 'string' && value.length > 40) {
                        return `${cleanKey}: ${value.substring(0, 40)}...`;
                    }

                    const cleanValue = value === null ? 'None' : (typeof value === 'object' ? JSON.stringify(value) : value);

                    return `${cleanKey}: ${cleanValue}`;
            }
        })
        .join(' | ');
};

const page = usePage();
const user = computed(() => page.props.auth.user);

const pinForm = useForm({});

const generatePin = () => {
    pinForm.post('/user/support-pin', {
        preserveScroll: true,
    });
};
</script>

<template>

    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div class="flex flex-col space-y-6">
        <Heading variant="small" title="Profile" description="Update your name and email address" />

        <Form v-bind="ProfileController.update.form()" class="space-y-6" v-slot="{ errors, processing }">
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input id="name" class="mt-1 block w-full" name="name" :default-value="user.name" required
                    autocomplete="name" placeholder="Full name" />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input id="email" type="email" class="mt-1 block w-full" name="email" :default-value="user.email"
                    required autocomplete="username" placeholder="Email address" />
                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div v-if="page.props.mustVerifyEmail && !user.email_verified_at">
                <p class="-mt-4 text-sm text-muted-foreground">
                    Your email address is unverified.
                    <Link :href="send()" as="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500">
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div v-if="page.props.status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600">
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-profile-button">Save</Button>
            </div>
        </Form>
    </div>

    <div v-if="$page.props.auth?.currentRole === 'owner'"
        class="mt-10 flex flex-col space-y-6 border-t border-border pt-10">
        <Heading variant="small" title="Support Access"
            description="Generate a temporary 6-digit PIN to allow platform administrators to securely access your account to resolve issues." />

        <div v-if="user.support_pin" class="rounded-md border border-amber-500/20 bg-amber-500/10 p-4">
            <p class="text-sm text-foreground">
                Your active Support PIN is:
                <span class="ml-2 font-mono text-lg font-bold tracking-widest text-amber-600">
                    {{ user.support_pin }}
                </span>
            </p>
            <p class="mt-1 text-xs text-muted-foreground">
                This PIN will automatically expire and be revoked in exactly 24 hours to protect your account security.
            </p>
        </div>
        <div v-else class="flex items-center gap-4">
            <Button @click="generatePin" :disabled="pinForm.processing" variant="secondary">
                Generate Support PIN
            </Button>
        </div>
    </div>

    <div class="mt-10 flex flex-col space-y-6 border-t border-border pt-10">
        <Heading variant="small" title="Support Access History"
            description="A secure audit log of all account modifications performed by platform administrators during impersonation sessions." />

        <div class="overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm">
            <div class="max-h-[400px] overflow-auto">
                <table class="w-full text-left text-sm relative">
                    <thead
                        class="sticky top-0 z-10 bg-muted/95 backdrop-blur text-xs uppercase text-muted-foreground shadow-sm">
                        <tr>
                            <th class="px-4 py-3 font-medium">Timestamp</th>
                            <th class="px-4 py-3 font-medium">Administrator</th>
                            <th class="px-4 py-3 font-medium">Action</th>
                            <th class="px-4 py-3 font-medium">Data Payload</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y border-t">
                        <tr v-for="log in auditLogs" :key="log.id" class="hover:bg-muted/30">
                            <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">
                                {{ new Date(log.created_at).toLocaleString() }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-foreground">
                                {{ log.impersonator?.name || 'Unknown Admin' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="font-medium text-foreground">
                                    {{ formatAction(log.route_name, log.method) }}
                                </span>
                                <br>
                                <span class="text-xs text-muted-foreground opacity-70">
                                    System Route: {{ log.route_name || log.url }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-muted-foreground">
                                <div v-if="log.payload" class="max-h-20 max-w-xs overflow-y-auto rounded bg-muted p-2">
                                    {{ formatPayload(log.payload) }}
                                </div>
                                <span v-else class="italic">No payload</span>
                            </td>
                        </tr>
                        <tr v-if="!auditLogs || auditLogs.length === 0">
                            <td colspan="4" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                <span class="block font-medium text-foreground/70 mb-1">No support modifications
                                    recorded.</span>
                                Any configuration changes made by platform administrators during an active support
                                session will be permanently logged here for your security.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-10 border-t border-border pt-10">
        <DeleteUser />
    </div>
</template>