<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Toaster } from '@/components/ui/sonner';
import AdminSidebarLayout from '@/layouts/app/AdminSidebarLayout.vue';
import MemberClientSidebarLayout from '@/layouts/app/MemberClientSidebarLayout.vue';
import OwnerSidebarLayout from '@/layouts/app/OwnerSidebarLayout.vue';
import PlatformAdminLayout from '@/layouts/app/PlatformAdminLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

const page = usePage();

watch(
    () => page.props.flash as { success?: string; error?: string } | undefined,
    (flash) => {
        if (flash?.success) {
            toast.success(flash.success);
        }

        if (flash?.error) {
            toast.error(flash.error);
        }
    },
    { deep: true, immediate: true },
);

const LayoutComponent = computed(() => {
    const user = page.props.auth?.user;
    const role = page.props.auth?.currentRole;

    if (user?.platform_role === 'super_admin' && !role) {
        return PlatformAdminLayout;
    }

    switch (role) {
        case 'owner':
            return OwnerSidebarLayout;
        case 'admin':
            return AdminSidebarLayout;
        case 'member':
        case 'client':
            return MemberClientSidebarLayout;
        default:
            return MemberClientSidebarLayout;
    }
});
</script>

<template>
    <component :is="LayoutComponent" :breadcrumbs="breadcrumbs">
        <slot />
    </component>

    <Toaster position="bottom-right" rich-colors />
</template>
