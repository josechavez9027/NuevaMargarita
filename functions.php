<?php
require_once 'config.php';

// Función para obtener clientes
function obtenerClientes($conn) {
    $sql = "SELECT * FROM cliente ORDER BY nombre, apellido_paterno";
    $result = $conn->query($sql);
    
    $clientes = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $clientes[] = $row;
        }
    }
    
    return $clientes;
}

// Función para obtener productos
function obtenerProductos($conn) {
    $sql = "SELECT p.*, c.nombre_categoria 
            FROM producto p 
            JOIN categoria c ON p.id_categoria = c.id_categoria 
            ORDER BY c.nombre_categoria, p.nombre";
    $result = $conn->query($sql);
    
    $productos = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
    }
    
    return $productos;
}

// Función para obtener categorías
function obtenerCategorias($conn) {
    $sql = "SELECT * FROM categoria ORDER BY nombre_categoria";
    $result = $conn->query($sql);
    
    $categorias = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }
    }
    
    return $categorias;
}

// Función para obtener pedidos
function obtenerPedidos($conn) {
    $sql = "SELECT p.*, 
                   CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', COALESCE(c.apellido_materno, '')) as nombre_cliente
            FROM pedido p 
            JOIN cliente c ON p.id_cliente = c.id_cliente 
            ORDER BY p.fecha_entrega DESC, p.id_pedido DESC";
    $result = $conn->query($sql);
    
    $pedidos = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
    }
    
    return $pedidos;
}

