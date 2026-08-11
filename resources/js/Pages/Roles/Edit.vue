<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Role: {{ role.name }}</h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <InputLabel for="name" value="Name" />
                                <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="description" value="Description" />
                                <TextInput id="description" v-model="form.description" class="mt-1 block w-full" />
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <InputLabel for="icon" value="Icon" />
                                    <TextInput id="icon" v-model="form.icon" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <InputLabel for="status" value="Status" />
                                    <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <InputLabel value="Permissions" />
                                <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    <label v-for="p in permissions" :key="p.id" class="flex items-center gap-2 rounded border border-gray-200 px-3 py-2">
                                        <input type="checkbox" :value="p.id" :checked="form.permissions.includes(p.id)" @change="togglePermission(p.id)" />
                                        <span class="text-sm">{{ p.name }}</span>
                                    </label>
                                </div>
                                <InputError :message="form.errors.permissions" class="mt-2" />
                            </div>
                            <div class="flex items-center justify-end gap-3">
                                <Link :href="route('roles.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Save</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
