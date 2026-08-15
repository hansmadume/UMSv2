<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $notifications = [];

        if ($user) {
            $notifications = AuditLog::query()
                ->when($user->can('audit_logs.view'), function (Builder $query) {
                    $query->latest('created_at')->limit(8);
                }, function (Builder $query) use ($user) {
                    if ($user->can('notifications.view')) {
                        $query->where(function (Builder $q) use ($user) {
                            $q->where('user_id', $user->id)
                                ->orWhere('action', 'like', 'Support Ticket%');
                        });
                    } else {
                        $query->where('user_id', $user->id);
                    }
                    $query->latest('created_at')->limit(8);
                })
                ->get(['id', 'user_name', 'action', 'created_at'])
                ->map(function ($log) {
                    return [
                        'id' => 'audit-'.$log->id,
                        'title' => $log->action,
                        'message' => ($log->user_name ?: 'System').' - '.($log->action ?: 'Activity'),
                        'time' => $log->created_at?->toIso8601String(),
                        'icon' => 'info',
                        'read' => false,
                    ];
                })
                ->values()
                ->all();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'full_name' => $user->full_name,
                    'name' => $user->getDisplayName(),
                    'role' => $user->roles->first()?->name,
                    'permissions' => $user->getAllPermissions()->pluck('slug'),
                    'is_admin' => $user->hasRole('Administrator'),
                    'is_manager' => $user->hasRole('Manager'),
                    'is_support_staff' => $user->hasRole('Support Staff'),
                    'profile_photo' => $user->profile_photo,
                    'last_login' => $user->last_login?->toIso8601String(),
                    'created_at' => $user->created_at?->toIso8601String(),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'login_notice' => fn () => $request->session()->get('login_notice'),
            ],
            'notifications' => $notifications,
        ];
    }
}
