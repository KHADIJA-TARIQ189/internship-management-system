<?php
require '../includes/auth.php';
require '../config/db.php';
require_role('student');

$uid = (int)$_SESSION['user_id'];
$st = $conn->query("SELECT id, full_name FROM students WHERE user_id=$uid")->fetch_assoc();
$sid = (int)$st['id'];

$counts = ['pending' => 0, 'accepted' => 0, 'rejected' => 0];
$r = $conn->query("SELECT status, COUNT(*) c FROM applications WHERE student_id=$sid GROUP BY status");
while ($x = $r->fetch_assoc()) $counts[$x['status']] = $x['c'];

$apps = $conn->query("SELECT a.status, a.applied_at, i.title, c.company_name
    FROM applications a
    JOIN internships i ON i.id = a.internship_id
    JOIN companies c ON c.id = i.company_id
    WHERE a.student_id=$sid ORDER BY a.applied_at DESC");
$badge = ['pending' => 'warning', 'accepted' => 'success', 'rejected' => 'danger'];

page_header('Student Dashboard'); ?>
<h3>Welcome, <?= e($st['full_name']) ?></h3>

<div class="row g-3 my-2">
<?php foreach ($counts as $k => $v): ?>
  <div class="col-md-4">
    <div class="card text-center p-3"><h2><?= $v ?></h2><span class="text-capitalize"><?= $k ?></span></div>
  </div>
<?php endforeach; ?>
</div>

<h5 class="mt-4">My Applications</h5>
<table class="table bg-white">
  <tr><th>Internship</th><th>Company</th><th>Applied</th><th>Status</th></tr>
  <?php while ($a = $apps->fetch_assoc()): ?>
  <tr>
    <td><?= e($a['title']) ?></td>
    <td><?= e($a['company_name']) ?></td>
    <td><?= e($a['applied_at']) ?></td>
    <td><span class="badge bg-<?= $badge[$a['status']] ?>"><?= e($a['status']) ?></span></td>
  </tr>
  <?php endwhile; ?>
</table>
<?php page_footer();