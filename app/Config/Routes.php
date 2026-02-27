<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

 $routes->cli('routes', function() use ($routes) {
    print_r($routes->getRoutes());
});

$routes->get('/', 'AuthController::login');
$routes->get('/login', 'AuthController::login');
$routes->post('/loginPost', 'AuthController::loginPost');
$routes->get('/logout', 'AuthController::logout');

// Recuperación de contraseña
$routes->get('/forgot-password', 'AuthController::forgotPassword');
$routes->post('/forgot-password', 'AuthController::forgotPasswordPost');
$routes->get('/reset-password/(:any)', 'AuthController::resetPassword/$1');
$routes->post('/reset-password-post', 'AuthController::resetPasswordPost');
$routes->get('/dashboardclient', 'ClientController::index');
$routes->get('/dashboardclient', 'ClientController::dashboard');
$routes->get('/dashboardclient', 'ClientController::dashboardSimplified');
$routes->get('/dashboard', 'ClientController::dashboard');
$routes->get('/dashboard', 'ClientController::showPanel');
$routes->get('client/dashboard', 'ClientController::dashboard');
$routes->get('client/suspended', 'AuthController::suspended');

// Rutas para dashboards específicos de cliente
$routes->get('client/dashboard-estandares/(:num)', 'ClientDashboardEstandaresController::index/$1');
$routes->get('client/dashboard-capacitaciones/(:num)', 'ClientDashboardCapacitacionesController::index/$1');
$routes->get('client/dashboard-plan-trabajo/(:num)', 'ClientDashboardPlanTrabajoController::index/$1');
$routes->get('client/dashboard-pendientes/(:num)', 'ClientDashboardPendientesController::index/$1');

// Rutas para PDF Unificado
$routes->get('/pdfUnificado', 'PdfUnificadoController::index');
$routes->get('/pdfUnificado/(:num)', 'PdfUnificadoController::index/$1');
$routes->post('/generarPdfUnificado', 'PdfUnificadoController::generarPdfUnificado');

// Rutas para dashboards de consultor (todos los clientes)
$routes->get('consultant/dashboard-estandares', 'ConsultantDashboardEstandaresController::index');
$routes->get('consultant/dashboard-capacitaciones', 'ConsultantDashboardCapacitacionesController::index');
$routes->get('consultant/dashboard-plan-trabajo', 'ConsultantDashboardPlanTrabajoController::index');
$routes->get('consultant/dashboard-pendientes', 'ConsultantDashboardPendientesController::index');

$routes->get('/dashboardconsultant', 'ConsultantController::index');
$routes->get('/admindashboard', 'AdminDashboardController::index');
$routes->get('/admin/delete-pta-abiertas', 'AdminDashboardController::deletePtaAbiertas');
$routes->post('/admin/count-pta-abiertas', 'AdminDashboardController::countPtaAbiertas');
$routes->post('/admin/delete-pta-abiertas', 'AdminDashboardController::deletePtaAbiertasPost');
$routes->get('/quick-access', 'QuickAccessDashboardController::index');

// Rutas para Ver Vista de Cliente (consultor y admin)
$routes->get('/vista-cliente', 'ViewAsClientController::index');
$routes->get('/vista-cliente/(:num)', 'ViewAsClientController::viewClient/$1');

$routes->get('/addClient', 'ConsultantController::addClient');
$routes->post('/addClient', 'ConsultantController::addClientPost');

$routes->get('/prueba_form', 'PruebaController::index');
$routes->post('/prueba_save', 'PruebaController::save');

$routes->get('/addTest', 'TestController::index');
$routes->post('/addTest', 'TestController::addTestPost');

$routes->get('/addConsultant', 'ConsultantController::addConsultant');
$routes->post('/addConsultantPost', 'ConsultantController::addConsultantPost');
$routes->get('/listConsultants', 'ConsultantController::listConsultants');
$routes->get('/editConsultant/(:num)', 'ConsultantController::editConsultant/$1');
$routes->post('/editConsultant/(:num)', 'ConsultantController::editConsultant/$1');
$routes->get('/deleteConsultant/(:num)', 'ConsultantController::deleteConsultant/$1');


$routes->get('/reportList', 'ReportController::reportList');
$routes->get('/addReport', 'ReportController::addReport');
$routes->post('/addReportPost', 'ReportController::addReportPost');
$routes->get('/editReport/(:num)', 'ReportController::editReport/$1');
$routes->post('/editReportPost/(:num)', 'ReportController::editReportPost/$1');
$routes->get('/deleteReport/(:num)', 'ReportController::deleteReport/$1');

$routes->get('/report_dashboard', 'ClienteReportController::index');
$routes->get('/report_dashboard/(:num)', 'ClienteReportController::index/$1');
$routes->get('/documento', 'DocumentoController::mostrarDocumento');

$routes->get('/showPhoto/(:num)', 'ConsultantController::showPhoto/$1');
$routes->post('/editConsultantPost/(:num)', 'ConsultantController::editConsultantPost/$1');
$routes->get('/documento', 'ClientController::documento');

$routes->get('/listClients', 'ConsultantController::listClients');
$routes->get('/editClient/(:num)', 'ConsultantController::editClient/$1');
$routes->post('/updateClient/(:num)', 'ConsultantController::updateClient/$1');
$routes->get('/deleteClient/(:num)', 'ConsultantController::deleteClient/$1');
$routes->post('/addClientPost', 'ConsultantController::addClientPost');

// Acciones de estado del cliente
$routes->post('/cliente/reactivar/(:num)',    'ConsultantController::reactivarCliente/$1');
$routes->post('/cliente/retirar/(:num)',      'ConsultantController::retirarCliente/$1');
$routes->post('/cliente/pendiente/(:num)',    'ConsultantController::marcarPendienteCliente/$1');
$routes->post('/cliente/paz-y-salvo/(:num)', 'ConsultantController::emitirPazYSalvo/$1');
$routes->post('/cliente/reenviar-credenciales/(:num)', 'ConsultantController::resendCredentials/$1');
$routes->get('/responsableSGSST/(:num)', 'SGSSTPlanear::responsableDelSGSST/$1');

$routes->get('/error', 'ErrorController::index');

$routes->get('/reportTypes', 'ReportTypeController::index');
$routes->get('/reportTypes/add', 'ReportTypeController::add');
$routes->post('/reportTypes/addPost', 'ReportTypeController::addPost');

$routes->get('/addReportType', 'ReportTypeController::addReportType');
$routes->post('/addReportTypePost', 'ReportTypeController::addReportTypePost');

$routes->get('/listReportTypes', 'ReportTypeController::index');

$routes->get('/listReportTypes', 'ReportTypeController::listReportTypes');
$routes->get('/addReportType', 'ReportTypeController::addReportType');
$routes->post('/addReportTypePost', 'ReportTypeController::addReportTypePost');
$routes->get('/editReportType/(:num)', 'ReportTypeController::edit/$1');
$routes->post('/editReportTypePost/(:num)', 'ReportTypeController::editPost/$1');
$routes->get('/deleteReportType/(:num)', 'ReportTypeController::delete/$1');

$routes->get('/viewDocuments', 'ClientController::viewDocuments');
$routes->get('/viewDocuments/(:num)', 'ClientController::viewDocuments/$1');

$routes->get('/listPolicies', 'PolicyController::listPolicies');
$routes->get('/addPolicy', 'PolicyController::addPolicy');
$routes->post('/addPolicyPost', 'PolicyController::addPolicyPost');
$routes->get('/editPolicy/(:num)', 'PolicyController::editPolicy/$1');
$routes->post('/editPolicyPost/(:num)', 'PolicyController::editPolicyPost/$1');
$routes->get('/deletePolicy/(:num)', 'PolicyController::deletePolicy/$1');

$routes->get('/listPolicyTypes', 'PolicyController::listPolicyTypes');
$routes->get('/addPolicyType', 'PolicyController::addPolicyType');
$routes->post('/addPolicyTypePost', 'PolicyController::addPolicyTypePost');
$routes->get('/editPolicyType/(:num)', 'PolicyController::editPolicyType/$1');
$routes->post('/editPolicyTypePost/(:num)', 'PolicyController::editPolicyTypePost/$1');
$routes->get('/deletePolicyType/(:num)', 'PolicyController::deletePolicyType/$1');

$routes->get('/policyNoAlcoholDrogas/(:num)', 'SGSSTPlanear::policyNoAlcoholDrogas/$1');
$routes->get('/asignacionResponsable/(:num)', 'PzasignacionresponsableController::asignacionResponsable/$1');
$routes->get('/asignacionResponsabilidades/(:num)', 'PzasignacionresponsabilidadesController::asignacionResponsabilidades/$1');
$routes->get('/prueba1/(:num)', 'Prueba1Controller::prueba1/$1');
$routes->get('/viewPolicy/(:num)', 'ClientDocumentController::viewPolicy/$1');
$routes->get('/addVersion', 'VersionController::addVersion');
$routes->post('/addVersionPost', 'VersionController::addVersionPost');
$routes->get('/editVersion/(:num)', 'VersionController::editVersion/$1');
$routes->post('/editVersionPost/(:num)', 'VersionController::editVersionPost/$1');
$routes->get('/deleteVersion/(:num)', 'VersionController::deleteVersion/$1');
$routes->get('/listVersions', 'VersionController::listVersions');
$routes->get('/getVersionsByClient/(:num)', 'VersionController::getVersionsByClient/$1');
$routes->get('/generatePdfNoAlcoholDrogas', 'SGSSTPlanear::generatePdfNoAlcoholDrogas');
$routes->get('/generatePdf_asignacionResponsable', 'PzasignacionresponsableController::generatePdf_asignacionResponsable');
$routes->get('/generatePdf_asignacionResponsabilidades', 'PzasignacionresponsabilidadesController::generatePdf_asignacionResponsabilidades');

