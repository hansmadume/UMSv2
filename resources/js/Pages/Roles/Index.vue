<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    roles: { type: Object, required: true },
});

const deleteRole = (r) => {
    if (confirm(`Delete role "${r.name}"?`)) {
        router.delete(route('roles.destroy', r.id));
    }
};
</script>

<template>
    <Head title="Roles" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Roles</h2>
                <Link :href="route('roles.create')" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Add Role</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Name</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Description</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Users</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Permissions</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="r in roles.data" :key="r.id">
                                        <td class="px-3 py-2 text-sm text-gray-900">{{ r.name }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-700">{{ r.description || '—' }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-700">{{ r.users_count }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-700">{{ r.permissions_count }}</td>
                                        <td class="px-3 py-2">
                                            <span :class="r.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex rounded-full px-2 text-xs font-semibold">{{ r.status }}</span>
                                        </td>
                                        <td class="px-3 py-2 text-right text-sm">
                                            <Link :href="route('roles.show', r.id)" class="text-indigo-600 hover:underline">View</Link>
                                            <Link :href="route('roles.edit', r.id)" class="ml-2 text-indigo-600 hover:underline">Edit</Link>
                                            <button @click="deleteRole(r)" class="ml-2 text-red-600 hover:underline">Delete</button>
                                        </td>
                                    </tr>
                                    <tr v-if="!roles.data.length">
                                        <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">No roles found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-600">Showing {{ roles.from || 0 }} to {{ roles.to || 0 }} of {{ roles.total }}</div>
                            <div class="flex gap-2">
                                <template v-for="link in roles.links" :key="link.label">
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
