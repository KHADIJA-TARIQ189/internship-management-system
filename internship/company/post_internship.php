<?php
require '../includes/auth.php';
require '../config/db.php';
require_role('company');

$co  = current_company($conn);
$cid = (int)$co['id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $co['approved']) {
    $s = $conn->prepare("INSERT INTO internships (company_id, title, description, skills_required, location, duration, deadline)
                         VALUES (?,?,?,?,?,?,?)");
    $s->bind_param("issssss", $cid, $_POST['title'], $_POST['description'], $_POST['skills_required'],
                   $_POST['location'], $_POST['duration'], $_POST['deadline']);
    $s->execute();
    $msg = "Internship posted.";
}

page_header('Post Internship'); ?>
<h3>Post Internship</h3>
<?php if (!$co['approved']): ?>
  <div class="alert alert-warning">Your company is awaiting admin approval. You can't post yet.</div>
<?php endif; ?>
<?php if ($msg) echo "<div class='alert alert-success'>" . e($msg) . "</div>"; ?>

<form method="post" class="col-md-7">
  <input class="form-control mb-2" name="title" placeholder="Title" required>
  <textarea class="form-control mb-2" name="description" rows="4" placeholder="Description"></textarea>
  <textarea class="form-control mb-2" name="skills_required" rows="2" placeholder="Skills required"></textarea>
  <input class="form-control mb-2" name="location" placeholder="Location">
  <input class="form-control mb-2" name="duration" placeholder="Duration (e.g. 3 months)">
  <label class="small text-muted">Deadline</label>
  <input class="form-control mb-3" type="date" name="deadline" required>
  <button class="btn btn-primary" <?= $co['approved'] ? '' : 'disabled' ?>>Post</button>
</form>
<?php page_footer();