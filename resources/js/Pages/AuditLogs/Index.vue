<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    logs: { type: Object, required: true },
    actions: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const action = ref(props.filters.action || '');

let searchTimeout = null;

const applyFilters = () => {
    router.get(route('audit-logs.index'), {
        search: search.value,
        action: action.value,
    }, { preserveState: true, replace: true });
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 400);
});

const formatDate = (d) => d ? new Date(d).toLocaleString() : '—';
</script>

<template>
    <Head title="Audit Logs" />
    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>Audit Log</h2>
            </div>
        </template>

        <div class="audit-logs">
            <div class="mui-card">
                <div class="search-box">
                    <div class="search-field">
                        <input v-model="search" type="text" placeholder="Search user, action, IP..." class="mui-input" />
                    </div>
                    <div class="search-field">
                        <select v-model="action" @change="applyFilters" class="mui-select mui-select-group">
                            <option value="">All Actions</option>
                            <option v-for="a in actions" :key="a" :value="a">{{ a }}</option>
                        </select>
                    </div>
                </div>

                <div class="mui-table-container">
                    <table class="mui-table">
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in logs.data" :key="log.id">
                                <td style="white-space: nowrap;">{{ formatDate(log.created_at) }}</td>
                                <td>{{ log.user_name || '—' }}</td>
                                <td><span class="status-badge">{{ log.action }}</span></td>
                                <td style="font-family: 'Courier New', monospace; font-size: 0.8rem;">{{ log.ip_address || '—' }}</td>
                            </tr>
                            <tr class="table-empty-state" v-if="!logs.data.length">
                                <td colspan="4">No audit logs found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="form-actions" style="justify-content: space-between;">
                    <div>Showing {{ logs.from || 0 }} to {{ logs.to || 0 }} of {{ logs.total }}</div>
                    <div class="search-box" style="margin-bottom: 0;">
                        <template v-for="link in logs.links" :key="link.label">
                            <Link v-if="link.url" :href="link.url" :class="link.active ? 'mui-btn mui-btn-contained' : 'mui-btn mui-btn-outlined'" class="mui-btn-sm" v-html="link.label" />
                            <span v-else class="mui-btn mui-btn-outlined mui-btn-sm" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
