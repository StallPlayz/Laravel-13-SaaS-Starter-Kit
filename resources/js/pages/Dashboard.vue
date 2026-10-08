<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { Building2, ArrowRight, CheckSquare, Inbox, CreditCard, Clock, AlertCircle, Circle, CheckCircle2 } from '@lucide/vue';
import { computed } from 'vue';
import { dashboard } from '@/routes';

defineProps<{
    myTasks?: any[];
    pendingApprovals?: any[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Global Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const page = usePage();
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

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'done': return CheckCircle2;
        case 'in_progress': return Clock;
        case 'review': return AlertCircle;
        default: return Circle;
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'done': return 'text-emerald-500';
        case 'in_progress': return 'text-blue-500';
        case 'review': return 'text-amber-500';
        default: return 'text-muted-foreground';
    }
};
</script>

<template>
    <Head title="Global Dashboard" />
    <div class="p-8">
        <div v-if="availableWorkspaces.length === 0" class="flex min-h-[60vh] flex-col items-center justify-center text-center">
            <div class="max-w-md space-y-6">
                <div class="flex justify-center">
                    <div class="rounded-full bg-primary/10 p-4">
                        <Building2 class="h-12 w-12 text-primary" />
                    </div>
                </div>
                <h1 class="text-3xl font-bold">Welcome to SyncDesk</h1>
                <p class="text-muted-foreground">
                    You don't belong to any agencies yet. To get started, you can
                    either create your own agency workspace, or wait for an
                    invitation from an existing team.
                </p>
                <div class="pt-4">
                    <Link
                        href="/workspaces/create"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-6 py-3 text-sm font-medium text-background shadow transition-colors hover:bg-primary/90"
                    >
                        Create New Workspace
                    </Link>
                </div>
            </div>
        </div>

        <div v-else class="w-full max-w-6xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Welcome back, {{ page.props.auth.user.name }}</h1>
                <p class="text-sm text-muted-foreground mt-1">Here is an overview of your activity across all your workspaces.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                <!-- Unified Command Center -->
                <div class="md:col-span-2 space-y-8">
                    <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
                        <div class="p-6 border-b flex items-center gap-2">
                            <CheckSquare class="w-5 h-5 text-primary" />
                            <h3 class="font-semibold text-lg">My Tasks</h3>
                        </div>
                        <div class="p-0">
                            <div v-if="!myTasks || myTasks.length === 0" class="p-6 text-center text-sm text-muted-foreground">
                                You don't have any active tasks across your workspaces.
                            </div>
                            <div v-else class="divide-y">
                                <div v-for="task in myTasks" :key="task.id" class="p-4 flex items-center justify-between hover:bg-muted/50 transition-colors">
                                    <div class="flex items-center gap-4">
                                        <component :is="getStatusIcon(task.status)" class="h-5 w-5" :class="getStatusColor(task.status)" />
                                        <div>
                                            <p class="font-medium">{{ task.title }}</p>
                                            <div class="flex items-center gap-2 mt-1 text-xs text-muted-foreground">
                                                <span class="font-medium text-foreground">{{ task.project?.workspace?.name }}</span>
                                                <span>&bull;</span>
                                                <span>{{ task.project?.name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <Link :href="`/workspaces/${task.project?.workspace?.slug}/projects/${task.project?.slug}/tasks`" class="text-sm text-primary hover:underline">
                                        View Task
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
                        <div class="p-6 border-b flex items-center gap-2">
                            <Inbox class="w-5 h-5 text-amber-500" />
                            <h3 class="font-semibold text-lg">Pending Approvals</h3>
                        </div>
                        <div class="p-0">
                            <div v-if="!pendingApprovals || pendingApprovals.length === 0" class="p-6 text-center text-sm text-muted-foreground">
                                No tasks require your approval right now.
                            </div>
                            <div v-else class="divide-y">
                                <div v-for="task in pendingApprovals" :key="task.id" class="p-4 flex items-center justify-between hover:bg-muted/50 transition-colors">
                                    <div>
                                        <p class="font-medium">{{ task.title }}</p>
                                        <div class="flex items-center gap-2 mt-1 text-xs text-muted-foreground">
                                            <span class="font-medium text-foreground">{{ task.project?.workspace?.name }}</span>
                                            <span>&bull;</span>
                                            <span>{{ task.project?.name }}</span>
                                        </div>
                                    </div>
                                    <Link :href="`/workspaces/${task.project?.workspace?.slug}/projects/${task.project?.slug}/tasks`" class="text-sm text-primary hover:underline">
                                        Review
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Workspace Grid -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-lg">Your Workspaces</h3>
                        <Link href="/workspaces/create" class="text-sm text-primary hover:underline">
                            Create New
                        </Link>
                    </div>

                    <div class="grid gap-4">
                        <button
                            v-for="workspace in availableWorkspaces"
                            :key="workspace.id"
                            @click="switchWorkspace(workspace.id)"
                            class="flex items-center justify-between p-4 rounded-xl border bg-card hover:border-primary/50 hover:shadow-md transition-all text-left group"
                        >
                            <div class="flex items-center gap-4">
                                <div class="flex aspect-square size-10 items-center justify-center rounded-lg bg-primary/10 text-primary font-bold">
                                    {{ workspace.name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="font-semibold group-hover:text-primary transition-colors">{{ workspace.name }}</p>
                                    <p class="text-xs text-muted-foreground capitalize">{{ (workspace as any).pivot?.role || 'Member' }}</p>
                                </div>
                            </div>
                            <ArrowRight class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
