<template>
    <Head :title="(isMyTicket ? 'My Ticket' : 'Ticket') + ' ' + ticket.ticket_id" />

    <AuthenticatedLayout>
        <template #header>
            <div class="section-header">
                <h2>{{ isMyTicket ? 'My Ticket' : 'Ticket' }} {{ ticket.ticket_id }}</h2>
            </div>
        </template>

        <div class="ticket-detail" v-if="ticket">
            <div class="mui-card">
                <div class="ticket-header">
                    <div>
                        <h3>{{ ticket.subject }}</h3>
                            <div class="ticket-meta">
                                <span class="ticket-id">#{{ ticket.ticket_id }}</span>
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
                    <h4>Conversation</h4>
                    <div class="conversation">
                        <div class="message customer-message">
                            <div class="message-bubble">
                                <div class="message-header">
                                    <span class="message-author">{{ ticket.username || 'You' }}</span>
                                    <span class="message-time">{{ formatDate(ticket.created_at) }}</span>
                                </div>
                                <p class="message-text">{{ ticket.comments }}</p>
                                <div v-if="ticket.attachment_path" class="message-attachment">
                                    <span class="material-icons">attach_file</span>
                                    <a :href="'/storage/' + ticket.attachment_path" target="_blank">View Attachment</a>
                                </div>
                            </div>
                        </div>

                        <div v-for="msg in sortedMessages" :key="msg.id" class="message" :class="msg.user_id === user.id ? 'customer-message' : 'support-message'">
                            <div class="message-bubble">
                                <div class="message-header">
                                    <span class="message-author">{{ msg.user_id === user.id ? 'You' : (msg.user?.name || msg.user?.username || 'Support Staff') }}</span>
                                    <span class="message-time">{{ formatDate(msg.created_at) }}</span>
                                </div>
                                <p class="message-text">{{ msg.message }}</p>
                                <div v-if="msg.attachment_path" class="message-attachment">
                                    <span class="material-icons">attach_file</span>
                                    <a :href="'/storage/' + msg.attachment_path" target="_blank">View Attachment</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!canReply" class="ticket-section reply-email-notice">
                    <h4>Reply to This Ticket</h4>
                    <p>To reply to this ticket, please use the reply button in the email you received when this ticket was created. Our support team will respond to your message.</p>
                </div>

                <div v-if="canReply" class="ticket-section reply-section">
                    <h4>Reply</h4>
                    <form @submit.prevent="submitReply">
                        <textarea
                            v-model="replyText"
                            rows="4"
                            class="mui-textarea"
                            placeholder="Type your reply..."
                            required
                            :disabled="replyStatus === 'sending'"
                        ></textarea>
                        <div class="reply-actions">
                            <input
                                type="file"
                                id="reply_attachment"
                                @change="onFileChange"
                                class="mui-file-input"
                                :disabled="replyStatus === 'sending'"
                            />
                            <label for="reply_attachment" class="mui-file-label">
                                <span class="material-icons">attach_file</span>
                                {{ replyAttachment ? replyAttachment.name : 'Attach files' }}
                            </label>
                            <button type="submit" class="mui-btn mui-btn-contained" :disabled="replyStatus === 'sending'">
                                <span v-if="replyStatus === 'sending'" class="material-icons spin">refresh</span>
                                <span v-else-if="replyStatus === 'sent'" class="material-icons">check</span>
                                <span v-else-if="replyStatus === 'error'" class="material-icons">error</span>
                                <span v-else class="material-icons">send</span>
                                {{ replyStatus === 'sending' ? 'Sending...' : replyStatus === 'sent' ? 'Sent' : replyStatus === 'error' ? 'Failed' : 'Send Reply' }}
                            </button>
                        </div>
                        <div v-if="replyStatus === 'sent'" class="reply-status reply-status-success">
                            Reply sent successfully. The customer will receive an email notification.
                        </div>
                        <div v-if="replyStatus === 'error'" class="reply-status reply-status-error">
                            {{ replyError }}
                        </div>
                    </form>
                </div>

                <div class="ticket-footer">
                    <div class="ticket-dates">
                        <span>Created: {{ formatDate(ticket.created_at) }}</span>
                        <span>Updated: {{ formatDate(ticket.updated_at) }}</span>
                    </div>
                    <div class="ticket-people" v-if="canManageTicket">
                        <span>Created By: {{ ticket.username || 'Anonymous' }}</span>
                        <span v-if="ticket.assignedTo">Assigned To: {{ ticket.assignedTo.name || ticket.assignedTo.username }}</span>
                    </div>
                </div>
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

