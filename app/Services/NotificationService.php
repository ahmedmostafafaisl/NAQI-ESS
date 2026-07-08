<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class NotificationService
{
    /** Notify a single user (employee or customer). */
    public function notifyUser(User $user, string $title, string $body, string $category = 'system', array $data = [], ?User $sender = null): void
    {
        $user->notify(new GeneralNotification($title, $body, $category, $data, $sender?->id));
    }

    /** Notify a list/collection of users at once. */
    public function notifyUsers(iterable $users, string $title, string $body, string $category = 'system', array $data = [], ?User $sender = null): void
    {
        NotificationFacade::send($users, new GeneralNotification($title, $body, $category, $data, $sender?->id));
    }

    /** Notify everyone holding the admin/super-admin role(s). */
    public function notifyAdmins(string $title, string $body, string $category = 'system', array $data = [], ?User $sender = null): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'super-admin']))
            ->active()
            ->get();

        $this->notifyUsers($admins, $title, $body, $category, $data, $sender);
    }

    /** Common cycle: employee action -> notify admins, admin decision -> notify employee. */
    public function leaveRequestSubmitted(User $employee, array $leave): void
    {
        $this->notifyAdmins(
            title: 'New leave request',
            body: "{$employee->username} submitted a leave request.",
            category: 'leave',
            data: $leave,
            sender: $employee,
        );
    }

    public function leaveRequestDecided(User $employee, array $leave, bool $approved, ?User $decidedBy = null): void
    {
        $this->notifyUser(
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

    public function payslipPublished(User $employee, array $payslip): void
    {
        $this->notifyUser(
            user: $employee,
            title: 'New payslip available',
            body: 'Your payslip for '.($payslip['period'] ?? 'this period').' is now available.',
            category: 'payslip',
            data: $payslip,
        );
    }
}
