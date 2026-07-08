<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:permissions.manage');
    }

    public function index(): View
    {
        $permissions = Permission::latest()->paginate(20);

        return view('permissions.index', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'unique:permissions,name'],
        ]);

        Permission::create(['name' => $data['name'], 'guard_name' => 'web']);

        return back()->with('success', __('admin.permissions.created_success'));
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return back()->with('success', __('admin.permissions.deleted_success'));
    }
}
