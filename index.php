<?php

require_once './database/connection.php';
require_once './models/UserRepository.php';
require_once './models/VideoRepository.php';
require_once './config/routes.php';

session_start();

$db = Database::getInstance();
$userRepo = new UserRepository($db);
$videoRepo = new VideoRepository($db);

function render($view, $data = []) {
    extract($data);
    ob_start();
    require "./views/$view.php";
    $content = ob_get_clean();
    require "./views/layout.php";
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$routes = include './config/routes.php';

if (isset($routes[$method][$uri])) {
    $route = $routes[$method][$uri];
    
    match ($route['controller']) {
        'VideoController' => match ($route['action']) {
            'homepage' => (function() use ($videoRepo) {
                $videos = $videoRepo->findAll();
                render('main/homepage', ['videos' => $videos]);
            })(),
            'watch' => (function() use ($videoRepo) {
                $id = $_GET['id'] ?? null;
                $video = $videoRepo->findById((int)$id);
                if (!$video) {
                    http_response_code(404);
                    render('errors/404');
                    exit;
                }
                render('main/watch', ['video' => $video]);
            })(),
            'search' => (function() use ($db, $videoRepo) {
                $q = $_GET['q'] ?? '';
                $stmt = $db->getConnection()->prepare("SELECT * FROM videos WHERE title LIKE ?");
                $stmt->execute(["%$q%"]);
                $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                render('main/search', ['videos' => $videos, 'query' => $q]);
            })(),
            'uploadForm' => (function() {
                if (!isset($_SESSION['user'])) {
                    header('Location: /login');
                    exit;
                }
                render('main/upload');
            })(),
            'upload' => (function() use ($videoRepo) {
                if (!isset($_SESSION['user'])) {
                    http_response_code(403);
                    exit;
                }
                
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $errors = [];

                if (empty($title)) $errors[] = 'Title is required';
                if (empty($description)) $errors[] = 'Description is required';

                if (!isset($_FILES['video']) || $_FILES['video']['error'] !== 0) {
                    $errors[] = 'Video file is required';
                }
                if (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] !== 0) {
                    $errors[] = 'Thumbnail is required';
                }

                if ($errors) {
                    render('main/upload', ['errors' => $errors]);
                    exit;
                }

                // Generuj losowy folder dla usera
                $userId = $_SESSION['user']['id'];
                $videoDir = "./public/uploads/videos/$userId";
                $thumbDir = "./public/uploads/thumbnails/$userId";

                if (!is_dir($videoDir)) mkdir($videoDir, 0777, true);
                if (!is_dir($thumbDir)) mkdir($thumbDir, 0777, true);

                // Zapisz pliki z losowymi nazwami
                $videoExt = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
                $thumbExt = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
                
                $videoName = uniqid() . '.' . $videoExt;
                $thumbName = uniqid() . '.' . $thumbExt;

                move_uploaded_file($_FILES['video']['tmp_name'], "$videoDir/$videoName");
                move_uploaded_file($_FILES['thumbnail']['tmp_name'], "$thumbDir/$thumbName");

                $videoPath = "/uploads/videos/$userId/$videoName";
                $thumbPath = "/uploads/thumbnails/$userId/$thumbName";
                $duration = (int)($_POST['duration'] ?? 0);

                $videoRepo->save($userId, $title, $description, $videoPath, $thumbPath, $duration);

                header('Location: /');
                exit;
            })(),
        },
        'AuthController' => match ($route['action']) {
            'loginForm' => render('auth/login'),
            'login' => (function() use ($userRepo) {
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $user = $userRepo->findByEmail($email);
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user'] = $user;
                    header('Location: /');
                    exit;
                }
                render('auth/login', ['error' => 'Invalid login']);
            })(),
            'registerForm' => render('auth/register'),
            'register' => (function() use ($userRepo) {
                $email = trim($_POST['email'] ?? '');
                $name = trim($_POST['name'] ?? '');
                $password = $_POST['password'] ?? '';
                $errors = [];

                // Validation
                if (empty($email)) $errors[] = 'Email is required';
                elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
                elseif ($userRepo->findByEmail($email)) $errors[] = 'Email already exists';

                if (empty($name)) $errors[] = 'Name is required';
                if (empty($password)) $errors[] = 'Password is required';
                elseif (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';

                if ($errors) {
                    render('auth/register', ['errors' => $errors]);
                } else {
                    $userRepo->save($email, $name, $password);
                    header('Location: /login?success=1');
                    exit;
                }
            })(),
            'logout' => (function() {
                session_destroy();
                header('Location: /login');
                exit;
            })(),
        }
    };
} else {
    http_response_code(404);
    render('errors/404');
}