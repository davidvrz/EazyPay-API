CREATE DATABASE eazypay;

USE eazypay;

-- Tabla de Usuarios
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    -- gastos_totales DECIMAL(10, 2) DEFAULT 0.00 -- (opcional)
);

-- Tabla de Grupos
CREATE TABLE grupos (
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    administrador INT,
    moneda VARCHAR(10) DEFAULT 'EUR',
    FOREIGN KEY (administrador) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de miembros de Grupos
CREATE TABLE miembros_grupos (
    id_usuario INT,
    id_grupo INT,
    -- fecha_union TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- (opcional)
    -- estado ENUM('activo', 'inactivo') DEFAULT 'activo', -- (opcional)
    saldo_acumulado DECIMAL(10, 2) DEFAULT 0.00,
    PRIMARY KEY (id_usuario, id_grupo),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo) ON DELETE CASCADE
);

-- Tabla de Gastos
CREATE TABLE gastos (
    id_gasto INT AUTO_INCREMENT PRIMARY KEY,
    id_grupo INT,
    descripcion TEXT NOT NULL,
    monto_total DECIMAL(10, 2) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pagador INT,
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo) ON DELETE CASCADE,
    FOREIGN KEY (pagador) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

-- Tabla ParticipantesGastos (participación en cada gasto)
CREATE TABLE participantes_gastos (
    id_gasto INT,
    id_usuario INT,
    monto DECIMAL(10, 2) NOT NULL,
    porcentaje_ratio FLOAT DEFAULT 1.0,
    PRIMARY KEY (id_gasto, id_usuario),
    FOREIGN KEY (id_gasto) REFERENCES gastos(id_gasto) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de Deudas (relación de deudas entre usuarios)
CREATE TABLE deudas (
    id_deuda INT AUTO_INCREMENT PRIMARY KEY,
    deudor INT,
    acreedor INT,
    id_grupo INT,
    monto DECIMAL(10, 2) NOT NULL,
    estado ENUM('pendiente', 'pagada') DEFAULT 'pendiente',
    FOREIGN KEY (deudor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (acreedor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo) ON DELETE CASCADE
);


-- Datos de prueba

INSERT INTO usuarios (nombre, email, contrasena)
VALUES
    ('Juan Pérez', 'juan.perez@example.com', 'password123'),
    ('María García', 'maria.garcia@example.com', 'password123'),
    ('Pedro Martínez', 'pedro.martinez@example.com', 'password123'),
    ('Laura López', 'laura.lopez@example.com', 'password123');

INSERT INTO grupos (nombre, descripcion, administrador)
VALUES
    ('Círculo de amigos', 'Grupo de amigos para compartir gastos de ocio', 1),
    ('Familia', 'Grupo familiar para eventos y gastos compartidos', 2),
    ('Compañeros de trabajo', 'Gastos compartidos en actividades laborales', 3);

INSERT INTO miembros_grupos (id_usuario, id_grupo)
VALUES
    (1, 1),
    (2, 1),
    (3, 1),
    (2, 2),
    (1, 3),
    (3, 3),
    (4, 3);

INSERT INTO gastos (id_grupo, descripcion, monto_total, pagador)
VALUES
    (1, 'Cena en grupo', 50.00, 1),
    (1, 'Cerveza', 20.00, 2),
    (2, 'Regalo de cumpleaños', 100.00, 3),
    (3, 'Almuerzo de trabajo', 30.00, 1);

INSERT INTO participantes_gastos (id_gasto, id_usuario, monto, porcentaje_ratio)
VALUES
    (1, 1, 25.00, 0.5),
    (1, 2, 25.00, 0.5),
    (2, 1, 20.00, 1.0),
    (3, 3, 100.00, 1.0),
    (4, 1, 30.00, 1.0);

INSERT INTO deudas (deudor, acreedor, id_grupo, monto, estado)
VALUES
    (2, 1, 1, 25.00, 'pendiente'),
    (3, 1, 1, 25.00, 'pendiente'),
    (2, 3, 2, 100.00, 'pendiente');


-- Crear un nuevo usuario
CREATE USER 'eazypay'@'localhost' IDENTIFIED BY 'eazypaypebb';

-- Otorgar privilegios al usuario sobre la base de datos eazypay
GRANT ALL PRIVILEGES ON eazypay.* TO 'eazypay'@'localhost' WITH GRANT OPTION;

-- Aplicar los cambios
FLUSH PRIVILEGES;
