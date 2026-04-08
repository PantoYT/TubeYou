<?php

class FeedController
{
    private FeedRepository $feedRepo;

    public function __construct(FeedRepository $feedRepo)
    {
        $this->feedRepo = $feedRepo;
    }

    private function requireAuth(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: /login');
            exit;
        }
    }

    public function subscriptions()
    {
        $this->requireAuth();
        $videos = $this->feedRepo->getSubscriptionFeed($_SESSION['user']['id']);
        render('feed/subscriptions', ['videos' => $videos]);
    }

    public function history()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear') {
            csrfVerify();
            $this->feedRepo->clearHistory($_SESSION['user']['id']);
            header('Location: /history');
            exit;
        }

        $videos = $this->feedRepo->getHistory($_SESSION['user']['id']);
        render('feed/history', ['videos' => $videos]);
    }

    public function liked()
    {
        $this->requireAuth();
        $videos = $this->feedRepo->getLiked($_SESSION['user']['id']);
        render('feed/liked', ['videos' => $videos]);
    }

    public function shorts()
    {
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $videos = $this->feedRepo->getShorts($page);
        $pages  = (int)ceil($this->feedRepo->countShorts() / 12);
        render('feed/shorts', ['videos' => $videos, 'page' => $page, 'pages' => $pages]);
    }
}