<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Briefcase,
    Receipt,
    Users,
    Settings,
    LifeBuoy,
    Headset,
} from '@lucide/vue';
import { computed } from 'vue';
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

const mainNavItems = computed<NavItem[]>(() => {
    const workspace = page.props.auth.activeWorkspace;
    const settingsUrl = workspace
        ? `/workspaces/${workspace.id}/settings`
        : '/dashboard';

    return [
        { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
        { title: 'Projects', href: '/projects', icon: Briefcase },
        { title: 'Invoices', href: '/invoices', icon: Receipt },
        { title: 'Directory', href: '/directory', icon: Users },
        { title: 'Workspace Settings', href: settingsUrl, icon: Settings },
    ];
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
            <NavMain :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
