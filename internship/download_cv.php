<?php
// CVs are private: only the applicant, the company that received it, or an admin may open one.
require 'includes/auth.php';
require 'config/db.php';
$f = basename($_GET['f'] ?? '');
$role = $_SESSION['role'] ?? ''; $uid = (int)($_SESSION['user_id'] ?? 0); $ok = false;
if ($role === 'admin') {
    $ok = true;
} elseif ($role === 'student') {
    $s = $conn->prepare("SELECT 1 FROM applications a JOIN students s ON s.id=a.student_id WHERE a.cv=? AND s.user_id=?");
} elseif ($role === 'company') {
    $s = $conn->prepare("SELECT 1 FROM applications a JOIN internships i ON i.id=a.internship_id JOIN companies c ON c.id=i.company_id WHERE a.cv=? AND c.user_id=?");
}
if (!$ok && isset($s)) { $s->bind_param("si", $f, $uid); $s->execute(); $ok = $s->get_result()->num_rows > 0; }
$path = __DIR__ . "/uploads/cvs/$f";
if (!$ok || !is_file($path)) { http_response_code(404); exit('Not found'); }
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="cv.pdf"');
header('X-Content-Type-Options: nosniff');
readfile($path);
