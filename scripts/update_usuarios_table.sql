-- Primero hacemos backup de la tabla actual
CREATE TABLE usuarios_backup LIKE usuarios;
INSERT INTO usuarios_backup SELECT * FROM usuarios;

-- Modificamos la tabla usuarios
ALTER TABLE usuarios 
  MODIFY cedula varchar(20) NOT NULL,
  MODIFY nombre varchar(100) NOT NULL,
  MODIFY primer_nombre varchar(50) NOT NULL,
  MODIFY primer_apellido varchar(50) NOT NULL,
  ADD UNIQUE KEY idx_cedula (cedula),
  ADD UNIQUE KEY idx_email (email);

-- Añadir triggers para generación automática de email y contraseña
DELIMITER //

CREATE TRIGGER before_insert_usuario
BEFORE INSERT ON usuarios
FOR EACH ROW
BEGIN
    -- Generar email automático basado en primer nombre y primer apellido
    SET NEW.email = CONCAT(
        LOWER(REPLACE(NEW.primer_nombre, ' ', '')),
        '.',
        LOWER(REPLACE(NEW.primer_apellido, ' ', '')),
        '@comfachoco.com'
    );
    
    -- Generar contraseña: primeras 3 letras del nombre + últimos 4 dígitos de la cédula
    SET NEW.password = MD5(CONCAT(
        LEFT(NEW.primer_nombre, 3),
        RIGHT(NEW.cedula, 4)
    ));
END;//

DELIMITER ;