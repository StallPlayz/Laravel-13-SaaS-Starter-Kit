<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onBeforeUnmount } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Accept Invitation',
        description:
            'Log in to your existing account to accept the workspace invitation.',
    },
});

const props = defineProps<{
    invitation: {
        workspace: { name: string };
        email: string;
        role: string;
    };
    token: string;
}>();

const loginMethod = ref<'password' | 'otp'>('password');
const otpSent = ref(false);
const countdown = ref(0);
let timerInterval: ReturnType<typeof setInterval> | null = null;

const otpForm = useForm({
    email: props.invitation.email,
    code: '',
});

const passwordForm = useForm({
    email: props.invitation.email,
    password: '',
    remember: false,
});

const submitPassword = () => {
    passwordForm.post('/login', {
        onFinish: () => passwordForm.reset('password'),
    });
};

const startCountdown = () => {
    countdown.value = 60;

    if (timerInterval) {
        clearInterval(timerInterval);
    }

    timerInterval = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--;
        } else {
            clearInterval(timerInterval!);
        }
    }, 1000);
};

const requestOtp = () => {
    otpForm.post('/login/otp/request', {
        onSuccess: () => {
            otpSent.value = true;
            otpForm.reset('code');
            startCountdown();
        },
    });
};

onBeforeUnmount(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
});

const verifyOtp = () => {
    otpForm.post('/login/otp/verify');
};
</script>

<template>
    <Head title="Log in to accept invitation" />

    <div class="mb-6 text-center text-sm">
        You are joining
        <span class="font-semibold text-foreground">{{
            invitation.workspace.name
        }}</span>
        as a {{ invitation.role }}.
    </div>

    <div class="mb-6 flex space-x-1 rounded-xl bg-muted p-1">
        <button
            @click="loginMethod = 'password'"
            :class="[
                'w-full rounded-lg py-2.5 text-sm leading-5 font-medium',
                loginMethod === 'password'
                    ? 'bg-background shadow'
                    : 'text-muted-foreground hover:bg-white/[0.12] hover:text-foreground',
            ]"
        >
            Password
        </button>
        <button
            @click="loginMethod = 'otp'"
            :class="[
                'w-full rounded-lg py-2.5 text-sm leading-5 font-medium',
                loginMethod === 'otp'
                    ? 'bg-background shadow'
                    : 'text-muted-foreground hover:bg-white/[0.12] hover:text-foreground',
            ]"
        >
            Email Code
        </button>
    </div>

    <form
        v-if="loginMethod === 'password'"
        @submit.prevent="submitPassword"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email">Email address (Locked to invitation)</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    v-model="passwordForm.email"
                    required
                    disabled
                    class="bg-muted"
                />
                <InputError :message="passwordForm.errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Password</Label>
                    <TextLink :href="request()" class="text-sm" :tabindex="5">
                        Forgot your password?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    v-model="passwordForm.password"
                    required
                    autofocus
                    :tabindex="2"
                    autocomplete="current-password"
                />
                <InputError :message="passwordForm.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Remember me</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                :tabindex="4"
                :disabled="passwordForm.processing"
                data-test="login-button"
            >
                <Spinner v-if="passwordForm.processing" />
                Log in
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Don't have an account?
            <TextLink :href="register()" :tabindex="5">Sign up</TextLink>
        </div>
    </form>

    <form
        v-else-if="loginMethod === 'otp' && !otpSent"
        @submit.prevent="requestOtp"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-2">
            <Label for="otp_email">Email address</Label>
            <Input
                id="otp_email"
                name="email"
                type="email"
                v-model="otpForm.email"
                disabled
                class="bg-muted"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />
            <InputError :message="otpForm.errors.email" />
        </div>
        <Button type="submit" :disabled="otpForm.processing">
            <Spinner v-if="otpForm.processing" class="mr-2" />
            Send Code
        </Button>
    </form>

    <form v-else @submit.prevent="verifyOtp" class="flex flex-col gap-6">
        <div class="grid gap-2 text-center">
            <Label>Enter Verification Code</Label>
            <p class="text-sm text-muted-foreground">
                We sent a 6-digit code to {{ otpForm.email }}.
            </p>

            <div class="mt-4 flex justify-center">
                <InputOTP
                    id="otp"
                    v-model="otpForm.code"
                    :maxlength="6"
                    :disabled="otpForm.processing"
                    autofocus
                >
                    <InputOTPGroup>
                        <InputOTPSlot
                            v-for="index in 6"
                            :key="index"
                            :index="index - 1"
                        />
                    </InputOTPGroup>
                </InputOTP>
            </div>

            <InputError :message="otpForm.errors.code" />

            <div class="mt-4 text-center text-sm">
                <span v-if="countdown > 0" class="text-muted-foreground">
                    Resend code in {{ countdown }}s
                </span>
                <button
                    v-else
                    @click.prevent="requestOtp"
                    type="button"
                    class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 hover:decoration-current!"
                    :disabled="otpForm.processing"
                >
                    Resend Code
                </button>
            </div>
        </div>

        <Button type="submit" class="w-full" :disabled="otpForm.processing">
            <Spinner v-if="otpForm.processing" class="mr-2" />
            Login
        </Button>
    </form>
</template>
