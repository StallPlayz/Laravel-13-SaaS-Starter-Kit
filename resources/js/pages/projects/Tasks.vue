<script setup lang="ts">
import { Head, usePage, router } from '@inertiajs/vue3';
import { setLayoutProps } from '@inertiajs/vue3';
import { Plus, CheckCircle2, Circle, Clock, AlertCircle } from '@lucide/vue';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import CreateTaskModal from './CreateTaskModal.vue';
import { index, show, tasks } from '@/routes/projects';

const props = defineProps<{
    workspace: any;
    project: any;
    members: any[];
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

const page = usePage();
const currentUser = computed(() => page.props.auth.user as any);
const currentRole = computed(() => page.props.auth.currentRole as string);

const canUpdateTask = (task: any) => {
    if (currentRole.value === 'owner' || currentRole.value === 'admin') {
        return true;
    }
    const isAssignee = task.assignee_id === currentUser.value.id;
    const isCollaborator = task.collaborators?.some((c: any) => c.id === currentUser.value.id);
    return isAssignee || isCollaborator;
};

const canApproveTask = (task: any) => {
    return currentRole.value === 'client' && task.requires_approval && task.approval_status === 'pending';
};

const updateTaskStatus = (task: any, status: string) => {
    if (task.status === status) return;

    router.patch(`/workspaces/${props.workspace.slug}/projects/${props.project.slug}/tasks/${task.id}`, {
        status,
    }, {
        preserveScroll: true,
    });
};

const submitApproval = (task: any, status: string) => {
    router.patch(`/workspaces/${props.workspace.slug}/projects/${props.project.slug}/tasks/${task.id}/approve`, {
        approval_status: status,
    }, {
        preserveScroll: true,
    });
};

const requestApproval = (task: any) => {
    router.patch(`/workspaces/${props.workspace.slug}/projects/${props.project.slug}/tasks/${task.id}`, {
        requires_approval: true,
        approval_status: 'pending',
    }, {
        preserveScroll: true,
    });
};

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
            <div v-for="task in project.tasks" :key="task.id" class="flex items-center justify-between p-4 rounded-lg border bg-card hover:bg-muted/50 transition-colors">
                <div class="flex items-center gap-4">
                    <DropdownMenu v-if="canUpdateTask(task)">
                        <DropdownMenuTrigger as-child>
                            <button class="focus:outline-none hover:opacity-80 transition-opacity">
                                <component :is="getStatusIcon(task.status)" class="h-5 w-5" :class="getStatusColor(task.status)" />
                            </button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start">
                            <DropdownMenuItem @click="updateTaskStatus(task, 'todo')">
                                <Circle class="mr-2 h-4 w-4 text-muted-foreground" />
                                <span>To Do</span>
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="updateTaskStatus(task, 'in_progress')">
                                <Clock class="mr-2 h-4 w-4 text-blue-500" />
                                <span>In Progress</span>
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="updateTaskStatus(task, 'review')">
                                <AlertCircle class="mr-2 h-4 w-4 text-amber-500" />
                                <span>Review</span>
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="updateTaskStatus(task, 'done')">
                                <CheckCircle2 class="mr-2 h-4 w-4 text-emerald-500" />
                                <span>Done</span>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <component v-else :is="getStatusIcon(task.status)" class="h-5 w-5" :class="getStatusColor(task.status)" />
                    <div>
                        <p class="font-medium">{{ task.title }}</p>
                        <div class="flex items-center gap-2 mt-1 text-xs text-muted-foreground">
                            <span v-if="task.milestone" class="inline-flex items-center rounded bg-secondary px-1.5 py-0.5 font-medium text-secondary-foreground">
                                {{ task.milestone.title }}
                            </span>
                            <span v-if="task.due_date">Due {{ new Date(task.due_date).toLocaleDateString() }}</span>

                            <span v-if="task.requires_approval" class="inline-flex items-center gap-1 ml-2">
                                <span v-if="task.approval_status === 'pending'" class="text-amber-500 font-medium">Pending Approval</span>
                                <span v-else-if="task.approval_status === 'approved'" class="text-emerald-500 font-medium">Approved</span>
                                <span v-else-if="task.approval_status === 'rejected'" class="text-red-500 font-medium">Changes Requested</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div v-if="canApproveTask(task)" class="flex items-center gap-2 mr-4">
                        <Button size="sm" variant="outline" class="text-emerald-600 border-emerald-200 hover:bg-emerald-50" @click="submitApproval(task, 'approved')">Approve</Button>
                        <Button size="sm" variant="outline" class="text-red-600 border-red-200 hover:bg-red-50" @click="submitApproval(task, 'rejected')">Reject</Button>
                    </div>
                    <div v-else-if="(currentRole === 'owner' || currentRole === 'admin') && !task.requires_approval && task.status === 'review'" class="mr-4">
                        <Button size="sm" variant="secondary" @click="requestApproval(task)">Request Approval</Button>
                    </div>

                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                        :class="{
                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': task.priority === 'urgent',
                            'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200': task.priority === 'high',
                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': task.priority === 'medium',
                            'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200': task.priority === 'low',
                        }">
                        {{ task.priority }}
                    </span>
                    <div class="flex -space-x-2 overflow-hidden mr-2" v-if="task.collaborators && task.collaborators.length > 0">
                        <div v-for="collaborator in task.collaborators" :key="collaborator.id" class="inline-block h-8 w-8 rounded-full ring-2 ring-background bg-muted flex items-center justify-center text-xs font-medium" :title="collaborator.name">
                            {{ collaborator.name.charAt(0) }}
                        </div>
                    </div>
                    <div v-if="task.assignee" class="h-8 w-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-xs font-medium ring-2 ring-background z-10" :title="`Assignee: ${task.assignee.name}`">
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
            :members="members"
            @close="showCreateModal = false"
        />
    </div>
</template>
