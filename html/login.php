<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión - Inmobiliaria</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <style>
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }
    .split {
      height: 100%;
      display: flex;
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
      max-width: 400px;
      background: #ffffff;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .btn-login {
      background-color: #007b5e;
      border: none;
    }
    .btn-login:hover {
      background-color: #005f46;
    }
    .login-header {
      text-align: center;
      margin-bottom: 1.5rem;
    }
    .login-header h2 {
      color: #007b5e;
    }
    .login-header p {
      color: #6c757d;
    }
  </style>
</head>

<body 
  data-mensaje="<?= isset($_GET['mensaje']) ? htmlspecialchars($_GET['mensaje']) : '' ?>" 
  data-tipo-mensaje="<?= isset($_GET['tipo']) ? htmlspecialchars($_GET['tipo']) : '' ?>">

<div class="split">
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