$routes->get('/asignacionVigia/(:num)', 'PzvigiaController::asignacionVigia/$1');
$routes->get('/generatePdf_asignacionVigia', 'PzvigiaController::generatePdf_asignacionVigia');
$routes->get('/exoneracionCocolab/(:num)', 'PzexoneracioncocolabController::exoneracionCocolab/$1');
$routes->get('/generatePdf_exoneracionCocolab', 'PzexoneracioncocolabController::generatePdf_exoneracionCocolab');
$routes->get('/registroAsistencia/(:num)', 'PzregistroasistenciaController::registroAsistencia/$1');
$routes->get('/generatePdf_registroAsistencia', 'PzregistroasistenciaController::generatePdf_registroAsistencia');
$routes->get('/actaCopasst/(:num)', 'PzactacopasstController::actaCopasst/$1');
$routes->get('/generatePdf_actaCopasst', 'PzactacopasstController::generatePdf_actaCopasst');
$routes->get('/inscripcionCopasst/(:num)', 'PzinscripcioncopasstController::inscripcionCopasst/$1');
$routes->get('/generatePdf_inscripcionCopasst', 'PzinscripcioncopasstController::generatePdf_inscripcionCopasst');
$routes->get('/formatoAsistencia/(:num)', 'PzformatodeasistenciaController::formatoAsistencia/$1');
$routes->get('/generatePdf_formatoAsistencia', 'PzformatodeasistenciaController::generatePdf_formatoAsistencia');
$routes->get('/confidencialidadCocolab/(:num)', 'PzconfidencialidadcocolabController::confidencialidadCocolab/$1');
$routes->get('/generatePdf_confidencialidadCocolab', 'PzconfidencialidadcocolabController::generatePdf_confidencialidadCocolab');
$routes->get('/inscripcionCocolab/(:num)', 'PzinscripcioncocolabController::inscripcionCocolab/$1');
$routes->get('/generatePdf_inscripcionCocolab', 'PzinscripcioncocolabController::generatePdf_inscripcionCocolab');
$routes->get('/quejaCocolab/(:num)', 'PzquejacocolabController::quejaCocolab/$1');
$routes->get('/generatePdf_quejaCocolab', 'PzquejacocolabController::generatePdf_quejaCocolab');
$routes->get('/manconvivenciaLaboral/(:num)', 'PzmanconvivencialaboralController::manconvivenciaLaboral/$1');
$routes->get('/generatePdf_manconvivenciaLaboral', 'PzmanconvivencialaboralController::generatePdf_manconvivenciaLaboral');
$routes->get('/prcCocolab/(:num)', 'PzprccocolabController::prcCocolab/$1');
$routes->get('/generatePdf_prcCocolab', 'PzprccocolabController::generatePdf_prcCocolab');
$routes->get('/prgCapacitacion/(:num)', 'PzprgcapacitacionController::prgCapacitacion/$1');
$routes->get('/generatePdf_prgCapacitacion', 'PzprgcapacitacionController::generatePdf_prgCapacitacion');
$routes->get('/prgInduccion/(:num)', 'PzprginduccionController::prgInduccion/$1');
$routes->get('/generatePdf_prgInduccion', 'PzprginduccionController::generatePdf_prgInduccion');
$routes->get('/ftevaluacionInduccion/(:num)', 'PzftevaluacioninduccionController::ftevaluacionInduccion/$1');
$routes->get('/generatePdf_ftevaluacionInduccion', 'PzftevaluacioninduccionController::generatePdf_ftevaluacionInduccion');
$routes->get('/politicaSst/(:num)', 'PzpoliticasstController::politicaSst/$1');
$routes->get('/generatePdf_politicaSst', 'PzpoliticasstController::generatePdf_politicaSst');
$routes->get('/politicaAlcohol/(:num)', 'PzpoliticaalcoholController::politicaAlcohol/$1');
$routes->get('/generatePdf_politicaAlcohol', 'PzpoliticaalcoholController::generatePdf_politicaAlcohol');
$routes->get('/politicaEmergencias/(:num)', 'PzpoliticaemergenciasController::politicaEmergencias/$1');
$routes->get('/generatePdf_politicaEmergencias', 'PzpoliticaemergenciasController::generatePdf_politicaEmergencias');
$routes->get('/politicaEpps/(:num)', 'PzpoliticaeppsController::politicaEpps/$1');
$routes->get('/generatePdf_politicaEpps', 'PzpoliticaeppsController::generatePdf_politicaEpps');
$routes->get('/politicaPesv/(:num)', 'PzpoliticapesvController::politicaPesv/$1');
$routes->get('/generatePdf_politicaPesv', 'PzpoliticapesvController::generatePdf_politicaPesv');
$routes->get('/regHigsegind/(:num)', 'PzreghigsegindController::regHigsegind/$1');
$routes->get('/generatePdf_regHigsegind', 'PzreghigsegindController::generatePdf_regHigsegind');
$routes->get('/oBjetivos/(:num)', 'PzobjetivosController::oBjetivos/$1');
$routes->get('/generatePdf_oBjetivos', 'PzobjetivosController::generatePdf_oBjetivos');
$routes->get('/documentosSgsst/(:num)', 'PzdocumentacionController::documentosSgsst/$1');
$routes->get('/generatePdf_documentosSgsst', 'PzdocumentacionController::generatePdf_documentosSgsst');
$routes->get('/rendicionCuentas/(:num)', 'PzrendicionController::rendicionCuentas/$1');
$routes->get('/generatePdf_rendicionCuentas', 'PzrendicionController::generatePdf_rendicionCuentas');
$routes->get('/comunicacionInterna/(:num)', 'PzcomunicacionController::comunicacionInterna/$1');
$routes->get('/generatePdf_comunicacionInterna', 'PzcomunicacionController::generatePdf_comunicacionInterna');
$routes->get('/manProveedores/(:num)', 'PzmanproveedoresController::manProveedores/$1');
$routes->get('/generatePdf_manProveedores', 'PzmanproveedoresController::generatePdf_manProveedores');
$routes->get('/saneamientoBasico/(:num)', 'PzsaneamientoController::saneamientoBasico/$1');
$routes->get('/generatePdf_saneamientoBasico', 'PzsaneamientoController::generatePdf_saneamientoBasico');
$routes->get('/medPreventiva/(:num)', 'PzmedpreventivaController::medPreventiva/$1');
$routes->get('/generatePdf_medPreventiva', 'PzmedpreventivaController::generatePdf_medPreventiva');
$routes->get('/reporteAccidente/(:num)', 'PzrepoaccidenteController::reporteAccidente/$1');
$routes->get('/generatePdf_reporteAccidente', 'PzrepoaccidenteController::generatePdf_reporteAccidente');
$routes->get('/inspeccionPlanynoplan/(:num)', 'PzinpeccionplanynoplanController::inspeccionPlanynoplan/$1');
$routes->get('/generatePdf_inspeccionPlanynoplan', 'PzinpeccionplanynoplanController::generatePdf_inspeccionPlanynoplan');
$routes->get('/funcionesyresponsabilidades/(:num)', 'HzfuncionesyrespController::funcionesyresponsabilidades/$1');
$routes->get('/generatePdf_entregaDotacion', 'HzentregadotacionController::generatePdf_entregaDotacion');
$routes->get('/responsablePesv/(:num)', 'HzresponsablepesvController::responsablePesv/$1');
$routes->get('/generatePdf_responsablePesv', 'HzresponsablepesvController::generatePdf_responsablePesv');
$routes->get('/responsabilidadesSalud/(:num)', 'HzrespsaludController::responsabilidadesSalud/$1');
$routes->get('/generatePdf_responsabilidadesSalud', 'HzrespsaludController::generatePdf_responsabilidadesSalud');
$routes->get('/indentPeligros/(:num)', 'HzindentpeligroController::indentPeligros/$1');
$routes->get('/generatePdf_indentPeligros', 'HzindentpeligroController::generatePdf_indentPeligros');
$routes->get('/revisionAltagerencia/(:num)', 'HzrevaltagerenciaController::revisionAltagerencia/$1');
$routes->get('/generatePdf_revisionAltagerencia', 'HzrevaltagerenciaController::generatePdf_revisionAltagerencia');
$routes->get('/accionCorrectiva/(:num)', 'HzaccioncorrectivaController::accionCorrectiva/$1');
$routes->get('/generatePdf_accionCorrectiva', 'HzaccioncorrectivaController::generatePdf_accionCorrectiva');
$routes->get('/pausasActivas/(:num)', 'HzpausaactivaController::pausasActivas/$1');
$routes->get('/generatePdf_pausasActivas', 'HzpausaactivaController::generatePdf_pausasActivas');
$routes->get('/requisitosLegales/(:num)', 'HzreqlegalesController::requisitosLegales/$1');
$routes->get('/generatePdf_requisitosLegales', 'HzreqlegalesController::generatePdf_requisitosLegales');
$routes->get('/actaCocolab/(:num)', 'PzactacocolabController::actaCocolab/$1');
$routes->get('/generatePdf_actaCocolab', 'PzactacocolabController::generatePdf_actaCocolab');
$routes->get('/procedimientoAuditoria/(:num)', 'HzauditoriaController::procedimientoAuditoria/$1');
$routes->get('/generatePdf_procedimientoAuditoria', 'HzauditoriaController::generatePdf_procedimientoAuditoria');



$routes->get('/listVigias', 'VigiaController::listVigias');
$routes->get('/addVigia', 'VigiaController::addVigia');
$routes->post('/saveVigia', 'VigiaController::saveVigia');
$routes->get('/editVigia/(:num)', 'VigiaController::editVigia/$1');
$routes->post('/updateVigia/(:num)', 'VigiaController::updateVigia/$1');
$routes->get('/deleteVigia/(:num)', 'VigiaController::deleteVigia/$1');


/* *********************KPI´S ****************************************/

$routes->get('/listKpiTypes', 'KpiTypeController::listKpiTypes');
$routes->get('/addKpiType', 'KpiTypeController::addKpiType');
$routes->post('/addKpiTypePost', 'KpiTypeController::addKpiTypePost');
$routes->get('/editKpiType/(:num)', 'KpiTypeController::editKpiType/$1');
$routes->post('/editKpiTypePost/(:num)', 'KpiTypeController::editKpiTypePost/$1');
$routes->get('/deleteKpiType/(:num)', 'KpiTypeController::deleteKpiType/$1');

$routes->get('/listKpiPolicies', 'KpiPolicyController::listKpiPolicies');
$routes->get('/addKpiPolicy', 'KpiPolicyController::addKpiPolicy');
$routes->post('/addKpiPolicyPost', 'KpiPolicyController::addKpiPolicyPost');
$routes->get('/editKpiPolicy/(:num)', 'KpiPolicyController::editKpiPolicy/$1');
$routes->post('/editKpiPolicyPost/(:num)', 'KpiPolicyController::editKpiPolicyPost/$1');
$routes->get('/deleteKpiPolicy/(:num)', 'KpiPolicyController::deleteKpiPolicy/$1');

$routes->get('/listObjectives', 'ObjectivesPolicyController::listObjectives');
$routes->get('/addObjective', 'ObjectivesPolicyController::addObjective');
$routes->post('/addObjectivePost', 'ObjectivesPolicyController::addObjectivePost');
$routes->get('/editObjective/(:num)', 'ObjectivesPolicyController::editObjective/$1');
$routes->post('/editObjectivePost/(:num)', 'ObjectivesPolicyController::editObjectivePost/$1');
$routes->get('/deleteObjective/(:num)', 'ObjectivesPolicyController::deleteObjective/$1');

$routes->get('/listKpiDefinitions', 'KpiDefinitionController::listKpiDefinitions');
$routes->get('/addKpiDefinition', 'KpiDefinitionController::addKpiDefinition');
$routes->post('/addKpiDefinitionPost', 'KpiDefinitionController::addKpiDefinitionPost');
$routes->get('/editKpiDefinition/(:num)', 'KpiDefinitionController::editKpiDefinition/$1');
$routes->post('/editKpiDefinitionPost/(:num)', 'KpiDefinitionController::editKpiDefinitionPost/$1');
$routes->get('/deleteKpiDefinition/(:num)', 'KpiDefinitionController::deleteKpiDefinition/$1');

$routes->get('/listDataOwners', 'DataOwnerController::listDataOwners');
$routes->get('/addDataOwner', 'DataOwnerController::addDataOwner');
$routes->post('/addDataOwnerPost', 'DataOwnerController::addDataOwnerPost');
$routes->get('/editDataOwner/(:num)', 'DataOwnerController::editDataOwner/$1');
$routes->post('/editDataOwnerPost/(:num)', 'DataOwnerController::editDataOwnerPost/$1');
$routes->get('/deleteDataOwner/(:num)', 'DataOwnerController::deleteDataOwner/$1');

$routes->get('/listNumeratorVariables', 'VariableNumeratorController::listNumeratorVariables');
$routes->get('/addNumeratorVariable', 'VariableNumeratorController::addNumeratorVariable');
$routes->post('/addNumeratorVariablePost', 'VariableNumeratorController::addNumeratorVariablePost');
$routes->get('/editNumeratorVariable/(:num)', 'VariableNumeratorController::editNumeratorVariable/$1');
$routes->post('/editNumeratorVariablePost/(:num)', 'VariableNumeratorController::editNumeratorVariablePost/$1');
$routes->get('/deleteNumeratorVariable/(:num)', 'VariableNumeratorController::deleteNumeratorVariable/$1');

$routes->get('/listKpis', 'KpisController::listKpis');
$routes->get('/addKpi', 'KpisController::addKpi');
$routes->post('/addKpiPost', 'KpisController::addKpiPost');
$routes->get('/editKpi/(:num)', 'KpisController::editKpi/$1');
$routes->post('/editKpiPost/(:num)', 'KpisController::editKpiPost/$1');
$routes->get('/deleteKpi/(:num)', 'KpisController::deleteKpi/$1');

$routes->get('/listDenominatorVariables', 'VariableDenominatorController::listDenominatorVariables');
$routes->get('/addDenominatorVariable', 'VariableDenominatorController::addDenominatorVariable');
$routes->post('/addDenominatorVariablePost', 'VariableDenominatorController::addDenominatorVariablePost');
$routes->get('/editDenominatorVariable/(:num)', 'VariableDenominatorController::editDenominatorVariable/$1');
$routes->post('/editDenominatorVariablePost/(:num)', 'VariableDenominatorController::editDenominatorVariablePost/$1');
$routes->get('/deleteDenominatorVariable/(:num)', 'VariableDenominatorController::deleteDenominatorVariable/$1');

$routes->get('/listMeasurementPeriods', 'MeasurementPeriodController::listMeasurementPeriods');
$routes->get('/addMeasurementPeriod', 'MeasurementPeriodController::addMeasurementPeriod');
$routes->post('/addMeasurementPeriodPost', 'MeasurementPeriodController::addMeasurementPeriodPost');
$routes->get('/editMeasurementPeriod/(:num)', 'MeasurementPeriodController::editMeasurementPeriod/$1');
$routes->post('/editMeasurementPeriodPost/(:num)', 'MeasurementPeriodController::editMeasurementPeriodPost/$1');
$routes->get('/deleteMeasurementPeriod/(:num)', 'MeasurementPeriodController::deleteMeasurementPeriod/$1');

$routes->get('/listClientKpis', 'ClientKpiController::listClientKpis');
$routes->get('/addClientKpi', 'ClientKpiController::addClientKpi');
$routes->post('/addClientKpiPost', 'ClientKpiController::addClientKpiPost');
$routes->get('/editClientKpi/(:num)', 'ClientKpiController::editClientKpi/$1');
$routes->post('/editClientKpiPost/(:num)', 'ClientKpiController::editClientKpiPost/$1');
$routes->get('/deleteClientKpi/(:num)', 'ClientKpiController::deleteClientKpi/$1');

$routes->get('/listClientKpisFull/(:num)', 'ClientKpiController::listClientKpisFull/$1');

