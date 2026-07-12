-- ══════════════════════════════════════════════════
--  EgoGym Fitness — Esquema de base de datos
--  Charset: utf8mb4  |  Engine: InnoDB
-- ══════════════════════════════════════════════════

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Base de datos ──────────────────────────────────
CREATE DATABASE IF NOT EXISTS egogym
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE egogym;

-- ── clientes ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS clientes (
  id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  nombre        VARCHAR(120)    NOT NULL,
  email         VARCHAR(191)    NOT NULL,
  password_hash VARCHAR(255)    NOT NULL,
  telefono      VARCHAR(20)     DEFAULT NULL,
  activo        TINYINT(1)      NOT NULL DEFAULT 1,
  created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── membresias ────────────────────────────────────
CREATE TABLE IF NOT EXISTS membresias (
  id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  nombre          VARCHAR(100)  NOT NULL,
  precio          DECIMAL(10,2) NOT NULL,
  duracion_dias   INT UNSIGNED  NOT NULL COMMENT 'días que dura la membresía',
  descripcion     TEXT          DEFAULT NULL,
  beneficios      TEXT          DEFAULT NULL COMMENT 'JSON array de bullets',
  activa          TINYINT(1)    NOT NULL DEFAULT 1,
  destacada       TINYINT(1)    NOT NULL DEFAULT 0 COMMENT 'aparece como recomendada',
  stripe_price_id VARCHAR(100)  DEFAULT NULL COMMENT 'precio en Stripe (opcional)',
  created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── compras ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS compras (
  id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  cliente_id          INT UNSIGNED    NOT NULL,
  membresia_id        INT UNSIGNED    NOT NULL,
  stripe_session_id   VARCHAR(200)    DEFAULT NULL,
  stripe_payment_id   VARCHAR(200)    DEFAULT NULL,
  monto               DECIMAL(10,2)   NOT NULL,
  fecha_pago          DATETIME        DEFAULT NULL,
  fecha_inicio        DATE            NOT NULL,
  fecha_fin           DATE            NOT NULL,
  status              ENUM('pendiente','completado','fallido','reembolsado')
                                      NOT NULL DEFAULT 'pendiente',
  correo_enviado      TINYINT(1)      NOT NULL DEFAULT 0,
  created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_cliente   (cliente_id),
  KEY idx_membresia (membresia_id),
  KEY idx_session   (stripe_session_id),
  CONSTRAINT fk_compra_cliente   FOREIGN KEY (cliente_id)   REFERENCES clientes(id)   ON DELETE RESTRICT,
  CONSTRAINT fk_compra_membresia FOREIGN KEY (membresia_id) REFERENCES membresias(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── usuarios_admin ────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios_admin (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  usuario       VARCHAR(60)   NOT NULL,
  password_hash VARCHAR(255)  NOT NULL,
  nombre        VARCHAR(120)  NOT NULL,
  email         VARCHAR(191)  DEFAULT NULL,
  activo        TINYINT(1)    NOT NULL DEFAULT 1,
  last_login    DATETIME      DEFAULT NULL,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuario (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ══════════════════════════════════════════════════
--  Datos iniciales
-- ══════════════════════════════════════════════════

-- Admin por defecto: usuario=admin  password=Admin1234!
-- (Cambia el hash generando uno nuevo con password_hash())
INSERT INTO usuarios_admin (usuario, password_hash, nombre, email) VALUES
('admin',
 '$2y$12$YourHashHereChangeThis.........................................',
 'Administrador EgoGym',
 'admin@egogym.com');

-- Membresías de ejemplo
INSERT INTO membresias (nombre, precio, duracion_dias, descripcion, beneficios, activa, destacada) VALUES
('1 Día',        60.00,   1,  'Acceso por un día completo',
 '["Acceso completo al gym","Uso de vestidores y casilleros"]',
 1, 0),
('Mensual',     550.00,  30,  'La membresía más popular',
 '["Acceso ilimitado 30 días","Evaluación física inicial","Uso de todas las áreas","Sin costo de inscripción"]',
 1, 1),
('Trimestral', 1350.00,  90,  'Ahorra con 3 meses',
 '["Acceso ilimitado 90 días","Evaluación física inicial","Asesoría nutricional básica","Acceso a clases grupales"]',
 1, 0),
('Semestral',  2400.00, 180,  '6 meses de entrenamiento',
 '["Acceso ilimitado 180 días","Evaluación física mensual","Asesoría nutricional","Acceso a todas las clases","Plan de entrenamiento personalizado"]',
 1, 0),
('Anual',      4200.00, 365,  'El mejor precio por día',
 '["Acceso ilimitado 365 días","Evaluaciones mensuales","Asesoría nutricional completa","Todas las clases incluidas","Plan personalizado","Congelamiento de membresía 1 vez"]',
 1, 0);
