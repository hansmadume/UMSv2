<script setup>
import { ref } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';
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
    identifier: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const showPassword = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="login-alert login-alert-info" role="alert">
            {{ status }}
        </div>

        <div v-if="$page.props.flash?.login_notice" class="login-alert login-alert-info" role="alert">
            {{ $page.props.flash.login_notice }}
        </div>

        <form @submit.prevent="submit" class="login-form">
            <div class="mui-input-group">
                <TextInput
                    id="identifier"
                    type="text"
                    v-model="form.identifier"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputLabel for="identifier" value="Email Address or Username" />
                <InputError :message="form.errors.identifier" />
            </div>

            <div class="mui-input-group">
                <input
                    :type="showPassword ? 'text' : 'password'"
                    id="password"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    class="mui-input"
                />
                <InputLabel for="password" value="Password" />
                <InputError :message="form.errors.password" />
                <button type="button" class="password-toggle" @click="togglePasswordVisibility" aria-label="Toggle password visibility">
                    <span class="material-icons">{{ showPassword ? 'visibility_off' : 'visibility' }}</span>
                </button>
            </div>

            <div class="login-options">
                <Checkbox name="remember" v-model:checked="form.remember" />
                <span class="checkbox-text">Remember me</span>
                <Link :href="route('password.request')" class="forgot-link">Forgot password?</Link>
            </div>

            <PrimaryButton class="login-btn" :disabled="form.processing">
                <span class="material-icons">login</span>
                Sign In
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>

<style scoped>
.login-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

.checkbox-text {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-left: 8px;
}

.forgot-link {
    font-size: 0.8rem;
    color: var(--green-light);
    transition: color var(--transition-fast);
}

.forgot-link:hover {
    color: var(--green-lighter);
    text-decoration: underline;
}

.login-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.password-toggle {
    position: absolute;
    right: 12px;
    bottom: 8px;
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    padding: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: color var(--transition-fast), background-color var(--transition-fast);
}

.password-toggle:hover {
    color: var(--text-primary);
    background-color: rgba(255, 255, 255, 0.06);
}

.password-toggle .material-icons {
    font-size: 20px;
}
</style>