<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ../login.php');
    exit();
}
?>
<!-- Resto del contenido para propietario -->

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Propietario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow">
        <div class="card-body text-center">
            <h1 class="card-title">Bienvenido Propietario</h1>
            <p class="card-text">Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>.</p>
            <a href="../../php/logout.php" class="btn btn-danger">Cerrar sesión</a>
        </div>
    </div>
</div>

</body>
</html>
