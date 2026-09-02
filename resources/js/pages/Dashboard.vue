<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Building2 } from '@lucide/vue';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const page = usePage();
const availableWorkspaces = computed(() => page.props.auth.availableWorkspaces || []);
</script>

<template>
    <Head title="Dashboard" />
    <div class="p-8 flex flex-col items-center justify-center min-h-[60vh] text-center">
        
        <div v-if="availableWorkspaces.length === 0" class="max-w-md space-y-6">
            <div class="flex justify-center">
                <div class="p-4 rounded-full bg-primary/10">
                    <Building2 class="w-12 h-12 text-primary" />
                </div>
            </div>
            <h1 class="text-3xl font-bold">Welcome to SyncDesk</h1>
            <p class="text-muted-foreground">
                You don't belong to any agencies yet. To get started, you can either create your own agency workspace, or wait for an invitation from an existing team.
            </p>
            <div class="pt-4">
                <Link 
                    href="/workspaces/create" 
                    class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-white transition-colors rounded-md shadow bg-primary hover:bg-primary/90"
                >
                    Create New Workspace
                </Link>
            </div>
        </div>

        <div v-else class="w-full text-left">
            <h1 class="text-2xl font-bold mb-4">Workspace Dashboard</h1>
            <p>Welcome back! You are viewing data for your active agency.</p>
        </div>

    </div>
</template>
