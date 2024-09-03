<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only admin or super_user can perform this action
check_role(['admin', 'super_user']);

if (isset($_GET['serie'])) {
    $serie = $conn->real_escape_string($_GET['serie']);

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Check if the weapon is currently assigned
        $check_sql = "SELECT * FROM asignacion WHERE Serie = ? AND Fecha_devolucion IS NULL";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $serie);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            throw new Exception("Este arma está actualmente asignada y no puede ser eliminada.");
        }

        // Soft delete: Update the weapon to set is_active to FALSE instead of deleting
        $sql = "UPDATE arma SET is_active = FALSE WHERE Serie = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $serie);
        
        if ($stmt->execute()) {
            // Log the soft delete action
            $log_sql = "INSERT INTO weapon_action_log (action_type, affected_weapon, performed_by) VALUES ('soft_delete', ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $performed_by = $_SESSION['cedula'] ?? 'Unknown'; // Assuming you store logged-in user's cedula in session
            $log_stmt->bind_param("ss", $serie, $performed_by);
            $log_stmt->execute();

            $conn->commit();
            header("Location: view_weapons.php?message=" . urlencode("Arma desactivada exitosamente"));
            exit();
        } else {
            throw new Exception("Error desactivando el arma");
        }
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: error.php?message=" . urlencode("Error: " . $e->getMessage()));
        exit();
    } finally {
        $conn->close();
    }
} else {
    header("Location: error.php?message=" . urlencode("No se especificó ningún arma para desactivar"));
    exit();
}
?>