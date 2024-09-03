<?php
include 'db.php';

$qr_code = $_GET['qr_code'];

$sql = "SELECT * FROM arma WHERE Serie='$qr_code'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Weapon ID: " . $row["Id_arma"]. " - Type: " . $row["Tipo_arma"]. " - Model: " . $row["Modelo"]. "<br>";
    }
} else {
    echo "No weapon found";
}

$conn->close();
?>
