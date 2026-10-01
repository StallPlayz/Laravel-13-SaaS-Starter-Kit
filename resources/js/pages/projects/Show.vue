<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { setLayoutProps } from '@inertiajs/vue3';
import { Activity, Users, Zap, CheckCircle2, Clock, AlertCircle, Circle } from '@lucide/vue';
import { computed } from 'vue';
import { index, show } from '@/routes/projects';

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
    ],
});

const taskStats = computed(() => {
    const tasks = props.project.tasks || [];
    const total = tasks.length;
    if (total === 0) return { total: 0, done: 0, progress: 0 };
    
    const done = tasks.filter((t: any) => t.status === 'done').length;
    return {
        total,
        done,
        progress: Math.round((done / total) * 100)
    };
});

const nextMilestone = computed(() => {
    const milestones = props.project.milestones || [];
    const pending = milestones.filter((m: any) => m.status === 'pending' && m.due_date);
    if (pending.length === 0) return null;
    
    return pending.sort((a: any, b: any) => new Date(a.due_date).getTime() - new Date(b.due_date).getTime())[0];
});
</script>

<template>
    <Head :title="`${project.name} - Overview`" />

    <div class="flex flex-col space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">Overview</h2>
                <p class="text-sm text-muted-foreground">Project pulse and recent activity.</p>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mb-6">
        <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-muted-foreground">Project Progress</h3>
                <Activity class="w-4 h-4 text-muted-foreground" />
            </div>
            <div class="text-2xl font-bold">{{ taskStats.progress }}%</div>
            <p class="text-xs text-muted-foreground mt-1">
                {{ taskStats.done }} of {{ taskStats.total }} tasks completed
            </p>
            <div class="mt-4 h-2 w-full bg-secondary rounded-full overflow-hidden">
                <div class="h-full bg-primary transition-all" :style="{ width: `${taskStats.progress}%` }"></div>
            </div>
        </div>

        <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-muted-foreground">Next Milestone</h3>
                <Zap class="w-4 h-4 text-muted-foreground" />
            </div>
            <template v-if="nextMilestone">
                <div class="text-lg font-bold truncate" :title="nextMilestone.title">{{ nextMilestone.title }}</div>
                <p class="text-xs text-muted-foreground mt-1">
                    Due {{ new Date(nextMilestone.due_date).toLocaleDateString() }}
                </p>
            </template>
            <template v-else>
                <div class="text-lg font-bold text-muted-foreground">No upcoming milestones</div>
            </template>
        </div>

        <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-muted-foreground">Team</h3>
                <Users class="w-4 h-4 text-muted-foreground" />
            </div>
            <div class="flex -space-x-2 overflow-hidden">
                <!-- Placeholder for team members -->
                <div class="inline-block h-8 w-8 rounded-full ring-2 ring-background bg-muted flex items-center justify-center text-xs font-medium">
                    You
                </div>
            </div>
            <p class="text-xs text-muted-foreground mt-2">
                Workspace members have access
            </p>
        </div>
    </div>
    
    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
            <div class="p-6 border-b">
                <h3 class="font-semibold">Recent Tasks</h3>
            </div>
            <div class="p-0">
                <div v-if="!project.tasks || project.tasks.length === 0" class="p-6 text-center text-sm text-muted-foreground">
                    No tasks created yet.
                </div>
                <div v-else class="divide-y">
                    <div v-for="task in project.tasks.slice(0, 5)" :key="task.id" class="p-4 flex items-center justify-between hover:bg-muted/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <CheckCircle2 v-if="task.status === 'done'" class="w-4 h-4 text-emerald-500" />
                            <Clock v-else-if="task.status === 'in_progress'" class="w-4 h-4 text-blue-500" />
                            <AlertCircle v-else-if="task.status === 'review'" class="w-4 h-4 text-amber-500" />
                            <Circle v-else class="w-4 h-4 text-muted-foreground" />
                            <span class="text-sm font-medium" :class="{ 'line-through text-muted-foreground': task.status === 'done' }">{{ task.title }}</span>
                        </div>
                        <span class="text-xs text-muted-foreground" v-if="task.due_date">
                            {{ new Date(task.due_date).toLocaleDateString() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
            <div class="p-6 border-b">
                <h3 class="font-semibold">Activity Feed</h3>
            </div>
            <div class="p-6 text-center text-sm text-muted-foreground">
                Activity tracking will be implemented here.
            </div>
        </div>
    </div>
    </div>
</template>
