<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    logs: { type: Object, required: true },
    actions: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const action = ref(props.filters.action || '');

const applyFilters = () => {
    router.get(route('audit-logs.index'), {
        search: search.value,
        action: action.value,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    search.value = '';
    action.value = '';
    applyFilters();
};

const formatDate = (d) => d ? new Date(d).toLocaleString() : '—';
</script>

<template>
    <Head title="Audit Logs" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Audit Logs</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-4">
                            <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Search user, action, IP..." class="rounded-md border-gray-300" />
                            <select v-model="action" @change="applyFilters" class="rounded-md border-gray-300">
                                <option value="">All Actions</option>
                                <option v-for="a in actions" :key="a" :value="a">{{ a }}</option>
                            </select>
                            <button @click="resetFilters" class="rounded-md border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50">Reset</button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">When</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">User</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Action</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">IP Address</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="log in logs.data" :key="log.id">
                                        <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-700">{{ formatDate(log.created_at) }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">{{ log.user_name || '—' }}</td>
                                        <td class="px-3 py-2"><span class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold text-gray-800">{{ log.action }}</span></td>
                                        <td class="px-3 py-2 text-sm text-gray-700">{{ log.ip_address || '—' }}</td>
                                    </tr>
                                    <tr v-if="!logs.data.length">
                                        <td colspan="4" class="px-3 py-6 text-center text-sm text-gray-500">No audit logs found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-600">Showing {{ logs.from || 0 }} to {{ logs.to || 0 }} of {{ logs.total }}</div>
                            <div class="flex gap-2">
                                <template v-for="link in logs.links" :key="link.label">
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
