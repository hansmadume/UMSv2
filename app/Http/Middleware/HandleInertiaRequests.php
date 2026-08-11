<?php

namespace App\Http\Middleware;

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
            $notifications = \App\Models\AuditLog::query()
                ->when($user->is_admin, function (Builder $query) {
                    $query->latest('created_at')->limit(8);
                }, function (Builder $query) use ($user) {
                    $query->where('user_id', $user->id)->latest('created_at')->limit(8);
                })
                ->get(['id', 'user_name', 'action', 'created_at'])
                ->map(function ($log) {
                    return [
                        'id' => 'audit-' . $log->id,
                        'title' => $log->action,
                        'message' => ($log->user_name ?: 'System') . ' - ' . ($log->action ?: 'Activity'),
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
                    'role' => $user->role?->name,
                    'is_admin' => $user->isAdmin(),
                    'is_manager' => $user->isManager(),
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
