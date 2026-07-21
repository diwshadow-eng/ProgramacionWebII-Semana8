<?php

/*=========================================
    CONEXIÓN A LA BASE DE DATOS
=========================================*/

$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$baseDatos = "TIENDA";

/*=========================================
    CREAR CONEXIÓN
=========================================*/

$conexion = new mysqli(
    $servidor,
    $usuario,
    $contrasena,
    $baseDatos
);

/*=========================================
    VALIDAR CONEXIÓN
=========================================*/

if ($conexion->connect_error) {

    die(
        "Error de conexión: " .
        $conexion->connect_error
    );

}

/*=========================================
    CONFIGURAR UTF-8
=========================================*/

$conexion->set_charset("utf8");