$routes->get('/planDeTrabajoKpi/(:num)', 'kpiplandetrabajoController::plandetrabajoKpi/$1');
$routes->get('/indicadorTresPeriodos/(:num)', 'kpitresperiodosController::indicadorTresPeriodos/$1');
$routes->get('/indicadorcuatroPeriodos/(:num)', 'kpicuatroperiodosController::indicadorcuatroPeriodos/$1');
$routes->get('/indicadorseisPeriodos/(:num)', 'kpiseisperiodosController::indicadorseisPeriodos/$1');
$routes->get('/indicadordocePeriodos/(:num)', 'kpidoceperiodosController::indicadordocePeriodos/$1');
$routes->get('/indicadorAnual/(:num)', 'kpianualController::indicadorAnual/$1');
$routes->get('/mipvrdcKpi/(:num)', 'kpimipvrdcController::mipvrdcKpi/$1');
$routes->get('/gestionriesgoKpi/(:num)', 'kpigestionriesgoController::gestionriesgoKpi/$1');
$routes->get('/vigepidemiologicaKpi/(:num)', 'kpivigepidemiologicaController::vigepidemiologicaKpi/$1');
$routes->get('/evinicialKpi/(:num)', 'kpievinicialController::evinicialKpi/$1');
$routes->get('/accpreventivaKpi/(:num)', 'kpiaccpreventivaController::accpreventivaKpi/$1');
$routes->get('/cumplilegalKpi/(:num)', 'kpicumplilegalController::cumplilegalKpi/$1');
$routes->get('/capacitacionKpi/(:num)', 'kpicapacitacionController::capacitacionKpi/$1');
$routes->get('/estructuraKpi/(:num)', 'kpiestructuraController::estructuraKpi/$1');
$routes->get('/atelKpi/(:num)', 'kpatelController::atelKpi/$1');
$routes->get('/indicefrecuenciaKpi/(:num)', 'kpiindicefrecuenciaController::indicefrecuenciaKpi/$1');
$routes->get('/indiceseveridadKpi/(:num)', 'kpiindiceseveridadController::indiceseveridadKpi/$1');
$routes->get('/mortalidadKpi/(:num)', 'kpimortalidadController::mortalidadKpi/$1');
$routes->get('/prevalenciaKpi/(:num)', 'kpiprevalenciaController::prevalenciaKpi/$1');
$routes->get('/incidenciaKpi/(:num)', 'kpiincidenciaController::incidenciaKpi/$1');
$routes->get('/rehabilitacionKpi/(:num)', 'kprehabilitacionController::rehabilitacionKpi/$1');
$routes->get('/ausentismoKpi/(:num)', 'kpiausentismoController::ausentismoKpi/$1');
$routes->get('/todoslosKpi/(:num)', 'kpitodoslosobjetivosController::todoslosKpi/$1');

/* *******************************EVALUACION INICIAL***************************************** */

$routes->get('/listEvaluaciones', 'EvaluationController::listEvaluaciones');
$routes->get('/addEvaluacion', 'EvaluationController::addEvaluacion');
$routes->post('/addEvaluacionPost', 'EvaluationController::addEvaluacionPost');
$routes->get('/editEvaluacion/(:num)', 'EvaluationController::editEvaluacion/$1');
$routes->post('/editEvaluacionPost/(:num)', 'EvaluationController::editEvaluacionPost/$1');
$routes->get('/deleteEvaluacion/(:num)', 'EvaluationController::deleteEvaluacion/$1');

$routes->get('/listEvaluaciones/(:num)', 'ClientEvaluationController::listEvaluaciones/$1');


$routes->get('/listCapacitaciones', 'CapacitacionController::listCapacitaciones');
$routes->get('/addCapacitacion', 'CapacitacionController::addCapacitacion');
$routes->post('/addCapacitacionPost', 'CapacitacionController::addCapacitacionPost');
$routes->get('/editCapacitacion/(:num)', 'CapacitacionController::editCapacitacion/$1');
$routes->post('/editCapacitacionPost/(:num)', 'CapacitacionController::editCapacitacionPost/$1');
$routes->get('/deleteCapacitacion/(:num)', 'CapacitacionController::deleteCapacitacion/$1');


$routes->get('/listcronogCapacitacion', 'CronogcapacitacionController::listcronogCapacitacion');
$routes->get('/addcronogCapacitacion', 'CronogcapacitacionController::addcronogCapacitacion');
$routes->post('/addcronogCapacitacionPost', 'CronogcapacitacionController::addcronogCapacitacionPost');
$routes->get('/editcronogCapacitacion/(:num)', 'CronogcapacitacionController::editcronogCapacitacion/$1');
$routes->post('/editcronogCapacitacionPost/(:num)', 'CronogcapacitacionController::editcronogCapacitacionPost/$1');
$routes->get('/deletecronogCapacitacion/(:num)', 'CronogcapacitacionController::deletecronogCapacitacion/$1');

// Ruta para actualizar fecha programada por mes seleccionado (botones mensuales)
$routes->post('/cronogCapacitacion/updateDateByMonth', 'CronogcapacitacionController::updateDateByMonth');

// Ruta para obtener lista de clientes (modal de generar cronograma)
$routes->get('/cronogCapacitacion/getClients', 'CronogcapacitacionController::getClients');

// Ruta para obtener el contrato del cliente (AJAX)
$routes->get('/cronogCapacitacion/getClientContract', 'CronogcapacitacionController::getClientContract');

// Ruta para generar cronograma de capacitación automáticamente
$routes->post('/cronogCapacitacion/generate', 'CronogcapacitacionController::generate');

$routes->get('/listPlanDeTrabajoAnual', 'PlanDeTrabajoAnualController::listPlanDeTrabajoAnual');
$routes->get('/addPlanDeTrabajoAnual', 'PlanDeTrabajoAnualController::addPlanDeTrabajoAnual');
$routes->post('/addPlanDeTrabajoAnualPost', 'PlanDeTrabajoAnualController::addPlanDeTrabajoAnualPost');

$routes->get('/editPlanDeTrabajoAnual/(:num)', 'PlanDeTrabajoAnualController::editPlanDeTrabajoAnual/$1');
$routes->post('/editPlanDeTrabajoAnualPost/(:num)', 'PlanDeTrabajoAnualController::editPlanDeTrabajoAnualPost/$1');
$routes->get('/deletePlanDeTrabajoAnual/(:num)', 'PlanDeTrabajoAnualController::deletePlanDeTrabajoAnual/$1');


$routes->get('/listPendientes', 'PendientesController::listPendientes');
$routes->get('/addPendiente', 'PendientesController::addPendiente');
$routes->post('/addPendientePost', 'PendientesController::addPendientePost');
$routes->get('/editPendiente/(:num)', 'PendientesController::editPendiente/$1');
$routes->post('/editPendientePost/(:num)', 'PendientesController::editPendientePost/$1');
$routes->get('/deletePendiente/(:num)', 'PendientesController::deletePendiente/$1');

$routes->get('/listPendientesCliente/(:num)', 'ClientePendientesController::listPendientesCliente/$1');
$routes->get('/listCronogramasCliente/(:num)', 'CronogramaCapacitacionController::listCronogramasCliente/$1');
$routes->get('/listPlanTrabajoCliente/(:num)', 'ClientePlanTrabajoController::listPlanTrabajoCliente/$1');

$routes->get('/listMatricesCycloid', 'MatrizCycloidController::listMatricesCycloid');
$routes->get('/addMatrizCycloid', 'MatrizCycloidController::addMatrizCycloid');
$routes->post('/addMatrizCycloidPost', 'MatrizCycloidController::addMatrizCycloidPost');
$routes->get('/editMatrizCycloid/(:num)', 'MatrizCycloidController::editMatrizCycloid/$1');
$routes->post('/editMatrizCycloidPost/(:num)', 'MatrizCycloidController::editMatrizCycloidPost/$1');
$routes->get('/deleteMatrizCycloid/(:num)', 'MatrizCycloidController::deleteMatrizCycloid/$1');




$routes->get('lookerstudio/list', 'LookerStudioController::list');
$routes->get('lookerstudio/add', 'LookerStudioController::add');
$routes->post('lookerstudio/addPost', 'LookerStudioController::addPost');
$routes->get('lookerstudio/edit/(:num)', 'LookerStudioController::edit/$1');
$routes->post('lookerstudio/editPost/(:num)', 'LookerStudioController::editPost/$1');
$routes->get('lookerstudio/delete/(:num)', 'LookerStudioController::delete/$1');

$routes->get('/client/lista-lookerstudio', 'ClientLookerStudioController::index');

$routes->get('matrices/list', 'MatricesController::list');
$routes->get('matrices/add', 'MatricesController::add');
$routes->post('matrices/addPost', 'MatricesController::addPost');
$routes->get('matrices/edit/(:num)', 'MatricesController::edit/$1');
$routes->post('matrices/editPost/(:num)', 'MatricesController::editPost/$1');
$routes->get('matrices/delete/(:num)', 'MatricesController::delete/$1');

$routes->get('/client/lista-matrices', 'ClientMatrices::index');


$routes->get('client/panel', 'ClientPanelController::showPanel');
$routes->get('client/panel/(:num)', 'ClientPanelController::showPanel/$1');

// Client Inspections (read-only web views)
$routes->group('client/inspecciones', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'ClientInspeccionesController::dashboard');
    $routes->get('actas-visita', 'ClientInspeccionesController::listActas');
    $routes->get('actas-visita/(:num)', 'ClientInspeccionesController::viewActa/$1');
    $routes->get('locativas', 'ClientInspeccionesController::listLocativas');
    $routes->get('locativas/(:num)', 'ClientInspeccionesController::viewLocativa/$1');
    $routes->get('senalizacion', 'ClientInspeccionesController::listSenalizacion');
    $routes->get('senalizacion/(:num)', 'ClientInspeccionesController::viewSenalizacion/$1');
    $routes->get('botiquin', 'ClientInspeccionesController::listBotiquin');
    $routes->get('botiquin/(:num)', 'ClientInspeccionesController::viewBotiquin/$1');
    $routes->get('extintores', 'ClientInspeccionesController::listExtintores');
    $routes->get('extintores/(:num)', 'ClientInspeccionesController::viewExtintores/$1');
    $routes->get('comunicaciones', 'ClientInspeccionesController::listComunicaciones');
    $routes->get('comunicaciones/(:num)', 'ClientInspeccionesController::viewComunicacion/$1');
    $routes->get('gabinetes', 'ClientInspeccionesController::listGabinetes');
    $routes->get('gabinetes/(:num)', 'ClientInspeccionesController::viewGabinete/$1');
    $routes->get('carta-vigia', 'ClientInspeccionesController::listCartasVigia');
    $routes->get('mantenimientos', 'ClientInspeccionesController::listMantenimientos');
    $routes->get('matriz-vulnerabilidad', 'ClientInspeccionesController::listMatrizVulnerabilidad');
    $routes->get('matriz-vulnerabilidad/(:num)', 'ClientInspeccionesController::viewMatrizVulnerabilidad/$1');
    $routes->get('probabilidad-peligros', 'ClientInspeccionesController::listProbabilidadPeligros');
    $routes->get('probabilidad-peligros/(:num)', 'ClientInspeccionesController::viewProbabilidadPeligros/$1');
    $routes->get('recursos-seguridad', 'ClientInspeccionesController::listRecursosSeguridad');
    $routes->get('recursos-seguridad/(:num)', 'ClientInspeccionesController::viewRecursosSeguridad/$1');
    $routes->get('hv-brigadista', 'ClientInspeccionesController::listHvBrigadista');
    $routes->get('hv-brigadista/(:num)', 'ClientInspeccionesController::viewHvBrigadista/$1');
    $routes->get('plan-emergencia', 'ClientInspeccionesController::listPlanEmergencia');
    $routes->get('plan-emergencia/(:num)', 'ClientInspeccionesController::viewPlanEmergencia/$1');
    $routes->get('simulacro', 'ClientInspeccionesController::listSimulacro');
    $routes->get('simulacro/(:num)', 'ClientInspeccionesController::viewSimulacro/$1');
    $routes->get('limpieza-desinfeccion', 'ClientInspeccionesController::listLimpieza');
    $routes->get('limpieza-desinfeccion/(:num)', 'ClientInspeccionesController::viewLimpieza/$1');
    $routes->get('dotacion-vigilante', 'ClientInspeccionesController::listDotacionVigilante');
    $routes->get('dotacion-vigilante/(:num)', 'ClientInspeccionesController::viewDotacionVigilante/$1');
    $routes->get('dotacion-aseadora', 'ClientInspeccionesController::listDotacionAseadora');
    $routes->get('dotacion-aseadora/(:num)', 'ClientInspeccionesController::viewDotacionAseadora/$1');
    $routes->get('dotacion-todero', 'ClientInspeccionesController::listDotacionTodero');
    $routes->get('dotacion-todero/(:num)', 'ClientInspeccionesController::viewDotacionTodero/$1');
    $routes->get('auditoria-zona-residuos', 'ClientInspeccionesController::listAuditoriaResiduos');
    $routes->get('auditoria-zona-residuos/(:num)', 'ClientInspeccionesController::viewAuditoriaResiduos/$1');
    $routes->get('asistencia-induccion', 'ClientInspeccionesController::listAsistenciaInduccion');
    $routes->get('asistencia-induccion/(:num)', 'ClientInspeccionesController::viewAsistenciaInduccion/$1');
    $routes->get('reporte-capacitacion', 'ClientInspeccionesController::listReporteCapacitacion');
    $routes->get('reporte-capacitacion/(:num)', 'ClientInspeccionesController::viewReporteCapacitacion/$1');
    $routes->get('preparacion-simulacro', 'ClientInspeccionesController::listPreparacionSimulacro');
    $routes->get('preparacion-simulacro/(:num)', 'ClientInspeccionesController::viewPreparacionSimulacro/$1');
    $routes->get('residuos-solidos', 'ClientInspeccionesController::listResiduos');
    $routes->get('residuos-solidos/(:num)', 'ClientInspeccionesController::viewResiduos/$1');
    $routes->get('control-plagas', 'ClientInspeccionesController::listPlagas');
    $routes->get('control-plagas/(:num)', 'ClientInspeccionesController::viewPlagas/$1');
    $routes->get('agua-potable', 'ClientInspeccionesController::listAguaPotable');
    $routes->get('agua-potable/(:num)', 'ClientInspeccionesController::viewAguaPotable/$1');
    $routes->get('plan-saneamiento', 'ClientInspeccionesController::listSaneamiento');
    $routes->get('plan-saneamiento/(:num)', 'ClientInspeccionesController::viewSaneamiento/$1');
    $routes->get('kpi-limpieza', 'ClientInspeccionesController::listKpiLimpieza');
    $routes->get('kpi-limpieza/(:num)', 'ClientInspeccionesController::viewKpiLimpieza/$1');
    $routes->get('kpi-residuos', 'ClientInspeccionesController::listKpiResiduos');
    $routes->get('kpi-residuos/(:num)', 'ClientInspeccionesController::viewKpiResiduos/$1');
    $routes->get('kpi-plagas', 'ClientInspeccionesController::listKpiPlagas');
    $routes->get('kpi-plagas/(:num)', 'ClientInspeccionesController::viewKpiPlagas/$1');
    $routes->get('kpi-agua-potable', 'ClientInspeccionesController::listKpiAguaPotable');
    $routes->get('kpi-agua-potable/(:num)', 'ClientInspeccionesController::viewKpiAguaPotable/$1');
});

