<?php

class AuthController
{
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function loginForm()
    {
        render('auth/login');
    }

    public function login()
    {
        csrfVerify();

        $email    = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userRepo->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            render('auth/login', ['error' => 'Invalid email or password']);
            return;
        }

        if (!$user['emailVerified']) {
            render('auth/login', ['error' => 'Please verify your email before logging in']);
            return;
        }

        $_SESSION['user'] = $user;
        header('Location: /');
        exit;
    }

    public function registerForm()
    {
        render('auth/register');
    }

    public function register()
    {
        csrfVerify();

        $email    = trim($_POST['email'] ?? '');
        $name     = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];

        if (empty($email))                                    $errors[] = 'Email is required';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))   $errors[] = 'Invalid email';
        elseif ($this->userRepo->findByEmail($email))         $errors[] = 'Email already exists';

        if (empty($name))                   $errors[] = 'Name is required';
        if (empty($password))               $errors[] = 'Password is required';
        elseif (strlen($password) < 6)      $errors[] = 'Password too short (min 6 chars)';

        if ($errors) {
            render('auth/register', ['errors' => $errors]);
            return;
        }

        $token = bin2hex(random_bytes(32));

        try {
            $this->userRepo->saveUnverified($email, $name, $password, $token);
            $mailer = new MailService();
            $mailer->sendVerification($email, $name, $token);
            render('auth/register-success', ['email' => $email]);
        } catch (Exception $e) {
            render('auth/register', ['errors' => ['Mail error: ' . $e->getMessage()]]);
        }
    }

    public function verify()
    {
        $token = $_GET['token'] ?? '';

        if (!$token) {
            render('auth/verify', ['success' => false, 'message' => 'Invalid token']);
            return;
        }

        $success = $this->userRepo->verifyEmail($token);
        render('auth/verify', [
            'success' => $success,
            'message' => $success ? 'Email verified! You can now log in.' : 'Invalid or expired token'
        ]);
    }

    public function logout()
    {
        session_destroy();
        header('Location: /login');
        exit;
    }
}