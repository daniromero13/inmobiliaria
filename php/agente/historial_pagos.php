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

// Obtener historial de pagos del agente (solo de sus contratos)
$stmtPagos = $conn->prepare(
    "SELECT pagos.id, pagos.contrato_id, pagos.monto, pagos.fecha_pago, p.titulo AS propiedad, u.nombre_completo AS arrendatario
     FROM pagos
     JOIN contratos c ON pagos.contrato_id = c.id
     JOIN propiedades p ON c.propiedad_id = p.id
     JOIN usuarios u ON c.arrendatario_id = u.id
     WHERE p.agente_id = ?
     ORDER BY pagos.fecha_pago DESC"
);
$stmtPagos->bind_param("i", $agente_id);
$stmtPagos->execute();
$pagos = $stmtPagos->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Pagos</title>
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
            max-width: 1100px;
            margin: 0 auto 40px auto;
            border-radius: 18px;
            box-shadow: 0 6px 32px 0 rgba(60,72,88,0.12);
            background: #fff;
            border: none;
            padding: 2rem 2.5rem;
        }
        .table-responsive { margin-top: 2rem; }
        .pagos-table-custom th, .pagos-table-custom td {
            padding: 12px 8px !important;
            font-size: 0.97rem;
            border: 2px solid #e0e7ef !important;
            background: #fff;
        }
        .pagos-table-custom th {
            background: #e6f0fa !important;
            color: #174ea6 !important;
            font-weight: 700;
        }
        .pagos-table-custom td {
            background: #fff !important;
            color: #222;
            font-weight: 500;
            vertical-align: middle !important;
            word-break: break-word;
            white-space: normal;
        }
        @media (max-width: 1200px) {
            .main-card { max-width: 100%; padding: 1rem; }
        }
        @media (max-width: 600px) {
            .main-card { padding: 0.5rem; }
            .nav-agente { flex-direction: column; gap: 8px; }
        }
    </style>
    <style>
        .pagos-table-custom th, .pagos-table-custom td {
            padding: 8px 6px !important; /* Menos padding */
            font-size: 0.95rem;          /* Más compacto */
            border: 2px solid #e0e7ef !important;
            background: #fff;
            vertical-align: middle !important;
            text-align: left;
        }
        .pagos-table-custom th {
            background: #e6f0fa !important;
            color: #174ea6 !important;
            font-weight: 700;
            text-align: center;
        }
        .pagos-table-custom td {
            background: #fff !important;
            color: #222;
            font-weight: 500;
            word-break: break-word;
            white-space: normal;
        }
    </style>
