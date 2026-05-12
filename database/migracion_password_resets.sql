-- Tabla para tokens de recuperación de contraseña
-- Ejecutar UNA sola vez en la base de datos

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id`        INT(11)      NOT NULL AUTO_INCREMENT,
  `email`     VARCHAR(150) NOT NULL,
  `token`     VARCHAR(64)  NOT NULL,
  `expira_en` DATETIME     NOT NULL,
  `creado_en` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
