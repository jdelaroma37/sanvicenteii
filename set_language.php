<?php

session_start();

require_once __DIR__ . '/lang/language_helper.php';

if (isset($_POST['lang'])) {
    lang_set_language($_POST['lang']);
}

$redirect = $_POST['redirect'] ?? ($_SERVER['HTTP_REFERER'] ?? '/');

// Ensure we only redirect within this application.
if (strpos($redirect, '/') !== 0) {
    $redirect = '/';
}

header('Location: ' . $redirect);
exit;

