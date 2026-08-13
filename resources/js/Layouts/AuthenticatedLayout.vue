<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import ContactSupportModal from '@/Components/ContactSupportModal.vue';

const page = usePage();

const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.is_admin);
const isManager = computed(() => user.value?.is_manager);
const canManageUsers = computed(() => isAdmin.value || isManager.value);
const backendNotifications = computed(() => page.props.notifications || []);
const flash = computed(() => page.props.flash || {});

const userDisplayName = computed(() => user.value?.full_name || user.value?.username || user.value?.email || 'User');
const userRoleLabel = computed(() => user.value?.role || (user.value?.is_admin ? 'Administrator' : user.value?.is_manager ? 'Manager' : 'User'));

const notifications = computed(() => {
    const signedInNotification = {
        id: 'signed-in',
        title: 'Signed in as ' + userRoleLabel.value,
        message: (userRoleLabel.value || 'User') + ' access is active. You can monitor users, roles, and audit activity.',
        time: new Date().toISOString(),
        icon: 'verified_user',
        read: false,
    };
    return [signedInNotification, ...backendNotifications.value];
});

const notificationCount = computed(() => notifications.value.length);
const currentPage = computed(() => {
    const component = page.component;
    const match = component.match(/^([^\\/]+)/);
    if (!match) return 'dashboard';
    const raw = match[1];
    const withSpaces = raw.replace(/([a-z])([A-Z])/g, '$1 $2').replace(/_/g, ' ');
    return withSpaces.toLowerCase();
});

const notificationPanelOpen = ref(false);
const notificationsRead = ref(false);
const logoutModalOpen = ref(false);
const contactSupportOpen = ref(false);

watch(logoutModalOpen, (isOpen) => {
    if (isOpen) {
        document.body.classList.add('modal-open');
    } else {
        document.body.classList.remove('modal-open');
    }
});

watch(contactSupportOpen, (isOpen) => {
    if (isOpen) {
        document.body.classList.add('modal-open');
    } else {
        document.body.classList.remove('modal-open');
    }
});

let clockInterval = null;
let sidebarOpen = false;

const startClock = (baseDate, timeZone, isOnline) => {
    const baseTimestamp = baseDate.getTime();
    const basePerformanceTime = performance.now();

    const tick = () => {
        const currentDate = new Date(baseTimestamp + (performance.now() - basePerformanceTime));
        const timeEl = document.getElementById('topbarClockTime');
        const dateEl = document.getElementById('topbarClockDate');
        const clockEl = document.querySelector('.topbar-clock');
        if (!timeEl || !dateEl) return;

        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        const dateOptions = { month: '2-digit', day: '2-digit', year: 'numeric' };
        if (timeZone) {
            timeOptions.timeZone = timeZone;
            dateOptions.timeZone = timeZone;
        }

        timeEl.textContent = new Intl.DateTimeFormat(undefined, timeOptions).format(currentDate);
        dateEl.textContent = new Intl.DateTimeFormat(undefined, dateOptions).format(currentDate);

        if (clockEl) {
            clockEl.setAttribute('title', isOnline && timeZone ? 'Online synced time · ' + timeZone : 'Current time');
        }
    };

    tick();
    clockInterval = setInterval(tick, 1000);
};

const applyOnlineTime = () => {
    startClock(new Date(), '', false);
};

const toggleTheme = () => {
    const current = document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
    const next = current === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', next);
    try { localStorage.setItem('ums-theme', next); } catch {}
};

const toggleSidebar = () => {
    sidebarOpen = !sidebarOpen;
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open', sidebarOpen);
    }
};

const closeSidebar = () => {
    if (window.innerWidth <= 768) {
        sidebarOpen = false;
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) sidebar.classList.remove('open');
    }
};

const toggleNotificationPanel = () => {
    notificationPanelOpen.value = !notificationPanelOpen.value;
    const panel = document.getElementById('notificationPanel');
    const toggle = document.querySelector('.notification-toggle');
    if (panel && toggle) {
        panel.hidden = !notificationPanelOpen.value;
        toggle.setAttribute('aria-expanded', String(notificationPanelOpen.value));
    }
    if (notificationPanelOpen.value) {
        notificationsRead.value = true;
        try { localStorage.setItem('ums-notifications-read', 'true'); } catch {}
    }
};

const markNotificationsRead = () => {
    notificationsRead.value = true;
    try { localStorage.setItem('ums-notifications-read', 'true'); } catch {}
};

