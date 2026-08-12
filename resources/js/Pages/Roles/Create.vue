<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    permissions: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    description: '',
    icon: '',
    status: 'active',
    permissions: [],
});

const togglePermission = (id) => {
    if (form.permissions.includes(id)) {
        form.permissions = form.permissions.filter(p => p !== id);
    } else {
        form.permissions.push(id);
    }
};

const submit = () => {
    form.post(route('roles.store'));
};
</script>

<template>
    <Head title="Create Role" />
    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>Create Role</h2>
            </div>
        </template>
        <div class="user-roles">
            <div class="mui-card">
                <h3>Add Role</h3>
                <form @submit.prevent="submit" class="user-form">
                    <div class="form-grid">
                        <div class="mui-input-group">
                            <TextInput id="name" v-model="form.name" required />
                            <InputLabel for="name" value="Role Name" />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="mui-input-group">
                            <TextInput id="description" v-model="form.description" />
                            <InputLabel for="description" value="Description" />
                            <InputError :message="form.errors.description" />
                        </div>
                        <div class="mui-input-group">
                            <select id="status" v-model="form.status" required class="mui-input">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <InputLabel for="status" value="Status" />
                            <InputError :message="form.errors.status" />
                        </div>
                        <div class="mui-input-group">
                            <TextInput id="icon" v-model="form.icon" />
                            <InputLabel for="icon" value="Icon" />
                            <InputError :message="form.errors.icon" />
                        </div>
                    </div>

                    <div v-if="permissions.length" class="mui-input-group">
                        <label class="mui-label" style="position: static; display: block; margin-bottom: 12px; font-size:0.85rem; color:var(--text-secondary);">Permissions</label>
                        <div class="permissions-grid">
                            <label v-for="p in permissions" :key="p.id" class="permission-checkbox">
                                <input
                                    type="checkbox"
                                    :value="p.id"
                                    :checked="form.permissions.includes(p.id)"
                                    @change="togglePermission(p.id)"
                                />
                                <span>{{ p.name }}</span>
                            </label>
                        </div>
                        <InputError :message="form.errors.permissions" />
                    </div>

                    <div class="form-actions">
                        <Link :href="route('roles.index')" class="mui-btn mui-btn-outlined">Cancel</Link>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            <span class="material-icons">add</span>
                            Add Role
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>