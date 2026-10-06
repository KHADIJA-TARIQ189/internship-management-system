<?php
require '../includes/auth.php';
require '../config/db.php';
require_role('student');

$st  = current_student($conn);
$sid = (int)$st['id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = $conn->prepare("UPDATE students SET full_name=?, phone=?, university=?, degree=?, skills=? WHERE id=?");
    $s->bind_param("sssssi", $_POST['full_name'], $_POST['phone'], $_POST['university'], $_POST['degree'], $_POST['skills'], $sid);
    $s->execute();
    $msg = "Profile updated.";
    $st = current_student($conn);
}

page_header('My Profile'); ?>
<h3>My Profile</h3>
<?php if ($msg) echo "<div class='alert alert-success'>" . e($msg) . "</div>"; ?>
<form method="post" class="col-md-6">
  <?php foreach (['full_name' => 'Full name', 'phone' => 'Phone', 'university' => 'University', 'degree' => 'Degree'] as $f => $l): ?>
    <label class="mt-2"><?= $l ?></label>
    <input class="form-control" name="<?= $f ?>" value="<?= e($st[$f]) ?>">
  <?php endforeach; ?>
  <label class="mt-2">Skills</label>
  <textarea class="form-control" name="skills" rows="3"><?= e($st['skills']) ?></textarea>
  <button class="btn btn-primary mt-3">Save</button>
</form>
<?php page_footer();