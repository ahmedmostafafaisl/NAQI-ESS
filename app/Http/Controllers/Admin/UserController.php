<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users.view')->only('index', 'show');
        $this->middleware('permission:users.create')->only('create', 'store');
        $this->middleware('permission:users.edit')->only('edit', 'update');
        $this->middleware('permission:users.delete')->only('destroy');
    }

    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->search, fn($q) => $q->where('username', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%"))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::pluck('name', 'name');

        return view('users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6'],
            'type' => ['required', 'in:employee,customer'],
            'status' => ['required', 'in:active,inactive'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $role = $data['role'];
        unset($data['role']);

        $user = User::create([
            ...$data,
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($role);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $roles = Role::pluck('name', 'name');

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'string', 'unique:users,phone,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'type' => ['required', 'in:employee,customer'],
            'status' => ['required', 'in:active,inactive'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $role = $data['role'];
        unset($data['role']);

        $user->fill($data);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user->syncRoles([$role]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }
}
