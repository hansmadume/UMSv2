<template>
    <Head title="Support Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>Support Tickets</h2>
            </div>
        </template>

        <div class="support-tickets">
            <div class="filters-bar">
                <TextInput
                    v-model="search"
                    placeholder="Search tickets..."
                    class="filter-search"
                    @update:modelValue="debouncedSearch"
                />
                <select v-model="statusFilter" class="mui-select filter-select" @change="applyFilters">
                    <option value="">All Statuses</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                </select>
                <select v-model="priorityFilter" class="mui-select filter-select" @change="applyFilters">
                    <option value="">All Priorities</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
                <button v-if="isSupportStaff" type="button" class="mui-btn mui-btn-outlined" @click="toggleMyTickets">
                    {{ filters.my_tickets ? 'All Tickets' : 'My Tickets' }}
                </button>
            </div>

            <div class="mui-card">
                <div class="table-responsive">
                    <table class="mui-table">
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Subject</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Assigned To</th>
                                <th>Created At</th>
                                <th style="text-align: right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ticket in tickets.data" :key="ticket.id">
                                <td><strong>{{ ticket.ticket_id }}</strong></td>
                                <td>{{ ticket.subject }}</td>
                                <td>{{ ticket.category }}</td>
                                <td>
                                    <span :class="['status-badge', ticket.status]">{{ ticket.status }}</span>
                                </td>
                                <td>
                                    <span class="priority-badge" :class="ticket.priority">{{ ticket.priority }}</span>
                                </td>
                                <td>{{ ticket.assigned_to ? ticket.assignedTo?.name || 'Assigned' : '—' }}</td>
                                <td>{{ formatDate(ticket.created_at) }}</td>
                                <td class="table-actions" style="text-align: right">
                                    <Link :href="route('support.show', ticket.id)" class="mui-btn mui-btn-outlined mui-btn-sm">
                                        View
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!tickets.data.length">
                                <td colspan="8" class="empty-state">No tickets found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <Link v-if="tickets.prev_page_url" :href="tickets.prev_page_url" class="mui-btn mui-btn-outlined mui-btn-sm">
                        Previous
                    </Link>
                    <span class="pagination-info">Page {{ tickets.current_page }} of {{ tickets.last_page }}</span>
                    <Link v-if="tickets.next_page_url" :href="tickets.next_page_url" class="mui-btn mui-btn-outlined mui-btn-sm">
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import TextInput from '@/Components/TextInput.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.is_admin);
const isSupportStaff = computed(() => user.value?.role === 'Support Staff');

const tickets = computed(() => page.props.tickets || { data: [] });
const filters = computed(() => page.props.filters || {});

const search = ref(filters.value.search || '');
const statusFilter = ref(filters.value.status || '');
const priorityFilter = ref(filters.value.priority || '');

let debounceTimer = null;
const debouncedSearch = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 300);
};

const toggleMyTickets = () => {
    const newValue = !filters.value.my_tickets;
    router.get(route('support.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        priority: priorityFilter.value || undefined,
        my_tickets: newValue || undefined,
    }, { preserveState: true, replace: true });
};

const applyFilters = () => {
    router.get(route('support.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        priority: priorityFilter.value || undefined,
        my_tickets: filters.value.my_tickets || undefined,
    }, { preserveState: true, replace: true });
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString();
};
</script>

<style scoped>
.support-tickets {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.filters-bar {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-search {
    flex: 1;
    min-width: 200px;
}

.filter-select {
    min-width: 150px;
}

.table-responsive {
    overflow-x: auto;
}

.mui-table {
    width: 100%;
    border-collapse: collapse;
}

.mui-table th,
.mui-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.mui-table th {
    font-weight: 600;
    color: var(--text-secondary, #6b7280);
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: var(--text-secondary, #6b7280);
}

.pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
}

.pagination-info {
    font-size: 0.9rem;
    color: var(--text-secondary, #6b7280);
}

.status-badge {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    padding: 4px 8px;
    border-radius: 4px;
}

.status-badge.open {
    background: #e3f2fd;
    color: #1565c0;
}

.status-badge.in_progress {
    background: #fff3e0;
    color: #ef6c00;
}

.status-badge.resolved {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-badge.closed {
    background: #eceff1;
    color: #455a64;
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
    .filters-bar {
        flex-direction: column;
    }

    .filter-search,
    .filter-select {
        width: 100%;
    }
}
</style>
