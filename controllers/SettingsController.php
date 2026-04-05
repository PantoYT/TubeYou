<?php

class SettingsController
{
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
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
        $user = $this->userRepo->findById($_SESSION['user']['id']);
        render('settings/index', ['user' => $user]);
    }

    public function update()
    {
        csrfVerify();
        $this->requireAuth();
        $userId      = $_SESSION['user']['id'];
        $displayName = trim($_POST['displayName'] ?? '');
        $bio         = trim($_POST['bio'] ?? '');

        $errors = [];
        if (empty($displayName)) $errors[] = 'Display name is required';

        if (!$errors && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file    = $_FILES['avatar'];
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

            if (!in_array($file['type'], $allowed)) {
                $errors[] = 'Invalid file type';
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $errors[] = 'Avatar max 2MB';
            } else {
                $dir = __DIR__ . '/../public/uploads/avatars/' . $userId . '/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                move_uploaded_file($file['tmp_name'], $dir . 'avatar.png');
                $this->userRepo->updateAvatar($userId, '/uploads/avatars/' . $userId . '/avatar.png');
                $_SESSION['user']['avatar'] = '/uploads/avatars/' . $userId . '/avatar.png';
            }
        }

        if ($errors) {
            $user = $this->userRepo->findById($userId);
            render('settings/index', ['user' => $user, 'errors' => $errors]);
            return;
        }

        $this->userRepo->updateProfile($userId, $displayName, $bio);
        $_SESSION['user']['displayName'] = $displayName;

        header('Location: /settings?saved=1');
        exit;
    }
}