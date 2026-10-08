<script setup lang="ts">
import {
    LayoutGrid,
    LifeBuoy,
    Headset,
    CheckSquare,
    Inbox,
} from '@lucide/vue';
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

const mainNavItems: NavItem[] = [
    { title: 'Global Dashboard', href: '/dashboard', icon: LayoutGrid },
    { title: 'My Tasks', href: '/dashboard#tasks', icon: CheckSquare },
    { title: 'Pending Approvals', href: '/dashboard#approvals', icon: Inbox },
];

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