const closeNotificationPanel = () => {
    notificationPanelOpen.value = false;
    const panel = document.getElementById('notificationPanel');
    const toggle = document.querySelector('.notification-toggle');
    if (panel) panel.hidden = true;
    if (toggle) toggle.setAttribute('aria-expanded', 'false');
};

const formatNotificationTime = (time) => {
    if (!time) return '';
    const date = new Date(time);
    if (Number.isNaN(date.getTime())) return '';
    return date.toLocaleString();
};

const logout = () => {
    logoutModalOpen.value = true;
};

const confirmLogout = () => {
    logoutModalOpen.value = false;
    router.post(route('logout'));
};

const cancelLogout = () => {
    logoutModalOpen.value = false;
};

const addRipple = (e) => {
    const btn = e.currentTarget;
    const old = btn.querySelector('.ripple-effect');
    if (old) old.remove();

    const ripple = document.createElement('span');
    const rect = btn.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    ripple.className = 'ripple-effect';
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
    ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
    btn.appendChild(ripple);
    setTimeout(() => ripple.remove(), 600);
};

const initInputs = () => {
    document.querySelectorAll('.mui-input, .mui-select').forEach((el) => {
        if (el.value && el.value.trim && el.value.trim() !== '') {
            el.classList.add('has-value');
        }
        el.addEventListener('input', () => {
            el.classList.toggle('has-value', el.value && el.value.trim() !== '');
        });
        el.addEventListener('change', () => {
            el.classList.toggle('has-value', el.value !== '');
        });
    });
};

onMounted(() => {
    const savedTheme = localStorage.getItem('ums-theme');
    if (savedTheme === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
    }

    applyOnlineTime();

    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }

    const menuToggle = document.getElementById('menuToggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', toggleSidebar);
    }

    document.addEventListener('click', (e) => {
        if (window.innerWidth > 768) return;
        const sidebar = document.querySelector('.sidebar');
        const menu = document.getElementById('menuToggle');
        if (sidebar && menu && !sidebar.contains(e.target) && !menu.contains(e.target)) {
            closeSidebar();
        }
    });

    const notificationToggle = document.querySelector('.notification-toggle');
    const notificationPanel = document.getElementById('notificationPanel');

    if (notificationToggle && notificationPanel) {
        const storageKey = 'ums-notifications-read';

        try {
            if (localStorage.getItem(storageKey) === 'true') {
                notificationsRead.value = true;
            }
        } catch {}

        notificationToggle.addEventListener('click', (event) => {
            event.stopPropagation();
            toggleNotificationPanel();
        });

        notificationPanel.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        document.addEventListener('click', () => {
            if (!notificationPanel.hidden) {
                closeNotificationPanel();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !notificationPanel.hidden) {
                closeNotificationPanel();
                notificationToggle.focus();
            }
        });
    }

    document.querySelectorAll('.mui-btn').forEach((btn) => {
        btn.addEventListener('click', addRipple);
    });

    initInputs();
});

onUnmounted(() => {
    if (clockInterval) clearInterval(clockInterval);
});
</script>

