<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: ../login.php');
    exit();
}
$nombreCompleto = isset($_SESSION['nombre_completo']) ? trim($_SESSION['nombre_completo']) : 'Administrador';
$inicial = strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8'));
include '../../config/db.php';

// Obtener todos los pagos con información relevante
$query = "SELECT p.id, p.contrato_id, p.monto, p.fecha_pago, 
                 c.fecha_inicio, c.fecha_fin, c.estado AS estado_contrato, 
                 u.nombre_completo AS arrendatario, 
                 pr.titulo AS propiedad, pr.ciudad, pr.ubicacion
          FROM pagos p
          JOIN contratos c ON p.contrato_id = c.id
          JOIN usuarios u ON c.arrendatario_id = u.id
          JOIN propiedades pr ON c.propiedad_id = pr.id
          ORDER BY p.fecha_pago DESC";
$res = $conn->query($query);
$pagos = [];
while ($row = $res->fetch_assoc()) {
    $pagos[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagos - Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ef 100%);
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
        }
        .header-admin {
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
        .header-admin h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .header-admin .nombre-usuario {
            font-size: 1.15rem;
            font-weight: 400;
            margin-bottom: 0.5rem;
        }
        .nav-admin {
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
        .nav-admin a {
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
        .nav-admin a:hover, .nav-admin a.active {
            background: #e6f0fa;
            color: #174ea6;
        }
        .main-card {
            max-width: 1200px;
            margin: 0 auto 40px auto;
            border-radius: 18px;
            box-shadow: 0 6px 32px 0 rgba(60,72,88,0.12);
            background: #fff;
            border: none;
            padding: 2rem 2.5rem;
        }
        @media (max-width: 600px) {
            .main-card { padding: 1rem; }
            .nav-admin { flex-direction: column; gap: 8px; }
        }
    </style>
</head>
<body>
    <header class="header-admin text-center position-relative">
        <div class="avatar"><?php echo $inicial; ?></div>
        <h1>Administrador</h1>
        <div class="nombre-usuario">Hola, <?php echo htmlspecialchars($nombreCompleto); ?></div>
    </header>
    <nav class="nav-admin mb-4">
        <a href="usuarios.php"><i class="bi bi-people"></i>Usuarios</a>
        <a href="propiedades.php"><i class="bi bi-house-door"></i>Propiedades</a>
        <a href="contratos.php"><i class="bi bi-file-earmark-text"></i>Contratos</a>
        <a href="pagos.php" class="active"><i class="bi bi-cash-stack"></i>Pagos</a>
        <a href="reportes_admin.php"><i class="bi bi-bar-chart"></i>Reportes</a>
        <a href="../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card shadow">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="../vistas/admin.php" class="btn btn-secondary btn-back">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <h2 class="mb-0 text-center flex-grow-1" style="font-size:1.7rem;">Todas las Transacciones de Pago</h2>
                <span style="width: 90px;"></span>
            </div>
            <div class="mb-3">
                <input type="text" id="buscadorPagos" class="form-control" placeholder="Buscar por propiedad, arrendatario, ciudad, contrato...">
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>ID Pago</th>
                            <th>Contrato</th>
                            <th>Propiedad</th>
                            <th>Arrendatario</th>
                            <th>Ciudad</th>
                            <th>Dirección</th>
                            <th>Monto</th>
                            <th>Fecha de Pago</th>
                            <th>Estado Contrato</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPagos">
                        <?php foreach ($pagos as $pago): ?>
                        <tr>
                            <td><?= htmlspecialchars($pago['id']) ?></td>
                            <td>#<?= htmlspecialchars($pago['contrato_id']) ?></td>
                            <td><?= htmlspecialchars($pago['propiedad']) ?></td>
                            <td><?= htmlspecialchars($pago['arrendatario']) ?></td>
                            <td><?= htmlspecialchars($pago['ciudad']) ?></td>
                            <td><?= htmlspecialchars($pago['ubicacion']) ?></td>
                            <td>$<?= number_format($pago['monto'], 2) ?></td>
                            <td><?= htmlspecialchars($pago['fecha_pago']) ?></td>
                            <td>
                                <?php
                                    $estado = $pago['estado_contrato'];
                                    if ($estado == 'Vigente') {
                                        echo '<span class="badge bg-success">Vigente</span>';
                                    } elseif ($estado == 'Firmado') {
                                        echo '<span class="badge bg-success">Firmado</span>';
                                    } elseif ($estado == 'Cancelado') {
                                        echo '<span class="badge bg-secondary">Cancelado</span>';
                                    } elseif ($estado == 'Cancelación anticipada') {
                                        echo '<span class="badge bg-warning text-dark">Cancelación anticipada</span>';
                                    } else {
                                        echo '<span class="badge bg-light text-dark">' . htmlspecialchars($estado) . '</span>';
                                    }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pagos)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">No hay pagos registrados.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('buscadorPagos').addEventListener('input', function() {
        const texto = this.value.toLowerCase();
        const filas = document.querySelectorAll('#tablaPagos tr');
        filas.forEach(function(fila) {
            const contenido = fila.textContent.toLowerCase();
            fila.style.display = contenido.includes(texto) ? '' : 'none';
        });
    });
    </script>
</body>
</html>
