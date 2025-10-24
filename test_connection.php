<?php
/**
 * Script de prueba de conexión a la base de datos
 * Verifica que la conexión a MySQL esté configurada correctamente
 */

require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html>\n";
echo "<html lang='es'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "    <title>Test de Conexión - ComfaChoco</title>\n";
echo "    <style>\n";
echo "        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }\n";
echo "        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }\n";
echo "        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }\n";
echo "        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }\n";
echo "        h1 { color: #333; }\n";
echo "        table { width: 100%; border-collapse: collapse; background: white; margin: 20px 0; }\n";
echo "        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }\n";
echo "        th { background: #007bff; color: white; }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";
echo "    <h1>🔍 Test de Conexión a Base de Datos - ComfaChoco</h1>\n";

try {
    // Intentar conectar
    echo "<div class='info'>📡 Intentando conectar a la base de datos...</div>\n";

    $conn = getConnection();

    echo "<div class='success'>✅ Conexión exitosa a la base de datos</div>\n";

    // Mostrar información de la conexión
    echo "<div class='info'>\n";
    echo "    <strong>Información de la conexión:</strong><br>\n";
    echo "    Host: " . DB_HOST . "<br>\n";
    echo "    Puerto: " . DB_PORT . "<br>\n";
    echo "    Base de datos: " . DB_NAME . "<br>\n";
    echo "    Usuario: " . DB_USER . "<br>\n";
    echo "    Charset: " . $conn->character_set_name() . "<br>\n";
    echo "</div>\n";

    // Verificar versión de MySQL
    $version = $conn->query("SELECT VERSION() AS version");
    if ($version) {
        $v = $version->fetch_assoc();
        echo "<div class='info'>🔧 Versión de MySQL: " . $v['version'] . "</div>\n";
    }

    // Listar tablas
    echo "<h2>📋 Tablas en la base de datos</h2>\n";
    $tables = $conn->query("SHOW TABLES");

    if ($tables && $tables->num_rows > 0) {
        echo "<table>\n";
        echo "    <thead><tr><th>#</th><th>Nombre de Tabla</th><th>Registros</th></tr></thead>\n";
        echo "    <tbody>\n";

        $i = 1;
        while ($row = $tables->fetch_array()) {
            $tableName = $row[0];

            // Contar registros en cada tabla
            $countResult = $conn->query("SELECT COUNT(*) as total FROM `$tableName`");
            $count = $countResult ? $countResult->fetch_assoc()['total'] : 0;

            echo "        <tr>\n";
            echo "            <td>$i</td>\n";
            echo "            <td><strong>$tableName</strong></td>\n";
            echo "            <td>$count registros</td>\n";
            echo "        </tr>\n";
            $i++;
        }

        echo "    </tbody>\n";
        echo "</table>\n";
    } else {
        echo "<div class='error'>⚠️ No se encontraron tablas en la base de datos</div>\n";
    }

    // Verificar datos de prueba en tabla usuarios
    echo "<h2>👤 Usuarios en el sistema</h2>\n";
    $usuarios = $conn->query("SELECT id, nombre, email, rol, activo FROM usuarios ORDER BY id ASC");

    if ($usuarios && $usuarios->num_rows > 0) {
        echo "<table>\n";
        echo "    <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th></tr></thead>\n";
        echo "    <tbody>\n";

        while ($user = $usuarios->fetch_assoc()) {
            $estado = $user['activo'] ? '<span style="color: green;">✓ Activo</span>' : '<span style="color: red;">✗ Inactivo</span>';
            echo "        <tr>\n";
            echo "            <td>{$user['id']}</td>\n";
            echo "            <td>{$user['nombre']}</td>\n";
            echo "            <td>{$user['email']}</td>\n";
            echo "            <td>" . ucfirst($user['rol']) . "</td>\n";
            echo "            <td>$estado</td>\n";
            echo "        </tr>\n";
        }

        echo "    </tbody>\n";
        echo "</table>\n";
    } else {
        echo "<div class='error'>⚠️ No se encontraron usuarios en la base de datos</div>\n";
    }

    // Cerrar conexión
    $conn->close();

    echo "<div class='success'>✅ Test completado exitosamente</div>\n";

} catch (Exception $e) {
    echo "<div class='error'>\n";
    echo "    ❌ <strong>Error de conexión:</strong><br>\n";
    echo "    " . $e->getMessage() . "\n";
    echo "</div>\n";

    echo "<div class='info'>\n";
    echo "    <strong>Verifica lo siguiente:</strong><br>\n";
    echo "    1. MySQL está corriendo (ejecuta: sudo /opt/lampp/lampp start)<br>\n";
    echo "    2. Las credenciales en el archivo .env son correctas<br>\n";
    echo "    3. La base de datos 'comfachoco' existe<br>\n";
    echo "    4. El usuario tiene permisos adecuados<br>\n";
    echo "</div>\n";
}

echo "</body>\n";
echo "</html>\n";