$routes->get('/detailreportlist', 'DetailReportController::detailReportList');
$routes->get('/detailreportadd', 'DetailReportController::detailReportAdd');
$routes->post('/detailreportadd', 'DetailReportController::detailReportAddPost');
$routes->get('/detailreportedit/(:num)', 'DetailReportController::detailReportEdit/$1');
$routes->post('/detailreportedit', 'DetailReportController::detailReportEditPost');
$routes->get('/detailreportdelete/(:num)', 'DetailReportController::detailReportDelete/$1');


$routes->post('/updatePlanDeTrabajo', 'PlanDeTrabajoAnualController::updatePlanDeTrabajo');

// Rutas en app/Config/Routes.php
$routes->get('/listinventarioactividades', 'InventarioActividadesController::listinventarioactividades');
$routes->get('/addinventarioactividades', 'InventarioActividadesController::addinventarioactividades');
$routes->post('/addinventarioactividades', 'InventarioActividadesController::addpostinventarioactividades');
$routes->get('/editinventarioactividades/(:num)', 'InventarioActividadesController::editinventarioactividades/$1');
$routes->post('/editinventarioactividades/(:num)', 'InventarioActividadesController::editpostinventarioactividades/$1');
$routes->get('/deleteinventarioactividades/(:num)', 'InventarioActividadesController::deleteinventarioactividades/$1');

$routes->get('consultant/plan', 'PlanController::index'); // Ruta para mostrar la vista
$routes->post('consultant/plan/upload', 'PlanController::upload'); // Ruta para procesar la carga
$routes->get('consultant/plan/getClients', 'PlanController::getClients'); // Obtener lista de clientes para el modal
$routes->post('consultant/plan/generate', 'PlanController::generate'); // Generar plan de trabajo automáticamente

$routes->get('/nuevoListPlanTrabajoCliente/(:num)', 'NuevoClientePlanTrabajoController::nuevoListPlanTrabajoCliente/$1');

$routes->post('/updatecronogCapacitacion', 'CronogcapacitacionController::updatecronogCapacitacion');

$routes->get('consultant/csvcronogramadecapacitacion', 'CsvCronogramaDeCapacitacion::index');
$routes->post('consultant/csvcronogramadecapacitacion/upload', 'CsvCronogramaDeCapacitacion::upload');

$routes->post('updateEvaluacion', 'EvaluationController::updateEvaluacion');

$routes->post('updatePendiente', 'PendientesController::updatePendiente');

$routes->get('consultant/csvpendientes', 'CsvPendientes::index');
$routes->post('consultant/csvpendientes/upload', 'CsvPendientes::upload');


$routes->get('consultant/csvevaluacioninicial', 'CsvEvaluacionInicial::index');
$routes->post('consultant/csvevaluacioninicial/upload', 'CsvEvaluacionInicial::upload');

// ELIMINADO: Carga CSV de políticas - Ahora se asignan automáticamente desde librería estática
// $routes->get('consultant/csvpoliticasparadocumentos', 'csvpoliticasparadocumentosController::index');
// $routes->post('consultant/csvpoliticasparadocumentos/upload', 'csvpoliticasparadocumentosController::upload');

// ELIMINADO: Carga CSV de versiones de documentos - Ahora se asignan automáticamente desde librería estática
// $routes->get('consultant/csvversionesdocumentos', 'csvversionesdocumentosController::index');
// $routes->post('consultant/csvversionesdocumentos/upload', 'csvversionesdocumentosController::upload');

$routes->get('consultant/csvkpisempresas', 'csvkpiempresasController::index');
$routes->post('consultant/csvkpisempresas/upload', 'csvkpiempresasController::upload');

$routes->get('consultant/listitemdashboard', 'AdminlistdashboardController::listitemdashboard');
$routes->get('consultant/additemdashboard', 'AdminlistdashboardController::additemdashboard');
$routes->post('consultant/additemdashboardpost', 'AdminlistdashboardController::additemdashboardpost');
$routes->get('consultant/edititemdashboar/(:num)', 'AdminlistdashboardController::edititemdashboar/$1');
$routes->post('consultant/editpostitemdashboar/(:num)', 'AdminlistdashboardController::editpostitemdashboar/$1');
$routes->get('consultant/deleteitemdashboard/(:num)', 'AdminlistdashboardController::deleteitemdashboard/$1');

$routes->get('admin/dashboard', 'CustomDashboardController::index');

$routes->get('/accesosseguncliente/list', 'AccesossegunclienteController::listaccesosseguncliente');
$routes->get('/accesosseguncliente/add', 'AccesossegunclienteController::addaccesosseguncliente');
$routes->post('/accesosseguncliente/add', 'AccesossegunclienteController::addpostaccesosseguncliente');
$routes->get('/accesosseguncliente/edit/(:num)', 'AccesossegunclienteController::editaccesosseguncliente/$1');
$routes->post('/accesosseguncliente/edit', 'AccesossegunclienteController::editpostaccesosseguncliente');
$routes->get('/accesosseguncliente/delete/(:num)', 'AccesossegunclienteController::deleteaccesosseguncliente/$1');

$routes->get('/estandarcontractual/list', 'EstandarcontractualController::listestandarcontractual');
$routes->get('/estandarcontractual/add', 'EstandarcontractualController::addestandarcontractual');
$routes->post('/estandarcontractual/add', 'EstandarcontractualController::addpostestandarcontractual');
$routes->get('/estandarcontractual/edit/(:num)', 'EstandarcontractualController::editestandarcontractual/$1');
$routes->post('/estandarcontractual/edit', 'EstandarcontractualController::editpostestandarcontractual');
$routes->get('/estandarcontractual/delete/(:num)', 'EstandarcontractualController::deleteestandarcontractual/$1');

$routes->get('/accesosseguncontractualidad/list', 'AccesosseguncontractualidadController::listaccesosseguncontractualidad');
$routes->get('/accesosseguncontractualidad/add', 'AccesosseguncontractualidadController::addaccesosseguncontractualidad');
$routes->post('/accesosseguncontractualidad/add', 'AccesosseguncontractualidadController::addpostaccesosseguncontractualidad');
$routes->get('/accesosseguncontractualidad/edit/(:num)', 'AccesosseguncontractualidadController::editaccesosseguncontractualidad/$1');
$routes->post('/accesosseguncontractualidad/edit', 'AccesosseguncontractualidadController::editpostaccesosseguncontractualidad');
$routes->get('/accesosseguncontractualidad/delete/(:num)', 'AccesosseguncontractualidadController::deleteaccesosseguncontractualidad/$1');




$routes->post('/recalcularConteoDias', 'PendientesController::recalcularConteoDias');

$routes->get('mantenimientos', 'MantenimientoController::findAll');
$routes->get('mantenimientos/add', 'MantenimientoController::addMantenimientoController');
$routes->post('mantenimientos/addpost', 'MantenimientoController::addPostMantenimientoController');
$routes->get('mantenimientos/edit/(:num)', 'MantenimientoController::editMantenimientoController/$1');
$routes->post('mantenimientos/editpost/(:num)', 'MantenimientoController::editPostMantenimientoController/$1');
$routes->get('mantenimientos/delete/(:num)', 'MantenimientoController::deleteMantenimientoController/$1');



// app/Config/Routes.php

$routes->get('vencimientos', 'VencimientosMantenimientoController::listVencimientosMantenimiento');
$routes->get('vencimientos/add', 'VencimientosMantenimientoController::addVencimientosMantenimiento');
$routes->post('vencimientos/addpost', 'VencimientosMantenimientoController::addpostVencimientosMantenimiento');
$routes->get('vencimientos/edit/(:num)', 'VencimientosMantenimientoController::editVencimientosMantenimiento/$1');
$routes->post('vencimientos/editpost/(:num)', 'VencimientosMantenimientoController::editpostVencimientosMantenimiento/$1');

$routes->get('vencimientos/delete/(:num)', 'VencimientosMantenimientoController::deleteVencimientosMantenimiento/$1');


$routes->get('cron/send-emails', 'VencimientosMantenimientoController::sendEmailsAutomatically');

$routes->get('vencimientos/testEmailForVencimiento/(:num)', 'VencimientosMantenimientoController::testEmailForVencimiento/$1');
$routes->get('vencimientos/send-emails', 'VencimientosMantenimientoController::sendEmailsForUpcomingVencimientos');
$routes->post('vencimientos/send-selected-emails', 'VencimientosMantenimientoController::sendSelectedEmails');


$routes->get('/listVencimientosCliente/(:num)', 'VencimientosClienteController::listVencimientosCliente/$1');

// Rutas API para operaciones vía AJAX
$routes->get('api/getClientes', 'PlanDeTrabajoAnualController::getClientes');
$routes->get('api/getActividadesAjax', 'PlanDeTrabajoAnualController::getActividadesAjax');
$routes->post('api/updatePlanDeTrabajo', 'PlanDeTrabajoAnualController::updatePlanDeTrabajo');
$routes->get('listPlanDeTrabajoAnualAjax', 'PlanDeTrabajoAnualController::listPlanDeTrabajoAnualAjax');




$routes->get('api/getClientes', 'EvaluationController::getClientes');
$routes->get('api/getEvaluaciones', 'EvaluationController::getEvaluaciones');
$routes->get('api/getClientIndicators', 'EvaluationController::getClientIndicators');
$routes->post('api/updateEvaluacion', 'EvaluationController::updateEvaluacion');
$routes->get('listEvaluacionesAjax', 'EvaluationController::listEvaluacionesAjax');
$routes->post('api/resetCicloPHVA', 'EvaluationController::resetCicloPHVA');
$routes->get('api/getClientesParaReseteo', 'EvaluationController::getClientesParaReseteo');

$routes->get('api/getClientes', 'CronogcapacitacionController::getClientes');
$routes->get('api/getCronogramasAjax', 'CronogcapacitacionController::getCronogramasAjax');
$routes->post('api/updatecronogCapacitacion', 'CronogcapacitacionController::updatecronogCapacitacion');
$routes->get('listcronogCapacitacionAjax', 'CronogcapacitacionController::listcronogCapacitacionAjax');

$routes->get('api/getClientes', 'PendientesController::getClientes');
$routes->get('api/getPendientesAjax', 'PendientesController::getPendientesAjax');
$routes->post('api/updatePendiente', 'PendientesController::updatePendiente');
$routes->get('listPendientesAjax', 'PendientesController::listPendientesAjax');

$routes->get('consultor/dashboard', 'ConsultorTablaItemsController::index');
$routes->get('consultant/dashboard', 'ConsultantDashboardController::index');


// Define new routes for PlanTrabajoAnualidad
$routes->get('/plantrabajoanualidad', 'PlanTrabajoAnualidadController::index');
$routes->get('/plantrabajoanualidad/getConsultationData', 'PlanTrabajoAnualidadController::getConsultationData');




// Vista de listado (ya existente)
$routes->get('/pta-cliente-nueva/list', 'PtaClienteNuevaController::listPtaClienteNuevaModel');