const isMyTicket = computed(() => {
    return ticket.value.user_id === user.value?.id;
});

const canManageTicket = computed(() => {
    return isAdmin.value || userRole.value === 'Support Staff' || userRole.value === 'Manager';
});

const canReply = computed(() => {
    return isAdmin.value || userRole.value === 'Support Staff' || userRole.value === 'Manager';
});

const sortedMessages = computed(() => {
    const messages = [...(ticket.value.messages || [])];
    return messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
});

const replyText = ref('');
const replyAttachment = ref(null);
const replyStatus = ref('idle'); // idle | sending | sent | error
const replyError = ref('');

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
        window.location.reload();
    }).catch(() => {
        window.location.reload();
    });
};

const submitReply = () => {
    const form = new FormData();
    form.append('_method', 'PUT');
    form.append('comments', replyText.value);
    if (replyAttachment.value) {
        form.append('attachment', replyAttachment.value);
    }

    replyStatus.value = 'sending';
    replyError.value = '';

    fetch(route('support.update', ticket.value.id), {
        method: 'POST',
        body: form,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    }).then(() => {
        replyStatus.value = 'sent';
        replyText.value = '';
        replyAttachment.value = null;
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }).catch(() => {
        replyStatus.value = 'error';
        replyError.value = 'Failed to send reply. Please try again.';
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
    background: var(--black-tertiary);
    padding: 2px 8px;
    border-radius: 4px;
}

.ticket-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.ticket-section {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--black-border);
}

.ticket-section h4 {
    margin: 0 0 12px;
    font-size: 1rem;
    color: var(--text-primary);
}

.ticket-description {
    white-space: pre-wrap;
    line-height: 1.6;
    color: var(--text-primary);
}

.ticket-attachment {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
}

.ticket-attachment .material-icons {
    color: var(--text-secondary);
}

.ticket-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
    flex-wrap: wrap;
    gap: 8px;
}

.ticket-dates,
.ticket-people {
    display: flex;
    gap: 16px;
    font-size: 0.85rem;
    color: #6b7280;
}

@media (prefers-color-scheme: dark) {
    .ticket-footer {
        border-top-color: var(--black-border);
    }

    .ticket-dates,
    .ticket-people {
        color: var(--text-secondary);
    }
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
    color: var(--text-secondary);
}

.status-badge {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    padding: 4px 8px;
    border-radius: 4px;
}

.status-badge.open {
    background: rgba(0, 200, 83, 0.12);
    color: var(--green-light);
}

.status-badge.in_progress {
    background: rgba(255, 183, 77, 0.12);
    color: var(--warning);
}

.status-badge.resolved {
    background: rgba(0, 200, 83, 0.14);
    color: var(--green-lighter);
}

.status-badge.closed {
    background: #e5e7eb;
    color: #4b5563;
}

@media (prefers-color-scheme: dark) {
    .status-badge.closed {
        background: rgba(255, 255, 255, 0.08);
        color: var(--text-muted);
    }
}

.priority-badge {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 4px;
}

.priority-badge.low {
    background: rgba(0, 200, 83, 0.12);
    color: var(--green-light);
}

.priority-badge.medium {
    background: rgba(255, 183, 77, 0.12);
    color: var(--warning);
}

.priority-badge.high {
    background: rgba(207, 102, 121, 0.12);
    color: var(--danger);
}

.priority-badge.urgent {
    background: rgba(207, 102, 121, 0.18);
    color: var(--danger);
}

