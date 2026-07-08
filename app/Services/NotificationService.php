<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class NotificationService
{
    public function __construct(protected FcmService $fcm) {}

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
        $users = $users instanceof \Illuminate\Support\Collection ? $users : collect($users);

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
