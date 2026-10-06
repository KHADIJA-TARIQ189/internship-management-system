<?php
require_once __DIR__ . '/../config/config.php';

const DISPOSABLE_DOMAINS = ['mailinator.com','10minutemail.com','guerrillamail.com','tempmail.com','temp-mail.org','yopmail.com',
    'trashmail.com','sharklasers.com','getnada.com','dispostable.com','maildrop.cc','throwawaymail.com','fakeinbox.com','mintemail.com'];

// Returns '' if the address looks real, otherwise an error message.
function check_email($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Please enter a valid email address.';
    $d = strtolower(substr(strrchr($email, '@'), 1));
    if (in_array($d, DISPOSABLE_DOMAINS, true)) return 'Disposable email addresses are not allowed.';
    if (!checkdnsrr($d, 'MX') && !checkdnsrr($d, 'A')) return 'That email domain cannot receive mail.';
    return '';
}

function send_mail($to, $subject, $html) {
    if (MAIL_DEV_MODE) {
        file_put_contents(__DIR__ . '/../logs/mail.log', date('c') . " TO: $to\n$subject\n" . strip_tags($html) . "\n\n", FILE_APPEND);
        return true;
    }
    require_once __DIR__ . '/../vendor/PHPMailer/Exception.php';
    require_once __DIR__ . '/../vendor/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/../vendor/PHPMailer/SMTP.php';
    try {
        $m = new PHPMailer\PHPMailer\PHPMailer(true);
        $m->isSMTP(); $m->Host = SMTP_HOST; $m->Port = SMTP_PORT; $m->SMTPAuth = true;
        $m->Username = SMTP_USER; $m->Password = SMTP_PASS; $m->SMTPSecure = 'tls';
        $m->setFrom(MAIL_FROM ?: SMTP_USER, SITE_NAME);
        $m->addAddress($to); $m->isHTML(true); $m->CharSet = 'UTF-8';
        $m->Subject = $subject; $m->Body = $html; $m->AltBody = strip_tags($html);
        $m->send();
        return true;
    } catch (Throwable $e) {
        error_log('Mail error: ' . $e->getMessage());
        return false;
    }
}

// Creates a fresh single-use token (stored hashed, valid 24h) and emails the link.
function issue_verification($conn, $uid, $email) {
    $token = bin2hex(random_bytes(32));
    $hash  = hash('sha256', $token);
    $s = $conn->prepare("UPDATE users SET verify_hash=?, verify_expires=DATE_ADD(NOW(), INTERVAL 24 HOUR), verify_sent_at=NOW() WHERE id=?");
    $s->bind_param("si", $hash, $uid);
    $s->execute();
    $link = site_url() . '/verify.php?token=' . $token;
    $body = "<div style='font-family:Arial,sans-serif;max-width:480px'><h2 style='color:#6D5BFF'>Welcome to " . SITE_NAME . "</h2>"
          . "<p>Please confirm your email address to activate your account.</p>"
          . "<p><a href='$link' style='background:#6D5BFF;color:#fff;padding:12px 22px;border-radius:8px;text-decoration:none;font-weight:bold'>Verify my email</a></p>"
          . "<p style='color:#777;font-size:13px'>This link expires in 24 hours. If you didn't sign up, ignore this email.<br>$link</p></div>";
    return send_mail($email, 'Verify your ' . SITE_NAME . ' account', $body);
}
