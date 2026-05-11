<?php
// Configuración de la base de datos
$servername = getenv('DB_HOST') ?: 'localhost';
$username   = getenv('DB_USER') ?: 'root';
$password   = getenv('DB_PASS') ?: 'proot';
$dbname     = getenv('DB_NAME') ?: 'eventospanaderia';
// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Establecer charset
$conn->set_charset("utf8mb4");
?>