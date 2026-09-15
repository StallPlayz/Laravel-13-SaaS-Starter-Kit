<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, watch, nextTick } from 'vue';
import Combobox from '@/components/Combobox.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import debounce from 'lodash/debounce';

const props = defineProps<{
    passwordRules?: string;
    token: string;
    invitation: {
        email: string;
        role: string;
        workspace: {
            name: string;
        };
    };
}>();

defineOptions({
    layout: {
        title: 'Accept Invitation',
        description:
            'Enter your details below to accept your invitation and create your account',
    },
});

const step = ref(1);

const selectedCountry = ref('');
const selectedProvince = ref('');
const selectedCity = ref('');
const selectedDistrict = ref('');

const countries = ref<{ value: string; label: string; id?: string }[]>([]);
const provinces = ref<{ value: string; label: string; id?: string }[]>([]);
const cities = ref<{ value: string; label: string; id?: string }[]>([]);

const form = useForm({
    name: '',
    phone_number: '',
    country: '',
    province: '',
    city: '',
    district: '',
    address: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

onMounted(async () => {
    try {
        const response = await fetch('/api/geo/countries');

        if (!response.ok) {
            throw new Error('Failed to fetch');
        }

        const data = await response.json();

        if (data && data.data) {
            countries.value = data.data
                .map((item: any) => ({
                    value: item.name,
                    label: item.name,
                    id: item.code,
                }))
                .sort((a: any, b: any) => a.label.localeCompare(b.label));
        }
    } catch (error) {
        console.error('Failed to fetch countries:', error);
    }
});

watch(selectedCountry, async (newCountryName) => {
    selectedProvince.value = '';
    selectedCity.value = '';
    selectedDistrict.value = '';
    provinces.value = [];
    cities.value = [];
    form.country = newCountryName;

    if (!newCountryName) {
        return;
    }

    const country = (countries.value as any[]).find(
        (c) => c.value === newCountryName,
    );

    if (!country) {
        return;
    }

    try {
        const response = await fetch(
            `/api/geo/countries/${country.id}/regions`,
        );

        if (!response.ok) {
            throw new Error('Failed to fetch');
        }

        const data = await response.json();

        if (data && data.data) {
            provinces.value = data.data
                .map((item: any) => ({
                    value: item.name,
                    label: item.name,
                    id: item.isoCode,
                }))
                .sort((a: any, b: any) => a.label.localeCompare(b.label));
        }
    } catch (error) {
        console.error('Failed to fetch provinces:', error);
    }
});

watch(selectedProvince, async (newProvinceName) => {
    selectedCity.value = '';
    selectedDistrict.value = '';
    cities.value = [];
    form.province = newProvinceName;

    if (!newProvinceName || !selectedCountry.value) {
        return;
    }

    const country = (countries.value as any[]).find(
        (c) => c.value === selectedCountry.value,
    );
    const province = (provinces.value as any[]).find(
        (p) => p.value === newProvinceName,
    );

    if (!country || !province) {
        return;
    }

    try {
        const response = await fetch(
            `/api/geo/cities?countryIds=${country.id}&adminCode=${province.id}`,
        );

        if (!response.ok) {
            throw new Error('Failed to fetch');
        }

        const data = await response.json();

        if (data && data.data) {
            cities.value = data.data
                .map((item: any) => ({
                    value: item.name,
                    label: item.name,
                    id: item.id,
                }))
                .sort((a: any, b: any) => a.label.localeCompare(b.label));
        }
    } catch (error) {
        console.error('Failed to fetch cities:', error);
    }
});

watch(selectedCity, (newCityName) => (form.city = newCityName));

type FormKeys =
    | 'name'
    | 'phone_number'
    | 'country'
    | 'province'
    | 'city'
    | 'district'
    | 'address'
    | 'password'
    | 'password_confirmation'
    | 'terms';

const validateField = async (field: FormKeys) => {
    const value = form[field];
    let error = '';

    if (!value && value !== false) {
        error = 'This field is required.';
    } else {
        switch (field) {
            case 'name':
                if (String(value).length < 3) {
                    error = `Name must be at least 3 characters. (got ${String(value).length}).`;
                } else if (!/^[a-zA-Z0-9\s\.\,\'\-]+$/.test(String(value))) {
                    error =
                        "Name contains invalid characters. Only letters, numbers, spaces, and (.) (,) (') (-) are allowed.";
                }

                break;

            case 'phone_number':
                const phone = String(value).trim();
                const digitsOnly = phone.replace(/^\+/, '').replace(/\D/g, '');
                const hasInvalidChars = /[^0-9+\s\-()]/.test(phone);

                if (hasInvalidChars) {
                    error =
                        'Phone number can only contain digits, spaces, -, (, ), and an optional leading (+).';
                } else if (digitsOnly.length === 0) {
                    error = 'Phone number must contain digits.';
                } else if (digitsOnly.length < 7) {
                    error = `Phone number is too short — at least 7 digits required. (got ${digitsOnly.length}).`;
                } else if (digitsOnly.length > 15) {
                    error = `Phone number is too long — at most 15 digits allowed. (got ${digitsOnly.length}).`;
                }

                break;

            case 'password':
                if (String(value).length < 8) {
                    error = `Password must be at least 8 characters. (got ${String(value).length}).`;
                }

                break;

            case 'password_confirmation':
                if (value !== form.password) {
                    error = 'Passwords do not match.';
                }

                break;

            case 'terms':
                if (!value) {
                    error =
                        'You must agree to the Terms of Service and Privacy Policy.';
                }

                break;
        }
    }

    if (error) {
        form.setError(field, error);
    } else {
        form.clearErrors(field);
    }
};

const handleSelection = async (field: FormKeys) => {
    await nextTick();
    validateField(field);
};

const debouncedValidators: Partial<Record<FormKeys, (...args: any[]) => void>> = {};

const debouncedValidate = (field: FormKeys) => {
    if (!debouncedValidators[field]) {
        debouncedValidators[field] = debounce((f: FormKeys) => {
            validateField(f);
        }, 1000);
    }
    
    debouncedValidators[field]!(field);
};

const nextStep = async (fields: FormKeys[]) => {
    let valid = true;

    for (const field of fields) {
        await validateField(field);

        if (form.errors[field]) {
            valid = false;
        }
    }

    if (valid) {
        step.value++;
    }
};

const handleEnter = () => {
    if (step.value === 1) {
        nextStep(['name', 'phone_number']);
    } else if (step.value === 2) {
        nextStep(['country', 'province', 'city', 'district', 'address']);
    } else if (step.value === 3) {
        submit();
    }
};

const submit = async () => {
    await validateField('password');
    await validateField('password_confirmation');
    await validateField('terms');

    if (
        form.errors.password ||
        form.errors.password_confirmation ||
        form.errors.terms
    ) {
        return;
    }

    form.post(`/invitations/${props.token}`, {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Accept Invitation" />

    <form @submit.prevent="submit" novalidate class="flex flex-col gap-6">
        <div class="grid gap-6" @keydown.enter.prevent="handleEnter">
            <div class="mb-2 text-center text-sm text-muted-foreground">
                You are joining
                <strong>{{ invitation.workspace.name }}</strong> as a
                <span class="capitalize">{{ invitation.role }}</span
                >.
            </div>

            <div v-show="step === 1" class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        v-model="form.name"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        placeholder="Full name"
                        @blur="validateField('name')"
                        @input="debouncedValidate('name')"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        :value="invitation.email"
                        disabled
                        :tabindex="2"
                        class="cursor-not-allowed bg-muted text-muted-foreground opacity-70"
                    />
                    <p class="text-xs text-muted-foreground">
                        Locked to this invitation.
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="phone_number">Phone Number</Label>
                    <Input
                        id="phone_number"
                        type="tel"
                        v-model="form.phone_number"
                        required
                        :tabindex="3"
                        autocomplete="tel"
                        placeholder="+1234567890"
                        @blur="validateField('phone_number')"
                        @input="debouncedValidate('phone_number')"
                    />
                    <InputError :message="form.errors.phone_number" />
                </div>

                <div class="mt-2 flex gap-4">
                    <Button
                        type="button"
                        class="w-full"
                        tabindex="4"
                        @click="nextStep(['name', 'phone_number'])"
                    >
                        Next
                    </Button>
                </div>
            </div>

            <div v-show="step === 2" class="grid gap-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="country">Country</Label>
                        <Combobox
                            v-model="selectedCountry"
                            :options="countries"
                            placeholder="Country"
                            empty-text="No country found."
                            @update:modelValue="handleSelection('country')"
                        />
                        <InputError :message="form.errors.country" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="province">Province / State</Label>
                        <Combobox
                            v-model="selectedProvince"
                            :options="provinces"
                            placeholder="Province"
                            empty-text="No province found."
                            :disabled="!selectedCountry"
                            @update:modelValue="handleSelection('province')"
                        />
                        <InputError :message="form.errors.province" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="city">City</Label>
                        <Combobox
                            v-model="selectedCity"
                            :options="cities"
                            placeholder="City"
                            empty-text="No city found."
                            :disabled="!selectedProvince"
                            @update:modelValue="handleSelection('city')"
                        />
                        <InputError :message="form.errors.city" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="district">District</Label>
                        <Input
                            id="district"
                            type="text"
                            v-model="form.district"
                            required
                            :tabindex="7"
                            placeholder="District"
                            @blur="validateField('district')"
                            @input="debouncedValidate('district')"
                        />
                        <InputError :message="form.errors.district" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="address">Street Address</Label>
                    <Input
                        id="address"
                        type="text"
                        v-model="form.address"
                        required
                        :tabindex="8"
                        placeholder="123 Main St"
                        @blur="validateField('address')"
                        @input="debouncedValidate('address')"
                    />
                    <InputError :message="form.errors.address" />
                </div>

                <div class="mt-2 grid grid-cols-2 gap-4">
                    <Button
                        type="button"
                        variant="outline"
                        class="w-full"
                        tabindex="10"
                        @click="step--"
                        >Back</Button
                    >
                    <Button
                        type="button"
                        class="w-full"
                        tabindex="9"
                        @click="
                            nextStep([
                                'country',
                                'province',
                                'city',
                                'district',
                                'address',
                            ])
                        "
                        >Next</Button
                    >
                </div>
            </div>

            <div v-show="step === 3" class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <PasswordInput
                        id="password"
                        v-model="form.password"
                        required
                        :tabindex="9"
                        autocomplete="new-password"
                        placeholder="Password"
                        :passwordrules="passwordRules"
                        @blur="validateField('password')"
                        @input="debouncedValidate('password')"
                    />
                    <InputError :message="form.errors.password" />
                </div>
                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        required
                        :tabindex="10"
                        autocomplete="new-password"
                        placeholder="Confirm password"
                        :passwordrules="passwordRules"
                        @blur="validateField('password_confirmation')"
                        @input="debouncedValidate('password_confirmation')"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <div class="mt-2 grid gap-2">
                    <div class="flex items-center gap-2">
                        <Checkbox
                            id="terms"
                            v-model="form.terms"
                            required
                            @update:modelValue="handleSelection('terms')"
                            :tabindex="11"
                        />
                        <Label for="terms" class="text-sm font-normal">
                            I agree to the
                            <a href="#" class="underline hover:text-primary"
                                >Terms of Service</a
                            >
                            and
                            <a href="#" class="underline hover:text-primary"
                                >Privacy Policy</a
                            >.
                        </Label>
                    </div>
                    <InputError :message="form.errors.terms" />
                </div>

                <div class="mt-2 grid grid-cols-2 gap-4">
                    <Button
                        type="button"
                        variant="outline"
                        class="w-full"
                        tabindex="13"
                        @click="step--"
                        >Back</Button
                    >
                    <Button
                        type="submit"
                        class="w-full"
                        tabindex="12"
                        :disabled="form.processing"
                    >
                        <Spinner v-if="form.processing" class="mr-2" />
                        Create account
                    </Button>
                </div>
            </div>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Already have an account?
            <TextLink
                :href="login()"
                class="underline underline-offset-4"
                :tabindex="14"
                >Log in</TextLink
            >
        </div>
    </form>
</template>
