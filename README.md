Version 2.0
🛒 Backend API - Proyecto Tienda Online (Laravel)
Este README documenta las actualizaciones y nuevas funcionalidades implementadas en el backend de una tienda online, construido con Laravel como API RESTful. El enfoque principal ha sido robustecer la lógica de negocio, reforzar la seguridad y ampliar la gestión de pedidos, carritos y usuarios.

📌 1. Modificaciones Clave
Las siguientes secciones describen cambios significativos en controladores, modelos y migraciones para mejorar la funcionalidad y mantener la integridad de los datos.

🧾 1.1 Gestión de Pedidos (PedidoController.php, routes/api.php)
✅ Eliminación Segura de Pedidos
Ruta actualizada:

php
Copy
Edit
Route::apiResource('pedidos', PedidoController::class)->only(['index', 'show', 'store', 'destroy']);
Controlador (destroy):

🔐 Autorización estricta: Solo el propietario del pedido o un administrador pueden eliminar.

🚫 Restricción por estado: Usuarios normales solo pueden eliminar pedidos completados o cancelados. Los administradores pueden eliminarlos en cualquier estado.

⚠️ Mensajes de error:

403 Forbidden si no tiene permisos.

400 Bad Request si el estado del pedido no lo permite.

🔄 Transacción atómica: La eliminación del pedido y sus productos asociados se realiza con DB::beginTransaction() para garantizar consistencia.

📦 Visualización Avanzada de Pedidos para Administración
El método indexAdmin carga relaciones completas:

php
Copy
Edit
Pedidos::with(['productos.producto', 'usuario', 'direccion'])
Esto asegura que el panel de administración tenga acceso a todos los datos relevantes del pedido.

🧬 1.2 Modelos, Migraciones y Seeders
🧩 Modelos (app/Models/*.php)
Modelos actualizados:

Carrito, CarritoProducto, Categoria, Direcciones, Pedidos, PedidosProductos, Producto, User

Cambios aplicados:

🔁 Redefinición de relaciones.

📋 Ajustes en fillable y hidden.

⚙️ Mejoras en la estructura de datos para eficiencia y seguridad.

🛠️ Migraciones (database/migrations/*.php)
Tablas modificadas:

categorias, carrito_producto, direcciones, pedidos, pedido_producto

Mejoras:

➕ Nuevas columnas.

🔄 Cambios en tipos de datos.

🛡️ Nuevas restricciones para mantener consistencia.

🌱 Seeders (database/seeders/*.php)
Archivos ajustados:

CategoriaProductoSeeder.php

DatabaseSeeder.php

Propósito:

Inicializar la base de datos con datos de prueba coherentes con el nuevo esquema.

✨ 2. Nuevas Funcionalidades
Se añadieron nuevos componentes clave que amplían el sistema.

🧾 2.1 Nuevos Controladores
CarritoController.php

🛍️ Lógica de carrito de compras: agregar, actualizar y eliminar productos.

DireccionController.php

🏠 Gestión de direcciones de envío y facturación mediante operaciones CRUD.

🧱 2.2 Nuevas Migraciones y Seeders
2025_06_04_155130_create_carritos_table.php

🗃️ Crea la tabla carritos, esencial para implementar el sistema de carrito de compras.

UsuarioDireccionSeeder.php

📥 Seeder para poblar la base de datos con usuarios y sus direcciones asociadas, útil en desarrollo y pruebas.

🚀 Notas Finales
Este backend está diseñado para escalar, proteger los datos del usuario y facilitar la integración con frontend o apps móviles. Con estas mejoras, se establece una base sólida para una experiencia de compra en línea segura, confiable y eficiente.


Version 1.13
-Agregacion de Categoria Controller
-Adicion de api para categorias

Version 1.12
-Agregacion de un madleware

-Comprobacion de los API (Productos)
    -POST (Funcional)
    -PUT (Funcional)
    -DELETE (Funcional)

-Correcion de errores en ProductosController.php


Version 1.10

-Conexion de Laravel con la Base de Datos
-Creacion de las tablas de la base de datos
-Creacion de los controlladores
-Instalacion de las APIs y conexion de las APIs