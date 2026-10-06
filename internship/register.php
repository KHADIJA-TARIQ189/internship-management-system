<?php
require 'includes/auth.php';
require 'config/db.php';
require 'includes/mail.php';
$msg = ""; $sent = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $name  = trim($_POST['name'] ?? '');
    $role  = ($_POST['role'] ?? '') === 'company' ? 'company' : 'student';
    $pass  = $_POST['password'] ?? '';
    $domain = substr(strrchr($email, '@') ?: '', 1);

    $msg = check_email($email);
    if (!$msg && $role === 'student' && ALLOWED_STUDENT_DOMAINS && !in_array($domain, ALLOWED_STUDENT_DOMAINS, true))
        $msg = "Students must register with their university email (" . implode(', ', array_map(fn($d) => "@$d", ALLOWED_STUDENT_DOMAINS)) . ").";
    if (!$msg && $name === '') $msg = "Please enter your name.";
    if (!$msg && strlen($pass) < 8) $msg = "Password must be at least 8 characters.";
    if (!$msg) {
        $chk = $conn->prepare("SELECT id FROM users WHERE email=?");
        $chk->bind_param("s", $email);
        $chk->execute();
        if ($chk->get_result()->num_rows) $msg = "Email already registered. Try logging in.";
    }
    if (!$msg) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (email, password, role, email_verified) VALUES (?,?,?,0)");
        $stmt->bind_param("sss", $email, $hash, $role);
        $stmt->execute();
        $uid = $conn->insert_id;

        $s = $conn->prepare($role === 'student' ? "INSERT INTO students (user_id, full_name) VALUES (?,?)"
                                                : "INSERT INTO companies (user_id, company_name) VALUES (?,?)");
        $s->bind_param("is", $uid, $name);
        $s->execute();

        if (issue_verification($conn, $uid, $email)) {
            $sent = $email;
        } else {
            $conn->query("DELETE FROM users WHERE id=" . (int)$uid);
            $msg = "We couldn't send the verification email. Please check the address and try again.";
        }
    }
}
auth_start('Create account'); ?>
<?php if ($sent): ?>
  <div class="text-center">
    <div class="ico mx-auto">✉️</div>
    <h3 class="fw-bold">Check your inbox</h3>
    <p class="text-secondary">We sent a verification link to <b><?= e($sent) ?></b>. Click it to activate your account (valid for 24 hours).</p>
    <a class="btn btn-primary" href="login.php">Go to login</a>
  </div>
<?php else: ?>
  <h3 class="fw-bold mb-1">Create your account</h3>
  <p class="text-secondary mb-4">Join <?= SITE_NAME ?> in less than a minute.</p>
  <?php if ($msg) echo "<div class='alert alert-danger'>" . e($msg) . "</div>"; ?>
  <form method="post">
    <select name="role" class="form-select mb-2">
      <option value="student" <?= ($_POST['role'] ?? '') === 'student' ? 'selected' : '' ?>>I'm a student</option>
      <option value="company" <?= ($_POST['role'] ?? '') === 'company' ? 'selected' : '' ?>>I'm a company</option>
    </select>
    <input class="form-control mb-2" name="name" placeholder="Full name / Company name" value="<?= e($_POST['name'] ?? '') ?>" required>
    <input class="form-control mb-2" type="email" name="email" placeholder="Email address" value="<?= e($_POST['email'] ?? '') ?>" required>
    <input class="form-control mb-3" type="password" name="password" placeholder="Password (min 8 characters)" required>
    <button class="btn btn-primary w-100 py-2">Create account</button>
  </form>
  <p class="mt-3 text-center">Already have an account? <a href="login.php">Log in</a></p>
<?php endif;
auth_end();
