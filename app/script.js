$(document).ready(function () {
	// Variables globales
	let productosPedido = [];

	// Inicializar DataTables para todas las tablas
	$("#tablaPedidos").DataTable({
		language: {
			url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
		},
		pageLength: 10,
		order: [[0, "desc"]],
	});

	$("#tablaClientes").DataTable({
		language: {
			url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
		},
		pageLength: 10,
		deferRender: true,
	});

	$("#tablaProductos").DataTable({
		language: {
			url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
		},
		pageLength: 10,
	});

	// Configurar fecha mínima para entrega (hoy)
	const hoy = new Date().toISOString().split("T")[0];
	$("#fecha_entrega").attr("min", hoy);

	// Agregar producto al pedido
	$("#agregarProducto").click(function () {
		const productoSelect = $("#productoSeleccionado");
		const productoId = productoSelect.val();
		const cantidad = parseInt($("#cantidadProducto").val());

		if (!productoId) {
			alert("Por favor selecciona un producto");
			return;
		}

		if (isNaN(cantidad) || cantidad < 1) {
			alert("Por favor ingresa una cantidad válida");
			return;
		}

		const precio = parseFloat(
			productoSelect.find("option:selected").data("precio")
		);
		const nombre = productoSelect.find("option:selected").data("nombre");
		const subtotal = precio * cantidad;

		// Verificar si el producto ya está en el pedido
		const productoExistente = productosPedido.find(
			(p) => p.id_producto == productoId
		);

		if (productoExistente) {
			// Actualizar cantidad y subtotal
			productoExistente.cantidad += cantidad;
			productoExistente.subtotal = productoExistente.cantidad * precio;
		} else {
			// Agregar nuevo producto
			productosPedido.push({
				id_producto: productoId,
				nombre: nombre,
				cantidad: cantidad,
				precio_unitario: precio,
				subtotal: subtotal,
			});
		}

		actualizarTablaProductosPedido();
		calcularTotalPedido();

		// Resetear selección
		productoSelect.val("");
		$("#cantidadProducto").val(1);
	});

	// Configurar modal de cambiar estado
	$("#cambiarEstadoModal").on("show.bs.modal", function (event) {
		const button = $(event.relatedTarget);
		const pedidoId = button.data("pedido-id");
		const estadoActual = button.data("estado-actual");

		$("#pedidoIdEstado").val(pedidoId);
		$("#estado").val(estadoActual);
	});

	$(document).on("click", ".btn-editar-cliente", function () {
		// Extraer los datos directamente del botón
		const clienteId = button.getAttribute("data-cliente-id");
		const telefono = button.getAttribute("data-telefono");
		const email = button.getAttribute("data-email");
		const direccion = button.getAttribute("data-direccion");
		console.log(telefono);

		// Colocar los datos en el modal
		$("#clienteIdEditar").val(clienteId);
		$("#telefono_editar").val(telefono);
		$("#correo_electronico_editar").val(email);
		$("#direccion_editar").val(direccion);
	});
	// Configurar modal de editar producto
	$("#editarProductoModal").on("show.bs.modal", function (event) {
		const button = $(event.relatedTarget);
		const productoId = button.data("producto-id");
		const nombre = button.data("nombre");
		const precio = button.data("precio");
		const disponible = button.data("disponible");

		$("#productoIdEditar").val(productoId);
		$("#nombreProductoEditar").val(nombre);
		$("#precio_unitario_editar").val(precio);
		$("#disponible_editar").val(disponible.toString());
	});

	// Configurar envío del formulario de pedido
	$("#formPedido").on("submit", function () {
		// Validar que haya al menos un producto en el pedido
		if (productosPedido.length === 0) {
			alert("Debe agregar al menos un producto al pedido");
			return false;
		}

		const subtotal = calcularTotalPedido();
		const anticipo = parseFloat($("#anticipo").val()) || 0;

		if (anticipo > subtotal) {
			alert("El anticipo no puede ser mayor al subtotal del pedido");
			return false;
		}

		// Agregar los productos al formulario antes de enviar
		productosPedido.forEach((producto, index) => {
			$(this).append(
				$("<input>").attr({
					type: "hidden",
					name: "productos[" + index + "][id_producto]",
					value: producto.id_producto,
				}),
				$("<input>").attr({
					type: "hidden",
					name: "productos[" + index + "][cantidad]",
					value: producto.cantidad,
				}),
				$("<input>").attr({
					type: "hidden",
					name: "productos[" + index + "][precio_unitario]",
					value: producto.precio_unitario,
				})
			);
		});

		return true;
	});

	$("#anticipo").on("change", function () {
		const subtotal = calcularTotalPedido();
		const anticipo = parseFloat($(this).val()) || 0;

		if (anticipo > subtotal) {
			alert("El anticipo no puede ser mayor al subtotal del pedido");
			$(this).val(subtotal);
		}
	});

	$("#cantidadProducto").on("keypress", function (e) {
		if (e.which === 13) {
			e.preventDefault();
			$("#agregarProducto").click();
		}
	});

	$("#nuevoPedidoModal").on("hidden.bs.modal", function () {
		productosPedido = [];
		actualizarTablaProductosPedido();
		calcularTotalPedido();
		$("#formPedido")[0].reset();

		// Restablecer fecha mínima
		const hoy = new Date().toISOString().split("T")[0];
		$("#fecha_entrega").attr("min", hoy);
	});
});

