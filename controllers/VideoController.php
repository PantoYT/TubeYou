<?php

class VideoController
{
    private VideoRepository $videoRepo;
    private LikeRepository $likeRepo;
    private SubRepository $subRepo;

    public function __construct(VideoRepository $videoRepo, LikeRepository $likeRepo, SubRepository $subRepo) {
        $this->videoRepo = $videoRepo;
        $this->likeRepo = $likeRepo;
        $this->subRepo = $subRepo;
    }

    public function homepage()
    {
        $videos = $this->videoRepo->findAll();
        render('main/homepage', ['videos' => $videos]);
    }

    public function watch()
    {
        $id = $_GET['id'] ?? null;
        $video = $this->videoRepo->findById((int)$id);

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

        $userId = $_SESSION['user']['id'] ?? null;

        $isLiked    = false;
        $isDisliked = false;
        $likeCount    = $this->likeRepo->countLikes((int)$id);
        $dislikeCount = $this->likeRepo->countDislikes((int)$id);

        $isSubbed = false;
        $subCount = $this->subRepo->countSubs($video['userId']);

        if ($userId) {
            $isLiked    = $this->likeRepo->isLiked($userId, (int)$id);
            $isDisliked = $this->likeRepo->isDisliked($userId, (int)$id);
            $isSubbed   = $this->subRepo->isSubbed($userId, $video['userId']);
        }

        render('main/watch', [
            'video'        => $video,
            'isLiked'      => $isLiked,
            'isDisliked'   => $isDisliked,
            'likeCount'    => $likeCount,
            'dislikeCount' => $dislikeCount,
            'isSubbed'     => $isSubbed,
            'subCount'     => $subCount,
        ]);
    }

    public function search()
    {
        $q = trim($_GET['q'] ?? '');
        $videos = $q ? $this->videoRepo->search($q) : [];

        render('main/search', [
            'videos' => $videos,
            'query'  => $q,
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

        $videoSrc     = '/uploads/videos/'     . $userId . '/' . $videoName;
        $thumbnailSrc = '/uploads/thumbnails/' . $userId . '/' . $thumbnailName;

        $this->videoRepo->save($userId, $title, $description, $videoSrc, $thumbnailSrc);

        header('Location: /');
        exit;
    }
}