<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(protected RoleService $roles)
    {
        $this->middleware('permission:roles.manage');
    }

    public function index(): View
    {
        $roles = $this->roles->paginate(perPage: 15, page: (int) request('page', 1), pageName: 'page');

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = $this->roles->permissionsGroupedByPrefix();

        return view('roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->roles->create($request->validated('name'), $request->validated('permissions') ?? []);

        return redirect()->route('admin.roles.index')->with('success', __('admin.roles.created_success'));
    }

    public function edit(Role $role): View
    {
        $permissions = $this->roles->permissionsGroupedByPrefix();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->roles->update($role, $request->validated('name'), $request->validated('permissions') ?? []);

        return redirect()->route('admin.roles.index')->with('success', __('admin.roles.updated_success'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($this->roles->isProtected($role)) {
            return back()->with('error', __('admin.roles.protected'));
        }

        $this->roles->delete($role);

        return back()->with('success', __('admin.roles.deleted_success'));
    }
}
