<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { ChevronsUpDown, Check, Plus, Settings } from '@lucide/vue';
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
                            {{ activeWorkspace?.name?.charAt(0) || 'W' }}
                        </div>
                        <div
                            class="grid flex-1 text-left text-sm leading-tight"
                        >
                            <span class="truncate font-semibold">{{
                                activeWorkspace?.name || 'Select Workspace'
                            }}</span>
                            <span class="truncate text-xs">{{
                                activeWorkspace?.tier === 'pro'
                                    ? 'Pro Plan'
                                    : 'Free Plan'
                            }}</span>
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
                    
                    <DropdownMenuItem as-child>
                        <Link
                            :href="`/workspaces/${activeWorkspace?.id}/settings`"
                            class="flex w-full cursor-pointer items-center gap-2 p-2"
                        >
                            <div class="flex size-6 items-center justify-center rounded-md border bg-background">
                                <Settings class="size-4" />
                            </div>
                            <div class="font-medium text-muted-foreground">
                                Manage workspace
                            </div>
                        </Link>
                    </DropdownMenuItem>

                    <DropdownMenuSeparator />
                    
                    <DropdownMenuItem
                        v-for="workspace in availableWorkspaces"
                        :key="workspace.id"
                        @click="switchWorkspace(workspace.id)"
                        class="gap-2 p-2 cursor-pointer"
                    >
                        <div
                            class="flex size-6 items-center justify-center rounded-sm border"
                        >
                            {{ workspace.name.charAt(0) }}
                        </div>
                        {{ workspace.name }}
                        <Check
                            v-if="workspace.id === activeWorkspace?.id"
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