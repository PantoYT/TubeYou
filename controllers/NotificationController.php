<?php

class NotificationController
{
    private NotificationRepository $notifRepo;

    public function __construct(NotificationRepository $notifRepo)
    {
        $this->notifRepo = $notifRepo;
    }

    public function index()
    {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: /login');
            exit;
        }
        $userId        = $_SESSION['user']['id'];
        $notifications = $this->notifRepo->getForUser($userId);
        $this->notifRepo->markAllRead($userId);
        render('main/notifications', ['notifications' => $notifications]);
    }

    public function markRead()
    {
        csrfVerify();
        if (!isset($_SESSION['user']['id'])) {
            http_response_code(403);
            exit;
        }
        $this->notifRepo->markAllRead($_SESSION['user']['id']);
        echo json_encode(['ok' => true]);
    }
}