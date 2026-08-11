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
            <div class="section-header">
                <h2>Dashboard</h2>
            </div>
        </template>

        <div class="dashboard">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons">people</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">{{ stats.total_users ?? 0 }}</div>
                        <div class="stat-label">Total Active Users</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons">admin_panel_settings</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">{{ stats.total_roles ?? 0 }}</div>
                        <div class="stat-label">Active Roles</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons">history</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">{{ stats.total_logs ?? 0 }}</div>
                        <div class="stat-label">Total Audit Logs</div>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="mui-card dashboard-card">
                    <div class="card-header">
                        <h3>Recent Users</h3>
                        <Link v-if="$page.props.auth.user?.is_admin || $page.props.auth.user?.is_manager" :href="route('users.index')" class="mui-btn mui-btn-outlined mui-btn-sm">Manage users &rarr;</Link>
                    </div>
                    <div class="activity-list">
                        <div v-for="u in recentUsers" :key="u.id" class="activity-item">
                            <div class="activity-info">
                                <div class="activity-text"><strong>{{ u.full_name || u.username }}</strong></div>
                                <div class="activity-time">{{ u.email }}</div>
                            </div>
                            <div class="activity-time">{{ u.role?.name ?? 'No role' }}</div>
                        </div>
                        <div v-if="!recentUsers.length" class="activity-item">
                            <div class="activity-info">
                                <div class="activity-text">No users yet.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mui-card dashboard-card">
                    <div class="card-header">
                        <h3>Recent Activity</h3>
                        <Link v-if="$page.props.auth.user?.is_admin" :href="route('audit-logs.index')" class="mui-btn mui-btn-outlined mui-btn-sm">View all logs &rarr;</Link>
                    </div>
                    <div class="activity-list">
                        <div v-for="log in recentLogs" :key="log.id" class="activity-item">
                            <div class="activity-icon">
                                <span class="material-icons">info</span>
                            </div>
                            <div class="activity-info">
                                <div class="activity-text"><strong>{{ log.action }}</strong></div>
                                <div class="activity-time">by {{ log.user_name }} - {{ formatDate(log.created_at) }}</div>
                            </div>
                        </div>
                        <div v-if="!recentLogs.length" class="activity-item">
                            <div class="activity-info">
                                <div class="activity-text">No recent activity.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
