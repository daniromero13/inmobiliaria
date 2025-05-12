<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: ../login.php');
    exit();
}
$nombreCompleto = isset($_SESSION['nombre_completo']) ? trim($_SESSION['nombre_completo']) : 'Administrador';
$inicial = strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8'));
include '../../config/db.php';

// Obtener todos los usuarios
$usuarios = [];
$res = $conn->query("SELECT id, nombre_completo, correo, rol_id FROM usuarios ORDER BY id DESC");
while ($row = $res->fetch_assoc()) {
    $usuarios[] = $row;
}

// Roles
$roles = [
    1 => 'Administrador',
    2 => 'Propietario',
    3 => 'Arrendatario',
    4 => 'Agente'
];

// Eliminar usuario
if (isset($_GET['eliminar'])) {
    $idEliminar = intval($_GET['eliminar']);
    if ($idEliminar != $_SESSION['id_usuario']) { // No permitir eliminarse a sí mismo
        $conn->query("DELETE FROM usuarios WHERE id = $idEliminar");
        header("Location: usuarios.php");
        exit();
    }
}

// Editar usuario
$usuarioEditar = null;
if (isset($_GET['editar'])) {
    $idEditar = intval($_GET['editar']);
    $resEdit = $conn->query("SELECT id, nombre_completo, correo, rol_id FROM usuarios WHERE id = $idEditar");
    if ($resEdit && $resEdit->num_rows > 0) {
        $usuarioEditar = $resEdit->fetch_assoc();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_usuario'])) {
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre_completo']);
    $correo = trim($_POST['correo']);
    $rol = intval($_POST['rol_id']);
    $stmt = $conn->prepare("UPDATE usuarios SET nombre_completo=?, correo=?, rol_id=? WHERE id=?");
    $stmt->bind_param("ssii", $nombre, $correo, $rol, $id);
    $stmt->execute();
    header("Location: usuarios.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
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
            max-width: 1000px;
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
        <a href="usuarios.php" class="active"><i class="bi bi-people"></i>Usuarios</a>
        <a href="propiedades.php"><i class="bi bi-house-door"></i>Propiedades</a>
        <a href="contratos.php"><i class="bi bi-file-earmark-text"></i>Contratos</a>
        <a href="pagos.php"><i class="bi bi-cash-stack"></i>Pagos</a>
        <a href="reportes_admin.php"><i class="bi bi-bar-chart"></i>Reportes</a>
        <a href="../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card shadow">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="../vistas/admin.php" class="btn btn-secondary btn-back">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <h2 class="mb-0 text-center flex-grow-1" style="font-size:1.7rem;">Gestión de Usuarios</h2>
                <span style="width: 90px;"></span>
            </div>
            <div class="mb-3">
                <input type="text" id="buscador" class="form-control" placeholder="Buscar usuario por nombre, correo o rol...">
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaUsuarios">
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['id']) ?></td>
                            <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
                            <td><?= htmlspecialchars($u['correo']) ?></td>
                            <td><?= htmlspecialchars($roles[$u['rol_id']] ?? 'Desconocido') ?></td>
                            <td>
                                <a href="usuarios.php?editar=<?= $u['id'] ?>" class="btn btn-sm btn-info"><i class="bi bi-pencil"></i> Editar</a>
                                <?php if ($u['id'] != $_SESSION['id_usuario']): ?>
                                <a href="usuarios.php?eliminar=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');"><i class="bi bi-trash"></i> Eliminar</a>
                                <?php else: ?>
                                <span class="text-muted" title="No puedes eliminarte a ti mismo"><i class="bi bi-lock"></i></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay usuarios registrados.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de edición -->
    <?php if ($usuarioEditar): ?>
    <div class="modal fade show" id="modalEditarUsuario" tabindex="-1" style="display:block;background:rgba(0,0,0,0.2);" aria-modal="true" role="dialog">
      <div class="modal-dialog">
        <form method="post" class="modal-content">
            <input type="hidden" name="editar_usuario" value="1">
            <input type="hidden" name="id" value="<?= $usuarioEditar['id'] ?>">
            <div class="modal-header">
                <h5 class="modal-title">Editar Usuario</h5>
                <a href="usuarios.php" class="btn-close"></a>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="nombre_completo" class="form-label">Nombre Completo</label>
                    <input type="text" name="nombre_completo" class="form-control" required value="<?= htmlspecialchars($usuarioEditar['nombre_completo']) ?>">
                </div>
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo</label>
                    <input type="email" name="correo" class="form-control" required value="<?= htmlspecialchars($usuarioEditar['correo']) ?>">
                </div>
                <div class="mb-3">
                    <label for="rol_id" class="form-label">Rol</label>
                    <select name="rol_id" class="form-select" required>
                        <?php foreach ($roles as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $usuarioEditar['rol_id'] == $k ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
      </div>
      <script>
        // Cerrar modal con escape o click fuera
        document.addEventListener('keydown', function(e){if(e.key==='Escape'){window.location='usuarios.php';}});
        document.addEventListener('click', function(e){
            if(e.target.classList.contains('modal')){window.location='usuarios.php';}
        });
      </script>
    </div>
    <?php endif; ?>

    <script>
    document.getElementById('buscador').addEventListener('input', function() {
        const texto = this.value.toLowerCase();
        const filas = document.querySelectorAll('#tablaUsuarios tr');
        filas.forEach(function(fila) {
            const contenido = fila.textContent.toLowerCase();
            fila.style.display = contenido.includes(texto) ? '' : 'none';
        });
    });
    </script>
</body>
</html>
