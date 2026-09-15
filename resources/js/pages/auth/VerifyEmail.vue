<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, onBeforeUnmount } from 'vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import Layout from '@/layouts/auth/AuthSimpleLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineOptions({
    layout: (props: { email: string }) => [
        Layout,
        {
            title: 'Email verification',
            description: `Please verify your email address by clicking on the link we just emailed to `,
            descriptionSuffix: props.email,
        },
    ],
});

defineProps<{
    status?: string;
    email: string;
}>();

let pollInterval: ReturnType<typeof setInterval> | null = null;

const checkVerificationStatus = async () => {
    try {
        const response = await axios.get('/api/user/verified-status');

        if (response.data.verified) {
            if (pollInterval) {
                clearInterval(pollInterval);
            }

            router.visit('/dashboard');
        }
    } catch (error) {
        console.error('Polling failed', error);
    }
};

onMounted(() => {
    pollInterval = setInterval(checkVerificationStatus, 3000);
});

onBeforeUnmount(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
});
</script>

<template>
    <Head title="Email verification" />

    <div
        v-if="status === 'verification-link-sent'"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        A new verification link has been sent to the email address you provided
        during registration.
    </div>

    <Form
        v-bind="send.form()"
        class="space-y-6 text-center"
        v-slot="{ processing }"
    >
        <Button :disabled="processing" variant="secondary">
            <Spinner v-if="processing" />
            Resend verification email
        </Button>

        <TextLink :href="logout()" as="button" class="mx-auto block text-sm">
            Log out
        </TextLink>
    </Form>
</template>
