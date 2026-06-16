<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['surname']) && isset($_POST['nombre'])) {
   // http_response_code(405);
   //echo " los nombres son : ".$_POST['nombre']." y ".$_POST['surname'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['surname'];
   echo json_encode( ["nombre" => $nombre,
   "apellido" => $apellido]);
 
}

?>