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

// Total propiedades
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM propiedades WHERE propietario_id = ?");
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$stmt->bind_result($total_propiedades);
$stmt->fetch();
$stmt->close();

// Total contratos
$stmt = $conn->prepare(
    "SELECT COUNT(*) as total FROM contratos c
     JOIN propiedades p ON c.propiedad_id = p.id
     WHERE p.propietario_id = ?"
);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$stmt->bind_result($total_contratos);
$stmt->fetch();
$stmt->close();

// Total pagos recibidos (sin comisiones)
$stmt = $conn->prepare(
    "SELECT SUM(pagos.monto) as total FROM pagos
     JOIN contratos c ON pagos.contrato_id = c.id
     JOIN propiedades p ON c.propiedad_id = p.id
     WHERE p.propietario_id = ?"
);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$stmt->bind_result($total_pagos);
$stmt->fetch();
$stmt->close();
$total_pagos = $total_pagos ?: 0;

// Calcular comisiones de agentes
// Solo comisión mensual: 10% de cada pago
$stmt = $conn->prepare(
    "SELECT pagos.monto FROM pagos
     JOIN contratos c ON pagos.contrato_id = c.id
     JOIN propiedades p ON c.propiedad_id = p.id
     WHERE p.propietario_id = ?"
);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$resPagos = $stmt->get_result();
$comision_mensual = 0;
while ($row = $resPagos->fetch_assoc()) {
    $comision_mensual += $row['monto'] * 0.10;
}
$stmt->close();

$total_comisiones_agente = $comision_mensual;
$total_recibido_propietario = $total_pagos - $total_comisiones_agente;
if ($total_recibido_propietario < 0) $total_recibido_propietario = 0;

// Pagos por mes (últimos 12 meses)
$stmt = $conn->prepare(
    "SELECT DATE_FORMAT(pagos.fecha_pago, '%Y-%m') as mes, SUM(pagos.monto) as total_mes
     FROM pagos
     JOIN contratos c ON pagos.contrato_id = c.id
     JOIN propiedades p ON c.propiedad_id = p.id
     WHERE p.propietario_id = ?
     GROUP BY mes
     ORDER BY mes DESC
     LIMIT 12"
);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$resPagosMes = $stmt->get_result();
$pagos_mes = [];
$comision_mensual_mes = [];
while ($row = $resPagosMes->fetch_assoc()) {
    $pagos_mes[] = $row;
    $comision_mensual_mes[$row['mes']] = floatval($row['total_mes']) * 0.10;
}
$stmt->close();

// Ejemplo de otros reportes útiles para propietarios:

// 1. Propiedades sin contrato vigente
$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM propiedades p
     LEFT JOIN contratos c ON p.id = c.propiedad_id AND c.estado = 'Vigente'
     WHERE p.propietario_id = ? AND c.id IS NULL"
);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$stmt->bind_result($propiedades_sin_contrato);
$stmt->fetch();
$stmt->close();

// 2. Contratos vencidos
$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM contratos c
     JOIN propiedades p ON c.propiedad_id = p.id
     WHERE p.propietario_id = ? AND c.estado = 'Cancelado'"
);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$stmt->bind_result($contratos_vencidos);
$stmt->fetch();
$stmt->close();

// 3. Arrendatarios únicos
$stmt = $conn->prepare(
    "SELECT COUNT(DISTINCT c.arrendatario_id) FROM contratos c
     JOIN propiedades p ON c.propiedad_id = p.id
     WHERE p.propietario_id = ?"
);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$stmt->bind_result($arrendatarios_unicos);
$stmt->fetch();
$stmt->close();

// 4. Agentes trabajados (número de agentes distintos con los que el propietario ha tenido contratos)
$stmt = $conn->prepare(
    "SELECT COUNT(DISTINCT p.agente_id) FROM contratos c
     JOIN propiedades p ON c.propiedad_id = p.id
     WHERE p.propietario_id = ?"
);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$stmt->bind_result($agentes_trabajados);
$stmt->fetch();
$stmt->close();

// 5. Propiedad con más contratos
$stmt = $conn->prepare(
    "SELECT p.titulo, COUNT(c.id) as total FROM contratos c
     JOIN propiedades p ON c.propiedad_id = p.id
     WHERE p.propietario_id = ?
     GROUP BY p.id
     ORDER BY total DESC
     LIMIT 1"
);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$stmt->bind_result($prop_mas_contratos, $prop_mas_contratos_total);
$stmt->fetch();
$stmt->close();
$prop_mas_contratos = $prop_mas_contratos ?: 'N/A';
$prop_mas_contratos_total = $prop_mas_contratos_total ?: 0;

