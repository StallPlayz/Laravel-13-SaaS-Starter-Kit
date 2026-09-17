<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Download } from '@lucide/vue';
import debounce from 'lodash/debounce';
import { ref, watch, onMounted, onUnmounted } from 'vue';
import TokenSearch from '@/components/TokenSearch.vue';
import { index } from '@/routes/admin/logs';

interface LogEntry {
    timestamp: string;
    environment: string;
    level: string;
    message: string;
}

const props = defineProps<{
    logs: {
        data: LogEntry[];
        links: any[];
        total: number;
        from: number;
        to: number;
    };
    filters: { search?: string; date?: string };
    availableDownloads: string[];
    today: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'System Logs',
                href: index(),
            },
        ],
    },
});

const searchQuery = ref(props.filters?.search || '');
const selectedDownloadDate = ref(props.filters?.date || props.today);

watch(searchQuery, debounce((value: string) => {
    router.get('/admin/logs', { 
        search: value,
        date: selectedDownloadDate.value
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));

watch(selectedDownloadDate, (newDate) => {
    router.get('/admin/logs', { search: searchQuery.value, date: newDate }, {
        preserveState: true,
        replace: true,
    });
});

const executeSearch = () => {
    router.get('/admin/logs', {
        search: searchQuery.value,
        date: selectedDownloadDate.value
    }, {
        preserveState: true,
        replace: true,
    });
};

const getBadgeClass = (level?: string) => {
    if (!level) {
return 'bg-muted text-muted-foreground border border-border';
}

    const normalized = String(level).toUpperCase();

    switch (normalized) {
        case 'EMERGENCY': case 'ALERT': case 'CRITICAL': case 'ERROR':
            return 'bg-destructive/10 text-destructive border border-destructive/20';
        case 'WARNING':
            return 'bg-amber-500/10 text-amber-500 border border-amber-500/20';
        case 'INFO': case 'NOTICE':
            return 'bg-blue-500/10 text-blue-500 border border-blue-500/20';
        default:
            return 'bg-muted text-muted-foreground border border-border';
    }
};

onMounted(() => {
    if (typeof window !== 'undefined' && window.Echo) {
        window.Echo.private('admin.health')
            .listen('.AdminDataUpdated', (e: { type: string }) => {
                if (e.type === 'log') {
                    router.reload({ only: ['logs'] });
                }
            });
    }
});

onUnmounted(() => {
    if (typeof window !== 'undefined' && window.Echo) {
        window.Echo.leave('admin.health');
    }
});
</script>

<template>

    <Head title="System Logs" />

    <div class="p-8">
        <h1 class="mb-6 text-3xl font-bold tracking-tight text-foreground">
            System Logs
        </h1>

        <div class="overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b px-6 py-5">
                <div>
                    <h3 class="text-lg font-medium">Application Logs</h3>
                    <p class="text-sm text-muted-foreground">
                        Showing {{ logs.from || 0 }} to {{ logs.to || 0 }} of {{ logs.total }} entries.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                    <div v-if="availableDownloads && availableDownloads.length > 0"
                        class="flex items-center gap-2 w-full sm:w-auto">
                        <select v-model="selectedDownloadDate"
                            class="h-9 w-full sm:w-auto rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option v-for="date in availableDownloads" :key="date" :value="date"
                                class="bg-background text-foreground">
                                {{ date === today ? `Today (${date})` : date }}
                            </option>
                        </select>

                        <a :href="`/admin/logs/download/${selectedDownloadDate}`"
                            class="inline-flex h-9 items-center justify-center rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 whitespace-nowrap"
                            download>
                            <Download class="mr-2 h-4 w-4" />
                            CSV
                        </a>
                    </div>

                    <div class="relative w-full sm:w-72">
                        <TokenSearch v-model="searchQuery" @search="executeSearch" :filters="[
                            { key: 'severity', label: 'Severity', options: ['info', 'warning', 'error', 'emergency', 'critical'] },
                            { key: 'date', label: 'Log Date', type: 'date' }
                        ]" />
                    </div>
                </div>
            </div>

            <div class="max-h-[600px] overflow-auto">
                <table class="w-full text-left text-sm">
                    <thead class="sticky top-0 z-10 bg-muted text-xs uppercase text-muted-foreground shadow-sm">
                        <tr>
                            <th class="px-6 py-3 font-medium w-48">Timestamp</th>
                            <th class="px-6 py-3 font-medium w-24">Env</th>
                            <th class="px-6 py-3 font-medium w-32">Severity</th>
                            <th class="px-6 py-3 font-medium">Message</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="(log, index) in logs.data" :key="index" class="hover:bg-muted/50">
                            <td class="whitespace-nowrap px-6 py-4 text-muted-foreground font-mono text-xs">
                                {{ log.timestamp }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-muted-foreground text-xs uppercase">
                                {{ log.environment }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                    :class="getBadgeClass(log.level)">
                                    {{ log.level }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-foreground/80 break-words">
                                {{ log.message }}
                            </td>
                        </tr>

                        <tr v-if="!logs.data || logs.data.length === 0">
                            <td colspan="4" class="px-6 py-4 text-center text-muted-foreground">
                                No logs found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="logs.links && logs.links.length > 3" class="flex flex-wrap justify-center gap-1 mt-6">
            <template v-for="(link, pIndex) in logs.links" :key="pIndex">
                <div v-if="link.url === null"
                    class="mb-1 mr-1 px-3 py-2 text-sm text-muted-foreground border rounded-md" v-html="link.label" />
                <Link v-else :href="link.url"
                    class="mb-1 mr-1 px-3 py-2 text-sm border rounded-md hover:bg-muted focus:border-primary focus:text-primary"
                    :class="{ 'bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground': link.active }"
                    v-html="link.label" />
            </template>
        </div>
    </div>
</template>