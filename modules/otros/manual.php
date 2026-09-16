<?php
/**
 * ============================================================================
 * PROYECTO ACADÉMICO - SISTEMA DE GESTIÓN ADMINISTRATIVO MICHELE C.A.
 * Archivo: modules/otros/manual.php
 * Descripción: Manual de usuario detallado e interactivo del sistema.
 * ============================================================================
 */

include __DIR__ . '/../../header.php';
?>

<div class="container mt-4 mb-5">
    <!-- Encabezado del Manual -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="text-primary fw-bold"><i class="fas fa-book-open me-2"></i> Manual de Usuario del Sistema</h1>
            <p class="lead text-muted">Guía práctica operativa y administrativa para la empresa Repuestos y Partes Michele C.A.</p>
        </div>
    </div>

    <!-- Acordeón Principal del Manual -->
    <div class="accordion shadow-sm border-0" id="manualAccordion">

        <!-- 1. Introducción y Arquitectura -->
        <div class="accordion-item border-0 mb-3 rounded shadow-sm">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button bg-primary text-white fw-bold rounded-top" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    <i class="fas fa-cubes me-2"></i> 1. Introducción y Arquitectura del Sistema
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#manualAccordion">
                <div class="accordion-body bg-white p-4">
                    <h5 class="text-primary">Bienvenido al Sistema de Gestión de Michele C.A.</h5>
                    <p>
                        Este software ha sido concebido bajo la metodología de <strong>Ingeniería Web (IWEB)</strong> como un portal centralizado para automatizar las operaciones comerciales de compra, venta, inventario y mantenimiento financiero de la empresa ubicada en Boca de Aroa, Edo. Falcón.
                    </p>
                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <div class="border p-3 rounded bg-light h-100">
                                <h6 class="fw-bold text-dark"><i class="fas fa-user-shield me-2 text-primary"></i> Rol Administrador</h6>
                                <ul class="small mb-0 ps-3">
                                    <li>Acceso completo a todos los módulos operacionales.</li>
                                    <li>Gestión de proveedores y órdenes de compra.</li>
                                    <li>Generación de reportes gerenciales en PDF.</li>
                                    <li>Mantenimiento y respaldo de la base de datos.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border p-3 rounded bg-light h-100">
                                <h6 class="fw-bold text-dark"><i class="fas fa-user-tag me-2 text-success"></i> Rol Vendedor / Almacenista</h6>
                                <ul class="small mb-0 ps-3">
                                    <li>Facturación y registro rápido de ventas.</li>
                                    <li>Consulta de catálogo y existencias en tiempo real.</li>
                                    <li>Gestión de clientes y edición de perfil personal.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Autenticación y Ojito de Contraseña -->
        <div class="accordion-item border-0 mb-3 rounded shadow-sm">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed bg-primary text-white fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    <i class="fas fa-key me-2"></i> 2. Iniciar Sesión y Ojito de Contraseña
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#manualAccordion">
                <div class="accordion-body bg-white p-4">
                    <h5 class="text-primary">Seguridad y Visibilidad de Credenciales</h5>
                    <ol class="mb-3">
                        <li class="mb-2">
                            <strong>Iniciar Sesión:</strong> Ingrese su correo electrónico registrado (ej: <code>admin@micheleca.com</code>) y su clave de acceso.
                        </li>
                        <li class="mb-2">
                            <strong>Botón de Ojito (<i class="fas fa-eye text-primary"></i> / <i class="fas fa-eye-slash text-secondary"></i>):</strong> Al escribir la contraseña, haga clic en el botón con el ícono de ojo ubicado a la derecha del campo. Esto alternará la visibilidad del texto para verificar que la clave ingresada sea correcta sin cometer errores de tipeo.
                        </li>
                        <li class="mb-2">
                            <strong>Cambio de Contraseña:</strong> Diríjase a <span class="badge bg-secondary">Perfil</span> en el menú superior para actualizar su nombre, correo o modificar su contraseña de forma segura mediante encriptación BCrypt.
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- 3. Gestión de Inventario -->
        <div class="accordion-item border-0 mb-3 rounded shadow-sm">
            <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed bg-primary text-white fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    <i class="fas fa-boxes me-2"></i> 3. Gestión de Inventario y Catálogo (Ford, Chevrolet, Toyota)
                </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#manualAccordion">
                <div class="accordion-body bg-white p-4">
                    <h5 class="text-primary">Catálogo Maestro de Repuestos</h5>
                    <p>El módulo de inventario permite administrar el stock de productos de las marcas atendidas (<strong>Ford, Chevrolet y Toyota</strong>):</p>
                    <ul class="mb-3">
                        <li><strong>Categorías Principales:</strong> Componentes de Motor, Bombeo y Fluidos, Filtración/Correas, Frenos/Suspensión, Rodamientos/Transmisión y Lubricantes.</li>
                        <li><strong>Agregar Productos:</strong> Haga clic en "Agregar Producto", complete el nombre, código de barras, categoría, precio y stock inicial.</li>
                        <li><strong>Actualización Automática de Stock:</strong> Cada venta registrada sustrae unidades del inventario en tiempo real; cada compra procesada suma unidades automáticamente.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 4. Gestión de Ventas -->
        <div class="accordion-item border-0 mb-3 rounded shadow-sm">
            <h2 class="accordion-header" id="headingFour">
                <button class="accordion-button collapsed bg-primary text-white fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                    <i class="fas fa-shopping-cart me-2"></i> 4. Registro de Ventas y Cálculo de IVA (16%)
                </button>
            </h2>
            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#manualAccordion">
                <div class="accordion-body bg-white p-4">
                    <h5 class="text-primary">Proceso de Facturación de Salida</h5>
                    <ol>
                        <li class="mb-2">Vaya al menú <strong>Ventas &gt; Ver Ventas</strong> y seleccione "Nueva Venta".</li>
                        <li class="mb-2">Seleccione un cliente registrado por su RIF o Cédula (o cree uno nuevo en el momento).</li>
                        <li class="mb-2">Seleccione los repuestos y especifique las cantidades. El sistema verificará en tiempo real si existe stock disponible en almacén.</li>
                        <li class="mb-2">El sistema calcula automáticamente el Subtotal, el **IVA (16%)** y el Total de la factura.</li>
                        <li class="mb-2">Haga clic en "Generar Factura" para consolidar la transacción y descontar las unidades del inventario.</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- 5. Gestión de Compras -->
        <div class="accordion-item border-0 mb-3 rounded shadow-sm">
            <h2 class="accordion-header" id="headingFive">
                <button class="accordion-button collapsed bg-primary text-white fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                    <i class="fas fa-truck me-2"></i> 5. Registro de Compras a Proveedores
                </button>
            </h2>
            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#manualAccordion">
                <div class="accordion-body bg-white p-4">
                    <h5 class="text-primary">Proceso de Abastecimiento de Mercancía</h5>
                    <p>Permite registrar las facturas de entrada recibidas de los proveedores:</p>
                    <ol>
                        <li class="mb-2">Vaya a <strong>Compras &gt; Ver Compras</strong> y haga clic en "Nueva Compra".</li>
                        <li class="mb-2">Ingrese el Número de Factura del proveedor y seleccione la empresa proveedora.</li>
                        <li class="mb-2">Añada los artículos recibidos con sus respectivos costos unitarios.</li>
                        <li class="mb-2">Al guardar, el sistema incrementará automáticamente el stock del catálogo maestro.</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- 6. Clientes y Proveedores -->
        <div class="accordion-item border-0 mb-3 rounded shadow-sm">
            <h2 class="accordion-header" id="headingSix">
                <button class="accordion-button collapsed bg-primary text-white fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                    <i class="fas fa-users me-2"></i> 6. Administración de Clientes y Proveedores
                </button>
            </h2>
            <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#manualAccordion">
                <div class="accordion-body bg-white p-4">
                    <h5 class="text-primary">Directorio Digital de Aliados Comerciales</h5>
                    <p>Permite mantener la información fiscal y de contacto actualizada:</p>
                    <ul>
                        <li><strong>Identificación Fiscal:</strong> Soporte para RIF Venezolano (J-12345678-9, V-12345678, G-, E-) y Cédulas de Identidad.</li>
                        <li><strong>Datos Registrados:</strong> Nombre/Razón Social, Correo Electrónico, Teléfono y Dirección Física.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 7. Reportes en PDF -->
        <div class="accordion-item border-0 mb-3 rounded shadow-sm">
            <h2 class="accordion-header" id="headingSeven">
                <button class="accordion-button collapsed bg-primary text-white fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                    <i class="fas fa-file-pdf me-2"></i> 7. Generación de Reportes Gerenciales en PDF
                </button>
            </h2>
            <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#manualAccordion">
                <div class="accordion-body bg-white p-4">
                    <h5 class="text-primary">Inteligencia de Negocio y Auditoría</h5>
                    <ol>
                        <li class="mb-2">Acceda a <strong>Otros &gt; Reportes</strong>.</li>
                        <li class="mb-2">Seleccione el tipo de reporte deseado (Ventas, Compras, Productos en Inventario, Proveedores o Clientes).</li>
                        <li class="mb-2">Establezca el filtro de Fecha Inicio y Fecha Fin para delimitar el período de auditoría.</li>
                        <li class="mb-2">Haga clic en <strong>"Descargar PDF"</strong> para obtener un documento impreso formateado con los totales calculados.</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- 8. Mantenimiento y Respaldos -->
        <div class="accordion-item border-0 mb-3 rounded shadow-sm">
            <h2 class="accordion-header" id="headingEight">
                <button class="accordion-button collapsed bg-primary text-white fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                    <i class="fas fa-database me-2"></i> 8. Mantenimiento de BD y Respaldos (Windows / Linux)
                </button>
            </h2>
            <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight" data-bs-parent="#manualAccordion">
                <div class="accordion-body bg-white p-4">
                    <h5 class="text-primary">Resguardo de Información Multiplataforma</h5>
                    <p>En el menú <strong>Otros &gt; Mantenimiento de Base de Datos</strong>:</p>
                    <ul>
                        <li class="mb-2">
                            <strong>Crear Respaldo SQL:</strong> Al pulsar "Crear Respaldo", el sistema detecta automáticamente si el servidor opera en Windows (XAMPP `mysqldump.exe`) o en Linux (`mariadb-dump`). Si la línea de comandos está bloqueada, ejecuta un **respaldo nativo mediante PDO** garantizando siempre el archivo `.sql`.
                        </li>
                        <li class="mb-2">
                            <strong>Descargar Respaldos:</strong> En la tabla de respaldos disponibles, utilice el botón <span class="badge bg-primary"><i class="fas fa-download"></i> Descargar</span> para guardar una copia local en su computadora.
                        </li>
                        <li class="mb-2">
                            <strong>Optimizar Tablas:</strong> Ejecuta la desfragmentación periódica del almacenamiento MySQL para acelerar la velocidad de consulta del sistema.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../../footer.php'; ?>