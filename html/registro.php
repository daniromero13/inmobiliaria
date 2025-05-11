<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro - Inmobiliaria</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"> <!-- Iconos Bootstrap -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/mensajes.js" defer></script>
  <style>
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      font-size: 14px; /* Reducido */
    }
    .split {
      min-height: 100vh;
      height: auto;
      display: flex;
      flex-direction: row;
      overflow: hidden; /* Quita scroll */
      position: relative;
    }
    .left {
      flex: 1;
      background: url('https://images.unsplash.com/photo-1515263487990-61b07816b324?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center;
      background-size: cover;
      min-width: 0;
    }
    .right {
      flex: 1;
      display: flex;
      align-items: center; /* Centrado vertical */
      justify-content: center; /* Centrado horizontal */
      background-color: #273746;
      min-width: 0;
      overflow: hidden;
    }
    .register-form {
      width: 100%;
      max-width: 400px; /* Reducido */
      background: #ffffff;
      padding: 2rem;    /* Reducido */
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      font-size: 1rem; /* Reducido */
      margin: 0;
      /* Limita el alto para pantallas pequeñas */
      max-height: none; /* Quita límite de alto */
      overflow-y: visible; /* Quita scroll vertical */
    }
    @media (max-width: 900px) {
      .split {
        flex-direction: column;
      }
      .left, .right {
        flex: none;
        width: 100%;
        min-height: 0;
      }
      .register-form {
        max-width: 95vw;
        padding: 1.2rem 0.5rem;
        font-size: 1rem;
        max-height: 98vh;
      }
    }
    .register-form .form-label {
      font-size: 1rem; /* Reducido */
    }
    .register-form .form-control {
      font-size: 1rem; /* Reducido */
      padding: 0.5rem 0.75rem; /* Reducido */
    }
    .register-form .btn-register {
      font-size: 1rem; /* Reducido */
      padding: 0.5rem 0.75rem; /* Reducido */
    }
    .btn-register {
      background-color: #007b5e;
      border: none;
      color: #fff;
    }
    .btn-register:hover {
      background-color: #005f46;
      color: #fff;
    }
    .register-header {
      text-align: center;
      margin-bottom: 1.2rem;
    }
    .register-header h2 {
      color: #007b5e;
      font-size: 1.5rem; /* Reducido */
    }
    .register-header p {
      color: #000;
      font-size: 1rem; /* Reducido */
    }
    .icon-bar {
      position: absolute;
      top: 24px;
      left: 32px;
      z-index: 20;
      display: flex;
      gap: 12px;
      align-items: center;
    }
    .home-icon, .login-arrow {
      position: static;
      width: 56px;
      height: 56px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.2rem;
      color: #007b5e;
      background: #fff;
      border-radius: 50%;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
      transition: background 0.2s;
      text-decoration: none;
      padding: 0;
    }
    .home-icon i, .login-arrow i {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      height: 100%;
      font-size: 2.2rem;
    }
    .home-icon:hover, .login-arrow:hover {
      background: #e9ecef;
      color: #005f46;
      text-decoration: none;
    }
  </style>
</head>

<body
  <?php if (isset($_SESSION['mensaje'])): ?>
    data-mensaje="<?php echo htmlspecialchars($_SESSION['mensaje']); ?>"
    data-tipo-mensaje="<?php echo htmlspecialchars($_SESSION['tipo_mensaje']); ?>"
    <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
  <?php endif; ?>
>
<div class="split">
  <div class="icon-bar">
    <a href="login.php" class="login-arrow" title="Volver al login">
      <i class="bi bi-arrow-left"></i>
    </a>
    <a href="index.html" class="home-icon" title="Inicio">
      <i class="bi bi-house-door-fill"></i>
    </a>
  </div>
  <div class="left"></div>
  <div class="right">
    <div class="register-form">
      <div class="register-header">
        <h2>Crear Cuenta</h2>
        <p>Regístrate para comenzar a usar la plataforma</p>
      </div>
      <form action="../php/procesar_registro.php" method="POST">
        <div class="mb-3">
          <label for="nombre_completo" class="form-label">Nombre Completo</label>
          <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required>
        </div>
        <div class="mb-3">
          <label for="correo" class="form-label">Correo Electrónico</label>
          <input type="email" class="form-control" id="correo" name="correo" required>
        </div>
        <div class="mb-3">
          <label for="telefono" class="form-label">Teléfono</label>
          <input type="text" class="form-control" id="telefono" name="telefono" required>
        </div>
        <div class="mb-3">
          <label for="contrasena" class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="contrasena" name="contrasena" required>
        </div>
        <div class="mb-3">
          <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña</label>
          <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
        </div>
        <div class="mb-3">
          <label for="rol" class="form-label">Rol</label>
          <select class="form-select" id="rol" name="rol" required>
            <option value="Propietario">Propietario</option>
            <option value="Arrendatario" selected>Arrendatario</option>
            <option value="Agente inmobiliario">Agente inmobiliario</option>
          </select>
        </div>
        <button type="submit" class="btn btn-register w-100">Registrarse</button>
      </form>
      <div class="text-center mt-3">
        <small>¿Ya tienes cuenta? <a href="login.php" class="text-decoration-none">Inicia sesión</a></small>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
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