// Rutas para Agregar Registro
$routes->get('/pta-cliente-nueva/add', 'PtaClienteNuevaController::addPtaClienteNuevaModel');
$routes->post('/pta-cliente-nueva/addpost', 'PtaClienteNuevaController::addpostPtaClienteNuevaModel');

// Rutas para Editar Registro
$routes->get('/pta-cliente-nueva/edit/(:num)', 'PtaClienteNuevaController::editPtaClienteNuevaModel/$1');
$routes->post('/pta-cliente-nueva/editpost/(:num)', 'PtaClienteNuevaController::editpostPtaClienteNuevaModel/$1');

// Ruta para edición inline (ya definida)
$routes->post('/pta-cliente-nueva/editinginline', 'PtaClienteNuevaController::editinginlinePtaClienteNuevaModel');

// Ruta para exportar a Excel (CSV)
$routes->get('/pta-cliente-nueva/excel', 'PtaClienteNuevaController::exportExcelPtaClienteNuevaModel');
$routes->get('/pta-cliente-nueva/delete/(:num)', 'PtaClienteNuevaController::deletePtaClienteNuevaModel/$1');

// Ruta para actualizar registros cerrados
$routes->post('/pta-cliente-nueva/updateCerradas', 'PtaClienteNuevaController::updateCerradas');

// Ruta para actualizar fecha por mes seleccionado (botones mensuales)
$routes->post('/pta-cliente-nueva/updateDateByMonth', 'PtaClienteNuevaController::updateDateByMonth');

$routes->get('consultant/actualizar_pta_cliente', 'CsvUploadController::index'); // Carga la vista
$routes->post('csv/upload', 'CsvUploadController::upload'); // Procesa el CSV

$routes->post('api/getCronogramasAjax', 'CronogramaCapacitacionController::getCronogramasAjax');

$routes->post('api/recalcularConteoDias', 'PendientesController::recalcularConteoDias');

// ============================================================================
// RUTAS DE GESTIÓN DE CONTRATOS
// ============================================================================

// Listado y dashboard de contratos
$routes->get('/contracts', 'ContractController::index');
$routes->get('/contracts/alerts', 'ContractController::alerts');

// Ver contrato individual
$routes->get('/contracts/view/(:num)', 'ContractController::view/$1');

// Crear nuevo contrato
$routes->get('/contracts/create', 'ContractController::create');
$routes->get('/contracts/create/(:num)', 'ContractController::create/$1');
$routes->post('/contracts/store', 'ContractController::store');

// Renovar contrato
$routes->get('/contracts/renew/(:num)', 'ContractController::renew/$1');
$routes->post('/contracts/process-renewal', 'ContractController::processRenewal');

// Cancelar contrato
$routes->get('/contracts/cancel/(:num)', 'ContractController::cancel/$1');
$routes->post('/contracts/cancel/(:num)', 'ContractController::cancel/$1');

// Historial de contratos por cliente
$routes->get('/contracts/client-history/(:num)', 'ContractController::clientHistory/$1');

// Mantenimiento automático (cron job)
$routes->get('/contracts/maintenance', 'ContractController::maintenance');

// Reporte semanal de contratos vencidos y próximos a vencer (cron job - lunes)
$routes->get('/contracts/weekly-report', 'ContractController::sendWeeklyContractReport');

// API endpoints
$routes->get('/api/contracts/active/(:num)', 'ContractController::getActiveContract/$1');
$routes->get('/api/contracts/stats', 'ContractController::getStats');

// Generación de contratos en PDF
$routes->get('/contracts/edit-contract-data/(:num)', 'ContractController::editContractData/$1');
$routes->post('/contracts/save-and-generate/(:num)', 'ContractController::saveAndGeneratePDF/$1');
$routes->get('/contracts/download-pdf/(:num)', 'ContractController::downloadPDF/$1');

// Generación de cláusula con IA
$routes->post('/contracts/generate-clausula-ia', 'ContractController::generateClausulaIA');
$routes->post('/contracts/generar-clausula-ia', 'ContractController::generarClausulaIA');
$routes->post('/contracts/generar-clausula1-ia', 'ContractController::generarClausula1IA');

// Descarga de documentación del contrato
$routes->get('/contracts/documentacion/(:num)', 'DocumentacionContratoController::previsualizarDocumentacion/$1');
$routes->get('/contracts/descargar-documentacion/(:num)', 'DocumentacionContratoController::descargarDocumentacion/$1');

// Descarga de documentación por cliente (desde reportList) - Nuevo flujo con selección de contrato/fechas
$routes->get('/contracts/seleccionar-documentacion/(:num)', 'DocumentacionContratoController::seleccionarDocumentacion/$1');
$routes->get('/contracts/filtrar-documentacion/(:num)', 'DocumentacionContratoController::filtrarDocumentacion/$1');
$routes->get('/contracts/descargar-filtrado/(:num)', 'DocumentacionContratoController::descargarFiltrado/$1');

// Rutas legacy (mantener compatibilidad)
$routes->get('/contracts/documentacion-cliente/(:num)', 'DocumentacionContratoController::seleccionarDocumentacion/$1');
$routes->get('/contracts/descargar-documentacion-cliente/(:num)', 'DocumentacionContratoController::descargarPorCliente/$1');

// ============================================================================
// RUTAS DE SOCIALIZACIÓN - DECRETO 1072 (Envío de emails)
// ============================================================================
$routes->post('/socializacion/send-plan-trabajo', 'SocializacionEmailController::sendPlanTrabajo');
$routes->post('/socializacion/send-cronograma-capacitaciones', 'SocializacionEmailController::sendCronogramaCapacitaciones');
$routes->post('/socializacion/send-evaluacion-estandares', 'SocializacionEmailController::sendEvaluacionEstandares');

// ============================================================================
// RUTAS DE GESTIÓN DE USUARIOS
// ============================================================================
$routes->get('/admin/users', 'UserController::listUsers');
$routes->get('/admin/users/add', 'UserController::addUser');
$routes->post('/admin/users/add', 'UserController::addUserPost');
$routes->get('/admin/users/edit/(:num)', 'UserController::editUser/$1');
$routes->post('/admin/users/edit/(:num)', 'UserController::editUserPost/$1');
$routes->get('/admin/users/delete/(:num)', 'UserController::deleteUser/$1');
$routes->get('/admin/users/toggle/(:num)', 'UserController::toggleStatus/$1');
$routes->get('/admin/users/reset-password/(:num)', 'UserController::resetPassword/$1');

// Ruta para vista de cuenta bloqueada
$routes->get('/auth/blocked', 'AuthController::blocked');

// ============================================================================
// RUTAS DE CONSUMO DE PLATAFORMA (TRACKING DE SESIONES)
// ============================================================================
$routes->get('/admin/usage', 'UsageController::index');
$routes->get('/admin/usage/user/(:num)', 'UsageController::userDetail/$1');
$routes->get('/admin/usage/export-csv', 'UsageController::exportCsv');
$routes->get('/admin/usage/chart-data', 'UsageController::chartData');

// ============================================================================
// RUTAS DE AUDITORÍA DEL PLAN DE TRABAJO ANUAL (PTA)
// ============================================================================
$routes->get('/audit-pta', 'AuditPtaController::index');
$routes->get('/audit-pta/view/(:num)', 'AuditPtaController::view/$1');
$routes->get('/audit-pta/history/(:num)', 'AuditPtaController::historyPta/$1');
$routes->get('/audit-pta/export', 'AuditPtaController::export');
$routes->get('/audit-pta/dashboard', 'AuditPtaController::dashboard');
$routes->get('/api/audit-pta/recent', 'AuditPtaController::apiRecentChanges');
$routes->get('/api/audit-pta/stats', 'AuditPtaController::apiStats');

// TRANSICIONES PTA (actividades que salieron de ABIERTA)
$routes->get('/pta-transiciones', 'PtaTransicionesController::index');
$routes->get('/pta-transiciones/export', 'PtaTransicionesController::export');

// Setup de tabla de auditoría (solo superadmin)
$routes->get('/setup-audit-table', 'SetupAuditTableController::index');
$routes->post('/setup-audit-table/create-local', 'SetupAuditTableController::createLocal');
$routes->post('/setup-audit-table/create-production', 'SetupAuditTableController::createProduction');
$routes->get('/setup-audit-table/check-status', 'SetupAuditTableController::checkStatus');

// ============================================================================
// RUTAS DE FIRMA DIGITAL DE CONTRATOS (Sistema 1)
// ============================================================================
$routes->post('/contracts/enviar-firma', 'ContractController::enviarFirma');
$routes->post('/contracts/regenerar-pdf-firmado', 'ContractController::regenerarPDFFirmado');
$routes->get('/contracts/estado-firma/(:num)', 'ContractController::estadoFirma/$1');
// Rutas públicas (sin autenticación) para firma de contratos
$routes->get('/contrato/firmar/(:segment)', 'ContractController::paginaFirmaContrato/$1');
$routes->post('/contrato/procesar-firma', 'ContractController::procesarFirmaContrato');
$routes->get('contrato/verificar/(:any)', 'ContractController::verificarFirma/$1');
$routes->get('contrato/certificado-pdf/(:num)', 'ContractController::certificadoPDF/$1');
$routes->post('/contracts/guardar-en-reportes/(:num)', 'ContractController::guardarEnReportes/$1');

// ============================================================================
// RUTAS DE FIRMA ELECTRÓNICA DE DOCUMENTOS SST (Sistema 2)
// ============================================================================
// Dashboard y gestión (requieren autenticación)
$routes->get('/firma/dashboard', 'FirmaElectronicaController::dashboard');
$routes->get('/firma/dashboard/(:num)', 'FirmaElectronicaController::dashboard/$1');
$routes->get('/firma/solicitar/(:num)', 'FirmaElectronicaController::solicitar/$1');
$routes->post('/firma/crear-solicitud', 'FirmaElectronicaController::crearSolicitud');
$routes->get('/firma/estado/(:num)', 'FirmaElectronicaController::estado/$1');
$routes->post('/firma/reenviar/(:num)', 'FirmaElectronicaController::reenviar/$1');
$routes->post('/firma/cancelar/(:num)', 'FirmaElectronicaController::cancelar/$1');
$routes->get('/firma/audit-log/(:num)', 'FirmaElectronicaController::auditLog/$1');
$routes->get('/firma/certificado-pdf/(:num)', 'FirmaElectronicaController::certificadoPDF/$1');
$routes->post('/firma/firmar-interno/(:num)', 'FirmaElectronicaController::firmarInterno/$1');
// Rutas públicas (sin autenticación) para firma electrónica
$routes->get('/firma/firmar/(:any)', 'FirmaElectronicaController::firmar/$1');
$routes->post('/firma/procesar', 'FirmaElectronicaController::procesarFirma');
$routes->get('/firma/confirmacion/(:any)', 'FirmaElectronicaController::confirmacion/$1');
$routes->get('/firma/verificar/(:any)', 'FirmaElectronicaController::verificar/$1');

