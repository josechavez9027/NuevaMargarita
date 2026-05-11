<?php
require_once 'config.php';
require_once 'functions.php';

// Obtener datos iniciales
$clientes = obtenerClientes($conn);
$productos = obtenerProductos($conn);
$pedidos = obtenerPedidos($conn);
$categorias = obtenerCategorias($conn);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'guardar_cliente':
                $resultado = guardarCliente($conn, $_POST);
                if ($resultado) {
                    echo "<script>alert('Cliente guardado correctamente');</script>";
                    header("Refresh:0");
                    exit;
                } else {
                    echo "<script>alert('Error al guardar el cliente');</script>";
                }
                break;

            case 'editar_pedido':
                $resultado = editarPedido($conn, $_POST);
                if ($resultado) {
                    echo "<script>alert('Pedido actualizado correctamente');</script>";
                    header("Refresh:0");
                    exit;
                } else {
                    echo "<script>alert('Error al actualizar el pedido');</script>";
                }
                break;
                    
                case 'guardar_producto':
                    $resultado = guardarProducto($conn, $_POST);
                    if ($resultado) {
                        echo "<script>alert('Producto guardado correctamente');</script>";
                        header("Refresh:0");
                        exit;
                    } else {
                        echo "<script>alert('Error al guardar el producto');</script>";
                    }
                    break;
                
            case 'guardar_pedido':
                // Procesar productos del pedido
                $productos_pedido = [];
                if (isset($_POST['productos'])) {
                    foreach ($_POST['productos'] as $producto) {
                        $productos_pedido[] = [
                            'id_producto' => $producto['id_producto'],
                            'cantidad' => $producto['cantidad'],
                            'precio_unitario' => $producto['precio_unitario'],
                            'subtotal' => $producto['cantidad'] * $producto['precio_unitario']
                        ];
                    }
                }
                
                $datos_pedido = [
                    'id_cliente' => $_POST['id_cliente'],
                    'fecha_entrega' => $_POST['fecha_entrega'],
                    'hora_entrega' => $_POST['hora_entrega'],
                    'subtotal' => $_POST['subtotal'],
                    'anticipo' => $_POST['anticipo'],
                    'saldo' => $_POST['subtotal'] - $_POST['anticipo'],
                    'observaciones' => $_POST['observaciones'],
                    'productos' => $productos_pedido
                ];
                
                $resultado = guardarPedido($conn, $datos_pedido);
                if ($resultado['success']) {
                    echo "<script>alert('Pedido guardado correctamente. ID: ' + {$resultado['id_pedido']});</script>";
                    header("Refresh:0");
                    exit;
                } else {
                    echo "<script>alert('Error al guardar el pedido: ' + '{$resultado['message']}');</script>";
                }
                break;
                
            case 'cambiar_estado':
                $resultado = cambiarEstadoPedido($conn, $_POST['id_pedido'], $_POST['estado']);
                if ($resultado) {
                    echo "<script>alert('Estado del pedido actualizado correctamente');</script>";
                    header("Refresh:0");
                    exit;
                } else {
                    echo "<script>alert('Error al actualizar el estado');</script>";
                }
                break;

                case 'actualizar_cliente':
                $resultado = actualizarCliente($conn, $_POST);
                if ($resultado) {
                    echo "<script>alert('Cliente actualizado correctamente');</script>";
                    header("Refresh:0");
                    exit;
                } else {
                    echo "<script>alert('Error al actualizar el cliente');</script>";
                }
                break;
                
                case 'actualizar_producto':
                $resultado = actualizarProducto($conn, $_POST);
                if ($resultado) {
                    echo "<script>alert('Producto actualizado correctamente');</script>";
                    header("Refresh:0");
                    exit;
                } else {
                    echo "<script>alert('Error al actualizar el producto');</script>";
                }
                break;

            case 'eliminar_cliente':
                $resultado = eliminarCliente($conn, $_POST['id_cliente']);
                if ($resultado['success']) {
                    echo "<script>alert('Cliente eliminado correctamente.');</script>";
                    header("Refresh:0");
                    exit;
                } else {
                    echo "<script>alert('" . addslashes($resultado['message']) . "');</script>";
                }
                break;

            case 'eliminar_producto':
                $resultado = eliminarProducto($conn, $_POST['id_producto']);
                if ($resultado['success']) {
                    echo "<script>alert('Producto eliminado correctamente.');</script>";
                    header("Refresh:0");
                    exit;
                } else {
                    echo "<script>alert('" . addslashes($resultado['message']) . "');</script>";
                }
                break;
                    }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Producto integrador. Aplicación Web con JavaScript