</head>
<body>
    <header class="header-agente text-center position-relative">
        <div class="avatar"><?php echo $inicial; ?></div>
        <h1>Bienvenido Agente</h1>
        <div class="nombre-usuario">Hola, <?php echo htmlspecialchars($nombreCompleto); ?></div>
    </header>
    <nav class="nav-agente mb-4">
        <a href="propiedades.php"><i class="bi bi-house-door"></i>Mis Propiedades</a>
        <a href="crear_contrato.php"><i class="bi bi-file-earmark-plus"></i>Crear Contrato</a>
        <a href="registrar_pago.php"><i class="bi bi-cash-stack"></i>Registrar Pago</a>
        <a href="historial_pagos.php" class="active"><i class="bi bi-receipt"></i>Historial de Pagos</a>
        <a href="historial_contratos.php"><i class="bi bi-clock-history"></i>Historial Contratos</a>
        <a href="reportes_agente.php"><i class="bi bi-bar-chart"></i>Reportes</a>
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card shadow pagos-historial-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="../vistas/agente.php" class="btn btn-secondary btn-back">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <h2 class="mb-0 text-center flex-grow-1" style="font-size:1.7rem;">Historial de Pagos</h2>
                <span style="width: 90px;"></span>
            </div>
            <!-- Filtros y buscador -->
            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <select id="filtroContrato" class="form-select">
                        <option value="">Todos los contratos</option>
                        <?php
                        // Obtener todos los contratos para el filtro
                        $stmtContratosFiltro = $conn->prepare(
                            "SELECT c.id, p.titulo FROM contratos c
                             JOIN propiedades p ON c.propiedad_id = p.id
                             WHERE p.agente_id = ?"
                        );
                        $stmtContratosFiltro->bind_param("i", $agente_id);
                        $stmtContratosFiltro->execute();
                        $resContratosFiltro = $stmtContratosFiltro->get_result();
                        while ($contratoFiltro = $resContratosFiltro->fetch_assoc()):
                        ?>
                            <option value="<?= $contratoFiltro['id'] ?>">#<?= $contratoFiltro['id'] ?> - <?= htmlspecialchars($contratoFiltro['titulo']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <input type="month" id="filtroMes" class="form-control" placeholder="Filtrar por mes pagado">
                </div>
                <div class="col-md-4 mb-2">
                    <input type="text" id="buscadorPagos" class="form-control" placeholder="Buscar...">
                </div>
            </div>
            <div class="table-responsive" style="overflow-x:auto; max-width:100%;">
                <table id="tablaPagos" class="table table-bordered align-middle mb-0 pagos-table-custom" style="min-width:900px; width:100%;">
                    <thead>
                        <tr>
                            <th class="text-center align-middle" style="width:8%;">ID Pago</th>
                            <th class="text-start align-middle" style="width:10%;">Contrato</th>
                            <th class="text-start align-middle" style="width:22%;">Propiedad</th>
                            <th class="text-start align-middle" style="width:18%;">Arrendatario</th>
                            <th class="text-start align-middle" style="width:14%;">Monto del Contrato</th>
                            <th class="text-start align-middle" style="width:14%;">Dinero recibido</th>
                            <th class="text-start align-middle" style="width:18%;">Fecha del Mes Pagado</th>
                            <th class="text-start align-middle" style="width:18%;">Fecha de Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pagos && $pagos->num_rows > 0): ?>
                            <?php
                            $pagos->data_seek(0);
                            while ($pago = $pagos->fetch_assoc()):
                                $created_at = null;
                                $stmtCreated = $conn->prepare("SELECT created_at FROM pagos WHERE id = ?");
                                $stmtCreated->bind_param("i", $pago['id']);
                                $stmtCreated->execute();
                                $stmtCreated->bind_result($created_at);
                                $stmtCreated->fetch();
                                $stmtCreated->close();
                            ?>
                                <tr>
                                    <td class="text-center align-middle"><?= $pago['id'] ?></td>
                                    <td class="text-start align-middle">#<?= $pago['contrato_id'] ?></td>
                                    <td class="text-start align-middle" style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($pago['propiedad']) ?></td>
                                    <td class="text-start align-middle" style="max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($pago['arrendatario']) ?></td>
                                    <td class="text-start align-middle" style="color:#2563eb; font-weight:600;">
                                        $<?= number_format($pago['monto'], 2, '.', ',') ?>
                                    </td>
                                    <td class="text-start align-middle" style="color:#10b981; font-weight:600;">
                                        $<?= number_format($pago['monto'] * 0.10, 2, '.', ',') ?>
                                    </td>
                                    <td class="text-start align-middle" style="white-space:nowrap;"><?= htmlspecialchars($pago['fecha_pago']) ?></td>
                                    <td class="text-start align-middle" style="white-space:nowrap;"><?= htmlspecialchars($created_at) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No hay pagos registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <style>
                .pagos-table-custom th, .pagos-table-custom td {
                    padding: 12px 10px !important;
                    font-size: 1rem;
                    border: 2px solid #e0e7ef !important;
                    background: #fff;
                    vertical-align: middle !important;
                    text-align: left;
                }
                .pagos-table-custom th {
                    background: #e6f0fa !important;
                    color: #174ea6 !important;
                    font-weight: 700;
                    text-align: center;
                    white-space: nowrap !important;
                }
                .pagos-table-custom td:nth-child(3),
                .pagos-table-custom td:nth-child(4) {
                    white-space: nowrap !important;
                    overflow: hidden !important;
                    text-overflow: ellipsis !important;
                    max-width: 220px;
                }
                .main-card {
                    overflow-x: auto;
                }
            </style>
        </div>
    </div>
    <style>
        .pagos-table-custom th, .pagos-table-custom td {
            padding: 14px 12px !important;
            font-size: 1rem;
            border: 2px solid #e0e7ef !important;
            background: #fff;
            vertical-align: middle !important;
            text-align: left;
        }
        .pagos-table-custom th {
            background: #e6f0fa !important;
            color: #174ea6 !important;
            font-weight: 700;
            text-align: center;
        }
        .pagos-table-custom td {
            background: #fff !important;
            color: #222;
            font-weight: 500;
            word-break: break-word;
            white-space: normal;
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filtroContrato = document.getElementById('filtroContrato');
        const filtroMes = document.getElementById('filtroMes');
        const buscador = document.getElementById('buscadorPagos');
        const tabla = document.getElementById('tablaPagos').getElementsByTagName('tbody')[0];

        function filtrarTabla() {
            const contrato = filtroContrato.value;
            const mes = filtroMes.value;
            const texto = buscador.value.toLowerCase();

            for (let row of tabla.rows) {
                let mostrar = true;

                // Filtro por contrato
                if (contrato && !row.cells[1].textContent.includes('#' + contrato)) {
                    mostrar = false;
                }
                // Filtro por mes pagado
                if (mes && !row.cells[5].textContent.startsWith(mes)) {
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

        filtroContrato.addEventListener('change', filtrarTabla);
        filtroMes.addEventListener('input', filtrarTabla);
        buscador.addEventListener('input', filtrarTabla);
    });
    </script>
</body>
</html>
