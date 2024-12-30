<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Consultor</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #F8F9FA;
            color: #343A40;
        }

        .navbar {
            background-color: #F8F9FA;
            border-bottom: 1px solid #E9ECEF;
        }

        .navbar-brand img {
            max-height: 50px;
        }

        .header-logos img {
            max-height: 50px;
            margin-right: 10px;
        }

        .content {
            padding: 20px;
        }

        .table th {
            background-color: #6C757D;
            color: #FFF;
        }

        .table td a {
            color: #495057;
            text-decoration: none;
        }

        .table td a:hover {
            color: #007BFF;
        }

        .logout-button {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">
            <!-- Logo izquierdo -->
            <div>
                <a href="https://dashboard.cycloidtalent.com/login" target="_blank">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
                </a>
            </div>
            <!-- Logo centro -->
            <div>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst" target="_blank">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
                </a>
            </div>
            <!-- Logo derecho -->
            <div>
                <a href="https://cycloidtalent.com/" target="_blank">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
                </a>
            </div>
        </div>
    </nav>

    <div style="height: 160px;"></div>

    <div class="container-fluid content">
        <div class="welcome-banner p-4 mb-4 rounded" style="background-color: #E9F7EF; border-left: 5px solid #28A745; color: #2D3436;">
            <h3 class="mb-3" style="color: #28A745;">¡Bienvenido al Dashboard de Consultores de Cycloid Talent!</h3>
            <p>En esta plataforma, podrás gestionar de manera eficiente la información esencial para tu labor diaria. Aquí encontrarás:</p>
            <ul>
                <li><strong>Capacitaciones y cronogramas</strong>: Planifica y organiza tus sesiones de formación de manera efectiva.</li>
                <li><strong>Documentación relevante</strong>: Accede a reportes, matrices y políticas actualizadas para mantenerte al día.</li>
                <li><strong>Gestión de clientes y consultores</strong>: Administra de forma eficiente la información de clientes y colegas.</li>
                <li><strong>Indicadores y KPIs</strong>: Monitorea y analiza resultados para un desempeño óptimo.</li>
                <li><strong>Evaluaciones y planes de trabajo</strong>: Asegura la calidad y el cumplimiento de objetivos en tus proyectos.</li>
            </ul>
            <p class="mt-3">Explora las diferentes secciones y aprovecha las herramientas disponibles para optimizar tu desempeño.</p>
        </div>


        <!-- Tabla con DataTables -->
        <div class="table-responsive">
            <table id="consultorTable" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Detalle</th>
                        <th>Descripción/Funcionalidad</th> <!-- Nueva columna -->
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Capacitaciones y Evaluaciones -->
                    <tr>
                        <td>Capacitaciones</td>
                        <td>Accede a la lista de capacitaciones disponibles.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listCapacitaciones') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Cronogramas de Capacitación</td>
                        <td>Accede a los cronogramas detallados de capacitaciones programadas.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listcronogCapacitacion') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Evaluaciones</td>
                        <td>Accede a la lista de evaluaciones realizadas y sus resultados.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listEvaluaciones') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>

                    <!-- Gestión de Clientes y Consultores -->
                    <tr>
                        <td>Clientes</td>
                        <td>Consulta los detalles de los clientes registrados.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('/listClients') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Consultores</td>
                        <td>Consulta la información de los consultores activos.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('/index.php/listConsultants') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>

                    <!-- Documentación -->
                    <tr>
                        <td>Documentos</td>
                        <td>Accede a los documentos generados en el sistema.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('/reportList') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Documentos y Matrices</td>
                        <td>Consulta los tipos de documentos y matrices disponibles.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listReportTypes') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Matrices</td>
                        <td>Matrices Interactivas de Gestión</td> <!-- Descripción -->
                        <td><a href="<?= base_url('matrices/list') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Looker Studio</td>
                        <td>Tableros de Indicadores del cliente</td> <!-- Descripción -->
                        <td><a href="<?= base_url('lookerstudio/list') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Versiones</td>
                        <td>Accede a la lista de versiones de los documentos generados.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listVersions') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Políticas</td>
                        <td>Consulta las políticas asociadas a la empresa.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('/listPolicies') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Tipos de Documentos</td>
                        <td>Consulta los diferentes tipos de documentos configurados.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('/listPolicyTypes') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>

                    <!-- Indicadores y KPIs -->
                    <tr>
                        <td>Indicadores de Clientes</td>
                        <td>Consulta los indicadores clave de desempeño para clientes.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listClientKpis') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Tipos de Indicadores y Significados</td>
                        <td>Accede a la lista de tipos de indicadores y sus significados.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listKpiTypes') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Definiciones de Indicadores</td>
                        <td>Consulta las definiciones y detalles de los indicadores utilizados.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listKpiDefinitions') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Nombres de Indicadores</td>
                        <td>Consulta la lista de nombres de indicadores configurados.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listKpis') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Responsables de Indicadores</td>
                        <td>Consulta la lista de responsables de cada indicador.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listDataOwners') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Numeradores de Indicadores</td>
                        <td>Consulta los numeradores utilizados en los indicadores.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listNumeratorVariables') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Denominadores de Indicadores</td>
                        <td>Consulta los denominadores utilizados en los indicadores.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listDenominatorVariables') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Objetivos de Indicadores</td>
                        <td>Consulta los objetivos de los indicadores establecidos.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listObjectives') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Políticas de SST</td>
                        <td>Accede a las políticas de Seguridad y Salud en el Trabajo.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listKpiPolicies') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>

                    <!-- Otros -->
                    <tr>
                        <td>Vigías</td>
                        <td>Consulta la lista de vigías asociados a la empresa.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listVigias') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Actividades del Plan Anual</td>
                        <td>Accede a las actividades del plan anual para la empresa.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listPlanDeTrabajoAnual') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Pendientes</td>
                        <td>Consulta la lista de tareas pendientes dentro de la plataforma.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listPendientes') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Matrices Cycloid</td>
                        <td>Consulta las matrices de datos de Cycloid.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listMatricesCycloid') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                    <tr>
                        <td>Períodos de Medición</td>
                        <td>Consulta los períodos de medición configurados para los indicadores.</td> <!-- Descripción -->
                        <td><a href="<?= base_url('listMeasurementPeriods') ?>" target="_blank"><button type="button" class="btn btn-outline-secondary">Abrir</button></a></td>
                    </tr>
                </tbody>
            </table>

        </div>

        <div class="logout-button">
            <a href="<?= base_url('/logout') ?>" target="_blank"><button type="button" class="btn btn-danger">Cerrar Sesión</button></a>
        </div>
    </div>

    <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
            <!-- Company and Rights -->
            <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
            <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
            <p style="margin: 5px 0;">NIT: 901.653.912</p>

            <!-- Website Link -->
            <p style="margin: 5px 0;">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" style="color: #007BFF; text-decoration: none;">https://cycloidtalent.com/</a>
            </p>

            <!-- Social Media Links -->
            <p style="margin: 15px 0 5px;"><strong>Nuestras Redes Sociales:</strong></p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
                </a>
            </div>
        </div>
    </footer>

    <!-- jQuery y DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#consultorTable').DataTable({
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 50,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json'
                }
            });
        });
    </script>

</body>

</html>