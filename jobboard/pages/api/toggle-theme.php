<?php
require_once '../../includes/config.php';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// If POST data exists, use it, otherwise just toggle
if(isset($input['theme'])) {
    $_SESSION['theme'] = $input['theme'];
} else {
    // Toggle theme in session
    if($_SESSION['theme'] == 'light') {
        $_SESSION['theme'] = 'dark';
    } else {
        $_SESSION['theme'] = 'light';
    }
}

// Set cookie for persistence (1 year)
setcookie('theme', $_SESSION['theme'], time() + (86400 * 365), '/');

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'theme' => $_SESSION['theme']
]);
?>