<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Database, HardDrive, Zap, CheckCircle2, XCircle } from '@lucide/vue';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js';
import { computed, onUnmounted, onMounted, ref, watch } from 'vue';
import { Bar } from 'vue-chartjs';
import { dashboard } from '@/routes/admin';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const props = defineProps<{
    vitals: {
        database: boolean;
        cache: boolean;
        storage: boolean;
    };
    telemetry: {
        labels: string[];
        errors: number[];
        warnings: number[];
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Admin Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const vitals = ref(props.vitals);
const telemetry = ref(props.telemetry);

watch(() => props.telemetry, (newTelemetry) => {
    telemetry.value = newTelemetry;
}, { deep: true });

onMounted(() => {
    if (typeof window !== 'undefined' && window.Echo) {
        window.Echo.private('admin.health')
            .listen('.AdminDataUpdated', (e: { type: string, payload?: { level: string } }) => {
                if (e.type === 'log' && e.payload) {
                    console.log('Reverb ping received! Level:', e.payload.level);

                    const todayIndex = telemetry.value.labels.length - 1;

                    if (['ERROR', 'CRITICAL', 'EMERGENCY'].includes(e.payload.level)) {
                        telemetry.value.errors[todayIndex]++;
                        telemetry.value.errors = [...telemetry.value.errors];
                    } else if (e.payload.level === 'WARNING') {
                        telemetry.value.warnings[todayIndex]++;
                        telemetry.value.warnings = [...telemetry.value.warnings];
                    }
                }
            });
    }
});

onUnmounted(() => {
    window.Echo.leave('admin.health');
});

const chartData = computed(() => ({
    labels: telemetry.value.labels,
    datasets: [
        {
            label: 'Errors (Critical/Emergency)',
            backgroundColor: '#ef4444',
            data: telemetry.value.errors,
            borderRadius: 4,
        },
        {
            label: 'Warnings',
            backgroundColor: '#f59e0b',
            data: telemetry.value.warnings,
            borderRadius: 4,
        }
    ]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: { color: '#9ca3af' }
        },
        tooltip: { mode: 'index' as const, intersect: false },
    },
    scales: {
        x: {
            stacked: true,
            grid: { display: false },
            ticks: { color: '#9ca3af' }
        },
        y: {
            stacked: true,
            grid: { color: '#374151' },
            ticks: { color: '#9ca3af', precision: 0 }
        }
    }
};
</script>

<template>
    <Head title="Platform Health" />

    <div class="p-8">
        <h1 class="mb-6 text-3xl font-bold tracking-tight text-foreground">
            Platform Health
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-primary/10 rounded-lg text-primary">
                        <Database class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">PostgreSQL</p>
                        <h3 class="text-xl font-bold">Database</h3>
                    </div>
                </div>
                <CheckCircle2 v-if="vitals.database" class="w-8 h-8 text-emerald-500" />
                <XCircle v-else class="w-8 h-8 text-destructive" />
            </div>

            <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-primary/10 rounded-lg text-primary">
                        <Zap class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Redis / Store</p>
                        <h3 class="text-xl font-bold">Cache</h3>
                    </div>
                </div>
                <CheckCircle2 v-if="vitals.cache" class="w-8 h-8 text-emerald-500" />
                <XCircle v-else class="w-8 h-8 text-destructive" />
            </div>

            <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-primary/10 rounded-lg text-primary">
                        <HardDrive class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Local Disk</p>
                        <h3 class="text-xl font-bold">Storage</h3>
                    </div>
                </div>
                <CheckCircle2 v-if="vitals.storage" class="w-8 h-8 text-emerald-500" />
                <XCircle v-else class="w-8 h-8 text-destructive" />
            </div>
        </div>

        <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6">
            <div class="mb-4">
                <h3 class="text-lg font-medium">7-Day System Pulse</h3>
                <p class="text-sm text-muted-foreground">Log severity volume across the platform.</p>
            </div>

            <div class="relative h-[400px] w-full">
                <Bar :data="chartData" :options="chartOptions" />
            </div>
        </div>
    </div>
</template>
