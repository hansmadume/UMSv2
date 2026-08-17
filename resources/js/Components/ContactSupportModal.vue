<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    user: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const sent = ref(false);

const form = useForm({
    username: '',
    email: '',
    comments: '',
    attachment: null,
});

watch(() => props.show, (isOpen) => {
    if (isOpen) {
        sent.value = false;
        if (props.user) {
            form.username = props.user.username ?? props.user.full_name ?? props.user.email ?? '';
            form.email = props.user.email ?? '';
        }
        form.comments = '';
        form.attachment = null;
        document.body.classList.add('modal-open');
    } else {
        document.body.classList.remove('modal-open');
    }
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

const cancelSupport = () => {
    form.clearErrors();
    form.reset();
    sent.value = false;
    emit('close');
};

const sendSupport = () => {
    form.post(route('support.contact.store'), {
        onSuccess: () => {
            form.reset();
            sent.value = true;
        },
        onError: () => {
            // validation errors will be displayed by InputError components
        },
    });
};
</script>

<template>
    <div v-if="show" class="confirm-modal" @click.self="cancelSupport" @keydown.escape="cancelSupport">
        <div class="confirm-modal-backdrop"></div>
        <div class="confirm-card confirm-card-lg" role="dialog" aria-modal="true" aria-labelledby="supportModalTitle">
            <div class="confirm-card-icon">
                <span class="material-icons" aria-hidden="true">support_agent</span>
            </div>
            <div class="confirm-card-content">
                <template v-if="!sent">
                    <h3 id="supportModalTitle">Contact Support</h3>
                    <p class="confirm-card-subtitle">We'll get back to you as soon as possible.</p>

                    <form @submit.prevent="sendSupport" class="support-form">
                        <div class="form-row">
                            <div class="mui-input-group">
                                <TextInput id="support_username" v-model="form.username" readonly />
                                <InputLabel for="support_username" value="Username" />
                            </div>
                            <div class="mui-input-group">
                                <TextInput id="support_email" v-model="form.email" readonly />
                                <InputLabel for="support_email" value="Email" />
                            </div>
                        </div>

                        <div class="mui-input-group">
                            <textarea
                                id="support_comments"
                                v-model="form.comments"
                                rows="4"
                                class="mui-textarea"
                                placeholder=" "
                            ></textarea>
                            <InputLabel for="support_comments" value="Comments" />
                            <InputError :message="form.errors.comments" />
                        </div>

                        <div class="mui-input-group">
                            <input
                                type="file"
                                id="support_attachment"
                                @change="onFileChange"
                                class="mui-file-input"
                            />
                            <label for="support_attachment" class="mui-file-label">
                                <span class="material-icons" aria-hidden="true">attach_file</span>
                                {{ form.attachment ? form.attachment.name : 'Attach files' }}
                            </label>
                            <InputError :message="form.errors.attachment" />
                        </div>

                        <div class="confirm-card-actions">
                            <button type="button" class="mui-btn mui-btn-outlined" @click="cancelSupport" :disabled="form.processing">Cancel</button>
                            <button type="submit" class="mui-btn mui-btn-contained" :disabled="form.processing">
                                <span v-if="form.processing" class="material-icons spin" aria-hidden="true">refresh</span>
                                <span v-else class="material-icons" aria-hidden="true">send</span>
                                {{ form.processing ? 'Sending...' : 'Send' }}
                            </button>
                        </div>
                    </form>
                </template>

                <template v-else>
                    <div class="support-success">
                        <span class="material-icons support-success-icon" aria-hidden="true">check_circle</span>
                        <h3>Message Sent Successfully!</h3>
                        <p>Thank you for contacting support. We've received your request and will get back to you as soon as possible.</p>
                        <button type="button" class="mui-btn mui-btn-contained" @click="cancelSupport">Close</button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
.confirm-card-lg {
    width: min(560px, 100%);
}

.confirm-card-subtitle {
    color: var(--text-secondary);
    font-size: 0.94rem;
    line-height: 1.55;
    margin-bottom: 20px;
    margin-top: -8px;
}

.support-form {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.support-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.support-success {
    text-align: center;
    padding: 12px 0;
}

.support-success-icon {
    font-size: 56px;
    color: #2e7d32;
    display: block;
    margin-bottom: 12px;
}

.support-success h3 {
    margin: 0 0 8px;
    font-size: 1.25rem;
}

.support-success p {
    color: var(--text-secondary);
    margin: 0 0 20px;
    line-height: 1.6;
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@media (max-width: 768px) {
    .support-form .form-row {
        grid-template-columns: 1fr;
    }

    .confirm-card-actions {
        flex-direction: column;
    }

    .confirm-card-actions .mui-btn {
        width: 100%;
    }
}
</style>
