<?php
include '../config/session.php';

// Log the logout activity
if (isLoggedIn()) {
    include '../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    // Delete remember me sessions
    $query = "DELETE FROM user_sessions WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([getUserId()]);
}

// Destroy session
destroyUserSession();

// Redirect to home page
header("Location: ../index.php?message=logged_out");
exit();
?>
