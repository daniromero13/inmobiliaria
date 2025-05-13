<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 4) {
    header('Location: ../login.php');
    exit();
}
$nombreCompleto = isset($_SESSION['nombre_completo']) ? trim($_SESSION['nombre_completo']) : 'Agente';
$inicial = strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8'));
include '../../config/db.php';

// Obtener lista de propietarios
$propietarios = [];
$result = $conn->query("SELECT id, nombre_completo FROM usuarios WHERE rol_id = 2");
while ($row = $result->fetch_assoc()) {
    $propietarios[] = $row;
}

// Lista de departamentos y ciudades (puedes ampliar según tu necesidad)
$departamentos_ciudades = [
    "Antioquia" => ["Medellín", "Envigado", "Bello", "Itagüí", "Rionegro"],
    "Cundinamarca" => ["Bogotá", "Soacha", "Chía", "Zipaquirá", "Facatativá"],
    "Valle del Cauca" => ["Cali", "Palmira", "Buenaventura", "Tuluá", "Cartago"],
    "Atlántico" => ["Barranquilla", "Soledad", "Malambo", "Puerto Colombia", "Sabanalarga"],
    "Santander" => ["Bucaramanga", "Floridablanca", "Girón", "Piedecuesta"],
    "Bolívar" => ["Cartagena", "Magangué", "Turbaco", "Arjona"],
    "Nariño" => ["Pasto", "Tumaco", "Ipiales"],
    "Caldas" => ["Manizales", "La Dorada", "Villamaría"],
    "Risaralda" => ["Pereira", "Dosquebradas", "Santa Rosa de Cabal"],
    "Quindío" => ["Armenia", "Calarcá", "Montenegro"],
    "Meta" => ["Villavicencio", "Acacías", "Granada"],
    "Huila" => ["Neiva", "Pitalito", "Garzón"],
    "Cesar" => ["Valledupar", "Aguachica", "Codazzi"],
    "Magdalena" => ["Santa Marta", "Ciénaga", "Fundación"],
    "Boyacá" => ["Tunja", "Duitama", "Sogamoso"],
    "Tolima" => ["Ibagué", "Espinal", "Melgar"],
    "Norte de Santander" => ["Cúcuta", "Ocaña", "Pamplona"],
    "Sucre" => ["Sincelejo", "Corozal", "Sampués"],
    "Córdoba" => ["Montería", "Lorica", "Sahagún"],
    "La Guajira" => ["Riohacha", "Maicao", "Uribia"]
    // ...puedes seguir agregando más...
];

// Registrar propiedad si se envió el formulario
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modal_registro'])) {
    $titulo = trim($_POST['titulo']);
    $tipo_inmueble = trim($_POST['tipo_inmueble']);
    $estrato = intval($_POST['estrato']);
    $antiguedad = intval($_POST['antiguedad']);
    $banos = intval($_POST['banos']);
    $habitaciones = intval($_POST['habitaciones']);
    $parqueadero = isset($_POST['parqueadero']) ? 1 : 0;
    $ubicacion = isset($_POST['ubicacion']) ? trim($_POST['ubicacion']) : ''; // Nuevo para ubicacion
    $zona = isset($_POST['zona']) ? trim($_POST['zona']) : ''; // Nuevo para zona
    $acepta_mascotas = isset($_POST['acepta_mascotas']) ? 1 : 0;
    $piso = isset($_POST['piso']) && $_POST['piso'] !== '' ? intval($_POST['piso']) : null;
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $propietario_id = intval($_POST['propietario_id']);
    $departamento = isset($_POST['departamento']) ? trim($_POST['departamento']) : '';
    $ciudad = isset($_POST['ciudad']) ? trim($_POST['ciudad']) : '';
    $agente_id = $_SESSION['id_usuario'];
    $imagenes = [];
    $estado = 'Disponible'; // Estado por defecto

    // Asegurarse de que la carpeta uploads exista
    $uploadsDir = realpath(__DIR__ . '/../../uploads');
    if ($uploadsDir === false) {
        mkdir(__DIR__ . '/../../uploads', 0777, true);
        $uploadsDir = realpath(__DIR__ . '/../../uploads');
    }

    // Guardar múltiples imágenes
    if (isset($_FILES['imagenes']) && is_array($_FILES['imagenes']['name'])) {
        foreach ($_FILES['imagenes']['tmp_name'] as $idx => $tmpName) {
            if ($_FILES['imagenes']['error'][$idx] == UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['imagenes']['name'][$idx], PATHINFO_EXTENSION);
                $nombreArchivo = uniqid('prop_') . '.' . $ext;
                $rutaDestino = $uploadsDir . DIRECTORY_SEPARATOR . $nombreArchivo;
                if (move_uploaded_file($tmpName, $rutaDestino)) {
                    $imagenes[] = $nombreArchivo;
                }
            }
        }
    }

    $imagenesStr = implode(',', $imagenes);

    // Orden de columnas según la tabla:
    // titulo, descripcion, propietario_id, precio, ubicacion, imagen, tipo_inmueble, estrato, antiguedad, banos, habitaciones, parqueadero, zona, acepta_mascotas, piso, agente_id, departamento, ciudad, estado
    $stmt = $conn->prepare("INSERT INTO propiedades (titulo, descripcion, propietario_id, precio, ubicacion, imagen, tipo_inmueble, estrato, antiguedad, banos, habitaciones, parqueadero, zona, acepta_mascotas, piso, agente_id, departamento, ciudad, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssidssssiiiisssisss",
        $titulo,           // s (titulo)
        $descripcion,      // s (descripcion)
        $propietario_id,   // i (propietario_id)
        $precio,           // d (precio)
        $ubicacion,        // s (ubicacion)
        $imagenesStr,      // s (imagen)
        $tipo_inmueble,    // s (tipo_inmueble)
        $estrato,          // i (estrato)
        $antiguedad,       // i (antiguedad)
        $banos,            // i (banos)
        $habitaciones,     // i (habitaciones)
        $parqueadero,      // i (parqueadero)
        $zona,             // s (zona)
        $acepta_mascotas,  // i (acepta_mascotas)
        $piso,             // s (piso)
        $agente_id,        // s (agente_id)
        $departamento,     // s (departamento)
        $ciudad,           // s (ciudad)
        $estado            // s (estado)
    );
    if ($stmt->execute()) {
        $mensaje = "Propiedad registrada exitosamente.";
    } else {
        $mensaje = "Error al registrar la propiedad.";
    }
}

