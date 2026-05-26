<?php

class StudioController
{
    private VideoRepository $videoRepo;
    private SubRepository   $subRepo;
    private TagRepository   $tagRepo;

    public function __construct(
        VideoRepository $videoRepo,
        SubRepository   $subRepo,
        TagRepository   $tagRepo
    ) {
        $this->videoRepo = $videoRepo;
        $this->subRepo   = $subRepo;
        $this->tagRepo   = $tagRepo;
    }

    public function index()
    {
        requireAuth();
        $userId = $_SESSION['user']['id'];
        $videos = $this->videoRepo->findByUserIdWithStats($userId);

        $totalViews    = array_sum(array_column($videos, 'views'));
        $totalLikes    = array_sum(array_column($videos, 'likeCount'));
        $totalComments = array_sum(array_column($videos, 'commentCount'));
        $subCount      = $this->subRepo->countSubs($userId);

        render('main/studio', [
            'videos'        => $videos,
            'totalViews'    => $totalViews,
            'totalLikes'    => $totalLikes,
            'totalComments' => $totalComments,
            'subCount'      => $subCount,
        ]);
    }

    public function quickEdit()
    {
        csrfVerify();
        requireAuth();

        $userId      = $_SESSION['user']['id'];
        $videoId     = (int)($_POST['videoId'] ?? 0);
        $title       = sanitizeTitle($_POST['title'] ?? '');
        $description = sanitizeText($_POST['description'] ?? '', 5000);
        $tags        = trim($_POST['tags'] ?? '');

        $video = $this->videoRepo->findById($videoId);
        if (!$video || (int)$video['userId'] !== $userId) {
            http_response_code(403);
            exit;
        }

        $this->videoRepo->update($videoId, $userId, $title, $description);
        $this->tagRepo->syncTags($videoId, $tags);

        header('Location: /studio');
        exit;
    }
}