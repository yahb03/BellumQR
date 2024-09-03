<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only admin or super_user can perform this action
check_role(['admin', 'super_user']);

// Check if 'cedula' parameter is present in the URL
if (isset($_GET['cedula'])) {
    $cedula = $conn->real_escape_string($_GET['cedula']);

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Update the user to set is_active to FALSE instead of deleting
        $sql = "UPDATE usuario SET is_active = FALSE WHERE Cedula = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $cedula);
        
        if ($stmt->execute()) {
            // Log the soft delete action
            $log_sql = "INSERT INTO user_action_log (action_type, affected_user, performed_by) VALUES ('soft_delete', ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $performed_by = $_SESSION['cedula'] ?? 'Unknown'; // Assuming you store logged-in user's cedula in session
            $log_stmt->bind_param("ss", $cedula, $performed_by);
            $log_stmt->execute();

            $conn->commit();
            header("Location: view_users.php?message=User+deactivated+successfully");
            exit();
        } else {
            throw new Exception("Error deactivating user");
        }
    } catch (Exception $e) {
        $conn->rollback();
        header("../src/views/error.php" . urlencode("Error: " . $e->getMessage()));
        exit();
    }
} else {
    header("../src/views/error.php" . urlencode("No user specified for deactivation"));
    exit();
}

$conn->close();
?>