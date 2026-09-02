<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import OwnerSidebarLayout from '@/layouts/app/OwnerSidebarLayout.vue';
import AdminSidebarLayout from '@/layouts/app/AdminSidebarLayout.vue';
import MemberClientSidebarLayout from '@/layouts/app/MemberClientSidebarLayout.vue';
import PlatformAdminLayout from '@/layouts/app/PlatformAdminLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

const page = usePage();

const LayoutComponent = computed(() => {
    const user = page.props.auth?.user;
    const role = page.props.auth?.currentRole;

    if (user?.platform_role === 'super_admin') {
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
</template>