</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100">
    <div class="container-fluid">
        <header class="text-white p-4 mb-4 shadow-md">
            <div class="container">
                <h1 class="text-3xl font-bold">Panadería "La Nueva Margarita"</h1>
                <p class="text-lg">Sistema de Administración de Pedidos</p>
            </div>
        </header>

        <div class="container">
            <!-- Navegación por pestañas -->
            <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pedidos-tab" data-bs-toggle="tab" data-bs-target="#pedidos" type="button" role="tab" aria-controls="pedidos" aria-selected="false">Pedidos</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="clientes-tab" data-bs-toggle="tab" data-bs-target="#clientes" type="button" role="tab" aria-controls="clientes" aria-selected="false">Clientes</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="productos-tab" data-bs-toggle="tab" data-bs-target="#productos" type="button" role="tab" aria-controls="productos" aria-selected="true">Productos</button>
                </li>
            </ul>

            <!-- Contenido de las pestañas -->
            <div class="tab-content" id="myTabContent">
                <!-- Pestaña de Pedidos -->
                <div class="tab-pane fade" id="pedidos" role="tabpanel" aria-labelledby="pedidos-tab">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2 class="text-xl font-bold mb-3">Lista de Pedidos</h2>
                        </div>
                        <div class="col-md-6 text-end">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoPedidoModal">Nuevo Pedido</button>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="tablaPedidos">
                                    <thead>
                                        <tr>
                                            <th data-sort="id_pedido">ID</th>
                                            <th data-sort="nombre_cliente">Cliente</th>
                                            <th data-sort="fecha_entrega">Fecha Entrega</th>
                                            <th data-sort="estado">Estado</th>
                                            <th data-sort="subtotal">Subtotal</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pedidos as $pedido): ?>
                                        <tr>
                                            <td><?php echo $pedido['id_pedido']; ?></td>
                                            <td><?php echo $pedido['nombre_cliente']; ?></td>
                                            <td><?php echo $pedido['fecha_entrega']; ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $pedido['estado']; ?>">
                                                    <?php 
                                                    $estados = [
                                                        'pendiente' => 'Pendiente',
                                                        'confirmado' => 'Confirmado',
                                                        'en_proceso' => 'En Proceso',
                                                        'listo' => 'Listo',
                                                        'entregado' => 'Entregado',
                                                        'cancelado' => 'Cancelado'
                                                    ];
                                                    echo $estados[$pedido['estado']] ?? $pedido['estado'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td>$<?php echo number_format($pedido['subtotal'], 2); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary btn-editar-pedido me-1 " 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editarPedidoModal"
                                                            data-pedido-id="<?php echo $pedido['id_pedido']; ?>"
                                                            data-fecha_entrega="<?php echo $pedido['fecha_entrega']; ?>"
                                                            data-hora_entrega="<?php echo $pedido['hora_entrega']; ?>"
                                                            >
                                                        Editar
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary btn-action me-1" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#cambiarEstadoModal"
                                                            data-pedido-id="<?php echo $pedido['id_pedido']; ?>"
                                                            data-estado-actual="<?php echo $pedido['estado']; ?>">
                                                        Cambiar Estado
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info btn-action" 
                                                            onclick="verDetalles(<?php echo $pedido['id_pedido']; ?>)">
                                                        Ver Detalles
                                                    </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pestaña de Clientes -->
                <div class="tab-pane fade" id="clientes" role="tabpanel" aria-labelledby="clientes-tab">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2 class="text-xl font-bold mb-3">Lista de Clientes</h2>
                        </div>
                        <div class="col-md-6 text-end">
                            <button class="btn btn-primary " data-bs-toggle="modal" data-bs-target="#nuevoClienteModal">Nuevo Cliente</button>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="tablaClientes">
                                    <thead>
                                        <tr>
                                            <th data-sort="id_cliente">ID</th>
                                            <th data-sort="nombre">Nombre</th>
                                            <th data-sort="telefono">Teléfono</th>
                                            <th data-sort="correo_electronico">Email</th>
                                            <th data-sort="direccion">Dirección</th>
                                            <th>Acciones</th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($clientes as $cliente): ?>
                                        <tr>
                                            <td><?php echo $cliente['id_cliente']; ?></td>
                                            <td>
                                                <?php echo $cliente['nombre'] . ' ' . $cliente['apellido_paterno'] . ' ' . ($cliente['apellido_materno'] ?? ''); ?>
                                            </td>
                                            <td><?php echo $cliente['telefono'] ?? ''; ?></td>
                                            <td><?php echo $cliente['correo_electronico'] ?? ''; ?></td>
                                            <td><?php echo $cliente['direccion'] ?? ''; ?></td>
                                            <td>

                                            
                                            <button class="btn btn-sm btn-outline-primary btn-editar-cliente btn-action" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editarClienteModal"
                                                    data-cliente-id="<?php echo $cliente['id_cliente']; ?>"
                                                    data-telefono="<?php echo $cliente['telefono']; ?>"
                                                    data-email="<?php echo $cliente['correo_electronico']; ?>"
                                                    data-direccion="<?php echo $cliente['direccion']; ?>">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-action"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#eliminarClienteModal"
                                                    data-cliente-id="<?php echo $cliente['id_cliente']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido_paterno'] . ' ' . ($cliente['apellido_materno'] ?? '')); ?>">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pestaña de Productos -->
                <div class="tab-pane fade show active" id="productos" role="tabpanel" aria-labelledby="productos-tab">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2 class="text-xl font-bold mb-3">Lista de Productos</h2>
                        </div>
                        <div class="col-md-6 text-end">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoProductoModal">Nuevo Producto</button>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="tablaProductos">
                                    <thead>
                                        <tr>
                                            <th data-sort="id_producto">ID</th>
                                            <th data-sort="nombre">Nombre</th>
                                            <th data-sort="descripcion">Descripción</th>
                                            <th data-sort="nombre_categoria">Categoría</th>
                                            <th data-sort="precio_unitario">Precio</th>
                                            <th data-sort="disponible">Disponible</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($productos as $producto): ?>
                                        <tr>
                                            <td><?php echo $producto['id_producto']; ?></td>
                                            <td><?php echo $producto['nombre']; ?></td>
                                            <td><?php echo $producto['descripcion']; ?></td>
                                            <td><?php echo $producto['nombre_categoria']; ?></td>
                                            <td>$<?php echo number_format($producto['precio_unitario'], 2); ?></td>
                                            <td>
                                                <?php if ($producto['disponible']): ?>
                                                    <span class="badge bg-success">Sí</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary btn-action" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editarProductoModal"
                                                        data-producto-id="<?php echo $producto['id_producto']; ?>"
                                                        data-nombre="<?php echo $producto['nombre']; ?>"
                                                        data-precio="<?php echo $producto['precio_unitario']; ?>"
                                                        data-disponible="<?php echo $producto['disponible']; ?>">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger btn-action"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#eliminarProductoModal"
                                                        data-producto-id="<?php echo $producto['id_producto']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para nuevo cliente -->
    <div class="modal fade" id="nuevoClienteModal" tabindex="-1" aria-labelledby="nuevoClienteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nuevoClienteModalLabel">Agregar Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="guardar_cliente">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="apellido_paterno" class="form-label">Apellido Paterno *</label>
                            <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" required>
                        </div>
                        <div class="mb-3">
                            <label for="apellido_materno" class="form-label">Apellido Materno</label>
                            <input type="text" class="form-control" id="apellido_materno" name="apellido_materno">
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono">
                        </div>
                        <div class="mb-3">
                            <label for="correo_electronico" class="form-label">Email</label>
                            <input type="email" class="form-control" id="correo_electronico" name="correo_electronico">
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para nuevo pedido -->
    <div class="modal fade" id="nuevoPedidoModal" tabindex="-1" aria-labelledby="nuevoPedidoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nuevoPedidoModalLabel">Crear Nuevo Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="" id="formPedido">
                    <input type="hidden" name="action" value="guardar_pedido">
                    <input type="hidden" name="subtotal" id="subtotalPedido" value="0">
                    
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="id_cliente" class="form-label">Cliente *</label>
                                <select class="form-select" id="id_cliente" name="id_cliente" required>
                                    <option value="">Seleccionar cliente</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id_cliente']; ?>">
                                        <?php echo $cliente['nombre'] . ' ' . $cliente['apellido_paterno'] . ' ' . ($cliente['apellido_materno'] ?? ''); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_entrega" class="form-label">Fecha de Entrega *</label>
                                <input type="date" class="form-control" id="fecha_entrega" name="fecha_entrega" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="hora_entrega" class="form-label">Hora de Entrega *</label>
                                <input type="time" class="form-control" id="hora_entrega" name="hora_entrega" required>
                            </div>
                            <div class="col-md-6">
                                <label for="anticipo" class="form-label">Anticipo</label>
                                <input type="number" class="form-control" id="anticipo" name="anticipo" 
                                       min="0" step="0.01" value="0">
                            </div>
                        </div>
                        
                        <h5 class="mt-4 mb-3">Productos del Pedido</h5>
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <select class="form-select" id="productoSeleccionado">
                                    <option value="">Seleccionar producto</option>
                                    <?php foreach ($productos as $producto): ?>
                                        <?php if ($producto['disponible']): ?>
                                        <option value="<?php echo $producto['id_producto']; ?>" 
                                                data-precio="<?php echo $producto['precio_unitario']; ?>"
                                                data-nombre="<?php echo $producto['nombre']; ?>">
                                            <?php echo $producto['nombre']; ?> - $<?php echo number_format($producto['precio_unitario'], 2); ?>
                                        </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" id="cantidadProducto" min="1" value="1">
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-primary w-100" id="agregarProducto">Agregar Producto</button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm" id="tablaProductosPedido">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Subtotal</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td id="totalPedido">$0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="guardarPedidoBtn">Guardar Pedido</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para cambiar estado de pedido -->
    <div class="modal fade" id="cambiarEstadoModal" tabindex="-1" aria-labelledby="cambiarEstadoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cambiarEstadoModalLabel">Cambiar Estado del Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="cambiar_estado">
                    <input type="hidden" name="id_pedido" id="pedidoIdEstado">
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="pendiente">Pendiente</option>
                                <option value="confirmado">Confirmado</option>
                                <option value="en_proceso">En Proceso</option>
                                <option value="listo">Listo</option>
                                <option value="entregado">Entregado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
<div class="modal fade" id="editarClienteModal" tabindex="-1" aria-labelledby="editarClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarClienteModalLabel">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="actualizar_cliente">
                <input type="hidden" name="id_cliente" id="clienteIdEditar">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="telefono_editar" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono_editar" name="telefono">
                    </div>
                    <div class="mb-3">
                        <label for="correo_electronico_editar" class="form-label">Email</label>
                        <input type="email" class="form-control" id="correo_electronico_editar" name="correo_electronico">
                    </div>
                    <div class="mb-3">
                        <label for="direccion_editar" class="form-label">Dirección</label>
                        <textarea class="form-control" id="direccion_editar" name="direccion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal confirmar eliminar cliente -->
<div class="modal fade" id="eliminarClienteModal" tabindex="-1" aria-labelledby="eliminarClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="eliminarClienteModalLabel">Eliminar Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="eliminar_cliente">
                <input type="hidden" name="id_cliente" id="eliminarClienteId">
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar al cliente <strong id="eliminarClienteNombre"></strong>?</p>
                    <p class="text-muted small mb-0">Esta acción no se puede deshacer. Solo se puede eliminar si el cliente no tiene pedidos registrados.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal confirmar eliminar producto -->
<div class="modal fade" id="eliminarProductoModal" tabindex="-1" aria-labelledby="eliminarProductoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="eliminarProductoModalLabel">Eliminar Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="eliminar_producto">
                <input type="hidden" name="id_producto" id="eliminarProductoId">
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar el producto <strong id="eliminarProductoNombre"></strong>?</p>
                    <p class="text-muted small mb-0">Esta acción no se puede deshacer. Solo se puede eliminar si el producto no está asociado a ningún pedido.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar producto -->
<div class="modal fade" id="editarProductoModal" tabindex="-1" aria-labelledby="editarProductoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarProductoModalLabel">Editar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="actualizar_producto">
                <input type="hidden" name="id_producto" id="productoIdEditar">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Producto</label>
                        <input type="text" class="form-control" id="nombreProductoEditar" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="precio_unitario_editar" class="form-label">Precio Unitario *</label>
                        <input type="number" class="form-control" id="precio_unitario_editar" name="precio_unitario" 
                               min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="disponible_editar" class="form-label">Disponible</label>
                        <select class="form-select" id="disponible_editar" name="disponible">
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver detalles del pedido -->
<div class="modal fade" id="detallesPedidoModal" tabindex="-1" aria-labelledby="detallesPedidoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detallesPedidoModalLabel">Detalles del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detallesPedidoContent">
                <!-- Los detalles se cargarán aquí dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar pedido -->
<div class="modal fade" id="editarPedidoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editarPedidoContent"></div>
        </div>
    </div>
</div>

<!-- Modal para agregar producto -->
<div class="modal fade" id="nuevoProductoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nuevo Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="guardar_producto">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoría *</label>
                        <select class="form-select" name="id_categoria" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['id_categoria']; ?>">
                                <?php echo $categoria['nombre_categoria']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio Unitario *</label>
                        <input type="number" class="form-control" name="precio_unitario" 
                               min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Disponible</label>
                        <select class="form-select" name="disponible">
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        let productosPedido = [];

        $(document).ready(function() {

            $('#tablaPedidos').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "pageLength": 10,
                "order": [[0, 'desc']] 
            });

            $('#tablaClientes').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "pageLength": 10,
                "deferRender": true 
            });

            $('#tablaProductos').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "pageLength": 10
            });

            const hoy = new Date().toISOString().split('T')[0];
            $('#fecha_entrega').attr('min', hoy);

            $('#agregarProducto').click(function() {
                const productoSelect = $('#productoSeleccionado');
                const productoId = productoSelect.val();
                const cantidad = parseInt($('#cantidadProducto').val());
                
                if (!productoId) {
                    alert('Por favor selecciona un producto');
                    return;
                }
                
                if (isNaN(cantidad) || cantidad < 1) {
                    alert('Por favor ingresa una cantidad válida');
                    return;
                }
                
                const precio = parseFloat(productoSelect.find('option:selected').data('precio'));
                const nombre = productoSelect.find('option:selected').data('nombre');
                const subtotal = precio * cantidad;
                const productoExistente = productosPedido.find(p => p.id_producto == productoId);
                
                if (productoExistente) {
                    productoExistente.cantidad += cantidad;
                    productoExistente.subtotal = productoExistente.cantidad * precio;
                } else {
                    productosPedido.push({
                        id_producto: productoId,
                        nombre: nombre,
                        cantidad: cantidad,
                        precio_unitario: precio,
                        subtotal: subtotal
                    });
                }
                
                actualizarTablaProductosPedido();
                calcularTotalPedido();
                
                productoSelect.val('');
                $('#cantidadProducto').val(1);
            });

            $('#cambiarEstadoModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const pedidoId = button.data('pedido-id');
                const estadoActual = button.data('estado-actual');
                
                $('#pedidoIdEstado').val(pedidoId);
                $('#estado').val(estadoActual);
            });

            $('#formPedido').on('submit', function() {
                productosPedido.forEach((producto, index) => {
                    $(this).append(
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'productos[' + index + '][id_producto]',
                            value: producto.id_producto
                        }),
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'productos[' + index + '][cantidad]',
                            value: producto.cantidad
                        }),
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'productos[' + index + '][precio_unitario]',
                            value: producto.precio_unitario
                        })
                    );
                });
                
                return true;
            });
        });

        function actualizarTablaProductosPedido() {
            const tbody = $('#tablaProductosPedido tbody');
            tbody.empty();
            
            productosPedido.forEach((producto, index) => {
                const fila = `
                    <tr>
                        <td>${producto.nombre}</td>
                        <td>${producto.cantidad}</td>
                        <td>$${producto.precio_unitario.toFixed(2)}</td>
                        <td>$${producto.subtotal.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarProductoPedido(${index})">Eliminar</button>
                        </td>
                    </tr>
                `;
                tbody.append(fila);
            });
        }

        function eliminarProductoPedido(index) {
            productosPedido.splice(index, 1);
            actualizarTablaProductosPedido();
            calcularTotalPedido();
        }

        function calcularTotalPedido() {
            const total = productosPedido.reduce((sum, producto) => sum + producto.subtotal, 0);
            $('#totalPedido').text(`$${total.toFixed(2)}`);
            $('#subtotalPedido').val(total);
            return total;
        }

