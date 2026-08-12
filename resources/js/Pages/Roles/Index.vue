<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
    roles: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || "");
const status = ref(props.filters.status || "");

let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route("roles.index"),
        {
            search: search.value,
            status: status.value,
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

const deleteRole = (r) => {
    if (confirm(`Delete role "${r.name}"?`)) {
        router.delete(route("roles.destroy", r.id));
    }
};
</script>

<template>
    <Head title="Roles" />
    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>Roles</h2>
                <div class="header-actions">
                    <Link
                        :href="route('roles.create')"
                        class="mui-btn mui-btn-contained"
                        >Add Role</Link
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
                            placeholder="Search by name, description..."
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
                </div>

                <div class="mui-table-container">
                    <table class="mui-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Users</th>
                                <th>Permissions</th>
                                <th>Status</th>
                                <th style="text-align: right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in roles.data" :key="r.id">
                                <td>{{ r.name }}</td>
                                <td>{{ r.description || "—" }}</td>
                                <td>{{ r.users_count }}</td>
                                <td>{{ r.permissions_count }}</td>
                                <td>
                                    <span :class="['status-badge', r.status]">{{
                                        r.status
                                    }}</span>
                                </td>
                                <td
                                    class="table-actions"
                                    style="text-align: right"
                                >
                                    <Link
                                        :href="route('roles.show', r.id)"
                                        class="mui-btn mui-btn-outlined mui-btn-sm"
                                        >View</Link
                                    >
                                    <Link
                                        :href="route('roles.edit', r.id)"
                                        class="mui-btn mui-btn-outlined mui-btn-sm"
                                        >Edit</Link
                                    >
                                    <button
                                        @click="deleteRole(r)"
                                        class="mui-btn mui-btn-danger mui-btn-sm"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <tr
                                class="table-empty-state"
                                v-if="!roles.data.length"
                            >
                                <td colspan="6">No roles found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    class="form-actions"
                    style="justify-content: space-between"
                >
                    <div>
                        Showing {{ roles.from || 0 }} to {{ roles.to || 0 }} of
                        {{ roles.total }}
                    </div>
                    <div class="search-box" style="margin-bottom: 0">
                        <template v-for="link in roles.links" :key="link.label">
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
