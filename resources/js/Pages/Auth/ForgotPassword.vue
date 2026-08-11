<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <form @submit.prevent="submit" class="login-form">
            <div v-if="status" class="login-alert login-alert-info">
                {{ status }}
            </div>

            <div class="mui-input-group">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError :message="form.errors.email" />
            </div>

            <div class="login-options" style="justify-content: flex-start;">
                <Link :href="route('login')" class="mui-btn mui-btn-outlined mui-btn-sm">
                    <span class="material-icons">arrow_back</span>
                    Back to Login
                </Link>
            </div>

            <PrimaryButton
                class="login-btn"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                <span class="material-icons">mail</span>
                Email Password Reset Link
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>

<style scoped>
.login-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
