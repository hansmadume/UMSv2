<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { usePersistentForm } from "@/composables/usePersistentForm";

const form = useForm({
    name: "",
    slug: "",
    description: "",
});

const { clear } = usePersistentForm(form, 'permissions.create');

const submit = () => {
    form.post(route("permissions.store"), {
        onSuccess: () => clear(),
    });
};
</script>

<template>
    <Head title="Add Permission" />
    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>Add Permission</h2>
            </div>
        </template>
        <div class="user-management">
            <div class="mui-card">
                <form @submit.prevent="submit" class="user-form">
                    <div class="mui-input-group">
                        <TextInput
                            id="name"
                            v-model="form.name"
                            required
                        />
                        <InputLabel for="name" value="Permission Name" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="mui-input-group">
                        <TextInput
                            id="slug"
                            v-model="form.slug"
                            required
                        />
                        <InputLabel for="slug" value="Slug" />
                        <InputError :message="form.errors.slug" />
                    </div>

                    <div class="mui-input-group">
                        <TextInput
                            id="description"
                            v-model="form.description"
                        />
                        <InputLabel for="description" value="Description" />
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="form-actions">
                        <Link :href="route('permissions.index')" class="mui-btn mui-btn-outlined">
                            Cancel
                        </Link>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Add Permission
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
