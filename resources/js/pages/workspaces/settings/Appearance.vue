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
import { appearance } from '@/routes/workspaces/settings';

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
            title: 'Appearance',
            href: appearance(props.workspace),
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
    <Head title="Appearance Settings" />

    <h1 class="sr-only">Appearance Settings</h1>

    <form @submit.prevent="submit">
        <div class="flex flex-col space-y-6">
            <Heading
                variant="small"
                title="Appearance"
                description="Manage your workspace branding."
            />

            <div class="space-y-6">
                <div class="grid gap-2">
                    <Label for="website_url">Website URL</Label>
                    <Input
                        id="website_url"
                        v-model="form.settings.website_url"
                        :disabled="isReadOnly"
                        type="url"
                        placeholder="https://example.com"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2"
                        :message="form.errors['settings.website_url']"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="theme_color">Theme Color</Label>
                    <div class="flex items-center gap-2 mt-1">
                        <input
                            id="theme_color"
                            v-model="form.settings.theme_color"
                            :disabled="isReadOnly"
                            type="color"
                            class="h-10 w-14 cursor-pointer rounded border bg-background p-1"
                        />
                        <Input
                            v-model="form.settings.theme_color"
                            :disabled="isReadOnly"
                            type="text"
                            class="flex-1"
                            placeholder="#ffffff"
                        />
                    </div>
                    <InputError class="mt-2"
                        :message="form.errors['settings.theme_color']"
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