$agente_id = $_SESSION['id_usuario'];

// Eliminar propiedad si se recibe el parámetro 'eliminar'
if (isset($_GET['eliminar'])) {
    $idEliminar = intval($_GET['eliminar']);
    // Verificar si la propiedad tiene contratos asociados
    $checkContrato = $conn->prepare("SELECT COUNT(*) as total FROM contratos WHERE propiedad_id = ?");
    $checkContrato->bind_param("i", $idEliminar);
    $checkContrato->execute();
    $resContrato = $checkContrato->get_result();
    $rowContrato = $resContrato->fetch_assoc();
    if ($rowContrato['total'] > 0) {
        $mensaje = "No se puede eliminar la propiedad porque tiene contratos asociados.";
    } else {
        // Eliminar imagen asociada
        $imgQ = $conn->prepare("SELECT imagen FROM propiedades WHERE id = ? AND agente_id = ?");
        $imgQ->bind_param("ii", $idEliminar, $agente_id);
        $imgQ->execute();
        $imgRes = $imgQ->get_result();
        if ($img = $imgRes->fetch_assoc()) {
            if ($img['imagen'] && file_exists('../../uploads/' . $img['imagen'])) {
                unlink('../../uploads/' . $img['imagen']);
            }
        }
        $stmtDel = $conn->prepare("DELETE FROM propiedades WHERE id = ? AND agente_id = ?");
        $stmtDel->bind_param("ii", $idEliminar, $agente_id);
        $stmtDel->execute();
        header("Location: propiedades.php");
        exit();
    }
}

// Obtener datos de la propiedad a editar si se recibe 'editar'
$propiedadEditar = null;
if (isset($_GET['editar'])) {
    $idEditar = intval($_GET['editar']);
    $stmtEdit = $conn->prepare("SELECT * FROM propiedades WHERE id = ? AND agente_id = ?");
    $stmtEdit->bind_param("ii", $idEditar, $agente_id);
    $stmtEdit->execute();
    $resEdit = $stmtEdit->get_result();
    if ($resEdit->num_rows > 0) {
        $propiedadEditar = $resEdit->fetch_assoc();
    }
}

