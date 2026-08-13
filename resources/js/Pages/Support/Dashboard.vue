<template>
    <Head title="Support Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>Support Dashboard</h2>
            </div>
        </template>

        <div class="support-dashboard">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons">inbox</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">{{ stats.open_tickets ?? 0 }}</div>
                        <div class="stat-label">Open Tickets</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons">assignment_ind</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">{{ stats.my_assigned_tickets ?? 0 }}</div>
                        <div class="stat-label">My Assigned Tickets</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons">priority_high</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">{{ stats.high_urgent_tickets ?? 0 }}</div>
                        <div class="stat-label">High / Urgent Tickets</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons">hourglass_empty</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">{{ stats.in_progress_tickets ?? 0 }}</div>
                        <div class="stat-label">In Progress</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons">check_circle</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">{{ stats.resolved_today ?? 0 }}</div>
                        <div class="stat-label">Resolved Today</div>
                    </div>
                </div>
            </div>

            <div class="mui-card dashboard-card">
                <div class="card-header">
                    <h3>Recently Updated Tickets</h3>
                </div>
                <div class="activity-list">
                    <div v-for="ticket in recentlyUpdated" :key="ticket.id" class="activity-item">
                        <div class="activity-info">
                            <div class="activity-text">
                                <strong>#{{ ticket.id }}</strong> — {{ ticket.username || 'Anonymous' }}
                            </div>
                            <div class="activity-time">{{ ticket.email }}</div>
                        </div>
                        <div class="activity-meta">
                            <span :class="['status-badge', ticket.status]">{{ ticket.status }}</span>
                            <span class="priority-badge" :class="ticket.priority">{{ ticket.priority }}</span>
                        </div>
                    </div>
                    <div v-if="!recentlyUpdated.length" class="activity-item">
                        <div class="activity-info">
                            <div class="activity-text">No tickets yet.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    recentlyUpdated: { type: Array, default: () => [] },
});
</script>

<style scoped>
.support-dashboard {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.stat-card {
    background: var(--card-bg, #fff);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(25, 118, 210, 0.1);
    color: #1976d2;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon .material-icons {
    font-size: 24px;
}

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary, #1f2937);
}

.stat-label {
    font-size: 0.85rem;
    color: var(--text-secondary, #6b7280);
}

.dashboard-card {
    padding: 20px;
}

.activity-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-text {
    font-size: 0.95rem;
    color: var(--text-primary, #1f2937);
}

.activity-time {
    font-size: 0.8rem;
    color: var(--text-secondary, #6b7280);
    margin-top: 2px;
}

.activity-meta {
    display: flex;
    align-items: center;
    gap: 8px;
}

.priority-badge {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 4px;
}

.priority-badge.low {
    background: #e3f2fd;
    color: #1565c0;
}

.priority-badge.medium {
    background: #fff3e0;
    color: #ef6c00;
}

.priority-badge.high {
    background: #fce4ec;
    color: #c2185b;
}

.priority-badge.urgent {
    background: #ffebee;
    color: #b71c1c;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .activity-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}
</style>
