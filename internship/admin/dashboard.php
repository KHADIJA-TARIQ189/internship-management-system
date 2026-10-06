<?php
require '../includes/auth.php';
require '../config/db.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id  = (int)$_POST['id'];
    $act = $_POST['act'] ?? '';
    if ($act === 'approve') $conn->query("UPDATE companies SET approved=1 WHERE id=$id");
    if ($act === 'revoke')  $conn->query("UPDATE companies SET approved=0 WHERE id=$id");
    if ($act === 'del_company') $conn->query("DELETE FROM users WHERE id=(SELECT user_id FROM companies WHERE id=$id)");
    if ($act === 'del_student') $conn->query("DELETE FROM users WHERE id=(SELECT user_id FROM students WHERE id=$id)");
}

$stat = fn($t) => $conn->query("SELECT COUNT(*) FROM $t")->fetch_row()[0];

$companies = $conn->query("SELECT * FROM companies ORDER BY approved, id DESC");
$students  = $conn->query("SELECT s.id, s.full_name, s.university, u.email FROM students s JOIN users u ON u.id=s.user_id");
$apps = $conn->query("SELECT s.full_name, i.title, c.company_name, a.status, a.applied_at
    FROM applications a
    JOIN students s ON s.id = a.student_id
    JOIN internships i ON i.id = a.internship_id
    JOIN companies c ON c.id = i.company_id
    ORDER BY a.applied_at DESC");

page_header('Admin Dashboard'); ?>
<h3>Admin Dashboard</h3>

<div class="row g-3 mb-4">
<?php foreach (['students' => 'Students', 'companies' => 'Companies', 'internships' => 'Internships', 'applications' => 'Applications'] as $t => $l): ?>
  <div class="col-md-3"><div class="card text-center p-3"><h2><?= $stat($t) ?></h2><?= $l ?></div></div>
<?php endforeach; ?>
</div>

<h5>Companies</h5>
<table class="table bg-white">
  <tr><th>Name</th><th>Industry</th><th>Approved</th><th></th></tr>
  <?php while ($c = $companies->fetch_assoc()): ?>
  <tr>
    <td><?= e($c['company_name']) ?></td>
    <td><?= e($c['industry']) ?></td>
    <td><?= $c['approved'] ? 'Yes' : 'No' ?></td>
    <td>
      <form method="post" class="d-flex gap-1">
        <input type="hidden" name="id" value="<?= $c['id'] ?>">
        <?php if ($c['approved']): ?>
          <button name="act" value="revoke" class="btn btn-sm btn-warning">Revoke</button>
        <?php else: ?>
          <button name="act" value="approve" class="btn btn-sm btn-success">Approve</button>
        <?php endif; ?>
        <button name="act" value="del_company" class="btn btn-sm btn-danger" onclick="return confirm('Delete company?')">Delete</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>

<h5>Students</h5>
<table class="table bg-white">
  <tr><th>Name</th><th>Email</th><th>University</th><th></th></tr>
  <?php while ($s = $students->fetch_assoc()): ?>
  <tr>
    <td><?= e($s['full_name']) ?></td>
    <td><?= e($s['email']) ?></td>
    <td><?= e($s['university']) ?></td>
    <td>
      <form method="post">
        <input type="hidden" name="id" value="<?= $s['id'] ?>">
        <button name="act" value="del_student" class="btn btn-sm btn-danger" onclick="return confirm('Delete student?')">Delete</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>

<h5>All Applications</h5>
<table class="table bg-white">
  <tr><th>Student</th><th>Internship</th><th>Company</th><th>Status</th><th>Date</th></tr>
  <?php while ($a = $apps->fetch_assoc()): ?>
  <tr>
    <td><?= e($a['full_name']) ?></td>
    <td><?= e($a['title']) ?></td>
    <td><?= e($a['company_name']) ?></td>
    <td><?= e($a['status']) ?></td>
    <td><?= e($a['applied_at']) ?></td>
  </tr>
  <?php endwhile; ?>
</table>
<?php page_footer();