<template>
    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <span class="material-icons sidebar-logo">admin_panel_settings</span>
                <h2>UMS</h2>
            </div>
            <nav class="sidebar-nav">
                <Link :href="route('dashboard')" class="nav-item" :class="{ active: route().current('dashboard') }">
                    <span class="material-icons">dashboard</span>
                    <span class="nav-text">Dashboard</span>
                </Link>
                <Link v-if="canManageUsers" :href="route('users.index')" class="nav-item" :class="{ active: route().current('users.*') }">
                    <span class="material-icons">group</span>
                    <span class="nav-text">Users  {{permissions}}</span>
                </Link>
                <Link v-if="isAdmin" :href="route('roles.index')" class="nav-item" :class="{ active: route().current('roles.*') }">
                    <span class="material-icons">security</span>
                    <span class="nav-text">Roles</span>
                </Link>
                <Link v-if="isAdmin" :href="route('audit-logs.index')" class="nav-item" :class="{ active: route().current('audit-logs.*') }">
                    <span class="material-icons">history</span>
                    <span class="nav-text">Audit Logs</span>
                </Link>
                <Link :href="route('profile.edit')" class="nav-item" :class="{ active: route().current('profile.*') }">
                    <span class="material-icons">person</span>
                    <span class="nav-text">Profile</span>
                </Link>
                <button type="button" class="nav-item" @click="contactSupportOpen = true">
                    <span class="material-icons">support_agent</span>
                    <span class="nav-text">Contact Support</span>
                </button>
                <div class="nav-spacer"></div>
                <button type="button" class="nav-item logout" @click="logout">
                    <span class="material-icons">logout</span>
                    <span class="nav-text">Logout</span>
                </button>
            </nav>
        </aside>

        <div class="main-wrapper">
            <header class="topbar">
                <div class="topbar-left">
                    <span class="material-icons topbar-menu-icon" id="menuToggle">menu</span>
                    <h3 class="topbar-title">{{ currentPage.replace(/\b\w/g, l => l.toUpperCase()) }}</h3>
                </div>
                <div class="topbar-right">
                    <button id="themeToggle" class="theme-toggle" type="button" aria-label="Toggle light/dark mode" title="Toggle light/dark mode">
                        <span class="theme-toggle-track">
                            <span class="theme-toggle-thumb">
                                <span class="material-icons theme-toggle-icon">dark_mode</span>
                            </span>
                        </span>
                    </button>
                    <div class="topbar-clock" title="Online synced time">
                        <span class="material-icons topbar-clock-icon">schedule</span>
                        <span class="topbar-clock-text">
                            <span class="topbar-clock-time" id="topbarClockTime">--:--:-- --</span>
                            <span class="topbar-clock-date" id="topbarClockDate">--/--/----</span>
                        </span>
                    </div>
                    <span class="topbar-user">{{ user?.name || user?.username || user?.email }}</span>
                    <div class="notification-menu">
                        <button
                            class="topbar-icon notification-toggle"
                            type="button"
                            aria-label="Open notifications"
                            aria-expanded="false"
                        >
                            <span class="material-icons">notifications</span>
                            <span v-if="notificationCount > 0 && !notificationsRead" class="notification-badge">{{ Math.min(notificationCount, 9) }}</span>
                        </button>
                        <div id="notificationPanel" class="notification-panel" hidden>
                            <div class="notification-panel-header">
                                <div>
                                    <h4>Notifications</h4>
                                    <p>{{ notificationCount }} updates</p>
                                </div>
                                <span class="material-icons">campaign</span>
                            </div>
                            <div class="notification-list">
                                <div
                                    v-for="item in notifications"
                                    :key="item.id"
                                    class="notification-item"
                                    :class="'notification-' + (item.icon || 'default')"
                                >
                                    <div class="notification-item-icon">
                                        <span class="material-icons">{{ item.icon || 'info' }}</span>
                                    </div>
                                    <div class="notification-item-content">
                                        <strong>{{ item.title }}</strong>
                                        <p>{{ item.message }}</p>
                                        <small>{{ formatNotificationTime(item.time) }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <Link :href="route('profile.edit')" class="topbar-profile-link" aria-label="Profile">
                        <img
                            v-if="user?.profile_photo"
                            :src="user.profile_photo"
                            alt="Profile"
                            class="topbar-profile-img"
                        />
                        <span v-else class="material-icons topbar-icon">account_circle</span>
                    </Link>
                </div>
            </header>

            <main class="content">
                <div v-if="flash.success" class="login-alert login-alert-info" role="alert">
                    {{ flash.success }}
                </div>
                <div v-if="flash.error" class="login-alert login-alert-error" role="alert">
                    {{ flash.error }}
                </div>

                <slot name="header">
                    <div class="section-header">
                        <h2 class="topbar-title" style="font-size: 1.5rem;">{{ currentPage.replace(/\b\w/g, l => l.toUpperCase()) }}</h2>
                    </div>
                </slot>

                <slot />
            </main>

            <footer class="main-footer">
                <p>&copy; {{ new Date().getFullYear() }} User Management System.</p>
            </footer>
        </div>

        <div v-if="logoutModalOpen" class="confirm-modal" @click.self="cancelLogout" @keydown.escape="cancelLogout">
            <div class="confirm-modal-backdrop"></div>
            <div class="confirm-card" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle">
                <div class="confirm-card-icon">
                    <span class="material-icons">logout</span>
                </div>
                <div class="confirm-card-content">
                    <h3 id="logoutModalTitle">Confirm Logout</h3>
                    <p>Are you sure you want to log out of your account?</p>
                </div>
                <div class="confirm-card-actions">
                    <button type="button" class="mui-btn mui-btn-outlined" @click="cancelLogout">Cancel</button>
                    <button type="button" class="mui-btn mui-btn-danger confirm-approve" @click="confirmLogout">Yes, Logout</button>
                </div>
            </div>
        </div>

        <ContactSupportModal
            :show="contactSupportOpen"
            :user="user"
            @close="contactSupportOpen = false"
        />
    </div>
</template>