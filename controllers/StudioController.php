<?php

class StudioController
{
    private VideoRepository $videoRepo;
    private LikeRepository  $likeRepo;
    private CommentRepository $commentRepo;
    private SubRepository   $subRepo;
    private TagRepository   $tagRepo;

    public function __construct(
        VideoRepository   $videoRepo,
        LikeRepository    $likeRepo,
        CommentRepository $commentRepo,
        SubRepository     $subRepo,
        TagRepository     $tagRepo
    ) {
        $this->videoRepo   = $videoRepo;
        $this->likeRepo    = $likeRepo;
        $this->commentRepo = $commentRepo;
        $this->subRepo     = $subRepo;
        $this->tagRepo     = $tagRepo;
    }

    private function requireAuth(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: /login');
            exit;
        }
    }

    public function index()
    {
        $this->requireAuth();
        $userId = $_SESSION['user']['id'];
        $videos = $this->videoRepo->findByUserId($userId);

        // Attach stats to each video
        foreach ($videos as &$v) {
            $v['likeCount']    = $this->likeRepo->countLikes((int)$v['id']);
            $v['dislikeCount'] = $this->likeRepo->countDislikes((int)$v['id']);
            $v['commentCount'] = $this->commentRepo->count((int)$v['id']);
            $v['tags']         = implode(', ', $this->tagRepo->getForVideo((int)$v['id']));
        }
        unset($v);

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
        $this->requireAuth();

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