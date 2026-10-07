<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { setLayoutProps } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useReadOnly } from '@/composables/useReadOnly';
import { index, show, settings } from '@/routes/projects';

const props = defineProps<{
    workspace: any;
    project: any;
    users: any[];
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
            title: 'Settings',
            href: settings([props.workspace, props.project]),
        },
    ],
});

const { isReadOnly } = useReadOnly();

const form = useForm({
    name: props.project.name,
    slug: props.project.slug,
    description: props.project.description || '',
    status: props.project.status,
    user_ids: props.project.users ? props.project.users.map((u: any) => u.id) : [],
});

const submit = () => {
    form.put(`/workspaces/${props.workspace.slug}/projects/${props.project.slug}`);
};
</script>

<template>
    <Head :title="`${project.name} - Settings`" />

    <div class="max-w-2xl">
        <form @submit.prevent="submit">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Project Settings"
                    description="Manage your project details."
                />

                <div class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Project Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            :disabled="isReadOnly"
                            type="text"
                            required
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="slug">Project Slug</Label>
                        <Input
                            id="slug"
                            v-model="form.slug"
                            :disabled="isReadOnly"
                            type="text"
                            required
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-2" :message="form.errors.slug" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Input
                            id="description"
                            v-model="form.description"
                            :disabled="isReadOnly"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            v-model="form.status"
                            :disabled="isReadOnly"
                            class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="active">Active</option>
                            <option value="paused">Paused</option>
                            <option value="completed">Completed</option>
                            <option value="archived">Archived</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.status" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="user_ids">Project Members & Clients</Label>
                        <select
                            id="user_ids"
                            v-model="form.user_ids"
                            :disabled="isReadOnly"
                            multiple
                            class="flex min-h-[120px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                        <p class="text-xs text-muted-foreground">Hold Ctrl/Cmd to select multiple users.</p>
                        <InputError class="mt-2" :message="form.errors.user_ids" />
                    </div>
                </div>
            </div>

            <div class="mt-10 flex items-center gap-4 border-t border-border pt-10">
                <Button
                    type="submit"
                    :disabled="isReadOnly || form.processing"
                >
                    Save Changes
                </Button>
            </div>
        </form>
    </div>
</template>
