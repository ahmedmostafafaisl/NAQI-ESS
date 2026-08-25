<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\StoreAdminNotificationRequest;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function index(): View
    {
        $items = $this->notifications->feedFor(auth()->user(), null, 20, (int) request('page', 1), 'page');

        return view('notifications.index', compact('items'));
    }

    public function create(): View
    {
        $users = $this->notifications->resolveAudience('all');

        return view('notifications.create', compact('users'));
    }

    public function store(StoreAdminNotificationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $result = $data['send_mode'] === 'tokens'
            ? $this->sendToTokens($request, $data)
            : $this->sendToAudience($data);

        $message = __('admin.notifications.sent_success')
            . " (📲 {$result['success']} delivered, {$result['failure']} failed, {$result['skipped']} skipped)";

        return redirect()->route('admin.notifications.index')->with('success', $message);
    }

    protected function sendToAudience(array $data): array
    {
        $users = $this->notifications->resolveAudience($data['audience'], $data['user_ids'] ?? []);

        $result = $this->notifications->notifyUsers(
            users: $users,
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

    protected function sendToTokens(StoreAdminNotificationRequest $request, array $data): array
    {
        $result = $this->notifications->notifyTokens(
            tokens: $request->parsedTokens(),
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
        $this->notifications->markAsReadFor(auth()->user(), $id);

        return back();
    }
}
