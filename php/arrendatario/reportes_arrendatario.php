<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 3) {
    header('Location: ../login.php');
    exit();
}
$nombreCompleto = isset($_SESSION['nombre_completo']) ? trim($_SESSION['nombre_completo']) : 'Arrendatario';
$inicial = strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8'));
include '../../config/db.php';

$arrendatario_id = $_SESSION['id_usuario'];

// Total contratos
$stmt = $conn->prepare("SELECT COUNT(*) FROM contratos WHERE arrendatario_id = ?");
$stmt->bind_param("i", $arrendatario_id);
$stmt->execute();
$stmt->bind_result($total_contratos);
$stmt->fetch();
$stmt->close();

// Total pagado
$stmt = $conn->prepare(
    "SELECT SUM(monto) FROM pagos WHERE contrato_id IN (SELECT id FROM contratos WHERE arrendatario_id = ?)"
);
$stmt->bind_param("i", $arrendatario_id);
$stmt->execute();
$stmt->bind_result($total_pagado);
$stmt->fetch();
$stmt->close();
$total_pagado = $total_pagado ?: 0;

// Contratos por estado
$stmt = $conn->prepare(
    "SELECT estado, COUNT(*) as total FROM contratos WHERE arrendatario_id = ? GROUP BY estado"
);
$stmt->bind_param("i", $arrendatario_id);
$stmt->execute();
$resEstados = $stmt->get_result();
$estados_labels = [];
$estados_data = [];
while ($row = $resEstados->fetch_assoc()) {
    $estados_labels[] = $row['estado'] ?: 'Sin estado';
    $estados_data[] = (int)$row['total'];
}
$stmt->close();

// Pagos por mes (últimos 12 meses)
$stmt = $conn->prepare(
    "SELECT DATE_FORMAT(fecha_pago, '%Y-%m') as mes, SUM(monto) as total_mes
     FROM pagos
     WHERE contrato_id IN (SELECT id FROM contratos WHERE arrendatario_id = ?)
     GROUP BY mes
     ORDER BY mes DESC
     LIMIT 12"
);
$stmt->bind_param("i", $arrendatario_id);
$stmt->execute();
$resPagosMes = $stmt->get_result();
$pagos_mes = [];
while ($row = $resPagosMes->fetch_assoc()) {
    $pagos_mes[] = $row;
}
$stmt->close();

// Contratos por tipo de inmueble
$stmt = $conn->prepare(
    "SELECT p.tipo_inmueble, COUNT(*) as total FROM contratos c
     JOIN propiedades p ON c.propiedad_id = p.id
     WHERE c.arrendatario_id = ?
     GROUP BY p.tipo_inmueble"
);
$stmt->bind_param("i", $arrendatario_id);
$stmt->execute();
$resTipos = $stmt->get_result();
$tipos_labels = [];
$tipos_data = [];
while ($row = $resTipos->fetch_assoc()) {
    $tipos_labels[] = $row['tipo_inmueble'] ?: 'Sin tipo';
    $tipos_data[] = (int)$row['total'];
}
$stmt->close();

// Contratos cancelados
$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM contratos WHERE arrendatario_id = ? AND estado = 'Cancelado'"
);
$stmt->bind_param("i", $arrendatario_id);
$stmt->execute();
$stmt->bind_result($contratos_cancelados);
$stmt->fetch();
$stmt->close();

// Contratos vigentes
$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM contratos WHERE arrendatario_id = ? AND estado = 'Vigente'"
);
$stmt->bind_param("i", $arrendatario_id);
$stmt->execute();
$stmt->bind_result($contratos_vigentes);
$stmt->fetch();
$stmt->close();

// Contratos con cancelación anticipada
$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM contratos WHERE arrendatario_id = ? AND estado = 'Cancelación anticipada'"
);
$stmt->bind_param("i", $arrendatario_id);
$stmt->execute();
$stmt->bind_result($contratos_anticipados);
$stmt->fetch();
$stmt->close();

