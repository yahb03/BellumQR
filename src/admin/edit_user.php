<?php
require_once __DIR__ . '/../core/db.php';

// Check if 'cedula' parameter is present in the URL
if (isset($_GET['cedula'])) {
    $cedula = $conn->real_escape_string($_GET['cedula']);

    // Query to fetch user details based on Cedula
    $sql = "SELECT * FROM usuario WHERE Cedula='$cedula'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "No user found with that Cedula.";
        exit();
    }
} else {
    echo "No Cedula specified.";
    exit();
}

// If the form is submitted, update the user details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $apellido = $conn->real_escape_string($_POST['apellido']);
    $grado = $conn->real_escape_string($_POST['grado']);
    $unidad = $conn->real_escape_string($_POST['unidad']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $correo_electronico = $conn->real_escape_string($_POST['correo_electronico']);
    $role = $conn->real_escape_string($_POST['role']);

    // Update query
    $update_sql = "UPDATE usuario SET 
        Nombre='$nombre', 
        Apellido='$apellido', 
        Grado='$grado', 
        Unidad='$unidad', 
        Telefono='$telefono', 
        Correo_electronico='$correo_electronico', 
        role='$role' 
        WHERE Cedula='$cedula'";

    if ($conn->query($update_sql) === TRUE) {
        header("Location: view_users.php?message=User+updated+successfully");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <h1>Edit User</h1>
    <form action="edit_user.php?cedula=<?php echo htmlspecialchars($cedula); ?>" method="post">
        <label for="nombre">First Name:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user['Nombre']); ?>" required>

        <label for="apellido">Last Name:</label>
        <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($user['Apellido']); ?>" required>

        <label for="grado">Rank:</label>
        <input type="text" id="grado" name="grado" value="<?php echo htmlspecialchars($user['Grado']); ?>" required>

        <label for="unidad">Unit:</label>
        <input type="text" id="unidad" name="unidad" value="<?php echo htmlspecialchars($user['Unidad']); ?>" required>

        <label for="telefono">Phone:</label>
        <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($user['Telefono']); ?>">

        <label for="correo_electronico">Email:</label>
        <input type="email" id="correo_electronico" name="correo_electronico" value="<?php echo htmlspecialchars($user['Correo_electronico']); ?>">

        <label for="role">Role:</label>
        <select id="role" name="role">
            <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
            <option value="super_user" <?php echo $user['role'] == 'super_user' ? 'selected' : ''; ?>>Super User</option>
            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
        </select>

        <button type="submit">Update User</button>
    </form>
    <a href="view_users.php">Back to Users</a>
</body>
</html>
