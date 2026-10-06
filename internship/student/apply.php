<?php
require '../includes/auth.php';
require '../config/db.php';
require_role('student');

$st  = current_student($conn);
$sid = (int)$st['id'];
$iid = (int)($_GET['id'] ?? $_POST['internship_id'] ?? 0);

$s = $conn->prepare("SELECT i.*, c.company_name FROM internships i
    JOIN companies c ON c.id = i.company_id
    WHERE i.id=? AND i.status='active' AND c.approved=1
      AND (i.deadline IS NULL OR i.deadline >= CURDATE())");
$s->bind_param("i", $iid);
$s->execute();
$in = $s->get_result()->fetch_assoc();
if (!$in) { header("Location: internships.php"); exit; }

$msg = ''; $done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = $_FILES['cv'] ?? null;
    $ok = $f && $f['error'] === UPLOAD_ERR_OK
        && $f['size'] <= 2 * 1024 * 1024
        && strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)) === 'pdf'
        && (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) === 'application/pdf';

    if (!$ok) {
        $msg = "Please upload a PDF CV (max 2 MB).";
    } else {
        $u = $conn->prepare("UPDATE students SET full_name=?, phone=?, university=?, degree=? WHERE id=?");
        $u->bind_param("ssssi", $_POST['full_name'], $_POST['phone'], $_POST['university'], $_POST['degree'], $sid);
        $u->execute();

        $name = "cv_{$sid}_{$iid}_" . time() . ".pdf";
        move_uploaded_file($f['tmp_name'], "../uploads/cvs/$name");
        try {
            $a = $conn->prepare("INSERT INTO applications (student_id, internship_id, cv, cover_letter) VALUES (?,?,?,?)");
            $a->bind_param("iiss", $sid, $iid, $name, $_POST['cover_letter']);
            $a->execute();
            $done = true;
        } catch (mysqli_sql_exception $ex) {
            @unlink("../uploads/cvs/$name");
            $msg = "You already applied to this internship.";
        }
    }
    $st = current_student($conn);
}

page_header('Apply'); ?>
<h3>Apply: <?= e($in['title']) ?> <small class="text-muted">at <?= e($in['company_name']) ?></small></h3>

<?php if ($done): ?>
  <div class="alert alert-success">Application submitted!
    <a href="my_applications.php">View my applications</a></div>
<?php else: ?>
  <?php if ($msg) echo "<div class='alert alert-danger'>" . e($msg) . "</div>"; ?>

  <div class="card mb-3"><div class="card-body small">
    <?= nl2br(e($in['description'])) ?><br><br>
    <b>Skills:</b> <?= e($in['skills_required']) ?> |
    <b>Location:</b> <?= e($in['location']) ?> |
    <b>Duration:</b> <?= e($in['duration']) ?> |
    <b>Deadline:</b> <?= e($in['deadline']) ?>
  </div></div>

  <form method="post" enctype="multipart/form-data" class="col-md-7">
    <input type="hidden" name="internship_id" value="<?= $iid ?>">

    <label>Full name</label>
    <input class="form-control mb-2" name="full_name" value="<?= e($st['full_name']) ?>" required>
    <label>Phone</label>
    <input class="form-control mb-2" name="phone" value="<?= e($st['phone']) ?>" required>
    <label>University</label>
    <input class="form-control mb-2" name="university" value="<?= e($st['university']) ?>" required>
    <label>Degree</label>
    <input class="form-control mb-2" name="degree" value="<?= e($st['degree']) ?>" required>
    <label>Cover letter (why should they choose you?)</label>
    <textarea class="form-control mb-2" name="cover_letter" rows="5" required></textarea>
    <label>CV (PDF, max 2 MB)</label>
    <input class="form-control mb-3" type="file" name="cv" accept="application/pdf" required>

    <button class="btn btn-success">Submit Application</button>
    <a class="btn btn-outline-secondary" href="internships.php">Cancel</a>
  </form>
<?php endif; ?>
<?php page_footer();