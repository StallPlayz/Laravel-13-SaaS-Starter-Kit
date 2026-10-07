<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { setLayoutProps } from '@inertiajs/vue3';
import { Building2, Briefcase, Receipt, Inbox, CheckSquare } from '@lucide/vue';

const props = defineProps<{
    workspace: any;
    role: string;
    stats?: {
        active_projects: number;
        pending_requests: number;
        outstanding_invoices: number;
    };
    recent_requests?: any[];
    my_tasks?: any[];
    active_projects?: any[];
    pending_approvals?: any[];
}>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Workspace Dashboard',
            href: `/workspaces/${props.workspace.slug}/dashboard`,
        },
    ],
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <Head :title="`${workspace.name} Dashboard`" />

    <div class="p-8 w-full max-w-6xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ workspace.name }}</h1>
            <p class="text-sm text-muted-foreground mt-1">
                <span class="capitalize">{{ role }}</span> Dashboard
            </p>
        </div>

        <!-- Owner / Admin View -->
        <template v-if="role === 'owner' || role === 'admin'">
            <div class="grid gap-6 md:grid-cols-3 mb-8">
                <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-muted-foreground">Active Projects</h3>
                        <Briefcase class="w-4 h-4 text-muted-foreground" />
                    </div>
                    <div class="text-2xl font-bold">{{ stats?.active_projects || 0 }}</div>
                </div>
                <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-muted-foreground">Pending Requests</h3>
                        <Inbox class="w-4 h-4 text-amber-500" />
                    </div>
                    <div class="text-2xl font-bold">{{ stats?.pending_requests || 0 }}</div>
                </div>
                <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-muted-foreground">Outstanding Invoices</h3>
                        <Receipt class="w-4 h-4 text-emerald-500" />
                    </div>
                    <div class="text-2xl font-bold">{{ formatCurrency(stats?.outstanding_invoices || 0) }}</div>
                </div>
            </div>

            <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
                <div class="p-6 border-b flex items-center justify-between">
                    <h3 class="font-semibold text-lg">Recent Service Requests</h3>
                    <Link :href="`/workspaces/${workspace.slug}/service-requests`" class="text-sm text-primary hover:underline">
                        View All
                    </Link>
                </div>
                <div class="p-0">
                    <div v-if="!recent_requests || recent_requests.length === 0" class="p-6 text-center text-sm text-muted-foreground">
                        No pending service requests.
                    </div>
                    <div v-else class="divide-y">
                        <div v-for="request in recent_requests" :key="request.id" class="p-4 flex items-center justify-between hover:bg-muted/50 transition-colors">
                            <div>
                                <p class="font-medium">{{ request.title }}</p>
                                <p class="text-xs text-muted-foreground mt-1">From {{ request.client?.name }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                PENDING
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Member View -->
        <template v-else-if="role === 'member'">
            <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
                <div class="p-6 border-b flex items-center gap-2">
                    <CheckSquare class="w-5 h-5 text-primary" />
                    <h3 class="font-semibold text-lg">My Active Tasks</h3>
                </div>
                <div class="p-0">
                    <div v-if="!my_tasks || my_tasks.length === 0" class="p-6 text-center text-sm text-muted-foreground">
                        You don't have any active tasks in this workspace.
                    </div>
                    <div v-else class="divide-y">
                        <div v-for="task in my_tasks" :key="task.id" class="p-4 flex items-center justify-between hover:bg-muted/50 transition-colors">
                            <div>
                                <p class="font-medium">{{ task.title }}</p>
                                <p class="text-xs text-muted-foreground mt-1">Project: {{ task.project?.name }}</p>
                            </div>
                            <Link :href="`/workspaces/${workspace.slug}/projects/${task.project?.slug}/tasks`" class="text-sm text-primary hover:underline">
                                View Task
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Client View -->
        <template v-else-if="role === 'client'">
            <div class="grid gap-6 md:grid-cols-2 mb-8">
                <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
                    <div class="p-6 border-b flex items-center justify-between">
                        <h3 class="font-semibold text-lg">My Projects</h3>
                        <Link :href="`/workspaces/${workspace.slug}/projects`" class="text-sm text-primary hover:underline">
                            View All
                        </Link>
                    </div>
                    <div class="p-0">
                        <div v-if="!active_projects || active_projects.length === 0" class="p-6 text-center text-sm text-muted-foreground">
                            No active projects.
                        </div>
                        <div v-else class="divide-y">
                            <div v-for="project in active_projects" :key="project.id" class="p-4 hover:bg-muted/50 transition-colors">
                                <div class="flex items-center justify-between mb-2">
                                    <Link :href="`/workspaces/${workspace.slug}/projects/${project.slug}`" class="font-medium hover:underline">
                                        {{ project.name }}
                                    </Link>
                                    <span class="text-xs text-muted-foreground">{{ project.completed_tasks_count }} / {{ project.total_tasks_count }} Tasks</span>
                                </div>
                                <div class="h-2 w-full bg-secondary rounded-full overflow-hidden">
                                    <div class="h-full bg-primary transition-all" :style="{ width: `${project.total_tasks_count > 0 ? (project.completed_tasks_count / project.total_tasks_count) * 100 : 0}%` }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
                    <div class="p-6 border-b flex items-center gap-2">
                        <CheckSquare class="w-5 h-5 text-amber-500" />
                        <h3 class="font-semibold text-lg">Pending Approvals</h3>
                    </div>
                    <div class="p-0">
                        <div v-if="!pending_approvals || pending_approvals.length === 0" class="p-6 text-center text-sm text-muted-foreground">
                            No tasks require your approval right now.
                        </div>
                        <div v-else class="divide-y">
                            <div v-for="task in pending_approvals" :key="task.id" class="p-4 flex items-center justify-between hover:bg-muted/50 transition-colors">
                                <div>
                                    <p class="font-medium">{{ task.title }}</p>
                                    <p class="text-xs text-muted-foreground mt-1">Project: {{ task.project?.name }}</p>
                                </div>
                                <Link :href="`/workspaces/${workspace.slug}/projects/${task.project?.slug}/tasks`" class="text-sm text-primary hover:underline">
                                    Review
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
