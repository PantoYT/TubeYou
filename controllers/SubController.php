<?php

class SubController
{
    private SubRepository $subRepo;

    public function __construct(SubRepository $subRepo)
    {
        $this->subRepo = $subRepo;
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

        echo json_encode(['subbed' => $subbed]);
    }
}