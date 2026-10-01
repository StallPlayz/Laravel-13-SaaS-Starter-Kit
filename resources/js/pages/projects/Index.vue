<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { setLayoutProps } from '@inertiajs/vue3';
import { Plus, FolderKanban } from '@lucide/vue';
import { index } from '@/routes/projects';

const props = defineProps<{
    workspace: any;
    projects: any[];
}>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Projects',
            href: index(props.workspace),
        },
    ],
});
</script>

<template>
    <Head title="Projects" />

    <div class="p-8 w-full">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Projects</h1>
                <p class="text-sm text-muted-foreground mt-1">Manage your workspace initiatives and repositories.</p>
            </div>
            <Link
                :href="`/workspaces/${workspace.slug}/projects/create`"
                class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
            >
                <Plus class="mr-2 h-4 w-4" />
                New Project
            </Link>
        </div>

        <div v-if="projects.length === 0" class="flex flex-col items-center justify-center rounded-xl border border-dashed p-12 text-center">
            <div class="rounded-full bg-primary/10 p-4 mb-4">
                <FolderKanban class="h-8 w-8 text-primary" />
            </div>
            <h3 class="text-lg font-medium">No projects yet</h3>
            <p class="text-sm text-muted-foreground mt-1 mb-4 max-w-sm">
                Get started by creating your first project to organize your team's work.
            </p>
            <Link
                :href="`/workspaces/${workspace.slug}/projects/create`"
                class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
            >
                Create Project
            </Link>
        </div>

        <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="project in projects"
                :key="project.id"
                :href="`/workspaces/${workspace.slug}/projects/${project.slug}`"
                class="group flex flex-col justify-between rounded-xl border bg-card p-6 text-card-foreground shadow-sm transition-all hover:border-primary/50 hover:shadow-md"
            >
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-lg group-hover:text-primary transition-colors">{{ project.name }}</h3>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="project.status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200'">
                            {{ project.status.toUpperCase() }}
                        </span>
                    </div>
                    <p class="text-sm text-muted-foreground line-clamp-2">
                        {{ project.description || 'No description provided.' }}
                    </p>
                </div>
                <div class="mt-6 flex items-center text-xs text-muted-foreground">
                    <span>Updated {{ new Date(project.updated_at).toLocaleDateString() }}</span>
                </div>
            </Link>
        </div>
    </div>
</template>
