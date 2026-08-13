<template>
    <Head :title="'Ticket ' + ticket.ticket_id" />

    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>Ticket {{ ticket.ticket_id }}</h2>
            </div>
        </template>

        <div class="ticket-detail" v-if="ticket">
            <div class="mui-card">
                <div class="ticket-header">
                    <div>
                        <h3>{{ ticket.subject }}</h3>
                        <div class="ticket-meta">
                            <span class="ticket-id">#{{ ticket.ticket_id }}</span>
                            <span class="ticket-category">{{ ticket.category }}</span>
                            <span :class="['priority-badge', ticket.priority]">{{ ticket.priority }}</span>
                            <span :class="['status-badge', ticket.status]">{{ ticket.status }}</span>
                        </div>
                    </div>
                    <div class="ticket-actions" v-if="canManageTicket">
                        <select v-model="ticket.status" class="mui-select" @change="updateTicket('status', $event.target.value)">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                        <select v-model="ticket.priority" class="mui-select" @change="updateTicket('priority', $event.target.value)">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        <select v-model="ticket.assigned_to" class="mui-select" @change="updateTicket('assigned_to', $event.target.value)">
                            <option value="">Unassigned</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name || u.username }}</option>
                        </select>
                    </div>
                </div>

                <div class="ticket-section">
                    <h4>Description</h4>
                    <p class="ticket-description">{{ ticket.comments }}</p>
                    <div v-if="ticket.attachment_path" class="ticket-attachment">
                        <span class="material-icons">attach_file</span>
                        <a :href="'/storage/' + ticket.attachment_path" target="_blank">View Attachment</a>
                    </div>
                </div>

                <div class="ticket-section" v-if="canAddNotes">
                    <h4>Internal Notes</h4>
                    <textarea
                        v-model="internalNotes"
                        rows="3"
                        class="mui-textarea"
                        placeholder="Add internal notes..."
                    ></textarea>
                    <button type="button" class="mui-btn mui-btn-contained mui-btn-sm" @click="updateTicket('internal_notes', internalNotes)">
                        Save Notes
                    </button>
                </div>

                <div class="ticket-section" v-if="ticket.internal_notes">
                    <h4>Notes</h4>
                    <p class="internal-notes-text">{{ ticket.internal_notes }}</p>
                </div>

                <div class="ticket-footer">
                    <div class="ticket-dates">
                        <span>Created: {{ formatDate(ticket.created_at) }}</span>
                        <span>Updated: {{ formatDate(ticket.updated_at) }}</span>
                    </div>
                    <div class="ticket-people">
                        <span>Created By: {{ ticket.username || 'Anonymous' }}</span>
                        <span v-if="ticket.assignedTo">Assigned To: {{ ticket.assignedTo.name || ticket.assignedTo.username }}</span>
                    </div>
                </div>
            </div>

            <div v-if="canReply" class="mui-card reply-card">
                <h4>Reply</h4>
                <form @submit.prevent="submitReply">
                    <textarea
                        v-model="replyText"
                        rows="4"
                        class="mui-textarea"
                        placeholder="Write your reply..."
                        required
                    ></textarea>
                    <div class="reply-actions">
                        <input
                            type="file"
                            id="reply_attachment"
                            @change="onFileChange"
                            class="mui-file-input"
                        />
                        <label for="reply_attachment" class="mui-file-label">
                            <span class="material-icons">attach_file</span>
                            {{ replyAttachment ? replyAttachment.name : 'Attach files' }}
                        </label>
                        <button type="submit" class="mui-btn mui-btn-contained" :disabled="submitting">
                            Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/Components/InputError.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.is_admin);
const userRole = computed(() => user.value?.role);
const ticket = computed(() => page.props.ticket);
const users = computed(() => page.props.users || []);

const canManageTicket = computed(() => {
    return isAdmin.value || userRole.value === 'Support Staff';
});

const canAddNotes = computed(() => {
    return isAdmin.value || userRole.value === 'Support Staff';
});

const canReply = computed(() => {
    return isAdmin.value || userRole.value === 'Support Staff';
});

const internalNotes = ref(ticket.value?.internal_notes || '');
const replyText = ref('');
const replyAttachment = ref(null);
const submitting = ref(false);

const onFileChange = (e) => {
    replyAttachment.value = e.target.files[0] || null;
};

const updateTicket = (field, value) => {
    const form = new FormData();
    form.append('_method', 'PUT');
    form.append(field, value);

    fetch(route('support.update', ticket.value.id), {
        method: 'POST',
        body: form,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    }).then(() => {
        // Optionally reload or show success
    });
};

const submitReply = () => {
    const form = new FormData();
    form.append('_method', 'PUT');
    form.append('comments', replyText.value);
    if (replyAttachment.value) {
        form.append('attachment', replyAttachment.value);
    }

    submitting.value = true;
    fetch(route('support.update', ticket.value.id), {
        method: 'POST',
        body: form,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    }).then(() => {
        replyText.value = '';
        replyAttachment.value = null;
        submitting.value = false;
        window.location.reload();
    });
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleString();
};
</script>

<style scoped>
.ticket-detail {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.ticket-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    flex-wrap: wrap;
}

.ticket-header h3 {
    margin: 0 0 8px;
}

.ticket-meta {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.ticket-id {
    font-family: monospace;
    font-size: 0.9rem;
    background: rgba(0, 0, 0, 0.05);
    padding: 2px 8px;
    border-radius: 4px;
}

.ticket-category {
    font-size: 0.85rem;
    color: var(--text-secondary, #6b7280);
}

.ticket-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.ticket-section {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid rgba(0, 0, 0, 0.06);
}

.ticket-section h4 {
    margin: 0 0 12px;
    font-size: 1rem;
    color: var(--text-primary, #1f2937);
}

.ticket-description {
    white-space: pre-wrap;
    line-height: 1.6;
    color: var(--text-primary, #1f2937);
}

.ticket-attachment {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
}

.ticket-attachment .material-icons {
    color: var(--text-secondary, #6b7280);
}

.internal-notes-text {
    background: #fffbdd;
    padding: 12px;
    border-radius: 8px;
    border-left: 4px solid #fbc02d;
    white-space: pre-wrap;
    line-height: 1.6;
}

.ticket-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid rgba(0, 0, 0, 0.06);
    flex-wrap: wrap;
    gap: 8px;
}

.ticket-dates,
.ticket-people {
    display: flex;
    gap: 16px;
    font-size: 0.85rem;
    color: var(--text-secondary, #6b7280);
}

.reply-card {
    padding: 20px;
}

.reply-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 12px;
    flex-wrap: wrap;
}

.reply-actions .mui-file-input {
    display: none;
}

.reply-actions .mui-file-label {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    color: var(--text-secondary, #6b7280);
}

.status-badge {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    padding: 4px 8px;
    border-radius: 4px;
}

.status-badge.open {
    background: #e3f2fd;
    color: #1565c0;
}

.status-badge.in_progress {
    background: #fff3e0;
    color: #ef6c00;
}

.status-badge.resolved {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-badge.closed {
    background: #eceff1;
    color: #455a64;
}

.priority-badge {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 4px;
}

.priority-badge.low {
    background: #e3f2fd;
    color: #1565c0;
}

.priority-badge.medium {
    background: #fff3e0;
    color: #ef6c00;
}

.priority-badge.high {
    background: #fce4ec;
    color: #c2185b;
}

.priority-badge.urgent {
    background: #ffebee;
    color: #b71c1c;
}

@media (max-width: 768px) {
    .ticket-header {
        flex-direction: column;
    }

    .ticket-footer {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
