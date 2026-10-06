<?php
require '../includes/auth.php';
require '../config/db.php';
require_role('company');

$uid = (int)$_SESSION['user_id'];
$co  = $conn->query("SELECT * FROM companies WHERE user_id=$uid")->fetch_assoc();
$cid = (int)$co['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['status'] ?? '', ['accepted', 'rejected'])) {
    $aid = (int)$_POST['id'];
    $s = $conn->prepare("UPDATE applications a JOIN internships i ON i.id=a.internship_id
        SET a.status=? WHERE a.id=? AND i.company_id=?");
    $s->bind_param("sii", $_POST['status'], $aid, $cid);
    $s->execute();
}

$ni = $conn->query("SELECT COUNT(*) FROM internships WHERE company_id=$cid")->fetch_row()[0];
$na = $conn->query("SELECT COUNT(*) FROM applications a JOIN internships i ON i.id=a.internship_id WHERE i.company_id=$cid")->fetch_row()[0];

$rows = $conn->query("SELECT a.id, a.cv, a.status, i.title, s.full_name, s.university, s.degree, u.email
    FROM applications a
    JOIN internships i ON i.id = a.internship_id
    JOIN students s ON s.id = a.student_id
    JOIN users u ON u.id = s.user_id
    WHERE i.company_id=$cid ORDER BY a.applied_at DESC");

page_header('Company Dashboard'); ?>
<h3><?= e($co['company_name']) ?></h3>

<?php if (!$co['approved']): ?>
  <div class="alert alert-warning">Your company is awaiting admin approval.</div>
<?php endif; ?>

<div class="row g-3 mb-4">
  <div class="col-md-6"><div class="card text-center p-3"><h2><?= $ni ?></h2>Internships posted</div></div>
  <div class="col-md-6"><div class="card text-center p-3"><h2><?= $na ?></h2>Applications received</div></div>
</div>

<h5>Applicants</h5>
<table class="table bg-white">
  <tr><th>Student</th><th>Internship</th><th>University / Degree</th><th>CV</th><th>Status</th><th></th></tr>
  <?php while ($a = $rows->fetch_assoc()): ?>
  <tr>
    <td><?= e($a['full_name']) ?><br><small><?= e($a['email']) ?></small></td>
    <td><?= e($a['title']) ?></td>
    <td><?= e($a['university']) ?><br><small><?= e($a['degree']) ?></small></td>
    <td><a href="<?= BASE ?>/download_cv.php?f=<?= urlencode($a['cv']) ?>" target="_blank">View CV</a></td>
    <td><?= e($a['status']) ?></td>
    <td>
      <form method="post" class="d-flex gap-1">
        <input type="hidden" name="id" value="<?= $a['id'] ?>">
        <button name="status" value="accepted" class="btn btn-sm btn-success">Accept</button>
        <button name="status" value="rejected" class="btn btn-sm btn-danger">Reject</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php page_footer();