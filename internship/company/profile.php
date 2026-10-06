<?php
require '../includes/auth.php';
require '../config/db.php';
require_role('company');

$co  = current_company($conn);
$cid = (int)$co['id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = $conn->prepare("UPDATE companies SET company_name=?, industry=?, address=?, description=? WHERE id=?");
    $s->bind_param("ssssi", $_POST['company_name'], $_POST['industry'], $_POST['address'], $_POST['description'], $cid);
    $s->execute();
    $msg = "Saved.";
    $co = current_company($conn);
}

page_header('Company Profile'); ?>
<h3>Company Profile</h3>
<?php if ($msg) echo "<div class='alert alert-success'>" . e($msg) . "</div>"; ?>
<form method="post" class="col-md-6">
  <?php foreach (['company_name' => 'Company name', 'industry' => 'Industry', 'address' => 'Address'] as $f => $l): ?>
    <label class="mt-2"><?= $l ?></label>
    <input class="form-control" name="<?= $f ?>" value="<?= e($co[$f]) ?>">
  <?php endforeach; ?>
  <label class="mt-2">Description</label>
  <textarea class="form-control" name="description" rows="4"><?= e($co['description']) ?></textarea>
  <button class="btn btn-primary mt-3">Save</button>
</form>
<?php page_footer();