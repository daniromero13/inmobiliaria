<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro - Inmobiliaria</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Agregado SweetAlert -->
  <script src="../js/mensajes.js" defer></script> <!-- Tu archivo de mensajes -->
  
  <style>
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }
    .register-form {
      width: 100%;
      max-width: 400px;
      margin: auto;
      margin-top: 5%;
      background: #ffffff;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .btn-register {
      background-color: #007b5e;
      border: none;
    }
    .btn-register:hover {
      background-color: #005f46;
    }
    .register-header {
      text-align: center;
      margin-bottom: 1.5rem;
    }
    .register-header h2 {
      color: #007b5e;
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
