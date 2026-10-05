<script setup lang="ts">
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
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
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    workspace: any;
    serviceRequests: any[];
    projects?: any[];
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
const showReviewModal = ref(false);
const showConvertModal = ref(false);
const selectedRequest = ref<any>(null);
const feedback = ref('');

const form = useForm({
    title: '',
    description: '',
    type: 'general',
});

const convertForm = useForm({
    title: '',
    description: '',
    type: 'project',
    project_id: '',
    feedback: '',
});

const openReviewModal = (request: any) => {
    selectedRequest.value = request;
    feedback.value = request.feedback || '';
    showReviewModal.value = true;
};

const openConvertModal = () => {
    if (!selectedRequest.value) return;
    
    convertForm.title = selectedRequest.value.title;
    convertForm.description = selectedRequest.value.description;
    convertForm.type = selectedRequest.value.type === 'task' ? 'task' : 'project';
    convertForm.project_id = '';
    convertForm.feedback = feedback.value;
    
    showReviewModal.value = false;
    showConvertModal.value = true;
};

const updateStatus = (status: string) => {
    if (!selectedRequest.value) return;
    
    router.patch(`/workspaces/${props.workspace.slug}/service-requests/${selectedRequest.value.id}/status`, {
        status,
        feedback: feedback.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showReviewModal.value = false;
        }
    });
};

const submitConvert = () => {
    if (!selectedRequest.value) return;
    
    convertForm.post(`/workspaces/${props.workspace.slug}/service-requests/${selectedRequest.value.id}/convert`, {
        onSuccess: () => {
            convertForm.reset();
            showConvertModal.value = false;
        },
    });
};

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
                    <div class="flex items-center gap-2" v-if="canManage && ['pending', 'reviewed'].includes(request.status)">
                        <Button size="sm" variant="outline" @click="openReviewModal(request)">
                            {{ request.status === 'reviewed' ? 'Manage' : 'Review' }}
                        </Button>
                    </div>
                </div>
                <div class="bg-muted/50 rounded-lg p-4 text-sm whitespace-pre-wrap">
                    {{ request.description }}
                </div>
                <div v-if="request.feedback" class="mt-4 p-4 bg-primary/5 border-l-4 border-primary rounded-r-lg text-sm">
                    <p class="font-semibold mb-1">Agency Response:</p>
                    <p class="whitespace-pre-wrap">{{ request.feedback }}</p>
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

        <!-- Review Modal -->
        <Dialog :open="showReviewModal" @update:open="showReviewModal = false">
            <DialogContent class="sm:max-w-[600px]">
                <DialogHeader>
                    <DialogTitle>Review Service Request</DialogTitle>
                    <DialogDescription>
                        Review the client's request and decide how to proceed.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="selectedRequest" class="space-y-6 py-4">
                    <div>
                        <h4 class="text-sm font-medium text-muted-foreground mb-1">Requested By</h4>
                        <p class="font-medium">{{ selectedRequest.client?.name }} ({{ selectedRequest.client?.email }})</p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-muted-foreground mb-1">Title</h4>
                        <p class="font-medium text-lg">{{ selectedRequest.title }}</p>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-muted-foreground mb-1">Description</h4>
                        <div class="bg-muted/50 rounded-lg p-4 text-sm whitespace-pre-wrap">
                            {{ selectedRequest.description }}
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-muted-foreground mb-1">Requested Type</h4>
                        <p class="font-medium capitalize">{{ selectedRequest.type }}</p>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-muted-foreground mb-1">Message to Client (Optional)</h4>
                        <textarea v-model="feedback" placeholder="Explain your decision or provide an update..." rows="3" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                    </div>

                    <div class="flex justify-between items-center pt-4 border-t">
                        <div class="flex gap-2">
                            <Button variant="outline" class="text-red-600 hover:text-red-700 hover:bg-red-50" @click="updateStatus('rejected')">Reject</Button>
                            <Button variant="outline" @click="updateStatus('reviewed')">Mark as Reviewed</Button>
                        </div>
                        <Button @click="openConvertModal">Convert to Work</Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Convert Modal -->
        <Dialog :open="showConvertModal" @update:open="showConvertModal = false">
            <DialogContent class="sm:max-w-[600px]">
                <DialogHeader>
                    <DialogTitle>Convert to Work</DialogTitle>
                    <DialogDescription>
                        Edit the details before converting this request into an actionable project or task.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitConvert" class="space-y-6 py-4">
                    <div class="grid gap-2">
                        <Label for="convert_type">Convert To</Label>
                        <select id="convert_type" v-model="convertForm.type" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                            <option value="project">New Project</option>
                            <option value="task">New Task</option>
                        </select>
                        <InputError :message="convertForm.errors.type" />
                    </div>

                    <div class="grid gap-2" v-if="convertForm.type === 'task'">
                        <Label for="convert_project_id">Select Project</Label>
                        <select id="convert_project_id" v-model="convertForm.project_id" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                            <option value="" disabled>Select a project...</option>
                            <option v-for="project in projects" :key="project.id" :value="project.id">
                                {{ project.name }}
                            </option>
                        </select>
                        <InputError :message="convertForm.errors.project_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="convert_title">Title</Label>
                        <Input id="convert_title" v-model="convertForm.title" required />
                        <InputError :message="convertForm.errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="convert_description">Description</Label>
                        <textarea id="convert_description" v-model="convertForm.description" rows="6" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                        <InputError :message="convertForm.errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="convert_feedback">Message to Client (Optional)</Label>
                        <textarea id="convert_feedback" v-model="convertForm.feedback" placeholder="Let the client know this is being worked on..." rows="3" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                        <InputError :message="convertForm.errors.feedback" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <Button type="button" variant="outline" @click="showConvertModal = false">Cancel</Button>
                        <Button type="submit" :disabled="convertForm.processing">Confirm Conversion</Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
