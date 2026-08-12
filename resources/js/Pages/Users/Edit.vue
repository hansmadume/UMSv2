<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: { type: Object, required: true },
    roles: { type: Array, default: () => [] },
});

const form = useForm({
    full_name: props.user.full_name || '',
    username: props.user.username,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role_id: props.user.role_id,
    status: props.user.status || 'active',
    contact_number: props.user.contact_number || '',
    address: props.user.address || '',
});

const submit = () => {
    form.put(route('users.update', props.user.id));
};
</script>

<template>
    <Head title="Edit User" />
    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>Edit User: {{ user.username }}</h2>
            </div>
        </template>
        <div class="user-management">
            <div class="mui-card">
                <form @submit.prevent="submit" class="user-form">
                    <div class="mui-input-group">
                        <TextInput id="full_name" v-model="form.full_name" required />
                        <InputLabel for="full_name" value="Full Name" />
                        <InputError :message="form.errors.full_name" />
                    </div>
                    <div class="form-grid">
                        <div class="mui-input-group">
                            <TextInput id="username" v-model="form.username" required />
                            <InputLabel for="username" value="Username" />
                            <InputError :message="form.errors.username" />
                        </div>
                        <div class="mui-input-group">
                            <TextInput id="email" type="email" v-model="form.email" required />
                            <InputLabel for="email" value="Email" />
                            <InputError :message="form.errors.email" />
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="mui-input-group">
                            <TextInput id="password" type="password" v-model="form.password" />
                            <InputLabel for="password" value="New Password (optional)" />
                            <InputError :message="form.errors.password" />
                        </div>
                        <div class="mui-input-group">
                            <TextInput id="password_confirmation" type="password" v-model="form.password_confirmation" />
                            <InputLabel for="password_confirmation" value="Confirm Password" />
                            <InputError :message="form.errors.password_confirmation" />
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="mui-input-group">
                            <select id="role_id" v-model="form.role_id" required class="mui-input">
                                <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                            </select>
                            <InputLabel for="role_id" value="Role" />
                            <InputError :message="form.errors.role_id" />
                        </div>
                        <div class="mui-input-group">
                            <select id="status" v-model="form.status" required class="mui-input">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <InputLabel for="status" value="Status" />
                            <InputError :message="form.errors.status" />
                        </div>
                    </div>
                    <div class="mui-input-group">
                        <TextInput id="contact_number" v-model="form.contact_number" />
                        <InputLabel for="contact_number" value="Contact Number" />
                        <InputError :message="form.errors.contact_number" />
                    </div>
                    <div class="mui-input-group">
                        <textarea id="address" v-model="form.address" rows="3" class="mui-input"></textarea>
                        <InputLabel for="address" value="Address" />
                        <InputError :message="form.errors.address" />
                    </div>
                    <div class="form-actions">
                        <Link :href="route('users.index')" class="mui-btn mui-btn-outlined">Cancel</Link>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Save</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
