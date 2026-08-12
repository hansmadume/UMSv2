<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'full_name' => 'required|string|max:150',
            'username' => 'required|string|max:100|unique:'.User::class.',username',
            'email' => 'required|string|email|max:190|unique:'.User::class.',email',
            'password' => ['required', 'confirmed', Rules\Password::min(6)],
        ]);

        $guestRole = Role::where('name', 'Guest')->first();

        $user = User::create([
            'full_name' => $request->full_name,
            'name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'password_hash' => $request->password,
            'status' => 'active',
        ]);

        if ($guestRole) {
            $user->assignRole($guestRole);
        }

        AuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->getDisplayName(),
            'action' => 'User Registered',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
