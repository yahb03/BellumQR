<?php
require_once __DIR__ . '/../core/db.php';

if(isset($_POST['cedula'])) {
    $cedula = $_POST['cedula'];
    
    $sql = "SELECT * FROM usuario WHERE Cedula = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "User found: {$user['Nombre']} {$user['Apellido']} (Grado: {$user['Grado']}, Unidad: {$user['Unidad']})";
    } else {
        echo "No user found with this ID.";
    }
    
    $stmt->close();
}
$conn->close();
?>