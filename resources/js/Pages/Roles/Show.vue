<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    role: { type: Object, required: true },
});

const formatDate = (d) => d ? new Date(d).toLocaleString() : '—';
</script>

<template>
    <Head :title="`Role: ${role.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Role: {{ role.name }}</h2>
                <div class="flex gap-2">
                    <Link :href="route('roles.edit', role.id)" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Edit</Link>
                    <Link :href="route('roles.index')" class="rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Back</Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ role.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Icon</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ role.icon || '—' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ role.description || '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1"><span :class="role.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex rounded-full px-2 text-xs font-semibold">{{ role.status }}</span></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Users</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ role.users?.length || 0 }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Permissions</dt>
                                <dd class="mt-1">
                                    <span v-for="p in role.permissions" :key="p.id" class="mb-1 mr-2 inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-800">{{ p.name }}</span>
                                    <span v-if="!role.permissions || !role.permissions.length" class="text-sm text-gray-500">No permissions</span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
