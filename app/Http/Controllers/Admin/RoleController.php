<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:roles.manage');
    }

    public function index(): View
    {
        $roles = Role::withCount('permissions')->paginate(15);

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = Permission::all()->groupBy(fn ($p) => explode('.', $p->name)[0]);

        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::all()->groupBy(fn ($p) => explode('.', $p->name)[0]);
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'unique:roles,name,'.$role->id],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, ['super-admin', 'admin'])) {
            return back()->with('error', 'This role cannot be deleted.');
        }

        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }
}
