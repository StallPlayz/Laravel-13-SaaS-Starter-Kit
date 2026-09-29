<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
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
        dates: string[];
        emergency: number[];
        alert: number[];
        critical: number[];
        error: number[];
        warning: number[];
        notice: number[];
        info: number[];
        debug: number[];
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

const isCtrlPressed = ref(false);

const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Control' || e.key === 'Meta') isCtrlPressed.value = true;
};
const handleKeyUp = (e: KeyboardEvent) => {
    if (e.key === 'Control' || e.key === 'Meta') isCtrlPressed.value = false;
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
    window.addEventListener('keyup', handleKeyUp);

    if (typeof window !== 'undefined' && window.Echo) {
        window.Echo.private('admin.health')
            .listen('.AdminDataUpdated', (e: { type: string, payload?: { level: string } }) => {
                if (e.type === 'log' && e.payload) {
                    console.log('Reverb ping received! Level:', e.payload.level);

                    const todayIndex = telemetry.value.labels.length - 1;
                    const level = e.payload.level.toLowerCase() as keyof typeof telemetry.value;

                    if (level in telemetry.value && level !== 'labels' && level !== 'dates') {
                        (telemetry.value[level] as number[])[todayIndex]++;
                        telemetry.value[level] = [...(telemetry.value[level] as number[])] as any;
                    }
                }
            });
    }
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
    window.removeEventListener('keyup', handleKeyUp);

    if (typeof window !== 'undefined' && window.Echo) {
        window.Echo.leave('admin.health');
    }
});

const chartData = computed(() => ({
    labels: telemetry.value.labels,
    datasets: [
        {
            label: 'Emergency',
            backgroundColor: '#d946ef', // fuchsia-500
            data: telemetry.value.emergency,
            borderRadius: 4,
        },
        {
            label: 'Alert',
            backgroundColor: '#f43f5e', // rose-500
            data: telemetry.value.alert,
            borderRadius: 4,
        },
        {
            label: 'Critical',
            backgroundColor: '#ef4444', // red-500
            data: telemetry.value.critical,
            borderRadius: 4,
        },
        {
            label: 'Error',
            backgroundColor: '#f97316', // orange-500
            data: telemetry.value.error,
            borderRadius: 4,
        },
        {
            label: 'Warning',
            backgroundColor: '#f59e0b', // amber-500
            data: telemetry.value.warning,
            borderRadius: 4,
        },
        {
            label: 'Notice',
            backgroundColor: '#10b981', // emerald-500
            data: telemetry.value.notice,
            borderRadius: 4,
        },
        {
            label: 'Info',
            backgroundColor: '#3b82f6', // blue-500
            data: telemetry.value.info,
            borderRadius: 4,
        },
        {
            label: 'Debug',
            backgroundColor: '#64748b', // slate-500
            data: telemetry.value.debug,
            borderRadius: 4,
        }
    ]
}));

const hoverOutlinePlugin = {
    id: 'hoverOutline',
    afterDraw(chart: any) {
        const activeElements = chart.getActiveElements();
        if (!activeElements.length) return;

        const ctx = chart.ctx;
        ctx.save();

        if (isCtrlPressed.value) {
            activeElements.forEach(({ datasetIndex, index }: any) => {
                const meta = chart.getDatasetMeta(datasetIndex);
                const element = meta.data[index];
                if (!element) return;

                ctx.beginPath();
                const left = element.x - element.width / 2;
                const top = element.y;
                const height = element.base - element.y;

                ctx.rect(left - 2, top - 2, element.width + 4, height + 4);
                ctx.lineWidth = 2;
                ctx.strokeStyle = chart.data.datasets[datasetIndex].backgroundColor;
                ctx.stroke();
            });
        } else {
            const index = activeElements[0].index;
            let minTop = Infinity;
            let maxBase = -Infinity;
            let x = 0;
            let width = 0;

            activeElements.forEach(({ datasetIndex }: any) => {
                const meta = chart.getDatasetMeta(datasetIndex);
                const element = meta.data[index];
                if (!element) return;

                if (element.base !== element.y) {
                    if (element.y < minTop) minTop = element.y;
                    if (element.base > maxBase) maxBase = element.base;
                    x = element.x;
                    width = element.width;
                }
            });

            if (width > 0 && minTop !== Infinity) {
                ctx.beginPath();
                const left = x - width / 2;
                const height = maxBase - minTop;

                ctx.rect(left - 2, minTop - 2, width + 4, height + 4);
                ctx.lineWidth = 2;
                ctx.strokeStyle = '#9ca3af';
                ctx.stroke();
            }
        }

        ctx.restore();
    }
};

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    onClick: (event: any, elements: any[], chart: any) => {
        if (!elements.length) return;

        const element = elements[0];
        const dataIndex = element.index;
        const datasetIndex = element.datasetIndex;

        const date = telemetry.value.dates[dataIndex];
        const severity = chart.data.datasets[datasetIndex].label.toLowerCase();

        let search = `date:"${date}"`;

        if (event.native && (event.native.ctrlKey || event.native.metaKey)) {
            search += ` severity:"${severity}"`;
        }

        router.get('/admin/logs', { search });
    },
    onHover: (event: any, elements: any[]) => {
        event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
    },
    interaction: {
        mode: (isCtrlPressed.value ? 'dataset' : 'index') as 'dataset' | 'index',
        intersect: isCtrlPressed.value,
    },
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: { color: '#9ca3af' }
        },
        tooltip: {
            mode: (isCtrlPressed.value ? 'dataset' : 'index') as 'dataset' | 'index',
            intersect: isCtrlPressed.value,
            callbacks: {
                footer: () => {
                    return [
                        '',
                        'Click to view logs for this day',
                        'Ctrl+Click to filter by specific severity'
                    ];
                }
            }
        },
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
}));
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
                <Bar :data="chartData" :options="chartOptions" :plugins="[hoverOutlinePlugin]" />
            </div>
        </div>
    </div>
</template>
