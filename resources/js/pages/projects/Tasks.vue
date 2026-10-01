<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { setLayoutProps } from '@inertiajs/vue3';
import { Plus, CheckCircle2, Circle, Clock, AlertCircle } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import CreateTaskModal from './CreateTaskModal.vue';
import { index, show, tasks } from '@/routes/projects';

const props = defineProps<{
    workspace: any;
    project: any;
}>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Projects',
            href: index(props.workspace),
        },
        {
            title: props.project.name,
            href: show([props.workspace, props.project]),
        },
        {
            title: 'Tasks',
            href: tasks([props.workspace, props.project]),
        },
    ],
});

const showCreateModal = ref(false);

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
    <Head :title="`${project.name} - Tasks`" />

    <div class="flex flex-col h-full">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">Tasks</h2>
                <p class="text-sm text-muted-foreground">Manage and track work for this project.</p>
            </div>
            <Button @click="showCreateModal = true">
                <Plus class="mr-2 h-4 w-4" />
                New Task
            </Button>
        </div>

        <div v-if="!project.tasks || project.tasks.length === 0" class="flex flex-col items-center justify-center rounded-xl border border-dashed p-12 text-center flex-1">
            <div class="rounded-full bg-primary/10 p-4 mb-4">
                <CheckCircle2 class="h-8 w-8 text-primary" />
            </div>
            <h3 class="text-lg font-medium">No tasks yet</h3>
            <p class="text-sm text-muted-foreground mt-1 mb-4 max-w-sm">
                Create tasks to break down the work and track progress.
            </p>
            <Button @click="showCreateModal = true">
                Create Task
            </Button>
        </div>

        <div v-else class="grid gap-4">
            <div v-for="task in project.tasks" :key="task.id" class="flex items-center justify-between p-4 rounded-lg border bg-card hover:bg-muted/50 transition-colors cursor-pointer">
                <div class="flex items-center gap-4">
                    <component :is="getStatusIcon(task.status)" class="h-5 w-5" :class="getStatusColor(task.status)" />
                    <div>
                        <p class="font-medium">{{ task.title }}</p>
                        <div class="flex items-center gap-2 mt-1 text-xs text-muted-foreground">
                            <span v-if="task.milestone" class="inline-flex items-center rounded bg-secondary px-1.5 py-0.5 font-medium text-secondary-foreground">
                                {{ task.milestone.title }}
                            </span>
                            <span v-if="task.due_date">Due {{ new Date(task.due_date).toLocaleDateString() }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                        :class="{
                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': task.priority === 'urgent',
                            'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200': task.priority === 'high',
                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': task.priority === 'medium',
                            'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200': task.priority === 'low',
                        }">
                        {{ task.priority }}
                    </span>
                    <div v-if="task.assignee" class="h-8 w-8 rounded-full bg-muted flex items-center justify-center text-xs font-medium" :title="task.assignee.name">
                        {{ task.assignee.name.charAt(0) }}
                    </div>
                    <div v-else class="h-8 w-8 rounded-full border border-dashed flex items-center justify-center text-xs text-muted-foreground" title="Unassigned">
                        ?
                    </div>
                </div>
            </div>
        </div>

        <CreateTaskModal
            :show="showCreateModal"
            :workspace="workspace"
            :project="project"
            @close="showCreateModal = false"
        />
    </div>
</template>
