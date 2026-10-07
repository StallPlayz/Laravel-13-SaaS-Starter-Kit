<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import type { NavItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const workspace = computed(() => page.props.workspace as any);
const project = computed(() => page.props.project as any);
const currentRole = computed(() => page.props.auth.currentRole as string);
const canManage = computed(() => currentRole.value === 'owner' || currentRole.value === 'admin');

const navItems = computed<NavItem[]>(() => {
    if (!workspace.value || !project.value) return [];
    
    const items: NavItem[] = [
        {
            title: 'Overview',
            href: `/workspaces/${workspace.value.slug}/projects/${project.value.slug}`,
        },
        {
            title: 'Tasks',
            href: `/workspaces/${workspace.value.slug}/projects/${project.value.slug}/tasks`,
        },
    ];

    if (canManage.value) {
        items.push({
            title: 'Settings',
            href: `/workspaces/${workspace.value.slug}/projects/${project.value.slug}/settings`,
        });
    }

    return items;
});

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <div class="flex flex-col h-full">
        <header class="border-b bg-background px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-bold">{{ project?.name }}</h1>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="project?.status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200'">
                    {{ project?.status.toUpperCase() }}
                </span>
            </div>
            
            <div class="flex items-center gap-2">
                <!-- Real-time presence avatars will go here -->
                <div class="flex -space-x-2 overflow-hidden">
                    <div class="inline-block h-8 w-8 rounded-full ring-2 ring-background bg-muted flex items-center justify-center text-xs font-medium">
                        You
                    </div>
                </div>
            </div>
        </header>

        <div class="border-b bg-muted/30 px-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <Link
                    v-for="item in navItems"
                    :key="toUrl(item.href)"
                    :href="item.href"
                    :class="[
                        isCurrentUrl(item.href)
                            ? 'border-primary text-primary'
                            : 'border-transparent text-muted-foreground hover:border-muted-foreground hover:text-foreground',
                        'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors'
                    ]"
                >
                    {{ item.title }}
                </Link>
            </nav>
        </div>

        <main class="flex-1 overflow-auto p-6">
            <slot />
        </main>
    </div>
</template>
