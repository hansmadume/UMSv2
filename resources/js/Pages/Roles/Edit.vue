<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    role: { type: Object, required: true },
    permissions: { type: Array, default: () => [] },
    assignedPermissions: { type: Array, default: () => [] },
});

const form = useForm({
    name: props.role.name,
    description: props.role.description || '',
    icon: props.role.icon || '',
    status: props.role.status || 'active',
    permissions: [...props.assignedPermissions],
});

const togglePermission = (id) => {
    if (form.permissions.includes(id)) {
        form.permissions = form.permissions.filter(p => p !== id);
    } else {
        form.permissions.push(id);
    }
};

const submit = () => {
    form.put(route('roles.update', props.role.id));
};
</script>

<template>
    <Head title="Edit Role" />
    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>Edit Role: {{ role.name }}</h2>
            </div>
        </template>
        <div class="user-roles">
            <div class="mui-card">
                <h3>Edit Role</h3>
                <form @submit.prevent="submit" class="user-form">
                    <div class="form-grid">
                        <div class="mui-input-group">
                            <InputLabel for="name" value="Role Name" />
                            <TextInput id="name" v-model="form.name" required />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="mui-input-group">
                            <InputLabel for="description" value="Description" />
                            <TextInput id="description" v-model="form.description" />
                            <InputError :message="form.errors.description" />
                        </div>
                        <div class="mui-input-group">
                            <select id="status" v-model="form.status" required class="mui-input">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <InputLabel for="status" value="Status" />
                        </div>
                        <div class="mui-input-group">
                            <InputLabel for="icon" value="Icon" />
                            <TextInput id="icon" v-model="form.icon" />
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
                            <span class="material-icons">save</span>
                            Update Role
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>