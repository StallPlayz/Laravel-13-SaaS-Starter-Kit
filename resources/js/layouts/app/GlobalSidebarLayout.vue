<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import GlobalSidebar from '@/components/GlobalSidebar.vue';
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
        <GlobalSidebar />
        <AppContent variant="sidebar">

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
