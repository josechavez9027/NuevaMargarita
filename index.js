const express = require("express");
const mysql = require("mysql2/promise");
const path = require("path");

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(express.static(path.join(__dirname, "public")));
app.set("view engine", "ejs");
app.set("views", path.join(__dirname, "views"));

// ─── Conexión a la base de datos ─────────────────────────────────────────────
const pool = mysql.createPool({
	host: process.env.MYSQLHOST,
	user: process.env.DB_USER,
	password: process.env.DB_PASS,
	database: process.env.DB_NAME,
	port: process.env.MYSQLPORT || 3306,
	charset: "utf8mb4",
	waitForConnections: true,
	connectionLimit: 10,
});

// ─── Funciones equivalentes a functions.php ───────────────────────────────────
async function obtenerClientes(conn) {
	const [rows] = await conn.query(
		"SELECT * FROM cliente ORDER BY nombre, apellido_paterno",
	);
	return rows;
}

async function obtenerProductos(conn) {
	const [rows] = await conn.query(`
    SELECT p.*, c.nombre_categoria
    FROM producto p
    JOIN categoria c ON p.id_categoria = c.id_categoria
    ORDER BY c.nombre_categoria, p.nombre
  `);
	return rows;
}

async function obtenerCategorias(conn) {
	const [rows] = await conn.query(
		"SELECT * FROM categoria ORDER BY nombre_categoria",
	);
	return rows;
}

async function obtenerPedidos(conn) {
	const [rows] = await conn.query(`
    SELECT p.*,
           CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', COALESCE(c.apellido_materno, '')) as nombre_cliente
    FROM pedido p
    JOIN cliente c ON p.id_cliente = c.id_cliente
    ORDER BY p.fecha_entrega DESC, p.id_pedido DESC
  `);
	return rows;
}

async function obtenerDetallesPedido(conn, id_pedido) {
	const [rows] = await conn.query(
		`
    SELECT dp.*, p.nombre as nombre_producto
    FROM detalle_pedido dp
    JOIN producto p ON dp.id_producto = p.id_producto
    WHERE dp.id_pedido = ?
  `,
		[id_pedido],
	);
	return rows;
}

async function obtenerPedidoCompleto(conn, id_pedido) {
	const [rows] = await conn.query(
		`
    SELECT p.*,
           CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', COALESCE(c.apellido_materno, '')) as nombre_cliente,
           c.telefono, c.correo_electronico, c.direccion
    FROM pedido p
    JOIN cliente c ON p.id_cliente = c.id_cliente
    WHERE p.id_pedido = ?
  `,
		[id_pedido],
	);

	if (rows.length === 0) return null;
	const pedido = rows[0];
	pedido.detalles = await obtenerDetallesPedido(conn, id_pedido);
	return pedido;
}

// ─── GET / — Página principal ─────────────────────────────────────────────────
app.get("/", async (req, res) => {
	const conn = await pool.getConnection();
	try {
		const [clientes, productos, pedidos, categorias] = await Promise.all([
			obtenerClientes(conn),
			obtenerProductos(conn),
			obtenerPedidos(conn),
			obtenerCategorias(conn),
		]);
		res.render("index", {
			clientes,
			productos,
			pedidos,
			categorias,
			mensaje: null,
		});
	} catch (err) {
		console.error(err);
		res.status(500).send("Error al cargar la aplicación: " + err.message);
	} finally {
		conn.release();
	}
});

