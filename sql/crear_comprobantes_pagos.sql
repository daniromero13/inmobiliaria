CREATE TABLE comprobantes_pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrato_id INT NOT NULL,
    archivo VARCHAR(255) NOT NULL,
    fecha_envio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Pendiente', 'Aceptado', 'Rechazado') NOT NULL DEFAULT 'Pendiente',
    FOREIGN KEY (contrato_id) REFERENCES contratos(id)
);
