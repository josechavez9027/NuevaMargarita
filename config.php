<?php
// Configuración de la base de datos
$host = "hopper.proxy.rlwy.net"; // El host del TCP Proxy
$port = "25286";                 // El puerto del TCP Proxy
$user = "root";
$pass = "pBAFmMJnJFbLMrdjkcirrtTrtREYYYaJ";
$db   = "railway";
// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Establecer charset
$conn->set_charset("utf8mb4");
?>