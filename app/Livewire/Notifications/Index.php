<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public ?string $successMessage = null;

    public function setFilter(string $filter): void
    {
        if (! in_array($filter, ['all', 'unread'], true)) {
            return;
        }

        $this->filter = $filter;
        $this->successMessage = null;
        $this->resetPage();
    }

    public function open(string $notificationId): void
    {
        $notification = auth()->user()
            ->notifications()
            ->whereKey($notificationId)
            ->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        $url = $notification->data['url'] ?? null;

        if (is_string($url) && $url !== '') {
            $this->redirect($url, navigate: true);

            return;
        }

        $this->successMessage = 'Notifikasi ditandai sudah dibaca.';
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();

        $this->successMessage = 'Semua notifikasi ditandai sudah dibaca.';
    }

    public function deleteNotification(string $notificationId): void
    {
        auth()->user()
            ->notifications()
            ->whereKey($notificationId)
            ->firstOrFail()
            ->delete();

        $this->successMessage = 'Notifikasi dihapus.';
    }

    public function render()
    {
        $query = $this->filter === 'unread'
            ? auth()->user()->unreadNotifications()
            : auth()->user()->notifications();

        return view('livewire.notifications.index', [
            'notifications' => $query->latest()->paginate(15),
            'unreadCount' => auth()->user()->unreadNotifications()->count(),
            'totalCount' => auth()->user()->notifications()->count(),
        ]);
    }
}
