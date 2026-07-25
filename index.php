<?php

session_start();
require_once "conexion.php";

/*==================================================
REGISTRO DE PRODUCTOS
==================================================*/

$mensajeProducto = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["registrarProducto"])) {

    $nombre = filter_input(INPUT_POST, "nombreProducto", FILTER_SANITIZE_SPECIAL_CHARS);
    $descripcion = filter_input(INPUT_POST, "descripcionProducto", FILTER_SANITIZE_SPECIAL_CHARS);
    $precio = filter_input(INPUT_POST, "precioProducto", FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, "stockProducto", FILTER_VALIDATE_INT);

    if (!$nombre || !$descripcion || $precio === false || $stock === false || $precio <= 0 || $stock < 0) {

        $mensajeProducto = "Debe ingresar información válida para el producto.";

    } else {

        $sql = "INSERT INTO PRODUCTO(nombre, descripcion, precio, stock)
                VALUES (?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssdi", $nombre, $descripcion, $precio, $stock);

        if ($stmt->execute()) {

            $mensajeProducto = "Producto registrado correctamente.";

        } else {

            $mensajeProducto = "Error al registrar el producto.";

        }

        $stmt->close();

    }

}

/*==================================================
REGISTRO DE CLIENTES
==================================================*/

$mensajeCliente = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["registrarCliente"])) {

    $nombre = filter_input(INPUT_POST, "nombreCliente", FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, "emailCliente", FILTER_VALIDATE_EMAIL);
    $direccion = filter_input(INPUT_POST, "direccionCliente", FILTER_SANITIZE_SPECIAL_CHARS);

    if (!$nombre || !$email || !$direccion) {

        $mensajeCliente = "Debe ingresar información válida del cliente.";

    } else {

        $sql = "INSERT INTO CLIENTE(nombre, email, direccion)
                VALUES (?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sss", $nombre, $email, $direccion);

        if ($stmt->execute()) {

            $mensajeCliente = "Cliente registrado correctamente.";

        } else {

            $mensajeCliente = "Error al registrar el cliente.";

        }

        $stmt->close();

    }

}


/* Tiempo máximo de inactividad (15 minutos) */

$tiempoMaximo = 900;

/* Si existe una sesión previa */

if (isset($_SESSION["ultimaActividad"])) {

    $tiempoInactivo = time() - $_SESSION["ultimaActividad"];

    if ($tiempoInactivo > $tiempoMaximo) {

        session_unset();

        session_destroy();

        session_start();
    }
}

/* Actualizar la última actividad */

$_SESSION["ultimaActividad"] = time();

/* Regenerar el ID de sesión una única vez */

if (!isset($_SESSION["idRegenerado"])) {

    session_regenerate_id(true);

    $_SESSION["idRegenerado"] = true;
}

$mensajeResena = "";

function registrarResena($nombre, $calificacion, $comentario)
{
    return "Gracias $nombre. Tu reseña de $calificacion estrellas ha sido registrada correctamente.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["enviar_resena"])) {

    $nombre = trim($_POST["nombre"]);
    $calificacion = trim($_POST["calificacion"]);
    $comentario = trim($_POST["comentario"]);

    if (
        empty($nombre) ||
        empty($calificacion) ||
        empty($comentario)
    ) {

        $mensajeResena =
            "Todos los campos son obligatorios.";

    } else {

        $mensajeResena =
            registrarResena(
                htmlspecialchars($nombre),
                htmlspecialchars($calificacion),
                htmlspecialchars($comentario)
            );

    }
}

class Pedido
{
    private $descripcionPedido;
    private $tipoPedido;
    private $producto;
    private $unidades;
    private $observaciones;

    public function __construct(
        $descripcionPedido,
        $tipoPedido,
        $producto,
        $unidades,
        $observaciones
    ) {
        $this->descripcionPedido = $descripcionPedido;
        $this->tipoPedido = $tipoPedido;
        $this->producto = $producto;
        $this->unidades = $unidades;
        $this->observaciones = $observaciones;
    }

    public function mostrarPedido()
    {
        return "
        <strong>Descripción:</strong> {$this->descripcionPedido}<br>
        <strong>Tipo:</strong> {$this->tipoPedido}<br>
        <strong>Producto:</strong> {$this->producto}<br>
        <strong>Unidades:</strong> {$this->unidades}<br>
        <strong>Observaciones:</strong> {$this->observaciones}
        ";
    }

    public function buscarPedido($criterio)
    {
        return stripos(
            $this->producto,
            $criterio
        ) !== false;
    }
}

/* CREACIÓN DEL OBJETO */

$pedidoEjemplo = new Pedido(
    "Compra para oficina",
    "Normal",
    "Monitor 24 pulgadas",
    2,
    "Entrega en horario laboral"
);

$mensajePedido = "";

if (
    empty($descripcionPedido) ||
    empty($tipoPedido) ||
    empty($producto) ||
    empty($unidades)
) {

    $mensajePedido = "Debe completar todos los campos obligatorios.";

} elseif (!is_numeric($unidades) || $unidades < 1) {

    $mensajePedido = "La cantidad de unidades debe ser mayor que cero.";

} elseif ($unidades > 100) {

    $mensajePedido = "No es posible solicitar más de 100 unidades en un solo pedido.";

} else {

    $pedidoCliente = new Pedido(
        htmlspecialchars($descripcionPedido),
        htmlspecialchars($tipoPedido),
        htmlspecialchars($producto),
        (int)$unidades,
        htmlspecialchars($observaciones)
    );

    $mensajePedido = "
        <h3>Pedido registrado correctamente</h3>
        " . $pedidoCliente->mostrarPedido();


    if (
        empty($descripcionPedido) ||
        empty($tipoPedido) ||
        empty($producto) ||
        empty($unidades)
    ) {

        $mensajePedido = "Debe completar todos los campos obligatorios.";

    } else {

        $pedidoCliente = new Pedido(
            htmlspecialchars($descripcionPedido),
            htmlspecialchars($tipoPedido),
            htmlspecialchars($producto),
            htmlspecialchars($unidades),
            htmlspecialchars($observaciones)
        );

        $mensajePedido = "
            <h3>Pedido registrado correctamente</h3>
            " . $pedidoCliente->mostrarPedido();
    }
}

/* ===========================
   CARRITO MEDIANTE SESIONES
=========================== */

if (!isset($_SESSION["carrito"])) {
    $_SESSION["carrito"] = [];
}

/* Agregar producto */

if (isset($_POST["agregarCarrito"])) {

    $producto = htmlspecialchars(trim($_POST["producto"]));
    $cantidad = (int)$_POST["cantidad"];

    if (!empty($producto) && $cantidad > 0) {

        if (isset($_SESSION["carrito"][$producto])) {

            $_SESSION["carrito"][$producto] += $cantidad;

        } else {

            $_SESSION["carrito"][$producto] = $cantidad;

        }

    }

}

/* Vaciar carrito */

if (isset($_POST["vaciarCarrito"])) {

    $_SESSION["carrito"] = [];

}

/*=====================================
REGISTRO DE COMPRAS
=====================================*/

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["registrarCompra"])) {

    $idProducto = filter_input(INPUT_POST, "idProducto", FILTER_VALIDATE_INT);
    $idCliente = filter_input(INPUT_POST, "idCliente", FILTER_VALIDATE_INT);
    $cantidad = filter_input(INPUT_POST, "cantidad", FILTER_VALIDATE_INT);

    if ($idProducto && $idCliente && $cantidad > 0) {

        $consulta = $conexion->prepare("SELECT precio, stock FROM PRODUCTO WHERE id_producto=?");
        $consulta->bind_param("i",$idProducto);
        $consulta->execute();

        $resultado = $consulta->get_result()->fetch_assoc();

        if($resultado["stock"] >= $cantidad){

            $total = $resultado["precio"] * $cantidad;

            $insertar = $conexion->prepare("
                INSERT INTO COMPRA
                (cantidad,total,fecha,id_producto,id_cliente)
                VALUES
                (?, ?, CURDATE(), ?, ?)
            ");

            $insertar->bind_param(
                "idii",
                $cantidad,
                $total,
                $idProducto,
                $idCliente
            );

            $insertar->execute();

            $actualizar = $conexion->prepare("
                UPDATE PRODUCTO
                SET stock = stock - ?
                WHERE id_producto = ?
            ");

            $actualizar->bind_param(
                "ii",
                $cantidad,
                $idProducto
            );

            $actualizar->execute();

            echo "<p class='correcto'>Compra registrada correctamente.</p>";

        }else{

            echo "<p class='error'>Stock insuficiente.</p>";

        }

    }

}



?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda de Comercio Electrónico</title>

    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <h1>Tienda de Comercio Electrónico</h1>
    <h2>Gestión de Pedidos - Nueva Funcionalidad</h2>

    <section class="session-info">

    <h2>Estado de la Sesión</h2>

    <p>

        La sesión del usuario se encuentra activa.

    </p>

    <p>

        ID de sesión:

        <strong>

            <?php echo session_id(); ?>

        </strong>

    </p>

</section>

    <div class="search-container">

        <input
            type="text"
            id="product-search"
            placeholder="Buscar producto">

        <select id="category-filter">
            <option value="todos">Todas las categorías</option>
            <option value="Tecnología">Tecnología</option>
            <option value="Gaming">Gaming</option>
            <option value="Accesorios">Accesorios</option>
        </select>

        <button id="search-btn">
            Buscar
        </button>

    </div>

    <div id="message-container"></div>

    <div id="notification-container"></div>

    <div id="cart-container">
        <h2>Carrito de Compras</h2>
        <p id="cart-status">
            Productos en carrito: 0
        </p>
    </div>

    <div id="results-container"></div>

    <script src="script.js"></script>

    <section class="review-section">

<section class="session-cart">

<h2>Carrito de Compras (Sesión PHP)</h2>

<form method="POST">

    <input
        type="text"
        name="producto"
        placeholder="Nombre del producto"
        required>

    <input
        type="number"
        name="cantidad"
        min="1"
        value="1"
        required>

    <button
        type="submit"
        name="agregarCarrito">

        Agregar Producto

    </button>

</form>

<h3>Productos almacenados en la sesión</h3>

<?php

if (count($_SESSION["carrito"]) > 0) {

    echo "<ul>";

    foreach ($_SESSION["carrito"] as $producto => $cantidad) {

        echo "<li><strong>$producto</strong> - Cantidad: $cantidad</li>";

    }

    echo "</ul>";

} else {

    echo "<p>El carrito está vacío.</p>";

}

?>

<form method="POST">

    <button
        type="submit"
        name="vaciarCarrito">

        Vaciar Carrito

    </button>

</form>

</section>


    <h2>Calificar Producto</h2>

    <form method="POST">

        <input
            type="text"
            name="nombre"
            placeholder="Ingrese su nombre">

        <select name="calificacion">

            <option value="">
                Seleccione una calificación
            </option>

            <option value="1">1 Estrella</option>
            <option value="2">2 Estrellas</option>
            <option value="3">3 Estrellas</option>
            <option value="4">4 Estrellas</option>
            <option value="5">5 Estrellas</option>

        </select>

        <textarea
            name="comentario"
            placeholder="Ingrese su reseña"></textarea>

        <button
            type="submit"
            name="enviar_resena">

            Enviar reseña

        </button>

    </form>

    <div class="review-message">
        <?php echo $mensajeResena; ?>
    </div>

    </section>

        <section class="pedido-demo">

        <h2>Ejemplo de Pedido</h2>

        <div class="pedido-info">
            <?php echo $pedidoEjemplo->mostrarPedido(); 
            ?>
        </div>

    </section>

    <section class="pedido-formulario">

    <h2>Registro de Pedido</h2>

    <form action="index.php" method="POST">

        <input
            type="text"
            name="descripcionPedido"
            placeholder="Descripción del pedido"
            required>

        <select
            name="tipoPedido"
            required>

            <option value="">
                Seleccione tipo de pedido
            </option>

            <option value="Normal">
                Normal
            </option>

            <option value="Express">
                Express
            </option>

        </select>

        <input
            type="text"
            name="producto"
            placeholder="Nombre del producto"
            required>

        <input
            type="number"
            name="unidades"
            min="1"
            placeholder="Cantidad de unidades"
            required>

        <textarea
            name="observaciones"
            placeholder="Observaciones adicionales"></textarea>

        <button
            type="submit"
            name="registrarPedido">

            Registrar Pedido

        </button>

        <div class="pedido-resultado">

         <?php
            echo $mensajePedido;
          ?>

</div>

    </form>

    </section>

    <section class="producto-formulario">

    <h2>Registro de Productos</h2>

    <form method="POST" id="formProducto">

        <input
            type="text"
            name="nombreProducto"
            id="nombreProducto"
            placeholder="Nombre del producto"
            required>

        <input
            type="text"
            name="descripcionProducto"
            id="descripcionProducto"
            placeholder="Descripción"
            required>

        <input
            type="number"
            name="precioProducto"
            id="precioProducto"
            step="0.01"
            min="1"
            placeholder="Precio"
            required>

        <input
            type="number"
            name="stockProducto"
            id="stockProducto"
            min="0"
            placeholder="Stock"
            required>

        <button
            type="submit"
            name="registrarProducto">

            Registrar Producto

        </button>

    </form>

    <div class="review-message">

        <?php echo $mensajeProducto; ?>

    </div>

    </section>

    <section class="cliente-formulario">

    <h2>Registro de Clientes</h2>

    <form method="POST" id="formCliente">

        <input
            type="text"
            name="nombreCliente"
            id="nombreCliente"
            placeholder="Nombre"
            required>

        <input
            type="email"
            name="emailCliente"
            id="emailCliente"
            placeholder="Correo electrónico"
            required>

        <input
            type="text"
            name="direccionCliente"
            id="direccionCliente"
            placeholder="Dirección"
            required>

        <button
            type="submit"
            name="registrarCliente">

            Registrar Cliente

        </button>

    </form>

    <div class="review-message">

        <?php echo $mensajeCliente; ?>

    </div>

    </section>

<h3>Productos Registrados</h3>

<table>

<tr>

    <th>ID</th>
    <th>Nombre</th>
    <th>Precio</th>
    <th>Stock</th>

</tr>

<section class="compra-formulario">

    <h2>Registro de Compras</h2>

    <form method="POST">

        <select name="idProducto" required>

            <option value="">Seleccione un producto</option>

            <?php

            $productos = $conexion->query("SELECT * FROM PRODUCTO WHERE stock > 0");

            while($producto = $productos->fetch_assoc()){

                echo "<option value='".$producto["id_producto"]."'>".$producto["nombre"]." (Stock: ".$producto["stock"].")</option>";

            }

            ?>

        </select>

        <select name="idCliente" required>

            <option value="">Seleccione un cliente</option>

            <?php

            $clientes = $conexion->query("SELECT * FROM CLIENTE");

            while($cliente = $clientes->fetch_assoc()){

                echo "<option value='".$cliente["id_cliente"]."'>".$cliente["nombre"]."</option>";

            }

            ?>

        </select>

        <input
            type="number"
            name="cantidad"
            min="1"
            placeholder="Cantidad"
            required>

        <button
            type="submit"
            name="registrarCompra">

            Registrar Compra

        </button>

    </form>

</section>

<?php

$resultado = $conexion->query("SELECT * FROM PRODUCTO");

while($fila = $resultado->fetch_assoc()) {

    echo "<tr>";

    echo "<td>".$fila["id_producto"]."</td>";
    echo "<td>".$fila["nombre"]."</td>";
    echo "<td>$".$fila["precio"]."</td>";
    echo "<td>".$fila["stock"]."</td>";

    echo "</tr>";

}

?>

</table>

<h3>Clientes Registrados</h3>

<table>

<tr>

    <th>ID</th>
    <th>Nombre</th>
    <th>Email</th>

</tr>

<?php

$resultado = $conexion->query("SELECT * FROM CLIENTE");

while($fila = $resultado->fetch_assoc()) {

    echo "<tr>";

    echo "<td>".$fila["id_cliente"]."</td>";
    echo "<td>".$fila["nombre"]."</td>";
    echo "<td>".$fila["email"]."</td>";

    echo "</tr>";

}

?>

</table>

<h2>Compras Registradas</h2>

<table>

<tr>

<th>ID</th>
<th>Cliente</th>
<th>Producto</th>
<th>Cantidad</th>
<th>Total</th>
<th>Fecha</th>

</tr>

<?php

$sql = "

SELECT

COMPRA.id_compra,
CLIENTE.nombre AS cliente,
PRODUCTO.nombre AS producto,
COMPRA.cantidad,
COMPRA.total,
COMPRA.fecha

FROM COMPRA

INNER JOIN CLIENTE
ON COMPRA.id_cliente = CLIENTE.id_cliente

INNER JOIN PRODUCTO
ON COMPRA.id_producto = PRODUCTO.id_producto

";

$resultado = $conexion->query($sql);

while($fila = $resultado->fetch_assoc()){

echo "<tr>";

echo "<td>".$fila["id_compra"]."</td>";
echo "<td>".$fila["cliente"]."</td>";
echo "<td>".$fila["producto"]."</td>";
echo "<td>".$fila["cantidad"]."</td>";
echo "<td>$".$fila["total"]."</td>";
echo "<td>".$fila["fecha"]."</td>";

echo "</tr>";

}

?>

</table>

<h2>Clientes con más de dos compras</h2>

<table>

<tr>

<th>Cliente</th>
<th>Cantidad de compras</th>

</tr>

<?php

$sql = "

SELECT

CLIENTE.nombre,
COUNT(COMPRA.id_compra) AS compras

FROM CLIENTE

INNER JOIN COMPRA

ON CLIENTE.id_cliente = COMPRA.id_cliente

GROUP BY

CLIENTE.id_cliente,
CLIENTE.nombre

HAVING COUNT(COMPRA.id_compra) > 2

ORDER BY compras DESC

";

$resultado = $conexion->query($sql);

while($fila = $resultado->fetch_assoc()){

echo "<tr>";

echo "<td>".$fila["nombre"]."</td>";

echo "<td>".$fila["compras"]."</td>";

echo "</tr>";

}

?>

</table>

</body>

</html>