<script setup>
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

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
    full_name: user.full_name || user.name || "",
    username: user.username || "",
    email: user.email || "",
    contact_number: user.contact_number || "",
    address: user.address || "",
});

const isEditing = ref(false);

const profileSummary = [
    {
        label: "Full Name",
        icon: "badge",
        value: () => user.full_name || user.name || "—",
    },
    {
        label: "Username",
        icon: "alternate_email",
        value: () => user.username || "—",
    },
    {
        label: "Email Address",
        icon: "mail",
        value: () => user.email || "—",
    },
    {
        label: "Contact Number",
        icon: "call",
        value: () => user.contact_number || "—",
    },
    {
        label: "Address",
        icon: "location_on",
        value: () => user.address || "—",
    },
    {
        label: "User Role",
        icon: "verified_user",
        value: () => user.role || "User",
    },
    {
        label: "Account Status",
        icon: "check_circle",
        value: () =>
            user.status
                ? user.status.charAt(0).toUpperCase() + user.status.slice(1)
                : "Active",
    },
    {
        label: "Date Created",
        icon: "event",
        value: () =>
            user.created_at
                ? new Date(user.created_at).toLocaleDateString()
                : "—",
    },
    {
        label: "Last Login",
        icon: "schedule",
        value: () =>
            user.last_login ? new Date(user.last_login).toLocaleString() : "—",
    },
    {
        label: "Profile Photo",
        icon: "photo_camera",
        value: () => user.profile_photo || "No profile photo uploaded",
    },
];

const startEdit = () => {
    form.full_name = user.full_name || user.name || "";
    form.username = user.username || "";
    form.email = user.email || "";
    form.contact_number = user.contact_number || "";
    form.address = user.address || "";
    isEditing.value = true;
};

const cancelEdit = () => {
    isEditing.value = false;
    form.clearErrors();
};

const saveDetails = () => {
    form.patch(route("profile.update"), {
        onSuccess: () => {
            isEditing.value = false;
            form.clearErrors();
        },
    });
};
</script>

<template>
    <div class="mui-card profile-form-card">
        <div class="profile-form-header">
            <div>
                <p class="profile-eyebrow">Account overview</p>
                <h3 class="form-title">Profile Details</h3>
                <p class="profile-form-subtitle">
                    View your account details and tap Edit Profile when you want
                    to update them.
                </p>
            </div>
            <SecondaryButton
                type="button"
                @click="isEditing ? cancelEdit() : startEdit()"
                class="edit-profile-btn"
            >
                <span class="material-icons">edit</span>
                {{ isEditing ? "Close Editor" : "Edit Profile" }}
            </SecondaryButton>
        </div>

        <div v-if="!isEditing" class="profile-summary-grid">
            <article
                v-for="item in profileSummary"
                :key="item.label"
                class="profile-summary-item"
            >
                <div class="profile-summary-icon">
                    <span class="material-icons">{{ item.icon }}</span>
                </div>
                <div>
                    <span class="profile-summary-label">{{ item.label }}</span>
                    <p class="profile-summary-value">{{ item.value() }}</p>
                </div>
            </article>
        </div>

        <Transition name="fade-slide">
            <div v-if="isEditing" class="profile-editor-card">
                <div class="profile-editor-header">
                    <div>
                        <p class="profile-eyebrow">Editable fields</p>
                        <h4>Update your profile information</h4>
                    </div>
                    <span class="profile-editor-note"
                        >Changes save instantly after you press Save
                        Changes.</span
                    >
                </div>

                <form
                    class="profile-form form-grid"
                    @submit.prevent="saveDetails"
                >
                    <div class="mui-input-group">
                        <TextInput
                            id="full_name"
                            type="text"
                            v-model="form.full_name"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <label class="mui-label">Full Name</label>
                        <InputError :message="form.errors.full_name" />
                    </div>

                    <div class="mui-input-group">
                        <TextInput
                            id="username"
                            type="text"
                            v-model="form.username"
                            required
                            autocomplete="username"
                        />
                        <label class="mui-label">Username</label>
                        <InputError :message="form.errors.username" />
                    </div>

                    <div class="mui-input-group">
                        <TextInput
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autocomplete="email"
                        />
                        <label class="mui-label">Email Address</label>
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="mui-input-group">
                        <TextInput
                            id="contact_number"
                            type="text"
                            v-model="form.contact_number"
                            autocomplete="tel"
                        />
                        <label class="mui-label">Contact Number</label>
                        <InputError :message="form.errors.contact_number" />
                    </div>

                    <div class="mui-input-group full-width">
                        <TextInput
                            id="address"
                            type="text"
                            v-model="form.address"
                        />
                        <label class="mui-label">Address</label>
                        <InputError :message="form.errors.address" />
                    </div>
                    <div class="form-actions profile-form-actions">
                        <PrimaryButton type="submit" :disabled="form.processing"
                            >Save Changes</PrimaryButton
                        >
                        <SecondaryButton type="button" @click="cancelEdit"
                            >Cancel</SecondaryButton
                        >
                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-if="form.recentlySuccessful"
                                style="
                                    color: var(--green-light);
                                    margin-left: 12px;
                                "
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>
        </Transition>
    </div>
</template>
