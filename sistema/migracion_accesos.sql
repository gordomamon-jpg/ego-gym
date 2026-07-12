-- ══════════════════════════════════════════════════
--  EgoGym — Migración: Control de Acceso
--  Ejecutar una sola vez en la base de datos egogym
-- ══════════════════════════════════════════════════

USE egogym;

-- ── Método de pago en compras (admin puede registrar efectivo, etc.) ──
ALTER TABLE compras
  ADD COLUMN metodo_pago ENUM('stripe','efectivo','tarjeta_presencial','cortesia')
    NOT NULL DEFAULT 'stripe' AFTER monto,
  ADD COLUMN notas VARCHAR(300) DEFAULT NULL AFTER correo_enviado;

-- ── Registro de accesos al gym ────────────────────
CREATE TABLE IF NOT EXISTS registros_acceso (
  id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  cliente_id  INT UNSIGNED    NOT NULL,
  tipo        ENUM('entrada','salida') NOT NULL DEFAULT 'entrada',
  metodo      ENUM('huella','manual','api') NOT NULL DEFAULT 'manual',
  resultado   ENUM('permitido','denegado') NOT NULL DEFAULT 'permitido',
  motivo      VARCHAR(200)    DEFAULT NULL COMMENT 'razón si fue denegado',
  fecha_hora  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  admin_id    INT UNSIGNED    DEFAULT NULL COMMENT 'quien lo registró si fue manual',
  PRIMARY KEY (id),
  KEY idx_cliente (cliente_id),
  KEY idx_fecha   (fecha_hora),
  KEY idx_tipo    (tipo, resultado),
  CONSTRAINT fk_acceso_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Huellas digitales (para lector externo) ───────
CREATE TABLE IF NOT EXISTS huellas (
  id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  cliente_id   INT UNSIGNED  NOT NULL,
  huella_hash  VARCHAR(255)  NOT NULL COMMENT 'template hash enviado por el lector',
  activa       TINYINT(1)    NOT NULL DEFAULT 1,
  created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_hash (huella_hash),
  CONSTRAINT fk_huella_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
