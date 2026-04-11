<?php

class SubController
{
    private SubRepository $subRepo;
    private NotificationRepository $notifRepo;

    public function __construct(SubRepository $subRepo, NotificationRepository $notifRepo)
    {
        $this->subRepo = $subRepo;
        $this->notifRepo = $notifRepo;
    }

    public function toggle()
    {
        csrfVerify();
        if (!isset($_SESSION['user'])) {
            http_response_code(403);
            exit;
        }

        $subscriberId   = $_SESSION['user']['id'];
        $subscribedToId = (int)($_POST['subscribedToId'] ?? 0);

        $subbed = $this->subRepo->toggleSub($subscriberId, $subscribedToId);

        if ($subbed) {
            $this->notifRepo->create($subscribedToId, $subscriberId, 'sub');
        }

        echo json_encode([
            'subbed'   => $subbed,
            'subCount' => $this->subRepo->countSubs($subscribedToId),
        ]);
    }
}