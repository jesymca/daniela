<?php
/**
 * ============================================================================
 * PROYECTO ACADÉMICO - SISTEMA DE GESTIÓN ADMINISTRATIVO MICHELE C.A.
 * Archivo: modules/otros/about.php
 * Descripción: Presentación institucional del proyecto sociotécnico y empresa.
 * ============================================================================
 */

include __DIR__ . '/../../header.php';
?>

<div class="container mt-4 mb-5">
    <!-- Encabezado del Módulo -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="text-primary fw-bold"><i class="fas fa-info-circle me-2"></i> Sobre El Sistema</h1>
            <p class="lead text-muted">Software de Gestión Administrativo Basado en la Web para la Estabilidad Financiera de la Empresa Repuestos y Partes Michele C.A.</p>
            <span class="badge bg-primary fs-6 px-3 py-2"><i class="fas fa-graduation-cap me-1"></i> Proyecto Socio-Tecnológico PNFI</span>
        </div>
    </div>

    <!-- Ficha Académica del Proyecto -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white py-3">
            <h5 class="mb-0"><i class="fas fa-university me-2"></i> Marco Institucional Académico</h5>
        </div>
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-3 text-center mb-3 mb-md-0">
                    <img src="image/logo_m.jpeg" alt="Logo Michele C.A." class="img-fluid rounded shadow-sm" style="max-height: 140px; object-fit: contain;">
                </div>
                <div class="col-md-9">
                    <h5 class="fw-bold text-primary">Universidad Politécnica Territorial de Puerto Cabello (UPTPC)</h5>
                    <p class="mb-1"><strong>Programa Nacional de Formación en Informática (PNFI)</strong></p>
                    <p class="mb-2 text-muted">Puerto Cabello - Estado Carabobo, Venezuela (Septiembre de 2026)</p>
                    <hr class="my-2">
                    <div class="row text-sm">
                        <div class="col-md-6">
                            <p class="mb-1"><strong><i class="fas fa-users text-primary me-1"></i> Autores (Investigadores):</strong></p>
                            <ul class="list-unstyled ms-3 mb-2 mb-md-0">
                                <li><i class="fas fa-user-graduate me-1 text-secondary"></i> Linarez Daniela</li>
                                <li><i class="fas fa-user-graduate me-1 text-secondary"></i> Padrón Eudimar</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong><i class="fas fa-chalkboard-teacher text-primary me-1"></i> Tutora del Proyecto:</strong></p>
                            <p class="ms-3 mb-0"><i class="fas fa-user-tie me-1 text-secondary"></i> Profa. Raiza Galíndez</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reseña Histórica e Información de Michele C.A. -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i> Reseña Histórica de la Empresa</h5>
                </div>
                <div class="card-body p-4">
                    <p class="card-text text-justify">
                        La génesis de <strong>Repuestos y Partes Michele C.A.</strong> se remonta al año <strong>1980</strong> en Boca de Aroa, Municipio José Laurencio Silva del Estado Falcón. Fue fundada por el ciudadano italiano <strong>Michele Severino Barone</strong> bajo la denominación de <em>Agro-Taller San Miguel</em>.
                    </p>
                    <p class="card-text text-justify">
                        En el año 2000, la empresa redefinió su enfoque comercial hacia la venta y suministro exclusivo de repuestos (<em>Agro Repuestos San Michele C.A.</em>). Tras el fallecimiento de su fundador en 2021, la administración fue asumida por su esposa e hijos, adoptando el nombre actual y manteniéndose como un referente comercial de tradición familiar en la región.
                    </p>
                    <div class="alert alert-light border-start border-primary border-4 mb-0">
                        <i class="fas fa-map-marker-alt text-danger me-2"></i> <strong>Ubicación:</strong> Carretera Morón-Coro, Sector Las Delicias, Boca de Aroa, Edo. Falcón.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-bullseye me-2"></i> Misión y Visión</h5>
                </div>
                <div class="card-body p-4">
                    <h6 class="fw-bold text-success"><i class="fas fa-flag me-1"></i> Misión</h6>
                    <p class="card-text small mb-3">
                        Tener la capacidad de distribución con excelencia y calidad de marca en sus productos, generando empleo, mayor rentabilidad y mejor calidad de vida para todo su personal laborante en sus distintos establecimientos.
                    </p>
                    <hr>
                    <h6 class="fw-bold text-success"><i class="fas fa-eye me-1"></i> Visión</h6>
                    <p class="card-text small mb-0">
                        Consolidarse a mediano y corto plazo como una red de distribución de repuestos automotrices reconocida a nivel regional y nacional, garantizando estabilidad económica sostenible y un servicio de vanguardia.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Metodología y Stack Tecnológico -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-info text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i> Metodología de Desarrollo</h5>
                </div>
                <div class="card-body p-4">
                    <h6 class="fw-bold text-info">Ingeniería Web (IWEB - Roger Pressman)</h6>
                    <p class="small text-muted mb-3">Ciclo de vida adaptativo, iterativo e incremental estructurado en 6 fases:</p>
                    <ol class="small mb-0 ps-3">
                        <li><strong>Formulación:</strong> Levantamiento de necesidades críticas de negocio.</li>
                        <li><strong>Planificación:</strong> Estructura cliente-servidor (XAMPP/Apache/MySQL).</li>
                        <li><strong>Análisis:</strong> Modelado de diagramas UML (DER, Casos de Uso, Secuencia).</li>
                        <li><strong>Diseño:</strong> Árbol de navegación y arquitectura UX/UI.</li>
                        <li><strong>Ingeniería (Construcción):</strong> Desarrollo en PHP, SQL y JavaScript.</li>
                        <li><strong>Generación de Páginas:</strong> Despliegue de componentes responsivos.</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-warning text-dark py-3">
                    <h5 class="mb-0"><i class="fas fa-laptop-code me-2"></i> Stack Tecnológico</h5>
                </div>
                <div class="card-body p-4">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fab fa-php text-primary me-2 fs-5"></i> <strong>Backend:</strong> PHP 8+ (PDO, BCrypt)</span>
                            <span class="badge bg-primary rounded-pill">Servidor</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-database text-warning me-2 fs-5"></i> <strong>Base de Datos:</strong> MySQL / MariaDB</span>
                            <span class="badge bg-warning text-dark rounded-pill">Relacional</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fab fa-bootstrap text-purple me-2 fs-5" style="color:#6f42c1;"></i> <strong>Frontend:</strong> Bootstrap 5 & CSS3</span>
                            <span class="badge bg-purple rounded-pill text-white" style="background-color:#6f42c1;">Responsive</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fab fa-js-square text-warning me-2 fs-5"></i> <strong>Dinamismo:</strong> JavaScript (ES6+ Vanilla)</span>
                            <span class="badge bg-secondary rounded-pill">Cliente</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-server text-success me-2 fs-5"></i> <strong>Servidor Local:</strong> XAMPP / Apache</span>
                            <span class="badge bg-success rounded-pill">Cross-Platform</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../footer.php'; ?>