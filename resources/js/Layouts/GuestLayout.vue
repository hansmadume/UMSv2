<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="login-wrapper">
        <div class="login-card mui-card">
            <div class="login-header">
                <div class="login-icon">
                    <span class="material-icons">admin_panel_settings</span>
                </div>
                <h1>User Management System</h1>
                <p class="login-subtitle">Sign in to your account</p>
            </div>

            <slot />

            <div class="login-footer">
                <p>&copy; {{ new Date().getFullYear() }} User Management System. All rights reserved.</p>
            </div>
        </div>
    </div>
</template>