<?php
require '../includes/auth.php';
require '../config/db.php';
require_role('student');

$st  = current_student($conn);
$sid = (int)$st['id'];

$applied = [];
$r = $conn->query("SELECT internship_id FROM applications WHERE student_id=$sid");
while ($x = $r->fetch_assoc()) $applied[$x['internship_id']] = true;

$q   = "%" . ($_GET['q'] ?? '') . "%";
$loc = "%" . ($_GET['loc'] ?? '') . "%";
$s = $conn->prepare("SELECT i.*, c.company_name
    FROM internships i JOIN companies c ON c.id = i.company_id
    WHERE i.status='active' AND c.approved=1
      AND (i.deadline IS NULL OR i.deadline >= CURDATE())
      AND (i.title LIKE ? OR i.skills_required LIKE ?)
      AND (i.location LIKE ? OR i.location IS NULL)
    ORDER BY i.created_at DESC");
$s->bind_param("sss", $q, $q, $loc);
$s->execute();
$rows = $s->get_result();

page_header('Internships'); ?>
<h3>Available Internships</h3>

<form class="row g-2 mb-3">
  <div class="col-md-5"><input class="form-control" name="q" placeholder="Title or skill" value="<?= e($_GET['q'] ?? '') ?>"></div>
  <div class="col-md-4"><input class="form-control" name="loc" placeholder="Location" value="<?= e($_GET['loc'] ?? '') ?>"></div>
  <div class="col-md-3"><button class="btn btn-dark w-100">Search</button></div>
</form>

<?php if ($rows->num_rows === 0): ?>
  <div class="alert alert-secondary">No internships available right now.</div>
<?php endif; ?>

<?php while ($i = $rows->fetch_assoc()): ?>
<div class="card mb-3"><div class="card-body">
  <h5><?= e($i['title']) ?> <small class="text-muted">at <?= e($i['company_name']) ?></small></h5>
  <p><?= nl2br(e($i['description'])) ?></p>
  <p class="small mb-2">
    <b>Skills:</b> <?= e($i['skills_required']) ?> |
    <b>Location:</b> <?= e($i['location']) ?> |
    <b>Duration:</b> <?= e($i['duration']) ?> |
    <b>Deadline:</b> <?= e($i['deadline']) ?>
  </p>
  <?php if (isset($applied[$i['id']])): ?>
    <span class="badge bg-secondary">Already applied</span>
  <?php else: ?>
    <a class="btn btn-success" href="apply.php?id=<?= $i['id'] ?>">Apply Now</a>
  <?php endif; ?>
</div></div>
<?php endwhile; ?>
<?php page_footer();