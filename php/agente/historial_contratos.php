<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 4) {
    header('Location: ../login.php');
    exit();
}
$nombreCompleto = isset($_SESSION['nombre_completo']) ? trim($_SESSION['nombre_completo']) : 'Agente';
$inicial = strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8'));
include '../../config/db.php';

$agente_id = $_SESSION['id_usuario'];

// Eliminar contrato si se recibe el parámetro 'eliminar'
if (isset($_GET['eliminar'])) {
    $idEliminar = intval($_GET['eliminar']);
    $stmtDel = $conn->prepare("DELETE FROM contratos WHERE id = ? AND propiedad_id IN (SELECT id FROM propiedades WHERE agente_id = ?)");
    $stmtDel->bind_param("ii", $idEliminar, $agente_id);
    $stmtDel->execute();
    header("Location: historial_contratos.php");
    exit();
}

$query = "SELECT c.id, p.titulo AS propiedad, u.nombre_completo AS arrendatario, c.fecha_inicio, c.fecha_fin, c.monto 
          FROM contratos c
          JOIN propiedades p ON c.propiedad_id = p.id
          JOIN usuarios u ON c.arrendatario_id = u.id
          WHERE p.agente_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $agente_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Contratos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ef 100%);
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
        }
        .header-agente {
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
        .header-agente h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .header-agente .nombre-usuario {
            font-size: 1.15rem;
            font-weight: 400;
            margin-bottom: 0.5rem;
        }
        .nav-agente {
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
        .nav-agente a {
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
        .nav-agente a:hover, .nav-agente a.active {
            background: #e6f0fa;
            color: #174ea6;
        }
        .main-card {
            max-width: 950px;
            margin: 0 auto 40px auto;
            border-radius: 18px;
            box-shadow: 0 6px 32px 0 rgba(60,72,88,0.12);
            background: #fff;
            border: none;
            padding: 2rem 2.5rem;
        }
        @media (max-width: 600px) {
            .main-card { padding: 1rem; }
            .nav-agente { flex-direction: column; gap: 8px; }
        }
    </style>
</head>
<body>
    <header class="header-agente text-center">
        <div class="avatar"><?php echo $inicial; ?></div>
        <h1>Bienvenido Agente</h1>
        <div class="nombre-usuario">Hola, <?php echo htmlspecialchars($nombreCompleto); ?></div>
    </header>
    <nav class="nav-agente mb-4">
        <a href="propiedades.php"><i class="bi bi-house-door"></i>Mis Propiedades</a>
        <a href="crear_contrato.php"><i class="bi bi-file-earmark-plus"></i>Crear Contrato</a>
        <a href="registrar_pago.php"><i class="bi bi-cash-stack"></i>Registrar Pago</a>
        <a href="historial_contratos.php" class="active"><i class="bi bi-clock-history"></i>Historial Contratos</a>
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card shadow">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="propiedades.php" class="btn btn-secondary btn-back">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <h2 class="mb-0 text-center flex-grow-1" style="font-size:1.7rem;">Historial de Contratos</h2>
                <span style="width: 90px;"></span>
            </div>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Propiedad</th>
                                <th>Arrendatario</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Monto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($contrato = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($contrato['id']) ?></td>
                                    <td><?= htmlspecialchars($contrato['propiedad']) ?></td>
                                    <td><?= htmlspecialchars($contrato['arrendatario']) ?></td>
                                    <td><?= htmlspecialchars($contrato['fecha_inicio']) ?></td>
                                    <td><?= htmlspecialchars($contrato['fecha_fin']) ?></td>
                                    <td>$<?= number_format($contrato['monto'], 2) ?></td>
                                    <td>
                                        <a href="historial_contratos.php?eliminar=<?= $contrato['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este contrato?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No hay contratos registrados.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