// Actualizar tabla de productos del pedido
function actualizarTablaProductosPedido() {
	const tbody = $("#tablaProductosPedido tbody");
	tbody.empty();

	productosPedido.forEach((producto, index) => {
		const fila = `
            <tr>
                <td>${producto.nombre}</td>
                <td>
                    <input type="number" class="form-control form-control-sm cantidad-producto" 
                           value="${producto.cantidad}" min="1" 
                           data-index="${index}" 
                           onchange="actualizarCantidadProducto(${index}, this.value)">
                </td>
                <td>$${producto.precio_unitario.toFixed(2)}</td>
                <td>$${producto.subtotal.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarProductoPedido(${index})">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </td>
            </tr>
        `;
		tbody.append(fila);
	});
}

// Actualizar cantidad de producto en el pedido
function actualizarCantidadProducto(index, nuevaCantidad) {
	const cantidad = parseInt(nuevaCantidad);

	if (isNaN(cantidad) || cantidad < 1) {
		alert("La cantidad debe ser un número mayor a 0");
		actualizarTablaProductosPedido();
		return;
	}

	productosPedido[index].cantidad = cantidad;
	productosPedido[index].subtotal =
		cantidad * productosPedido[index].precio_unitario;

	actualizarTablaProductosPedido();
	calcularTotalPedido();
}

// Eliminar producto del pedido
function eliminarProductoPedido(index) {
	if (confirm("¿Está seguro de que desea eliminar este producto del pedido?")) {
		productosPedido.splice(index, 1);
		actualizarTablaProductosPedido();
		calcularTotalPedido();
	}
}

// Calcular total del pedido
function calcularTotalPedido() {
	const total = productosPedido.reduce(
		(sum, producto) => sum + producto.subtotal,
		0
	);
	$("#totalPedido").text(`$${total.toFixed(2)}`);
	$("#subtotalPedido").val(total);

	// Actualizar saldo automáticamente
	const anticipo = parseFloat($("#anticipo").val()) || 0;
	const saldo = total - anticipo;

	if ($("#saldoPedido").length === 0) {
		$("#totalPedido").parent().after(`
            <tr>
                <td colspan="3" class="text-end"><strong>Saldo:</strong></td>
                <td id="saldoPedido">$${saldo.toFixed(2)}</td>
                <td></td>
            </tr>
        `);
	} else {
		$("#saldoPedido").text(`$${saldo.toFixed(2)}`);
	}

	return total;
}
// Cargar formulario de editar pedido

