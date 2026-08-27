<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import Combobox from '@/components/Combobox.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Checkbox } from '@/components/ui/checkbox';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: 'Create an account',
        description: 'Enter your details below to create your account',
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

onMounted(async () => {
    try {
        const response = await fetch('/api/geo/countries');
        if (!response.ok) throw new Error('Failed to fetch');
        
        const data = await response.json();
        if (data && data.data) {
            countries.value = data.data.map((item: any) => ({ value: item.name, label: item.name, id: item.code })).sort((a: any, b: any) => a.label.localeCompare(b.label));
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

    if (!newCountryName) return;

    const country = (countries.value as any[]).find(c => c.value === newCountryName);
    if (!country) return;

    try {
        const response = await fetch(`/api/geo/countries/${country.id}/regions`);
        if (!response.ok) throw new Error('Failed to fetch');
        
        const data = await response.json();
        if (data && data.data) {
            provinces.value = data.data.map((item: any) => ({ value: item.name, label: item.name, id: item.isoCode })).sort((a: any, b: any) => a.label.localeCompare(b.label));
        }
    } catch (error) {
        console.error('Failed to fetch provinces:', error);
    }
});

watch(selectedProvince, async (newProvinceName) => {
    selectedCity.value = '';
    selectedDistrict.value = '';
    cities.value = [];

    if (!newProvinceName || !selectedCountry.value) return;

    const country = (countries.value as any[]).find(c => c.value === selectedCountry.value);
    const province = (provinces.value as any[]).find(p => p.value === newProvinceName);
    
    if (!country || !province) return;

    try {
        const response = await fetch(`/api/geo/cities?countryIds=${country.id}&adminCode=${province.id}`);
        if (!response.ok) throw new Error('Failed to fetch');
        
        const data = await response.json();
        if (data && data.data) {
            cities.value = data.data.map((item: any) => ({ value: item.name, label: item.name, id: item.id })).sort((a: any, b: any) => a.label.localeCompare(b.label));
        }
    } catch (error) {
        console.error('Failed to fetch cities:', error);
    }
});

const nextStep = (validate: any, fields: string[]) => {
    validate({
        only: fields,
        onSuccess: () => {
            step.value++;
        },
    });
};

const handleEnter = (validate: any, submit: any) => {
    if (step.value === 1) {
        nextStep(validate, ['name', 'email', 'phone_number']);
    } else if (step.value === 2) {
        nextStep(validate, ['country', 'province', 'city', 'district', 'address']);
    } else if (step.value === 3) {
        submit();
    }
};

const validateTerms = (validate: any) => {
    setTimeout(() => validate('terms'), 10);
};
</script>

<template>
    <Head title="Register" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing, validate, submit }"
        novalidate
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6" @keydown.enter.prevent="handleEnter(validate, submit)">
            <!-- Step 1 -->
            <div v-show="step === 1" class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="Full name"
                        @blur="validate('name')"
                        @input="validate('name')"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                        @blur="validate('email')"
                        @input="validate('email')"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="phone_number">Phone Number</Label>
                    <Input
                        id="phone_number"
                        type="tel"
                        required
                        :tabindex="3"
                        autocomplete="tel"
                        name="phone_number"
                        placeholder="+1234567890"
                        @blur="validate('phone_number')"
                        @input="validate('phone_number')"
                    />
                    <InputError :message="errors.phone_number" />
                </div>

                <div class="flex gap-4 mt-2">
                    <Button
                        type="button"
                        class="w-full"
                        tabindex="4"
                        @click="nextStep(validate, ['name', 'email', 'phone_number'])"
                    >
                        Next
                    </Button>
                </div>
            </div>

                        <!-- Step 2 -->
            <div v-show="step === 2" class="grid gap-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="country">Country</Label>
                        <Combobox
                            v-model="selectedCountry"
                            :options="countries"
                            placeholder="Country"
                            empty-text="No country found."
                            name="country"
                            @blur="validate('country')"
                        />
                        <InputError :message="errors.country" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="province">Province / State</Label>
                        <Combobox
                            v-model="selectedProvince"
                            :options="provinces"
                            placeholder="Province"
                            empty-text="No province found."
                            :disabled="!selectedCountry"
                            name="province"
                            @blur="validate('province')"
                        />
                        <InputError :message="errors.province" />
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
                            name="city"
                            @blur="validate('city')"
                        />
                        <InputError :message="errors.city" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="district">District</Label>
                        <Input
                            id="district"
                            type="text"
                            required
                            :tabindex="7"
                            name="district"
                            placeholder="District"
                            @blur="validate('district')"
                            @input="validate('district')"
                        />
                        <InputError :message="errors.district" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="address">Street Address</Label>
                    <Input
                        id="address"
                        type="text"
                        required
                        :tabindex="8"
                        name="address"
                        placeholder="123 Main St"
                        @blur="validate('address')"
                        @input="validate('address')"
                    />
                    <InputError :message="errors.address" />
                </div>

                <div class="grid grid-cols-2 gap-4 mt-2">
                    <Button
                        type="button"
                        variant="outline"
                        class="w-full"
                        tabindex="10"
                        @click="step--"
                    >
                        Back
                    </Button>
                    <Button
                        type="button"
                        class="w-full"
                        tabindex="9"
                        @click="nextStep(validate, ['country', 'province', 'city', 'district', 'address'])"
                    >
                        Next
                    </Button>
                </div>
            </div>

            <!-- Step 3 -->
            <div v-show="step === 3" class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <PasswordInput
                        id="password"
                        required
                        :tabindex="9"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Password"
                        :passwordrules="passwordRules"
                        @blur="validate('password')"
                        @keyup="validate('password')"
                    />
                    <InputError :message="errors.password" />
                </div>
                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        required
                        :tabindex="10"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                        :passwordrules="passwordRules"
                        @blur="validate('password_confirmation')"
                        @keyup="validate('password_confirmation')"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <div class="mt-2 grid gap-2">
                    <div class="flex items-center gap-2">
                        <Checkbox
                            id="terms"
                            name="terms"
                            required
                            :tabindex="11"
                            value="1"
                            @update:checked="() => validateTerms(validate)"
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
                    <InputError :message="errors.terms" />
                </div>

                <div class="grid grid-cols-2 gap-4 mt-2">
                    <Button
                        type="button"
                        variant="outline"
                        class="w-full"
                        tabindex="13"
                        @click="step--"
                    >
                        Back
                    </Button>
                    <Button
                        type="submit"
                        class="w-full"
                        tabindex="12"
                        :disabled="processing"
                        data-test="register-user-button"
                    >
                        <Spinner v-if="processing" />
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
    </Form>
</template>
