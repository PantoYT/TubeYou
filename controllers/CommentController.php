<?php

class CommentController
{
    private CommentRepository $commentRepo;
    private NotificationRepository $notifRepo;

    public function __construct(CommentRepository $commentRepo, NotificationRepository $notifRepo)
    {
        $this->commentRepo = $commentRepo;
        $this->notifRepo = $notifRepo;
    }

    private function requireAuth(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            http_response_code(403);
            exit;
        }
    }

    public function store()
    {
        csrfVerify();
        $this->requireAuth();

        $userId   = $_SESSION['user']['id'];
        $videoId  = (int)($_POST['videoId'] ?? 0);
        $content  = sanitizeText($_POST['content'] ?? '', 5000);
        $parentId = ($_POST['parentId'] ?? null) ? (int)$_POST['parentId'] : null;
        $video = $this->commentRepo->findVideoOwner($videoId);

        if ($video && $video['userId'] !== $userId) {
            $this->notifRepo->create($video['userId'], $userId, $parentId ? 'reply' : 'comment', $videoId, null);
        }

        if ($content && $videoId) {
            $this->commentRepo->create($userId, $videoId, $content, $parentId);
        }

        header('Location: /watch?id=' . $videoId . '#comments');
        exit;
    }

    public function delete()
    {
        csrfVerify();
        $this->requireAuth();

        $userId    = $_SESSION['user']['id'];
        $commentId = (int)($_POST['commentId'] ?? 0);
        $videoId   = (int)($_POST['videoId'] ?? 0);

        $this->commentRepo->delete($commentId, $userId);

        header('Location: /watch?id=' . $videoId . '#comments');
        exit;
    }

    public function pin()
    {
        csrfVerify();
        $this->requireAuth();

        $userId    = $_SESSION['user']['id'];
        $commentId = (int)($_POST['commentId'] ?? 0);
        $videoId   = (int)($_POST['videoId'] ?? 0);

        $this->commentRepo->pin($commentId, $videoId, $userId);

        header('Location: /watch?id=' . $videoId . '#comments');
        exit;
    }

    public function like()
    {
        csrfVerify();
        $this->requireAuth();

        $userId    = $_SESSION['user']['id'];
        $commentId = (int)($_POST['commentId'] ?? 0);
        $type      = (int)($_POST['type'] ?? 1);
        $videoId   = (int)($_POST['videoId'] ?? 0);

        $this->commentRepo->toggleLike($userId, $commentId, $type);

        header('Location: /watch?id=' . $videoId . '#comments');
        exit;
    }

    public function edit()
    {
        csrfVerify();
        $this->requireAuth();

        $userId    = $_SESSION['user']['id'];
        $commentId = (int)($_POST['commentId'] ?? 0);
        $videoId   = (int)($_POST['videoId'] ?? 0);
        $content  = sanitizeText($_POST['content'] ?? '', 5000);

        if ($content) {
            $this->commentRepo->update($commentId, $userId, $content);
        }

        header('Location: /watch?id=' . $videoId . '#comment-' . $commentId);
        exit;
    }
}