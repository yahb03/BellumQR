<?php
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
$qr_filename = isset($_GET['qr']) ? htmlspecialchars($_GET['qr']) : '';
$type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Success</h1>
        <?php if (!empty($message)) : ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
        
        <?php if (!empty($qr_filename)) : ?>
            <div class="qr-code">
                <img src="../qrcodes/<?php echo $qr_filename; ?>" alt="QR Code">
            </div>
        <?php endif; ?>
        
        <a href="index.php" class="button">Go Back</a>
    </div>
</body>
</html>