.conversation {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.message {
    display: flex;
    width: 100%;
}

.message.customer-message {
    justify-content: flex-end;
}

.message.support-message {
    justify-content: flex-start;
}

.message-bubble {
    max-width: 70%;
    padding: 14px 18px;
    border-radius: 14px;
    line-height: 1.6;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
}

.customer-message .message-bubble {
    background: #2563eb;
    color: #fff;
    border-bottom-right-radius: 4px;
}

.support-message .message-bubble {
    background: #f3f4f6;
    color: #111827;
    border-bottom-left-radius: 4px;
}

@media (prefers-color-scheme: dark) {
    .customer-message .message-bubble {
        background: #3b82f6;
        color: #fff;
    }

    .support-message .message-bubble {
        background: var(--black-tertiary);
        color: var(--text-primary);
    }
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 6px;
}

.message-author {
    font-weight: 600;
    font-size: 0.85rem;
}

.customer-message .message-author {
    color: #fff;
}

.support-message .message-author {
    color: #111827;
}

@media (prefers-color-scheme: dark) {
    .support-message .message-author {
        color: var(--text-primary);
    }
}

.message-time {
    font-size: 0.75rem;
    opacity: 0.8;
}

.message-text {
    margin: 0;
    white-space: pre-wrap;
    word-break: break-word;
}

.message-attachment {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid rgba(0, 0, 0, 0.15);
    font-size: 0.85rem;
}

.customer-message .message-attachment {
    border-top-color: rgba(255, 255, 255, 0.3);
}

@media (prefers-color-scheme: dark) {
    .support-message .message-attachment {
        border-top-color: var(--black-border);
    }
}

.message-attachment .material-icons {
    font-size: 16px;
}

.ticket-message {
    display: flex;
    gap: 16px;
    align-items: flex-start;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.message-avatar .material-icons {
    color: #6b7280;
    font-size: 20px;
}

@media (prefers-color-scheme: dark) {
    .message-avatar {
        background: var(--black-tertiary);
    }

    .message-avatar .material-icons {
        color: var(--text-secondary);
    }
}

.message-content {
    flex: 1;
}

.message-content .message-header {
    margin-bottom: 8px;
}

.message-content .ticket-description {
    white-space: pre-wrap;
    line-height: 1.6;
    color: var(--text-primary);
}

.message-content .ticket-attachment {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
}

.message-content .ticket-attachment .material-icons {
    color: var(--text-secondary);
}

.reply-section {
    border-top: 1px solid #e5e7eb;
    padding-top: 24px;
    margin-top: 24px;
}

.reply-section h4 {
    margin: 0 0 12px;
    font-size: 1rem;
    color: #111827;
}

@media (prefers-color-scheme: dark) {
    .reply-section {
        border-top-color: var(--black-border);
    }

    .reply-section h4 {
        color: var(--text-primary);
    }
}

.reply-status {
    margin-top: 12px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 0.9rem;
    line-height: 1.5;
}

.reply-status-success {
    background: rgba(0, 200, 83, 0.12);
    color: #166534;
    border-left: 4px solid #16a34a;
}

.reply-status-error {
    background: rgba(207, 102, 121, 0.12);
    color: #991b1b;
    border-left: 4px solid #e11d48;
}

@media (prefers-color-scheme: dark) {
    .reply-status-success {
        color: var(--green-light);
        border-left-color: var(--green-light);
    }

    .reply-status-error {
        color: var(--danger);
        border-left-color: var(--danger);
    }
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

.reply-email-notice {
    background: #f3f4f6;
    padding: 16px;
    border-radius: 8px;
    border-left: 4px solid #2563eb;
}

.reply-email-notice h4 {
    margin: 0 0 8px;
    font-size: 1rem;
    color: #111827;
}

.reply-email-notice p {
    margin: 0;
    color: #374151;
    line-height: 1.6;
}

@media (prefers-color-scheme: dark) {
    .reply-email-notice {
        background: var(--black-tertiary);
        border-left-color: var(--primary);
    }

    .reply-email-notice h4 {
        color: var(--text-primary);
    }

    .reply-email-notice p {
        color: var(--text-secondary);
    }
}

@media (max-width: 768px) {
    .ticket-header {
        flex-direction: column;
    }

    .ticket-footer {
        flex-direction: column;
        align-items: flex-start;
    }

    .ticket-message {
        flex-direction: column;
    }

    .message-avatar {
        display: none;
    }
}
</style>
