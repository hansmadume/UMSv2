<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request)
    {
        $totalUsers = User::where('status', 'active')->count();
        $totalRoles = Role::where('status', 'active')->count();
        $totalLogs = AuditLog::count();

        $recentUsers = User::with(['role', 'roles'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentLogs = AuditLog::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Notifications (recent logins or important events)
        $notifications = $recentLogs->map(function ($log) {
            $icon = match (true) {
                str_contains($log->action, 'Login') => 'login',
                str_contains($log->action, 'Created') => 'add_circle',
                str_contains($log->action, 'Updated') => 'edit',
                str_contains($log->action, 'Deleted') => 'delete',
                default => 'notifications',
            };

            return [
                'type' => 'info',
                'icon' => $icon,
                'title' => $log->action,
                'message' => "by {$log->user_name}",
                'time' => $log->created_at?->diffForHumans() ?? 'Just now',
            ];
        })->take(5)->values()->all();

        return inertia('Dashboard', [
            'stats' => [
                'total_users' => $totalUsers,
                'total_roles' => $totalRoles,
                'total_logs' => $totalLogs,
            ],
            'recentUsers' => $recentUsers,
            'recentLogs' => $recentLogs,
            'notifications' => $notifications,
        ]);
    }
}
