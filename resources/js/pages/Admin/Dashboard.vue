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
        <h1 class="text-3xl font-bold tracking-tight text-red-600 mb-6">Platform Overview</h1>
        
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 mb-8">
            <div class="p-6 border rounded-xl bg-card text-card-foreground shadow-sm">
                <div class="flex items-center gap-4">
                    <Building2 class="w-8 h-8 text-muted-foreground" />
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Total Agencies</p>
                        <p class="text-2xl font-bold">{{ stats.totalAgencies }}</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 border rounded-xl bg-card text-card-foreground shadow-sm">
                <div class="flex items-center gap-4">
                    <Users class="w-8 h-8 text-muted-foreground" />
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Total Users</p>
                        <p class="text-2xl font-bold">{{ stats.totalUsers }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="border rounded-xl p-6 bg-card">
            <h2 class="text-lg font-semibold mb-4">Recently Created Agencies</h2>
            <div class="divide-y">
                <div 
                    v-for="agency in stats.recentAgencies" 
                    :key="agency.id" 
                    class="py-3 flex justify-between items-center text-sm"
                >
                    <div>
                        <p class="font-medium">{{ agency.name }}</p>
                        <p class="text-xs text-muted-foreground">Owner: {{ agency.owner?.name || 'N/A' }} ({{ agency.owner?.email }})</p>
                    </div>
                    <span class="text-xs text-muted-foreground">{{ new Date(agency.created_at).toLocaleDateString() }}</span>
                </div>
            </div>
        </div>
    </div>
</template>