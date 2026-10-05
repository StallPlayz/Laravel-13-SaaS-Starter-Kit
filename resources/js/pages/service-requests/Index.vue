<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { setLayoutProps } from '@inertiajs/vue3';
import { Plus, Inbox, CheckCircle2, Clock, AlertCircle, XCircle } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    workspace: any;
    serviceRequests: any[];
}>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Service Requests',
            href: `/workspaces/${props.workspace.slug}/service-requests`,
        },
    ],
});

const page = usePage();
const currentRole = computed(() => page.props.auth.currentRole as string);
const canManage = computed(() => currentRole.value === 'owner' || currentRole.value === 'admin');

const showCreateModal = ref(false);

const form = useForm({
    title: '',
    description: '',
    type: 'general',
});

const submit = () => {
    form.post(`/workspaces/${props.workspace.slug}/service-requests`, {
        onSuccess: () => {
            form.reset();
            showCreateModal.value = false;
        },
    });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'pending': return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200';
        case 'reviewed': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        case 'converted': return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200';
        case 'rejected': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
    }
};
</script>

<template>
    <Head title="Service Requests" />

    <div class="p-8 w-full">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Service Requests</h1>
                <p class="text-sm text-muted-foreground mt-1">
                    {{ canManage ? 'Manage incoming requests from your clients.' : 'Submit new requests to the agency.' }}
                </p>
            </div>
            <Button v-if="!canManage" @click="showCreateModal = true">
                <Plus class="mr-2 h-4 w-4" />
                New Request
            </Button>
        </div>

        <div v-if="serviceRequests.length === 0" class="flex flex-col items-center justify-center rounded-xl border border-dashed p-12 text-center">
            <div class="rounded-full bg-primary/10 p-4 mb-4">
                <Inbox class="h-8 w-8 text-primary" />
            </div>
            <h3 class="text-lg font-medium">No requests yet</h3>
            <p class="text-sm text-muted-foreground mt-1 mb-4 max-w-sm">
                {{ canManage ? "You don't have any pending service requests from clients." : "Need something done? Submit a new service request." }}
            </p>
            <Button v-if="!canManage" @click="showCreateModal = true">
                Create Request
            </Button>
        </div>

        <div v-else class="grid gap-4">
            <div v-for="request in serviceRequests" :key="request.id" class="rounded-xl border bg-card p-6 shadow-sm">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="font-semibold text-lg">{{ request.title }}</h3>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" :class="getStatusColor(request.status)">
                                {{ request.status.toUpperCase() }}
                            </span>
                        </div>
                        <p class="text-sm text-muted-foreground" v-if="canManage">
                            Requested by <span class="font-medium text-foreground">{{ request.client?.name }}</span> on {{ new Date(request.created_at).toLocaleDateString() }}
                        </p>
                        <p class="text-sm text-muted-foreground" v-else>
                            Submitted on {{ new Date(request.created_at).toLocaleDateString() }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2" v-if="canManage && request.status === 'pending'">
                        <Button size="sm" variant="outline">Review</Button>
                    </div>
                </div>
                <div class="bg-muted/50 rounded-lg p-4 text-sm whitespace-pre-wrap">
                    {{ request.description }}
                </div>
                <div class="mt-4 flex items-center gap-2 text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    Type: {{ request.type }}
                </div>
            </div>
        </div>

        <Dialog :open="showCreateModal" @update:open="showCreateModal = false">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>New Service Request</DialogTitle>
                    <DialogDescription>
                        Describe what you need help with. The agency will review your request shortly.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-6 py-4">
                    <div class="grid gap-2">
                        <Label for="title">Request Title</Label>
                        <Input id="title" v-model="form.title" placeholder="e.g. New Landing Page Design" required />
                        <InputError :message="form.errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="type">Request Type</Label>
                        <select id="type" v-model="form.type" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                            <option value="project">New Project</option>
                            <option value="task">New Task / Feature</option>
                            <option value="general">General Inquiry</option>
                        </select>
                        <InputError :message="form.errors.type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <textarea id="description" v-model="form.description" placeholder="Please provide as much detail as possible..." rows="5" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required></textarea>
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="showCreateModal = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">Submit Request</Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
