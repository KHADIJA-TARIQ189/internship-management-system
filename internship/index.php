<?php
require 'includes/auth.php';
require 'config/db.php';
$n = fn($q) => (int)$conn->query($q)->fetch_row()[0];
$stats = [
  'Verified students'  => $n("SELECT COUNT(*) FROM users u JOIN students s ON s.user_id=u.id WHERE u.email_verified=1"),
  'Approved companies' => $n("SELECT COUNT(*) FROM companies WHERE approved=1"),
  'Open internships'   => $n("SELECT COUNT(*) FROM internships i JOIN companies c ON c.id=i.company_id WHERE i.status='active' AND c.approved=1 AND (i.deadline IS NULL OR i.deadline>=CURDATE())"),
];
$role = $_SESSION['role'] ?? '';
sk_head('Find your first real internship'); ?>
<body>
<div id="splash"><div><img src="<?= BASE ?>/assets/logo.svg" alt=""><small>Welcome to</small><h1><?= SITE_NAME ?></h1></div></div>
<script>
(function(){var s=document.getElementById('splash');
 function hide(){s.classList.add('hide');setTimeout(function(){s.remove()},700)}
 try{if(sessionStorage.getItem('sk_seen')){s.remove();return}sessionStorage.setItem('sk_seen','1')}catch(e){}
 s.onclick=hide;setTimeout(hide,2600);})();
</script>

<nav class="sk-nav">
  <?php brand_link(); ?>
  <span class="ms-auto d-flex gap-2">
  <?php if ($role): ?>
    <a class="btn btn-primary btn-sm" href="<?= BASE ?>/<?= e($role) ?>/dashboard.php">Go to dashboard</a>
  <?php else: ?>
    <a class="btn btn-outline-secondary btn-sm" href="<?= BASE ?>/login.php">Log in</a>
    <a class="btn btn-primary btn-sm" href="<?= BASE ?>/register.php">Sign up free</a>
  <?php endif; ?>
  </span>
</nav>

<header class="hero"><div class="container text-center" style="max-width:820px">
  <span class="pill mb-4">✔ Email-verified students · Approved companies</span>
  <h1>Launch your career with <span class="grad">real internships</span></h1>
  <p class="lead text-secondary my-4">Build your profile once, discover internships from trusted companies, and apply in minutes with your CV.</p>
  <div class="d-flex gap-2 justify-content-center flex-wrap">
    <a class="btn btn-primary btn-lg px-4" href="<?= BASE ?>/register.php">Get started</a>
    <a class="btn btn-outline-dark btn-lg px-4" href="<?= BASE ?>/login.php">I have an account</a>
  </div>
</div></header>

<section class="container"><div class="row text-center g-3 py-3">
  <?php foreach ($stats as $label => $v): ?>
    <div class="col-md-4 stat"><b class="grad"><?= $v ?></b><span class="text-secondary"><?= $label ?></span></div>
  <?php endforeach; ?>
</div></section>

<section class="container mt-5"><div class="row g-4">
  <?php foreach ([['🔒','Verified accounts','Everyone confirms their email before they can log in. No fake or throw-away sign-ups.'],
                  ['🏢','Approved companies','Companies are reviewed by an admin before they can post, so every listing is genuine.'],
                  ['⚡','Apply in minutes','One form, one PDF CV, and your application goes straight to the hiring team.']] as [$i,$t,$d]): ?>
    <div class="col-md-4"><div class="card feat"><div class="ico"><?= $i ?></div><h5 class="fw-bold"><?= $t ?></h5><p class="text-secondary mb-0"><?= $d ?></p></div></div>
  <?php endforeach; ?>
</div></section>

<section class="container mt-5"><h3 class="fw-bold text-center mb-4">How it works</h3>
<div class="row g-4" style="counter-reset:s">
  <?php foreach ([['Create your account','Sign up and confirm your email.'],['Find an internship','Search by skill, title or location.'],['Apply & track','Upload your CV and follow your status live.']] as [$t,$d]): ?>
    <div class="col-md-4 step"><h5 class="fw-bold"><?= $t ?></h5><p class="text-secondary"><?= $d ?></p></div>
  <?php endforeach; ?>
</div></section>

<section class="container mt-5"><div class="cta text-center">
  <h2 class="fw-bold">Ready to take the first step?</h2>
  <p class="opacity-75">Hiring interns? Register your company and reach motivated students.</p>
  <a class="btn btn-light btn-lg px-4" href="<?= BASE ?>/register.php">Create your free account</a>
</div></section>

<footer class="sk-foot"><div class="container d-flex justify-content-between flex-wrap gap-2">
  <span><?= SITE_NAME ?> · Internship platform</span><span>© <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</span>
</div></footer>
</body></html>
