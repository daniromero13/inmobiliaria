<!-- filepath: c:\xampp\htdocs\inmobiliaria\php\vistas\propiedades.php -->
<?php
session_start();
include '../../config/db.php'; // Conexión a la base de datos

// Opciones de tipo de inmueble
$tipos_inmueble = [
    "Casa",
    "Apartamento",
    "Apartaestudio",
    "Habitación"
];

// Opciones de departamentos y ciudades (igual que en agente)
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
];

// Obtener valores de filtros
$tipo = $_GET['tipo'] ?? '';
$habitaciones = $_GET['habitaciones'] ?? '';
$banos = $_GET['banos'] ?? '';
$departamento = $_GET['departamento'] ?? '';
$ciudad = $_GET['ciudad'] ?? '';

// Construir consulta con filtros
$where = [];
if ($tipo !== '') $where[] = "p.tipo_inmueble = '" . $conn->real_escape_string($tipo) . "'";
if ($habitaciones !== '') $where[] = "p.habitaciones = " . intval($habitaciones);
if ($banos !== '') $where[] = "p.banos = " . intval($banos);
if ($departamento !== '') $where[] = "p.departamento = '" . $conn->real_escape_string($departamento) . "'";
if ($ciudad !== '') $where[] = "p.ciudad = '" . $conn->real_escape_string($ciudad) . "'";

$whereSql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "SELECT p.*, u.nombre_completo AS propietario_nombre, a.nombre_completo AS agente_nombre
          FROM propiedades p
          LEFT JOIN usuarios u ON p.propietario_id = u.id
          LEFT JOIN usuarios a ON p.agente_id = a.id
          $whereSql";
$resultado = $conn->query($query);

