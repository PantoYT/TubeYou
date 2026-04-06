<?php

class LikeController
{
    private LikeRepository $likeRepo;

    public function __construct(LikeRepository $likeRepo)
    {
        $this->likeRepo = $likeRepo;
    }

    public function toggle()
    {
        csrfVerify();
        if (!isset($_SESSION['user'])) {
            http_response_code(403);
            exit;
        }

        $userId  = $_SESSION['user']['id'];
        $videoId = (int)($_POST['videoId'] ?? 0);
        $type    = (int)($_POST['type'] ?? 1);

        if ($type === -1) {
            $active = $this->likeRepo->toggleDislike($userId, $videoId);
        } else {
            $active = $this->likeRepo->toggleLike($userId, $videoId);
        }

        echo json_encode(['active' => $active, 'type' => $type]);
    }
}