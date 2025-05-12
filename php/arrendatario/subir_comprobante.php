<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 3) {
    header('Location: ../login.php');
    exit();
}
$nombreCompleto = isset($_SESSION['nombre_completo']) ? trim($_SESSION['nombre_completo']) : 'Arrendatario';
$inicial = strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8'));

include '../../config/db.php';
$id_usuario = $_SESSION['id_usuario'];

// Obtener contratos vigentes del arrendatario
$stmtContratos = $conn->prepare(
    "SELECT c.id, p.titulo, c.fecha_inicio, c.fecha_fin
     FROM contratos c
     JOIN propiedades p ON c.propiedad_id = p.id
     WHERE c.arrendatario_id = ?"
);
$stmtContratos->bind_param("i", $id_usuario);
$stmtContratos->execute();
$resContratos = $stmtContratos->get_result();
$contratos = [];
while ($row = $resContratos->fetch_assoc()) $contratos[] = $row;

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contrato_id = intval($_POST['contrato_id']);
    if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
        $mensaje = '<div class="alert alert-danger">Debe seleccionar un archivo de comprobante válido.</div>';
    } else {
        $archivo = $_FILES['comprobante'];
        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($ext, $permitidos)) {
            $mensaje = '<div class="alert alert-danger">Formato de archivo no permitido. Solo JPG, PNG o PDF.</div>';
        } else {
            $nombreArchivo = uniqid('comp_') . '.' . $ext;
            $rutaDestino = realpath(__DIR__ . '/../../uploads');
            if ($rutaDestino === false) {
                mkdir(__DIR__ . '/../../uploads', 0777, true);
                $rutaDestino = realpath(__DIR__ . '/../../uploads');
            }
            $rutaCompleta = $rutaDestino . DIRECTORY_SEPARATOR . $nombreArchivo;
            if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                $stmt = $conn->prepare("INSERT INTO comprobantes_pagos (contrato_id, archivo, fecha_envio, estado) VALUES (?, ?, NOW(), 'Pendiente')");
                $stmt->bind_param("is", $contrato_id, $nombreArchivo);
                if ($stmt->execute()) {
                    $mensaje = '<div class="alert alert-success">Comprobante enviado correctamente. Quedará pendiente de revisión.</div>';
                } else {
                    $mensaje = '<div class="alert alert-danger">Error al guardar el comprobante.</div>';
                }
            } else {
                $mensaje = '<div class="alert alert-danger">Error al subir el archivo.</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir Comprobante de Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ef 100%);
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
        }
        .header-arrendatario {
            background: #2563eb;
            color: #fff;
            border-radius: 0 0 24px 24px;
            padding: 32px 0 24px 0;
            box-shadow: 0 4px 24px 0 rgba(37,99,235,0.08);
            margin-bottom: 0;
        }
        .avatar {
            width: 64px;
            height: 64px;
            background: #fff;
            color: #2563eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            font-weight: bold;
            margin: 0 auto 12px auto;
            box-shadow: 0 2px 8px 0 rgba(37,99,235,0.10);
        }
        .header-arrendatario h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .header-arrendatario .nombre-usuario {
            font-size: 1.15rem;
            font-weight: 400;
            margin-bottom: 0.5rem;
        }
        .nav-arrendatario {
            background: #fff;
            border-radius: 0 0 18px 18px;
            box-shadow: 0 2px 12px 0 rgba(60,72,88,0.10);
            margin-top: 0;
            margin-bottom: 32px;
            padding: 0.5rem 1rem;
            display: flex;
            justify-content: center;
            gap: 24px;
        }
        .nav-arrendatario a {
            color: #2563eb;
            font-weight: 500;
            text-decoration: none;
            padding: 8px 18px;
            border-radius: 8px;
            transition: background 0.18s, color 0.18s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-arrendatario a:hover, .nav-arrendatario a.active {
            background: #e6f0fa;
            color: #174ea6;
        }
        .main-card {
            max-width: 500px;
            margin: 0 auto 40px auto;
            border-radius: 18px;
            box-shadow: 0 6px 32px 0 rgba(60,72,88,0.12);
            background: #fff;
            border: none;
            padding: 2rem 2.5rem;
        }
        @media (max-width: 600px) {
            .main-card { padding: 1rem; }
            .nav-arrendatario { flex-direction: column; gap: 8px; }
        }
    </style>
</head>
<body>
    <header class="header-arrendatario text-center">
        <div class="avatar"><?php echo $inicial; ?></div>
        <h1>Bienvenido Arrendatario</h1>
        <div class="nombre-usuario">Hola, <?php echo htmlspecialchars($nombreCompleto); ?></div>
    </header>
    <nav class="nav-arrendatario mb-4">
        <a href="mis_propiedades.php"><i class="bi bi-house-door"></i>Propiedades Arrendadas</a>
        <a href="mis_contratos.php"><i class="bi bi-file-earmark-text"></i>Mis Contratos</a>
        <a href="mis_pagos.php"><i class="bi bi-clock-history"></i>Historial de Pagos</a>
        <a href="subir_comprobante.php" class="active"><i class="bi bi-upload"></i>Subir Comprobante</a>
        <a href="reportes_arrendatario.php"><i class="bi bi-bar-chart"></i>Reportes</a>
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card shadow" style="max-width:950px;padding:2rem 2.5rem;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="../vistas/arrendatario.php" class="btn btn-secondary btn-back px-4 py-2" style="font-size:1rem;font-weight:500;display:inline-flex;align-items:center;justify-content:center;min-width:120px;max-width:180px;">
                    <i class="bi bi-arrow-left me-2" style="font-size:1.1rem;"></i> Volver
                </a>
                <h2 class="mb-0 text-center flex-grow-1" style="font-size:1.7rem;font-weight:700;">Subir Comprobante de Pago</h2>
                <span style="width: 120px;"></span>
            </div>
            <?php if ($mensaje) echo $mensaje; ?>
            <form method="post" enctype="multipart/form-data" class="mb-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contrato_id" class="form-label">Contrato</label>
                        <select name="contrato_id" id="contrato_id" class="form-select" required>
                            <option value="">Seleccione un contrato</option>
                            <?php foreach ($contratos as $c): ?>
                                <option value="<?= $c['id'] ?>">
                                    #<?= $c['id'] ?> - <?= htmlspecialchars($c['titulo']) ?> (<?= htmlspecialchars($c['fecha_inicio']) ?> a <?= htmlspecialchars($c['fecha_fin']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="comprobante" class="form-label">Comprobante de pago (imagen o PDF)</label>
                        <input type="file" name="comprobante" id="comprobante" class="form-control" accept="image/*,application/pdf" required>
                        <small class="text-muted">Formatos permitidos: JPG, PNG, PDF.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-3 d-grid">
                        <button type="submit" class="btn btn-primary w-100" style="font-size:1rem;font-weight:500;height:48px;">Enviar Comprobante</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
