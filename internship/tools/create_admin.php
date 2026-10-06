<?php
// Usage (command line only):  php tools/create_admin.php admin@yourmail.com "StrongPassword"
if (PHP_SAPI !== 'cli') exit('CLI only');
require __DIR__ . '/../config/db.php';
$email = $argv[1] ?? ''; $pass = $argv[2] ?? '';
if (!$email || strlen($pass) < 8) exit("Usage: php tools/create_admin.php email password(min 8)\n");
$h = password_hash($pass, PASSWORD_DEFAULT);
$s = $conn->prepare("INSERT INTO users (email, password, role, email_verified) VALUES (?,?,'admin',1)");
$s->bind_param("ss", $email, $h);
$s->execute();
echo "Admin created: $email\n";
