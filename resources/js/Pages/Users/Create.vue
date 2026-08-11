<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    roles: { type: Array, default: () => [] },
});

const form = useForm({
    full_name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: '',
    status: 'active',
    contact_number: '',
    address: '',
});

const submit = () => {
    form.post(route('users.store'));
};
</script>

<template>
    <Head title="Create User" />

    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>Create User</h2>
            </div>
        </template>

        <div class="user-management">
            <div class="mui-card">
                <form @submit.prevent="submit" class="user-form">
                    <div class="mui-input-group">
                        <InputLabel for="full_name" value="Full Name" />
                        <TextInput id="full_name" v-model="form.full_name" required />
                        <InputError :message="form.errors.full_name" />
                    </div>

                    <div class="form-grid">
                        <div class="mui-input-group">
                            <InputLabel for="username" value="Username" />
                            <TextInput id="username" v-model="form.username" required />
                            <InputError :message="form.errors.username" />
                        </div>
                        <div class="mui-input-group">
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" type="email" v-model="form.email" required />
                            <InputError :message="form.errors.email" />
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="mui-input-group">
                            <InputLabel for="password" value="Password" />
                            <TextInput id="password" type="password" v-model="form.password" required />
                            <InputError :message="form.errors.password" />
                        </div>
                        <div class="mui-input-group">
                            <InputLabel for="password_confirmation" value="Confirm Password" />
                            <TextInput id="password_confirmation" type="password" v-model="form.password_confirmation" required />
                            <InputError :message="form.errors.password_confirmation" />
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="mui-input-group">
                            <InputLabel for="role_id" value="Role" />
                            <div class="mui-select-group">
                                <select id="role_id" v-model="form.role_id" required class="mui-select">
                                    <option value="">Select a role</option>
                                    <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                                </select>
                            </div>
                            <InputError :message="form.errors.role_id" />
                        </div>
                        <div class="mui-input-group">
                            <InputLabel for="status" value="Status" />
                            <div class="mui-select-group">
                                <select id="status" v-model="form.status" required class="mui-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mui-input-group">
                        <InputLabel for="contact_number" value="Contact Number" />
                        <TextInput id="contact_number" v-model="form.contact_number" />
                    </div>

                    <div class="mui-input-group">
                        <InputLabel for="address" value="Address" />
                        <textarea id="address" v-model="form.address" rows="3" class="mui-input"></textarea>
                    </div>

                    <div class="form-actions">
                        <Link :href="route('users.index')" class="mui-btn mui-btn-outlined">Cancel</Link>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Create</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
