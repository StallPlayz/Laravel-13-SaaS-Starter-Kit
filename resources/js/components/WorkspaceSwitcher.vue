<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { ChevronsUpDown, Check, Plus, Settings, Home } from '@lucide/vue';
import { computed } from 'vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

const page = usePage();
const activeWorkspace = computed(() => page.props.auth.activeWorkspace);
const availableWorkspaces = computed(
    () => page.props.auth.availableWorkspaces || [],
);
const isGlobalDashboard = computed(() => 
    page.url.startsWith('/dashboard') || 
    page.url.startsWith('/settings') || 
    page.url.startsWith('/my-tasks') || 
    page.url.startsWith('/notifications')
);

const switchWorkspace = (workspaceId: number) => {
    router.post(
        '/workspaces/switch',
        { workspace_id: workspaceId },
        {
            preserveScroll: true,
            preserveState: false,
        },
    );
};
</script>

<template>
    <SidebarMenu v-if="availableWorkspaces.length > 0">
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        size="lg"
                        class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                    >
                        <div
                            class="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground"
                        >
                            <Home v-if="isGlobalDashboard" class="size-4" />
                            <span v-else>{{ activeWorkspace?.name?.charAt(0) || 'W' }}</span>
                        </div>
                        <div
                            class="grid flex-1 text-left text-sm leading-tight"
                        >
                            <span class="truncate font-semibold">{{
                                isGlobalDashboard ? 'Global Dashboard' : (activeWorkspace?.name || 'Select Workspace')
                            }}</span>
                            <span class="truncate text-xs" v-if="!isGlobalDashboard">{{
                                activeWorkspace?.tier === 'pro'
                                    ? 'Pro Plan'
                                    : 'Free Plan'
                            }}</span>
                            <span class="truncate text-xs" v-else>Personal Account</span>
                        </div>
                        <ChevronsUpDown class="ml-auto size-4" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg"
                    align="start"
                    side="bottom"
                    :side-offset="4"
                >
                    <DropdownMenuLabel class="text-xs text-muted-foreground">
                        Workspaces
                    </DropdownMenuLabel>

                    <DropdownMenuItem as-child v-if="!isGlobalDashboard">
                        <Link
                            href="/dashboard"
                            class="flex w-full cursor-pointer items-center gap-2 p-2"
                        >
                            <div
                                class="flex size-6 items-center justify-center rounded-md border bg-background"
                            >
                                <Home class="size-4" />
                            </div>
                            <div class="font-medium text-muted-foreground">
                                Global Dashboard
                            </div>
                        </Link>
                    </DropdownMenuItem>

                    <DropdownMenuItem as-child v-if="!isGlobalDashboard && activeWorkspace">
                        <Link
                            :href="`/workspaces/${activeWorkspace.slug}/settings`"
                            class="flex w-full cursor-pointer items-center gap-2 p-2"
                        >
                            <div
                                class="flex size-6 items-center justify-center rounded-md border bg-background"
                            >
                                <Settings class="size-4" />
                            </div>
                            <div class="font-medium text-muted-foreground">
                                Manage workspace
                            </div>
                        </Link>
                    </DropdownMenuItem>

                    <DropdownMenuSeparator v-if="!isGlobalDashboard" />

                    <DropdownMenuItem
                        v-for="workspace in availableWorkspaces"
                        :key="workspace.id"
                        @click="switchWorkspace(workspace.id)"
                        class="cursor-pointer gap-2 p-2"
                    >
                        <div
                            class="flex size-6 items-center justify-center rounded-sm border"
                        >
                            {{ workspace.name.charAt(0) }}
                        </div>
                        {{ workspace.name }}
                        <Check
                            v-if="!isGlobalDashboard && workspace.id === activeWorkspace?.id"
                            class="ml-auto size-4"
                        />
                    </DropdownMenuItem>

                    <DropdownMenuSeparator />

                    <DropdownMenuItem as-child>
                        <Link
                            href="/workspaces/create"
                            class="flex w-full cursor-pointer items-center gap-2 p-2"
                        >
                            <div
                                class="flex size-6 items-center justify-center rounded-md border bg-background"
                            >
                                <Plus class="size-4" />
                            </div>
                            <div class="font-medium text-muted-foreground">
                                Add workspace
                            </div>
                        </Link>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
