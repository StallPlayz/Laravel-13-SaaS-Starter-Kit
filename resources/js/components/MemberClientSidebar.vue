<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Folder,
    CheckSquare,
    CreditCard,
    LifeBuoy,
    Headset,
    Inbox,
    CheckCircle,
} from '@lucide/vue';
import { computed } from 'vue';
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
    const workspace = page.props.auth.activeWorkspace;
    const dashboardUrl = workspace
        ? `/workspaces/${workspace.slug}/dashboard`
        : '/dashboard';
    const projectsUrl = workspace
        ? `/workspaces/${workspace.slug}/projects`
        : '/dashboard';
    const invoicesUrl = workspace
        ? `/workspaces/${workspace.slug}/invoices`
        : '/dashboard';
    const requestsUrl = workspace
        ? `/workspaces/${workspace.slug}/service-requests`
        : '/dashboard';
    const approvalsUrl = workspace
        ? `/workspaces/${workspace.slug}/approvals`
        : '/dashboard';

    const items: NavItem[] = [
        { title: 'Dashboard', href: dashboardUrl, icon: LayoutGrid },
    ];

    if (currentRole.value === 'client') {
        items.push({ title: 'Service Requests', href: requestsUrl, icon: Inbox });
        items.push({ title: 'Approvals', href: approvalsUrl, icon: CheckCircle });
    }

    items.push({ title: 'My Projects', href: projectsUrl, icon: Folder });

    if (currentRole.value === 'member') {
        // TODO: Implement a global "My Tasks" view later
        // items.push({ title: 'My Tasks', href: '/tasks', icon: CheckSquare });
    }

    if (currentRole.value === 'client') {
        items.push({ title: 'Invoices', href: invoicesUrl, icon: CreditCard });
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
