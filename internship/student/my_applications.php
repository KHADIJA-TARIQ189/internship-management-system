<?php
require '../includes/auth.php';
require '../config/db.php';
require_role('student');

$st  = current_student($conn);
$sid = (int)$st['id'];

$rows = $conn->query("SELECT a.status, a.applied_at, i.title, c.company_name
    FROM applications a
    JOIN internships i ON i.id = a.internship_id
    JOIN companies c ON c.id = i.company_id
    WHERE a.student_id=$sid ORDER BY a.applied_at DESC");
$badge = ['pending' => 'warning', 'accepted' => 'success', 'rejected' => 'danger'];

page_header('My Applications'); ?>
<h3>My Applications</h3>
<table class="table bg-white">
  <tr><th>Internship</th><th>Company</th><th>Applied</th><th>Status</th></tr>
  <?php while ($a = $rows->fetch_assoc()): ?>
  <tr>
    <td><?= e($a['title']) ?></td>
    <td><?= e($a['company_name']) ?></td>
    <td><?= e($a['applied_at']) ?></td>
    <td><span class="badge bg-<?= $badge[$a['status']] ?>"><?= e($a['status']) ?></span></td>
  </tr>
  <?php endwhile; ?>
</table>
<?php page_footer();