// ============================================================================
// Módulo de Inspecciones SST (PWA)
// ============================================================================
$routes->group('inspecciones', ['namespace' => 'App\Controllers\Inspecciones', 'filter' => 'auth'], function($routes) {
    $routes->get('/', 'InspeccionesController::dashboard');

    // Acta de Visita
    $routes->get('acta-visita', 'ActaVisitaController::list');
    $routes->get('acta-visita/create', 'ActaVisitaController::create');
    $routes->get('acta-visita/create/(:num)', 'ActaVisitaController::create/$1');
    $routes->post('acta-visita/store', 'ActaVisitaController::store');
    $routes->get('acta-visita/edit/(:num)', 'ActaVisitaController::edit/$1');
    $routes->post('acta-visita/update/(:num)', 'ActaVisitaController::update/$1');
    $routes->get('acta-visita/view/(:num)', 'ActaVisitaController::view/$1');
    $routes->get('acta-visita/firma/(:num)', 'ActaVisitaController::firma/$1');
    $routes->post('acta-visita/save-firma/(:num)', 'ActaVisitaController::saveFirma/$1');
    $routes->get('acta-visita/pdf/(:num)', 'ActaVisitaController::generatePdf/$1');
    $routes->get('acta-visita/regenerar/(:num)', 'ActaVisitaController::regenerarPdf/$1');
    $routes->post('acta-visita/finalizar/(:num)', 'ActaVisitaController::finalizar/$1');
    $routes->get('acta-visita/delete/(:num)', 'ActaVisitaController::delete/$1');

    // Inspección Locativa
    $routes->get('inspeccion-locativa', 'InspeccionLocativaController::list');
    $routes->get('inspeccion-locativa/create', 'InspeccionLocativaController::create');
    $routes->get('inspeccion-locativa/create/(:num)', 'InspeccionLocativaController::create/$1');
    $routes->post('inspeccion-locativa/store', 'InspeccionLocativaController::store');
    $routes->get('inspeccion-locativa/edit/(:num)', 'InspeccionLocativaController::edit/$1');
    $routes->post('inspeccion-locativa/update/(:num)', 'InspeccionLocativaController::update/$1');
    $routes->get('inspeccion-locativa/view/(:num)', 'InspeccionLocativaController::view/$1');
    $routes->get('inspeccion-locativa/pdf/(:num)', 'InspeccionLocativaController::generatePdf/$1');
    $routes->get('inspeccion-locativa/regenerar/(:num)', 'InspeccionLocativaController::regenerarPdf/$1');
    $routes->post('inspeccion-locativa/finalizar/(:num)', 'InspeccionLocativaController::finalizar/$1');
    $routes->get('inspeccion-locativa/delete/(:num)', 'InspeccionLocativaController::delete/$1');

    // Inspección Señalización
    $routes->get('senalizacion', 'InspeccionSenalizacionController::list');
    $routes->get('senalizacion/create', 'InspeccionSenalizacionController::create');
    $routes->get('senalizacion/create/(:num)', 'InspeccionSenalizacionController::create/$1');
    $routes->post('senalizacion/store', 'InspeccionSenalizacionController::store');
    $routes->get('senalizacion/edit/(:num)', 'InspeccionSenalizacionController::edit/$1');
    $routes->post('senalizacion/update/(:num)', 'InspeccionSenalizacionController::update/$1');
    $routes->get('senalizacion/view/(:num)', 'InspeccionSenalizacionController::view/$1');
    $routes->get('senalizacion/pdf/(:num)', 'InspeccionSenalizacionController::generatePdf/$1');
    $routes->get('senalizacion/regenerar/(:num)', 'InspeccionSenalizacionController::regenerarPdf/$1');
    $routes->post('senalizacion/finalizar/(:num)', 'InspeccionSenalizacionController::finalizar/$1');
    $routes->get('senalizacion/delete/(:num)', 'InspeccionSenalizacionController::delete/$1');

    // Inspección Extintores
    $routes->get('extintores', 'InspeccionExtintoresController::list');
    $routes->get('extintores/create', 'InspeccionExtintoresController::create');
    $routes->get('extintores/create/(:num)', 'InspeccionExtintoresController::create/$1');
    $routes->post('extintores/store', 'InspeccionExtintoresController::store');
    $routes->get('extintores/edit/(:num)', 'InspeccionExtintoresController::edit/$1');
    $routes->post('extintores/update/(:num)', 'InspeccionExtintoresController::update/$1');
    $routes->get('extintores/view/(:num)', 'InspeccionExtintoresController::view/$1');
    $routes->get('extintores/pdf/(:num)', 'InspeccionExtintoresController::generatePdf/$1');
    $routes->get('extintores/regenerar/(:num)', 'InspeccionExtintoresController::regenerarPdf/$1');
    $routes->post('extintores/finalizar/(:num)', 'InspeccionExtintoresController::finalizar/$1');
    $routes->get('extintores/delete/(:num)', 'InspeccionExtintoresController::delete/$1');

    // Inspección Botiquín
    $routes->get('botiquin', 'InspeccionBotiquinController::list');
    $routes->get('botiquin/create', 'InspeccionBotiquinController::create');
    $routes->get('botiquin/create/(:num)', 'InspeccionBotiquinController::create/$1');
    $routes->post('botiquin/store', 'InspeccionBotiquinController::store');
    $routes->get('botiquin/edit/(:num)', 'InspeccionBotiquinController::edit/$1');
    $routes->post('botiquin/update/(:num)', 'InspeccionBotiquinController::update/$1');
    $routes->get('botiquin/view/(:num)', 'InspeccionBotiquinController::view/$1');
    $routes->get('botiquin/pdf/(:num)', 'InspeccionBotiquinController::generatePdf/$1');
    $routes->get('botiquin/regenerar/(:num)', 'InspeccionBotiquinController::regenerarPdf/$1');
    $routes->post('botiquin/finalizar/(:num)', 'InspeccionBotiquinController::finalizar/$1');
    $routes->get('botiquin/delete/(:num)', 'InspeccionBotiquinController::delete/$1');

    // Inspección Gabinetes
    $routes->get('gabinetes', 'InspeccionGabineteController::list');
    $routes->get('gabinetes/create', 'InspeccionGabineteController::create');
    $routes->get('gabinetes/create/(:num)', 'InspeccionGabineteController::create/$1');
    $routes->post('gabinetes/store', 'InspeccionGabineteController::store');
    $routes->get('gabinetes/edit/(:num)', 'InspeccionGabineteController::edit/$1');
    $routes->post('gabinetes/update/(:num)', 'InspeccionGabineteController::update/$1');
    $routes->get('gabinetes/view/(:num)', 'InspeccionGabineteController::view/$1');
    $routes->get('gabinetes/pdf/(:num)', 'InspeccionGabineteController::generatePdf/$1');
    $routes->get('gabinetes/regenerar/(:num)', 'InspeccionGabineteController::regenerarPdf/$1');
    $routes->post('gabinetes/finalizar/(:num)', 'InspeccionGabineteController::finalizar/$1');
    $routes->get('gabinetes/delete/(:num)', 'InspeccionGabineteController::delete/$1');

    // Inspección Comunicaciones
    $routes->get('comunicaciones', 'InspeccionComunicacionController::list');
    $routes->get('comunicaciones/create', 'InspeccionComunicacionController::create');
    $routes->get('comunicaciones/create/(:num)', 'InspeccionComunicacionController::create/$1');
    $routes->post('comunicaciones/store', 'InspeccionComunicacionController::store');
    $routes->get('comunicaciones/edit/(:num)', 'InspeccionComunicacionController::edit/$1');
    $routes->post('comunicaciones/update/(:num)', 'InspeccionComunicacionController::update/$1');
    $routes->get('comunicaciones/view/(:num)', 'InspeccionComunicacionController::view/$1');
    $routes->get('comunicaciones/pdf/(:num)', 'InspeccionComunicacionController::generatePdf/$1');
    $routes->get('comunicaciones/regenerar/(:num)', 'InspeccionComunicacionController::regenerarPdf/$1');
    $routes->post('comunicaciones/finalizar/(:num)', 'InspeccionComunicacionController::finalizar/$1');
    $routes->get('comunicaciones/delete/(:num)', 'InspeccionComunicacionController::delete/$1');

    // Inspección Recursos de Seguridad
    $routes->get('recursos-seguridad', 'InspeccionRecursosSeguridadController::list');
    $routes->get('recursos-seguridad/create', 'InspeccionRecursosSeguridadController::create');
    $routes->get('recursos-seguridad/create/(:num)', 'InspeccionRecursosSeguridadController::create/$1');
    $routes->post('recursos-seguridad/store', 'InspeccionRecursosSeguridadController::store');
    $routes->get('recursos-seguridad/edit/(:num)', 'InspeccionRecursosSeguridadController::edit/$1');
    $routes->post('recursos-seguridad/update/(:num)', 'InspeccionRecursosSeguridadController::update/$1');
    $routes->get('recursos-seguridad/view/(:num)', 'InspeccionRecursosSeguridadController::view/$1');
    $routes->get('recursos-seguridad/pdf/(:num)', 'InspeccionRecursosSeguridadController::generatePdf/$1');
    $routes->get('recursos-seguridad/regenerar/(:num)', 'InspeccionRecursosSeguridadController::regenerarPdf/$1');
    $routes->post('recursos-seguridad/finalizar/(:num)', 'InspeccionRecursosSeguridadController::finalizar/$1');
    $routes->get('recursos-seguridad/delete/(:num)', 'InspeccionRecursosSeguridadController::delete/$1');

    // Probabilidad de Ocurrencia de Peligros
    $routes->get('probabilidad-peligros', 'ProbabilidadPeligrosController::list');
    $routes->get('probabilidad-peligros/create', 'ProbabilidadPeligrosController::create');
    $routes->get('probabilidad-peligros/create/(:num)', 'ProbabilidadPeligrosController::create/$1');
    $routes->post('probabilidad-peligros/store', 'ProbabilidadPeligrosController::store');
    $routes->get('probabilidad-peligros/edit/(:num)', 'ProbabilidadPeligrosController::edit/$1');
    $routes->post('probabilidad-peligros/update/(:num)', 'ProbabilidadPeligrosController::update/$1');
    $routes->get('probabilidad-peligros/view/(:num)', 'ProbabilidadPeligrosController::view/$1');
    $routes->get('probabilidad-peligros/pdf/(:num)', 'ProbabilidadPeligrosController::generatePdf/$1');
    $routes->get('probabilidad-peligros/regenerar/(:num)', 'ProbabilidadPeligrosController::regenerarPdf/$1');
    $routes->post('probabilidad-peligros/finalizar/(:num)', 'ProbabilidadPeligrosController::finalizar/$1');
    $routes->get('probabilidad-peligros/delete/(:num)', 'ProbabilidadPeligrosController::delete/$1');

    // Matriz de Vulnerabilidad
    $routes->get('matriz-vulnerabilidad', 'MatrizVulnerabilidadController::list');
    $routes->get('matriz-vulnerabilidad/create', 'MatrizVulnerabilidadController::create');
    $routes->get('matriz-vulnerabilidad/create/(:num)', 'MatrizVulnerabilidadController::create/$1');
    $routes->post('matriz-vulnerabilidad/store', 'MatrizVulnerabilidadController::store');
    $routes->get('matriz-vulnerabilidad/edit/(:num)', 'MatrizVulnerabilidadController::edit/$1');
    $routes->post('matriz-vulnerabilidad/update/(:num)', 'MatrizVulnerabilidadController::update/$1');
    $routes->get('matriz-vulnerabilidad/view/(:num)', 'MatrizVulnerabilidadController::view/$1');
    $routes->get('matriz-vulnerabilidad/pdf/(:num)', 'MatrizVulnerabilidadController::generatePdf/$1');
    $routes->get('matriz-vulnerabilidad/regenerar/(:num)', 'MatrizVulnerabilidadController::regenerarPdf/$1');
    $routes->post('matriz-vulnerabilidad/finalizar/(:num)', 'MatrizVulnerabilidadController::finalizar/$1');
    $routes->get('matriz-vulnerabilidad/delete/(:num)', 'MatrizVulnerabilidadController::delete/$1');

    // Plan de Emergencia
    $routes->get('plan-emergencia', 'PlanEmergenciaController::list');
    $routes->get('plan-emergencia/create', 'PlanEmergenciaController::create');
    $routes->get('plan-emergencia/create/(:num)', 'PlanEmergenciaController::create/$1');
    $routes->post('plan-emergencia/store', 'PlanEmergenciaController::store');
    $routes->get('plan-emergencia/edit/(:num)', 'PlanEmergenciaController::edit/$1');
    $routes->post('plan-emergencia/update/(:num)', 'PlanEmergenciaController::update/$1');
    $routes->get('plan-emergencia/view/(:num)', 'PlanEmergenciaController::view/$1');
    $routes->get('plan-emergencia/pdf/(:num)', 'PlanEmergenciaController::generatePdf/$1');
    $routes->get('plan-emergencia/regenerar/(:num)', 'PlanEmergenciaController::regenerarPdf/$1');
    $routes->post('plan-emergencia/finalizar/(:num)', 'PlanEmergenciaController::finalizar/$1');
    $routes->get('plan-emergencia/delete/(:num)', 'PlanEmergenciaController::delete/$1');
    $routes->get('plan-emergencia/check-inspecciones/(:num)', 'PlanEmergenciaController::checkInspeccionesCompletas/$1');

    // Dotación Vigilante
    $routes->get('dotacion-vigilante', 'DotacionVigilanteController::list');
    $routes->get('dotacion-vigilante/create', 'DotacionVigilanteController::create');
    $routes->get('dotacion-vigilante/create/(:num)', 'DotacionVigilanteController::create/$1');
    $routes->post('dotacion-vigilante/store', 'DotacionVigilanteController::store');
    $routes->get('dotacion-vigilante/edit/(:num)', 'DotacionVigilanteController::edit/$1');
    $routes->post('dotacion-vigilante/update/(:num)', 'DotacionVigilanteController::update/$1');
    $routes->get('dotacion-vigilante/view/(:num)', 'DotacionVigilanteController::view/$1');
    $routes->get('dotacion-vigilante/pdf/(:num)', 'DotacionVigilanteController::generatePdf/$1');
    $routes->get('dotacion-vigilante/regenerar/(:num)', 'DotacionVigilanteController::regenerarPdf/$1');
    $routes->post('dotacion-vigilante/finalizar/(:num)', 'DotacionVigilanteController::finalizar/$1');
    $routes->get('dotacion-vigilante/delete/(:num)', 'DotacionVigilanteController::delete/$1');

    // Dotación Aseadora
    $routes->get('dotacion-aseadora', 'DotacionAseadoraController::list');
    $routes->get('dotacion-aseadora/create', 'DotacionAseadoraController::create');
    $routes->get('dotacion-aseadora/create/(:num)', 'DotacionAseadoraController::create/$1');
    $routes->post('dotacion-aseadora/store', 'DotacionAseadoraController::store');
    $routes->get('dotacion-aseadora/edit/(:num)', 'DotacionAseadoraController::edit/$1');
    $routes->post('dotacion-aseadora/update/(:num)', 'DotacionAseadoraController::update/$1');
    $routes->get('dotacion-aseadora/view/(:num)', 'DotacionAseadoraController::view/$1');
    $routes->get('dotacion-aseadora/pdf/(:num)', 'DotacionAseadoraController::generatePdf/$1');
    $routes->get('dotacion-aseadora/regenerar/(:num)', 'DotacionAseadoraController::regenerarPdf/$1');
    $routes->post('dotacion-aseadora/finalizar/(:num)', 'DotacionAseadoraController::finalizar/$1');
    $routes->get('dotacion-aseadora/delete/(:num)', 'DotacionAseadoraController::delete/$1');

    // Dotación Todero
    $routes->get('dotacion-todero', 'DotacionToderoController::list');
    $routes->get('dotacion-todero/create', 'DotacionToderoController::create');
    $routes->get('dotacion-todero/create/(:num)', 'DotacionToderoController::create/$1');
    $routes->post('dotacion-todero/store', 'DotacionToderoController::store');
    $routes->get('dotacion-todero/edit/(:num)', 'DotacionToderoController::edit/$1');
    $routes->post('dotacion-todero/update/(:num)', 'DotacionToderoController::update/$1');
    $routes->get('dotacion-todero/view/(:num)', 'DotacionToderoController::view/$1');
    $routes->get('dotacion-todero/pdf/(:num)', 'DotacionToderoController::generatePdf/$1');
    $routes->get('dotacion-todero/regenerar/(:num)', 'DotacionToderoController::regenerarPdf/$1');
    $routes->post('dotacion-todero/finalizar/(:num)', 'DotacionToderoController::finalizar/$1');
    $routes->get('dotacion-todero/delete/(:num)', 'DotacionToderoController::delete/$1');

    // Auditoría Zona de Residuos
    $routes->get('auditoria-zona-residuos', 'AuditoriaZonaResiduosController::list');
    $routes->get('auditoria-zona-residuos/create', 'AuditoriaZonaResiduosController::create');
    $routes->get('auditoria-zona-residuos/create/(:num)', 'AuditoriaZonaResiduosController::create/$1');
    $routes->post('auditoria-zona-residuos/store', 'AuditoriaZonaResiduosController::store');
    $routes->get('auditoria-zona-residuos/edit/(:num)', 'AuditoriaZonaResiduosController::edit/$1');
    $routes->post('auditoria-zona-residuos/update/(:num)', 'AuditoriaZonaResiduosController::update/$1');
    $routes->get('auditoria-zona-residuos/view/(:num)', 'AuditoriaZonaResiduosController::view/$1');
    $routes->get('auditoria-zona-residuos/pdf/(:num)', 'AuditoriaZonaResiduosController::generatePdf/$1');
    $routes->get('auditoria-zona-residuos/regenerar/(:num)', 'AuditoriaZonaResiduosController::regenerarPdf/$1');
    $routes->post('auditoria-zona-residuos/finalizar/(:num)', 'AuditoriaZonaResiduosController::finalizar/$1');
    $routes->get('auditoria-zona-residuos/delete/(:num)', 'AuditoriaZonaResiduosController::delete/$1');

    // Preparación Simulacro
    $routes->get('preparacion-simulacro', 'PreparacionSimulacroController::list');
    $routes->get('preparacion-simulacro/create', 'PreparacionSimulacroController::create');
    $routes->get('preparacion-simulacro/create/(:num)', 'PreparacionSimulacroController::create/$1');
    $routes->post('preparacion-simulacro/store', 'PreparacionSimulacroController::store');
    $routes->get('preparacion-simulacro/edit/(:num)', 'PreparacionSimulacroController::edit/$1');
    $routes->post('preparacion-simulacro/update/(:num)', 'PreparacionSimulacroController::update/$1');
    $routes->get('preparacion-simulacro/view/(:num)', 'PreparacionSimulacroController::view/$1');
    $routes->get('preparacion-simulacro/pdf/(:num)', 'PreparacionSimulacroController::generatePdf/$1');
    $routes->get('preparacion-simulacro/regenerar/(:num)', 'PreparacionSimulacroController::regenerarPdf/$1');
    $routes->post('preparacion-simulacro/finalizar/(:num)', 'PreparacionSimulacroController::finalizar/$1');
    $routes->get('preparacion-simulacro/delete/(:num)', 'PreparacionSimulacroController::delete/$1');

    // Evaluación Simulacro de Evacuación (admin)
    $routes->get('simulacro', 'EvaluacionSimulacroController::list');
    $routes->get('simulacro/view/(:num)', 'EvaluacionSimulacroController::view/$1');
    $routes->get('simulacro/edit/(:num)', 'EvaluacionSimulacroController::edit/$1');
    $routes->post('simulacro/update/(:num)', 'EvaluacionSimulacroController::update/$1');
    $routes->get('simulacro/pdf/(:num)', 'EvaluacionSimulacroController::generatePdf/$1');
    $routes->get('simulacro/regenerar/(:num)', 'EvaluacionSimulacroController::regenerarPdf/$1');
    $routes->post('simulacro/finalizar/(:num)', 'EvaluacionSimulacroController::finalizar/$1');
    $routes->get('simulacro/delete/(:num)', 'EvaluacionSimulacroController::delete/$1');

    // Hoja de Vida Brigadista (admin)
    $routes->get('hv-brigadista', 'HvBrigadistaController::list');
    $routes->get('hv-brigadista/view/(:num)', 'HvBrigadistaController::view/$1');
    $routes->get('hv-brigadista/edit/(:num)', 'HvBrigadistaController::edit/$1');
    $routes->post('hv-brigadista/update/(:num)', 'HvBrigadistaController::update/$1');
    $routes->get('hv-brigadista/pdf/(:num)', 'HvBrigadistaController::generatePdf/$1');
    $routes->get('hv-brigadista/regenerar/(:num)', 'HvBrigadistaController::regenerarPdf/$1');
    $routes->post('hv-brigadista/finalizar/(:num)', 'HvBrigadistaController::finalizar/$1');
    $routes->get('hv-brigadista/delete/(:num)', 'HvBrigadistaController::delete/$1');

    // Reporte de Capacitacion
    $routes->get('reporte-capacitacion', 'ReporteCapacitacionController::list');
    $routes->get('reporte-capacitacion/create', 'ReporteCapacitacionController::create');
    $routes->get('reporte-capacitacion/create/(:num)', 'ReporteCapacitacionController::create/$1');
    $routes->post('reporte-capacitacion/store', 'ReporteCapacitacionController::store');
    $routes->get('reporte-capacitacion/edit/(:num)', 'ReporteCapacitacionController::edit/$1');
    $routes->post('reporte-capacitacion/update/(:num)', 'ReporteCapacitacionController::update/$1');
    $routes->get('reporte-capacitacion/view/(:num)', 'ReporteCapacitacionController::view/$1');
    $routes->get('reporte-capacitacion/pdf/(:num)', 'ReporteCapacitacionController::generatePdf/$1');
    $routes->get('reporte-capacitacion/regenerar/(:num)', 'ReporteCapacitacionController::regenerarPdf/$1');
    $routes->post('reporte-capacitacion/finalizar/(:num)', 'ReporteCapacitacionController::finalizar/$1');
    $routes->get('reporte-capacitacion/delete/(:num)', 'ReporteCapacitacionController::delete/$1');

    // Gestión de Pendientes (compromisos)
    $routes->get('pendientes', 'PendientesPwaController::list');
    $routes->get('pendientes/cliente/(:num)', 'PendientesPwaController::list/$1');
    $routes->get('pendientes/create/(:num)', 'PendientesPwaController::create/$1');
    $routes->post('pendientes/store', 'PendientesPwaController::store');
    $routes->get('pendientes/edit/(:num)', 'PendientesPwaController::edit/$1');
    $routes->post('pendientes/update/(:num)', 'PendientesPwaController::update/$1');
    $routes->post('pendientes/estado/(:num)', 'PendientesPwaController::changeEstado/$1');
    $routes->get('pendientes/delete/(:num)', 'PendientesPwaController::delete/$1');

    // Gestión de Mantenimientos (vencimientos)
    $routes->get('mantenimientos', 'MantenimientosPwaController::list');
    $routes->get('mantenimientos/cliente/(:num)', 'MantenimientosPwaController::list/$1');
    $routes->get('mantenimientos/create/(:num)', 'MantenimientosPwaController::create/$1');
    $routes->post('mantenimientos/store', 'MantenimientosPwaController::store');
    $routes->get('mantenimientos/edit/(:num)', 'MantenimientosPwaController::edit/$1');
    $routes->post('mantenimientos/update/(:num)', 'MantenimientosPwaController::update/$1');
    $routes->post('mantenimientos/ejecutado/(:num)', 'MantenimientosPwaController::markEjecutado/$1');
    $routes->get('mantenimientos/delete/(:num)', 'MantenimientosPwaController::delete/$1');

    // Carta Vigía SST
    $routes->get('carta-vigia', 'CartaVigiaPwaController::list');
    $routes->get('carta-vigia/cliente/(:num)', 'CartaVigiaPwaController::list/$1');
    $routes->get('carta-vigia/create/(:num)', 'CartaVigiaPwaController::create/$1');
    $routes->post('carta-vigia/store', 'CartaVigiaPwaController::store');
    $routes->get('carta-vigia/edit/(:num)', 'CartaVigiaPwaController::edit/$1');
    $routes->post('carta-vigia/update/(:num)', 'CartaVigiaPwaController::update/$1');
    $routes->get('carta-vigia/delete/(:num)', 'CartaVigiaPwaController::delete/$1');
    $routes->post('carta-vigia/reenviar/(:num)', 'CartaVigiaPwaController::reenviar/$1');
    $routes->get('carta-vigia/pdf/(:num)', 'CartaVigiaPwaController::verPdf/$1');

    // Asistencia Induccion
    $routes->get('asistencia-induccion', 'AsistenciaInduccionController::list');
    $routes->get('asistencia-induccion/create', 'AsistenciaInduccionController::create');
    $routes->get('asistencia-induccion/create/(:num)', 'AsistenciaInduccionController::create/$1');
    $routes->post('asistencia-induccion/store', 'AsistenciaInduccionController::store');
    $routes->get('asistencia-induccion/edit/(:num)', 'AsistenciaInduccionController::edit/$1');
    $routes->post('asistencia-induccion/update/(:num)', 'AsistenciaInduccionController::update/$1');
    $routes->get('asistencia-induccion/view/(:num)', 'AsistenciaInduccionController::view/$1');
    $routes->get('asistencia-induccion/firmas/(:num)', 'AsistenciaInduccionController::firmas/$1');
    $routes->post('asistencia-induccion/guardar-firma/(:num)', 'AsistenciaInduccionController::guardarFirma/$1');
    $routes->get('asistencia-induccion/pdf/(:num)', 'AsistenciaInduccionController::generatePdf/$1');
    $routes->get('asistencia-induccion/regenerar/(:num)', 'AsistenciaInduccionController::regenerarPdf/$1');
    $routes->post('asistencia-induccion/finalizar/(:num)', 'AsistenciaInduccionController::finalizar/$1');
    $routes->get('asistencia-induccion/delete/(:num)', 'AsistenciaInduccionController::delete/$1');

    // Programa Limpieza y Desinfección
    $routes->get('limpieza-desinfeccion', 'ProgramaLimpiezaController::list');
    $routes->get('limpieza-desinfeccion/create', 'ProgramaLimpiezaController::create');
    $routes->get('limpieza-desinfeccion/create/(:num)', 'ProgramaLimpiezaController::create/$1');
    $routes->post('limpieza-desinfeccion/store', 'ProgramaLimpiezaController::store');
    $routes->get('limpieza-desinfeccion/edit/(:num)', 'ProgramaLimpiezaController::edit/$1');
    $routes->post('limpieza-desinfeccion/update/(:num)', 'ProgramaLimpiezaController::update/$1');
    $routes->get('limpieza-desinfeccion/view/(:num)', 'ProgramaLimpiezaController::view/$1');
    $routes->get('limpieza-desinfeccion/pdf/(:num)', 'ProgramaLimpiezaController::generatePdf/$1');
    $routes->get('limpieza-desinfeccion/regenerar/(:num)', 'ProgramaLimpiezaController::regenerarPdf/$1');
    $routes->post('limpieza-desinfeccion/finalizar/(:num)', 'ProgramaLimpiezaController::finalizar/$1');
    $routes->get('limpieza-desinfeccion/delete/(:num)', 'ProgramaLimpiezaController::delete/$1');

    // Programa Manejo Integral de Residuos Sólidos
    $routes->get('residuos-solidos', 'ProgramaResiduosController::list');
    $routes->get('residuos-solidos/create', 'ProgramaResiduosController::create');
    $routes->get('residuos-solidos/create/(:num)', 'ProgramaResiduosController::create/$1');
    $routes->post('residuos-solidos/store', 'ProgramaResiduosController::store');
    $routes->get('residuos-solidos/edit/(:num)', 'ProgramaResiduosController::edit/$1');
    $routes->post('residuos-solidos/update/(:num)', 'ProgramaResiduosController::update/$1');
    $routes->get('residuos-solidos/view/(:num)', 'ProgramaResiduosController::view/$1');
    $routes->get('residuos-solidos/pdf/(:num)', 'ProgramaResiduosController::generatePdf/$1');
    $routes->get('residuos-solidos/regenerar/(:num)', 'ProgramaResiduosController::regenerarPdf/$1');
    $routes->post('residuos-solidos/finalizar/(:num)', 'ProgramaResiduosController::finalizar/$1');
    $routes->get('residuos-solidos/delete/(:num)', 'ProgramaResiduosController::delete/$1');

    // Control Integrado de Plagas
    $routes->get('control-plagas', 'ProgramaPlagasController::list');
    $routes->get('control-plagas/create', 'ProgramaPlagasController::create');
    $routes->get('control-plagas/create/(:num)', 'ProgramaPlagasController::create/$1');
    $routes->post('control-plagas/store', 'ProgramaPlagasController::store');
    $routes->get('control-plagas/edit/(:num)', 'ProgramaPlagasController::edit/$1');
    $routes->post('control-plagas/update/(:num)', 'ProgramaPlagasController::update/$1');
    $routes->get('control-plagas/view/(:num)', 'ProgramaPlagasController::view/$1');
    $routes->get('control-plagas/pdf/(:num)', 'ProgramaPlagasController::generatePdf/$1');
    $routes->get('control-plagas/regenerar/(:num)', 'ProgramaPlagasController::regenerarPdf/$1');
    $routes->post('control-plagas/finalizar/(:num)', 'ProgramaPlagasController::finalizar/$1');
    $routes->get('control-plagas/delete/(:num)', 'ProgramaPlagasController::delete/$1');

    // Abastecimiento y Control de Agua Potable
    $routes->get('agua-potable', 'ProgramaAguaPotableController::list');
    $routes->get('agua-potable/create', 'ProgramaAguaPotableController::create');
    $routes->get('agua-potable/create/(:num)', 'ProgramaAguaPotableController::create/$1');
    $routes->post('agua-potable/store', 'ProgramaAguaPotableController::store');
    $routes->get('agua-potable/edit/(:num)', 'ProgramaAguaPotableController::edit/$1');
    $routes->post('agua-potable/update/(:num)', 'ProgramaAguaPotableController::update/$1');
    $routes->get('agua-potable/view/(:num)', 'ProgramaAguaPotableController::view/$1');
    $routes->get('agua-potable/pdf/(:num)', 'ProgramaAguaPotableController::generatePdf/$1');
    $routes->get('agua-potable/regenerar/(:num)', 'ProgramaAguaPotableController::regenerarPdf/$1');
    $routes->post('agua-potable/finalizar/(:num)', 'ProgramaAguaPotableController::finalizar/$1');
    $routes->get('agua-potable/delete/(:num)', 'ProgramaAguaPotableController::delete/$1');

    // Plan de Saneamiento Básico
    $routes->get('plan-saneamiento', 'PlanSaneamientoController::list');
    $routes->get('plan-saneamiento/create', 'PlanSaneamientoController::create');
    $routes->get('plan-saneamiento/create/(:num)', 'PlanSaneamientoController::create/$1');
    $routes->post('plan-saneamiento/store', 'PlanSaneamientoController::store');
    $routes->get('plan-saneamiento/edit/(:num)', 'PlanSaneamientoController::edit/$1');
    $routes->post('plan-saneamiento/update/(:num)', 'PlanSaneamientoController::update/$1');
    $routes->get('plan-saneamiento/view/(:num)', 'PlanSaneamientoController::view/$1');
    $routes->get('plan-saneamiento/pdf/(:num)', 'PlanSaneamientoController::generatePdf/$1');
    $routes->get('plan-saneamiento/regenerar/(:num)', 'PlanSaneamientoController::regenerarPdf/$1');
    $routes->post('plan-saneamiento/finalizar/(:num)', 'PlanSaneamientoController::finalizar/$1');
    $routes->get('plan-saneamiento/delete/(:num)', 'PlanSaneamientoController::delete/$1');

    // KPI Programa de Limpieza y Desinfección
    $routes->get('kpi-limpieza', 'KpiLimpiezaController::list');
    $routes->get('kpi-limpieza/create', 'KpiLimpiezaController::create');
    $routes->get('kpi-limpieza/create/(:num)', 'KpiLimpiezaController::create/$1');
    $routes->post('kpi-limpieza/store', 'KpiLimpiezaController::store');
    $routes->get('kpi-limpieza/edit/(:num)', 'KpiLimpiezaController::edit/$1');
    $routes->post('kpi-limpieza/update/(:num)', 'KpiLimpiezaController::update/$1');
    $routes->get('kpi-limpieza/view/(:num)', 'KpiLimpiezaController::view/$1');
    $routes->get('kpi-limpieza/pdf/(:num)', 'KpiLimpiezaController::generatePdf/$1');
    $routes->get('kpi-limpieza/regenerar/(:num)', 'KpiLimpiezaController::regenerarPdf/$1');
    $routes->post('kpi-limpieza/finalizar/(:num)', 'KpiLimpiezaController::finalizar/$1');
    $routes->get('kpi-limpieza/delete/(:num)', 'KpiLimpiezaController::delete/$1');

    // KPI Programa de Manejo Integral de Residuos Sólidos
    $routes->get('kpi-residuos', 'KpiResiduosController::list');
    $routes->get('kpi-residuos/create', 'KpiResiduosController::create');
    $routes->get('kpi-residuos/create/(:num)', 'KpiResiduosController::create/$1');
    $routes->post('kpi-residuos/store', 'KpiResiduosController::store');
    $routes->get('kpi-residuos/edit/(:num)', 'KpiResiduosController::edit/$1');
    $routes->post('kpi-residuos/update/(:num)', 'KpiResiduosController::update/$1');
    $routes->get('kpi-residuos/view/(:num)', 'KpiResiduosController::view/$1');
    $routes->get('kpi-residuos/pdf/(:num)', 'KpiResiduosController::generatePdf/$1');
    $routes->get('kpi-residuos/regenerar/(:num)', 'KpiResiduosController::regenerarPdf/$1');
    $routes->post('kpi-residuos/finalizar/(:num)', 'KpiResiduosController::finalizar/$1');
    $routes->get('kpi-residuos/delete/(:num)', 'KpiResiduosController::delete/$1');

    // KPI Programa de Control Integrado de Plagas
    $routes->get('kpi-plagas', 'KpiPlagasController::list');
    $routes->get('kpi-plagas/create', 'KpiPlagasController::create');
    $routes->get('kpi-plagas/create/(:num)', 'KpiPlagasController::create/$1');
    $routes->post('kpi-plagas/store', 'KpiPlagasController::store');
    $routes->get('kpi-plagas/edit/(:num)', 'KpiPlagasController::edit/$1');
    $routes->post('kpi-plagas/update/(:num)', 'KpiPlagasController::update/$1');
    $routes->get('kpi-plagas/view/(:num)', 'KpiPlagasController::view/$1');
    $routes->get('kpi-plagas/pdf/(:num)', 'KpiPlagasController::generatePdf/$1');
    $routes->get('kpi-plagas/regenerar/(:num)', 'KpiPlagasController::regenerarPdf/$1');
    $routes->post('kpi-plagas/finalizar/(:num)', 'KpiPlagasController::finalizar/$1');
    $routes->get('kpi-plagas/delete/(:num)', 'KpiPlagasController::delete/$1');

    // KPI Programa de Abastecimiento y Control de Agua Potable
    $routes->get('kpi-agua-potable', 'KpiAguaPotableController::list');
    $routes->get('kpi-agua-potable/create', 'KpiAguaPotableController::create');
    $routes->get('kpi-agua-potable/create/(:num)', 'KpiAguaPotableController::create/$1');
    $routes->post('kpi-agua-potable/store', 'KpiAguaPotableController::store');
    $routes->get('kpi-agua-potable/edit/(:num)', 'KpiAguaPotableController::edit/$1');
    $routes->post('kpi-agua-potable/update/(:num)', 'KpiAguaPotableController::update/$1');
    $routes->get('kpi-agua-potable/view/(:num)', 'KpiAguaPotableController::view/$1');
    $routes->get('kpi-agua-potable/pdf/(:num)', 'KpiAguaPotableController::generatePdf/$1');
    $routes->get('kpi-agua-potable/regenerar/(:num)', 'KpiAguaPotableController::regenerarPdf/$1');
    $routes->post('kpi-agua-potable/finalizar/(:num)', 'KpiAguaPotableController::finalizar/$1');
    $routes->get('kpi-agua-potable/delete/(:num)', 'KpiAguaPotableController::delete/$1');

    // Accesos Rápidos (URLs)
    $routes->get('urls', 'UrlsPwaController::list');
    $routes->get('urls/create', 'UrlsPwaController::create');
    $routes->post('urls/store', 'UrlsPwaController::store');
    $routes->get('urls/edit/(:num)', 'UrlsPwaController::edit/$1');
    $routes->post('urls/update/(:num)', 'UrlsPwaController::update/$1');
    $routes->get('urls/delete/(:num)', 'UrlsPwaController::delete/$1');

    // API endpoints AJAX
    $routes->get('api/clientes', 'InspeccionesController::getClientes');
    $routes->get('api/pendientes/(:num)', 'InspeccionesController::getPendientes/$1');
    $routes->get('api/mantenimientos/(:num)', 'InspeccionesController::getMantenimientos/$1');
    $routes->get('api/mantenimientos-catalog', 'MantenimientosPwaController::apiCatalog');
    $routes->post('api/mantenimientos-catalog', 'MantenimientosPwaController::apiAddCatalog');
    $routes->get('api/vencimientos/(:num)', 'MantenimientosPwaController::apiVencimientos/$1');
});

