<?php
require 'includes/auth.php';
require 'config/db.php';
require 'includes/mail.php';
$error = ""; $info = ""; $unverified = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $stmt = $conn->prepare("SELECT id, password, role, email_verified, verify_sent_at FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
        if ($user['role'] !== 'admin' && !$user['email_verified']) {
            $unverified = true;
            $recent = $user['verify_sent_at'] && (time() - strtotime($user['verify_sent_at'])) < 60;
            if ($recent) {
                $error = "Please verify your email first. A link was just sent. Wait a minute before requesting another.";
            } elseif (issue_verification($conn, (int)$user['id'], $email)) {
                $info = "Your email isn't verified yet. We've sent a new verification link to $email.";
            } else {
                $error = "Your email isn't verified and we couldn't send the link. Please try again later.";
            }
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            header("Location: " . BASE . "/{$user['role']}/dashboard.php");
            exit;
        }
    } else {
        $error = "Invalid email or password.";
    }
}
auth_start('Log in'); ?>
<h3 class="fw-bold mb-1">Welcome back</h3>
<p class="text-secondary mb-4">Log in to your <?= SITE_NAME ?> account.</p>
<?php if ($error) echo "<div class='alert alert-danger'>" . e($error) . "</div>"; ?>
<?php if ($info)  echo "<div class='alert alert-info'>" . e($info) . "</div>"; ?>
<form method="post">
  <input class="form-control mb-2" type="email" name="email" placeholder="Email" value="<?= e($_POST['email'] ?? '') ?>" required>
  <input class="form-control mb-3" type="password" name="password" placeholder="Password" required>
  <button class="btn btn-primary w-100 py-2">Log in</button>
</form>
<p class="mt-3 text-center">New here? <a href="register.php">Create an account</a></p>
<?php auth_end();
