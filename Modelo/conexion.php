<?php
/**
 * Clase DB - Conexión singleton a MySQL mediante PDO
 * Expone métodos: insertSeguro(), updateSeguro(), query()
 */
class DB {
    private static $instancia = null;
    private $conn;

    // Configuración de la base de datos
    private $host     = "localhost";
    private $dbname   = "productosdb";
    private $usuario  = "root";
    private $password = "";

    // Constructor privado: evita instanciación directa
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->usuario,
                $this->password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false
                ]
            );
        } catch (PDOException $e) {
            // Retornar JSON de error y detener ejecución
            header("Content-Type: application/json");
            echo json_encode([
                "success" => false,
                "message" => "Error de conexión: " . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Obtener la única instancia de la clase (Singleton)
     */
    public static function obtenerInstancia(): DB {
        if (self::$instancia === null) {
            self::$instancia = new DB();
        }
        return self::$instancia;
    }

    /**
     * Ejecutar INSERT con parámetros preparados
     * @param string $sql   Consulta SQL con placeholders :param
     * @param array  $datos Array asociativo con los valores
     * @return int          ID del registro insertado
     */
    public function insertSeguro(string $sql, array $datos): int {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($datos);
        return (int) $this->conn->lastInsertId();
    }

    /**
     * Ejecutar UPDATE con parámetros preparados
     * @param string $sql   Consulta SQL con placeholders :param
     * @param array  $datos Array asociativo con los valores
     * @return int          Número de filas afectadas
     */
    public function updateSeguro(string $sql, array $datos): int {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($datos);
        return $stmt->rowCount();
    }

    /**
     * Ejecutar SELECT con parámetros opcionales
     * @param string $sql    Consulta SQL
     * @param array  $params Parámetros opcionales para la consulta
     * @return array         Resultados como array asociativo
     */
    public function query(string $sql, array $params = []): array {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}