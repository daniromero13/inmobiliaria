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
$mensaje = '';

// Obtener contratos vigentes del agente, incluyendo fecha_inicio y duración calculada en meses
$stmtContratos = $conn->prepare(
    "SELECT c.id, p.titulo, u.nombre_completo AS arrendatario, p.precio, c.fecha_inicio, 
        TIMESTAMPDIFF(MONTH, c.fecha_inicio, c.fecha_fin) + 1 AS duracion_meses
     FROM contratos c
     JOIN propiedades p ON c.propiedad_id = p.id
     JOIN usuarios u ON c.arrendatario_id = u.id
     WHERE p.agente_id = ?"
);
$stmtContratos->bind_param("i", $agente_id);
$stmtContratos->execute();
$contratosVigentes = $stmtContratos->get_result();
$contratosArray = [];
while ($contrato = $contratosVigentes->fetch_assoc()) {
    $contratosArray[] = $contrato;
}

// Obtener pagos existentes por contrato
$pagosPorContrato = [];
if (count($contratosArray) > 0) {
    $ids = array_column($contratosArray, 'id');
    $ids_placeholder = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmtPagos = $conn->prepare(
        "SELECT contrato_id, fecha_pago FROM pagos WHERE contrato_id IN ($ids_placeholder)"
    );
    $stmtPagos->bind_param($types, ...$ids);
    $stmtPagos->execute();
    $resultPagos = $stmtPagos->get_result();
    while ($row = $resultPagos->fetch_assoc()) {
        $pagosPorContrato[$row['contrato_id']][] = $row['fecha_pago'];
    }
}

// Si viene por GET con comprobante_id, prellenar el formulario
$comprobanteSeleccionado = null;
if (isset($_GET['comprobante_id'])) {
    $comprobante_id = intval($_GET['comprobante_id']);
    $sql = "SELECT cp.id, cp.contrato_id, cp.estado, p.precio
            FROM comprobantes_pagos cp
            JOIN contratos c ON cp.contrato_id = c.id
            JOIN propiedades p ON c.propiedad_id = p.id
            WHERE cp.id = ? AND p.agente_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $comprobante_id, $agente_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $comprobanteSeleccionado = $row;
    }
}

// Procesar registro de pago y actualizar comprobante a aceptado si corresponde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contrato_id = isset($_POST['contrato_id']) ? $_POST['contrato_id'] : null;
    $monto = isset($_POST['monto']) ? $_POST['monto'] : null;
    $fecha_pago = isset($_POST['fecha_pago']) ? $_POST['fecha_pago'] : null;
    $comprobante_id = isset($_POST['comprobante_id']) ? intval($_POST['comprobante_id']) : null;

    // Verificar que los campos requeridos existen
    if (!$contrato_id || !$monto || !$fecha_pago) {
        $mensaje = "<div class='alert alert-danger'>Por favor complete todos los campos requeridos.</div>";
    } else {
        // Verificar que el contrato_id existe y pertenece al agente
        $checkContratoQuery = "SELECT c.id FROM contratos c JOIN propiedades p ON c.propiedad_id = p.id WHERE c.id = ? AND p.agente_id = ?";
        $stmt = $conn->prepare($checkContratoQuery);
        $stmt->bind_param("ii", $contrato_id, $agente_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $mensaje = "<div class='alert alert-danger'>Error: El contrato especificado no existe o no pertenece a usted.</div>";
        } else {
            // Insertar el pago en la tabla pagos
            $stmt = $conn->prepare("INSERT INTO pagos (contrato_id, monto, fecha_pago) VALUES (?, ?, ?)");
            $stmt->bind_param("ids", $contrato_id, $monto, $fecha_pago);

            if ($stmt->execute()) {
                // Si viene comprobante_id, actualizar comprobante a aceptado
                if ($comprobante_id) {
                    $stmtUpd = $conn->prepare("UPDATE comprobantes_pagos SET estado='Aceptado' WHERE id=?");
                    $stmtUpd->bind_param("i", $comprobante_id);
                    $stmtUpd->execute();
                }
                header("Location: registrar_pago.php?success=1");
                exit();
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al registrar el pago: " . $conn->error . "</div>";
            }
        }
    }
}

