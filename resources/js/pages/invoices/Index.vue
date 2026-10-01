<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { setLayoutProps } from '@inertiajs/vue3';
import { Plus, Receipt, FileText, CheckCircle2, Clock, AlertCircle, XCircle } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    workspace: any;
    invoices: any[];
}>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Invoices',
            href: `/workspaces/${props.workspace.slug}/invoices`,
        },
    ],
});

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'paid': return CheckCircle2;
        case 'sent': return Clock;
        case 'overdue': return AlertCircle;
        case 'cancelled': return XCircle;
        default: return FileText;
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'paid': return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200';
        case 'sent': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        case 'overdue': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        case 'cancelled': return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const stats = computed(() => {
    const total = props.invoices.length;
    const paid = props.invoices.filter(i => i.status === 'paid').reduce((sum, i) => sum + parseFloat(i.total), 0);
    const outstanding = props.invoices.filter(i => ['draft', 'sent', 'overdue'].includes(i.status)).reduce((sum, i) => sum + parseFloat(i.total), 0);
    
    return { total, paid, outstanding };
});
</script>

<template>
    <Head title="Invoices" />

    <div class="p-8 w-full">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Invoices</h1>
                <p class="text-sm text-muted-foreground mt-1">Manage your billing and payments.</p>
            </div>
            <Link
                :href="`/workspaces/${workspace.slug}/invoices/create`"
                class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
            >
                <Plus class="mr-2 h-4 w-4" />
                New Invoice
            </Link>
        </div>

        <div class="grid gap-6 md:grid-cols-3 mb-8">
            <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-muted-foreground">Total Invoices</h3>
                    <Receipt class="w-4 h-4 text-muted-foreground" />
                </div>
                <div class="text-2xl font-bold">{{ stats.total }}</div>
            </div>
            <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-muted-foreground">Outstanding</h3>
                    <AlertCircle class="w-4 h-4 text-amber-500" />
                </div>
                <div class="text-2xl font-bold">{{ formatCurrency(stats.outstanding) }}</div>
            </div>
            <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-muted-foreground">Paid</h3>
                    <CheckCircle2 class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="text-2xl font-bold">{{ formatCurrency(stats.paid) }}</div>
            </div>
        </div>

        <div v-if="invoices.length === 0" class="flex flex-col items-center justify-center rounded-xl border border-dashed p-12 text-center">
            <div class="rounded-full bg-primary/10 p-4 mb-4">
                <Receipt class="h-8 w-8 text-primary" />
            </div>
            <h3 class="text-lg font-medium">No invoices yet</h3>
            <p class="text-sm text-muted-foreground mt-1 mb-4 max-w-sm">
                Get started by creating your first invoice to bill your clients.
            </p>
            <Link
                :href="`/workspaces/${workspace.slug}/invoices/create`"
                class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
            >
                Create Invoice
            </Link>
        </div>

        <div v-else class="rounded-xl border bg-card text-card-foreground shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-medium">Invoice</th>
                            <th scope="col" class="px-6 py-3 font-medium">Client</th>
                            <th scope="col" class="px-6 py-3 font-medium">Issue Date</th>
                            <th scope="col" class="px-6 py-3 font-medium">Amount</th>
                            <th scope="col" class="px-6 py-3 font-medium">Status</th>
                            <th scope="col" class="px-6 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="invoice in invoices" :key="invoice.id" class="border-b last:border-0 hover:bg-muted/50 transition-colors">
                            <td class="px-6 py-4 font-medium">
                                <Link :href="`/workspaces/${workspace.slug}/invoices/${invoice.id}`" class="hover:underline">
                                    {{ invoice.invoice_number }}
                                </Link>
                            </td>
                            <td class="px-6 py-4">
                                <div>{{ invoice.client_name }}</div>
                                <div class="text-xs text-muted-foreground">{{ invoice.client_email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                {{ new Date(invoice.issue_date).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4 font-medium">
                                {{ formatCurrency(invoice.total) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" :class="getStatusColor(invoice.status)">
                                    {{ invoice.status.toUpperCase() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="`/workspaces/${workspace.slug}/invoices/${invoice.id}`" class="text-primary hover:underline font-medium">
                                    View
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
