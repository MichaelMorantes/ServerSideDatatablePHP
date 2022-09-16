<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();

if (isset($_SESSION['timeout']) && (time() - $_SESSION['timeout'] > 60 * 30)) {
    session_unset();
    session_destroy();
    header('Location: index.php');
} else {
    session_regenerate_id(true);
    $_SESSION['timeout'] = time();
}

require_once __DIR__ . '/template/header.html';

if (!empty($_SESSION['user']) && isset($_GET['tabla'])) {
    $content = __DIR__ . '/template/main/index.phtml';
} elseif (!empty($_SESSION['user'])) {
    $content = __DIR__ . '/template/menu/index.phtml';
} else {
    $content = __DIR__ . '/template/login/index.phtml';
    // HHFRD_JU
}
require $content;

require_once __DIR__ . '/template/footer.html';
