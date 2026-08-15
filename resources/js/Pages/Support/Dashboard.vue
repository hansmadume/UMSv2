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
                        <span class="material-icons" aria-hidden="true">inbox</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">{{ stats.open_tickets ?? 0 }}</div>
                        <div class="stat-label">Open Tickets</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons" aria-hidden="true">assignment_ind</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">{{ stats.my_assigned_tickets ?? 0 }}</div>
                        <div class="stat-label">My Assigned Tickets</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons" aria-hidden="true">priority_high</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">{{ stats.high_urgent_tickets ?? 0 }}</div>
                        <div class="stat-label">High / Urgent Tickets</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons" aria-hidden="true">hourglass_empty</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">{{ stats.in_progress_tickets ?? 0 }}</div>
                        <div class="stat-label">In Progress</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons" aria-hidden="true">check_circle</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">{{ stats.resolved_today ?? 0 }}</div>
                        <div class="stat-label">Resolved Today</div>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Recently Updated Tickets</h3>
                </div>
                <div class="activity-list">
                    <div v-for="ticket in recentlyUpdated" :key="ticket.id" class="activity-item">
                        <div class="activity-info">
                            <div class="activity-text">
                                <strong>#{{ ticket.ticket_number }}</strong> — {{ ticket.username || 'Anonymous' }}
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
    background: var(--card-bg);
    border: 1px solid var(--black-border);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--shadow-sm);
    transition: background-color var(--transition-fast), border-color var(--transition-fast), transform var(--transition-fast), box-shadow var(--transition-fast);
}

.stat-card:hover {
    border-color: var(--green-primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(0, 200, 83, 0.12);
    color: var(--green-light);
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
    gap: 4px;
    min-width: 0;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.2;
}

.stat-label {
    font-size: 0.85rem;
    color: var(--text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dashboard-card {
    background: var(--card-bg);
    border: 1px solid var(--black-border);
    border-radius: 12px;
    padding: 20px;
    box-shadow: var(--shadow-sm);
    /* Override global dashboard.css .dashboard-card { padding: 0; overflow: hidden; } */
    overflow: visible;
}

.dashboard-card .card-header {
    /* Override global dashboard.css .dashboard-card .card-header styles */
    padding: 0 0 16px;
    border-bottom: 1px solid var(--black-border);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.dashboard-card .card-header h3 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.activity-list {
    /* Override global dashboard.css .activity-list { padding: 8px 0; } */
    padding: 0;
}

.activity-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--black-border);
    /* Override global dashboard.css .activity-item { gap: 14px; align-items: flex-start; } */
    gap: 16px;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-item:hover {
    background-color: var(--black-tertiary);
}

.activity-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
    flex: 1;
}

.activity-text {
    font-size: 0.95rem;
    color: var(--text-secondary);
    line-height: 1.4;
}

.activity-text strong {
    font-weight: 600;
    color: var(--text-primary);
}

.activity-time {
    font-size: 0.8rem;
    color: var(--text-muted);
    white-space: nowrap;
}

.activity-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    flex-wrap: wrap;
}

/* Status badges */
.status-badge {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    padding: 4px 10px;
    border-radius: 4px;
    white-space: nowrap;
    display: inline-block;
}

.status-badge.open {
    background: rgba(0, 200, 83, 0.12);
    color: var(--green-light);
}

.status-badge.in_progress {
    background: rgba(255, 183, 77, 0.12);
    color: var(--warning);
}

.status-badge.resolved {
    background: rgba(0, 200, 83, 0.14);
    color: var(--green-lighter);
}

.status-badge.closed {
    background: #e5e7eb;
    color: #4b5563;
}

/* Priority badges */
.priority-badge {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    padding: 2px 10px;
    border-radius: 4px;
    white-space: nowrap;
    display: inline-block;
}

.priority-badge.low {
    background: rgba(0, 200, 83, 0.12);
    color: var(--green-light);
}

.priority-badge.medium {
    background: rgba(255, 183, 77, 0.12);
    color: var(--warning);
}

.priority-badge.high {
    background: rgba(207, 102, 121, 0.12);
    color: var(--danger);
}

.priority-badge.urgent {
    background: rgba(207, 102, 121, 0.18);
    color: var(--danger);
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

    .activity-meta {
        width: 100%;
        justify-content: flex-start;
    }

    .dashboard-card {
        padding: 16px;
    }

    .stat-card {
        padding: 16px;
    }
}

/* Dark mode adjustments for stat cards */
@media (prefers-color-scheme: dark) {
    .stat-card,
    .dashboard-card {
        background: var(--card-bg);
        border-color: var(--black-border);
    }

    .stat-icon {
        background: rgba(0, 200, 83, 0.18);
        color: var(--green-light);
    }

    .status-badge.closed {
        background: rgba(255, 255, 255, 0.08);
        color: var(--text-muted);
    }
}

/* Light mode explicit overrides (when data-theme="light" is set) */
html[data-theme="light"] .stat-card,
html[data-theme="light"] .dashboard-card {
    background: var(--card-bg);
    border-color: var(--black-border);
    box-shadow: var(--shadow-sm);
}

html[data-theme="light"] .stat-icon {
    background: rgba(118, 185, 0, 0.12);
    color: var(--green-light);
}

html[data-theme="light"] .status-badge.closed {
    background: #e5e7eb;
    color: #4b5563;
}

/* Ensure proper contrast for badges in light mode */
html[data-theme="light"] .status-badge.open,
html[data-theme="light"] .status-badge.resolved {
    background: rgba(0, 200, 83, 0.15);
}

html[data-theme="light"] .status-badge.in_progress {
    background: rgba(255, 183, 77, 0.15);
}

html[data-theme="light"] .priority-badge.low,
html[data-theme="light"] .priority-badge.medium {
    background: rgba(255, 183, 77, 0.15);
}

html[data-theme="light"] .priority-badge.high,
html[data-theme="light"] .priority-badge.urgent {
    background: rgba(207, 102, 121, 0.15);
}
</style>
