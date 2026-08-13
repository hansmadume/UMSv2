<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
    permissions: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || "");

let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route("permissions.index"),
        { search: search.value },
        { preserveState: true, replace: true },
    );
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 400);
});

const deletePermission = (p) => {
    if (confirm(`Delete permission "${p.name}"?`)) {
        router.delete(route("permissions.destroy", p.id));
    }
};
</script>

<template>
    <Head title="Permissions" />
    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>Permissions</h2>
                <div class="header-actions">
                    <Link :href="route('permissions.create')" class="mui-btn mui-btn-contained">
                        Add Permission
                    </Link>
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
                            placeholder="Search by name, slug, description..."
                            class="mui-input"
                        />
                    </div>
                </div>

                <div class="mui-table-container">
                    <table class="mui-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Description</th>
                                <th style="text-align: right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in permissions.data" :key="p.id">
                                <td><strong>{{ p.description || p.name }}</strong></td>
                                <td><code class="permission-slug">{{ p.slug }}</code></td>
                                <td>{{ p.name }}</td>
                                <td class="table-actions" style="text-align: right">
                                    <Link
                                        :href="route('permissions.edit', p.id)"
                                        class="mui-btn mui-btn-outlined mui-btn-sm"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deletePermission(p)"
                                        class="mui-btn mui-btn-danger mui-btn-sm"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <tr class="table-empty-state" v-if="!permissions.data.length">
                                <td colspan="4">No permissions found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="form-actions" style="justify-content: space-between">
                    <div>
                        Showing {{ permissions.from || 0 }} to
                        {{ permissions.to || 0 }} of {{ permissions.total }}
                    </div>
                    <div class="search-box" style="margin-bottom: 0">
                        <template v-for="link in permissions.links" :key="link.label">
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
