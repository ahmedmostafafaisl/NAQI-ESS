<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function index(Request $request): View
    {
        $items = auth()->user()->notifications()->latest()->paginate(20);

        return view('notifications.index', compact('items'));
    }

    public function create(): View
    {
        $users = User::active()->orderBy('username')->get();

        return view('notifications.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'audience' => ['required', 'in:all,employees,customers,specific'],
            'user_ids' => ['required_if:audience,specific', 'array'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $query = match ($data['audience']) {
            'employees' => User::where('type', 'employee'),
            'customers' => User::where('type', 'customer'),
            'specific' => User::whereIn('id', $data['user_ids'] ?? []),
            default => User::query(),
        };

        $this->notifications->notifyUsers(
            users: $query->active()->get(),
            title: $data['title'],
            body: $data['body'],
            category: $data['category'] ?? 'system',
            sender: auth()->user(),
        );

        return redirect()->route('admin.notifications.index')->with('success', __('admin.notifications.sent_success'));
    }

    public function markAsRead(string $id): RedirectResponse
    {
        auth()->user()->notifications()->findOrFail($id)->markAsRead();

        return back();
    }
}
