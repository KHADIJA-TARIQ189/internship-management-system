<?php
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')]);
    session_start();
}

function require_role($role) {
    if (($_SESSION['role'] ?? '') !== $role) {
        header("Location: " . BASE . "/login.php");
        exit;
    }
}

function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function current_student($conn) {
    $uid = (int)$_SESSION['user_id'];
    return $conn->query("SELECT * FROM students WHERE user_id=$uid")->fetch_assoc();
}

function current_company($conn) {
    $uid = (int)$_SESSION['user_id'];
    return $conn->query("SELECT * FROM companies WHERE user_id=$uid")->fetch_assoc();
}

function sk_head($title) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> · <?= SITE_NAME ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= BASE ?>/assets/logo.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= BASE ?>/assets/style.css" rel="stylesheet">
</head>
<?php }

function brand_link($home = true) { ?>
  <a class="sk-brand" href="<?= BASE ?>/<?= $home ? 'index.php' : '' ?>"><img src="<?= BASE ?>/assets/logo.svg" alt=""><?= SITE_NAME ?></a>
<?php }

// Split-screen layout for login / register / verify
function auth_start($title) { sk_head($title); ?>
<body>
<div class="auth">
  <aside class="auth-side">
    <?php brand_link(); ?>
    <div><h2 class="fw-bold display-6">Where students meet real opportunities.</h2>
      <p class="opacity-75 mt-3">Every account is email-verified and every company is approved by our team, so you only deal with real people.</p></div>
    <small class="opacity-50">© <?= date('Y') ?> <?= SITE_NAME ?></small>
  </aside>
  <main class="auth-main"><div class="auth-card">
<?php }

function auth_end() { ?>
  </div></main>
</div>
</body>
</html>
<?php }

function page_header($title) {
    $role = $_SESSION['role'] ?? '';
    $menus = [
        'student' => ['dashboard' => 'Dashboard', 'profile' => 'My Profile', 'internships' => 'Internships', 'my_applications' => 'My Applications'],
        'company' => ['dashboard' => 'Dashboard', 'profile' => 'Profile', 'post_internship' => 'Post Internship', 'my_internships' => 'My Internships'],
        'admin'   => ['dashboard' => 'Dashboard'],
    ];
    $links = $menus[$role] ?? [];
    sk_head($title); ?>
<body>
<nav class="sk-nav">
  <?php brand_link(); ?>
  <?php foreach ($links as $file => $label): ?>
    <a class="nav-link" href="<?= BASE ?>/<?= $role ?>/<?= $file ?>.php"><?= $label ?></a>
  <?php endforeach; ?>
  <a class="btn btn-sm btn-outline-secondary ms-auto" href="<?= BASE ?>/logout.php">Logout</a>
</nav>
<div class="container py-4">
<?php }

function page_footer() { ?>
</div>
</body>
</html>
<?php }
