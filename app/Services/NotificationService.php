<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class NotificationService
{
    public function __construct(
        protected FcmService $fcm,
        protected UserRepositoryInterface $users,
        protected NotificationRepositoryInterface $notificationFeed,
    ) {}

    /**
     * Resolve a named audience ("all"/"employees"/"customers"/"specific")
     * into the actual list of active Users it refers to. Moved here from
     * the admin controller — deciding what "employees" means is a
     * notification-sending concern, not an HTTP concern.
     */
    public function resolveAudience(string $audience, array $specificUserIds = []): Collection
    {
        return match ($audience) {
            'employees' => $this->users->activeUsersFor(type: 'employee'),
            'customers' => $this->users->activeUsersFor(type: 'customer'),
            'specific' => $this->users->activeUsersFor(ids: $specificUserIds),
            default => $this->users->activeUsersFor(),
        };
    }

    /** A notifiable's own feed, paginated, optionally filtered by category. */
    public function feedFor(Model $notifiable, ?string $category, int $perPage, int $page, string $pageName): LengthAwarePaginator
    {
        return $this->notificationFeed->paginateFor($notifiable, $category, $perPage, $page, $pageName);
    }

    public function unreadCountFor(Model $notifiable): int
    {
        return $this->notificationFeed->unreadCountFor($notifiable);
    }

    public function markAsReadFor(Model $notifiable, string $id): void
    {
        $this->notificationFeed->markAsRead($this->notificationFeed->findForOrFail($notifiable, $id));
    }

    public function markAllAsReadFor(Model $notifiable): void
    {
        $this->notificationFeed->markAllAsReadFor($notifiable);
    }

    public function deleteFor(Model $notifiable, string $id): void
    {
        $this->notificationFeed->delete($this->notificationFeed->findForOrFail($notifiable, $id));
    }

    /**
     * Notify a single user: writes the in-app record and pushes via FCM if they have a token.
     *
     * @return array{success:int, failure:int, invalid_tokens:string[], skipped_users_without_token:int[]}
     */
    public function notifyUser(User $user, string $title, string $body, string $category = 'system', array $data = [], ?User $sender = null): array
    {
        return $this->notifyUsers([$user], $title, $body, $category, $data, $sender);
    }

    /**
     * Notify a list/collection of users: writes in-app records for all of them,
     * then pushes via FCM to whichever ones have a registered device token.
     *
     * @param  iterable<User>  $users
     * @return array{success:int, failure:int, invalid_tokens:string[], skipped_users_without_token:int[]}
     */
    public function notifyUsers(iterable $users, string $title, string $body, string $category = 'system', array $data = [], ?User $sender = null): array
    {
        $users = $users instanceof Collection ? $users : collect($users);

        if ($users->isEmpty()) {
            return ['success' => 0, 'failure' => 0, 'invalid_tokens' => [], 'skipped_users_without_token' => []];
        }

        NotificationFacade::send($users, new GeneralNotification($title, $body, $category, $data, $sender?->id));

        return $this->fcm->sendToUsers($users, $title, $body, array_merge($data, ['category' => $category]));
    }

    /** Notify everyone holding the admin/super-admin role(s). */
    public function notifyAdmins(string $title, string $body, string $category = 'system', array $data = [], ?User $sender = null): array
    {
        $admins = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'super-admin']))
            ->active()
            ->get();

        return $this->notifyUsers($admins, $title, $body, $category, $data, $sender);
    }

    /**
     * Scenario 2: push directly to a raw list of FCM device tokens, bypassing the
     * User/notifiable model entirely (no in-app record is created since there may
     * be no corresponding user — e.g. tokens supplied by an external system).
     *
     * @param  string[]  $tokens
     * @return array{success:int, failure:int, invalid_tokens:string[]}
     */
    public function notifyTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        return $this->fcm->sendToTokens($tokens, $title, $body, $data);
    }

    /** Common cycle: employee action -> notify admins, admin decision -> notify employee. */
    public function leaveRequestSubmitted(User $employee, array $leave): array
    {
        return $this->notifyAdmins(
            title: 'New leave request',
            body: "{$employee->username} submitted a leave request.",
            category: 'leave',
            data: $leave,
            sender: $employee,
        );
    }

    public function leaveRequestDecided(User $employee, array $leave, bool $approved, ?User $decidedBy = null): array
    {
        return $this->notifyUser(
            user: $employee,
            title: $approved ? 'Leave request approved' : 'Leave request rejected',
            body: $approved
                ? 'Your leave request has been approved.'
                : 'Your leave request has been rejected.',
            category: 'leave',
            data: $leave,
            sender: $decidedBy,
        );
    }

    public function payslipPublished(User $employee, array $payslip): array
    {
        return $this->notifyUser(
            user: $employee,
            title: 'New payslip available',
            body: 'Your payslip for ' . ($payslip['period'] ?? 'this period') . ' is now available.',
            category: 'payslip',
            data: $payslip,
        );
    }
}