function verDetalles(pedidoId) {

    $('#detallesPedidoContent').html(`
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando detalles del pedido...</p>
        </div>
    `);
    
    $('#detallesPedidoModal').modal('show');
    
    $.ajax({
        url: 'ajax_obtener_detalles_pedido.php',
        type: 'POST',
        data: { id_pedido: pedidoId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const pedido = response.pedido;
                let html = `
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6><strong>ID Pedido:</strong> ${pedido.id_pedido}</h6>
                            <h6><strong>Cliente:</strong> ${pedido.nombre_cliente}</h6>
                            <h6><strong>Teléfono:</strong> ${pedido.telefono || 'No especificado'}</h6>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>Fecha Entrega:</strong> ${pedido.fecha_entrega}</h6>
                            <h6><strong>Hora Entrega:</strong> ${pedido.hora_entrega}</h6>
                            <h6><strong>Estado:</strong> <span class="status-badge status-${pedido.estado}">${obtenerTextoEstado(pedido.estado)}</span></h6>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6><strong>Dirección:</strong></h6>
                            <p>${pedido.direccion || 'No especificada'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>Observaciones:</strong></h6>
                            <p>${pedido.observaciones || 'Ninguna'}</p>
                        </div>
                    </div>
                    
                    <h5 class="mb-3">Productos del Pedido</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                pedido.detalles.forEach(detalle => {
                    html += `
                        <tr>
                            <td>${detalle.nombre_producto}</td>
                            <td>${detalle.cantidad}</td>
                            <td>$${parseFloat(detalle.precio_unitario).toFixed(2)}</td>
                            <td>$${parseFloat(detalle.subtotal).toFixed(2)}</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><strong>$${parseFloat(pedido.subtotal).toFixed(2)}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Anticipo:</strong></td>
                                    <td><strong>$${parseFloat(pedido.anticipo).toFixed(2)}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Saldo:</strong></td>
                                    <td><strong>$${parseFloat(pedido.saldo).toFixed(2)}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `;
                
                $('#detallesPedidoContent').html(html);
            } else {
                $('#detallesPedidoContent').html(`
                    <div class="alert alert-danger">
                        Error al cargar los detalles del pedido: ${response.message}
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            $('#detallesPedidoContent').html(`
                <div class="alert alert-danger">
                    Error al cargar los detalles del pedido. Por favor, intente nuevamente.
                </div>
            `);
        }
    });
}

