<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(protected PermissionService $permissions)
    {
        $this->middleware('permission:permissions.manage');
    }

    public function index(): View
    {
        $permissions = $this->permissions->paginate(perPage: 20, page: (int) request('page', 1), pageName: 'page');

        return view('permissions.index', compact('permissions'));
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $this->permissions->create($request->validated('name'));

        return back()->with('success', __('admin.permissions.created_success'));
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $this->permissions->delete($permission);

        return back()->with('success', __('admin.permissions.deleted_success'));
    }
}
