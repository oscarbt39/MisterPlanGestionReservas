CREATE DATABASE IF NOT EXISTS RESERVAS_MISTERPLAN;
USE RESERVAS_MISTERPLAN;


-- ==========================================
-- CREACION DE TABLAS
-- ==========================================
CREATE TABLE RESERVA (
    ID_RESERVA INT AUTO_INCREMENT PRIMARY KEY,
    NOMBRE_HUESPED VARCHAR(50) NOT NULL,
    EMAIL_HUESPED VARCHAR(50) NOT NULL,
    NOM_ALOJAMIENTO VARCHAR(50) NOT NULL,
    FEC_ENTRADA DATE NOT NULL,
    FEC_SALIDA DATE NOT NULL,
    ESTADO VARCHAR(20) NOT NULL CHECK (ESTADO IN ('pendiente', 'confirmada', 'cancelada')),
    IMPORTE DECIMAL(10, 2) NOT NULL,
    NOTAS TEXT,
    FEC_CREACION DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE RESERVA_EVENTO (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    ID_RESERVA INT NOT NULL,
    TIPO VARCHAR(50) NOT NULL,
    DESCRIPCION VARCHAR(255) NOT NULL,
    FEC_CREACION DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_RESERVA_EVENTO FOREIGN KEY (ID_RESERVA) REFERENCES RESERVA(ID_RESERVA) ON DELETE CASCADE
);

-- ==========================================
-- DATOS DE PRUEBA: RESERVAS
-- ==========================================
INSERT INTO RESERVA (NOMBRE_HUESPED, EMAIL_HUESPED, NOM_ALOJAMIENTO, FEC_ENTRADA, FEC_SALIDA, ESTADO, IMPORTE, NOTAS) VALUES
('Ana Valderrama', 'ana.valderrama@example.com', 'Hotel Vista Mar', '2026-06-10', '2026-06-15', 'confirmada', 450.00, 'Prefiere habitación en planta alta.'),
('Carlos Pérez', 'carlos.p@example.com', 'Casa Rural El Roble', '2026-07-01', '2026-07-07', 'pendiente', 700.50, 'Viaja con un perro pequeño.'),
('Lucía Martínez', 'lucia.m@example.com', 'Apartamento Céntrico', '2026-05-12', '2026-05-14', 'cancelada', 220.00, 'Cancelado por motivos personales.'),
('David Ruiz', 'david.ruiz@example.com', 'Hotel Vista Mar', '2026-08-20', '2026-08-25', 'pendiente', 550.00, 'Solicita cuna para bebé.'),
('Elena Torres', 'elena.torres@example.com', 'Villa del Sol', '2026-09-05', '2026-09-12', 'confirmada', 1200.00, 'Llegada estimada sobre las 18:00h.');

-- ==========================================
-- DATOS DE PRUEBA: HISTORIAL DE EVENTOS
-- ==========================================
INSERT INTO RESERVA_EVENTO (ID_RESERVA, TIPO, DESCRIPCION) VALUES
(1, 'CREACION', 'Reserva creada en el sistema.'),
(1, 'CAMBIO_ESTADO', 'La reserva cambió de estado a: confirmada.'),
(2, 'CREACION', 'Reserva creada en el sistema.'),
(3, 'CREACION', 'Reserva creada en el sistema.'),
(3, 'CAMBIO_ESTADO', 'La reserva cambió de estado a: cancelada.'),
(4, 'CREACION', 'Reserva creada en el sistema.'),
(5, 'CREACION', 'Reserva creada en el sistema.'),
(5, 'CAMBIO_ESTADO', 'La reserva cambió de estado a: confirmada.');