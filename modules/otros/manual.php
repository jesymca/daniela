<?php include '../../header.php'; ?>

<div class="container mt-4">
    <h2>Manual de Uso del Sistema</h2>
    <div class="accordion" id="manualAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    Introducción
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#manualAccordion">
                <div class="accordion-body">
                    Este manual proporciona una guía básica para el uso del Sistema de Gestión de Michele C.A.
                    El sistema está dividido en módulos principales: Dashboard, Inventario, Ventas, Compras, Clientes, Proveedores y Otros.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Gestión de Ventas
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#manualAccordion">
                <div class="accordion-body">
                    Para crear una venta: Ve a Ventas > Ver Ventas. Haz clic en "Nueva Venta". Selecciona o crea un cliente, agrega productos, calcula IVA automáticamente. Guarda la venta.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    Gestión de Compras
                </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#manualAccordion">
                <div class="accordion-body">
                    Para registrar una compra: Ve a Compras > Ver Compras. Haz clic en "Nueva Compra". Selecciona o crea un proveedor, ingresa el número de factura, agrega productos. El inventario se actualiza automáticamente.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFour">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                    Gestión de Inventario
                </button>
            </h2>
            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#manualAccordion">
                <div class="accordion-body">
                    Para gestionar productos: Ve a Inventario > Ver Productos. Puedes agregar, editar o eliminar productos. El stock se actualiza con ventas y compras.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFive">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                    Clientes y Proveedores
                </button>
            </h2>
            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#manualAccordion">
                <div class="accordion-body">
                    Gestiona clientes y proveedores desde sus respectivos menús. Incluye información como RIF, teléfono, dirección.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingSix">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                    Reportes y Mantenimiento
                </button>
            </h2>
            <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#manualAccordion">
                <div class="accordion-body">
                    Accede a reportes desde Otros > Reportes. Para mantenimiento, ve a Otros > Mantenimiento de Base de Datos para respaldos y optimización.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../footer.php'; ?>