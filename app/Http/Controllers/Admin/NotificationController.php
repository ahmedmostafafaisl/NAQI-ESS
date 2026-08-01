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
            'send_mode' => ['required', 'in:audience,tokens'],

            // send_mode = audience
            'audience' => ['required_if:send_mode,audience', 'in:all,employees,customers,specific'],
            'user_ids' => ['required_if:audience,specific', 'array'],

            // send_mode = tokens
            'tokens' => ['required_if:send_mode,tokens', 'string'],

            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $result = $data['send_mode'] === 'tokens'
            ? $this->sendToTokens($data)
            : $this->sendToAudience($data);

        $message = __('admin.notifications.sent_success')
            . " (📲 {$result['success']} delivered, {$result['failure']} failed, {$result['skipped']} skipped)";

        return redirect()->route('admin.notifications.index')->with('success', $message);
    }

    /** send_mode = audience: resolves a group of Users and notifies them (in-app + push). */
    protected function sendToAudience(array $data): array
    {
        $query = match ($data['audience']) {
            'employees' => User::where('type', 'employee'),
            'customers' => User::where('type', 'customer'),
            'specific' => User::whereIn('id', $data['user_ids'] ?? []),
            default => User::query(),
        };

        $result = $this->notifications->notifyUsers(
            users: $query->active()->get(),
            title: $data['title'],
            body: $data['body'],
            category: $data['category'] ?? 'system',
            sender: auth()->user(),
        );

        return [
            'success' => $result['success'],
            'failure' => $result['failure'],
            'skipped' => count($result['skipped_users_without_token']),
        ];
    }

    /** send_mode = tokens: push directly to raw device tokens, no User relationship, no in-app record. */
    protected function sendToTokens(array $data): array
    {
        // Accept tokens separated by newlines and/or commas, trim blanks.
        $tokens = collect(preg_split('/[\r\n,]+/', $data['tokens']))
            ->map(fn($t) => trim($t))
            ->filter()
            ->values()
            ->all();

        $result = $this->notifications->notifyTokens(
            tokens: $tokens,
            title: $data['title'],
            body: $data['body'],
            data: ['category' => $data['category'] ?? 'system'],
        );

        return [
            'success' => $result['success'],
            'failure' => $result['failure'],
            'skipped' => 0,
        ];
    }

    public function markAsRead(string $id): RedirectResponse
    {
        auth()->user()->notifications()->findOrFail($id)->markAsRead();

        return back();
    }
}
