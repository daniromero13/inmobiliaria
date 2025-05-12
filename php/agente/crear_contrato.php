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
$query = "SELECT * FROM propiedades WHERE agente_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $agente_id);
$stmt->execute();
$propiedades = $stmt->get_result();

// Obtener arrendatarios (usuarios con rol_id = 3)
$arrendatarios = [];
$resArr = $conn->query("SELECT id, nombre_completo FROM usuarios WHERE rol_id = 3");
while ($row = $resArr->fetch_assoc()) {
    $arrendatarios[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propiedad_id = $_POST['propiedad_id'];
    $arrendatario_id = $_POST['arrendatario_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $monto = $_POST['monto'];

    // Verificar si la propiedad ya tiene un contrato vigente
    $stmtCheck = $conn->prepare(
        "SELECT 1 FROM contratos 
         WHERE propiedad_id = ? 
         AND estado = 'Vigente'
         AND (
             (fecha_inicio <= ? AND fecha_fin >= ?) OR
             (fecha_inicio <= ? AND fecha_fin >= ?) OR
             (fecha_inicio >= ? AND fecha_fin <= ?)
         )"
    );
    $stmtCheck->bind_param(
        "issssss",
        $propiedad_id,
        $fecha_inicio, $fecha_inicio,
        $fecha_fin, $fecha_fin,
        $fecha_inicio, $fecha_fin
    );
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        echo <<<HTML
        <div class='alert alert-danger alert-dismissible fade show' role='alert'>
            No se puede crear el contrato: la propiedad ya está asignada a otro arrendatario en el periodo seleccionado.
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Cerrar'></button>
        </div>
        HTML;
    } else {
        // Obtener datos de propiedad, agente y arrendatario
        $stmtProp = $conn->prepare("SELECT * FROM propiedades WHERE id = ?");
        $stmtProp->bind_param("i", $propiedad_id);
        $stmtProp->execute();
        $propiedad = $stmtProp->get_result()->fetch_assoc();

        $stmtArr = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmtArr->bind_param("i", $arrendatario_id);
        $stmtArr->execute();
        $arrendatario = $stmtArr->get_result()->fetch_assoc();

        $stmtAgente = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmtAgente->bind_param("i", $agente_id);
        $stmtAgente->execute();
        $agente = $stmtAgente->get_result()->fetch_assoc();

        // Insertar contrato (sin PDF aún)
        $stmt = $conn->prepare("INSERT INTO contratos (propiedad_id, arrendatario_id, fecha_inicio, fecha_fin, monto) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $propiedad_id, $arrendatario_id, $fecha_inicio, $fecha_fin, $monto);
        if ($stmt->execute()) {
            $contrato_id = $stmt->insert_id;

            // Generar PDF profesional con soporte UTF-8
            require_once(__DIR__ . '/../../fpdf/fpdf.php');
            $pdfDir = __DIR__ . '/../../documentos';
            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0777, true);
            }
            $pdfPath = $pdfDir . "/contrato_{$contrato_id}.pdf";
            $pdfRelPath = "documentos/contrato_{$contrato_id}.pdf";

            $pdf = new FPDF();
            // Usar solo fuentes estándar de FPDF para evitar errores de fuentes externas
            $pdf->SetFont('Arial','',14);
            $pdf->AddPage();

            // Encabezado
            $pdf->SetFillColor(37,99,235);
            $pdf->SetTextColor(255,255,255);
            $pdf->Cell(0,15,utf8_decode('CONTRATO DE ARRENDAMIENTO'),0,1,'C',true);
            $pdf->Ln(2);

            // Datos del contrato
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont('Arial','',11);
            $pdf->SetFillColor(230,230,250);
            $pdf->Cell(0,8,utf8_decode('Datos del Contrato'),0,1,'L',true);
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(0,7,utf8_decode("Fecha de inicio: {$fecha_inicio}"),0,1);
            $pdf->Cell(0,7,utf8_decode("Fecha de fin: {$fecha_fin}"),0,1);
            $pdf->Cell(0,7,utf8_decode("Monto mensual: $ {$monto}"),0,1);
            $pdf->Ln(2);

            // Datos de la propiedad
            $pdf->SetFont('Arial','',11);
            $pdf->SetFillColor(230,230,250);
            $pdf->Cell(0,8,utf8_decode('Datos de la Propiedad'),0,1,'L',true);
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(0,7,utf8_decode("Título: {$propiedad['titulo']}"),0,1);
            $pdf->Cell(0,7,utf8_decode("Tipo: {$propiedad['tipo_inmueble']}"),0,1);
            $pdf->Cell(0,7,utf8_decode("Zona: {$propiedad['zona']}"),0,1);
            $pdf->Cell(0,7,utf8_decode("Departamento: {$propiedad['departamento']}"),0,1);
            $pdf->Cell(0,7,utf8_decode("Ciudad: {$propiedad['ciudad']}"),0,1);
            $pdf->Cell(0,7,utf8_decode("Estrato: {$propiedad['estrato']}"),0,1);
            $pdf->Cell(0,7,utf8_decode("Precio: $ {$propiedad['precio']}"),0,1);
            $pdf->MultiCell(0,7,utf8_decode("Descripción: {$propiedad['descripcion']}"));
            $pdf->Ln(2);

            // Datos del agente
            $pdf->SetFont('Arial','B',11);
            $pdf->SetFillColor(230,230,250);
            $pdf->Cell(0,8,utf8_decode('Datos del Agente'),0,1,'L',true);
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(0,7,utf8_decode("Nombre: {$agente['nombre_completo']}"),0,1);
            $pdf->Cell(0,7,utf8_decode("Correo: {$agente['correo']}"),0,1);
            $pdf->Cell(0,7,utf8_decode("Teléfono: {$agente['telefono']}"),0,1);
            $pdf->Ln(2);

            // Datos del arrendatario
            $pdf->SetFont('Arial','B',11);
            $pdf->SetFillColor(230,230,250);
            $pdf->Cell(0,8,utf8_decode('Datos del Arrendatario'),0,1,'L',true);
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(0,7,utf8_decode("Nombre: {$arrendatario['nombre_completo']}"),0,1);
            $pdf->Cell(0,7,utf8_decode("Correo: {$arrendatario['correo']}"),0,1);
            $pdf->Cell(0,7,utf8_decode("Teléfono: {$arrendatario['telefono']}"),0,1);

            // Firmas
            $pdf->Ln(15);
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(90,7,utf8_decode('________________________'),0,0,'C');
            $pdf->Cell(10,7,'',0,0);
            $pdf->Cell(90,7,utf8_decode('________________________'),0,1,'C');
            $pdf->Cell(90,7,utf8_decode('Firma Agente'),0,0,'C');
            $pdf->Cell(10,7,'',0,0);
            $pdf->Cell(90,7,utf8_decode('Firma Arrendatario'),0,1,'C');

            $pdf->Output('F', $pdfPath);

            // Actualizar contrato con la ruta del PDF
            $stmtUpd = $conn->prepare("UPDATE contratos SET pdf_contrato = ? WHERE id = ?");
            $stmtUpd->bind_param("si", $pdfRelPath, $contrato_id);
            $stmtUpd->execute();

            // Redirigir inmediatamente después de crear el contrato para evitar duplicados por recarga
            header("Location: historial_contratos.php");
            exit();
        } else {
            echo "<div class='alert alert-danger'>Error al crear el contrato.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Contrato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
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
        @media (max-width: 600px) {
            .main-card { padding: 1rem; }
            .nav-agente { flex-direction: column; gap: 8px; }
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
        <a href="crear_contrato.php" class="active"><i class="bi bi-file-earmark-plus"></i>Crear Contrato</a>
        <a href="../../php/agente/registrar_pago.php"><i class="bi bi-cash-stack"></i>Registrar Pago</a>
        <a href="historial_pagos.php"><i class="bi bi-receipt"></i>Historial de Pagos</a>
        <a href="../../php/agente/historial_contratos.php"><i class="bi bi-clock-history"></i>Historial Contratos</a>
        <a href="reportes_agente.php"><i class="bi bi-bar-chart"></i>Reportes</a>
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card shadow">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="propiedades.php" class="btn btn-secondary btn-back">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <h2 class="mb-0 text-center flex-grow-1" style="font-size:1.7rem;">Crear Contrato</h2>
                <!-- Espacio para alinear el título al centro -->
                <span style="width: 90px;"></span>
            </div>
            <form method="POST">
                <div class="mb-3">
                    <label for="propiedad_id" class="form-label">Propiedad</label>
                    <select class="form-select" id="propiedad_id" name="propiedad_id" required onchange="setPrecioPropiedad()">
                        <option value="">Seleccione una propiedad</option>
                        <?php
                        // Guardar precios en un array JS
                        $preciosJS = [];
                        while ($propiedad = $propiedades->fetch_assoc()):
                            $preciosJS[$propiedad['id']] = $propiedad['precio'];
                        ?>
                            <option value="<?= $propiedad['id'] ?>" data-precio="<?= $propiedad['precio'] ?>">
                                <?= htmlspecialchars($propiedad['titulo']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="arrendatario_id" class="form-label">Nombre del Arrendatario</label>
                    <select class="form-select" id="arrendatario_id" name="arrendatario_id" required>
                        <option value="">Seleccione un arrendatario</option>
                        <?php foreach ($arrendatarios as $arr): ?>
                            <option value="<?= $arr['id'] ?>"><?= htmlspecialchars($arr['nombre_completo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                </div>
                <div class="mb-3">
                    <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                </div>
                <div class="mb-3">
                    <label for="monto" class="form-label">Monto mensual (COP)</label>
                    <input type="text" class="form-control" id="monto" name="monto" required placeholder="El valor se asigna automáticamente según el precio de la propiedad." readonly>
                </div>
                <button type="submit" class="btn btn-primary w-100">Crear Contrato</button>
            </form>
        </div>
    </div>
    <script>
        // Precios de propiedades en JS
        const precios = <?php echo json_encode($preciosJS); ?>;
        function setPrecioPropiedad() {
            const select = document.getElementById('propiedad_id');
            const monto = document.getElementById('monto');
            const id = select.value;
            monto.value = id && precios[id] ? precios[id] : '';
        }
    </script>
</body>
</html>
