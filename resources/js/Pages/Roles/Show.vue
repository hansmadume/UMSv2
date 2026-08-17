<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    role: { type: Object, required: true },
});

const permissionGroups = computed(() => {
    const groups = {};
    const order = [
        'Dashboard', 'Profile', 'Users', 'Roles', 'Permissions', 'Tickets', 'Ticket Replies', 'Staff', 'Reports', 'Notifications', 'Audit Logs', 'Settings', 'Support'
    ];

    (props.role.permissions || []).forEach(p => {
        const category = p.slug.split('.')[0];
        const label = category.charAt(0).toUpperCase() + category.slice(1);
        if (!groups[label]) {
            groups[label] = [];
        }
        groups[label].push(p);
    });

    return Object.keys(groups)
        .sort((a, b) => {
            const indexA = order.indexOf(a);
            const indexB = order.indexOf(b);
            if (indexA !== -1 && indexB !== -1) return indexA - indexB;
            if (indexA !== -1) return -1;
            if (indexB !== -1) return 1;
            return a.localeCompare(b);
        })
        .map(category => ({
            category,
            permissions: groups[category],
        }));
});
</script>

<template>
    <Head :title="`Role: ${role.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>Role: {{ role.name }}</h2>
                <div class="header-actions">
                    <Link :href="route('roles.edit', role.id)" class="mui-btn mui-btn-contained">Edit</Link>
                    <Link :href="route('roles.index')" class="mui-btn mui-btn-outlined">Back</Link>
                </div>
            </div>
        </template>

        <div class="user-management">
            <div class="mui-card">
                <div class="form-grid">
                    <div>
                        <div>Name</div>
                        <div>{{ role.name }}</div>
                    </div>
                    <div>
                        <div>Icon</div>
                        <div>{{ role.icon || '—' }}</div>
                    </div>
                    <div class="full-width">
                        <div>Description</div>
                        <div>{{ role.description || '—' }}</div>
                    </div>
                    <div>
                        <div>Status</div>
                        <div>
                            <span :class="['status-badge', role.status]">{{ role.status }}</span>
                        </div>
                    </div>
                    <div>
                        <div>Users</div>
                        <div>{{ role.users?.length || 0 }}</div>
                    </div>
                    <div class="full-width">
                        <div>Permissions</div>
                        <div>
                            <template v-for="group in permissionGroups" :key="group.category">
                                <div class="permission-group-header">{{ group.category }}</div>
                                <div style="margin-bottom: 8px;">
                                    <span v-for="p in group.permissions" :key="p.id" class="status-badge" style="margin-right: 8px; margin-bottom: 4px;">{{ p.description || p.name }}</span>
                                </div>
                            </template>
                            <span v-if="!role.permissions || !role.permissions.length">No permissions</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.permission-group-header {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary);
    margin-top: 10px;
    margin-bottom: 4px;
}
</style>
