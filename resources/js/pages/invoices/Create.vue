<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { setLayoutProps } from '@inertiajs/vue3';
import { Plus, Trash2 } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    workspace: any;
    projects: any[];
}>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Invoices',
            href: `/workspaces/${props.workspace.slug}/invoices`,
        },
        {
            title: 'Create Invoice',
            href: `/workspaces/${props.workspace.slug}/invoices/create`,
        },
    ],
});

const form = useForm({
    client_name: '',
    client_email: '',
    project_id: '',
    issue_date: new Date().toISOString().split('T')[0],
    due_date: '',
    notes: '',
    items: [
        { description: '', quantity: 1, unit_price: 0 }
    ],
});

const addItem = () => {
    form.items.push({ description: '', quantity: 1, unit_price: 0 });
};

const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const submit = () => {
    form.post(`/workspaces/${props.workspace.slug}/invoices`);
};
</script>

<template>
    <Head title="Create Invoice" />

    <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <form @submit.prevent="submit">
            <div class="flex flex-col space-y-8">
                <div class="flex items-center justify-between">
                    <Heading
                        title="Create Invoice"
                        description="Generate a new invoice for your client."
                    />
                    <Button type="submit" :disabled="form.processing">
                        Save Invoice
                    </Button>
                </div>

                <div class="grid gap-8 md:grid-cols-2">
                    <div class="space-y-6 rounded-xl border bg-card p-6 shadow-sm">
                        <h3 class="font-medium text-lg">Client Details</h3>
                        
                        <div class="grid gap-2">
                            <Label for="client_name">Client Name</Label>
                            <Input id="client_name" v-model="form.client_name" required />
                            <InputError :message="form.errors.client_name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="client_email">Client Email</Label>
                            <Input id="client_email" type="email" v-model="form.client_email" />
                            <InputError :message="form.errors.client_email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="project_id">Link to Project (Optional)</Label>
                            <select id="project_id" v-model="form.project_id" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                <option value="">None</option>
                                <option v-for="project in projects" :key="project.id" :value="project.id">
                                    {{ project.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.project_id" />
                        </div>
                    </div>

                    <div class="space-y-6 rounded-xl border bg-card p-6 shadow-sm">
                        <h3 class="font-medium text-lg">Invoice Details</h3>
                        
                        <div class="grid gap-2">
                            <Label for="issue_date">Issue Date</Label>
                            <Input id="issue_date" type="date" v-model="form.issue_date" required />
                            <InputError :message="form.errors.issue_date" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="due_date">Due Date</Label>
                            <Input id="due_date" type="date" v-model="form.due_date" />
                            <InputError :message="form.errors.due_date" />
                        </div>
                    </div>
                </div>

                <div class="space-y-6 rounded-xl border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium text-lg">Line Items</h3>
                        <Button type="button" variant="outline" size="sm" @click="addItem">
                            <Plus class="w-4 h-4 mr-2" />
                            Add Item
                        </Button>
                    </div>

                    <div class="space-y-4">
                        <div v-for="(item, index) in form.items" :key="index" class="flex items-start gap-4">
                            <div class="grid gap-2 flex-1">
                                <Label v-if="index === 0">Description</Label>
                                <Input v-model="item.description" placeholder="Item description" required />
                                <InputError :message="form.errors[`items.${index}.description`]" />
                            </div>
                            <div class="grid gap-2 w-24">
                                <Label v-if="index === 0">Qty</Label>
                                <Input type="number" min="1" v-model="item.quantity" required />
                                <InputError :message="form.errors[`items.${index}.quantity`]" />
                            </div>
                            <div class="grid gap-2 w-32">
                                <Label v-if="index === 0">Price</Label>
                                <Input type="number" min="0" step="0.01" v-model="item.unit_price" required />
                                <InputError :message="form.errors[`items.${index}.unit_price`]" />
                            </div>
                            <div class="grid gap-2 w-32">
                                <Label v-if="index === 0">Total</Label>
                                <div class="h-10 flex items-center px-3 border rounded-md bg-muted/50 font-medium">
                                    ${{ (item.quantity * item.unit_price).toFixed(2) }}
                                </div>
                            </div>
                            <div class="pt-8" v-if="index === 0">
                                <Button type="button" variant="ghost" size="icon" @click="removeItem(index)" :disabled="form.items.length === 1">
                                    <Trash2 class="w-4 h-4 text-destructive" />
                                </Button>
                            </div>
                            <div class="" v-else>
                                <Button type="button" variant="ghost" size="icon" @click="removeItem(index)">
                                    <Trash2 class="w-4 h-4 text-destructive" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 rounded-xl border bg-card p-6 shadow-sm">
                    <div class="grid gap-2">
                        <Label for="notes">Notes / Terms</Label>
                        <textarea id="notes" v-model="form.notes" placeholder="Thank you for your business!" rows="3" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                        <InputError :message="form.errors.notes" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
