<?php
require_once __DIR__ . "/conexion.php";

/**
 * Clase Producto
 * Maneja las operaciones CRUD sobre la tabla `productos`
 * Propiedades: id, codigo, producto, precio, cantidad
 */
class Producto {
    // Propiedades del producto
    public int    $id       = 0;
    public string $codigo   = "";
    public string $producto = "";
    public float  $precio   = 0.0;
    public int    $cantidad = 0;

    // Instancia de la BD
    private DB $db;

    public function __construct() {
        $this->db = DB::obtenerInstancia();
    }

    // ─────────────────────────────────────────────
    // VALIDACIÓN
    // ─────────────────────────────────────────────

    /**
     * Valida los campos obligatorios del producto
     * @return array  Lista de errores (vacía si todo está bien)
     */
    public function validar(): array {
        $errores = [];

        if (empty(trim($this->codigo))) {
            $errores[] = "El campo Código es obligatorio.";
        }

        if (empty(trim($this->producto))) {
            $errores[] = "El campo Producto es obligatorio.";
        }

        if (!is_numeric($this->precio) || $this->precio <= 0) {
            $errores[] = "El Precio debe ser un número mayor a 0.";
        }

        if (!is_numeric($this->cantidad) || $this->cantidad < 0) {
            $errores[] = "La Cantidad debe ser un número entero mayor o igual a 0.";
        }

        return $errores;
    }

    // ─────────────────────────────────────────────
    // GUARDAR (INSERT)
    // ─────────────────────────────────────────────

    /**
     * Inserta un nuevo producto en la base de datos
     * @return array  Respuesta JSON con success, message, accion
     */
    public function guardar(): array {
        // Validar antes de guardar
        $errores = $this->validar();
        if (!empty($errores)) {
            return [
                "success" => false,
                "message" => "Datos inválidos.",
                "accion"  => "Guardar",
                "errors"  => $errores
            ];
        }

        // Verificar que el código no esté duplicado
        $existe = $this->db->query(
            "SELECT id FROM productos WHERE codigo = :codigo",
            [":codigo" => $this->codigo]
        );

        if (!empty($existe)) {
            return [
                "success" => false,
                "message" => "Ya existe un producto con ese código.",
                "accion"  => "Guardar",
                "errors"  => ["Código duplicado: {$this->codigo}"]
            ];
        }

        $sql = "INSERT INTO productos (codigo, producto, precio, cantidad)
                VALUES (:codigo, :producto, :precio, :cantidad)";

        $id = $this->db->insertSeguro($sql, [
            ":codigo"   => $this->codigo,
            ":producto" => $this->producto,
            ":precio"   => $this->precio,
            ":cantidad" => $this->cantidad
        ]);

        if ($id > 0) {
            return [
                "success" => true,
                "message" => "Producto guardado correctamente.",
                "accion"  => "Guardar",
                "id"      => $id
            ];
        }

        return [
            "success" => false,
            "message" => "No se pudo guardar el producto.",
            "accion"  => "Guardar",
            "errors"  => ["Error al ejecutar el INSERT."]
        ];
    }

    // ─────────────────────────────────────────────
    // EDITAR (UPDATE)
    // ─────────────────────────────────────────────

    /**
     * Actualiza un producto existente por ID
     * @return array  Respuesta JSON con success, message, accion
     */
    public function editar(): array {
        // Validar ID
        if ($this->id <= 0) {
            return [
                "success" => false,
                "message" => "ID inválido para editar.",
                "accion"  => "Modificar",
                "errors"  => ["El ID del producto no es válido."]
            ];
        }

        // Validar campos
        $errores = $this->validar();
        if (!empty($errores)) {
            return [
                "success" => false,
                "message" => "Datos inválidos.",
                "accion"  => "Modificar",
                "errors"  => $errores
            ];
        }

        $sql = "UPDATE productos
                SET codigo   = :codigo,
                    producto = :producto,
                    precio   = :precio,
                    cantidad = :cantidad
                WHERE id = :id";

        $filas = $this->db->updateSeguro($sql, [
            ":codigo"   => $this->codigo,
            ":producto" => $this->producto,
            ":precio"   => $this->precio,
            ":cantidad" => $this->cantidad,
            ":id"       => $this->id
        ]);

        if ($filas > 0) {
            return [
                "success" => true,
                "message" => "Producto actualizado correctamente.",
                "accion"  => "Modificar"
            ];
        }

        return [
            "success" => false,
            "message" => "No se realizaron cambios o el producto no existe.",
            "accion"  => "Modificar",
            "errors"  => ["Sin cambios detectados o ID inexistente."]
        ];
    }

    // ─────────────────────────────────────────────
    // BUSCAR (SELECT)
    // ─────────────────────────────────────────────

    /**
     * Busca un producto por su código
     * @return array  Respuesta JSON con success, data/message
     */
    public function buscar(): array {
        if (empty(trim($this->codigo))) {
            return [
                "success" => false,
                "message" => "Ingrese un código para buscar.",
                "accion"  => "Buscar",
                "errors"  => ["El campo código está vacío."]
            ];
        }

        $resultado = $this->db->query(
            "SELECT * FROM productos WHERE codigo = :codigo LIMIT 1",
            [":codigo" => $this->codigo]
        );

        if (!empty($resultado)) {
            return [
                "success" => true,
                "message" => "Producto encontrado.",
                "accion"  => "Buscar",
                "data"    => $resultado[0]
            ];
        }

        return [
            "success" => false,
            "message" => "No se encontró ningún producto con ese código.",
            "accion"  => "Buscar",
            "errors"  => ["Código no registrado: {$this->codigo}"]
        ];
    }

    // ─────────────────────────────────────────────
    // LISTAR TODOS
    // ─────────────────────────────────────────────

    /**
     * Retorna todos los productos de la tabla
     * @return array  Array de productos
     */
    public function listar(): array {
        return $this->db->query("SELECT * FROM productos ORDER BY id DESC");
    }
}