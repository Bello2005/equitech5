<?php
require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();
    
    // Crear tabla usuarios si no existe
    $sql = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        tipo_empleado_id INT,
        departamento VARCHAR(100) DEFAULT 'General',
        fecha_ingreso DATE,
        activo BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Tabla usuarios creada o ya existente\n";
    }
    
    // Crear tabla solicitudes si no existe
    $sql = "CREATE TABLE IF NOT EXISTS solicitudes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        departamento VARCHAR(100) DEFAULT NULL,
        tipo ENUM('vacaciones','permiso_medico','teletrabajo','capacitacion','otro') NOT NULL,
        fecha_inicio DATE NOT NULL,
        fecha_fin DATE NOT NULL,
        dias INT NOT NULL,
        dias_habiles DECIMAL(5,2) DEFAULT NULL,
        motivo TEXT DEFAULT NULL,
        documento_adjunto VARCHAR(255) DEFAULT NULL,
        tipo_documento VARCHAR(100) DEFAULT NULL,
        estado ENUM('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
        prioridad ENUM('alta','media','baja') DEFAULT 'media',
        es_reemplazo TINYINT(1) DEFAULT 0,
        usuario_reemplazo_id INT DEFAULT NULL,
        validacion_disponibilidad TEXT DEFAULT NULL,
        periodo_solicitud INT DEFAULT 1,
        aprobador_id INT DEFAULT NULL,
        aprobador_rol VARCHAR(50) DEFAULT NULL,
        fecha_aprobacion TIMESTAMP NULL DEFAULT NULL,
        comentarios TEXT DEFAULT NULL,
        fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Tabla usuarios creada o ya existente\n";
        
        // Insertar usuario de prueba si no existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = 1");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $sql = "INSERT INTO usuarios (id, nombre, email, password, departamento, fecha_ingreso) 
                    VALUES (1, 'Usuario Prueba', 'prueba@example.com', 'test123', 'IT', '2024-01-01')";
            if ($conn->query($sql) === TRUE) {
                echo "Usuario de prueba creado (ID: 1)\n";
            }
        } else {
            echo "Usuario de prueba ya existe\n";
        }
    }
    
    // Crear tabla tipos_empleado si no existe
    $sql = "CREATE TABLE IF NOT EXISTS tipos_empleado (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Tabla tipos_empleado creada o ya existente\n";
    }
    
    $conn->close();
    echo "¡Listo! Ahora puedes usar test_set_session.php\n";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}