function obtenerTextoEstado(estado) {
    const estados = {
        'pendiente': 'Pendiente',
        'confirmado': 'Confirmado',
        'en_proceso': 'En Proceso',
        'listo': 'Listo',
        'entregado': 'Entregado',
        'cancelado': 'Cancelado'
    };
    return estados[estado] || estado;
}

        $('#editarProductoModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const productoId = button.data('producto-id');
            const nombre = button.data('nombre');
            const precio = button.data('precio');
            const disponible = button.data('disponible');
            
            $('#productoIdEditar').val(productoId);
            $('#nombreProductoEditar').val(nombre);
            $('#precio_unitario_editar').val(precio);
            $('#disponible_editar').val(disponible.toString());
        });

        	$("#editarClienteModal").on("show.bs.modal", function (event) {
		const button = $(event.relatedTarget);
		const clienteId = button.data("cliente-id");
		const telefono = button.data("telefono") || "";
		const email = button.data("email") || "";
		const direccion = button.data("direccion") || "";

		$("#clienteIdEditar").val(clienteId);
		$("#telefono_editar").val(telefono);
		$("#correo_electronico_editar").val(email);
		$("#direccion_editar").val(direccion);
	});

    	$(document).on("click", ".btn-editar-pedido", function (e) {
		e.preventDefault();
		const pedidoId = $(this).data("pedido-id");

		$("#editarPedidoContent").html(`
			<div class="text-center">
				<div class="spinner-border" role="status">
					<span class="visually-hidden">Cargando...</span>
				</div>
			</div>
		`);

		$("#editarPedidoModal").modal("show");

		$.ajax({
			url: "ajax_obtener_detalles_pedido.php",
			type: "POST",
			data: { id_pedido: pedidoId },
			dataType: "json",
			success: function (response) {
				if (response.success) {
					cargarFormularioEditarPedido(response.pedido);
				} else {
					$("#editarPedidoContent").html(`
						<div class="alert alert-danger">
							Error al cargar el pedido
						</div>
					`);
				}
			},
		});
	});

    function cargarFormularioEditarPedido(pedido) {
	const html = `
        <form method="POST" action="" id="formEditarPedido">
            <input type="hidden" name="action" value="editar_pedido">
            <input type="hidden" name="id_pedido" value="${pedido.id_pedido}">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Cliente</label>
                    <input type="text" class="form-control" value="${
											pedido.nombre_cliente
										}" readonly>
                </div>
                <div class="col-md-6">
                    <label for="fecha_entrega_edit" class="form-label">Fecha de Entrega *</label>
                    <input type="date" class="form-control" id="fecha_entrega_edit" 
                           name="fecha_entrega" value="${
															pedido.fecha_entrega
														}" required>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="hora_entrega_edit" class="form-label">Hora de Entrega *</label>
                    <input type="time" class="form-control" id="hora_entrega_edit" 
                           name="hora_entrega" value="${
															pedido.hora_entrega
														}" required>
                </div>
                <div class="col-md-6">
                    <label for="anticipo_edit" class="form-label">Anticipo</label>
                    <input type="number" class="form-control" id="anticipo_edit" 
                           name="anticipo" value="${
															pedido.anticipo
														}" min="0" step="0.01">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="observaciones_edit" class="form-label">Observaciones</label>
                <textarea class="form-control" id="observaciones_edit" 
                          name="observaciones" rows="3">${
														pedido.observaciones || ""
													}</textarea>
            </div>
            
            <div class="text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    `;

	$("#editarPedidoContent").html(html);

	$("#formEditarPedido").on("submit", function (e) {
		e.preventDefault();

		const anticipo = parseFloat($("#anticipo_edit").val()) || 0;

		if (anticipo > pedido.subtotal) {
			alert("El anticipo no puede ser mayor al subtotal del pedido");
			return false;
		}

            $.ajax({
                url: "index.php",
                type: "POST",
                data: $(this).serialize(),
                success: function (response) {
                    location.reload();
                },
                error: function () {
                    alert("Error al actualizar el pedido");
                },
            });
        });
    }

    // Poblar modal eliminar cliente
    $('#eliminarClienteModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        $('#eliminarClienteId').val(button.data('cliente-id'));
        $('#eliminarClienteNombre').text(button.data('nombre'));
    });

    // Poblar modal eliminar producto
    $('#eliminarProductoModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        $('#eliminarProductoId').val(button.data('producto-id'));
        $('#eliminarProductoNombre').text(button.data('nombre'));
    });

    </script>
</body>
</html>
<?php $conn->close(); ?>