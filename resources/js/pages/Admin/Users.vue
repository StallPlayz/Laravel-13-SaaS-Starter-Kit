<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { watch, ref, onMounted, onUnmounted } from 'vue';
import { index } from '@/routes/admin/users';

const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            workspaces_count: number;
            created_at: string;
            support_pin_expires_at: string | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: { search?: string };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Platform Users',
                href: index(),
            },
        ],
    },
});

const searchQuery = ref(props.filters?.search || '');

watch(searchQuery, debounce((value: string) => {
    router.get(index(), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const initiateImpersonation = (user: { id: number; name: string }) => {
    const pin = window.prompt(`Enter the 6-digit Support PIN provided by ${user.name}:`);

    if (pin) {
        router.post(`/admin/users/${user.id}/impersonate`, { pin }, {
            preserveScroll: true,
        });
    }
};

onMounted(() => {
    if (typeof window !== 'undefined' && window.Echo) {
        window.Echo.private('admin.health')
            .listen('.AdminDataUpdated', (e: { type: string }) => {
                if (e.type === 'user') {
                    router.reload({ only: ['users'] });
                }
            });
    }
});

onUnmounted(() => {
    if (typeof window !== 'undefined' && window.Echo) {
        window.Echo.leave('admin.health');
    }
});
</script>

<template>

    <Head title="Super Admin - Users" />

    <div class="p-8">
        <h1 class="mb-6 text-3xl font-bold tracking-tight text-foreground">
            Platform Users
        </h1>

        <div class="overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b px-6 py-5">
                <div>
                    <h3 class="text-lg font-medium">Global User Directory</h3>
                    <p class="text-sm text-muted-foreground">Manage accounts and initiate secure impersonation sessions
                        via Support PINs.</p>
                </div>

                <div class="relative w-full sm:w-72">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <input v-model="searchQuery" type="text" placeholder="Search users..."
                        class="h-9 w-full rounded-md border border-input bg-transparent px-8 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-muted text-xs uppercase text-muted-foreground">
                        <tr>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Workspaces</th>
                            <th class="px-6 py-3">Joined</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-muted/50">
                            <td class="px-6 py-4 font-medium">{{ user.name }}</td>
                            <td class="px-6 py-4 text-muted-foreground">{{ user.email }}</td>
                            <td class="px-6 py-4">{{ user.workspaces_count }}</td>
                            <td class="px-6 py-4 text-muted-foreground">
                                {{ new Date(user.created_at).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button @click="initiateImpersonation(user)"
                                    class="inline-flex items-center justify-center rounded-md bg-amber-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-500 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                    Impersonate
                                </button>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="5" class="px-6 py-4 text-center text-muted-foreground">
                                No users found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-if="users.links && users.links.length > 3" class="flex flex-wrap justify-center gap-1 mt-6">
            <template v-for="(link, pIndex) in users.links" :key="pIndex">
                <div v-if="link.url === null"
                    class="mb-1 mr-1 px-3 py-2 text-sm text-muted-foreground border rounded-md" v-html="link.label" />
                <Link v-else :href="link.url"
                    class="mb-1 mr-1 px-3 py-2 text-sm border rounded-md hover:bg-muted focus:border-primary focus:text-primary"
                    :class="{ 'bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground': link.active }"
                    v-html="link.label" />
            </template>
        </div>
    </div>
</template>