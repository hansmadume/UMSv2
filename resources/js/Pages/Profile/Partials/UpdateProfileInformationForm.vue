<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
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

const form = useForm({
    full_name: user.full_name || user.name || '',
    username: user.username || '',
    email: user.email || '',
    contact_number: user.contact_number || '',
    address: user.address || '',
});

const isEditing = ref(false);

const startEdit = () => {
    form.full_name = user.full_name || user.name || '';
    form.username = user.username || '';
    form.email = user.email || '';
    form.contact_number = user.contact_number || '';
    form.address = user.address || '';
    isEditing.value = true;
};

const cancelEdit = () => {
    isEditing.value = false;
    form.clearErrors();
};

const saveDetails = () => {
    form.patch(route('profile.update'), {
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};
</script>

<template>
    <div class="mui-card profile-form-card">
        <div class="form-title-row">
            <h3 class="form-title">Profile Details</h3>
            <SecondaryButton v-if="!isEditing" @click="startEdit" class="edit-profile-btn">
                <span class="material-icons">edit</span>
                Edit
            </SecondaryButton>
        </div>

        <div class="profile-form">
            <div class="form-row">
                <div class="mui-input-group">
                    <TextInput
                        v-if="isEditing"
                        id="full_name"
                        type="text"
                        v-model="form.full_name"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <input
                        v-else
                        type="text"
                        class="mui-input"
                        :value="user.full_name || user.name || '—'"
                        readonly
                    >
                    <label class="mui-label">Full Name</label>
                    <InputError :message="form.errors.full_name" />
                </div>
                <div class="mui-input-group">
                    <TextInput
                        v-if="isEditing"
                        id="username"
                        type="text"
                        v-model="form.username"
                        required
                        autocomplete="username"
                    />
                    <input
                        v-else
                        type="text"
                        class="mui-input"
                        :value="user.username || '—'"
                        readonly
                    >
                    <label class="mui-label">Username</label>
                    <InputError :message="form.errors.username" />
                </div>
            </div>

            <div class="form-row">
                <div class="mui-input-group">
                    <TextInput
                        v-if="isEditing"
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autocomplete="username"
                    />
                    <input
                        v-else
                        type="text"
                        class="mui-input"
                        :value="user.email || '—'"
                        readonly
                    >
                    <label class="mui-label">Email Address</label>
                    <InputError :message="form.errors.email" />
                </div>
                <div class="mui-input-group">
                    <TextInput
                        v-if="isEditing"
                        id="contact_number"
                        type="text"
                        v-model="form.contact_number"
                        autocomplete="tel"
                    />
                    <input
                        v-else
                        type="text"
                        class="mui-input"
                        :value="user.contact_number || '—'"
                        readonly
                    >
                    <label class="mui-label">Contact Number</label>
                    <InputError :message="form.errors.contact_number" />
                </div>
            </div>

            <div class="form-row">
                <div class="mui-input-group">
                    <TextInput
                        v-if="isEditing"
                        id="address"
                        type="text"
                        v-model="form.address"
                    />
                    <input
                        v-else
                        type="text"
                        class="mui-input"
                        :value="user.address || '—'"
                        readonly
                    >
                    <label class="mui-label">Address</label>
                    <InputError :message="form.errors.address" />
                </div>
                <div class="mui-input-group">
                    <input type="text" class="mui-input" :value="user.role || 'User'" readonly>
                    <label class="mui-label">User Role</label>
                </div>
            </div>

            <div class="form-row">
                <div class="mui-input-group">
                    <input type="text" class="mui-input" :value="user.status ? user.status.charAt(0).toUpperCase() + user.status.slice(1) : 'Active'" readonly>
                    <label class="mui-label">Account Status</label>
                </div>
                <div class="mui-input-group">
                    <input type="text" class="mui-input" :value="user.created_at ? new Date(user.created_at).toLocaleDateString() : '—'" readonly>
                    <label class="mui-label">Date Created</label>
                </div>
            </div>

            <div class="form-row">
                <div class="mui-input-group">
                    <input type="text" class="mui-input" :value="user.last_login ? new Date(user.last_login).toLocaleString() : '—'" readonly>
                    <label class="mui-label">Last Login</label>
                </div>
                <div class="mui-input-group">
                    <input type="text" class="mui-input" :value="user.profile_photo || 'No profile photo uploaded'" readonly>
                    <label class="mui-label">Profile Photo</label>
                </div>
            </div>

            <div v-if="isEditing" class="form-actions">
                <PrimaryButton :disabled="form.processing">Save Changes</PrimaryButton>
                <SecondaryButton type="button" @click="cancelEdit">Cancel</SecondaryButton>
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" style="color: var(--green-light); margin-left: 12px;">
                        Saved.
                    </p>
                </Transition>
            </div>
        </div>
    </div>
</template>