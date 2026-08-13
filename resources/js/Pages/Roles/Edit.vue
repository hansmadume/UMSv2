<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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

const permissionGroups = computed(() => {
    const groups = {};
    const order = [
        'Dashboard', 'Profile', 'Users', 'Roles', 'Permissions',
        'Tickets', 'Staff', 'Notifications',
        'Audit Logs', 'Settings', 'Support'
    ];

    props.permissions.forEach(p => {
        const category = p.slug.split('.')[0];
        const label = category.charAt(0).toUpperCase() + category.slice(1);
        if (!groups[label]) {
            groups[label] = [];
        }
        groups[label].push(p);
    });

    return Object.keys(groups)
        .sort((a, b) => {
            const indexA = order.indexOf(a);
            const indexB = order.indexOf(b);
            if (indexA !== -1 && indexB !== -1) return indexA - indexB;
            if (indexA !== -1) return -1;
            if (indexB !== -1) return 1;
            return a.localeCompare(b);
        })
        .map(category => ({
            category,
            permissions: groups[category],
        }));
});
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
                            <template v-for="group in permissionGroups" :key="group.category">
                                <div class="permission-group">
                                    <div class="permission-group-header">{{ group.category }}</div>
                                    <label v-for="p in group.permissions" :key="p.id" class="permission-checkbox">
                                        <input
                                            type="checkbox"
                                            :value="p.id"
                                            :checked="form.permissions.includes(p.id)"
                                            @change="togglePermission(p.id)"
                                        />
                                        <span>{{ p.description || p.name }}</span>
                                    </label>
                                </div>
                            </template>
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

<style scoped>
.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
}

.permission-group {
    background: var(--black-tertiary);
    border: 1px solid var(--black-border);
    border-radius: 8px;
    padding: 12px;
}

.permission-group-header {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary);
    margin-bottom: 8px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--black-border);
}

.permission-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 0;
    cursor: pointer;
    font-size: 0.9rem;
}

.permission-checkbox input[type="checkbox"] {
    accent-color: var(--primary);
}
</style>