<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    workspace: any;
    project: any;
    show: boolean;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    title: '',
    description: '',
    status: 'todo',
    priority: 'medium',
    due_date: '',
});

const submit = () => {
    form.post(`/workspaces/${props.workspace.slug}/projects/${props.project.slug}/tasks`, {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <Dialog :open="show" @update:open="emit('close')">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>Create Task</DialogTitle>
                <DialogDescription>
                    Add a new task to the project.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4 py-4">
                <div class="grid gap-2">
                    <Label for="title">Task Title</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        type="text"
                        required
                        autofocus
                        placeholder="e.g. Design landing page"
                    />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="description">Description</Label>
                    <Input
                        id="description"
                        v-model="form.description"
                        type="text"
                        placeholder="Add more details..."
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            v-model="form.status"
                            class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="todo">To Do</option>
                            <option value="in_progress">In Progress</option>
                            <option value="review">Review</option>
                            <option value="done">Done</option>
                        </select>
                        <InputError :message="form.errors.status" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="priority">Priority</Label>
                        <select
                            id="priority"
                            v-model="form.priority"
                            class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        <InputError :message="form.errors.priority" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="due_date">Due Date</Label>
                    <Input
                        id="due_date"
                        v-model="form.due_date"
                        type="date"
                    />
                    <InputError :message="form.errors.due_date" />
                </div>

                <DialogFooter class="pt-4">
                    <Button type="button" variant="outline" @click="emit('close')">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        Create Task
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
