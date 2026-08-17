<template>
    <Head title="Create Support Ticket" />

    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>Create Support Ticket</h2>
            </div>
        </template>

        <div class="support-create">
            <div class="mui-card">
                <form @submit.prevent="submit" class="support-form">
                    <div class="form-grid">
                        <div class="mui-input-group">
                            <TextInput id="subject" v-model="form.subject" required />
                            <InputLabel for="subject" value="Subject" />
                            <InputError :message="form.errors.subject" />
                        </div>
                        <div class="mui-input-group">
                            <select id="category" v-model="form.category" required class="mui-input">
                                <option value="">Select Category</option>
                                <option value="Technical">Technical</option>
                                <option value="Billing">Billing</option>
                                <option value="Account">Account</option>
                                <option value="General">General</option>
                                <option value="Other">Other</option>
                            </select>
                            <InputLabel for="category" value="Category" />
                            <InputError :message="form.errors.category" />
                        </div>
                    </div>

                    <div class="mui-input-group">
                        <select id="priority" v-model="form.priority" required class="mui-input">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        <InputLabel for="priority" value="Priority" />
                        <InputError :message="form.errors.priority" />
                    </div>

                    <div class="mui-input-group">
                        <textarea
                            id="comments"
                            v-model="form.comments"
                            rows="6"
                            class="mui-textarea"
                            placeholder="Describe your issue..."
                            required
                        ></textarea>
                        <InputLabel for="comments" value="Description" />
                        <InputError :message="form.errors.comments" />
                    </div>

                    <div class="mui-input-group">
                        <input
                            type="file"
                            id="attachment"
                            @change="onFileChange"
                            class="mui-file-input"
                        />
                        <label for="attachment" class="mui-file-label">
                            <span class="material-icons" aria-hidden="true">attach_file</span>
                            {{ form.attachment ? form.attachment.name : 'Attach files (optional)' }}
                        </label>
                        <InputError :message="form.errors.attachment" />
                    </div>

                    <div class="form-actions">
                        <Link :href="route('support.index')" class="mui-btn mui-btn-outlined">Cancel</Link>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            <span class="material-icons" aria-hidden="true">send</span>
                            Submit Ticket
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    subject: '',
    category: '',
    priority: 'medium',
    comments: '',
    attachment: null,
});

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!allowedTypes.includes(file.type)) {
            form.errors.attachment = 'File type not allowed. Allowed types: JPG, PNG, GIF, WebP, PDF, DOC, DOCX, TXT';
            e.target.value = '';
            return;
        }
        
        if (file.size > maxSize) {
            form.errors.attachment = 'File size must not exceed 5MB';
            e.target.value = '';
            return;
        }
        
        form.errors.attachment = '';
        form.attachment = file;
    } else {
        form.attachment = null;
    }
};

const submit = () => {
    form.post(route('support.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<style scoped>
.support-create {
    max-width: 800px;
}

.support-form {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
