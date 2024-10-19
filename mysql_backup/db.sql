CREATE DATABASE eazypay;

USE eazypay;

-- Tabla de Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Grupos
CREATE TABLE grupos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_creador INT,
    FOREIGN KEY (id_creador) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de Miembros de Grupos
CREATE TABLE miembros_grupos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    id_grupo INT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_grupo) REFERENCES grupos(id) ON DELETE CASCADE,
    UNIQUE (id_usuario, id_grupo) -- Evita que un usuario se añada más de una vez al mismo grupo
);

-- Tabla de Pagos
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_grupo INT,
    descripcion VARCHAR(255) NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT,
    FOREIGN KEY (id_grupo) REFERENCES grupos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla de Movimientos
CREATE TABLE movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_grupo INT,
    id_pagador INT,
    id_deudor INT,
    monto DECIMAL(10, 2) NOT NULL,
    descripcion VARCHAR(255),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_grupo) REFERENCES grupos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_pagador) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_deudor) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Datos de prueba

INSERT INTO usuarios (id_usuario, nombre, email, contraseña, fecha_registro)
VALUES
    (1, 'Juan Pérez', 'juan.perez@example.com', 'password123', NOW()),
    (2, 'María García', 'maria.garcia@example.com', 'password123', NOW()),
    (3, 'Pedro Martínez', 'pedro.martinez@example.com', 'password123', NOW()),
    (4, 'Laura López', 'laura.lopez@example.com', 'password123', NOW());

INSERT INTO grupos (id_grupo, nombre_grupo, id_usuario, fecha_creacion)
VALUES
    (1, 'Círculo de amigos', 1, NOW()),
    (2, 'Familia', 2, NOW()),
    (3, 'Compañeros de trabajo', 1, NOW());


INSERT INTO pagos (id_pago, id_grupo, descripcion, monto, fecha_pago, id_usuario)
VALUES
    (1, 1, 'Cena en grupo', 50.00, NOW(), 1),
    (2, 1, 'Cerveza', 20.00, NOW(), 2),
    (3, 2, 'Regalo de cumpleaños', 100.00, NOW(), 3),
    (4, 3, 'Almuerzo de trabajo', 30.00, NOW(), 1);


INSERT INTO movimientos (id_movimiento, id_pago, id_usuario_deudor, id_usuario_creditor, monto, estado)
VALUES
    (1, 1, 2, 1, 25.00, 'pendiente'),
    (2, 1, 3, 1, 25.00, 'pendiente'),
    (3, 2, 1, 2, 20.00, 'pagado'),
    (4, 3, 4, 3, 100.00, 'pendiente'),
    (5, 4, 1, 1, 30.00, 'pagado');


-- Crear un nuevo usuario
CREATE USER 'eazypay'@'localhost' IDENTIFIED BY 'eazypaypebb';

-- Otorgar privilegios al usuario sobre la base de datos eazypay
GRANT ALL PRIVILEGES ON eazypay.* TO 'eazypay'@'localhost' WITH GRANT OPTION;

-- Aplicar los cambios
FLUSH PRIVILEGES;