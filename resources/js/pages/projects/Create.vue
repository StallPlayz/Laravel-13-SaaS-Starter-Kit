<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { setLayoutProps } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useReadOnly } from '@/composables/useReadOnly';
import { index, create } from '@/routes/projects';

const props = defineProps<{
    workspace: any;
    clients: any[];
}>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Projects',
            href: index(props.workspace),
        },
        {
            title: 'Create Project',
            href: create(props.workspace),
        },
    ],
});

const { isReadOnly } = useReadOnly();

const form = useForm({
    name: '',
    slug: '',
    description: '',
    client_id: '',
});

const submit = () => {
    form.post(`/workspaces/${props.workspace.slug}/projects`);
};
</script>

<template>
    <Head title="Create Project" />

    <div class="max-w-2xl mx-auto py-10">
        <form @submit.prevent="submit">
            <div class="flex flex-col space-y-6">
                <Heading
                    title="Create a new Project"
                    description="Projects are where your team collaborates on specific initiatives."
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
                            autofocus
                            class="mt-1 block w-full"
                            placeholder="e.g. Marketing Website"
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
                            placeholder="e.g. marketing-website"
                        />
                        <InputError class="mt-2" :message="form.errors.slug" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description (Optional)</Label>
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
                        <Label for="client_id">Assign Client (Optional)</Label>
                        <select
                            id="client_id"
                            v-model="form.client_id"
                            :disabled="isReadOnly"
                            class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="">No Client</option>
                            <option v-for="client in clients" :key="client.id" :value="client.id">
                                {{ client.name }} ({{ client.email }})
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.client_id" />
                    </div>
                </div>
            </div>

            <div class="mt-10 flex items-center gap-4 border-t border-border pt-10">
                <Button
                    type="submit"
                    :disabled="isReadOnly || form.processing"
                >
                    Create Project
                </Button>
            </div>
        </form>
    </div>
</template>
