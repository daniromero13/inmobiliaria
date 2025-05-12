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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contrato_id = $_POST['contrato_id'];
    $monto = $_POST['monto'];
    $fecha_pago = $_POST['fecha_pago'];

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
            // Redirigir tras el POST para evitar reenvío accidental (POST/REDIRECT/GET)
            header("Location: registrar_pago.php?success=1");
            exit();
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al registrar el pago: " . $conn->error . "</div>";
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
                                Este formulario es solo para registrar pagos presenciales realizados directamente con el arrendatario.
                            </div>
                            <?php if ($mensaje) echo $mensaje; ?>
                            <form method="POST">
                                <div class="row align-items-end">
                                    <div class="col-md-12 mb-3">
                                        <label for="contrato_id" class="form-label">Contrato Vigente</label>
                                        <select class="form-select" id="contrato_id" name="contrato_id" required onchange="setMontoYFechaPorContrato()">
                                            <option value="">Seleccione un contrato</option>
                                            <?php foreach ($contratosArray as $contrato): ?>
                                                <option value="<?= $contrato['id'] ?>"
                                                    data-precio="<?= $contrato['precio'] ?>"
                                                    data-fecha_inicio="<?= $contrato['fecha_inicio'] ?>"
                                                    data-duracion="<?= $contrato['duracion_meses'] ?>">
                                                    #<?= $contrato['id'] ?> - <?= htmlspecialchars($contrato['titulo']) ?> (<?= htmlspecialchars($contrato['arrendatario']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="monto" class="form-label">Monto</label>
                                        <input type="number" class="form-control" id="monto" name="monto" required readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha_pago" class="form-label">Fecha de Pago</label>
                                        <select class="form-select" id="fecha_pago" name="fecha_pago" required>
                                            <option value="">Seleccione el mes a pagar</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3 d-grid">
                                        <button type="submit" class="btn btn-primary">Registrar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- No hay columna de historial aquí -->
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