// Procesar edición de propiedad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modal_editar'])) {
    $id_prop = intval($_POST['id_propiedad']);
    $titulo = trim($_POST['titulo']);
    $tipo_inmueble = trim($_POST['tipo_inmueble']);
    $estrato = intval($_POST['estrato']);
    $antiguedad = intval($_POST['antiguedad']);
    $banos = intval($_POST['banos']);
    $habitaciones = intval($_POST['habitaciones']);
    $parqueadero = isset($_POST['parqueadero']) ? 1 : 0;
    $ubicacion = isset($_POST['ubicacion']) ? trim($_POST['ubicacion']) : '';
    $zona = isset($_POST['zona']) ? trim($_POST['zona']) : '';
    $acepta_mascotas = isset($_POST['acepta_mascotas']) ? 1 : 0;
    $piso = isset($_POST['piso']) && $_POST['piso'] !== '' ? intval($_POST['piso']) : null;
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $propietario_id = intval($_POST['propietario_id']);
    $departamento = isset($_POST['departamento']) ? trim($_POST['departamento']) : '';
    $ciudad = isset($_POST['ciudad']) ? trim($_POST['ciudad']) : '';
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'Disponible';
    // Recibe el orden final de imágenes desde el input oculto
    $imagenes_finales = isset($_POST['imagenes_orden']) ? explode(',', $_POST['imagenes_orden']) : [];
    $imagenes_eliminar = isset($_POST['imagenes_eliminar']) ? explode(',', $_POST['imagenes_eliminar']) : [];

    // Eliminar imágenes seleccionadas
    $uploadsDir = realpath(__DIR__ . '/../../uploads');
    foreach ($imagenes_eliminar as $img) {
        if ($img && file_exists($uploadsDir . DIRECTORY_SEPARATOR . $img)) {
            unlink($uploadsDir . DIRECTORY_SEPARATOR . $img);
        }
    }

    // Subir nuevas imágenes
    if (isset($_FILES['imagenes']) && is_array($_FILES['imagenes']['name'])) {
        foreach ($_FILES['imagenes']['tmp_name'] as $idx => $tmpName) {
            if ($_FILES['imagenes']['error'][$idx] == UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['imagenes']['name'][$idx], PATHINFO_EXTENSION);
                $nombreArchivo = uniqid('prop_') . '.' . $ext;
                $rutaDestino = $uploadsDir . DIRECTORY_SEPARATOR . $nombreArchivo;
                if (move_uploaded_file($tmpName, $rutaDestino)) {
                    $imagenes_finales[] = $nombreArchivo;
                }
            }
        }
    }

    // Eliminar imágenes vacías y reindexar
    $imagenes_finales = array_values(array_filter($imagenes_finales));
    $imagenesStr = implode(',', $imagenes_finales);

    // UPDATE siguiendo el orden exacto de la tabla:
    // id, agente_id, titulo, descripcion, propietario_id, precio, ubicacion, imagen, tipo_inmueble, estrato, antiguedad, banos, habitaciones, parqueadero, zona, acepta_mascotas, piso, departamento, ciudad, estado
    $stmt = $conn->prepare("UPDATE propiedades SET agente_id=?, titulo=?, descripcion=?, propietario_id=?, precio=?, ubicacion=?, imagen=?, tipo_inmueble=?, estrato=?, antiguedad=?, banos=?, habitaciones=?, parqueadero=?, zona=?, acepta_mascotas=?, piso=?, departamento=?, ciudad=?, estado=? WHERE id=? AND agente_id=?");
    $stmt->bind_param(
        "issidsssiiiiisiisssii", // Esta es la cadena correcta con 20 caracteres
        $agente_id,           
        $titulo,              
        $descripcion,         
        $propietario_id,      
        $precio,              
        $ubicacion,           
        $imagenesStr,         
        $tipo_inmueble,       
        $estrato,             
        $antiguedad,          
        $banos,               
        $habitaciones,        
        $parqueadero,         
        $zona,                
        $acepta_mascotas,     
        $piso,                
        $departamento,        
        $ciudad,              
        $estado,              // nuevo campo
        $id_prop,             
        $agente_id            
    );
    if ($stmt->execute()) {
        $mensaje = "Propiedad editada exitosamente.";
    } else {
        $mensaje = "Error al editar la propiedad.";
    }
    header("Location: propiedades.php");
    exit();
}

$query = "SELECT p.*, u.nombre_completo AS propietario_nombre 
          FROM propiedades p 
          LEFT JOIN usuarios u ON p.propietario_id = u.id 
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
    <title>Mis Propiedades</title>
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
            max-width: 1200px;
            margin: 0 auto 40px auto;
            border-radius: 18px;
            box-shadow: 0 6px 32px 0 rgba(60,72,88,0.12);
            background: #fff;
            border: none;
            padding: 2rem 2.5rem;
        }
        .property-card {
            border-radius: 18px;
            box-shadow: 0 4px 24px 0 rgba(139,92,246,0.10);
            overflow: hidden;
            margin-bottom: 32px;
            background: #fff;
            transition: box-shadow 0.18s, transform 0.18s;
            border: 1.5px solid #ede9fe;
            position: relative;
        }
        .property-card:hover {
            box-shadow: 0 8px 32px 0 rgba(139,92,246,0.18);
            transform: translateY(-6px) scale(1.025);
            border-color: #a78bfa;
        }
        .property-img {
            width: 100%;
            height: 210px;
            object-fit: cover;
            background: #e5e7eb;
            border-radius: 18px 18px 0 0;
        }
        .property-actions {
            display: flex;
            gap: 8px;
        }
        .property-card .card-body {
            padding: 1.5rem 1.5rem 1.2rem 1.5rem;
        }
        .property-card .card-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #6d28d9;
            margin-bottom: 0.5rem;
        }
        .property-card .card-text {
            color: #4b5563;
            margin-bottom: 1.1rem;
        }
        .property-card .mb-1 strong {
            color: #7c3aed;
        }
        .property-actions .btn-warning {
            background: #fbbf24;
            border: none;
            color: #fff;
            font-weight: 500;
        }
        .property-actions .btn-warning:hover {
            background: #f59e42;
            color: #fff;
        }
        .property-actions .btn-danger {
            background: #ef4444;
            border: none;
            font-weight: 500;
        }
        .property-actions .btn-danger:hover {
            background: #b91c1c;
        }
        .property-card {
            box-shadow: 0 4px 24px 0 rgba(139,92,246,0.10);
        }
        .property-card .property-actions {
            margin-top: 1.2rem;
        }
        @media (max-width: 900px) {
            .main-card { padding: 1rem; }
        }
        @media (max-width: 600px) {
            .property-img { height: 140px; }
            .property-card .card-body { padding: 1rem; }
        }
    </style>
    <script>
    // Departamentos y ciudades (debe coincidir con el array PHP)
    const departamentosCiudades = {
        "Antioquia": ["Medellín", "Envigado", "Bello", "Itagüí", "Rionegro"],
        "Cundinamarca": ["Bogotá", "Soacha", "Chía", "Zipaquirá", "Facatativá"],
        "Valle del Cauca": ["Cali", "Palmira", "Buenaventura", "Tuluá", "Cartago"],
        "Atlántico": ["Barranquilla", "Soledad", "Malambo", "Puerto Colombia", "Sabanalarga"],
        "Santander": ["Bucaramanga", "Floridablanca", "Girón", "Piedecuesta"],
        "Bolívar": ["Cartagena", "Magangué", "Turbaco", "Arjona"],
        "Nariño": ["Pasto", "Tumaco", "Ipiales"],
        "Caldas": ["Manizales", "La Dorada", "Villamaría"],
        "Risaralda": ["Pereira", "Dosquebradas", "Santa Rosa de Cabal"],
        "Quindío": ["Armenia", "Calarcá", "Montenegro"],
        "Meta": ["Villavicencio", "Acacías", "Granada"],
        "Huila": ["Neiva", "Pitalito", "Garzón"],
        "Cesar": ["Valledupar", "Aguachica", "Codazzi"],
        "Magdalena": ["Santa Marta", "Ciénaga", "Fundación"],
        "Boyacá": ["Tunja", "Duitama", "Sogamoso"],
        "Tolima": ["Ibagué", "Espinal", "Melgar"],
        "Norte de Santander": ["Cúcuta", "Ocaña", "Pamplona"],
        "Sucre": ["Sincelejo", "Corozal", "Sampués"],
        "Córdoba": ["Montería", "Lorica", "Sahagún"],
        "La Guajira": ["Riohacha", "Maicao", "Uribia"]
        // ...puedes seguir agregando más...
    };

    function actualizarCiudades(selectDeptoId, selectCiudadId, ciudadSeleccionada = "") {
        const depto = document.getElementById(selectDeptoId).value;
        const ciudadSelect = document.getElementById(selectCiudadId);
        ciudadSelect.innerHTML = '<option value="">Seleccione ciudad</option>';
        if (departamentosCiudades[depto]) {
            departamentosCiudades[depto].forEach(function(ciudad) {
                const selected = ciudad === ciudadSeleccionada ? "selected" : "";
                ciudadSelect.innerHTML += `<option value="${ciudad}" ${selected}>${ciudad}</option>`;
            });
        }
    }
    </script>
