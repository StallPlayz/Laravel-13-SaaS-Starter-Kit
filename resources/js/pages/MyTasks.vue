<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { setLayoutProps } from '@inertiajs/vue3';
import { CheckSquare, Clock, AlertCircle, CheckCircle2, Circle } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    tasks: any[];
}>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Global Dashboard',
            href: '/dashboard',
        },
        {
            title: 'My Tasks',
            href: '/my-tasks',
        },
    ],
});

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

const activeTasks = computed(() => props.tasks.filter(t => t.status !== 'done'));
const completedTasks = computed(() => props.tasks.filter(t => t.status === 'done'));
</script>

<template>
    <Head title="My Tasks" />

    <div class="p-8 w-full max-w-6xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-foreground">My Tasks</h1>
            <p class="text-sm text-muted-foreground mt-1">All tasks assigned to you across all workspaces.</p>
        </div>

        <div class="space-y-8">
            <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
                <div class="p-6 border-b flex items-center gap-2">
                    <CheckSquare class="w-5 h-5 text-primary" />
                    <h3 class="font-semibold text-lg">Active Tasks ({{ activeTasks.length }})</h3>
                </div>
                <div class="p-0">
                    <div v-if="activeTasks.length === 0" class="p-12 text-center text-sm text-muted-foreground">
                        You don't have any active tasks. Great job!
                    </div>
                    <div v-else class="divide-y">
                        <div v-for="task in activeTasks" :key="task.id" class="p-4 flex items-center justify-between hover:bg-muted/50 transition-colors">
                            <div class="flex items-center gap-4">
                                <component :is="getStatusIcon(task.status)" class="h-5 w-5" :class="getStatusColor(task.status)" />
                                <div>
                                    <p class="font-medium">{{ task.title }}</p>
                                    <div class="flex items-center gap-2 mt-1 text-xs text-muted-foreground">
                                        <span class="font-medium text-foreground">{{ task.project?.workspace?.name }}</span>
                                        <span>&bull;</span>
                                        <span>{{ task.project?.name }}</span>
                                        <span v-if="task.due_date">&bull; Due {{ new Date(task.due_date).toLocaleDateString() }}</span>
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

            <div class="rounded-xl border bg-card text-card-foreground shadow-sm opacity-75">
                <div class="p-6 border-b flex items-center gap-2">
                    <CheckCircle2 class="w-5 h-5 text-emerald-500" />
                    <h3 class="font-semibold text-lg">Completed Tasks ({{ completedTasks.length }})</h3>
                </div>
                <div class="p-0">
                    <div v-if="completedTasks.length === 0" class="p-6 text-center text-sm text-muted-foreground">
                        No completed tasks yet.
                    </div>
                    <div v-else class="divide-y">
                        <div v-for="task in completedTasks" :key="task.id" class="p-4 flex items-center justify-between hover:bg-muted/50 transition-colors">
                            <div class="flex items-center gap-4">
                                <CheckCircle2 class="h-5 w-5 text-emerald-500" />
                                <div>
                                    <p class="font-medium line-through text-muted-foreground">{{ task.title }}</p>
                                    <div class="flex items-center gap-2 mt-1 text-xs text-muted-foreground">
                                        <span class="font-medium">{{ task.project?.workspace?.name }}</span>
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
        </div>
    </div>
</template>
