<?php
/**
 * Cargador de Variables de Entorno
 * Carga y parsea el archivo .env
 */

class Env {
    private static $loaded = false;
    private static $variables = [];

    /**
     * Cargar variables de entorno desde archivo .env
     */
    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }

        if ($path === null) {
            $path = dirname(__DIR__) . '/.env';
        }

        if (!file_exists($path)) {
            throw new Exception("Archivo .env no encontrado en: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parsear línea
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Remover comillas si existen
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                    $value = $matches[1];
                }

                // Guardar en array y en $_ENV
                self::$variables[$name] = $value;
                $_ENV[$name] = $value;
                putenv("{$name}={$value}");
            }
        }

        self::$loaded = true;
    }

    /**
     * Obtener valor de variable de entorno
     *
     * @param string $key Nombre de la variable
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }

        // Buscar en orden: variables cargadas, $_ENV, $_SERVER, getenv()
        if (isset(self::$variables[$key])) {
            return self::parseValue(self::$variables[$key]);
        }

        if (isset($_ENV[$key])) {
            return self::parseValue($_ENV[$key]);
        }

        if (isset($_SERVER[$key])) {
            return self::parseValue($_SERVER[$key]);
        }

        $value = getenv($key);
        if ($value !== false) {
            return self::parseValue($value);
        }

        return $default;
    }

    /**
     * Parsear valor para convertir strings a tipos apropiados
     *
     * @param string $value
     * @return mixed
     */
    private static function parseValue($value) {
        if (is_string($value)) {
            // Convertir strings booleanos
            $lower = strtolower($value);
            if ($lower === 'true') return true;
            if ($lower === 'false') return false;

            // Convertir null
            if ($lower === 'null') return null;

            // Convertir números
            if (is_numeric($value)) {
                return strpos($value, '.') !== false ? (float)$value : (int)$value;
            }
        }

        return $value;
    }

    /**
     * Obtener todas las variables cargadas
     *
     * @return array
     */
    public static function all() {
        if (!self::$loaded) {
            self::load();
        }
        return self::$variables;
    }

    /**
     * Verificar si una variable existe
     *
     * @param string $key
     * @return bool
     */
    public static function has($key) {
        if (!self::$loaded) {
            self::load();
        }
        return isset(self::$variables[$key]);
    }
}

/**
 * Helper function para obtener variable de entorno
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env($key, $default = null) {
    return Env::get($key, $default);
}

// Cargar variables de entorno automáticamente
try {
    Env::load();
} catch (Exception $e) {
    // En desarrollo, usar valores por defecto si no existe .env
    if (strpos($e->getMessage(), 'no encontrado') !== false) {
        error_log("ADVERTENCIA: Archivo .env no encontrado. Usando valores por defecto.");
        // No hacer nada, las funciones env() usarán valores por defecto
    } else {
        error_log("Error cargando .env: " . $e->getMessage());
        die("Error de configuración. Por favor contacte al administrador.");
    }
}
?>
