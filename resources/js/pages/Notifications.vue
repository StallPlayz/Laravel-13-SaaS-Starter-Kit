<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { setLayoutProps } from '@inertiajs/vue3';
import { Bell, Check } from '@lucide/vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    notifications: any[];
}>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Global Dashboard',
            href: '/dashboard',
        },
        {
            title: 'Notifications',
            href: '/notifications',
        },
    ],
});

const markAsRead = (id: string) => {
    router.post(`/notifications/${id}/read`, {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Notifications" />

    <div class="p-8 w-full max-w-4xl mx-auto">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Notifications</h1>
                <p class="text-sm text-muted-foreground mt-1">Your global inbox for all workspace activity.</p>
            </div>
        </div>

        <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
            <div class="p-0">
                <div v-if="notifications.length === 0" class="flex flex-col items-center justify-center p-12 text-center">
                    <div class="rounded-full bg-primary/10 p-4 mb-4">
                        <Bell class="h-8 w-8 text-primary" />
                    </div>
                    <h3 class="text-lg font-medium">You're all caught up!</h3>
                    <p class="text-sm text-muted-foreground mt-1">
                        You don't have any notifications right now.
                    </p>
                </div>
                <div v-else class="divide-y">
                    <div v-for="notification in notifications" :key="notification.id" class="p-4 flex items-start gap-4 hover:bg-muted/50 transition-colors" :class="{ 'bg-primary/5': !notification.read_at }">
                        <div class="mt-1">
                            <div class="h-2 w-2 rounded-full bg-primary" v-if="!notification.read_at"></div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium">{{ notification.data.message || 'New Notification' }}</p>
                            <p class="text-xs text-muted-foreground mt-1">{{ new Date(notification.created_at).toLocaleString() }}</p>
                            <div class="mt-2" v-if="notification.data.url">
                                <a :href="notification.data.url" class="text-xs text-primary hover:underline font-medium">View Details &rarr;</a>
                            </div>
                        </div>
                        <div v-if="!notification.read_at">
                            <Button variant="ghost" size="sm" @click="markAsRead(notification.id)">
                                <Check class="w-4 h-4 mr-2" />
                                Mark as Read
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