// Rutas públicas Carta Vigía (sin autenticación, patrón de firma contratos)
$routes->get('carta-vigia/firmar/(:any)', 'Inspecciones\CartaVigiaPwaController::firmar/$1');
$routes->post('carta-vigia/procesar-firma', 'Inspecciones\CartaVigiaPwaController::procesarFirma');
$routes->get('carta-vigia/verificar/(:any)', 'Inspecciones\CartaVigiaPwaController::verificar/$1');

// Evaluación Simulacro de Evacuación (público, sin autenticación)
$routes->get('simulacro', 'SimulacroPublicoController::form');
$routes->get('simulacro/api/clientes', 'SimulacroPublicoController::getClientesActivos');
$routes->post('simulacro/save-step', 'SimulacroPublicoController::saveStep');
$routes->post('simulacro/upload-foto', 'SimulacroPublicoController::uploadFoto');
$routes->post('simulacro/store', 'SimulacroPublicoController::store');

// Hoja de Vida Brigadista (público, sin autenticación)
$routes->get('hv-brigadista', 'HvBrigadistaPublicoController::form');
$routes->get('hv-brigadista/api/clientes', 'HvBrigadistaPublicoController::getClientesActivos');
$routes->post('hv-brigadista/store', 'HvBrigadistaPublicoController::store');

