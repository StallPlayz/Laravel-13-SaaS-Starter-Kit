<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Building2, Users } from '@lucide/vue';
import { dashboard } from '@/routes/admin';

defineProps<{
    stats: {
        totalAgencies: number;
        totalUsers: number;
        recentAgencies: Array<{
            id: number;
            name: string;
            created_at: string;
            owner?: { name: string; email: string };
        }>;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Admin Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Super Admin Dashboard" />
    <div class="p-8">
        <h1 class="mb-6 text-3xl font-bold tracking-tight text-red-600">
            Platform Overview
        </h1>

        <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div
                class="rounded-xl border bg-card p-6 text-card-foreground shadow-sm"
            >
                <div class="flex items-center gap-4">
                    <Building2 class="h-8 w-8 text-muted-foreground" />
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">
                            Total Agencies
                        </p>
                        <p class="text-2xl font-bold">
                            {{ stats.totalAgencies }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border bg-card p-6 text-card-foreground shadow-sm"
            >
                <div class="flex items-center gap-4">
                    <Users class="h-8 w-8 text-muted-foreground" />
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">
                            Total Users
                        </p>
                        <p class="text-2xl font-bold">{{ stats.totalUsers }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border bg-card p-6">
            <h2 class="mb-4 text-lg font-semibold">
                Recently Created Agencies
            </h2>
            <div class="divide-y">
                <div
                    v-for="agency in stats.recentAgencies"
                    :key="agency.id"
                    class="flex items-center justify-between py-3 text-sm"
                >
                    <div>
                        <p class="font-medium">{{ agency.name }}</p>
                        <p class="text-xs text-muted-foreground">
                            Owner: {{ agency.owner?.name || 'N/A' }} ({{
                                agency.owner?.email
                            }})
                        </p>
                    </div>
                    <span class="text-xs text-muted-foreground">{{
                        new Date(agency.created_at).toLocaleDateString()
                    }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
