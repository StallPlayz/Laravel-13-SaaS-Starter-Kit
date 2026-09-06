<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Workspace } from '@/types';

const props = defineProps<{
    workspace: Workspace;
}>();

const form = useForm({
    name: props.workspace.name,
    slug: props.workspace.slug,
    settings: {
        description: props.workspace.settings?.description || '',
        theme_color: props.workspace.settings?.theme_color || '#ffffff',
        website_url: props.workspace.settings?.website_url || '',
    }
});

const submit = () => {
    form.put(`/workspaces/${props.workspace.id}`);
};
</script>

<template>
        <Head title="Workspace Settings" />

        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mx-auto w-full max-w-md">
                <Card>
                    <CardHeader>
                        <CardTitle>Workspace Settings</CardTitle>
                        <CardDescription>
                            Manage your workspace details.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="grid gap-4">
                            <div class="grid gap-2">
                                <Label for="name">Workspace Name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    autofocus
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="slug">Workspace URL</Label>
                                <Input
                                    id="slug"
                                    v-model="form.slug"
                                    type="text"
                                    required
                                />
                                <p class="text-xs text-muted-foreground">This is your public URL: /{{ form.slug }}</p>
                                <InputError :message="form.errors.slug" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="description">Public Description</Label>
                                <Input
                                    id="description"
                                    v-model="form.settings.description"
                                    type="text"
                                    placeholder="We build awesome things."
                                />
                                <InputError :message="form.errors['settings.description']" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="website_url">Website URL</Label>
                                <Input
                                    id="website_url"
                                    v-model="form.settings.website_url"
                                    type="url"
                                    placeholder="https://example.com"
                                />
                                <InputError :message="form.errors['settings.website_url']" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="theme_color">Theme Color</Label>
                                <div class="flex items-center gap-2">
                                    <input
                                        id="theme_color"
                                        v-model="form.settings.theme_color"
                                        type="color"
                                        class="h-10 w-14 cursor-pointer rounded border bg-background p-1"
                                    />
                                    <Input
                                        v-model="form.settings.theme_color"
                                        type="text"
                                        class="flex-1"
                                        placeholder="#ffffff"
                                    />
                                </div>
                                <InputError :message="form.errors['settings.theme_color']" />
                            </div>
                            <Button
                                type="submit"
                                class="w-full"
                                :disabled="form.processing"
                            >
                                Save Changes
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
</template>
