<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Destroy session
session_unset();
session_destroy();

// Delete remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
  setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to home page
header('Location: ../../../index.php');
exit;
?>