</head>
<body>
    <header class="header-agente text-center position-relative">
        <div class="avatar"><?php echo $inicial; ?></div>
        <h1>Bienvenido Agente</h1>
        <div class="nombre-usuario">Hola, <?php echo htmlspecialchars($nombreCompleto); ?></div>
    </header>
    <nav class="nav-agente mb-4">
        <a href="propiedades.php" class="active"><i class="bi bi-house-door"></i>Mis Propiedades</a>
        <a href="crear_contrato.php"><i class="bi bi-file-earmark-plus"></i>Crear Contrato</a>
        <a href="../../php/agente/registrar_pago.php"><i class="bi bi-cash-stack"></i>Registrar Pago</a>
        <a href="historial_pagos.php"><i class="bi bi-receipt"></i>Historial de Pagos</a>
        <a href="../../php/agente/historial_contratos.php"><i class="bi bi-clock-history"></i>Historial Contratos</a>
        <a href="reportes_agente.php"><i class="bi bi-bar-chart"></i>Reportes</a>
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="../vistas/agente.php" class="btn btn-secondary btn-back"><i class="bi bi-arrow-left"></i> Volver</a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrarPropiedad">
                    <i class="bi bi-plus-circle"></i> Registrar nueva propiedad
                </button>
            </div>
            <?php if ($mensaje): ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>
            <h2 class="mb-4">Mis Propiedades</h2>
            <div class="row">
                <?php if ($resultado->num_rows === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            No tienes propiedades registradas.
                        </div>
                    </div>
                <?php else: ?>
                <?php while ($propiedad = $resultado->fetch_assoc()): ?>
                <?php
                    $imagenes = array_filter(explode(',', $propiedad['imagen']));
                    $imgPrincipal = isset($imagenes[0]) && file_exists('../../uploads/' . $imagenes[0])
                        ? '../../uploads/' . $imagenes[0]
                        : 'https://via.placeholder.com/400x180?text=Sin+Imagen';
                    $descCorta = mb_strlen($propiedad['descripcion']) > 80
                        ? mb_substr($propiedad['descripcion'], 0, 80) . '...'
                        : $propiedad['descripcion'];
                    $estado_prop = isset($propiedad['estado']) ? $propiedad['estado'] : 'Disponible';

                    // Si hay contrato firmado, mostrar como "Ocupado (Firmado)"
                    $ocupadoFirmado = false;
                    $stmtContratoFirmado = $conn->prepare("SELECT id FROM contratos WHERE propiedad_id = ? AND estado = 'Firmado' LIMIT 1");
                    $stmtContratoFirmado->bind_param("i", $propiedad['id']);
                    $stmtContratoFirmado->execute();
                    $resContratoFirmado = $stmtContratoFirmado->get_result();
                    if ($resContratoFirmado->fetch_assoc()) {
                        $ocupadoFirmado = true;
                    }

                    // Consulta rápida para saber si tiene contrato vigente o pagos asociados
                    $contratoVigente = false;
                    $stmtContrato = $conn->prepare("SELECT c.id FROM contratos c WHERE c.propiedad_id = ? AND c.estado = 'Vigente' LIMIT 1");
                    $stmtContrato->bind_param("i", $propiedad['id']);
                    $stmtContrato->execute();
                    $resContrato = $stmtContrato->get_result();
                    $contratoId = null;
                    if ($rowC = $resContrato->fetch_assoc()) {
                        $contratoId = $rowC['id'];
                        $contratoVigente = true;
                    }
                    // Si hay contrato vigente, verificar si tiene pagos
                    $ocupado = false;
                    if ($contratoId) {
                        $stmtPagos = $conn->prepare("SELECT COUNT(*) as total FROM pagos WHERE contrato_id = ?");
                        $stmtPagos->bind_param("i", $contratoId);
                        $stmtPagos->execute();
                        $resPagos = $stmtPagos->get_result();
                        if ($rowP = $resPagos->fetch_assoc()) {
                            if ($rowP['total'] > 0) {
                                $ocupado = true;
                            }
                        }
                    }
                    // Mostrar "Ocupado" si hay contrato vigente y pagos, si no mostrar el estado original
                    if ($ocupadoFirmado) {
                        $estado_mostrar = '<span class="badge bg-success text-light">Ocupado (Firmado)</span>';
                    } elseif ($ocupado) {
                        $estado_mostrar = '<span class="badge bg-warning text-dark">Ocupado</span>';
                    } else {
                        $estado_mostrar = htmlspecialchars($estado_prop);
                    }
                ?>
                <div class="col-md-4">
                    <div class="property-card card">
                        <img src="<?= $imgPrincipal ?>" class="property-img card-img-top" alt="Imagen propiedad">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($propiedad['titulo']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($descCorta) ?></p>
                            <p class="mb-1"><strong>Habitaciones:</strong> <?= htmlspecialchars($propiedad['habitaciones']) ?></p>
                            <p class="mb-1"><strong>Baños:</strong> <?= htmlspecialchars($propiedad['banos']) ?></p>
                            <p class="mb-1"><strong>Estrato:</strong> <?= htmlspecialchars($propiedad['estrato']) ?></p>
                            <p class="mb-1"><strong>Precio:</strong> $<?= number_format($propiedad['precio'], 2) ?></p>
                            <p class="mb-1"><strong>Dirección:</strong> <?= htmlspecialchars($propiedad['ubicacion']) ?></p>
                            <p class="mb-1"><strong>Ciudad:</strong> <?= htmlspecialchars($propiedad['ciudad']) ?></p>
                            <p class="mb-1"><strong>Propietario:</strong> <?= htmlspecialchars($propiedad['propietario_nombre'] ?? 'No asignado') ?></p>
                            <p class="mb-1"><strong>Estado:</strong> <?= $estado_mostrar ?></p>
                            <div class="property-actions mt-2">
                                <button type="button" class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarPropiedad"
                                    onclick="editarPropiedad(
                                        <?= $propiedad['id'] ?>,
                                        '<?= htmlspecialchars(addslashes($propiedad['titulo'])) ?>',
                                        '<?= htmlspecialchars(addslashes($propiedad['descripcion'])) ?>',
                                        '<?= $propiedad['precio'] ?>',
                                        '<?= $propiedad['propietario_id'] ?>',
                                        '<?= htmlspecialchars(addslashes($propiedad['imagen'])) ?>',
                                        '<?= htmlspecialchars(addslashes($propiedad['tipo_inmueble'] ?? '')) ?>',
                                        '<?= htmlspecialchars(addslashes($propiedad['estrato'] ?? '')) ?>',
                                        '<?= htmlspecialchars(addslashes($propiedad['antiguedad'] ?? '')) ?>',
                                        '<?= htmlspecialchars(addslashes($propiedad['banos'] ?? '')) ?>',
                                        '<?= htmlspecialchars(addslashes($propiedad['habitaciones'] ?? '')) ?>',
                                        '<?= htmlspecialchars(addslashes($propiedad['parqueadero'] ?? '')) ?>',
                                        '<?= htmlspecialchars(addslashes($propiedad['zona'] ?? '')) ?>',
                                        '<?= htmlspecialchars(addslashes($propiedad['acepta_mascotas'] ?? '')) ?>',
                                        '<?= htmlspecialchars(addslashes($propiedad['piso'] ?? '')) ?>',
                                        '<?= htmlspecialchars(addslashes($propiedad['departamento'] ?? '')) ?>',
                                        '<?= htmlspecialchars(addslashes($propiedad['ciudad'] ?? '')) ?>',
                                        '<?= htmlspecialchars(addslashes($estado_prop)) ?>',
                                        '<?= htmlspecialchars(addslashes($propiedad['ubicacion'] ?? '')) ?>'
                                    )"
                                >Editar</button>
                                <a href="propiedades.php?eliminar=<?= $propiedad['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar esta propiedad?');">Eliminar</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de registro de propiedad -->
    <div class="modal fade" id="modalRegistrarPropiedad" tabindex="-1" aria-labelledby="modalRegistrarPropiedadLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <form method="post" class="modal-content" enctype="multipart/form-data">
            <input type="hidden" name="modal_registro" value="1">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRegistrarPropiedadLabel">Registrar Nueva Propiedad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Nombre/Título <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" id="titulo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_inmueble" class="form-label">Tipo de Inmueble <span class="text-danger">*</span></label>
                            <select name="tipo_inmueble" id="tipo_inmueble" class="form-select" required>
                                <option value="">Seleccione</option>
                                <option value="Casa">Casa</option>
                                <option value="Apartamento">Apartamento</option>
                                <option value="Apartaestudio">Apartaestudio</option>
                                <option value="Habitación">Habitación</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="estrato" class="form-label">
                                Estrato <span class="text-danger">*</span>
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="Nivel socioeconómico (Estrato): 1 = Bajo-Bajo, 2 = Bajo, 3 = Medio-Bajo, 4 = Medio, 5 = Medio-Alto, 6 = Alto"></i>
                            </label>
                            <input type="number" name="estrato" id="estrato" class="form-control" min="1" max="6" required>
                        </div>
                        <div class="mb-3">
                            <label for="antiguedad" class="form-label">Antigüedad (años) <span class="text-danger">*</span></label>
                            <input type="number" name="antiguedad" id="antiguedad" class="form-control" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="banos" class="form-label">Baños <span class="text-danger">*</span></label>
                            <input type="number" name="banos" id="banos" class="form-control" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="habitaciones" class="form-label">Habitaciones <span class="text-danger">*</span></label>
                            <input type="number" name="habitaciones" id="habitaciones" class="form-control" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="zona" class="form-label">Zona <span class="text-danger">*</span></label>
                            <select name="zona" id="zona" class="form-select" required>
                                <option value="">Seleccione</option>
                                <option value="Conjunto público">Conjunto público</option>
                                <option value="Conjunto privado">Conjunto privado</option>
                                <option value="Barrio">Barrio</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="departamento" class="form-label">Departamento <span class="text-danger">*</span></label>
                            <select name="departamento" id="departamento" class="form-select" required onchange="actualizarCiudades('departamento','ciudad')">
                                <option value="">Seleccione departamento</option>
                                <?php foreach ($departamentos_ciudades as $depto => $ciudades): ?>
                                    <option value="<?php echo htmlspecialchars($depto); ?>"><?php echo htmlspecialchars($depto); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="ciudad" class="form-label">Ciudad <span class="text-danger">*</span></label>
                            <select name="ciudad" id="ciudad" class="form-select" required>
                                <option value="">Seleccione ciudad</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="ubicacion" class="form-label">Dirección <span class="text-danger">*</span></label>
                            <input type="text" name="ubicacion" id="ubicacion" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="piso" class="form-label">Número de piso (si aplica)</label>
                            <input type="number" name="piso" id="piso" class="form-control" min="0">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="parqueadero" id="parqueadero" class="form-check-input">
                            <label for="parqueadero" class="form-check-label">Parqueadero</label>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="acepta_mascotas" id="acepta_mascotas" class="form-check-input">
                            <label for="acepta_mascotas" class="form-check-label">Acepta mascotas</label>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="precio" id="precio" class="form-control" step="0.01" required placeholder="500000000">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="propietario_id" class="form-label">Propietario <span class="text-danger">*</span></label>
                            <select name="propietario_id" id="propietario_id" class="form-select" required>
                                <option value="">Seleccione un propietario</option>
                                <?php foreach ($propietarios as $prop): ?>
                                    <option value="<?php echo $prop['id']; ?>"><?php echo htmlspecialchars($prop['nombre_completo']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="imagenes" class="form-label">Imágenes</label>
                            <input type="file" name="imagenes[]" id="imagenes" class="form-control" accept="image/*" multiple>
                            <small class="text-muted">Puedes seleccionar varias imágenes.</small>
                            <div id="progressContainer" class="mt-2" style="display:none;">
                                <div class="progress">
                                    <div id="progressBar" class="progress-bar" role="progressbar" style="width:0%">0%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Registrar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </form>
      </div>
    </div>

    <!-- Modal de edición de propiedad -->
    <div class="modal fade" id="modalEditarPropiedad" tabindex="-1" aria-labelledby="modalEditarPropiedadLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <form method="post" class="modal-content" enctype="multipart/form-data" id="formEditarPropiedad">
            <input type="hidden" name="modal_editar" value="1">
            <input type="hidden" name="id_propiedad" id="edit_id_propiedad">
            <input type="hidden" name="imagenes_orden" id="edit_imagenes_orden">
            <input type="hidden" name="imagenes_eliminar" id="edit_imagenes_eliminar">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarPropiedadLabel">Editar Propiedad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit_titulo" class="form-label">Nombre/Título <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" id="edit_titulo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_tipo_inmueble" class="form-label">Tipo de Inmueble <span class="text-danger">*</span></label>
                            <select name="tipo_inmueble" id="edit_tipo_inmueble" class="form-select" required>
                                <option value="">Seleccione</option>
                                <option value="Casa">Casa</option>
                                <option value="Apartamento">Apartamento</option>
                                <option value="Apartaestudio">Apartaestudio</option>
                                <option value="Habitación">Habitación</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_estrato" class="form-label">
                                Estrato <span class="text-danger">*</span>
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="Nivel socioeconómico (Estrato): 1 = Bajo-Bajo, 2 = Bajo, 3 = Medio-Bajo, 4 = Medio, 5 = Medio-Alto, 6 = Alto"></i>
                            </label>
                            <input type="number" name="estrato" id="edit_estrato" class="form-control" min="1" max="6" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_antiguedad" class="form-label">Antigüedad (años) <span class="text-danger">*</span></label>
                            <input type="number" name="antiguedad" id="edit_antiguedad" class="form-control" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_banos" class="form-label">Baños <span class="text-danger">*</span></label>
                            <input type="number" name="banos" id="edit_banos" class="form-control" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_habitaciones" class="form-label">Habitaciones <span class="text-danger">*</span></label>
                            <input type="number" name="habitaciones" id="edit_habitaciones" class="form-control" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_zona" class="form-label">Zona <span class="text-danger">*</span></label>
                            <select name="zona" id="edit_zona" class="form-select" required>
                                <option value="">Seleccione</option>
                                <option value="Conjunto público">Conjunto público</option>
                                <option value="Conjunto privado">Conjunto privado</option>
                                <option value="Barrio">Barrio</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_departamento" class="form-label">Departamento <span class="text-danger">*</span></label>
                            <select name="departamento" id="edit_departamento" class="form-select" required onchange="actualizarCiudades('edit_departamento','edit_ciudad')">
                                <option value="">Seleccione departamento</option>
                                <?php foreach ($departamentos_ciudades as $depto => $ciudades): ?>
                                    <option value="<?php echo htmlspecialchars($depto); ?>"><?php echo htmlspecialchars($depto); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_ciudad" class="form-label">Ciudad <span class="text-danger">*</span></label>
                            <select name="ciudad" id="edit_ciudad" class="form-select" required>
                                <option value="">Seleccione ciudad</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_estado" class="form-label">Estado <span class="text-danger">*</span></label>
                            <select name="estado" id="edit_estado" class="form-select" required>
                                <option value="Disponible">Disponible</option>
                                <option value="Arrendando">Arrendando</option>
                                <option value="Remodelación">Remodelación</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_ubicacion" class="form-label">Dirección <span class="text-danger">*</span></label>
                            <input type="text" name="ubicacion" id="edit_ubicacion" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit_piso" class="form-label">Número de piso (si aplica)</label>
                            <input type="number" name="piso" id="edit_piso" class="form-control" min="0">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="parqueadero" id="edit_parqueadero" class="form-check-input">
                            <label for="edit_parqueadero" class="form-check-label">Parqueadero</label>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="acepta_mascotas" id="edit_acepta_mascotas" class="form-check-input">
                            <label for="edit_acepta_mascotas" class="form-check-label">Acepta mascotas</label>
                        </div>
                        <div class="mb-3">
                            <label for="edit_descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                            <textarea name="descripcion" id="edit_descripcion" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_precio" class="form-label">Precio <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="precio" id="edit_precio" class="form-control" step="0.01" required placeholder="500000000">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_propietario_id" class="form-label">Propietario <span class="text-danger">*</span></label>
                            <select name="propietario_id" id="edit_propietario_id" class="form-select" required>
                                <option value="">Seleccione un propietario</option>
                                <?php foreach ($propietarios as $prop): ?>
                                    <option value="<?php echo $prop['id']; ?>"><?php echo htmlspecialchars($prop['nombre_completo']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Imágenes actuales</label>
                            <div id="edit_imagenes_preview" class="d-flex flex-wrap gap-2"></div>
                            <small class="text-muted">Arrastra las imágenes para cambiar el orden. Haz clic en la X para eliminar.</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_imagenes" class="form-label">Agregar nuevas imágenes</label>
                            <input type="file" name="imagenes[]" id="edit_imagenes" class="form-control" accept="image/*" multiple>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </form>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Drag & drop para reordenar imágenes en edición
    let imagenesEditar = [];
    let imagenesEliminar = [];

    function editarPropiedad(id, titulo, descripcion, precio, propietario_id, imagenesStr, tipo_inmueble, estrato, antiguedad, banos, habitaciones, parqueadero, zona, acepta_mascotas, piso, departamento = '', ciudad = '', estado = 'Disponible', ubicacion = '') {
        document.getElementById('edit_id_propiedad').value = id;
        document.getElementById('edit_titulo').value = titulo;
        document.getElementById('edit_descripcion').value = descripcion;
        document.getElementById('edit_precio').value = precio;
        document.getElementById('edit_propietario_id').value = propietario_id;
        document.getElementById('edit_tipo_inmueble').value = tipo_inmueble;
        document.getElementById('edit_estrato').value = estrato;
        document.getElementById('edit_antiguedad').value = antiguedad;
        document.getElementById('edit_banos').value = banos;
        document.getElementById('edit_habitaciones').value = habitaciones;
        document.getElementById('edit_zona').value = zona;
        document.getElementById('edit_piso').value = piso !== null && piso !== undefined ? piso : '';
        document.getElementById('edit_parqueadero').checked = parqueadero == 1;
        document.getElementById('edit_acepta_mascotas').checked = acepta_mascotas == 1;
        document.getElementById('edit_departamento').value = departamento;
        actualizarCiudades('edit_departamento', 'edit_ciudad', ciudad);
        // Estado
        if (document.getElementById('edit_estado')) {
            document.getElementById('edit_estado').value = estado;
            // Si está arrendando, bloquear edición
            if (estado === 'Arrendando') {
                document.getElementById('edit_estado').setAttribute('disabled', 'disabled');
            } else {
                document.getElementById('edit_estado').removeAttribute('disabled');
            }
        }
        document.getElementById('edit_ubicacion').value = ubicacion;

        imagenesEditar = imagenesStr ? imagenesStr.split(',').filter(Boolean) : [];
        imagenesEliminar = [];
        renderImagenesPreview();
        document.getElementById('edit_imagenes_orden').value = imagenesEditar.join(',');
        document.getElementById('edit_imagenes_eliminar').value = '';
    }

    function renderImagenesPreview() {
        let preview = document.getElementById('edit_imagenes_preview');
        preview.innerHTML = '';
        imagenesEditar.forEach(function(img, idx) {
            if (!img) return;
            let div = document.createElement('div');
            div.className = 'position-relative imagen-draggable';
            div.style.width = '90px';
            div.style.height = '70px';
            div.style.cursor = 'grab';
            div.setAttribute('draggable', 'true');
            div.setAttribute('data-idx', idx);
            div.innerHTML = `
                <img src="../../uploads/${img}" alt="Imagen" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" style="padding:2px 6px;font-size:0.9rem;" onclick="eliminarImagenEditar('${img}', this)">
                    <i class="bi bi-x"></i>
                </button>
            `;
            // Drag events
            div.ondragstart = function(e) {
                e.dataTransfer.setData('text/plain', idx);
            };
            div.ondragover = function(e) {
                e.preventDefault();
                div.style.border = '2px dashed #a78bfa';
            };
            div.ondragleave = function(e) {
                div.style.border = '';
            };
            div.ondrop = function(e) {
                e.preventDefault();
                div.style.border = '';
                let fromIdx = parseInt(e.dataTransfer.getData('text/plain'));
                let toIdx = parseInt(div.getAttribute('data-idx'));
                if (fromIdx !== toIdx) {
                    let temp = imagenesEditar[fromIdx];
                    imagenesEditar[fromIdx] = imagenesEditar[toIdx];
                    imagenesEditar[toIdx] = temp;
                    renderImagenesPreview();
                    document.getElementById('edit_imagenes_orden').value = imagenesEditar.join(',');
                }
            };
            preview.appendChild(div);
        });
        document.getElementById('edit_imagenes_orden').value = imagenesEditar.join(',');
    }

    function eliminarImagenEditar(img, btn) {
        // Agregar a la lista de imágenes a eliminar
        if (!imagenesEliminar.includes(img)) {
            imagenesEliminar.push(img);
        }
        // Quitar de la lista de imágenes a mostrar
        imagenesEditar = imagenesEditar.filter(function(i) { return i !== img; });
        renderImagenesPreview();
        document.getElementById('edit_imagenes_eliminar').value = imagenesEliminar.join(',');
    }

    // Tooltip Bootstrap para estrato
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Indicador de progreso para carga de imágenes
    document.getElementById('imagenes').addEventListener('change', function(e) {
        var files = e.target.files;
        var progressContainer = document.getElementById('progressContainer');
        var progressBar = document.getElementById('progressBar');
        if (files.length > 0) {
            progressContainer.style.display = 'block';
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';

            // Simulación de progreso (solo visual, no real upload AJAX)
            var percent = 0;
            var interval = setInterval(function() {
                percent += 10;
                if (percent > 100) percent = 100;
                progressBar.style.width = percent + '%';
                progressBar.textContent = percent + '%';
                if (percent === 100) {
                    clearInterval(interval);
                    setTimeout(function() {
                        progressContainer.style.display = 'none';
                        // Mostrar previsualización de imágenes seleccionadas
                        mostrarPreviewImagenes(files);
                    }, 300);
                }
            }, 80);
        } else {
            progressContainer.style.display = 'none';
            document.getElementById('previewImagenesNuevas')?.remove();
        }
    });

    // Mostrar previsualización de imágenes seleccionadas en el registro
    function mostrarPreviewImagenes(files) {
        // Elimina previsualización anterior si existe
        document.getElementById('previewImagenesNuevas')?.remove();
        var container = document.createElement('div');
        container.id = 'previewImagenesNuevas';
        container.className = 'd-flex flex-wrap gap-2 mt-2';
        for (let i = 0; i < files.length; i++) {
            let file = files[i];
            let reader = new FileReader();
            let div = document.createElement('div');
            div.style.width = '90px';
            div.style.height = '70px';
            div.style.overflow = 'hidden';
            div.style.borderRadius = '6px';
            div.style.background = '#e5e7eb';
            div.style.display = 'flex';
            div.style.alignItems = 'center';
            div.style.justifyContent = 'center';
            reader.onload = function(e) {
                let img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '6px';
                div.appendChild(img);
            };
            reader.readAsDataURL(file);
            container.appendChild(div);
        }
        // Insertar después del input de imágenes
        var input = document.getElementById('imagenes');
        input.parentNode.appendChild(container);
    }
    </script>
    <?php if ($mensaje): ?>
    <script>
        // Si hubo registro, cerrar el modal automáticamente
        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRegistrarPropiedad'));
        modal.hide();
    </script>
    <?php endif; ?>
</body>
</html>
