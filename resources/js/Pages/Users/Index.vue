<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    users: { type: Object, required: true },
    roles: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const roleId = ref(props.filters.role_id || '');

const applyFilters = () => {
    router.get(route('users.index'), {
        search: search.value,
        status: status.value,
        role_id: roleId.value,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    search.value = '';
    status.value = '';
    roleId.value = '';
    applyFilters();
};

const deleteUser = (u) => {
    if (confirm(`Delete user ${u.full_name || u.username}?`)) {
        router.delete(route('users.destroy', u.id));
    }
};

const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '—';
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Users</h2>
                <Link :href="route('users.create')" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Add User</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-4">
                            <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Search by name, username, email..." class="rounded-md border-gray-300" />
                            <select v-model="status" @change="applyFilters" class="rounded-md border-gray-300">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <select v-model="roleId" @change="applyFilters" class="rounded-md border-gray-300">
                                <option value="">All Roles</option>
                                <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                            </select>
                            <button @click="resetFilters" class="rounded-md border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50">Reset</button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Name</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Username</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Email</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Role</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Last Login</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="u in users.data" :key="u.id">
                                        <td class="px-3 py-2 text-sm text-gray-900">{{ u.full_name || '—' }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-700">{{ u.username }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-700">{{ u.email }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-700">{{ u.role?.name ?? '—' }}</td>
                                        <td class="px-3 py-2">
                                            <span :class="u.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex rounded-full px-2 text-xs font-semibold">{{ u.status }}</span>
                                        </td>
                                        <td class="px-3 py-2 text-sm text-gray-500">{{ formatDate(u.last_login) }}</td>
                                        <td class="px-3 py-2 text-right text-sm">
                                            <Link :href="route('users.show', u.id)" class="text-indigo-600 hover:underline">View</Link>
                                            <Link :href="route('users.edit', u.id)" class="ml-2 text-indigo-600 hover:underline">Edit</Link>
                                            <button @click="deleteUser(u)" class="ml-2 text-red-600 hover:underline">Delete</button>
                                        </td>
                                    </tr>
                                    <tr v-if="!users.data.length">
                                        <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-500">No users found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-600">Showing {{ users.from || 0 }} to {{ users.to || 0 }} of {{ users.total }}</div>
                            <div class="flex gap-2">
                                <template v-for="link in users.links" :key="link.label">
                                    <Link v-if="link.url" :href="link.url" :class="link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700'" class="rounded border px-3 py-1 text-sm" v-html="link.label" />
                                    <span v-else class="rounded border px-3 py-1 text-sm text-gray-400" v-html="link.label" />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
