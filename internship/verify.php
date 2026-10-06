<?php
// Only add this file if C:\xampp3\htdocs\internship\verify.php does NOT already exist.
require 'includes/auth.php';
require 'config/db.php';

$ok = false;
$token = $_GET['token'] ?? '';

if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $hash = hash('sha256', $token);
    $s = $conn->prepare("SELECT id FROM users WHERE verify_hash=? AND verify_expires > NOW() LIMIT 1");
    $s->bind_param("s", $hash);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    if ($row) {
        $u = $conn->prepare("UPDATE users SET email_verified=1, verify_hash=NULL, verify_expires=NULL WHERE id=?");
        $u->bind_param("i", $row['id']);
        $u->execute();
        $ok = true;
    }
}
auth_start('Verify email'); ?>
<div class="text-center">
<?php if ($ok): ?>
  <h3 class="fw-bold">Email verified ✅</h3>
  <p class="text-secondary">Your account is active. You can log in now.</p>
  <a class="btn btn-primary" href="login.php">Go to login</a>
<?php else: ?>
  <h3 class="fw-bold">Link invalid or expired</h3>
  <p class="text-secondary">Try logging in again and we'll send you a new verification link.</p>
  <a class="btn btn-primary" href="login.php">Go to login</a>
<?php endif; ?>
</div>
<?php auth_end();
