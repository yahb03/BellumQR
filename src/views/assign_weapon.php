<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../../phpqrcode/qrlib.php'; 

function isWeaponAvailable($conn, $serie) {
    $sql = "SELECT Estado_arma FROM arma WHERE Serie = ? AND Estado_arma IN ('BUEN ESTADO', 'REGULAR ESTADO')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $serie);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function getWeaponDetails($conn, $serie) {
    $sql = "SELECT * FROM arma WHERE Serie = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $serie);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getUserDetails($conn, $cedula) {
    $sql = "SELECT * FROM usuario WHERE Cedula = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serie = $_POST['serie'];
    $cedula = $_POST['cedula'];
    $fecha_asignacion = date('Y-m-d');

    if (isWeaponAvailable($conn, $serie)) {
        $sql = "INSERT INTO asignacion (Serie, Cedula, Fecha_asignacion) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $serie, $cedula, $fecha_asignacion);
        
        if ($stmt->execute()) {
            $update_sql = "UPDATE arma SET Ubicacion_actual = ? WHERE Serie = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $cedula, $serie);
            $update_stmt->execute();
            $update_stmt->close();

            // Get weapon and user details
            $weapon = getWeaponDetails($conn, $serie);
            $user = getUserDetails($conn, $cedula);

            // Generate QR code with detailed information
            $qr_data = "Asignación:\n";
            $qr_data .= "Fecha: $fecha_asignacion\n";
            $qr_data .= "Arma:\n";
            $qr_data .= "  Serie: {$weapon['Serie']}\n";
            $qr_data .= "  Tipo: {$weapon['Tipo_arma']}\n";
            $qr_data .= "  Modelo: {$weapon['Modelo']}\n";
            $qr_data .= "Usuario:\n";
            $qr_data .= "  Cedula: {$user['Cedula']}\n";
            $qr_data .= "  Nombre: {$user['Nombre']} {$user['Apellido']}\n";
            $qr_data .= "  Grado: {$user['Grado']}\n";
            $qr_data .= "  Unidad: {$user['Unidad']}\n";

            $qr_filename = 'assign_' . $serie . '_' . $cedula . '.png';
            $qr_path = __DIR__ . '/../qrcodes/' . $qr_filename;
            QRcode::png($qr_data, $qr_path);

            header("Location: success.php?message=Weapon+assigned+successfully&qr=$qr_filename&type=assignment");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error: Weapon is not available for assignment.";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Armamento</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <div class=container>
    <h1>Asignar Armamento</h1>
<form action="assign_weapon.php" method="post" id="assignForm">
    <label for="serie">Serial del Arma:</label>
    <input type="text" id="serie" name="serie" required>
    <button type="button" id="searchWeapon">Buscar</button>
    <div id="weaponInfo"></div>

    <label for="cedula">Cedula/ ID:</label>
    <input type="text" id="cedula" name="cedula" required>
    <button type="button" id="searchUser">Buscar</button>
    <div id="userInfo"></div>

    <button type="submit" id="assignButton" disabled>Asignar</button>
</form>
<a href="index.php" class="button">Volver</a>

    <script>
$(document).ready(function() {
    $('#searchWeapon').click(function() {
        var serie = $('#serie').val();
        $.ajax({
            url: 'search_weapon.php',
            type: 'POST',
            data: {serie: serie},
            success: function(response) {
                $('#weaponInfo').html(response);
                checkAssignButton();
            }
        });
    });

    $('#searchUser').click(function() {
        var cedula = $('#cedula').val();
        $.ajax({
            url: 'search_user.php',
            type: 'POST',
            data: {cedula: cedula},
            success: function(response) {
                $('#userInfo').html(response);
                checkAssignButton();
            }
        });
    });

    function checkAssignButton() {
        if ($('#weaponInfo').text().includes("Available") && $('#userInfo').text().includes("found")) {
            $('#assignButton').prop('disabled', false);
        } else {
            $('#assignButton').prop('disabled', true);
        }
    }

    // Ensure the button is always visible
    $('#assignButton').show();
});
</script>
</div>
</body>
</html>