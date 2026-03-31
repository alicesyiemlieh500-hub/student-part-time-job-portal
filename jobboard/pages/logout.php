<?php
require_once '../includes/config.php';

// Destroy all session data
session_destroy();

// Redirect to home page
header("Location: home.php");
exit();
?>