<?php

class VideoController
{
    private VideoRepository $videoRepo;
    private LikeRepository $likeRepo;
    private SubRepository $subRepo;
    private CommentRepository $commentRepo;
    private FeedRepository $feedRepo;
    private TagRepository $tagRepo;

    public function __construct(
    VideoRepository $videoRepo, 
    LikeRepository $likeRepo, 
    SubRepository $subRepo, 
    CommentRepository $commentRepo, 
    FeedRepository $feedRepo,
    TagRepository $tagRepo
    ) {
        $this->videoRepo = $videoRepo;
        $this->likeRepo = $likeRepo;
        $this->subRepo = $subRepo;
        $this->commentRepo = $commentRepo;
        $this->feedRepo = $feedRepo;
        $this->tagRepo     = $tagRepo;
    }

    public function homepage()
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $videos  = $this->videoRepo->findAllPaginated($page, $perPage);
        $total   = $this->videoRepo->countAll();
        $pages   = (int)ceil($total / $perPage);

        render('main/homepage', [
            'videos' => $videos,
            'page'   => $page,
            'pages'  => $pages,
        ]);
    }

    public function watch()
    {
        $id    = $_GET['id'] ?? null;
        $video = $this->videoRepo->findById((int)$id);
        $tags  = $this->tagRepo->getForVideo((int)$id);

        if (!$video) {
            http_response_code(404);
            render('errors/404');
            return;
        }

        $viewKey = 'viewed_' . $id;
        if (empty($_SESSION[$viewKey])) {
            $this->videoRepo->incrementViews((int)$id);
            $_SESSION[$viewKey] = true;
        }

        $userId       = $_SESSION['user']['id'] ?? null;
        $isLiked      = false;
        $isDisliked   = false;
        $likeCount    = $this->likeRepo->countLikes((int)$id);
        $dislikeCount = $this->likeRepo->countDislikes((int)$id);
        $isSubbed     = false;
        $subCount     = $this->subRepo->countSubs($video['userId']);

        if ($userId) {
            $isLiked    = $this->likeRepo->isLiked($userId, (int)$id);
            $isDisliked = $this->likeRepo->isDisliked($userId, (int)$id);
            $isSubbed   = $this->subRepo->isSubbed($userId, $video['userId']);
            $this->feedRepo->recordHistory($userId, (int)$id);
        }

        $commentPage  = max(1, (int)($_GET['cpage'] ?? 1));
        $commentPerPage = 10;
        $comments     = $this->commentRepo->getForVideoPaginated((int)$id, $commentPage, $commentPerPage);
        $commentCount = $this->commentRepo->count((int)$id);
        $commentPages = (int)ceil($commentCount / $commentPerPage);
        $suggested    = $this->videoRepo->findSuggested((int)$id, $userId ?? 0);

        render('main/watch', [
            'video'         => $video,
            'isLiked'       => $isLiked,
            'isDisliked'    => $isDisliked,
            'likeCount'     => $likeCount,
            'dislikeCount'  => $dislikeCount,
            'isSubbed'      => $isSubbed,
            'subCount'      => $subCount,
            'comments'      => $comments,
            'commentCount'  => $commentCount,
            'commentPage'   => $commentPage,
            'commentPages'  => $commentPages,
            'suggested'     => $suggested,
            'tags'          => $tags,
        ]);
    }

    public function search()
    {
        $q = trim($_GET['q'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $videos = $q ? $this->videoRepo->search($q) : [];
        $pages  = (int)ceil(count($videos) / $perPage);

        render('main/search', [
            'videos' => $videos,
            'query'  => $q,
            'page'   => $page,
            'pages'  => $pages,
        ]);
    }
    
    public function uploadForm()
    {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: /login');
            exit;
        }
        render('main/upload');
    }

    public function upload()
    {
        csrfVerify();

        if (!isset($_SESSION['user']['id'])) {
            header('Location: /login');
            exit;
        }

        $userId      = $_SESSION['user']['id'];
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $errors = [];

        if (empty($title))       $errors[] = 'Title is required';
        if (empty($description)) $errors[] = 'Description is required';

        $videoFile     = $_FILES['video'] ?? null;
        $thumbnailFile = $_FILES['thumbnail'] ?? null;

        if (!$videoFile || $videoFile['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Video file is required';
        }
        if (!$thumbnailFile || $thumbnailFile['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Thumbnail is required';
        }

        if ($errors) {
            render('main/upload', ['errors' => $errors]);
            return;
        }

        $videoDir     = __DIR__ . '/../public/uploads/videos/' . $userId . '/';
        $thumbnailDir = __DIR__ . '/../public/uploads/thumbnails/' . $userId . '/';

        if (!is_dir($videoDir))     mkdir($videoDir, 0755, true);
        if (!is_dir($thumbnailDir)) mkdir($thumbnailDir, 0755, true);

        $videoName     = uniqid() . '.mp4';
        $thumbnailName = uniqid() . '.jpg';

        move_uploaded_file($videoFile['tmp_name'],     $videoDir . $videoName);
        move_uploaded_file($thumbnailFile['tmp_name'], $thumbnailDir . $thumbnailName);

        $input  = $videoDir . $videoName;
        $output = $videoDir . 'thumb_auto.jpg';

        shell_exec(
            "ffmpeg -i " . escapeshellarg($input) .
            " -ss 00:00:03 -vframes 1 " .
            escapeshellarg($output) . " 2>&1"
        );

        $qualities = [
            '1080p' => '1920x1080',
            '720p'  => '1280x720',
            '480p'  => '854x480',
            '360p'  => '640x360',
        ];

        foreach ($qualities as $label => $size) {
            shell_exec(
                "ffmpeg -i " . escapeshellarg($input) .
                " -vf scale={$size} -c:v libx264 -crf 23 " .
                escapeshellarg($videoDir . $label . '.mp4') . " 2>&1"
            );
        }

        $videoSrc     = '/uploads/videos/'     . $userId . '/' . $videoName;
        $thumbnailSrc = '/uploads/thumbnails/' . $userId . '/' . $thumbnailName;
        $isShort = isset($_POST['isShort']) ? 1 : 0;

        $this->videoRepo->save($userId, $title, $description, $videoSrc, $thumbnailSrc, 0, $isShort);
        $videoId = (int)$this->videoRepo->lastInsertId();
        $tags    = trim($_POST['tags'] ?? '');
        if ($tags) {
            $this->tagRepo->syncTags($videoId, $tags);
        }

        header('Location: /');
        exit;
    }

    public function tag()
    {
        $name   = trim($_GET['name'] ?? '');
        $videos = $name ? $this->tagRepo->getVideosByTag($name) : [];
        render('main/tag', ['videos' => $videos, 'tag' => $name]);
    }
}