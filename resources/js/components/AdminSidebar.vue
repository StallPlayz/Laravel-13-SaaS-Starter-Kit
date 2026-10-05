<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Briefcase,
    Receipt,
    Users,
    LifeBuoy,
    Headset,
    Inbox,
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
    const projectsUrl = workspace
        ? `/workspaces/${workspace.slug}/projects`
        : '/dashboard';
    const invoicesUrl = workspace
        ? `/workspaces/${workspace.slug}/invoices`
        : '/dashboard';
    const requestsUrl = workspace
        ? `/workspaces/${workspace.slug}/service-requests`
        : '/dashboard';

    return [
        { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
        { title: 'Service Requests', href: requestsUrl, icon: Inbox },
        { title: 'Projects', href: projectsUrl, icon: Briefcase },
        { title: 'Invoices', href: invoicesUrl, icon: Receipt },
        { title: 'Team & Clients', href: '/directory', icon: Users },
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
