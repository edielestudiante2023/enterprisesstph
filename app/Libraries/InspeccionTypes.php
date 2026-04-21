<?php

namespace App\Libraries;

/**
 * Catalogo central de tipos de inspeccion/modulo del grupo /inspecciones.
 * Consumido por MatrizInspeccionesController.
 *
 * Campos por entrada:
 *  - slug: identificador canonico (usa el path de la ruta o un alias unico).
 *  - label: titulo visible.
 *  - group: seccion visual (Inspecciones SST, Plan Emergencia, Saneamiento, etc.).
 *  - icon: clase FontAwesome.
 *  - table: tabla maestra en BD.
 *  - date_col: columna de fecha principal para agregacion por anio.
 *  - estado_col: columna de estado (default 'estado'); null = ignorar filtro.
 *  - estado_value: valor de estado que cuenta como "hecha" (default 'completo'); null = no filtrar.
 *  - extra_where: array col=>val para filtros adicionales (p.ej. certificados por id_mantenimiento).
 *  - list_route / create_route / view_route: URLs relativas del modulo.
 */
class InspeccionTypes
{
    public static function all(): array
    {
        return [
            // ========== Inspecciones SST (nucleo) ==========
            [
                'slug' => 'acta-visita', 'label' => 'Acta de Visita', 'group' => 'Inspecciones SST',
                'icon' => 'fa-file-contract',
                'table' => 'tbl_acta_visita', 'date_col' => 'fecha_visita',
                'list_route' => 'inspecciones/acta-visita', 'create_route' => 'inspecciones/acta-visita/create', 'view_route' => 'inspecciones/acta-visita/view',
            ],
            [
                'slug' => 'inventario-choque', 'label' => 'Inventario Fotos de Choque', 'group' => 'Inspecciones SST',
                'icon' => 'fa-images',
                'table' => 'tbl_inventario_choque', 'date_col' => 'fecha_captura', 'estado_col' => null,
                'list_route' => 'inspecciones/inventario-choque', 'create_route' => 'inspecciones/inventario-choque/create', 'view_route' => 'inspecciones/inventario-choque/view',
            ],
            [
                'slug' => 'inspeccion-locativa', 'label' => 'Locativa', 'group' => 'Inspecciones SST',
                'icon' => 'fa-hard-hat',
                'table' => 'tbl_inspeccion_locativa', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/inspeccion-locativa', 'create_route' => 'inspecciones/inspeccion-locativa/create', 'view_route' => 'inspecciones/inspeccion-locativa/view',
            ],
            [
                'slug' => 'senalizacion', 'label' => 'Señalización', 'group' => 'Inspecciones SST',
                'icon' => 'fa-search',
                'table' => 'tbl_inspeccion_senalizacion', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/senalizacion', 'create_route' => 'inspecciones/senalizacion/create', 'view_route' => 'inspecciones/senalizacion/view',
            ],
            [
                'slug' => 'extintores', 'label' => 'Extintores', 'group' => 'Inspecciones SST',
                'icon' => 'fa-fire-extinguisher',
                'table' => 'tbl_inspeccion_extintores', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/extintores', 'create_route' => 'inspecciones/extintores/create', 'view_route' => 'inspecciones/extintores/view',
            ],
            [
                'slug' => 'botiquin', 'label' => 'Botiquín', 'group' => 'Inspecciones SST',
                'icon' => 'fa-first-aid',
                'table' => 'tbl_inspeccion_botiquin', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/botiquin', 'create_route' => 'inspecciones/botiquin/create', 'view_route' => 'inspecciones/botiquin/view',
            ],
            [
                'slug' => 'gabinetes', 'label' => 'Gabinetes', 'group' => 'Inspecciones SST',
                'icon' => 'fa-shower',
                'table' => 'tbl_inspeccion_gabinetes', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/gabinetes', 'create_route' => 'inspecciones/gabinetes/create', 'view_route' => 'inspecciones/gabinetes/view',
            ],
            [
                'slug' => 'comunicaciones', 'label' => 'Comunicaciones', 'group' => 'Inspecciones SST',
                'icon' => 'fa-walkie-talkie',
                'table' => 'tbl_inspeccion_comunicaciones', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/comunicaciones', 'create_route' => 'inspecciones/comunicaciones/create', 'view_route' => 'inspecciones/comunicaciones/view',
            ],
            [
                'slug' => 'recursos-seguridad', 'label' => 'Recursos de Seguridad', 'group' => 'Inspecciones SST',
                'icon' => 'fa-shield-alt',
                'table' => 'tbl_inspeccion_recursos_seguridad', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/recursos-seguridad', 'create_route' => 'inspecciones/recursos-seguridad/create', 'view_route' => 'inspecciones/recursos-seguridad/view',
            ],
            [
                'slug' => 'productos-quimicos', 'label' => 'Productos Químicos', 'group' => 'Inspecciones SST',
                'icon' => 'fa-flask',
                'table' => 'tbl_inspeccion_productos_quimicos', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/productos-quimicos', 'create_route' => 'inspecciones/productos-quimicos/create', 'view_route' => 'inspecciones/productos-quimicos/view',
            ],
            // ========== Plan de Emergencia ==========
            [
                'slug' => 'brigada-simulacros', 'label' => 'Brigada y Simulacros', 'group' => 'Plan de Emergencia',
                'icon' => 'fa-people-carry',
                'table' => 'tbl_inspeccion_brigada_simulacros', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/brigada-simulacros', 'create_route' => 'inspecciones/brigada-simulacros/create', 'view_route' => 'inspecciones/brigada-simulacros/view',
            ],
            [
                'slug' => 'probabilidad-peligros', 'label' => 'Probabilidad de Peligros', 'group' => 'Plan de Emergencia',
                'icon' => 'fa-exclamation-triangle',
                'table' => 'tbl_probabilidad_peligros', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/probabilidad-peligros', 'create_route' => 'inspecciones/probabilidad-peligros/create', 'view_route' => 'inspecciones/probabilidad-peligros/view',
            ],
            [
                'slug' => 'matriz-vulnerabilidad', 'label' => 'Matriz de Vulnerabilidad', 'group' => 'Plan de Emergencia',
                'icon' => 'fa-th-list',
                'table' => 'tbl_matriz_vulnerabilidad', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/matriz-vulnerabilidad', 'create_route' => 'inspecciones/matriz-vulnerabilidad/create', 'view_route' => 'inspecciones/matriz-vulnerabilidad/view',
            ],
            [
                'slug' => 'plan-emergencia', 'label' => 'Plan de Emergencia', 'group' => 'Plan de Emergencia',
                'icon' => 'fa-file-medical',
                'table' => 'tbl_plan_emergencia', 'date_col' => 'fecha_visita',
                'list_route' => 'inspecciones/plan-emergencia', 'create_route' => 'inspecciones/plan-emergencia/create', 'view_route' => 'inspecciones/plan-emergencia/view',
            ],
            // ========== Infraestructura Especializada ==========
            [
                'slug' => 'ascensores', 'label' => 'Ascensores', 'group' => 'Infraestructura Especializada',
                'icon' => 'fa-elevator',
                'table' => 'tbl_inspeccion_ascensores', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/ascensores', 'create_route' => 'inspecciones/ascensores/create', 'view_route' => 'inspecciones/ascensores/view',
            ],
            [
                'slug' => 'piscinas', 'label' => 'Piscinas', 'group' => 'Infraestructura Especializada',
                'icon' => 'fa-water-ladder',
                'table' => 'tbl_inspeccion_piscinas', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/piscinas', 'create_route' => 'inspecciones/piscinas/create', 'view_route' => 'inspecciones/piscinas/view',
            ],
            [
                'slug' => 'piscinero', 'label' => 'Piscinero / Salvavidas', 'group' => 'Infraestructura Especializada',
                'icon' => 'fa-person-swimming',
                'table' => 'tbl_inspeccion_piscinero', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/piscinero', 'create_route' => 'inspecciones/piscinero/create', 'view_route' => 'inspecciones/piscinero/view',
            ],
            // ========== Dotaciones ==========
            [
                'slug' => 'dotacion-vigilante', 'label' => 'Dotación Vigilante', 'group' => 'Dotaciones',
                'icon' => 'fa-user-shield',
                'table' => 'tbl_dotacion_vigilante', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/dotacion-vigilante', 'create_route' => 'inspecciones/dotacion-vigilante/create', 'view_route' => 'inspecciones/dotacion-vigilante/view',
            ],
            [
                'slug' => 'dotacion-aseadora', 'label' => 'Dotación Aseadora', 'group' => 'Dotaciones',
                'icon' => 'fa-broom',
                'table' => 'tbl_dotacion_aseadora', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/dotacion-aseadora', 'create_route' => 'inspecciones/dotacion-aseadora/create', 'view_route' => 'inspecciones/dotacion-aseadora/view',
            ],
            [
                'slug' => 'dotacion-todero', 'label' => 'Dotación Todero', 'group' => 'Dotaciones',
                'icon' => 'fa-user-cog',
                'table' => 'tbl_dotacion_todero', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/dotacion-todero', 'create_route' => 'inspecciones/dotacion-todero/create', 'view_route' => 'inspecciones/dotacion-todero/view',
            ],
            // ========== Simulacros / Brigadistas ==========
            [
                'slug' => 'preparacion-simulacro', 'label' => 'Preparación Simulacro', 'group' => 'Simulacros',
                'icon' => 'fa-clipboard-check',
                'table' => 'tbl_preparacion_simulacro', 'date_col' => 'fecha_simulacro',
                'list_route' => 'inspecciones/preparacion-simulacro', 'create_route' => 'inspecciones/preparacion-simulacro/create', 'view_route' => 'inspecciones/preparacion-simulacro/view',
            ],
            [
                'slug' => 'simulacro', 'label' => 'Evaluación Simulacro', 'group' => 'Simulacros',
                'icon' => 'fa-person-running',
                'table' => 'tbl_evaluacion_simulacro', 'date_col' => 'fecha',
                'list_route' => 'inspecciones/simulacro', 'create_route' => 'inspecciones/simulacro', 'view_route' => 'inspecciones/simulacro/view',
            ],
            [
                'slug' => 'hv-brigadista', 'label' => 'Hoja de Vida Brigadista', 'group' => 'Simulacros',
                'icon' => 'fa-id-card',
                'table' => 'tbl_hv_brigadista', 'date_col' => 'fecha_registro',
                'list_route' => 'inspecciones/hv-brigadista', 'create_route' => 'inspecciones/hv-brigadista', 'view_route' => 'inspecciones/hv-brigadista/view',
            ],
            // ========== Capacitaciones ==========
            [
                'slug' => 'reporte-capacitacion', 'label' => 'Reporte de Capacitación', 'group' => 'Capacitaciones',
                'icon' => 'fa-chalkboard-teacher',
                'table' => 'tbl_reporte_capacitacion', 'date_col' => 'fecha_capacitacion',
                'list_route' => 'inspecciones/reporte-capacitacion', 'create_route' => 'inspecciones/reporte-capacitacion/create', 'view_route' => 'inspecciones/reporte-capacitacion/view',
            ],
            [
                'slug' => 'asistencia-capacitacion', 'label' => 'Asistencia Capacitación', 'group' => 'Capacitaciones',
                'icon' => 'fa-clipboard-list',
                'table' => 'tbl_asistencia_capacitacion', 'date_col' => 'fecha_sesion', 'estado_col' => null,
                'list_route' => 'inspecciones/asistencia-capacitacion', 'create_route' => 'inspecciones/asistencia-capacitacion/create', 'view_route' => 'inspecciones/asistencia-capacitacion/view',
            ],
            [
                'slug' => 'evaluacion-capacitacion', 'label' => 'Evaluación Capacitación', 'group' => 'Capacitaciones',
                'icon' => 'fa-pen-fancy',
                'table' => 'tbl_evaluaciones', 'date_col' => 'created_at', 'estado_col' => null,
                'list_route' => 'inspecciones/evaluacion-capacitacion', 'create_route' => 'inspecciones/evaluacion-capacitacion/create', 'view_route' => 'inspecciones/evaluacion-capacitacion/view',
            ],
            // ========== Saneamiento Básico ==========
            [
                'slug' => 'limpieza-desinfeccion', 'label' => 'Programa Limpieza y Desinfección', 'group' => 'Saneamiento Básico',
                'icon' => 'fa-pump-soap',
                'table' => 'tbl_programa_limpieza', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/limpieza-desinfeccion', 'create_route' => 'inspecciones/limpieza-desinfeccion/create', 'view_route' => 'inspecciones/limpieza-desinfeccion/view',
            ],
            [
                'slug' => 'residuos-solidos', 'label' => 'Programa Residuos Sólidos', 'group' => 'Saneamiento Básico',
                'icon' => 'fa-recycle',
                'table' => 'tbl_programa_residuos', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/residuos-solidos', 'create_route' => 'inspecciones/residuos-solidos/create', 'view_route' => 'inspecciones/residuos-solidos/view',
            ],
            [
                'slug' => 'control-plagas', 'label' => 'Control Integrado de Plagas', 'group' => 'Saneamiento Básico',
                'icon' => 'fa-bug',
                'table' => 'tbl_programa_plagas', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/control-plagas', 'create_route' => 'inspecciones/control-plagas/create', 'view_route' => 'inspecciones/control-plagas/view',
            ],
            [
                'slug' => 'agua-potable', 'label' => 'Agua Potable', 'group' => 'Saneamiento Básico',
                'icon' => 'fa-tint',
                'table' => 'tbl_programa_agua_potable', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/agua-potable', 'create_route' => 'inspecciones/agua-potable/create', 'view_route' => 'inspecciones/agua-potable/view',
            ],
            [
                'slug' => 'auditoria-zona-residuos', 'label' => 'Auditoría Zona Residuos', 'group' => 'Saneamiento Básico',
                'icon' => 'fa-dumpster',
                'table' => 'tbl_auditoria_zona_residuos', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/auditoria-zona-residuos', 'create_route' => 'inspecciones/auditoria-zona-residuos/create', 'view_route' => 'inspecciones/auditoria-zona-residuos/view',
            ],
            [
                'slug' => 'plan-saneamiento', 'label' => 'Plan de Saneamiento', 'group' => 'Saneamiento Básico',
                'icon' => 'fa-soap',
                'table' => 'tbl_plan_saneamiento', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/plan-saneamiento', 'create_route' => 'inspecciones/plan-saneamiento/create', 'view_route' => 'inspecciones/plan-saneamiento/view',
            ],
            // ========== Planes de Contingencia ==========
            [
                'slug' => 'contingencia-plagas', 'label' => 'Contingencia Plagas', 'group' => 'Contingencias',
                'icon' => 'fa-bug',
                'table' => 'tbl_plan_contingencia_plagas', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/contingencia-plagas', 'create_route' => 'inspecciones/contingencia-plagas/create', 'view_route' => 'inspecciones/contingencia-plagas/view',
            ],
            [
                'slug' => 'contingencia-agua', 'label' => 'Contingencia Sin Agua', 'group' => 'Contingencias',
                'icon' => 'fa-tint-slash',
                'table' => 'tbl_plan_contingencia_agua', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/contingencia-agua', 'create_route' => 'inspecciones/contingencia-agua/create', 'view_route' => 'inspecciones/contingencia-agua/view',
            ],
            [
                'slug' => 'contingencia-basura', 'label' => 'Contingencia Basura', 'group' => 'Contingencias',
                'icon' => 'fa-trash-alt',
                'table' => 'tbl_plan_contingencia_basura', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/contingencia-basura', 'create_route' => 'inspecciones/contingencia-basura/create', 'view_route' => 'inspecciones/contingencia-basura/view',
            ],
            // ========== Certificados de Servicio ==========
            [
                'slug' => 'lavado-tanques', 'label' => 'Lavado de Tanques', 'group' => 'Certificados de Servicio',
                'icon' => 'fa-water',
                'table' => 'tbl_certificado_servicio', 'date_col' => 'created_at', 'estado_col' => null,
                'extra_where' => ['id_mantenimiento' => 2],
                'list_route' => 'inspecciones/lavado-tanques', 'create_route' => 'inspecciones/lavado-tanques/create', 'view_route' => 'inspecciones/lavado-tanques/view',
            ],
            [
                'slug' => 'fumigacion', 'label' => 'Fumigación', 'group' => 'Certificados de Servicio',
                'icon' => 'fa-spray-can',
                'table' => 'tbl_certificado_servicio', 'date_col' => 'created_at', 'estado_col' => null,
                'extra_where' => ['id_mantenimiento' => 3],
                'list_route' => 'inspecciones/fumigacion', 'create_route' => 'inspecciones/fumigacion/create', 'view_route' => 'inspecciones/fumigacion/view',
            ],
            [
                'slug' => 'desratizacion', 'label' => 'Desratización', 'group' => 'Certificados de Servicio',
                'icon' => 'fa-mouse',
                'table' => 'tbl_certificado_servicio', 'date_col' => 'created_at', 'estado_col' => null,
                'extra_where' => ['id_mantenimiento' => 4],
                'list_route' => 'inspecciones/desratizacion', 'create_route' => 'inspecciones/desratizacion/create', 'view_route' => 'inspecciones/desratizacion/view',
            ],
            // ========== KPIs Saneamiento ==========
            [
                'slug' => 'kpi-limpieza', 'label' => 'KPI Limpieza', 'group' => 'KPIs Saneamiento',
                'icon' => 'fa-chart-line',
                'table' => 'tbl_kpi_limpieza', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/kpi-limpieza', 'create_route' => 'inspecciones/kpi-limpieza/create', 'view_route' => 'inspecciones/kpi-limpieza/view',
            ],
            [
                'slug' => 'kpi-residuos', 'label' => 'KPI Residuos', 'group' => 'KPIs Saneamiento',
                'icon' => 'fa-chart-bar',
                'table' => 'tbl_kpi_residuos', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/kpi-residuos', 'create_route' => 'inspecciones/kpi-residuos/create', 'view_route' => 'inspecciones/kpi-residuos/view',
            ],
            [
                'slug' => 'kpi-plagas', 'label' => 'KPI Plagas', 'group' => 'KPIs Saneamiento',
                'icon' => 'fa-chart-pie',
                'table' => 'tbl_kpi_plagas', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/kpi-plagas', 'create_route' => 'inspecciones/kpi-plagas/create', 'view_route' => 'inspecciones/kpi-plagas/view',
            ],
            [
                'slug' => 'kpi-agua-potable', 'label' => 'KPI Agua Potable', 'group' => 'KPIs Saneamiento',
                'icon' => 'fa-chart-area',
                'table' => 'tbl_kpi_agua_potable', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/kpi-agua-potable', 'create_route' => 'inspecciones/kpi-agua-potable/create', 'view_route' => 'inspecciones/kpi-agua-potable/view',
            ],
            // ========== Otros ==========
            [
                'slug' => 'planilla-seg-social', 'label' => 'Planilla Seg. Social', 'group' => 'Otros',
                'icon' => 'fa-file-invoice',
                'table' => 'tbl_planilla_ss_inspeccion', 'date_col' => 'created_at', 'estado_col' => null,
                'list_route' => 'inspecciones/planilla-ss', 'create_route' => 'inspecciones/planilla-ss/create', 'view_route' => 'inspecciones/planilla-ss/view',
            ],
            [
                'slug' => 'carta-vigia', 'label' => 'Carta Vigía', 'group' => 'Otros',
                'icon' => 'fa-envelope-open-text',
                'table' => 'tbl_carta_vigia', 'date_col' => 'created_at', 'estado_col' => null,
                'list_route' => 'inspecciones/carta-vigia', 'create_route' => 'inspecciones/carta-vigia', 'view_route' => 'inspecciones/carta-vigia/view',
            ],
        ];
    }

    public static function slugs(): array
    {
        return array_column(self::all(), 'slug');
    }

    public static function bySlug(string $slug): ?array
    {
        foreach (self::all() as $t) {
            if ($t['slug'] === $slug) {
                return $t;
            }
        }
        return null;
    }

    public static function grouped(): array
    {
        $out = [];
        foreach (self::all() as $t) {
            $g = $t['group'] ?? 'Otros';
            $out[$g][] = $t;
        }
        return $out;
    }
}
