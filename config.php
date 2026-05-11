<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "proot";
$dbname = "eventospanaderia";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Establecer charset
$conn->set_charset("utf8mb4");
?>