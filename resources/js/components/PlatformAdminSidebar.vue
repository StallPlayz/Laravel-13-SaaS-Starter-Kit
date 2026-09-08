<script setup lang="ts">
import { Activity, Building2, Users, ShieldAlert, Database } from '@lucide/vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    { title: 'Platform Health', href: '/admin', icon: Activity },
    { title: 'All Workspaces', href: '/admin/workspaces', icon: Building2 },
    { title: 'Global Users', href: '/admin/users', icon: Users },
    { title: 'System Logs', href: '/admin/logs', icon: ShieldAlert },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="border-red-900/20">
        <SidebarHeader class="border-b p-4">
            <div class="flex items-center gap-2 font-bold text-red-600">
                <Database class="h-5 w-5" />
                <span>SyncDesk Admin</span>
            </div>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
