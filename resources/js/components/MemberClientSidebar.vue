<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { LayoutGrid, Folder, CheckSquare, CreditCard, LifeBuoy, Headset } from '@lucide/vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import WorkspaceSwitcher from '@/components/WorkspaceSwitcher.vue';
import type { NavItem } from '@/types';

const page = usePage();
const currentRole = computed(() => page.props.auth.currentRole);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
        { title: 'My Projects', href: '/projects', icon: Folder },
    ];

    if (currentRole.value === 'member') {
        items.push({ title: 'My Tasks', href: '/tasks', icon: CheckSquare });
    }

    if (currentRole.value === 'client') {
        items.push({ title: 'Invoices', href: '/invoices', icon: CreditCard });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    { title: 'Help Center', href: '/help', icon: LifeBuoy },
    { title: 'Contact Support', href: '/support', icon: Headset },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <WorkspaceSwitcher />
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>