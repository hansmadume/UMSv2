<?php

namespace App\Http\Requests\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     * Allows login by username OR email against the legacy password_hash column.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $identifier = trim((string) $this->input('identifier'));
        $password = (string) $this->input('password');
        $remember = $this->boolean('remember');

        $user = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (! $user || ! Hash::check($password, $user->password_hash)) {
            RateLimiter::hit($this->throttleKey());

            AuditLog::create([
                'user_id' => $user?->id,
                'user_name' => $user?->getDisplayName() ?: $identifier,
                'action' => 'Login Failed',
                'ip_address' => $this->ip(),
                'created_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'identifier' => trans('auth.failed'),
            ]);
        }

        if ($user->status !== 'active') {
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->getDisplayName(),
                'action' => 'Login Failed',
                'ip_address' => $this->ip(),
                'created_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'identifier' => 'Your account is inactive. Please contact an administrator.',
            ]);
        }

        Auth::login($user, $remember);

        $user->forceFill(['last_login' => now()])->saveQuietly();

        AuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->getDisplayName(),
            'action' => 'Login Successful',
            'ip_address' => $this->ip(),
            'created_at' => now(),
        ]);

        // Stamp session activity so inactivity middleware starts counting now.
        $this->session()->put('last_activity_time', time());

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'identifier' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('identifier')).'|'.$this->ip());
    }
}
