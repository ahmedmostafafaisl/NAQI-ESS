<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\IndexUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(protected UserService $users)
    {
        $this->middleware('permission:users.view')->only('index', 'show');
        $this->middleware('permission:users.create')->only('create', 'store');
        $this->middleware('permission:users.edit')->only('edit', 'update');
        $this->middleware('permission:users.delete')->only('destroy');
    }

    public function index(IndexUserRequest $request): View
    {
        $users = $this->users->paginate(
            search: $request->validated('search'),
            type: $request->validated('type'),
            perPage: 15,
            page: (int) $request->validated('page', 1),
            pageName: 'page',
        )->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = $this->users->assignableRoles();

        return view('users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->users->create($request->validated());

        return redirect()->route('admin.users.index')->with('success', __('admin.users.created_success'));
    }

    public function edit(User $user): View
    {
        $roles = $this->users->assignableRoles();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->users->update($user, $request->validated());

        return redirect()->route('admin.users.index')->with('success', __('admin.users.updated_success'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->users->delete($user);

        return back()->with('success', __('admin.users.deleted_success'));
    }
}
