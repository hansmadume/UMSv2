<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    role: { type: Object, required: true },
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
                            <span v-for="p in role.permissions" :key="p.id" class="status-badge" style="margin-right: 8px; margin-bottom: 4px;">{{ p.name }}</span>
                            <span v-if="!role.permissions || !role.permissions.length">No permissions</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
