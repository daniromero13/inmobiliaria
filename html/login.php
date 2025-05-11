<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión - Inmobiliaria</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"> <!-- Iconos Bootstrap -->
  <style>
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      font-size: 14px; /* Reducido */
    }
    .split {
      height: 100%;
      display: flex;
      position: relative;
    }
    .left {
      flex: 1;
      background: url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c') no-repeat center center;
      background-size: cover;
    }
    .right {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #273746;
    }
    .login-form {
      width: 100%;
      max-width: 400px; /* Reducido */
      background: #ffffff;
      padding: 2rem;    /* Reducido */
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      font-size: 1rem; /* Reducido */
    }
    .login-form .form-label {
      font-size: 1rem; /* Reducido */
    }
    .login-form .form-control {
      font-size: 1rem; /* Reducido */
      padding: 0.5rem 0.75rem; /* Reducido */
    }
    .login-form .btn-login {
      font-size: 1rem; /* Reducido */
      padding: 0.5rem 0.75rem; /* Reducido */
    }
    .login-header h2 {
      font-size: 1.5rem; /* Reducido */
      color: #007b5e; /* Color solicitado */
    }
    .login-header p {
      font-size: 1rem; /* Reducido */
    }
    .login-form small, .login-form .alert {
      font-size: 0.95rem; /* Reducido */
    }
    .btn-login {
      background-color: #007b5e;
      border: none;
      color: #fff; /* Texto blanco */
    }
    .btn-login:hover {
      background-color: #005f46;
      color: #fff; /* Texto blanco */
    }
    .login-header {
      text-align: center;
      margin-bottom: 1.5rem;
    }
    .home-icon {
      position: absolute;
      top: 24px;
      left: 32px;
      z-index: 10;
      width: 56px;              /* Más ancho */
      height: 56px;             /* Más alto */
      display: flex;            /* Centrado flex */
      align-items: center;      /* Centrado vertical */
      justify-content: center;  /* Centrado horizontal */
      font-size: 2.2rem;        /* Ícono más grande */
      color: #007b5e;
      background: #fff;
      border-radius: 50%;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
      transition: background 0.2s;
      text-decoration: none;
      padding: 0;               /* Sin padding, usa flex */
    }
    .home-icon i {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      height: 100%;
      font-size: 2.2rem;        /* Asegura tamaño del ícono */
    }
    .home-icon:hover {
      background: #e9ecef;
      color: #005f46;
      text-decoration: none;
    }
  </style>
</head>

<body 
  data-mensaje="<?= isset($_GET['mensaje']) ? htmlspecialchars($_GET['mensaje']) : '' ?>" 
  data-tipo-mensaje="<?= isset($_GET['tipo']) ? htmlspecialchars($_GET['tipo']) : '' ?>">

<div class="split">
  <a href="index.html" class="home-icon" title="Inicio">
    <i class="bi bi-house-door-fill"></i>
  </a>
  <!-- Imagen izquierda -->
  <div class="left"></div>

  <!-- Formulario de login derecha -->
  <div class="right">
    <div class="login-form">
      <div class="login-header">
        <h2>Bienvenido</h2>
        <p>Inicia sesión para administrar tus propiedades</p>
      </div>
      <form action="../php/procesar_login.php" method="POST">
        <div class="mb-3">
          <label for="correo" class="form-label">Correo electrónico</label>
          <input type="email" class="form-control" id="correo" name="correo" required>
        </div>
        <div class="mb-3">
          <label for="clave" class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="clave" name="clave" required>
        </div>
        <button type="submit" class="btn btn-login w-100">Ingresar</button>
      </form>

      <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success text-center mt-3">
            <?php echo htmlspecialchars($_GET['mensaje']); ?>
        </div>
      <?php endif; ?>

      <div class="text-center mt-3">
        <small>¿No tienes cuenta? <a href="registro.php" class="text-decoration-none">Regístrate</a></small>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const mensaje = document.body.getAttribute('data-mensaje');
    const tipoMensaje = document.body.getAttribute('data-tipo-mensaje');

    if (mensaje) {
      Swal.fire({
        title: tipoMensaje === 'success' ? '¡Éxito!' : '¡Error!',
        text: mensaje,
        icon: tipoMensaje === 'success' ? 'success' : 'error',
        confirmButtonText: 'Aceptar'
      }).then((result) => {
        if (result.isConfirmed && tipoMensaje === 'success') {
          window.location.href = 'login.php';
        }
      });
    }
  });
</script>
</body>
</html>