// Opciones únicas para habitaciones y baños
$habitaciones_opts = $conn->query("SELECT DISTINCT habitaciones FROM propiedades WHERE habitaciones IS NOT NULL AND habitaciones != '' ORDER BY habitaciones");
$banos_opts = $conn->query("SELECT DISTINCT banos FROM propiedades WHERE banos IS NOT NULL AND banos != '' ORDER BY banos");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propiedades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border: none;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .propiedad-arrendando {
            pointer-events: none;
            opacity: 0.7;
        }
        .propiedad-arrendando .card-body {
            pointer-events: auto;
        }
        .propiedad-arrendando .btn-ver-detalles {
            pointer-events: auto;
            opacity: 1;
        }
        .propiedad-arrendando:hover {
            /* No efecto hover */
            transform: none;
        }
        .property-img.arrendando-img {
            filter: grayscale(100%);
        }
        .property-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: #e5e7eb;
        }
        .property-details-list {
            font-size: 0.97rem;
            margin-bottom: 0.5rem;
            padding-left: 0;
            list-style: none;
        }
        .property-details-list li {
            display: inline-block;
            margin-right: 12px;
        }
        .propiedad-arriendo {
            pointer-events: none;
            opacity: 0.7;
        }
        .property-img.arriendo-img {
            filter: grayscale(100%);
        }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Inmobiliaria</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../../html/index.html">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link active" href="#">Propiedades</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Contratos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Reportes</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Propiedades Disponibles</h2>
    </div>

    <!-- Filtros -->
    <div class="container mt-4">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label for="tipo" class="form-label">Tipo</label>
                <select name="tipo" id="tipo" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach($tipos_inmueble as $tipo_op): ?>
                        <option value="<?= htmlspecialchars($tipo_op) ?>" <?= $tipo === $tipo_op ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo_op) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="habitaciones" class="form-label">Habitaciones</label>
                <select name="habitaciones" id="habitaciones" class="form-select">
                    <option value="">Todas</option>
                    <?php while($row = $habitaciones_opts->fetch_assoc()): ?>
                        <option value="<?= $row['habitaciones'] ?>" <?= $habitaciones !== '' && $habitaciones == $row['habitaciones'] ? 'selected' : '' ?>>
                            <?= $row['habitaciones'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="banos" class="form-label">Baños</label>
                <select name="banos" id="banos" class="form-select">
                    <option value="">Todos</option>
                    <?php while($row = $banos_opts->fetch_assoc()): ?>
                        <option value="<?= $row['banos'] ?>" <?= $banos !== '' && $banos == $row['banos'] ? 'selected' : '' ?>>
                            <?= $row['banos'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="departamento" class="form-label">Departamento</label>
                <select name="departamento" id="departamento" class="form-select" onchange="actualizarCiudades('departamento','ciudad')">
                    <option value="">Todos</option>
                    <?php foreach ($departamentos_ciudades as $depto => $ciudadesArr): ?>
                        <option value="<?= htmlspecialchars($depto) ?>" <?= $departamento === $depto ? 'selected' : '' ?>>
                            <?= htmlspecialchars($depto) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="ciudad" class="form-label">Ciudad</label>
                <select name="ciudad" id="ciudad" class="form-select">
                    <option value="">Todas</option>
                    <?php
                    if ($departamento && isset($departamentos_ciudades[$departamento])) {
                        foreach ($departamentos_ciudades[$departamento] as $ciudad_op) {
                            $selected = $ciudad === $ciudad_op ? 'selected' : '';
                            echo '<option value="'.htmlspecialchars($ciudad_op).'" '.$selected.'>'.htmlspecialchars($ciudad_op).'</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="propiedades.php" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>
    <script>
    // Igual que en agente: departamentos y ciudades
    const departamentosCiudades = <?php echo json_encode($departamentos_ciudades); ?>;
    function actualizarCiudades(selectDeptoId, selectCiudadId) {
        const depto = document.getElementById(selectDeptoId).value;
        const ciudadSelect = document.getElementById(selectCiudadId);
        ciudadSelect.innerHTML = '<option value="">Todas</option>';
        if (departamentosCiudades[depto]) {
            departamentosCiudades[depto].forEach(function(ciudad) {
                ciudadSelect.innerHTML += `<option value="${ciudad}">${ciudad}</option>`;
            });
        }
    }
    // Si hay un departamento seleccionado, cargar ciudades al cargar la página
    window.addEventListener('DOMContentLoaded', function() {
        var deptoSel = document.getElementById('departamento').value;
        var ciudadSel = "<?= htmlspecialchars($ciudad) ?>";
        if (deptoSel) {
            actualizarCiudades('departamento', 'ciudad');
            if (ciudadSel) {
                document.getElementById('ciudad').value = ciudadSel;
            }
        }
    });
    </script>

    <!-- Lista de Propiedades -->
    <div class="container mt-5">
        <div class="row">
            <?php while ($propiedad = $resultado->fetch_assoc()): ?>
                <?php
                    $estado = strtolower(trim($propiedad['estado']));
                    $enArrendando = ($estado === 'arrendando');
                    $cardClass = $enArrendando ? 'card mb-4 propiedad-arrendando' : 'card mb-4';
                    $imgClass = 'property-img' . ($enArrendando ? ' arrendando-img' : '');
                    $btnDisabled = ''; // Siempre se puede ver detalles
                ?>
                <div class="col-md-4">
                    <div class="<?= $cardClass ?>">
                        <?php
                        $imgPath = (!empty($propiedad['imagen']) && file_exists(__DIR__ . '/../../uploads/' . $propiedad['imagen']))
                            ? '../../uploads/' . $propiedad['imagen']
                            : 'https://via.placeholder.com/400x180?text=Sin+Imagen';
                        ?>
                        <img src="<?= $imgPath ?>" class="card-img-top <?= $imgClass ?>" alt="Propiedad">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($propiedad['titulo']) ?></h5>
                            <?php
                                $badgeClass = 'bg-secondary';
                                if ($estado === 'arrendando') {
                                    $badgeClass = 'bg-danger';
                                } elseif ($estado === 'disponible') {
                                    $badgeClass = 'bg-success';
                                } elseif ($estado === 'remodelación' || $estado === 'remodelacion') {
                                    $badgeClass = 'bg-warning text-dark';
                                }
                            ?>
                            <span class="badge <?= $badgeClass ?> mb-2"><?= htmlspecialchars($propiedad['estado']) ?></span>
                            <ul class="property-details-list">
                                <li><strong>Precio:</strong> $<?= number_format($propiedad['precio'], 2) ?></li>
                                <?php if (!empty($propiedad['ubicacion'])): ?>
                                    <li><strong>Dirección:</strong> <?= htmlspecialchars($propiedad['ubicacion']) ?></li>
                                <?php endif; ?>
                            </ul>
                            <ul class="property-details-list">
                                <?php if (!empty($propiedad['habitaciones'])): ?>
                                    <li><?= intval($propiedad['habitaciones']) ?> hab.</li>
                                <?php endif; ?>
                                <?php if (!empty($propiedad['banos'])): ?>
                                    <li><?= intval($propiedad['banos']) ?> baños</li>
                                <?php endif; ?>
                                <?php if (!empty($propiedad['metros_cuadrados'])): ?>
                                    <li><?= intval($propiedad['metros_cuadrados']) ?> m²</li>
                                <?php endif; ?>
                                <?php if (!empty($propiedad['estrato'])): ?>
                                    <li>Estrato <?= htmlspecialchars($propiedad['estrato']) ?></li>
                                <?php endif; ?>
                                <?php if (!empty($propiedad['tipo'])): ?>
                                    <li><?= htmlspecialchars($propiedad['tipo']) ?></li>
                                <?php endif; ?>
                                <?php if (!empty($propiedad['ciudad'])): ?>
                                    <li><?= htmlspecialchars($propiedad['ciudad']) ?></li>
                                <?php endif; ?>
                                <?php if (!empty($propiedad['barrio'])): ?>
                                    <li><?= htmlspecialchars($propiedad['barrio']) ?></li>
                                <?php endif; ?>
                            </ul>
                            <p class="mb-1"><strong>Agente:</strong> <?= htmlspecialchars($propiedad['agente_nombre'] ?? 'No asignado') ?></p>
                            <p class="mb-2"><strong>Propietario:</strong> <?= htmlspecialchars($propiedad['propietario_nombre'] ?? 'No asignado') ?></p>
                            <p class="card-text"><?= htmlspecialchars($propiedad['descripcion']) ?></p>
                            <button class="btn btn-primary btn-ver-detalles" 
                                data-bs-toggle="modal"
                                data-bs-target="#modalDetallePropiedad"
                                onclick="mostrarDetalle(
                                    '<?= htmlspecialchars(addslashes($propiedad['titulo'])) ?>',
                                    '<?= htmlspecialchars(addslashes($propiedad['descripcion'])) ?>',
                                    '<?= $imgPath ?>',
                                    '<?= htmlspecialchars(addslashes($propiedad['agente_nombre'] ?? 'No asignado')) ?>',
                                    '<?= htmlspecialchars(addslashes($propiedad['propietario_nombre'] ?? 'No asignado')) ?>',
                                    '<?= number_format($propiedad['precio'], 2) ?>',
                                    '<?= htmlspecialchars(addslashes($propiedad['ubicacion'] ?? '')) ?>',
                                    '<?= intval($propiedad['habitaciones'] ?? 0) ?>',
                                    '<?= intval($propiedad['banos'] ?? 0) ?>',
                                    '<?= intval($propiedad['metros_cuadrados'] ?? 0) ?>',
                                    '<?= htmlspecialchars(addslashes($propiedad['estrato'] ?? '')) ?>',
                                    '<?= htmlspecialchars(addslashes($propiedad['tipo'] ?? '')) ?>',
                                    '<?= htmlspecialchars(addslashes($propiedad['ciudad'] ?? '')) ?>',
                                    '<?= htmlspecialchars(addslashes($propiedad['barrio'] ?? '')) ?>',
                                    '<?= htmlspecialchars($propiedad['estado']) ?>'
                                )"
                            >Ver Detalles</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Modal Detalle Propiedad -->
    <div class="modal fade" id="modalDetallePropiedad" tabindex="-1" aria-labelledby="modalDetallePropiedadLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalDetallePropiedadLabel">Detalle de la Propiedad</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <img id="detalle_imagen" src="" alt="Imagen propiedad" class="img-fluid rounded" style="max-height:260px;">
                </div>
                <div class="col-md-6">
                    <h4 id="detalle_titulo"></h4>
                    <p id="detalle_descripcion"></p>
                    <ul class="property-details-list">
                        <li><strong>Precio:</strong> $<span id="detalle_precio"></span></li>
                        <li><strong>Dirección:</strong> <span id="detalle_direccion"></span></li>
                        <li><strong>Ciudad:</strong> <span id="detalle_ciudad"></span></li>
                        <li><strong>Estado:</strong> <span id="detalle_estado"></span></li>
                    </ul>
                    <ul class="property-details-list">
                        <li><span id="detalle_habitaciones"></span> hab.</li>
                        <li><span id="detalle_banos"></span> baños</li>
                        <li>Estrato <span id="detalle_estrato"></span></li>
                        <li><span id="detalle_tipo"></span></li>
                    </ul>
                    <p><strong>Agente:</strong> <span id="detalle_agente"></span></p>
                    <p><strong>Propietario:</strong> <span id="detalle_propietario"></span></p>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <footer class="text-white text-center py-4 mt-5 bg-dark" style="width:100%;">
        <p>&copy; 2025 Sistema de Gestión de Propiedades Inmobiliarias. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function mostrarDetalle(titulo, descripcion, imagen, agente, propietario, precio, direccion, habitaciones, banos, metros, estrato, tipo, ciudad, barrio, estado) {
        document.getElementById('detalle_titulo').textContent = titulo;
        document.getElementById('detalle_descripcion').textContent = descripcion;
        document.getElementById('detalle_imagen').src = imagen;
        document.getElementById('detalle_agente').textContent = agente;
        document.getElementById('detalle_propietario').textContent = propietario;
        document.getElementById('detalle_precio').textContent = precio;
        document.getElementById('detalle_direccion').textContent = direccion;
        document.getElementById('detalle_habitaciones').textContent = habitaciones;
        document.getElementById('detalle_banos').textContent = banos;
        document.getElementById('detalle_estrato').textContent = estrato;
        document.getElementById('detalle_tipo').textContent = tipo;
        document.getElementById('detalle_ciudad').textContent = ciudad;
        document.getElementById('detalle_barrio').textContent = barrio;
        document.getElementById('detalle_estado').textContent = estado;
    }
    </script>
</body>
</html>