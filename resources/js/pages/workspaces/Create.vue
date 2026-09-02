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

const form = useForm({
    name: '',
});

const submit = () => {
    form.post('/workspaces');
};
</script>

<template>
        <Head title="Create Workspace" />

        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mx-auto w-full max-w-md">
                <Card>
                    <CardHeader>
                        <CardTitle>Create Workspace</CardTitle>
                        <CardDescription>
                            Create a new workspace to manage your agency and
                            clients.
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
                                    placeholder="Acme Agency"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <Button
                                type="submit"
                                class="w-full"
                                :disabled="form.processing"
                            >
                                Create Workspace
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
</template>
