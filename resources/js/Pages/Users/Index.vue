<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
    users: { type: Object, required: true },
    roles: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || "");
const status = ref(props.filters.status || "");
const roleId = ref(props.filters.role_id || "");

let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route("users.index"),
        {
            search: search.value,
            status: status.value,
            role_id: roleId.value,
        },
        { preserveState: true, replace: true },
    );
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 400);
});

const resetFilters = () => {
    search.value = "";
    status.value = "";
    roleId.value = "";
    applyFilters();
};

const deleteUser = (u) => {
    if (confirm(`Delete user ${u.full_name || u.username}?`)) {
        router.delete(route("users.destroy", u.id));
    }
};

const formatDate = (d) => (d ? new Date(d).toLocaleDateString() : "—");
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>Users</h2>
                <div class="header-actions">
                    <Link
                        :href="route('users.create')"
                        class="mui-btn mui-btn-contained"
                        >Add User</Link
                    >
                </div>
            </div>
        </template>

        <div class="user-management">
            <div class="mui-card">
                <div class="search-box">
                    <div class="search-field">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by name, username, email..."
                            class="mui-input"
                        />
                    </div>
                    <div class="search-field">
                        <select
                            v-model="status"
                            @change="applyFilters"
                            class="mui-select mui-select-group"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="search-field">
                        <select
                            v-model="roleId"
                            @change="applyFilters"
                            class="mui-select mui-select-group"
                        >
                            <option value="">All Roles</option>
                            <option
                                v-for="r in roles"
                                :key="r.id"
                                :value="r.id"
                            >
                                {{ r.name }}
                            </option>
                        </select>
                    </div>
                    <button
                        @click="resetFilters"
                        class="mui-btn mui-btn-outlined mui-btn-sm"
                    >
                        Reset
                    </button>
                </div>

                <div class="mui-table-container">
                    <table class="mui-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th style="text-align: right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="u in users.data" :key="u.id">
                                <td>{{ u.full_name || "—" }}</td>
                                <td>{{ u.username }}</td>
                                <td>{{ u.email }}</td>
                                <td>{{ u.role?.name || "—" }}</td>
                                <td>
                                    <span :class="['status-badge', u.status]">{{
                                        u.status
                                    }}</span>
                                </td>
                                <td>{{ formatDate(u.last_login) }}</td>
                                <td
                                    class="table-actions"
                                    style="text-align: right"
                                >
                                    <Link
                                        :href="route('users.show', u.id)"
                                        class="mui-btn mui-btn-outlined mui-btn-sm"
                                        >View</Link
                                    >
                                    <Link
                                        :href="route('users.edit', u.id)"
                                        class="mui-btn mui-btn-outlined mui-btn-sm"
                                        >Edit</Link
                                    >
                                    <button
                                        @click="deleteUser(u)"
                                        class="mui-btn mui-btn-danger mui-btn-sm"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <tr
                                class="table-empty-state"
                                v-if="!users.data.length"
                            >
                                <td colspan="7">No users found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    class="form-actions"
                    style="justify-content: space-between"
                >
                    <div>
                        Showing {{ users.from || 0 }} to {{ users.to || 0 }} of
                        {{ users.total }}
                    </div>
                    <div class="search-box" style="margin-bottom: 0">
                        <template v-for="link in users.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="
                                    link.active
                                        ? 'mui-btn mui-btn-contained'
                                        : 'mui-btn mui-btn-outlined'
                                "
                                class="mui-btn-sm"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="mui-btn mui-btn-outlined mui-btn-sm"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
