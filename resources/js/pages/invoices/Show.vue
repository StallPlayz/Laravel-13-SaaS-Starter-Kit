<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { setLayoutProps } from '@inertiajs/vue3';
import { FileText, Download, Send, CheckCircle2, AlertCircle, XCircle } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const props = defineProps<{
    workspace: any;
    invoice: any;
}>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Invoices',
            href: `/workspaces/${props.workspace.slug}/invoices`,
        },
        {
            title: props.invoice.invoice_number,
            href: `/workspaces/${props.workspace.slug}/invoices/${props.invoice.id}`,
        },
    ],
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const updateStatus = (status: string) => {
    router.patch(`/workspaces/${props.workspace.slug}/invoices/${props.invoice.id}/status`, {
        status,
    }, {
        preserveScroll: true,
    });
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
</script>

<template>
    <Head :title="`Invoice ${invoice.invoice_number}`" />

    <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    {{ invoice.invoice_number }}
                </h1>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" :class="getStatusColor(invoice.status)">
                    {{ invoice.status.toUpperCase() }}
                </span>
            </div>
            
            <div class="flex items-center gap-2">
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline">
                            Update Status
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="updateStatus('draft')" :disabled="invoice.status === 'draft'">
                            <FileText class="mr-2 h-4 w-4" />
                            <span>Draft</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="updateStatus('sent')" :disabled="invoice.status === 'sent'">
                            <Send class="mr-2 h-4 w-4 text-blue-500" />
                            <span>Mark as Sent</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="updateStatus('paid')" :disabled="invoice.status === 'paid'">
                            <CheckCircle2 class="mr-2 h-4 w-4 text-emerald-500" />
                            <span>Mark as Paid</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="updateStatus('overdue')" :disabled="invoice.status === 'overdue'">
                            <AlertCircle class="mr-2 h-4 w-4 text-amber-500" />
                            <span>Mark as Overdue</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="updateStatus('cancelled')" :disabled="invoice.status === 'cancelled'">
                            <XCircle class="mr-2 h-4 w-4 text-red-500" />
                            <span>Cancel Invoice</span>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                
                <Button variant="default">
                    <Download class="w-4 h-4 mr-2" />
                    Download PDF
                </Button>
            </div>
        </div>

        <div class="rounded-xl border bg-card text-card-foreground shadow-sm overflow-hidden">
            <div class="p-8 sm:p-12">
                <div class="flex justify-between items-start mb-12">
                    <div>
                        <h2 class="text-2xl font-bold text-primary mb-2">{{ workspace.name }}</h2>
                        <p class="text-muted-foreground text-sm max-w-xs">
                            {{ workspace.settings?.description || 'Workspace description' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <h3 class="text-xl font-bold text-muted-foreground mb-2">INVOICE</h3>
                        <p class="font-medium">{{ invoice.invoice_number }}</p>
                        <p class="text-sm text-muted-foreground mt-1">
                            Issued: {{ new Date(invoice.issue_date).toLocaleDateString() }}
                        </p>
                        <p class="text-sm text-muted-foreground" v-if="invoice.due_date">
                            Due: {{ new Date(invoice.due_date).toLocaleDateString() }}
                        </p>
                    </div>
                </div>

                <div class="mb-12">
                    <h4 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider mb-4">Bill To</h4>
                    <p class="font-medium text-lg">{{ invoice.client_name }}</p>
                    <p class="text-muted-foreground">{{ invoice.client_email }}</p>
                    <p class="text-muted-foreground mt-2" v-if="invoice.project">
                        Project: <span class="font-medium">{{ invoice.project.name }}</span>
                    </p>
                </div>

                <div class="mb-12">
                    <table class="w-full text-left">
                        <thead class="border-b-2 border-muted">
                            <tr>
                                <th class="py-3 font-semibold text-muted-foreground">Description</th>
                                <th class="py-3 font-semibold text-muted-foreground text-center">Qty</th>
                                <th class="py-3 font-semibold text-muted-foreground text-right">Price</th>
                                <th class="py-3 font-semibold text-muted-foreground text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-muted">
                            <tr v-for="item in invoice.items" :key="item.id">
                                <td class="py-4">{{ item.description }}</td>
                                <td class="py-4 text-center">{{ item.quantity }}</td>
                                <td class="py-4 text-right">{{ formatCurrency(item.unit_price) }}</td>
                                <td class="py-4 text-right font-medium">{{ formatCurrency(item.total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end">
                    <div class="w-full max-w-sm space-y-3">
                        <div class="flex justify-between text-muted-foreground">
                            <span>Subtotal</span>
                            <span>{{ formatCurrency(invoice.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-muted-foreground">
                            <span>Tax</span>
                            <span>{{ formatCurrency(invoice.tax) }}</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold border-t pt-3">
                            <span>Total</span>
                            <span>{{ formatCurrency(invoice.total) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-16 pt-8 border-t text-sm text-muted-foreground" v-if="invoice.notes">
                    <h4 class="font-semibold text-foreground mb-2">Notes</h4>
                    <p class="whitespace-pre-wrap">{{ invoice.notes }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
