<?php

class PlaylistController
{
    private PlaylistRepository $playlistRepo;

    public function __construct(PlaylistRepository $playlistRepo)
    {
        $this->playlistRepo = $playlistRepo;
    }

    public function index()
    {
        requireAuth();
        $playlists = $this->playlistRepo->getForUser($_SESSION['user']['id']);
        render('main/playlists', ['playlists' => $playlists]);
    }

    public function show()
    {
        $id       = (int)($_GET['id'] ?? 0);
        $playlist = $this->playlistRepo->getById($id);

        if (!$playlist) {
            http_response_code(404);
            render('errors/404');
            return;
        }

        if (!$playlist['isPublic'] &&
            (!isset($_SESSION['user']['id']) || (int)$_SESSION['user']['id'] !== (int)$playlist['userId'])) {
            http_response_code(403);
            render('errors/404');
            return;
        }

        $videos = $this->playlistRepo->getVideos($id);
        render('main/playlist_show', ['playlist' => $playlist, 'videos' => $videos]);
    }

    public function create()
    {
        csrfVerify();
        requireAuth();

        $title       = sanitizeTitle($_POST['title'] ?? '');
        $description = sanitizeText($_POST['description'] ?? '', 1000);
        $isPublic    = isset($_POST['isPublic']) ? 1 : 0;

        if (!$title) {
            header('Location: /playlists');
            exit;
        }

        $this->playlistRepo->create($_SESSION['user']['id'], $title, $description, $isPublic);
        header('Location: /playlists');
        exit;
    }

    public function addVideo()
    {
        csrfVerify();
        requireAuth();

        $playlistId = (int)($_POST['playlistId'] ?? 0);
        $videoId    = (int)($_POST['videoId']    ?? 0);

        if ($this->playlistRepo->isOwner($playlistId, $_SESSION['user']['id'])) {
            $this->playlistRepo->addVideo($playlistId, $videoId);
        }

        echo json_encode(['ok' => true]);
    }

    public function removeVideo()
    {
        csrfVerify();
        requireAuth();

        $playlistId = (int)($_POST['playlistId'] ?? 0);
        $videoId    = (int)($_POST['videoId']    ?? 0);
        $redirectId = (int)($_POST['videoId']    ?? 0);

        if ($this->playlistRepo->isOwner($playlistId, $_SESSION['user']['id'])) {
            $this->playlistRepo->removeVideo($playlistId, $videoId);
        }

        header('Location: /playlist?id=' . $playlistId);
        exit;
    }

    public function delete()
    {
        csrfVerify();
        requireAuth();

        $playlistId = (int)($_POST['playlistId'] ?? 0);
        $this->playlistRepo->delete($playlistId, $_SESSION['user']['id']);
        header('Location: /playlists');
        exit;
    }
}