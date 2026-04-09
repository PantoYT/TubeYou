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
        $limiter = new RateLimiter();
        if (!$limiter->check('login_' . $limiter->ip(), 10, 60)) {
            render('auth/login', ['error' => 'Too many attempts. Wait a minute.']);
            return;
        }

        $email    = sanitizeText($_POST['email'] ?? '', 255);
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
        $limiter = new RateLimiter();
        $limiter = new RateLimiter();
        if (!$limiter->check('register_' . $limiter->ip(), 5, 3600)) {
            render('auth/register', ['errors' => ['Too many registrations from this IP.']]);
            return;
        }

        $email    = sanitizeText($_POST['email'] ?? '', 255);
        $name     = sanitizeText($_POST['name'] ?? '', 255);
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

    public function forgotForm()
    {
        render('auth/forgot');
    }

    public function forgot()
    {
        csrfVerify();
        $email    = sanitizeText($_POST['email'] ?? '', 255);
        $user  = $this->userRepo->findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $this->userRepo->setResetToken($email, $token);
            $mailer = new MailService();
            $mailer->sendPasswordReset($email, $user['displayName'], $token);
        }

        render('auth/forgot-sent');
    }

    public function resetForm()
    {
        $token = $_GET['token'] ?? '';
        $user  = $this->userRepo->findByResetToken($token);

        if (!$user) {
            render('auth/reset', ['error' => 'Invalid or expired link', 'token' => '']);
            return;
        }

        render('auth/reset', ['token' => $token]);
    }

    public function reset()
    {
        csrfVerify();
        $token    = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';

        if (strlen($password) < 6) {
            render('auth/reset', ['token' => $token, 'error' => 'Password too short']);
            return;
        }

        $user = $this->userRepo->findByResetToken($token);
        if (!$user) {
            render('auth/reset', ['token' => $token, 'error' => 'Invalid or expired link']);
            return;
        }

        $this->userRepo->resetPassword($token, $password);
        header('Location: /login');
        exit;
    }
}