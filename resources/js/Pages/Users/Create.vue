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
    role_id: props.roles[0]?.id ?? '',
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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Create User</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <InputLabel for="full_name" value="Full Name" />
                                <TextInput id="full_name" v-model="form.full_name" class="mt-1 block w-full" required />
                                <InputError :message="form.errors.full_name" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <InputLabel for="username" value="Username" />
                                    <TextInput id="username" v-model="form.username" class="mt-1 block w-full" required />
                                    <InputError :message="form.errors.username" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel for="email" value="Email" />
                                    <TextInput id="email" type="email" v-model="form.email" class="mt-1 block w-full" required />
                                    <InputError :message="form.errors.email" class="mt-2" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <InputLabel for="password" value="Password" />
                                    <TextInput id="password" type="password" v-model="form.password" class="mt-1 block w-full" required />
                                    <InputError :message="form.errors.password" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel for="password_confirmation" value="Confirm Password" />
                                    <TextInput id="password_confirmation" type="password" v-model="form.password_confirmation" class="mt-1 block w-full" required />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <InputLabel for="role_id" value="Role" />
                                    <select id="role_id" v-model="form.role_id" class="mt-1 block w-full rounded-md border-gray-300" required>
                                        <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                                    </select>
                                    <InputError :message="form.errors.role_id" class="mt-2" />
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
                                <InputLabel for="contact_number" value="Contact Number" />
                                <TextInput id="contact_number" v-model="form.contact_number" class="mt-1 block w-full" />
                            </div>

                            <div>
                                <InputLabel for="address" value="Address" />
                                <textarea id="address" v-model="form.address" rows="3" class="mt-1 block w-full rounded-md border-gray-300" />
                            </div>

                            <div class="flex items-center justify-end gap-3">
                                <Link :href="route('users.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Create</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
