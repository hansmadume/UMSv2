<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    recentUsers: { type: Array, default: () => [] },
    recentLogs: { type: Array, default: () => [] },
});

const formatDate = (d) => d ? new Date(d).toLocaleString() : '';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">Total Active Users</div>
                            <div class="mt-2 text-3xl font-semibold text-gray-900">{{ stats.total_users ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">Active Roles</div>
                            <div class="mt-2 text-3xl font-semibold text-gray-900">{{ stats.total_roles ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">Total Audit Logs</div>
                            <div class="mt-2 text-3xl font-semibold text-gray-900">{{ stats.total_logs ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Users</h3>
                            <ul class="mt-4 divide-y divide-gray-200">
                                <li v-for="u in recentUsers" :key="u.id" class="flex items-center justify-between py-3">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ u.full_name || u.username }}</div>
                                        <div class="text-xs text-gray-500">{{ u.email }}</div>
                                    </div>
                                    <div class="text-xs text-gray-500">{{ u.role?.name ?? 'No role' }}</div>
                                </li>
                                <li v-if="!recentUsers.length" class="py-3 text-sm text-gray-500">No users yet.</li>
                            </ul>
                            <Link v-if="$page.props.auth.user?.is_admin || $page.props.auth.user?.is_manager" :href="route('users.index')" class="mt-4 inline-block text-sm text-indigo-600 hover:underline">Manage users &rarr;</Link>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                            <ul class="mt-4 divide-y divide-gray-200">
                                <li v-for="log in recentLogs" :key="log.id" class="py-3">
                                    <div class="text-sm font-medium text-gray-900">{{ log.action }}</div>
                                    <div class="text-xs text-gray-500">by {{ log.user_name }} - {{ formatDate(log.created_at) }}</div>
                                </li>
                                <li v-if="!recentLogs.length" class="py-3 text-sm text-gray-500">No recent activity.</li>
                            </ul>
                            <Link v-if="$page.props.auth.user?.is_admin" :href="route('audit-logs.index')" class="mt-4 inline-block text-sm text-indigo-600 hover:underline">View all logs &rarr;</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
