<?php
/**
 * registrar.php — Controlador principal del CRUD
 * Recibe $_POST['Accion'] y despacha: Guardar | Modificar | Buscar | Listar
 * Siempre retorna JSON limpio.
 */

// ── Cabecera JSON: debe ir ANTES de cualquier salida ──
header("Content-Type: application/json");

// ── Incluir modelos ──
require_once __DIR__ . "/Modelo/conexion.php";
require_once __DIR__ . "/Modelo/Productos.php";

// ── Leer la acción enviada ──
$accion = $_POST["Accion"] ?? $_GET["Accion"] ?? "";

// ── Instanciar el modelo ──
$p = new Producto();

// ── Mapear los campos del formulario a las propiedades del objeto ──
$p->id       = (int)   ($_POST["id"]       ?? 0);
$p->codigo   = trim(    $_POST["Codigo"]    ?? "");
$p->producto = trim(    $_POST["Producto"]  ?? "");
$p->precio   = (float)  ($_POST["Precio"]   ?? 0);
$p->cantidad = (int)   ($_POST["Cantidad"]  ?? 0);

// ── Switch principal de acciones ──
switch ($accion) {

    case "Guardar":
        $respuesta = $p->guardar();
        break;

    case "Modificar":
        $respuesta = $p->editar();
        break;

    case "Buscar":
        $respuesta = $p->buscar();
        break;

    case "Listar":
        $lista = $p->listar();
        $respuesta = [
            "success" => true,
            "accion"  => "Listar",
            "data"    => $lista
        ];
        break;

    default:
        $respuesta = [
            "success" => false,
            "message" => "Acción no reconocida: '$accion'.",
            "accion"  => $accion,
            "errors"  => ["El parámetro 'Accion' es inválido o está vacío."]
        ];
        break;
}

// ── Salida JSON — única línea de echo en todo el archivo ──
echo json_encode($respuesta);
exit;