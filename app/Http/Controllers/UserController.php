<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCredentialsMail;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $field  = $request->get('field', 'all');

        $users = User::query()
            ->with('roles')
            ->when($search, function ($q) use ($search, $field) {
                $q->where(function ($query) use ($search, $field) {
                    if ($field === 'all') {
                        $query->where('id', 'like', "%{$search}%")
                              ->orWhere('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%")
                              ->orWhereHas('roles', function ($roleQ) use ($search) {
                                  $roleQ->where('name', 'like', "%{$search}%");
                              });
                    } elseif ($field === 'id') {
                        $query->where('id', 'like', "%{$search}%");
                    } elseif ($field === 'name') {
                        $query->where('name', 'like', "%{$search}%");
                    } elseif ($field === 'email') {
                        $query->where('email', 'like', "%{$search}%");
                    } elseif ($field === 'role') {
                        $query->whereHas('roles', function ($roleQ) use ($search) {
                            $roleQ->where('name', 'like', "%{$search}%");
                        });
                    }
                });
            })
            ->orderBy('id', 'asc')
            ->paginate(10);

        return view('users.index', compact('users', 'search'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:150','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'roles'    => ['required','array','min:1'],
            'roles.*'  => ['string','exists:roles,name'],
        ]);

        $plainPassword = $request->password;

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($plainPassword),
        ]);

        // Assign roles by name
        $user->syncRoles($request->roles);


        // Send credentials email
        try {
            Mail::to($user->email)->send(
                new UserCredentialsMail(
                    user: $user,
                    plainPassword: $plainPassword,           // send on create
                    roles: $request->roles,
                    event: 'created',
                    passwordChanged: true
                )
            );
        } catch (\Throwable $e) {
            // Optional: log error but don't block the request
            \Log::error('UserCredentialsMail failed: '.$e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $userRoles = $user->roles->pluck('name')->toArray();
        return view('users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:150', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','string','min:8','confirmed'],
            'roles'    => ['required','array','min:1'],
            'roles.*'  => ['string','exists:roles,name'],
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;

        $passwordChanged = false;
        $plainPassword   = null;
        if ($request->filled('password')) {
            $passwordChanged = true;
            $plainPassword   = $request->password;
            $user->password  = bcrypt($plainPassword);
        }

        $user->save();
        $user->syncRoles($request->roles);

        // Send update email (include password only if changed)
        try {
            // Get final role names to display (fresh)
            $finalRoles = $user->roles()->pluck('name')->all();

            Mail::to($user->email)->send(
                new UserCredentialsMail(
                    user: $user,
                    plainPassword: $plainPassword, // null if not changed
                    roles: $finalRoles,
                    event: 'updated',
                    passwordChanged: $passwordChanged
                )
            );
        } catch (\Throwable $e) {
            \Log::error('UserCredentialsMail (update) failed: '.$e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('success', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
