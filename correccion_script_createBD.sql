-- =====================================================================
-- REFACTORIZACIÓN DE CONTROL DE ACCESOS (RBAC UNIFICADO)
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Eliminar la tabla de asignación antigua que acoplaba los roles solo a administrativos
DROP TABLE IF EXISTS `ASIGNACION_ROL`;

-- 2. Crear la nueva tabla intermedia conectada directamente a las CREDENCIALES
CREATE TABLE IF NOT EXISTS `USUARIO_ROL` (
    `id_usuario_rol` INT NOT NULL AUTO_INCREMENT,
    `id_credenciales` INT NOT NULL,
    `id_rol` INT NOT NULL,
    `fecha_asignacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_usuario_rol`),
    -- Evita que a un mismo usuario se le asigne el mismo rol más de una vez
    UNIQUE KEY `uq_usuario_rol` (`id_credenciales`, `id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Crear las restricciones de llaves foráneas para mantener la integridad de los datos
ALTER TABLE `USUARIO_ROL`
    ADD CONSTRAINT `fk_usuariorol_credenciales`
    FOREIGN KEY (`id_credenciales`) REFERENCES `CREDENCIALES`(`id_credenciales`)
    ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `USUARIO_ROL`
    ADD CONSTRAINT `fk_usuariorol_rol`
    FOREIGN KEY (`id_rol`) REFERENCES `ROL`(`id_rol`)
    ON UPDATE CASCADE ON DELETE RESTRICT;

-- 4. Limpiar e insertar los roles base en la tabla ROL (Opcional, ajusta si ya tienes datos)
-- Usamos TRUNCATE para reiniciar el contador si la tabla estaba vacía o con pruebas antiguas.
-- Si ya tenías datos vinculados a la tabla SUELDO_ADMI, omite el TRUNCATE y solo haz INSERTS individuales
INSERT INTO `ROL` (`nombre`) VALUES
('Director'),
('Docente');

SET FOREIGN_KEY_CHECKS = 1;