// ─── POST /accion — Todas las acciones del formulario ────────────────────────
app.post("/accion", async (req, res) => {
	const conn = await pool.getConnection();
	try {
		const { action } = req.body;

		switch (action) {
			case "guardar_cliente": {
				await conn.query(
					"INSERT INTO cliente (nombre, apellido_paterno, apellido_materno, telefono, correo_electronico, direccion) VALUES (?,?,?,?,?,?)",
					[
						req.body.nombre,
						req.body.apellido_paterno,
						req.body.apellido_materno || "",
						req.body.telefono || "",
						req.body.correo_electronico || "",
						req.body.direccion || "",
					],
				);
				break;
			}

			case "actualizar_cliente": {
				await conn.query(
					"UPDATE cliente SET telefono=?, correo_electronico=?, direccion=? WHERE id_cliente=?",
					[
						req.body.telefono || "",
						req.body.correo_electronico || "",
						req.body.direccion || "",
						req.body.id_cliente,
					],
				);
				break;
			}

			case "eliminar_cliente": {
				const [[{ total }]] = await conn.query(
					"SELECT COUNT(*) as total FROM pedido WHERE id_cliente=?",
					[req.body.id_cliente],
				);
				if (total > 0) break; // No eliminar si tiene pedidos
				await conn.query("DELETE FROM cliente WHERE id_cliente=?", [
					req.body.id_cliente,
				]);
				break;
			}

			case "guardar_producto": {
				await conn.query(
					"INSERT INTO producto (nombre, descripcion, id_categoria, precio_unitario, disponible) VALUES (?,?,?,?,?)",
					[
						req.body.nombre,
						req.body.descripcion || "",
						req.body.id_categoria,
						req.body.precio_unitario,
						req.body.disponible ?? 1,
					],
				);
				break;
			}

			case "actualizar_producto": {
				await conn.query(
					"UPDATE producto SET precio_unitario=?, disponible=? WHERE id_producto=?",
					[
						req.body.precio_unitario,
						req.body.disponible ?? 0,
						req.body.id_producto,
					],
				);
				break;
			}

			case "eliminar_producto": {
				const [[{ total }]] = await conn.query(
					"SELECT COUNT(*) as total FROM detalle_pedido WHERE id_producto=?",
					[req.body.id_producto],
				);
				if (total > 0) break;
				await conn.query("DELETE FROM producto WHERE id_producto=?", [
					req.body.id_producto,
				]);
				break;
			}

			case "guardar_pedido": {
				const productos = req.body.productos || [];
				const subtotal = parseFloat(req.body.subtotal) || 0;
				const anticipo = parseFloat(req.body.anticipo) || 0;
				const saldo = subtotal - anticipo;

				await conn.beginTransaction();
				const [pedidoResult] = await conn.query(
					"INSERT INTO pedido (id_cliente, fecha_entrega, hora_entrega, subtotal, anticipo, saldo, observaciones) VALUES (?,?,?,?,?,?,?)",
					[
						req.body.id_cliente,
						req.body.fecha_entrega,
						req.body.hora_entrega,
						subtotal,
						anticipo,
						saldo,
						req.body.observaciones || "",
					],
				);
				const id_pedido = pedidoResult.insertId;

				for (const p of productos) {
					const sub = parseFloat(p.cantidad) * parseFloat(p.precio_unitario);
					await conn.query(
						"INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES (?,?,?,?,?)",
						[id_pedido, p.id_producto, p.cantidad, p.precio_unitario, sub],
					);
				}

				const [[cliente]] = await conn.query(
					"SELECT direccion FROM cliente WHERE id_cliente=?",
					[req.body.id_cliente],
				);
				await conn.query(
					"INSERT INTO entrega (id_pedido, direccion_entrega) VALUES (?,?)",
					[id_pedido, cliente.direccion || ""],
				);
				await conn.commit();
				break;
			}

			case "editar_pedido": {
				const id_pedido = req.body.id_pedido;
				const [[{ subtotal }]] = await conn.query(
					"SELECT subtotal FROM pedido WHERE id_pedido=?",
					[id_pedido],
				);
				const anticipo = parseFloat(req.body.anticipo) || 0;
				const saldo = subtotal - anticipo;
				await conn.query(
					"UPDATE pedido SET fecha_entrega=?, hora_entrega=?, anticipo=?, saldo=?, observaciones=? WHERE id_pedido=?",
					[
						req.body.fecha_entrega,
						req.body.hora_entrega,
						anticipo,
						saldo,
						req.body.observaciones || "",
						id_pedido,
					],
				);
				break;
			}

			case "cambiar_estado": {
				await conn.query("UPDATE pedido SET estado=? WHERE id_pedido=?", [
					req.body.estado,
					req.body.id_pedido,
				]);
				break;
			}
		}

		res.redirect("/");
	} catch (err) {
		console.error(err);
		res.redirect("/");
	} finally {
		conn.release();
	}
});

