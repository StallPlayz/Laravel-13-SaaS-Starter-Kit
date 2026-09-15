<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AdminSidebar from '@/components/AdminSidebar.vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AdminSidebar />
        <AppContent variant="sidebar">

            <div v-if="$page.props.auth?.isGhostMode"
                class="sticky top-0 z-50 flex w-full items-center justify-center gap-4 bg-red-600 px-4 py-2 text-center text-sm font-medium text-white shadow-md">
                <span>You are in Ghost Mode (Read-Only).</span>
                <Link href="/admin/workspaces/ghost/exit" method="post" as="button"
                    class="rounded bg-white px-3 py-1 text-xs font-bold text-red-600 transition hover:bg-gray-100">
                Exit Ghost Mode
                </Link>
            </div>

            <div v-if="!$page.props.auth?.isGhostMode && $page.props.auth?.activeWorkspace?.is_suspended"
                class="sticky top-0 z-50 flex w-full items-center justify-center border-b border-red-900 bg-red-950 px-4 py-2 text-center text-sm font-medium text-red-200 shadow-md">
                <span>This workspace has been suspended by the platform administrator. Access is read-only.</span>
            </div>

            <div v-if="$page.props.auth?.isImpersonating"
                class="sticky top-0 z-50 flex w-full items-center justify-center gap-4 bg-amber-600 px-4 py-2 text-center text-sm font-medium text-white shadow-md">
                <span>You are currently impersonating a user. All actions are logged.</span>
                <Link href="/admin/impersonation/leave" method="post" as="button"
                    class="rounded bg-white px-3 py-1 text-xs font-bold text-amber-700 transition hover:bg-amber-50">
                Leave Impersonation
                </Link>
            </div>

            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
