<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only admin or super_user can access this page
check_role(['admin', 'super_user']);

// SQL query to get assigned weapons with user information
$sql = "SELECT usuario.Cedula, usuario.Nombre, usuario.Apellido, usuario.Grado, usuario.Unidad, 
               arma.Serie, arma.Tipo_arma, arma.Modelo, asignacion.Fecha_asignacion, asignacion.Fecha_devolucion
        FROM asignacion
        JOIN usuario ON asignacion.Cedula = usuario.Cedula
        JOIN arma ON asignacion.Serie = arma.Serie
        ORDER BY asignacion.Fecha_asignacion DESC";

$result = $conn->query($sql);

// Statistics queries
$total_assignments = $conn->query("SELECT COUNT(*) as count FROM asignacion")->fetch_assoc()['count'];
$current_assignments = $conn->query("SELECT COUNT(*) as count FROM asignacion WHERE Fecha_devolucion IS NULL")->fetch_assoc()['count'];
$most_assigned_weapon = $conn->query("SELECT Serie, COUNT(*) as count FROM asignacion GROUP BY Serie ORDER BY count DESC LIMIT 1")->fetch_assoc();
$user_with_most_assignments = $conn->query("SELECT Cedula, COUNT(*) as count FROM asignacion GROUP BY Cedula ORDER BY count DESC LIMIT 1")->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Asignaciones de Armamento</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .table-container {
            max-height: 70vh;
            overflow-y: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            position: sticky;
            top: 0;
            background-color: #2c2c2c;
            color: #AE8A50;
            cursor: pointer;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #444;
        }
        tr:nth-child(even) {
            background-color: #1c1c1c;
        }
        tr:hover {
            background-color: #333;
        }
        .search-box, .stats-container {
            margin-bottom: 20px;
        }
        .stats-item {
            display: inline-block;
            margin-right: 20px;
            padding: 10px;
            background-color: #2c2c2c;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Ver Asignaciones de Armamento</h1>
        
        <?php if (isset($_GET['message'])): ?>
            <p class="message"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>

        <div class="stats-container">
            <div class="stats-item">Total Asignaciones: <?php echo $total_assignments; ?></div>
            <div class="stats-item">Asignaciones Actuales: <?php echo $current_assignments; ?></div>
            <div class="stats-item">Arma Más Asignada: <?php echo $most_assigned_weapon['Serie']; ?> (<?php echo $most_assigned_weapon['count']; ?> veces)</div>
            <div class="stats-item">Usuario con Más Asignaciones: <?php echo $user_with_most_assignments['Cedula']; ?> (<?php echo $user_with_most_assignments['count']; ?> veces)</div>
        </div>

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Buscar...">
        </div>

        <div class="table-container">
            <table id="assignmentTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">Cédula</th>
                        <th onclick="sortTable(1)">Nombre</th>
                        <th onclick="sortTable(2)">Apellido</th>
                        <th onclick="sortTable(3)">Grado</th>
                        <th onclick="sortTable(4)">Unidad</th>
                        <th onclick="sortTable(5)">Serie Armamento</th>
                        <th onclick="sortTable(6)">Tipo de Arma</th>
                        <th onclick="sortTable(7)">Modelo</th>
                        <th onclick="sortTable(8)">Fecha de Asignación</th>
                        <th onclick="sortTable(9)">Fecha de Devolución</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['Cedula']) . "</td>
                                    <td>" . htmlspecialchars($row['Nombre']) . "</td>
                                    <td>" . htmlspecialchars($row['Apellido']) . "</td>
                                    <td>" . htmlspecialchars($row['Grado']) . "</td>
                                    <td>" . htmlspecialchars($row['Unidad']) . "</td>
                                    <td>" . htmlspecialchars($row['Serie']) . "</td>
                                    <td>" . htmlspecialchars($row['Tipo_arma']) . "</td>
                                    <td>" . htmlspecialchars($row['Modelo']) . "</td>
                                    <td>" . htmlspecialchars($row['Fecha_asignacion']) . "</td>
                                    <td>" . (!empty($row['Fecha_devolucion']) ? htmlspecialchars($row['Fecha_devolucion']) : 'No Devuelto') . "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10'>No se encontraron asignaciones de armamento</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <a href="panel_admin.php" class="button">Volver al Panel de Administración</a>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("assignmentTable");
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td");
                for (var j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        } else {
                            tr[i].style.display = "none";
                        }
                    }
                }
            }
        });

        // Sorting functionality
        function sortTable(n) {
            var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            table = document.getElementById("assignmentTable");
            switching = true;
            dir = "asc";
            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    if (dir == "asc") {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else {
                    if (switchcount == 0 && dir == "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>