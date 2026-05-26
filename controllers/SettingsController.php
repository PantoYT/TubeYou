<?php

class SettingsController
{
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function index()
    {
        requireAuth();
        $user = $this->userRepo->findById($_SESSION['user']['id']);
        render('settings/index', ['user' => $user]);
    }

    public function update()
    {
        csrfVerify();
        requireAuth();
        $userId      = $_SESSION['user']['id'];
        $displayName = trim($_POST['displayName'] ?? '');
        $bio         = trim($_POST['bio'] ?? '');

        $errors = [];
        if (empty($displayName)) $errors[] = 'Display name is required';

        $finfo = new finfo(FILEINFO_MIME_TYPE);

        if (!$errors && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file        = $_FILES['avatar'];
            $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $realMime    = $finfo->file($file['tmp_name']);

            if (!in_array($realMime, $allowedMime)) {
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

        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $file        = $_FILES['banner'];
            $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
            $realMime    = $finfo->file($file['tmp_name']);
            if (in_array($realMime, $allowedMime) && $file['size'] <= 5 * 1024 * 1024) {
                $dir = __DIR__ . '/../public/uploads/banners/' . $userId . '/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                move_uploaded_file($file['tmp_name'], $dir . 'banner.png');
                $this->userRepo->updateBanner($userId, '/uploads/banners/' . $userId . '/banner.png');
                $_SESSION['user']['banner'] = '/uploads/banners/' . $userId . '/banner.png';
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

    public function deleteAccount()
    {
        csrfVerify();
        requireAuth();

        $userId   = $_SESSION['user']['id'];
        $password = $_POST['password'] ?? '';
        $user     = $this->userRepo->findById($userId);

        if (!password_verify($password, $user['password'])) {
            render('settings/index', [
                'user'   => $user,
                'errors' => ['Incorrect password']
            ]);
            return;
        }

        $this->userRepo->delete($userId);
        session_destroy();
        header('Location: /register');
        exit;
    }
}