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

INSERT INTO usuarios (nombre, email, contrasena)
VALUES
    ('Juan Pérez', 'juan.perez@example.com', 'password123'),
    ('María García', 'maria.garcia@example.com', 'password123'),
    ('Pedro Martínez', 'pedro.martinez@example.com', 'password123'),
    ('Laura López', 'laura.lopez@example.com', 'password123');

INSERT INTO grupos (nombre, id_creador)
VALUES
    ('Círculo de amigos', 1),
    ('Familia', 2),
    ('Compañeros de trabajo', 3);

INSERT INTO miembros_grupos (id_usuario, id_grupo)
VALUES
    (1, 1),
    (2, 1),
    (3, 1),
    (2, 2),
    (1, 3),
    (3, 3),
    (4, 3);

INSERT INTO pagos (id_grupo, descripcion, monto, id_usuario)
VALUES
    (1, 'Cena en grupo', 50.00, 1),
    (1, 'Cerveza', 20.00, 2),
    (2, 'Regalo de cumpleaños', 100.00, 3),
    (3, 'Almuerzo de trabajo', 30.00, 1);

INSERT INTO movimientos (id_grupo, id_pagador, id_deudor, monto, descripcion)
VALUES
    (1, 1, 2, 25.00, 'Pago parcial por la cena'),
    (1, 1, 3, 25.00, 'Pago parcial por la cena'),
    (2, 3, 2, 100.00, 'Regalo de cumpleaños');


-- Crear un nuevo usuario
CREATE USER 'eazypay'@'localhost' IDENTIFIED BY 'eazypaypebb';

-- Otorgar privilegios al usuario sobre la base de datos eazypay
GRANT ALL PRIVILEGES ON eazypay.* TO 'eazypay'@'localhost' WITH GRANT OPTION;

-- Aplicar los cambios
FLUSH PRIVILEGES;
