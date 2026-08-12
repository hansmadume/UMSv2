<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    user: { type: Object, required: true },
});

const formatDate = (d) => d ? new Date(d).toLocaleString() : '—';
</script>

<template>
    <Head :title="`User: ${user.username}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>User Details: {{ user.full_name || user.username }}</h2>
                <div class="header-actions">
                    <Link :href="route('users.edit', user.id)" class="mui-btn mui-btn-contained">Edit</Link>
                    <Link :href="route('users.index')" class="mui-btn mui-btn-outlined">Back</Link>
                </div>
            </div>
        </template>

        <div class="user-management">
            <div class="mui-card">
                <div class="form-grid">
                    <div>
                        <div>Full Name</div>
                        <div>{{ user.full_name || '—' }}</div>
                    </div>
                    <div>
                        <div>Username</div>
                        <div>{{ user.username }}</div>
                    </div>
                    <div>
                        <div>Email</div>
                        <div>{{ user.email }}</div>
                    </div>
                    <div>
                        <div>Role</div>
                        <div>{{ user.role?.name || '—' }}</div>
                    </div>
                    <div>
                        <div>Status</div>
                        <div>
                            <span :class="['status-badge', user.status]">{{ user.status }}</span>
                        </div>
                    </div>
                    <div>
                        <div>Contact Number</div>
                        <div>{{ user.contact_number || '—' }}</div>
                    </div>
                    <div class="full-width">
                        <div>Address</div>
                        <div>{{ user.address || '—' }}</div>
                    </div>
                    <div>
                        <div>Last Login</div>
                        <div>{{ formatDate(user.last_login) }}</div>
                    </div>
                    <div>
                        <div>Created At</div>
                        <div>{{ formatDate(user.created_at) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
