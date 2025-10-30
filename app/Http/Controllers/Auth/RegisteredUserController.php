<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCredentialsMail;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration form with a role dropdown.
     */
    public function create(): View
    {
        // Use the default guard and hide roles you don't want public users to choose (e.g., Admin)
        $guard = config('auth.defaults.guard', 'web');

        $roles = Role::where('guard_name', $guard)
            ->whereNotIn('name', ['Admin'])   // adjust to your needs
            ->orderBy('name')
            ->get();

        return view('auth.register', compact('roles'));
    }

    /**
     * Handle registration.
     */
    public function store(Request $request): RedirectResponse
    {
        $guard = config('auth.defaults.guard', 'web');
        $plainPassword = $request->password;

        $allowedRoles = Role::where('guard_name', $guard)
            ->whereNotIn('name', ['Admin'])
            ->pluck('name')
            ->all();

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', 'string', Rule::in($allowedRoles)],
        ]);

        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => strtolower($request->input('email')),
            'password' => Hash::make($request->input('password')),
        ]);

        // Assign the selected role
        $user->assignRole($request->input('role'));

        // Build an ARRAY of roles for the email
        $finalRoles = $user->roles()->pluck('name')->all(); // ['Manager'] etc.

        try {
            Mail::to($user->email)->send(
                new UserCredentialsMail(
                    user: $user,
                    plainPassword: $plainPassword,
                    roles: $finalRoles,        // <-- pass array, not string
                    event: 'created',
                    passwordChanged: true
                )
            );
        } catch (\Throwable $e) {
            \Log::error('UserCredentialsMail failed: '.$e->getMessage());
        }

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('clients.index');
    }
}