// ─── POST /ajax/detalles-pedido ───────────────────────────────────────────────
app.post("/ajax/detalles-pedido", async (req, res) => {
	const conn = await pool.getConnection();
	try {
		const pedido = await obtenerPedidoCompleto(
			conn,
			parseInt(req.body.id_pedido),
		);
		if (pedido) {
			res.json({ success: true, pedido });
		} else {
			res.json({ success: false, message: "No se pudo encontrar el pedido" });
		}
	} catch (err) {
		res.json({ success: false, message: err.message });
	} finally {
		conn.release();
	}
});

// ─── POST /ajax/detalles-producto ────────────────────────────────────────────
app.post("/ajax/detalles-producto", async (req, res) => {
	const conn = await pool.getConnection();
	try {
		const id_producto = parseInt(req.body.id_producto);

		const [[producto]] = await conn.query(
			`SELECT p.*, c.nombre_categoria
       FROM producto p
       LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
       WHERE p.id_producto = ?`,
			[id_producto],
		);

		if (!producto) {
			return res.json({ success: false, message: "Producto no encontrado" });
		}

		const [historial] = await conn.query(
			`SELECT dp.cantidad, dp.precio_unitario, dp.subtotal,
              pe.id_pedido, pe.fecha_entrega, pe.estado,
              CONCAT(cl.nombre, ' ', cl.apellido_paterno) AS nombre_cliente
       FROM detalle_pedido dp
       JOIN pedido pe  ON dp.id_pedido  = pe.id_pedido
       JOIN cliente cl ON pe.id_cliente = cl.id_cliente
       WHERE dp.id_producto = ?
       ORDER BY pe.fecha_entrega DESC
       LIMIT 10`,
			[id_producto],
		);

		const [[stats]] = await conn.query(
			`SELECT
        COALESCE(SUM(dp.cantidad), 0) AS total_vendido,
        COALESCE(SUM(dp.subtotal),  0) AS ingresos_totales,
        COUNT(DISTINCT dp.id_pedido)   AS total_pedidos
       FROM detalle_pedido dp
       WHERE dp.id_producto = ?`,
			[id_producto],
		);

		res.json({ success: true, producto, historial, stats });
	} catch (err) {
		res.json({ success: false, message: err.message });
	} finally {
		conn.release();
	}
});

// ─── POST /ajax/detalles-cliente ─────────────────────────────────────────────
app.post("/ajax/detalles-cliente", async (req, res) => {
	const conn = await pool.getConnection();
	try {
		const id_cliente = parseInt(req.body.id_cliente);

		const [[cliente]] = await conn.query(
			"SELECT * FROM cliente WHERE id_cliente = ?",
			[id_cliente],
		);

		if (!cliente) {
			return res.json({ success: false, message: "Cliente no encontrado" });
		}

		const [pedidos] = await conn.query(
			`SELECT id_pedido, fecha_entrega, hora_entrega,
              estado, subtotal, anticipo, saldo, observaciones
       FROM pedido
       WHERE id_cliente = ?
       ORDER BY fecha_entrega DESC
       LIMIT 10`,
			[id_cliente],
		);

		const [[stats]] = await conn.query(
			`SELECT
        COUNT(*)                   AS total_pedidos,
        COALESCE(SUM(subtotal), 0) AS total_gastado,
        COALESCE(SUM(saldo),    0) AS saldo_pendiente
       FROM pedido
       WHERE id_cliente = ?`,
			[id_cliente],
		);

		res.json({ success: true, cliente, pedidos, stats });
	} catch (err) {
		res.json({ success: false, message: err.message });
	} finally {
		conn.release();
	}
});

// ─── Iniciar servidor ─────────────────────────────────────────────────────────
app.listen(PORT, () => {
	console.log(`Servidor corriendo en puerto ${PORT}`);
});
