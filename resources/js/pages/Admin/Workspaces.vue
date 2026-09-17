<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { index } from '@/routes/admin/workspaces';
import { Search } from '@lucide/vue';

const props = defineProps<{
    workspaces: {
        data: Array<{
            id: number;
            name: string;
            slug: string;
            is_suspended: boolean;
            users_count: number;
            tier: string;
            owner?: { name: string; email: string };
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: { search?: string };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Platform Workspaces',
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

const toggleSuspend = (id: number) => {
    router.patch(
        `/admin/workspaces/${id}/suspend`,
        {},
        { preserveScroll: true },
    );
};

const enterGhost = (id: number) => {
    router.post(`/admin/workspaces/${id}/ghost`);
};

onMounted(() => {
    if (typeof window !== 'undefined' && window.Echo) {
        window.Echo.private('admin.health')
            .listen('.AdminDataUpdated', (e: { type: string }) => {
                if (e.type === 'workspace') {
                    router.reload({ only: ['workspaces'] });
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

    <Head title="Super Admin - Workspaces" />

    <div class="p-8">
        <h1 class="mb-6 text-3xl font-bold tracking-tight text-foreground">
            Platform Workspaces
        </h1>

        <div class="overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b px-6 py-5">
                <div>
                    <h3 class="text-lg font-medium">Active & Suspended Workspaces</h3>
                    <p class="text-sm text-muted-foreground">Manage global platform access and enter Ghost Mode for
                        debugging.</p>
                </div>

                <div class="relative w-full sm:w-72">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <input v-model="searchQuery" type="text" placeholder="Search workspaces or owners..."
                        class="h-9 w-full rounded-md border border-input bg-transparent px-8 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-muted text-xs text-muted-foreground uppercase">
                        <tr>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Owner</th>
                            <th class="px-6 py-3">Users</th>
                            <th class="px-6 py-3">Tier</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="ws in workspaces.data" :key="ws.id" class="hover:bg-muted/50">
                            <td class="px-6 py-4 font-medium">{{ ws.name }}</td>
                            <td class="px-6 py-4 text-muted-foreground">
                                {{ ws.owner?.name || 'N/A' }}
                            </td>
                            <td class="px-6 py-4">{{ ws.users_count }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="ws.tier === 'pro' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200'">
                                    {{ ws.tier.toUpperCase() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="ws.is_suspended
                                        ? 'font-medium text-red-500'
                                        : 'text-muted-foreground'
                                    ">
                                    {{
                                        ws.is_suspended ? 'Suspended' : 'Active'
                                    }}
                                </span>
                            </td>
                            <td class="space-x-2 px-6 py-4 text-right">
                                <button @click="enterGhost(ws.id)"
                                    class="inline-flex items-center justify-center rounded-md bg-primary px-3 py-1.5 text-xs font-medium text-primary-foreground focus:ring-2 focus:ring-primary focus:outline-none disabled:opacity-50">
                                    Ghost Mode
                                </button>
                                <button @click="toggleSuspend(ws.id)"
                                    class="inline-flex items-center justify-center rounded-md border px-3 py-1.5 text-xs font-medium hover:bg-muted focus:ring-2 focus:ring-ring focus:outline-none">
                                    {{
                                        ws.is_suspended
                                            ? 'Unsuspend'
                                            : 'Suspend'
                                    }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="workspaces.data.length === 0">
                            <td colspan="5" class="px-6 py-4 text-center text-muted-foreground">
                                No workspaces found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-if="workspaces.links && workspaces.links.length > 3" class="flex flex-wrap justify-center gap-1 mt-6">
            <template v-for="(link, pIndex) in workspaces.links" :key="pIndex">
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
