<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    public function roles(array $roles, string $title, string $message, string $kind = 'info', ?string $url = null): void
    {
        $users = User::where('status', 'active')->whereHas('roles', fn ($q) => $q->whereIn('name', $roles))->get();
        Notification::send($users, new SystemNotification($title, $message, $kind, $url));
    }

    public function user(User $user, string $title, string $message, string $kind = 'info', ?string $url = null): void
    {
        $user->notify(new SystemNotification($title,$message,$kind,$url));
    }
}
