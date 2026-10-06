<?php
// ======== EDIT THESE BEFORE GOING LIVE ========
const SITE_NAME = 'Skilvane';
const SITE_URL  = '';   // Live address incl. folder, no trailing slash, e.g. 'https://skilvane.example.com'. Empty = auto-detect.
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'internship_db';
// Email sending (needed for verification links)
const SMTP_HOST = 'smtp.gmail.com';
const SMTP_PORT = 587;
const SMTP_USER = 'dimplequeen4453@gmail.com';   // <-- put your Gmail address here
const SMTP_PASS = '';    // <-- put the 16-character app password here, no spaces
const MAIL_FROM = '';   // blank = SMTP_USER
const MAIL_DEV_MODE = false;   // false = send real emails
const ALLOWED_STUDENT_DOMAINS = [];   // [] = any real email accepted
// ===============================================

$__root = str_replace('\\', '/', dirname(__DIR__));
$__doc  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
define('BASE', ($__doc !== '' && strpos($__root, $__doc) === 0) ? substr($__root, strlen($__doc)) : '/internship');

function site_url() {
    if (SITE_URL !== '') return rtrim(SITE_URL, '/');
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE;
}