<?php

namespace OfficeHub\Controllers;

use OfficeHub\Core\Controller;
use OfficeHub\Models\Notification;

class NotificationController extends Controller
{
    public function poll(array $params = []): void
    {
        $this->requireAuth();

        $user = $this->currentUser();
        $userId = (int)($user['id'] ?? 0);
        $enabled = (int)($user['notifications_enabled'] ?? 1) === 1;

        if ($userId <= 0) {
            $this->json([
                'ok' => true,
                'enabled' => false,
                'count' => 0,
                'items' => [],
            ]);
        }

        Notification::syncCommitNotifications();

        if (!$enabled) {
            $this->json([
                'ok' => true,
                'enabled' => false,
                'count' => 0,
                'items' => [],
            ]);
        }

        $this->json([
            'ok' => true,
            'enabled' => true,
            'count' => Notification::unreadCount($userId),
            'items' => array_map([$this, 'present'], Notification::unreadFor($userId)),
        ]);
    }

    public function markRead(array $params = []): void
    {
        $this->requireAuth();

        Notification::markRead((int)($params['id'] ?? 0), (int)($this->currentUser()['id'] ?? 0));

        $this->json(['ok' => true]);
    }

    public function markAllRead(array $params = []): void
    {
        $this->requireAuth();

        Notification::markAllRead((int)($this->currentUser()['id'] ?? 0));

        $this->json(['ok' => true]);
    }

    private function present(array $notification): array
    {
        return [
            'id' => (int)$notification['id'],
            'type' => (string)$notification['type'],
            'title' => (string)$notification['title'],
            'body' => (string)($notification['body'] ?? ''),
            'link' => (string)($notification['link'] ?? '#'),
            'actor' => $notification['actor_name'] ?? null,
            'created_at' => date('d/m/Y H:i', strtotime((string)$notification['created_at'])),
        ];
    }
}
