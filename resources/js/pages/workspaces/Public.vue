<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    workspace: {
        name: string;
        slug: string;
        tier: string;
        settings?: {
            description?: string;
            theme_color?: string;
            website_url?: string;
        };
    };
}>();

const themeColor = computed(() => props.workspace.settings?.theme_color || '#ffffff');
</script>

<template>
    <Head :title="workspace.name" />

    <div class="min-h-screen flex flex-col items-center justify-center p-4 transition-colors duration-500" :style="{ backgroundColor: themeColor }">
        <div class="max-w-md w-full text-center space-y-6 bg-background/80 backdrop-blur-sm p-8 rounded-2xl shadow-lg border">
            <div class="space-y-4">
                <h1 class="text-4xl font-bold tracking-tight">{{ workspace.name }}</h1>
                
                <p v-if="workspace.settings?.description" class="text-muted-foreground text-lg">
                    {{ workspace.settings.description }}
                </p>
                <p v-else class="text-muted-foreground">
                    Welcome to the public profile of {{ workspace.name }}.
                </p>

                <div v-if="workspace.settings?.website_url" class="pt-2">
                    <a :href="workspace.settings.website_url" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline font-medium">
                        Visit Website &rarr;
                    </a>
                </div>
            </div>
            
            <div class="pt-4">
                <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80">
                    {{ workspace.tier === 'pro' ? 'Pro Agency' : 'Standard Agency' }}
                </div>
            </div>
        </div>
    </div>
</template>
