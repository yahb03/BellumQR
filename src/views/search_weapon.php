<?php
require_once __DIR__ . '/../core/db.php';

if(isset($_POST['serie'])) {
    $serie = $_POST['serie'];
    
    $sql = "SELECT * FROM arma WHERE Serie = ? AND Estado_arma IN ('BUEN ESTADO', 'REGULAR ESTADO')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $serie);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $weapon = $result->fetch_assoc();
        echo "Weapon found: " . $weapon['Tipo_arma'] . " - " . $weapon['Modelo'] . " (Status: " . $weapon['Estado_arma'] . "). Available for assignment.";
    } else {
        echo "No available weapon found with this serial number or not in proper condition.";
    }
    
    $stmt->close();
}
$conn->close();
?>