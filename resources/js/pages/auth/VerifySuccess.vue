<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Email Verified" />
    <div class="flex items-center justify-center bg-background text-foreground">
        <div class="space-y-4 text-center">
            <h1 class="text-2xl font-bold text-green-600">
                Email Verified Successfully!
            </h1>
            <p class="text-muted-foreground">
                You can safely close this window and return to your original
                tab.
            </p>
        </div>
    </div>
</template>
