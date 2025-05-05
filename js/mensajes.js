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
        // Si fue exitoso, redirigimos al login
        window.location.href = 'login.php'; // Ajusta la ruta si es necesario
      }
    });
  }
});
