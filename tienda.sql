CREATE DATABASE TIENDA;

USE TIENDA;

CREATE TABLE PRODUCTO (

    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL

);

CREATE TABLE CLIENTE (

    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NOT NULL

);

CREATE TABLE COMPRA (

    id_compra INT AUTO_INCREMENT PRIMARY KEY,
    cantidad INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    fecha DATE NOT NULL,
    id_producto INT NOT NULL,
    id_cliente INT NOT NULL,

    CONSTRAINT fk_producto
        FOREIGN KEY (id_producto)
        REFERENCES PRODUCTO(id_producto),

    CONSTRAINT fk_cliente
        FOREIGN KEY (id_cliente)
        REFERENCES CLIENTE(id_cliente)

);