// Contratos firmados (para "Contratos Vigentes")
$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM contratos WHERE arrendatario_id = ? AND estado = 'Firmado'"
);
$stmt->bind_param("i", $arrendatario_id);
$stmt->execute();
$stmt->bind_result($contratos_firmados);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes del Arrendatario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            min-height: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: box-shadow 0.18s, transform 0.18s;
        }
        .report-card h4 {
            font-size: 1.15rem;
            color: #6366f1;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .report-card .report-value {
            font-size: 2.1rem;
            font-weight: 700;
            color: #2563eb;
        }
        .report-value.total-pagado {
            color: #ef4444 !important;
        }
        .row.mb-4 > .col-md-4, .row.mb-4 > .col-md-6 {
            display: flex;
        }
        .row.mb-4 > .col-md-4 > .report-card,
        .row.mb-4 > .col-md-6 > .report-card {
            width: 100%;
            min-height: 120px;
        }
        @media (max-width: 900px) {
            .report-card {
                min-height: 100px;
            }
        }
        @media (max-width: 600px) {
            .main-card { padding: 1rem; }
            .nav-arrendatario { flex-direction: column; gap: 8px; }
            .report-card { min-height: 90px; }
            .report-card canvas { max-width: 100%; }
        }
        .report-card canvas {
            max-width: 260px;
            max-height: 180px;
            margin: 0 auto !important;
            display: block !important;
            position: static !important;
            width: 100% !important;
            height: auto !important;
            image-rendering: auto !important;
        }
        .bg-white.rounded-4.shadow-sm.p-3.w-100 {
            min-height: unset !important;
            height: 220px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        @media (min-resolution: 2dppx), (min-device-pixel-ratio: 2) {
            .report-card canvas {
                image-rendering: -webkit-optimize-contrast !important;
                image-rendering: crisp-edges !important;
            }
        }
    </style>
</head>
<body>
    <header class="header-arrendatario text-center position-relative">
        <div class="avatar"><?php echo $inicial; ?></div>
        <h1>Bienvenido Arrendatario</h1>
        <div class="nombre-usuario">Hola, <?php echo htmlspecialchars($nombreCompleto); ?></div>
    </header>
    <nav class="nav-arrendatario mb-4">
        <a href="mis_propiedades.php"><i class="bi bi-house-door"></i>Propiedades Arrendadas</a>
        <a href="mis_contratos.php"><i class="bi bi-file-earmark-text"></i>Mis Contratos</a>
        <a href="mis_pagos.php"><i class="bi bi-clock-history"></i>Historial de Pagos</a>
        <a href="subir_comprobante.php"><i class="bi bi-upload"></i>Subir Comprobante</a>
        <a href="reportes_arrendatario.php" class="active"><i class="bi bi-bar-chart"></i>Reportes</a>
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card">
            <a href="../vistas/arrendatario.php" class="btn btn-secondary btn-back mb-3"><i class="bi bi-arrow-left"></i> Volver</a>
            <h2 class="mb-4">Reportes</h2>
            <!-- Fila 1: KPIs Generales -->
            <div class="row mb-4">
                <div class="col-md-4 d-flex">
                    <div class="report-card w-100">
                        <h4>Total Contratos</h4>
                        <div class="report-value" id="anim-contratos">0</div>
                    </div>
                </div>
                <div class="col-md-4 d-flex">
                    <div class="report-card w-100">
                        <h4>Total Pagado</h4>
                        <div class="report-value total-pagado" id="anim-pagado">$0</div>
                    </div>
                </div>
                <div class="col-md-4 d-flex">
                    <div class="report-card w-100">
                        <h4>Contratos Vigentes</h4>
                        <div class="report-value" id="anim-vigentes">0</div>
                    </div>
                </div>
            </div>
            <!-- Fila 2: Reportes adicionales de contratos -->
            <div class="row mb-4">
                <div class="col-md-6 d-flex">
                    <div class="report-card w-100">
                        <h4>Contratos Cancelados</h4>
                        <div class="report-value" id="anim-cancelados">0</div>
                    </div>
                </div>
                <div class="col-md-6 d-flex">
                    <div class="report-card w-100">
                        <h4>Cancelación Anticipada</h4>
                        <div class="report-value" id="anim-anticipados">0</div>
                    </div>
                </div>
            </div>
            <!-- Fila 3: Gráficos de Tendencias -->
            <div class="row mb-4">
                <div class="col-md-6 d-flex">
                    <div class="report-card w-100" style="min-height:260px;">
                        <h4 class="mb-3">Pagos realizados por mes (últimos 12 meses)</h4>
                        <canvas id="pagosMesChart" height="180" style="max-width:100%; min-height:180px;"></canvas>
                    </div>
                </div>
                <div class="col-md-6 d-flex">
                    <div class="report-card w-100" style="min-height:260px;">
                        <h4 class="mb-3">Contratos por estado</h4>
                        <div class="bg-white rounded-4 shadow-sm p-3 w-100" style="height:220px;display:flex;align-items:center;justify-content:center;">
                            <canvas id="estadosChart" style="max-width:260px;max-height:180px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Fila 4: Contratos por tipo de inmueble -->
            <div class="row mb-4">
                <div class="col-md-12 d-flex">
                    <div class="report-card w-100">
                        <h4 class="mb-3 text-center">Contratos por tipo de inmueble</h4>
                        <div class="bg-white rounded-4 shadow-sm p-3 w-100" style="height:220px;display:flex;align-items:center;justify-content:center;">
                            <canvas id="tiposChart" style="max-width:260px;max-height:180px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animaciones de números
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
        animateNumber('anim-contratos', <?= (int)$total_contratos ?>);
        animateNumber('anim-pagado', <?= (float)$total_pagado ?>, '$', 1400, 2);
        animateNumber('anim-vigentes', <?= (int)$contratos_firmados ?>);
        animateNumber('anim-cancelados', <?= (int)$contratos_cancelados ?>);
        animateNumber('anim-anticipados', <?= (int)$contratos_anticipados ?>);

        // Gráfica de pagos por mes
        const pagosMes = <?= json_encode(array_reverse($pagos_mes)) ?>;
        let labelsMes = [];
        let dataPagos = [];
        pagosMes.forEach(function(row) {
            labelsMes.push(row.mes);
            let totalMes = parseFloat(row.total_mes);
            dataPagos.push(totalMes);
        });

        // Pagos por mes
        const ctxPagos = document.getElementById('pagosMesChart').getContext('2d');
        new Chart(ctxPagos, {
            type: 'line',
            data: {
                labels: labelsMes.map(m => {
                    let d = new Date(m + '-01');
                    return d.toLocaleString('es-ES', { month: 'long', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Pagos realizados',
                    data: dataPagos,
                    backgroundColor: 'rgba(37,99,235,0.18)',
                    borderColor: '#2563eb',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#2563eb',
                    pointRadius: 6,
                    pointHoverRadius: 7,
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                elements: {
                    line: { borderJoinStyle: 'round', borderCapStyle: 'round' }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#444',
                            font: { size: 14, family: "'Segoe UI', 'Roboto', Arial, sans-serif" }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e0e7ef', borderDash: [4, 4] },
                        ticks: {
                            color: '#444',
                            font: { size: 14, family: "'Segoe UI', 'Roboto', Arial, sans-serif" },
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Contratos por estado (doughnut)
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
                cutout: '65%',
                plugins: {
                    legend: { display: true, position: 'bottom' },
                    tooltip: { enabled: true }
                }
            }
        });

        // Contratos por tipo de inmueble (doughnut)
        const ctxTipos = document.getElementById('tiposChart').getContext('2d');
        new Chart(ctxTipos, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($tipos_labels) ?>,
                datasets: [{
                    data: <?= json_encode($tipos_data) ?>,
                    backgroundColor: [
                        '#fbbf24', '#10b981', '#6366f1', '#ef4444', '#2563eb'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: true, position: 'bottom' },
                    tooltip: { enabled: true }
                }
            }
        });

        // Ajuste para que las gráficas de líneas sean más compactas y responsivas
        function ajustarAlturaCanvasLineas() {
            document.getElementById('pagosMesChart').height = 220;
        }
        window.addEventListener('resize', ajustarAlturaCanvasLineas);
        ajustarAlturaCanvasLineas();

        // Forzar alta resolución en los canvas de Chart.js
        function setCanvasDPI(canvas, width, height) {
            const dpr = window.devicePixelRatio || 1;
            canvas.width = width * dpr;
            canvas.height = height * dpr;
            canvas.style.width = width + "px";
            canvas.style.height = height + "px";
            const ctx = canvas.getContext('2d');
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.scale(dpr, dpr);
        }
        setCanvasDPI(document.getElementById('pagosMesChart'), 700, 220);
        setCanvasDPI(document.getElementById('estadosChart'), 260, 180);
        setCanvasDPI(document.getElementById('tiposChart'), 260, 180);
    });
    </script>
</body>
</html>
