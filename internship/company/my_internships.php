<?php
require '../includes/auth.php';
require '../config/db.php';
require_role('company');

$co  = current_company($conn);
$cid = (int)$co['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    if (($_POST['act'] ?? '') === 'delete') {
        $s = $conn->prepare("DELETE FROM internships WHERE id=? AND company_id=?");
    } else {
        $s = $conn->prepare("UPDATE internships SET status=IF(status='active','closed','active') WHERE id=? AND company_id=?");
    }
    $s->bind_param("ii", $id, $cid);
    $s->execute();
}

$rows = $conn->query("SELECT * FROM internships WHERE company_id=$cid ORDER BY created_at DESC");

page_header('My Internships'); ?>
<h3>My Internships</h3>
<table class="table bg-white">
  <tr><th>Title</th><th>Location</th><th>Deadline</th><th>Status</th><th></th></tr>
  <?php while ($i = $rows->fetch_assoc()): ?>
  <tr>
    <td><?= e($i['title']) ?></td>
    <td><?= e($i['location']) ?></td>
    <td><?= e($i['deadline']) ?></td>
    <td><?= e($i['status']) ?></td>
    <td>
      <form method="post" class="d-flex gap-1">
        <input type="hidden" name="id" value="<?= $i['id'] ?>">
        <button name="act" value="toggle" class="btn btn-sm btn-outline-secondary">Open/Close</button>
        <button name="act" value="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this internship?')">Delete</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php page_footer();