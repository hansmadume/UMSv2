<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const avatarInput = ref(null);
const avatarForm = useForm({
    profile_photo: null,
});

const onAvatarChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    avatarForm.profile_photo = file;
    avatarForm.post(route('profile.avatar'), {
        onSuccess: () => {
            if (avatarInput.value) {
                avatarInput.value.value = '';
            }
        },
    });
};
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>Profile</h2>
            </div>
        </template>

        <div class="profile-page">
            <div class="mui-card profile-header-card">
                <div class="profile-avatar" @click="$refs.avatarInput.click()">
                    <span v-if="!$page.props.auth.user.profile_photo" class="material-icons">person</span>
                    <img v-else :src="$page.props.auth.user.profile_photo" alt="Profile photo" />
                    <span class="avatar-edit-hint">
                        <span class="material-icons">camera_alt</span>
                    </span>
                </div>
                <input ref="avatarInput" type="file" accept="image/*" style="display: none;" @change="onAvatarChange">
                <div class="profile-info">
                    <h2>{{ $page.props.auth.user.full_name || $page.props.auth.user.name || $page.props.auth.user.username }}</h2>
                    <p class="profile-email">{{ $page.props.auth.user.email }}</p>
                    <span class="status-badge active">{{ $page.props.auth.user.role || 'User' }}</span>
                </div>
                <div class="profile-meta">
                    <div class="meta-item">
                        <span class="material-icons">calendar_today</span>
                        <span>Joined: {{ $page.props.auth.user.created_at ? new Date($page.props.auth.user.created_at).toLocaleDateString() : '—' }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="material-icons">login</span>
                        <span>Last Login: {{ $page.props.auth.user.last_login ? new Date($page.props.auth.user.last_login).toLocaleString() : '—' }}</span>
                    </div>
                </div>
            </div>

            <UpdateProfileInformationForm
                :must-verify-email="mustVerifyEmail"
                :status="status"
            />

            <UpdatePasswordForm />

            <DeleteUserForm />
        </div>
    </AuthenticatedLayout>
</template>