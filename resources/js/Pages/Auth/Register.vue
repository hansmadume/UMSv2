<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    full_name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <div class="mui-input-group">
                <InputLabel for="full_name" value="Full Name" />

                <TextInput
                    id="full_name"
                    type="text"
                    v-model="form.full_name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError :message="form.errors.full_name" />
            </div>

            <div class="mui-input-group">
                <InputLabel for="username" value="Username" />

                <TextInput
                    id="username"
                    type="text"
                    v-model="form.username"
                    required
                    autocomplete="username"
                />

                <InputError :message="form.errors.username" />
            </div>

            <div class="mui-input-group">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError :message="form.errors.email" />
            </div>

            <div class="mui-input-group">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password" />
            </div>

            <div class="mui-input-group">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    :message="form.errors.password_confirmation"
                />
            </div>

            <PrimaryButton
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                Register
            </PrimaryButton>

            <Link :href="route('login')">
                Already registered?
            </Link>
        </form>
    </GuestLayout>
</template>
