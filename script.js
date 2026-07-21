// OBJETOS PRODUCTO

function Producto(nombre, categoria, precio, stock) {

    this.nombre = nombre;
    this.categoria = categoria;
    this.precio = precio;
    this.stock = stock;

    this.mostrarInformacion = function () {

        return `
            <h3>${this.nombre}</h3>
            <p>Categoría: ${this.categoria}</p>
            <p>Precio: $${this.precio}</p>
            <p>Stock disponible: ${this.stock}</p>
        `;
    };

}


// PRODUCTOS

const productos = [

    new Producto(
        "Notebook Gamer",
        "Gaming",
        899990,
        10
    ),

    new Producto(
        "Mouse RGB",
        "Accesorios",
        24990,
        25
    ),

    new Producto(
        "Monitor 24 pulgadas",
        "Tecnología",
        179990,
        15
    ),

    new Producto(
        "Teclado Mecánico",
        "Gaming",
        59990,
        18
    )

];


// ELEMENTOS DOM

const inputBusqueda =
    document.getElementById(
        "product-search"
    );

const filtroCategoria =
    document.getElementById(
        "category-filter"
    );

const contenedorResultados =
    document.getElementById(
        "results-container"
    );

const mensaje =
    document.getElementById(
        "message-container"
    );

const botonBuscar =
    document.getElementById(
        "search-btn"
    );

const notificacion =
    document.getElementById(
        "notification-container"
    );

const estadoCarrito =
    document.getElementById(
        "cart-status"
    );


// VARIABLES GLOBALES

let cantidadCarrito = 0;


// EVENTO BUSCAR

botonBuscar.addEventListener(
    "click",
    buscarProductos
);


// BUSCAR PRODUCTOS

function buscarProductos() {

    const textoBusqueda =
        inputBusqueda.value
            .trim()
            .toLowerCase();

    const categoriaSeleccionada =
        filtroCategoria.value;

    mensaje.textContent = "";

    contenedorResultados.innerHTML = "";

    if (
        textoBusqueda === "" &&
        categoriaSeleccionada === "todos"
    ) {

        mensaje.textContent =
            "Debe ingresar un nombre o seleccionar una categoría.";

        return;
    }

    const resultados =
        productos.filter(
            producto => {

                const coincideNombre =
                    producto.nombre
                        .toLowerCase()
                        .includes(textoBusqueda);

                const coincideCategoria =
                    categoriaSeleccionada === "todos" ||
                    producto.categoria === categoriaSeleccionada;

                return coincideNombre &&
                    coincideCategoria;

            }
        );

    if (resultados.length === 0) {

        mensaje.textContent =
            "No se encontraron productos.";

        return;
    }

    resultados.forEach(
        producto => {

            const tarjeta =
                document.createElement(
                    "div"
                );

            tarjeta.classList.add(
                "product-card"
            );

            tarjeta.innerHTML =
                producto.mostrarInformacion();

            const botonAgregar =
                document.createElement(
                    "button"
                );

            botonAgregar.textContent =
                "Agregar al carrito";

            botonAgregar.classList.add(
                "add-cart-btn"
            );

            botonAgregar.addEventListener(
                "click",
                function () {

                    cantidadCarrito++;

                    estadoCarrito.textContent =
                        `Productos en carrito: ${cantidadCarrito}`;

                    notificacion.textContent =
                        `✅ ${producto.nombre} agregado al carrito correctamente.`;

                }
            );

            tarjeta.appendChild(
                botonAgregar
            );

            contenedorResultados.appendChild(
                tarjeta
            );

        }
    );

}



// PROMOCIONES

function mostrarPromocion() {

    const promociones = [

        "Promoción: 15% de descuento en productos Gaming.",

        "Compra un accesorio y obtén envío gratuito.",

        "Descuento especial en notebooks durante esta semana."

    ];

    const indice =
        Math.floor(
            Math.random() *
            promociones.length
        );

    notificacion.textContent =
        promociones[indice];

}


// EVENTO LOAD

window.addEventListener(
    "load",
    mostrarPromocion
);



/*=====================================
VALIDACIONES PRODUCTO
=====================================*/

document.getElementById("formProducto").addEventListener("submit", function (e) {

    const precio = parseFloat(document.getElementById("precioProducto").value);
    const stock = parseInt(document.getElementById("stockProducto").value);

    if (precio <= 0 || stock < 0) {

        alert("Ingrese un precio y stock válidos.");
        e.preventDefault();

    }

});

/*=====================================
VALIDACIONES CLIENTE
=====================================*/

document.getElementById("formCliente").addEventListener("submit", function (e) {

    const email = document.getElementById("emailCliente").value;

    const expresion = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!expresion.test(email)) {

        alert("Ingrese un correo electrónico válido.");
        e.preventDefault();

    }

});