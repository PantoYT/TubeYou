<?php

class VideoController
{
    private VideoRepository $videoRepo;
    private LikeRepository $likeRepo;
    private SubRepository $subRepo;
    private CommentRepository $commentRepo;
    private FeedRepository $feedRepo;
    private TagRepository $tagRepo;

    private string $ffmpeg  = 'C:\\ffmpeg\\bin\\ffmpeg.exe';
    private string $ffprobe = 'C:\\ffmpeg\\bin\\ffprobe.exe';

    public function __construct(
        VideoRepository $videoRepo,
        LikeRepository $likeRepo,
        SubRepository $subRepo,
        CommentRepository $commentRepo,
        FeedRepository $feedRepo,
        TagRepository $tagRepo
    ) {
        $this->videoRepo   = $videoRepo;
        $this->likeRepo    = $likeRepo;
        $this->subRepo     = $subRepo;
        $this->commentRepo = $commentRepo;
        $this->feedRepo    = $feedRepo;
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

        if (!$video) {
            http_response_code(404);
            render('errors/404');
            return;
        }

        $tags = $this->tagRepo->getForVideo((int)$id);

        $viewKey = 'viewed_' . $id;
        if (empty($_SESSION[$viewKey])) {
            $this->videoRepo->incrementViews((int)$id);
            $_SESSION[$viewKey] = true;
        }

        $userId       = $_SESSION['user']['id'] ?? null;
        $isLiked      = false;
        $isDisliked   = false;
        $isWatchLater = false;
        $likeCount    = $this->likeRepo->countLikes((int)$id);
        $dislikeCount = $this->likeRepo->countDislikes((int)$id);
        $isSubbed     = false;
        $subCount     = $this->subRepo->countSubs($video['userId']);

        if ($userId) {
            $isLiked      = $this->likeRepo->isLiked($userId, (int)$id);
            $isDisliked   = $this->likeRepo->isDisliked($userId, (int)$id);
            $isSubbed     = $this->subRepo->isSubbed($userId, $video['userId']);
            $isWatchLater = $this->feedRepo->isInWatchLater($userId, (int)$id);
            $this->feedRepo->recordHistory($userId, (int)$id);
        }

        $commentPage    = max(1, (int)($_GET['cpage'] ?? 1));
        $commentPerPage = 10;
        $sort           = in_array($_GET['sort'] ?? 'new', ['new', 'top'])
                          ? ($_GET['sort'] ?? 'new') : 'new';

        $comments     = $this->commentRepo->getForVideoPaginated((int)$id, $commentPage, $commentPerPage, $sort);
        $commentCount = $this->commentRepo->count((int)$id);
        $commentPages = (int)ceil($commentCount / $commentPerPage);
        $suggested    = $this->videoRepo->findSuggested((int)$id, $userId ?? 0);

        render('main/watch', [
            'video'         => $video,
            'tags'          => $tags,
            'isLiked'       => $isLiked,
            'isDisliked'    => $isDisliked,
            'isWatchLater'  => $isWatchLater,
            'likeCount'     => $likeCount,
            'dislikeCount'  => $dislikeCount,
            'isSubbed'      => $isSubbed,
            'subCount'      => $subCount,
            'comments'      => $comments,
            'commentCount'  => $commentCount,
            'commentPage'   => $commentPage,
            'commentPages'  => $commentPages,
            'suggested'     => $suggested,
            'sort'          => $sort,
        ]);
    }

    public function search()
    {
        $q       = trim($_GET['q'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $videos  = $q ? $this->videoRepo->search($q) : [];
        $pages   = (int)ceil(count($videos) / $perPage);

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
        $title       = sanitizeTitle($_POST['title'] ?? '');
        $description = sanitizeText($_POST['description'] ?? '', 5000);
        $errors      = [];

        if (empty($title))       $errors[] = 'Title is required';
        if (empty($description)) $errors[] = 'Description is required';

        $videoFile     = $_FILES['video']     ?? null;
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

        $videoDir     = __DIR__ . '/../public/uploads/videos/'     . $userId . '/';
        $thumbnailDir = __DIR__ . '/../public/uploads/thumbnails/' . $userId . '/';

        if (!is_dir($videoDir))     mkdir($videoDir,     0755, true);
        if (!is_dir($thumbnailDir)) mkdir($thumbnailDir, 0755, true);

        $baseName      = uniqid();
        $videoName     = $baseName . '.mp4';
        $thumbnailName = uniqid() . '.jpg';

        move_uploaded_file($videoFile['tmp_name'],     $videoDir . $videoName);
        move_uploaded_file($thumbnailFile['tmp_name'], $thumbnailDir . $thumbnailName);

        $input = $videoDir . $videoName;

        // Duration
        $duration = 0;
        if (file_exists($this->ffprobe)) {
            $raw = shell_exec(
                escapeshellarg($this->ffprobe) .
                " -v error -show_entries format=duration" .
                " -of default=noprint_wrappers=1:nokey=1 " .
                escapeshellarg($input) . " 2>&1"
            );
            if ($raw && is_numeric(trim($raw))) {
                $duration = (int)round((float)trim($raw));
            }
        }

        // Auto thumbnail z 3. sekundy
        if (file_exists($this->ffmpeg)) {
            shell_exec(
                escapeshellarg($this->ffmpeg) .
                " -y -i " . escapeshellarg($input) .
                " -ss 00:00:03 -vframes 1 " .
                escapeshellarg($videoDir . $baseName . '_thumb.jpg') . " 2>&1"
            );
        }

        // Quality transcoding
        if (file_exists($this->ffmpeg)) {
            $qualities = [
                '1080p' => 'scale=1920:1080:force_original_aspect_ratio=decrease',
                '720p'  => 'scale=1280:720:force_original_aspect_ratio=decrease',
                '480p'  => 'scale=854:480:force_original_aspect_ratio=decrease',
                '360p'  => 'scale=640:360:force_original_aspect_ratio=decrease',
            ];

            foreach ($qualities as $label => $filter) {
                shell_exec(
                    escapeshellarg($this->ffmpeg) .
                    " -y -i " . escapeshellarg($input) .
                    " -vf {$filter} -c:v libx264 -crf 23 -preset fast -c:a aac " .
                    escapeshellarg($videoDir . $baseName . '_' . $label . '.mp4') . " 2>&1"
                );
            }
        }

        $videoSrc     = '/uploads/videos/'     . $userId . '/' . $videoName;
        $thumbnailSrc = '/uploads/thumbnails/' . $userId . '/' . $thumbnailName;
        $isShort      = isset($_POST['isShort']) ? 1 : 0;

        $this->videoRepo->save($userId, $title, $description, $videoSrc, $thumbnailSrc, $duration, $isShort);
        $videoId = (int)$this->videoRepo->lastInsertId();

        $tags = trim($_POST['tags'] ?? '');
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