// Ver detalles del pedido
function verDetalles(pedidoId) {
	$("#detallesPedidoContent").html(`
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando detalles del pedido...</p>
        </div>
    `);

	$("#detallesPedidoModal").modal("show");

	$.ajax({
		url: "ajax_obtener_detalles_pedido.php",
		type: "POST",
		data: { id_pedido: pedidoId },
		dataType: "json",
		success: function (response) {
			if (response.success) {
				const pedido = response.pedido;
				let html = `
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6><strong>ID Pedido:</strong> ${
															pedido.id_pedido
														}</h6>
                            <h6><strong>Cliente:</strong> ${
															pedido.nombre_cliente
														}</h6>
                            <h6><strong>Teléfono:</strong> ${
															pedido.telefono || "No especificado"
														}</h6>
                            <h6><strong>Email:</strong> ${
															pedido.correo_electronico || "No especificado"
														}</h6>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>Fecha Entrega:</strong> ${
															pedido.fecha_entrega
														}</h6>
                            <h6><strong>Hora Entrega:</strong> ${
															pedido.hora_entrega
														}</h6>
                            <h6><strong>Estado:</strong> <span class="status-badge status-${
															pedido.estado
														}">${obtenerTextoEstado(pedido.estado)}</span></h6>
                            <h6><strong>Fecha Creación:</strong> ${
															pedido.fecha_creacion || "No especificada"
														}</h6>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6><strong>Dirección de Entrega:</strong></h6>
                            <p class="border p-2 rounded">${
															pedido.direccion || "No especificada"
														}</p>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6><strong>Observaciones:</strong></h6>
                            <p class="border p-2 rounded bg-light">${
															pedido.observaciones || "Ninguna"
														}</p>
                        </div>
                    </div>
                    
                    <h5 class="mb-3 border-bottom pb-2">Productos del Pedido</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio Unitario</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

				if (pedido.detalles && pedido.detalles.length > 0) {
					pedido.detalles.forEach((detalle) => {
						html += `
                            <tr>
                                <td>${detalle.nombre_producto}</td>
                                <td class="text-center">${detalle.cantidad}</td>
                                <td class="text-end">$${parseFloat(
																	detalle.precio_unitario
																).toFixed(2)}</td>
                                <td class="text-end">$${parseFloat(
																	detalle.subtotal
																).toFixed(2)}</td>
                            </tr>
                        `;
					});
				} else {
					html += `
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay productos en este pedido</td>
                        </tr>
                    `;
				}

				html += `
                            </tbody>
                            <tfoot class="table-group-divider">
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end"><strong>$${parseFloat(
																			pedido.subtotal
																		).toFixed(2)}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Anticipo:</strong></td>
                                    <td class="text-end"><strong>$${parseFloat(
																			pedido.anticipo
																		).toFixed(2)}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Saldo Pendiente:</strong></td>
                                    <td class="text-end"><strong class="${
																			pedido.saldo > 0
																				? "text-warning"
																				: "text-success"
																		}">$${parseFloat(pedido.saldo).toFixed(
					2
				)}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `;

				$("#detallesPedidoContent").html(html);
			} else {
				$("#detallesPedidoContent").html(`
                    <div class="alert alert-danger">
                        <h6>Error al cargar los detalles del pedido</h6>
                        <p class="mb-0">${
													response.message || "Por favor, intente nuevamente."
												}</p>
                    </div>
                `);
			}
		},
		error: function (xhr, status, error) {
			$("#detallesPedidoContent").html(`
                <div class="alert alert-danger">
                    <h6>Error de conexión</h6>
                    <p class="mb-0">No se pudieron cargar los detalles del pedido.</p>
                </div>
            `);
		},
	});
}

// Función auxiliar para obtener texto del estado
function obtenerTextoEstado(estado) {
	const estados = {
		pendiente: "Pendiente",
		confirmado: "Confirmado",
		en_proceso: "En Proceso",
		listo: "Listo",
		entregado: "Entregado",
		cancelado: "Cancelado",
	};
	return estados[estado] || estado;
}

// Inicializar tooltips de Bootstrap
function inicializarTooltips() {
	const tooltipTriggerList = [].slice.call(
		document.querySelectorAll('[data-bs-toggle="tooltip"]')
	);
	tooltipTriggerList.map(function (tooltipTriggerEl) {
		return new bootstrap.Tooltip(tooltipTriggerEl);
	});
}

$(document).ready(function () {
	inicializarTooltips();
});