// Mostrar mensaje de éxito si viene por GET
if (isset($_GET['success'])) {
    $mensaje = "<div class='alert alert-success' id='msg-exito'>Pago registrado exitosamente.</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Pago</title>
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
            max-width: 600px;
            margin: 0 auto 40px auto;
            border-radius: 18px;
            box-shadow: 0 6px 32px 0 rgba(60,72,88,0.12);
            background: #fff;
            border: none;
            padding: 2rem 2.5rem;
        }
        .table-responsive { margin-top: 2rem; }
        @media (max-width: 600px) {
            .main-card { padding: 1rem; }
            .nav-agente { flex-direction: column; gap: 8px; }
        }
        /* Animación de vibración para la campana */
        @keyframes bell-shake {
            0% { transform: rotate(0); }
            15% { transform: rotate(-15deg); }
            30% { transform: rotate(10deg); }
            45% { transform: rotate(-10deg); }
            60% { transform: rotate(6deg); }
            75% { transform: rotate(-4deg); }
            85% { transform: rotate(2deg); }
            100% { transform: rotate(0); }
        }
        .bell-animate {
            animation: bell-shake 1s cubic-bezier(.36,.07,.19,.97) both;
            animation-iteration-count: 2;
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
        <a href="registrar_pago.php" class="active"><i class="bi bi-cash-stack"></i>Registrar Pago</a>
        <a href="historial_pagos.php"><i class="bi bi-receipt"></i>Historial de Pagos</a>
        <a href="historial_contratos.php"><i class="bi bi-clock-history"></i>Historial Contratos</a>
        <a href="reportes_agente.php"><i class="bi bi-bar-chart"></i>Reportes</a>
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 d-flex justify-content-center">
                <div class="row g-4 justify-content-center w-100">
                    <!-- Registrar Pago (centrado) -->
                    <div class="col-lg-7 col-md-10 d-flex align-items-stretch mx-auto">
                        <div class="main-card shadow mb-4 w-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <a href="propiedades.php" class="btn btn-secondary btn-back">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </a>
                                <h2 class="mb-0 text-center flex-grow-1" style="font-size:1.7rem;">Registrar Pago</h2>
                                <span style="width: 90px;"></span>
                            </div>
                            <div class="alert alert-info">
                                Este formulario es solo para registrar pagos presenciales realizados directamente con el arrendatario o aceptar comprobantes enviados.
                            </div>
                            <?php if ($mensaje) echo $mensaje; ?>
                            <form method="POST">
                                <?php if ($comprobanteSeleccionado): ?>
                                    <input type="hidden" name="comprobante_id" value="<?= $comprobanteSeleccionado['id'] ?>">
                                <?php endif; ?>
                                <div class="row align-items-end">
                                    <div class="col-md-12 mb-3">
                                        <label for="contrato_id" class="form-label">Contrato Vigente</label>
                                        <select class="form-select" id="contrato_id" name="contrato_id" required onchange="setMontoYFechaPorContrato()" <?= $comprobanteSeleccionado ? 'readonly disabled' : '' ?>>
                                            <option value="">Seleccione un contrato</option>
                                            <?php foreach ($contratosArray as $contrato): ?>
                                                <option value="<?= $contrato['id'] ?>"
                                                    data-precio="<?= $contrato['precio'] ?>"
                                                    data-fecha_inicio="<?= $contrato['fecha_inicio'] ?>"
                                                    data-duracion="<?= $contrato['duracion_meses'] ?>"
                                                    <?= ($comprobanteSeleccionado && $contrato['id'] == $comprobanteSeleccionado['contrato_id']) ? 'selected' : '' ?>
                                                >
                                                    #<?= $contrato['id'] ?> - <?= htmlspecialchars($contrato['titulo']) ?> (<?= htmlspecialchars($contrato['arrendatario']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($comprobanteSeleccionado): ?>
                                            <input type="hidden" name="contrato_id" value="<?= $comprobanteSeleccionado['contrato_id'] ?>">
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="monto" class="form-label">Monto</label>
                                        <input type="number" class="form-control" id="monto" name="monto" required readonly
                                            value="<?= $comprobanteSeleccionado ? htmlspecialchars($comprobanteSeleccionado['precio']) : '' ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha_pago" class="form-label">Fecha de Pago</label>
                                        <select class="form-select" id="fecha_pago" name="fecha_pago" required>
                                            <option value="">Seleccione el mes a pagar</option>
                                            <!-- Las opciones se llenan por JS, pero si hay comprobante seleccionado, puedes dejarlo vacío para que el agente seleccione manualmente -->
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3 d-grid">
                                        <button type="submit" class="btn btn-primary">Registrar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- Apartado de comprobantes de pagos recibidos -->
                    <div class="col-lg-5 col-md-10 d-flex align-items-stretch mx-auto">
                        <div class="main-card shadow mb-4 w-100">
                            <h4 class="mb-3 text-center"><i class="bi bi-receipt"></i> Comprobantes de pagos recibidos</h4>
                            <?php
                            // Obtener comprobantes de pagos recibidos para este agente
                            $sqlComprobantes = "SELECT cp.id, cp.archivo, cp.fecha_envio, cp.estado, u.nombre_completo AS arrendatario, p.titulo AS propiedad, c.id AS contrato_id
                                FROM comprobantes_pagos cp
                                JOIN contratos c ON cp.contrato_id = c.id
                                JOIN propiedades p ON c.propiedad_id = p.id
                                JOIN usuarios u ON c.arrendatario_id = u.id
                                WHERE p.agente_id = ?
                                ORDER BY cp.fecha_envio DESC";
                            $stmtComprobantes = $conn->prepare($sqlComprobantes);
                            $stmtComprobantes->bind_param("i", $agente_id);
                            $stmtComprobantes->execute();
                            $resComprobantes = $stmtComprobantes->get_result();
                            if ($resComprobantes->num_rows > 0): ?>
                                <div class="list-group" style="max-height:400px;overflow-y:auto;">
                                    <?php while ($comp = $resComprobantes->fetch_assoc()): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-start flex-column flex-md-row mb-2" style="border-radius:10px;">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold"><?= htmlspecialchars($comp['arrendatario']) ?> (<?= htmlspecialchars($comp['propiedad']) ?>)</div>
                                                <div class="small text-muted mb-1">
                                                    Contrato #<?= $comp['contrato_id'] ?> | <?= date('d/m/Y H:i', strtotime($comp['fecha_envio'])) ?>
                                                </div>
                                                <span class="badge bg-<?= 
                                                    $comp['estado'] === 'Pendiente' ? 'warning text-dark' :
                                                    ($comp['estado'] === 'Aceptado' ? 'success' : 'secondary')
                                                ?>">
                                                    <?= htmlspecialchars($comp['estado']) ?>
                                                </span>
                                            </div>
                                            <div class="d-flex flex-column align-items-end gap-2 mt-2 mt-md-0 ms-md-3">
                                                <a href="../../uploads/<?= htmlspecialchars($comp['archivo']) ?>" target="_blank" class="btn btn-sm btn-info">Ver comprobante</a>
                                                <?php if ($comp['estado'] === 'Pendiente'): ?>
                                                    <a href="registrar_pago.php?comprobante_id=<?= $comp['id'] ?>" class="btn btn-sm btn-success">Aceptar</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info text-center mb-0">No hay comprobantes de pagos recibidos.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Fin apartado comprobantes -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS para el modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Guardar precios y fechas de inicio por contrato en JS
        const preciosPorContrato = {};
        const fechasInicioPorContrato = {};
        const duracionesPorContrato = {};
        const pagosPorContrato = {};
        <?php foreach ($contratosArray as $contrato): ?>
            preciosPorContrato[<?= $contrato['id'] ?>] = <?= $contrato['precio'] ?>;
            fechasInicioPorContrato[<?= $contrato['id'] ?>] = "<?= $contrato['fecha_inicio'] ?>";
            duracionesPorContrato[<?= $contrato['id'] ?>] = <?= $contrato['duracion_meses'] ?>;
        <?php endforeach; ?>
        <?php foreach ($pagosPorContrato as $cid => $pagos): ?>
            pagosPorContrato[<?= $cid ?>] = <?= json_encode($pagos) ?>;
        <?php endforeach; ?>

        function setMontoYFechaPorContrato() {
            const select = document.getElementById('contrato_id');
            const monto = document.getElementById('monto');
            const fechaPago = document.getElementById('fecha_pago');
            const id = select.value;
            monto.value = id && preciosPorContrato[id] ? preciosPorContrato[id] : '';

            // Limpiar opciones de fecha_pago
            fechaPago.innerHTML = '<option value="">Seleccione el mes a pagar</option>';
            if (!id || !fechasInicioPorContrato[id] || !duracionesPorContrato[id]) return;

            const fechaInicio = new Date(fechasInicioPorContrato[id]);
            const duracion = duracionesPorContrato[id];
            const pagosRealizados = pagosPorContrato[id] || [];
            // Convertir pagos realizados a formato YYYY-MM
            const pagosMeses = pagosRealizados.map(f => f.slice(0,7));

            for (let i = 0; i < duracion; i++) {
                let fechaMes = new Date(fechaInicio);
                fechaMes.setMonth(fechaMes.getMonth() + i);
                let mesStr = fechaMes.toISOString().slice(0,7); // YYYY-MM
                let label = fechaMes.toLocaleString('es-ES', { month: 'long', year: 'numeric' });
                let fechaCompleta = fechaMes.toISOString().slice(0,10);
                let disabled = pagosMeses.includes(mesStr) ? 'disabled' : '';
                fechaPago.innerHTML += `<option value="${fechaCompleta}" ${disabled}>${label}${disabled ? ' (Pagado)' : ''}</option>`;
            }
        }

        // Si hay un contrato seleccionado al cargar, inicializar los campos
        window.addEventListener('DOMContentLoaded', function() {
            setMontoYFechaPorContrato();
            const msg = document.getElementById('msg-exito');
            if (msg) {
                setTimeout(() => {
                    msg.style.transition = 'opacity 0.5s';
                    msg.style.opacity = '0';
                    setTimeout(() => {
                        msg.style.display = 'none';
                    }, 500);
                }, 2000);
            }
        });
    </script>
</body>
</html>
