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
