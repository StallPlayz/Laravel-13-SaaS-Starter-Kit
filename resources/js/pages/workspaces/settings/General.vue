<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { setLayoutProps } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useReadOnly } from '@/composables/useReadOnly';
import type { Workspace } from '@/types';
import { settings } from '@/routes/workspaces';

const props = defineProps<{
    workspace: Workspace;
}>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Workspace Settings',
            href: settings(props.workspace),
        },
        {
            title: 'General',
            href: settings(props.workspace),
        },
    ],
});

const { isReadOnly } = useReadOnly();

const form = useForm({
    name: props.workspace.name,
    slug: props.workspace.slug,
    settings: {
        description: props.workspace.settings?.description || '',
        theme_color: props.workspace.settings?.theme_color || '#ffffff',
        website_url: props.workspace.settings?.website_url || '',
    },
});

const submit = () => {
    form.put(`/workspaces/${props.workspace.slug}`);
};
</script>

<template>
    <Head title="General Settings" />

    <h1 class="sr-only">General Settings</h1>

    <form @submit.prevent="submit">
        <div class="flex flex-col space-y-6">
            <Heading
                variant="small"
                title="General Settings"
                description="Manage your workspace details."
            />

            <div class="space-y-6">
                <div class="grid gap-2">
                    <Label for="name">Workspace Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        :disabled="isReadOnly"
                        type="text"
                        required
                        autofocus
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="slug">Workspace URL</Label>
                    <Input
                        id="slug"
                        v-model="form.slug"
                        :disabled="isReadOnly"
                        type="text"
                        required
                        class="mt-1 block w-full"
                    />
                    <p class="text-xs text-muted-foreground">
                        This is your public URL: /{{ form.slug }}
                    </p>
                    <InputError class="mt-2" :message="form.errors.slug" />
                </div>

                <div class="grid gap-2">
                    <Label for="description">Public Description</Label>
                    <Input
                        id="description"
                        v-model="form.settings.description"
                        :disabled="isReadOnly"
                        type="text"
                        placeholder="We build awesome things."
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2"
                        :message="form.errors['settings.description']"
                    />
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
</template>
