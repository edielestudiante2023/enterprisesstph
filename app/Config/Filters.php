<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;
use App\Filters\AuthFilter;

class Filters extends BaseFilters
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array<string, class-string|list<class-string>>
     *
     * [filter_name => classname]
     * or [filter_name => [classname1, classname2, ...]]
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        'auth'          => AuthFilter::class,
    ];

    /**
     * List of special required filters.
     *
     * The filters listed here are special. They are applied before and after
     * other kinds of filters, and always applied even if a route does not exist.
     *
     * Filters set by default provide framework functionality. If removed,
     * those functions will no longer work.
     *
     * @see https://codeigniter.com/user_guide/incoming/filters.html#provided-filters
     *
     * @var array{before: list<string>, after: list<string>}
     */
    public array $required = [
        'before' => [
            'forcehttps', // Force Global Secure Requests
            'pagecache',  // Web Page Caching
        ],
        'after' => [
            'pagecache',   // Web Page Caching
            'performance', // Performance Metrics
            'toolbar',     // Debug Toolbar
        ],
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     *
     * @var array<string, array<string, array<string, string>>>|array<string, list<string>>
     */
    public array $globals = [
        'before' => [
            // 'honeypot',
            // 'csrf',
            // 'invalidchars',
        ],
        'after' => [
            // 'honeypot',
            // 'secureheaders',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'POST' => ['foo', 'bar']
     *
     * If you use this, you should disable auto-routing because auto-routing
     * permits any HTTP method to access a controller. Accessing the controller
     * with a method you don't expect could bypass the filter.
     *
     * @var array<string, list<string>>
     */
    public array $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     *
     * @var array<string, array<string, list<string>>>
     */
    public array $filters = [
        'auth' => [
            'before' => [
                'dashboard*',
                'dashboardclient*',
                'dashboardconsultant*',
                'admindashboard*',
                'client/*',
                'consultant/*',
                'admin/*',
                'addClient*',
                'editClient*',
                'deleteClient*',
                'listClients*',
                'addConsultant*',
                'editConsultant*',
                'deleteConsultant*',
                'listConsultants*',
                'addReport',  // Solo la vista GET, no addReportPost (usado por n8n)
                'editReport*',
                'deleteReport*',
                'reportList*',
                'addPolicy*',
                'editPolicy*',
                'deletePolicy*',
                'listPolicies*',
                'listPolicyTypes*',
                'addPolicyType*',
                'editPolicyType*',
                'deletePolicyType*',
                'viewDocuments*',
                'pdfUnificado*',
                'generarPdfUnificado*',
                'quick-access*',
                'listVigias*',
                'addVigia*',
                'editVigia*',
                'deleteVigia*',
                'listKpi*',
                'addKpi*',
                'editKpi*',
                'deleteKpi*',
                'listEvaluaciones*',
                'addEvaluacion*',
                'editEvaluacion*',
                'deleteEvaluacion*',
                'listCapacitaciones*',
                'addCapacitacion*',
                'editCapacitacion*',
                'deleteCapacitacion*',
                'listcronogCapacitacion*',
                'addcronogCapacitacion*',
                'editcronogCapacitacion*',
                'deletecronogCapacitacion*',
                'listPlanDeTrabajoAnual*',
                'addPlanDeTrabajoAnual*',
                'editPlanDeTrabajoAnual*',
                'deletePlanDeTrabajoAnual*',
                'listPendientes*',
                'addPendiente*',
                'editPendiente*',
                'deletePendiente*',
                'contracts',          // Listado de contratos (excluye /contracts/maintenance y /contracts/weekly-report para cron)
                'contracts/view/*',
                'contracts/create*',
                'contracts/store*',
                'contracts/renew/*',
                'contracts/process-renewal*',
                'contracts/cancel/*',
                'contracts/client-history/*',
                'contracts/alerts*',
                'contracts/edit-contract-data/*',
                'contracts/save-and-generate/*',
                'contracts/download-pdf/*',
                'contracts/generate-clausula-ia*',
                'contracts/documentacion/*',
                'contracts/descargar-documentacion/*',
                'contracts/seleccionar-documentacion/*',
                'contracts/filtrar-documentacion/*',
                'contracts/descargar-filtrado/*',
                'contracts/documentacion-cliente/*',
                'contracts/descargar-documentacion-cliente/*',
                'lookerstudio/*',
                'matrices/*',
                'mantenimientos*',
                'vencimientos*',
                'api/*',
                'pta-cliente-nueva/*',
                'accesosseguncliente/*',
                'estandarcontractual/*',
                'accesosseguncontractualidad/*',
                // Rutas de documentos PDF
                'responsableSGSST/*',
                'policyNoAlcoholDrogas/*',
                'asignacionResponsable/*',
                'asignacionResponsabilidades/*',
                'viewPolicy/*',
                'generatePdf*',
                // Y muchas más rutas de documentos...
            ],
        ],
    ];
}

