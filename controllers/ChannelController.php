<?php
class ChannelController
{
    private UserRepository $userRepo;
    private VideoRepository $videoRepo;
    private SubRepository $subRepo;

    public function __construct(UserRepository $userRepo, VideoRepository $videoRepo, SubRepository $subRepo)
    {
        $this->userRepo  = $userRepo;
        $this->videoRepo = $videoRepo;
        $this->subRepo   = $subRepo;
    }

    public function index()
    {
        $id   = (int)($_GET['id'] ?? 0);
        $user = $this->userRepo->findById($id);

        if (!$user) {
            http_response_code(404);
            render('errors/404');
            return;
        }

        $videos   = $this->videoRepo->findByUserId($id);
        $subCount = $this->subRepo->countSubs($id);
        $isSubbed = false;

        if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] !== $id) {
            $isSubbed = $this->subRepo->isSubbed($_SESSION['user']['id'], $id);
        }

        render('main/channel', [
            'channel'  => $user,
            'videos'   => $videos,
            'subCount' => $subCount,
            'isSubbed' => $isSubbed,
        ]);
    }
}