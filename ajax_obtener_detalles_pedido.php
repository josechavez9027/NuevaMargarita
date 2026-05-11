<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'])) {
    $id_pedido = intval($_POST['id_pedido']);
    $pedido = obtenerPedidoCompleto($conn, $id_pedido);
    
    if ($pedido) {
        echo json_encode([
            'success' => true,
            'pedido' => $pedido
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo encontrar el pedido'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Solicitud inválida'
    ]);
}

$conn->close();
?>