// Informe de Avances (panel admin consultor)
// Informe de Avances — Vistas web (requiere sesión)
$routes->group('informe-avances', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'InformeAvancesController::list');
    $routes->get('create', 'InformeAvancesController::create');
    $routes->get('create/(:num)', 'InformeAvancesController::create/$1');
    $routes->post('store', 'InformeAvancesController::store');
    $routes->get('edit/(:num)', 'InformeAvancesController::edit/$1');
    $routes->post('update/(:num)', 'InformeAvancesController::update/$1');
    $routes->get('view/(:num)', 'InformeAvancesController::view/$1');
    $routes->get('pdf/(:num)', 'InformeAvancesController::generatePdf/$1');
    $routes->post('finalizar/(:num)', 'InformeAvancesController::finalizar/$1');
    $routes->get('delete/(:num)', 'InformeAvancesController::delete/$1');
    $routes->post('generar-resumen', 'InformeAvancesController::generarResumen');
    $routes->get('api/metricas/(:num)', 'InformeAvancesController::calcularMetricas/$1');
    $routes->get('api/clientes', 'InformeAvancesController::getClientes');
    $routes->post('enviar/(:num)', 'InformeAvancesController::enviar/$1');
});

// Informe de Avances — API programática (OpenClaw, sesión OR API Key)
// Prefijo ext-api/ para evitar conflicto con $filters['auth']['api/*']
$routes->group('ext-api/informe-avances', ['filter' => 'authOrApiKey'], function($routes) {
    $routes->get('clientes', 'InformeAvancesController::getClientes');
    $routes->get('clientes-con-visita', 'InformeAvancesController::getClientesConVisita');
    $routes->get('metricas/(:num)', 'InformeAvancesController::calcularMetricas/$1');
    $routes->post('generar-resumen', 'InformeAvancesController::generarResumen');
    $routes->post('generar-y-enviar/(:num)', 'InformeAvancesController::apiGenerarYEnviar/$1');
    $routes->post('enviar/(:num)', 'InformeAvancesController::enviar/$1');
});