// Función para guardar un nuevo cliente
function guardarCliente($conn, $datos) {
    $nombre = $conn->real_escape_string($datos['nombre']);
    $apellido_paterno = $conn->real_escape_string($datos['apellido_paterno']);
    $apellido_materno = $conn->real_escape_string($datos['apellido_materno'] ?? '');
    $telefono = $conn->real_escape_string($datos['telefono'] ?? '');
    $correo_electronico = $conn->real_escape_string($datos['correo_electronico'] ?? '');
    $direccion = $conn->real_escape_string($datos['direccion'] ?? '');
    
    $sql = "INSERT INTO cliente (nombre, apellido_paterno, apellido_materno, telefono, correo_electronico, direccion) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $nombre, $apellido_paterno, $apellido_materno, $telefono, $correo_electronico, $direccion);
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// Función para actualizar cliente
function actualizarCliente($conn, $datos) {
    $id_cliente = intval($datos['id_cliente']);
    $telefono = $conn->real_escape_string($datos['telefono'] ?? '');
    $correo_electronico = $conn->real_escape_string($datos['correo_electronico'] ?? '');
    $direccion = $conn->real_escape_string($datos['direccion'] ?? '');
    
    $sql = "UPDATE cliente SET telefono = ?, correo_electronico = ?, direccion = ? WHERE id_cliente = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $telefono, $correo_electronico, $direccion, $id_cliente);
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// Función para actualizar producto
function actualizarProducto($conn, $datos) {
    $id_producto = $conn->real_escape_string($datos['id_producto']);
    $precio_unitario = $conn->real_escape_string($datos['precio_unitario']);
    $disponible = $conn->real_escape_string($datos['disponible'] ?? 0);
    
    $sql = "UPDATE producto SET precio_unitario = ?, disponible = ? WHERE id_producto = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dii", $precio_unitario, $disponible, $id_producto);
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// NUEVA: Función para guardar nuevo producto
function guardarProducto($conn, $datos) {
    $nombre = $conn->real_escape_string($datos['nombre']);
    $descripcion = $conn->real_escape_string($datos['descripcion'] ?? '');
    $id_categoria = intval($datos['id_categoria']);
    $precio_unitario = floatval($datos['precio_unitario']);
    $disponible = intval($datos['disponible'] ?? 1);
    
    $sql = "INSERT INTO producto (nombre, descripcion, id_categoria, precio_unitario, disponible) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssidi", $nombre, $descripcion, $id_categoria, $precio_unitario, $disponible);
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// Función para guardar un nuevo pedido
function guardarPedido($conn, $datos) {
    $conn->begin_transaction();
    
    try {
        // Insertar el pedido
        $sql_pedido = "INSERT INTO pedido (id_cliente, fecha_entrega, hora_entrega, subtotal, anticipo, saldo, observaciones) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_pedido = $conn->prepare($sql_pedido);
        $stmt_pedido->bind_param("issddds", 
            $datos['id_cliente'],
            $datos['fecha_entrega'],
            $datos['hora_entrega'],
            $datos['subtotal'],
            $datos['anticipo'],
            $datos['saldo'],
            $datos['observaciones']
        );
        
        $stmt_pedido->execute();
        $id_pedido = $conn->insert_id;
        $stmt_pedido->close();
        
        // Insertar los detalles del pedido
        $sql_detalle = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal) 
                       VALUES (?, ?, ?, ?, ?)";
        
        $stmt_detalle = $conn->prepare($sql_detalle);
        
        foreach ($datos['productos'] as $producto) {
            $stmt_detalle->bind_param("iiidd", 
                $id_pedido,
                $producto['id_producto'],
                $producto['cantidad'],
                $producto['precio_unitario'],
                $producto['subtotal']
            );
            $stmt_detalle->execute();
        }
        
        $stmt_detalle->close();
        
        // Insertar la entrega
        $sql_entrega = "INSERT INTO entrega (id_pedido, direccion_entrega) 
                       VALUES (?, ?)";
        
        // Obtener dirección del cliente
        $sql_cliente = "SELECT direccion FROM cliente WHERE id_cliente = ?";
        $stmt_cliente = $conn->prepare($sql_cliente);
        $stmt_cliente->bind_param("i", $datos['id_cliente']);
        $stmt_cliente->execute();
        $stmt_cliente->bind_result($direccion);
        $stmt_cliente->fetch();
        $stmt_cliente->close();
        
        $stmt_entrega = $conn->prepare($sql_entrega);
        $stmt_entrega->bind_param("is", $id_pedido, $direccion);
        $stmt_entrega->execute();
        $stmt_entrega->close();
        
        $conn->commit();
        return ['success' => true, 'id_pedido' => $id_pedido];
        
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// NUEVA: Función para editar pedido
function editarPedido($conn, $datos) {
    $id_pedido = intval($datos['id_pedido']);
    $fecha_entrega = $conn->real_escape_string($datos['fecha_entrega']);
    $hora_entrega = $conn->real_escape_string($datos['hora_entrega']);
    $anticipo = floatval($datos['anticipo']);
    $observaciones = $conn->real_escape_string($datos['observaciones'] ?? '');
    
    // Obtener subtotal actual
    $sql_subtotal = "SELECT subtotal FROM pedido WHERE id_pedido = ?";
    $stmt = $conn->prepare($sql_subtotal);
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    $stmt->bind_result($subtotal);
    $stmt->fetch();
    $stmt->close();
    
    // Calcular nuevo saldo
    $saldo = $subtotal - $anticipo;
    
    $sql = "UPDATE pedido SET fecha_entrega = ?, hora_entrega = ?, anticipo = ?, saldo = ?, observaciones = ? WHERE id_pedido = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssddsi", $fecha_entrega, $hora_entrega, $anticipo, $saldo, $observaciones, $id_pedido);
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// Función para cambiar el estado de un pedido
function cambiarEstadoPedido($conn, $id_pedido, $estado) {
    $sql = "UPDATE pedido SET estado = ? WHERE id_pedido = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $estado, $id_pedido);
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// Función para obtener detalles de un pedido
function obtenerDetallesPedido($conn, $id_pedido) {
    $sql = "SELECT dp.*, p.nombre as nombre_producto
            FROM detalle_pedido dp
            JOIN producto p ON dp.id_producto = p.id_producto
            WHERE dp.id_pedido = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $detalles = [];
    while ($row = $result->fetch_assoc()) {
        $detalles[] = $row;
    }
    
    $stmt->close();
    return $detalles;
}

// Función para eliminar un cliente (solo si no tiene pedidos)
function eliminarCliente($conn, $id_cliente) {
    $id_cliente = intval($id_cliente);

    // Verificar si el cliente tiene pedidos
    $sql_check = "SELECT COUNT(*) as total FROM pedido WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();

    if ($total > 0) {
        return ['success' => false, 'message' => "No se puede eliminar el cliente porque tiene $total pedido(s) registrado(s)."];
    }

    $sql = "DELETE FROM cliente WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cliente);
    $result = $stmt->execute();
    $stmt->close();

    if ($result) {
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => 'Error al eliminar el cliente.'];
    }
}

// Función para eliminar un producto (solo si no está en pedidos)
function eliminarProducto($conn, $id_producto) {
    $id_producto = intval($id_producto);

    // Verificar si el producto está en algún detalle de pedido
    $sql_check = "SELECT COUNT(*) as total FROM detalle_pedido WHERE id_producto = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();

    if ($total > 0) {
        return ['success' => false, 'message' => "No se puede eliminar el producto porque está asociado a $total pedido(s)."];
    }

    $sql = "DELETE FROM producto WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_producto);
    $result = $stmt->execute();
    $stmt->close();

    if ($result) {
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => 'Error al eliminar el producto.'];
    }
}

// Función para obtener información completa de un pedido
function obtenerPedidoCompleto($conn, $id_pedido) {
    $sql = "SELECT p.*, 
                   CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', COALESCE(c.apellido_materno, '')) as nombre_cliente,
                   c.telefono, c.correo_electronico, c.direccion
            FROM pedido p 
            JOIN cliente c ON p.id_cliente = c.id_cliente 
            WHERE p.id_pedido = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    $result = $stmt->get_result();
    $pedido = $result->fetch_assoc();
    $stmt->close();
    
    if ($pedido) {
        $pedido['detalles'] = obtenerDetallesPedido($conn, $id_pedido);
    }
    
    return $pedido;
}
?>