// 6. Ciudad con más propiedades
$stmt = $conn->prepare(
    "SELECT ciudad, COUNT(*) as total FROM propiedades
     WHERE propietario_id = ?
     GROUP BY ciudad
     ORDER BY total DESC
     LIMIT 1"
);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$stmt->bind_result($ciudad_top, $ciudad_top_total);
$stmt->fetch();
$stmt->close();
$ciudad_top = $ciudad_top ?: 'N/A';
$ciudad_top_total = $ciudad_top_total ?: 0;

// Nueva gráfica: Distribución de propiedades por ciudad (top 5)
$stmt = $conn->prepare(
    "SELECT ciudad, COUNT(*) as total FROM propiedades
     WHERE propietario_id = ?
     GROUP BY ciudad
     ORDER BY total DESC
     LIMIT 5"
);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$resCiudades = $stmt->get_result();
$ciudades_labels = [];
$ciudades_data = [];
while ($row = $resCiudades->fetch_assoc()) {
    $ciudades_labels[] = $row['ciudad'] ?: 'Sin ciudad';
    $ciudades_data[] = (int)$row['total'];
}
$stmt->close();

// Nueva gráfica: Distribución de contratos por estado (Vigente, Cancelado, etc.)
$stmt = $conn->prepare(
    "SELECT c.estado, COUNT(*) as total FROM contratos c
     JOIN propiedades p ON c.propiedad_id = p.id
     WHERE p.propietario_id = ?
     GROUP BY c.estado"
);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$resEstados = $stmt->get_result();
$estados_labels = [];
$estados_data = [];
while ($row = $resEstados->fetch_assoc()) {
    $estados_labels[] = $row['estado'] ?: 'Sin estado';
    $estados_data[] = (int)$row['total'];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes de Propietario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            max-width: 1100px;
            margin: 0 auto 40px auto;
            border-radius: 18px;
            box-shadow: 0 6px 32px 0 rgba(60,72,88,0.12);
            background: #fff;
            border: none;
            padding: 2rem 2.5rem;
        }
        .report-card {
            border-radius: 14px;
            background: #f3f4f6;
            box-shadow: 0 2px 8px 0 rgba(60,72,88,0.08);
            padding: 1.5rem 1.2rem;
            margin-bottom: 24px;
            text-align: center;
        }
        .report-card h4 {
            font-size: 1.1rem;
            color: #6366f1;
            margin-bottom: 0.5rem;
        }
        .report-card .report-value {
            font-size: 2.1rem;
            font-weight: 700;
            color: #2563eb;
        }
        @media (max-width: 600px) {
            .main-card { padding: 1rem; }
            .nav-propietario { flex-direction: column; gap: 8px; }
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
        <a href="ver_contratos.php"><i class="bi bi-file-earmark-text"></i>Contratos</a>
        <a href="ver_pagos.php"><i class="bi bi-cash-stack"></i>Pagos</a>
        <a href="reportes_propietario.php" class="active"><i class="bi bi-bar-chart"></i>Reportes</a>
        <!-- <a href="perfil_propietario.php"><i class="bi bi-person"></i>Perfil</a> -->
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card">
            <a href="../vistas/propietario.php" class="btn btn-secondary btn-back mb-3"><i class="bi bi-arrow-left"></i> Volver</a>
            <h2 class="mb-4">Reportes</h2>
            <!-- Fila 1: Visión General y Finanzas Totales -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="report-card">
                        <h4>Total Propiedades</h4>
                        <div class="report-value" id="anim-propiedades">0</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="report-card">
                        <h4>Total Contratos</h4>
                        <div class="report-value" id="anim-contratos">0</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="report-card">
                        <h4>Total Pagos Recibidos (Bruto)</h4>
                        <div class="report-value" id="anim-pagos">$0</div>
                    </div>
                </div>
            </div>
            <!-- Fila 2: Desglose Financiero Detallado -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="report-card">
                        <h4>Dinero recibido por los agentes</h4>
                        <div class="report-value" id="anim-propietario" style="color:#10b981;">$0</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="report-card">
                        <h4>Dinero dado a agentes</h4>
                        <div class="report-value" id="anim-agentes-comision" style="color:#ef4444;">$0</div>
                    </div>
                </div>
            </div>
            <!-- Fila 3: Indicadores Clave de Rendimiento y Alertas -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="report-card">
                        <h4>Propiedades sin contrato vigente</h4>
                        <div class="report-value" id="anim-sincontrato">0</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="report-card">
                        <h4>Contratos vencidos</h4>
                        <div class="report-value" id="anim-vencidos">0</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="report-card">
                        <h4>Arrendatarios únicos</h4>
                        <div class="report-value" id="anim-arrendatarios">0</div>
                    </div>
                </div>
            </div>
            <!-- Fila 4: Métricas de Operación y Rendimiento Específico -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="report-card">
                        <h4>Agentes trabajados</h4>
                        <div class="report-value" id="anim-agentes">0</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="report-card">
                        <h4>Propiedad con más contratos</h4>
                        <div class="report-value" id="anim-propmascontratos"><?= htmlspecialchars($prop_mas_contratos) ?> <span style="font-size:1rem;color:#6366f1;">(<?= $prop_mas_contratos_total ?>)</span></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="report-card">
                        <h4>Ciudad con más propiedades</h4>
                        <div class="report-value" id="anim-ciudadtop"><?= htmlspecialchars($ciudad_top) ?> <span style="font-size:1rem;color:#6366f1;">(<?= $ciudad_top_total ?>)</span></div>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <div class="row justify-content-center align-items-start g-0">
                    <div class="col-12 col-md-4 d-flex flex-column align-items-center mb-4 mb-md-0">
                        <div class="w-100 d-flex flex-column align-items-center" style="max-width:340px; min-width:240px;">
                            <h4 class="mb-3 text-center">Pagos recibidos por mes (últimos 12 meses)</h4>
                            <div class="bg-white rounded-4 shadow-sm p-3 w-100" style="min-width:240px; min-height:290px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                                <canvas id="pagosMesChart" height="180" style="max-width:260px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 d-flex flex-column align-items-center mb-4 mb-md-0">
                        <div class="w-100 d-flex flex-column align-items-center" style="max-width:340px; min-width:240px;">
                            <h4 class="mb-3 text-center">Dinero recibido por el propietario (últimos 12 meses)</h4>
                            <div class="bg-white rounded-4 shadow-sm p-3 w-100" style="min-width:240px; min-height:290px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                                <canvas id="propietarioMesChart" height="180" style="max-width:260px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 d-flex flex-column align-items-center">
                        <div class="w-100 d-flex flex-column align-items-center" style="max-width:340px; min-width:240px;">
                            <h4 class="mb-3 text-center">Dinero dado a agentes (últimos 12 meses)</h4>
                            <div class="bg-white rounded-4 shadow-sm p-3 w-100" style="min-width:240px; min-height:290px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                                <canvas id="agentesMesChart" height="180" style="max-width:260px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <div class="row justify-content-center align-items-start g-0">
                    <div class="col-12 col-md-6 d-flex flex-column align-items-center mb-4 mb-md-0">
                        <div class="w-100 d-flex flex-column align-items-center" style="max-width:340px; min-width:240px;">
                            <h4 class="mb-3 text-center">Distribución de propiedades por ciudad (Top 5)</h4>
                            <div class="bg-white rounded-4 shadow-sm p-3 w-100" style="min-width:240px; min-height:290px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                                <canvas id="ciudadesChart" height="180" style="max-width:260px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 d-flex flex-column align-items-center">
                        <div class="w-100 d-flex flex-column align-items-center" style="max-width:340px; min-width:240px; margin-top:32px;">
                            <h4 class="mb-3 text-center">Contratos por estado</h4>
                            <div class="bg-white rounded-4 shadow-sm p-3 w-100" style="min-width:240px; min-height:290px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                                <canvas id="estadosChart" height="180" style="max-width:260px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('pagosMesChart').getContext('2d');
        const data = {
            labels: [
                <?php
                $labels = array_reverse(array_column($pagos_mes, 'mes'));
                foreach ($labels as $l) {
                    $mes = DateTime::createFromFormat('Y-m', $l);
                    echo "'" . ucfirst(strftime('%B %Y', $mes->getTimestamp())) . "',";
                }
                ?>
            ],
            datasets: [{
                label: 'Pagos recibidos',
                data: <?= json_encode(array_reverse(array_map(function($r){return floatval($r['total_mes']);}, $pagos_mes))) ?>,
                backgroundColor: 'rgba(37,99,235,0.18)',
                borderColor: '#2563eb',
                borderWidth: 2,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#2563eb'
            }]
        };
        new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Datos para las gráficas de propietario y agentes (solo 10% mensual)
        const pagosMes = <?= json_encode(array_reverse($pagos_mes)) ?>;
        const comisionMensualPorMes = <?= json_encode($comision_mensual_mes) ?>;

        let labelsMes = [];
        let dataPropietario = [];
        let dataAgentes = [];
        pagosMes.forEach(function(row) {
            labelsMes.push(row.mes);
            let totalMes = parseFloat(row.total_mes);
            let comisionMes = comisionMensualPorMes[row.mes] ? parseFloat(comisionMensualPorMes[row.mes]) : 0;
            let netoMes = totalMes - comisionMes;
            dataPropietario.push(netoMes < 0 ? 0 : netoMes);
            dataAgentes.push(comisionMes);
        });

        // Gráfica: Dinero recibido por el propietario
        const ctxPropietario = document.getElementById('propietarioMesChart').getContext('2d');
        new Chart(ctxPropietario, {
            type: 'line',
            data: {
                labels: labelsMes.map(m => {
                    let d = new Date(m + '-01');
                    return d.toLocaleString('es-ES', { month: 'long', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Dinero recibido',
                    data: dataPropietario,
                    backgroundColor: 'rgba(16,185,129,0.18)',
                    borderColor: '#10b981',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#10b981'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Gráfica: Dinero dado a agentes
        const ctxAgentes = document.getElementById('agentesMesChart').getContext('2d');
        new Chart(ctxAgentes, {
            type: 'line',
            data: {
                labels: labelsMes.map(m => {
                    let d = new Date(m + '-01');
                    return d.toLocaleString('es-ES', { month: 'long', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Dinero dado a agentes',
                    data: dataAgentes,
                    backgroundColor: 'rgba(239,68,68,0.18)',
                    borderColor: '#ef4444',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#ef4444'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Gráfica de ciudades (tipo doughnut)
        const ctxCiudades = document.getElementById('ciudadesChart').getContext('2d');
        new Chart(ctxCiudades, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($ciudades_labels) ?>,
                datasets: [{
                    data: <?= json_encode($ciudades_data) ?>,
                    backgroundColor: [
                        '#6366f1', '#2563eb', '#fbbf24', '#10b981', '#ef4444'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'bottom' }
                }
            }
        });

        // Gráfica de contratos por estado (tipo doughnut)
        const ctxEstados = document.getElementById('estadosChart').getContext('2d');
        new Chart(ctxEstados, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($estados_labels) ?>,
                datasets: [{
                    data: <?= json_encode($estados_data) ?>,
                    backgroundColor: [
                        '#10b981', '#fbbf24', '#6366f1', '#ef4444', '#2563eb'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'bottom' }
                }
            }
        });

        animateNumber('anim-propiedades', <?= (int)$total_propiedades ?>);
        animateNumber('anim-contratos', <?= (int)$total_contratos ?>);
        animateNumber('anim-pagos', <?= (float)$total_pagos ?>, '$', 1400, 2);
        animateNumber('anim-sincontrato', <?= (int)$propiedades_sin_contrato ?>);
        animateNumber('anim-vencidos', <?= (int)$contratos_vencidos ?>);
        animateNumber('anim-arrendatarios', <?= (int)$arrendatarios_unicos ?>);
        animateNumber('anim-agentes', <?= (int)$agentes_trabajados ?>);
        animateNumber('anim-propietario', <?= (float)$total_recibido_propietario ?>, '$', 1400, 2);
        animateNumber('anim-agentes-comision', <?= (float)$total_comisiones_agente ?>, '$', 1400, 2);
        // Los siguientes dos reportes son de texto, no animar el número, pero podrías animar el número entre paréntesis si lo deseas:
        animateNumber('anim-propmascontratos', <?= (int)$prop_mas_contratos_total ?>, '', 1200, 0);
        animateNumber('anim-ciudadtop', <?= (int)$ciudad_top_total ?>, '', 1200, 0);
    });

    // Animación de subida para los reportes
    function animateNumber(id, end, prefix = '', duration = 1200, decimals = 0) {
        const el = document.getElementById(id);
        if (!el) return;
        let start = 0;
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            let value = start + (end - start) * progress;
            if (decimals > 0) {
                value = value.toFixed(decimals);
            } else {
                value = Math.round(value);
            }
            el.textContent = prefix + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }
    </script>
</body>
</html>
