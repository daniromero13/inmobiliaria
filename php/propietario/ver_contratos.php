<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ../login.php');
    exit();
}
$nombreCompleto = isset($_SESSION['nombre_completo']) ? trim($_SESSION['nombre_completo']) : 'Usuario';
$inicial = strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8'));
include '../../config/db.php';

$propietario_id = $_SESSION['id_usuario'];

// Consultar contratos de las propiedades de este propietario (agregando el agente)
$stmt = $conn->prepare(
    "SELECT c.id, p.titulo AS propiedad, u.nombre_completo AS arrendatario, c.fecha_inicio, c.fecha_fin, c.monto, c.estado, c.pdf_contrato, a.nombre_completo AS agente
     FROM contratos c
     JOIN propiedades p ON c.propiedad_id = p.id
     JOIN usuarios u ON c.arrendatario_id = u.id
     JOIN usuarios a ON p.agente_id = a.id
     WHERE p.propietario_id = ?
     ORDER BY c.fecha_inicio DESC"
);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$result = $stmt->get_result();
$contratos = [];
while ($row = $result->fetch_assoc()) {
    $contratos[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Contratos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ef 100%);
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
        }
        .header-propietario {
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
        .header-propietario h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .header-propietario .nombre-usuario {
            font-size: 1.15rem;
            font-weight: 400;
            margin-bottom: 0.5rem;
        }
        .nav-propietario {
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
        .nav-propietario a {
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
        .nav-propietario a:hover, .nav-propietario a.active {
            background: #e6f0fa;
            color: #174ea6;
        }
        .main-card {
            max-width: 100%;
            margin: 0 auto 40px auto;
            border-radius: 18px;
            box-shadow: 0 6px 32px 0 rgba(60,72,88,0.12);
            background: #fff;
            border: none;
            padding: 2rem 2.5rem;
        }
        .table thead {
            background: #2563eb;
            color: #fff;
        }
        .table tbody tr:hover {
            background: #e6f0fa;
        }
        .btn-back {
            margin-bottom: 18px;
        }
        .btn-ver-pdf {
            min-width: 70px;
        }
        @media (max-width: 600px) {
            .main-card { padding: 1rem; }
            .nav-propietario { flex-direction: column; gap: 8px; }
        }
        .table-responsive {
            margin-top: 2rem;
            width: 100%;
        }
        .contratos-table-custom th, .contratos-table-custom td {
            padding: 12px 8px !important;
            font-size: 0.97rem;
            border: 2px solid #e0e7ef !important;
            background: #fff;
            white-space: nowrap !important;
        }
        .contratos-table-custom th {
            background: #e6f0fa !important;
            color: #174ea6 !important;
            font-weight: 700;
            text-align: center;
        }
        .contratos-table-custom td {
            background: #fff !important;
            color: #222;
            font-weight: 500;
            vertical-align: middle !important;
            word-break: break-word;
            white-space: normal;
            text-align: left;
        }
    </style>
</head>
<body>
    <header class="header-propietario text-center">
        <div class="avatar"><?php echo $inicial; ?></div>
        <h1>Bienvenido Propietario</h1>
        <div class="nombre-usuario">Hola, <?php echo htmlspecialchars($nombreCompleto); ?></div>
    </header>
    <nav class="nav-propietario mb-4">
        <a href="ver_propiedades.php"><i class="bi bi-house-door"></i>Propiedades</a>
        <a href="ver_contratos.php" class="active"><i class="bi bi-file-earmark-text"></i>Contratos</a>
        <a href="ver_pagos.php"><i class="bi bi-cash-stack"></i>Pagos</a>
        <a href="reportes_propietario.php"><i class="bi bi-bar-chart"></i>Reportes</a>
        <!-- <a href="perfil_propietario.php"><i class="bi bi-person"></i>Perfil</a> -->
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card">
            <a href="../vistas/propietario.php" class="btn btn-secondary btn-back mb-3"><i class="bi bi-arrow-left"></i> Volver</a>
            <h2 class="mb-4">Mis Contratos</h2>
            <!-- Filtros y buscador -->
            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <select id="filtroEstado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="Firmado">Firmado</option>
                        <option value="Cancelado">Cancelado</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <input type="month" id="filtroMesContrato" class="form-control" placeholder="Filtrar por mes de inicio">
                </div>
                <div class="col-md-4 mb-2">
                    <input type="text" id="buscadorContratos" class="form-control" placeholder="Buscar...">
                </div>
            </div>
            <div class="table-responsive">
                <table id="tablaContratos" class="table table-bordered align-middle mb-0 contratos-table-custom" style="width:100%;">
                    <thead>
                        <tr>
                            <th class="text-center align-middle" style="width:6%;">ID</th>
                            <th class="text-center align-middle" style="width:18%;">Propiedad</th>
                            <th class="text-center align-middle" style="width:15%;">Arrendatario</th>
                            <th class="text-center align-middle" style="width:15%;">Agente</th>
                            <th class="text-center align-middle" style="width:10%;">Fecha Inicio</th>
                            <th class="text-center align-middle" style="width:10%;">Fecha Fin</th>
                            <th class="text-center align-middle" style="width:10%;">Monto</th>
                            <th class="text-center align-middle" style="width:10%;">Dinero Al Agente</th>
                            <th class="text-center align-middle" style="width:8%;">Estado</th>
                            <th class="text-center align-middle" style="width:8%;">PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contratos as $contrato): ?>
                        <tr>
                            <td class="text-center align-middle"><?= htmlspecialchars($contrato['id']) ?></td>
                            <td class="align-middle"><?= htmlspecialchars($contrato['propiedad']) ?></td>
                            <td class="align-middle"><?= htmlspecialchars($contrato['arrendatario']) ?></td>
                            <td class="align-middle"><?= htmlspecialchars($contrato['agente']) ?></td>
                            <td class="text-center align-middle"><?= htmlspecialchars($contrato['fecha_inicio']) ?></td>
                            <td class="text-center align-middle"><?= htmlspecialchars($contrato['fecha_fin']) ?></td>
                            <td class="text-end align-middle" style="color:#2563eb; font-weight:600; white-space:nowrap;">
                                $<?= number_format($contrato['monto'], 2) ?>
                            </td>
                            <td class="text-end align-middle" style="color:#ef4444; font-weight:600; white-space:nowrap;">
                                <?php
                                    // Calcular meses de duración del contrato
                                    $fecha_inicio = new DateTime($contrato['fecha_inicio']);
                                    $fecha_fin = new DateTime($contrato['fecha_fin']);
                                    $interval = $fecha_inicio->diff($fecha_fin);
                                    $meses = ($interval->y * 12) + $interval->m + ($interval->d > 0 ? 1 : 0);
                                    $meses = $meses > 0 ? $meses : 1; // Evitar división por cero
                                    $mes_arriendo = $contrato['monto'] / $meses;
                                ?>
                                $<?= number_format($mes_arriendo, 2) ?>
                            </td>
                            <td class="text-center align-middle" style="white-space:nowrap;"><?= htmlspecialchars($contrato['estado']) ?></td>
                            <td class="text-center align-middle">
                                <?php if (!empty($contrato['pdf_contrato']) && file_exists(__DIR__ . '/../../' . $contrato['pdf_contrato'])): ?>
                                    <a href="../../<?= htmlspecialchars($contrato['pdf_contrato']) ?>" target="_blank" class="btn btn-info btn-sm btn-ver-pdf">Ver PDF</a>
                                <?php else: ?>
                                    <span class="text-muted">No disponible</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($contratos)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">No tienes contratos registrados.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filtroEstado = document.getElementById('filtroEstado');
        const filtroMes = document.getElementById('filtroMesContrato');
        const buscador = document.getElementById('buscadorContratos');
        const tabla = document.getElementById('tablaContratos').getElementsByTagName('tbody')[0];

        function filtrarTabla() {
            const estado = filtroEstado.value;
            const mes = filtroMes.value;
            const texto = buscador.value.toLowerCase();

            for (let row of tabla.rows) {
                let mostrar = true;

                // Filtro por estado
                if (estado && row.cells[6].textContent.indexOf(estado) === -1) {
                    mostrar = false;
                }
                // Filtro por mes de inicio
                if (mes && !row.cells[3].textContent.startsWith(mes)) {
                    mostrar = false;
                }
                // Buscador general
                if (texto) {
                    let rowText = row.textContent.toLowerCase();
                    if (!rowText.includes(texto)) {
                        mostrar = false;
                    }
                }
                row.style.display = mostrar ? '' : 'none';
            }
        }

        filtroEstado.addEventListener('change', filtrarTabla);
        filtroMes.addEventListener('input', filtrarTabla);
        buscador.addEventListener('input', filtrarTabla);
    });
    </script>
</body>
</html>
