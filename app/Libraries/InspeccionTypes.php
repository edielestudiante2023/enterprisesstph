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
 *  - keywords: terminos tipicos que aparecen en la actividad PTA — usado por Match IA.
 *  - descripcion_ia: 1 linea de contexto semantico para que el IA discrimine entre slugs similares.
 *  - actividad_template: redaccion oficial que se inserta en tbl_pta_cliente.actividad_plandetrabajo
 *    cuando el consultor importa desde la Matriz. Si frecuencia>1, se sufija " (Periodo N)".
 *    Tomada del PTA2026.csv para consistencia con la consultoria.
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
                'keywords' => ['acta de visita', 'visita técnica', 'visita mensual', 'levantamiento de acta', 'acta consultoría', 'visita SST'],
                'descripcion_ia' => 'Acta firmada de la visita mensual del consultor SST a la copropiedad, con hallazgos y compromisos.',
                'actividad_template' => 'Realizar visita técnica de consultoría SST y elaborar acta',
            ],
            [
                'slug' => 'inventario-choque', 'label' => 'Inventario Fotos de Choque', 'group' => 'Inspecciones SST',
                'icon' => 'fa-images',
                'table' => 'tbl_inventario_choque', 'date_col' => 'fecha_captura', 'estado_col' => null,
                'list_route' => 'inspecciones/inventario-choque', 'create_route' => 'inspecciones/inventario-choque/create', 'view_route' => 'inspecciones/inventario-choque/view',
                'keywords' => ['inventario de choque', 'fotos de choque', 'evidencia fotográfica inicial', 'levantamiento fotográfico', 'registro fotográfico inicial', 'estado inicial'],
                'descripcion_ia' => 'Levantamiento fotográfico inicial del estado de la copropiedad como evidencia base de partida.',
                'actividad_template' => 'Realizar inventario fotográfico de choque del estado de la copropiedad',
            ],
            [
                'slug' => 'inspeccion-locativa', 'label' => 'Locativa', 'group' => 'Inspecciones SST',
                'icon' => 'fa-hard-hat',
                'table' => 'tbl_inspeccion_locativa', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/inspeccion-locativa', 'create_route' => 'inspecciones/inspeccion-locativa/create', 'view_route' => 'inspecciones/inspeccion-locativa/view',
                'keywords' => ['locativa', 'inspección locativa', 'áreas comunes', 'condiciones locativas', 'instalaciones físicas', 'recorrido locativo', 'checklist locativo'],
                'descripcion_ia' => 'Inspección general de áreas comunes y condiciones físicas/estructurales de la copropiedad.',
                'actividad_template' => 'Realizar la inspección locativa',
            ],
            [
                'slug' => 'senalizacion', 'label' => 'Señalización', 'group' => 'Inspecciones SST',
                'icon' => 'fa-search',
                'table' => 'tbl_inspeccion_senalizacion', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/senalizacion', 'create_route' => 'inspecciones/senalizacion/create', 'view_route' => 'inspecciones/senalizacion/view',
                'keywords' => ['señalización', 'señalética', 'demarcación', 'señales de seguridad', 'avisos preventivos', 'rotulación', 'pictogramas', 'rutas de evacuación señalizadas'],
                'descripcion_ia' => 'Revisión de señalética de seguridad, rutas de evacuación y demarcación de áreas.',
                'actividad_template' => 'Inspeccionar la señalización de seguridad y salud en el trabajo',
            ],
            [
                'slug' => 'extintores', 'label' => 'Extintores', 'group' => 'Inspecciones SST',
                'icon' => 'fa-fire-extinguisher',
                'table' => 'tbl_inspeccion_extintores', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/extintores', 'create_route' => 'inspecciones/extintores/create', 'view_route' => 'inspecciones/extintores/view',
                'keywords' => ['extintor', 'extintores', 'recarga extintor', 'mantenimiento extintor', 'agente extintor', 'PQS', 'CO2', 'multipropósito', 'H2O', 'extintor portátil', 'prueba hidrostática'],
                'descripcion_ia' => 'Inspección visual periódica de extintores portátiles (carga, manómetro, ubicación, vencimiento).',
                'actividad_template' => 'Inspeccionar los extintores',
            ],
            [
                'slug' => 'botiquin', 'label' => 'Botiquín', 'group' => 'Inspecciones SST',
                'icon' => 'fa-first-aid',
                'table' => 'tbl_inspeccion_botiquin', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/botiquin', 'create_route' => 'inspecciones/botiquin/create', 'view_route' => 'inspecciones/botiquin/view',
                'keywords' => ['botiquín', 'botiquin', 'primeros auxilios', 'insumos médicos', 'kit primeros auxilios', 'medicamentos vencidos', 'dotación botiquín'],
                'descripcion_ia' => 'Verificación de existencia, vigencia y completitud del botiquín de primeros auxilios.',
                'actividad_template' => 'Inspeccionar los botiquines de primeros auxilios',
            ],
            [
                'slug' => 'gabinetes', 'label' => 'Gabinetes', 'group' => 'Inspecciones SST',
                'icon' => 'fa-shower',
                'table' => 'tbl_inspeccion_gabinetes', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/gabinetes', 'create_route' => 'inspecciones/gabinetes/create', 'view_route' => 'inspecciones/gabinetes/view',
                'keywords' => ['gabinete contra incendios', 'gabinete', 'mangueras contraincendios', 'BIE', 'red contra incendio', 'hidrante', 'sistema contraincendio', 'boquilla'],
                'descripcion_ia' => 'Inspección de gabinetes y red contra incendio: manguera, llave, válvula, boquilla, hacha.',
                'actividad_template' => 'Inspeccionar los gabinetes contra incendio',
            ],
            [
                'slug' => 'comunicaciones', 'label' => 'Comunicaciones', 'group' => 'Inspecciones SST',
                'icon' => 'fa-walkie-talkie',
                'table' => 'tbl_inspeccion_comunicaciones', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/comunicaciones', 'create_route' => 'inspecciones/comunicaciones/create', 'view_route' => 'inspecciones/comunicaciones/view',
                'keywords' => ['comunicaciones', 'radios', 'radio teléfono', 'sistema de comunicación', 'walkie talkie', 'directorio de emergencia', 'punto de comunicación', 'central de comunicaciones'],
                'descripcion_ia' => 'Verificación de equipos de comunicación de emergencia y directorios de contacto.',
                'actividad_template' => 'Inspeccionar los equipos de comunicación',
            ],
            [
                'slug' => 'recursos-seguridad', 'label' => 'Recursos de Seguridad', 'group' => 'Inspecciones SST',
                'icon' => 'fa-shield-alt',
                'table' => 'tbl_inspeccion_recursos_seguridad', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/recursos-seguridad', 'create_route' => 'inspecciones/recursos-seguridad/create', 'view_route' => 'inspecciones/recursos-seguridad/view',
                'keywords' => ['recursos de seguridad', 'recursos para emergencia', 'EPP', 'elementos de protección personal', 'recursos de respuesta', 'inventario seguridad', 'arnés', 'línea de vida'],
                'descripcion_ia' => 'Inventario y revisión de recursos físicos para respuesta ante emergencias (EPP, alturas, rescate).',
                'actividad_template' => 'Inspeccionar los recursos destinados para la seguridad',
            ],
            [
                'slug' => 'productos-quimicos', 'label' => 'Productos Químicos', 'group' => 'Inspecciones SST',
                'icon' => 'fa-flask',
                'table' => 'tbl_inspeccion_productos_quimicos', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/productos-quimicos', 'create_route' => 'inspecciones/productos-quimicos/create', 'view_route' => 'inspecciones/productos-quimicos/view',
                'keywords' => ['productos químicos', 'sustancias químicas', 'hojas de seguridad', 'FDS', 'SGA', 'rombo NFPA', 'almacenamiento químico', 'envasado químicos', 'compatibilidad química'],
                'descripcion_ia' => 'Inspección de almacenamiento, rotulado y FDS (SGA) de productos químicos.',
                'actividad_template' => 'Inspeccionar el almacenamiento de productos químicos',
            ],
            // ========== Plan de Emergencia ==========
            [
                'slug' => 'brigada-simulacros', 'label' => 'Brigada y Simulacros', 'group' => 'Plan de Emergencia',
                'icon' => 'fa-people-carry',
                'table' => 'tbl_inspeccion_brigada_simulacros', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/brigada-simulacros', 'create_route' => 'inspecciones/brigada-simulacros/create', 'view_route' => 'inspecciones/brigada-simulacros/view',
                'keywords' => ['brigada de emergencia', 'capacitación brigada', 'entrenamiento brigada', 'brigadistas', 'formación brigada', 'conformación brigada'],
                'descripcion_ia' => 'Conformación, entrenamiento y dotación de la brigada de emergencia.',
                'actividad_template' => 'Revisar el estado y la capacitación de los brigadistas',
            ],
            [
                'slug' => 'probabilidad-peligros', 'label' => 'Probabilidad de Peligros', 'group' => 'Plan de Emergencia',
                'icon' => 'fa-exclamation-triangle',
                'table' => 'tbl_probabilidad_peligros', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/probabilidad-peligros', 'create_route' => 'inspecciones/probabilidad-peligros/create', 'view_route' => 'inspecciones/probabilidad-peligros/view',
                'keywords' => ['identificación de peligros', 'probabilidad de peligros', 'matriz de peligros', 'GTC 45', 'IPER', 'evaluación de riesgos', 'peligros identificados'],
                'descripcion_ia' => 'Identificación y valoración de peligros bajo GTC 45 (matriz IPER) en la copropiedad.',
                'actividad_template' => 'Diligenciar la matriz de peligros',
            ],
            [
                'slug' => 'matriz-vulnerabilidad', 'label' => 'Matriz de Vulnerabilidad', 'group' => 'Plan de Emergencia',
                'icon' => 'fa-th-list',
                'table' => 'tbl_matriz_vulnerabilidad', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/matriz-vulnerabilidad', 'create_route' => 'inspecciones/matriz-vulnerabilidad/create', 'view_route' => 'inspecciones/matriz-vulnerabilidad/view',
                'keywords' => ['matriz de vulnerabilidad', 'análisis de vulnerabilidad', 'amenazas', 'rombo de vulnerabilidad', 'amenazas naturales antrópicas', 'colores vulnerabilidad', 'metodología colores'],
                'descripcion_ia' => 'Análisis de amenazas y vulnerabilidad por método de colores (personas, recursos, sistemas).',
                'actividad_template' => 'Diligenciar la matriz de vulnerabilidad',
            ],
            [
                'slug' => 'plan-emergencia', 'label' => 'Plan de Emergencia', 'group' => 'Plan de Emergencia',
                'icon' => 'fa-file-medical',
                'table' => 'tbl_plan_emergencia', 'date_col' => 'fecha_visita',
                'list_route' => 'inspecciones/plan-emergencia', 'create_route' => 'inspecciones/plan-emergencia/create', 'view_route' => 'inspecciones/plan-emergencia/view',
                'keywords' => ['plan de emergencia', 'plan de emergencias', 'documento plan emergencia', 'elaboración plan emergencia', 'actualización plan emergencia', 'PEC'],
                'descripcion_ia' => 'Elaboración o actualización del documento Plan de Emergencias y Contingencias (PEC).',
                'actividad_template' => 'Elaborar/actualizar el Plan de Emergencias',
            ],
            // ========== Infraestructura Especializada ==========
            [
                'slug' => 'ascensores', 'label' => 'Ascensores', 'group' => 'Infraestructura Especializada',
                'icon' => 'fa-elevator',
                'table' => 'tbl_inspeccion_ascensores', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/ascensores', 'create_route' => 'inspecciones/ascensores/create', 'view_route' => 'inspecciones/ascensores/view',
                'keywords' => ['ascensor', 'ascensores', 'certificado ascensor', 'mantenimiento ascensor', 'cabina ascensor', 'inspección ascensor', 'NTC 5926'],
                'descripcion_ia' => 'Inspección/verificación de mantenimiento y certificación de ascensores (NTC 5926).',
                'actividad_template' => 'Realizar la inspección de ascensores y verificar certificación NTC 5926',
            ],
            [
                'slug' => 'piscinas', 'label' => 'Piscinas', 'group' => 'Infraestructura Especializada',
                'icon' => 'fa-water-ladder',
                'table' => 'tbl_inspeccion_piscinas', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/piscinas', 'create_route' => 'inspecciones/piscinas/create', 'view_route' => 'inspecciones/piscinas/view',
                'keywords' => ['piscina', 'piscinas', 'cloro residual', 'pH piscina', 'agua piscina', 'reja piscina', 'normativa piscinas', 'Ley 1209', 'borde piscina'],
                'descripcion_ia' => 'Inspección de condiciones de seguridad y calidad del agua de piscinas (Ley 1209).',
                'actividad_template' => 'Realizar la inspección de piscinas según Ley 1209',
            ],
            [
                'slug' => 'piscinero', 'label' => 'Piscinero / Salvavidas', 'group' => 'Infraestructura Especializada',
                'icon' => 'fa-person-swimming',
                'table' => 'tbl_inspeccion_piscinero', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/piscinero', 'create_route' => 'inspecciones/piscinero/create', 'view_route' => 'inspecciones/piscinero/view',
                'keywords' => ['piscinero', 'salvavidas', 'operador de piscina', 'certificado piscinero', 'curso piscinero', 'RCP salvavidas'],
                'descripcion_ia' => 'Verificación de certificaciones y dotación del piscinero/salvavidas asignado.',
                'actividad_template' => 'Verificar certificaciones y dotación del piscinero/salvavidas',
            ],
            // ========== Dotaciones ==========
            [
                'slug' => 'dotacion-vigilante', 'label' => 'Dotación Vigilante', 'group' => 'Dotaciones',
                'icon' => 'fa-user-shield',
                'table' => 'tbl_dotacion_vigilante', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/dotacion-vigilante', 'create_route' => 'inspecciones/dotacion-vigilante/create', 'view_route' => 'inspecciones/dotacion-vigilante/view',
                'keywords' => ['dotación vigilante', 'uniforme vigilante', 'EPP vigilante', 'dotación seguridad', 'dotación portero', 'entrega dotación vigilancia'],
                'descripcion_ia' => 'Entrega y verificación de dotación al personal de vigilancia/portería.',
                'actividad_template' => 'Inspeccionar la dotación del personal de vigilancia',
            ],
            [
                'slug' => 'dotacion-aseadora', 'label' => 'Dotación Aseadora', 'group' => 'Dotaciones',
                'icon' => 'fa-broom',
                'table' => 'tbl_dotacion_aseadora', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/dotacion-aseadora', 'create_route' => 'inspecciones/dotacion-aseadora/create', 'view_route' => 'inspecciones/dotacion-aseadora/view',
                'keywords' => ['dotación aseadora', 'uniforme aseadora', 'EPP aseadora', 'dotación de aseo', 'dotación servicios generales', 'entrega dotación limpieza'],
                'descripcion_ia' => 'Entrega y verificación de dotación al personal de aseo/servicios generales.',
                'actividad_template' => 'Inspeccionar la dotación del personal de aseo',
            ],
            [
                'slug' => 'dotacion-todero', 'label' => 'Dotación Todero', 'group' => 'Dotaciones',
                'icon' => 'fa-user-cog',
                'table' => 'tbl_dotacion_todero', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/dotacion-todero', 'create_route' => 'inspecciones/dotacion-todero/create', 'view_route' => 'inspecciones/dotacion-todero/view',
                'keywords' => ['dotación todero', 'uniforme todero', 'EPP mantenimiento', 'dotación mantenimiento', 'dotación operario', 'entrega dotación oficios varios'],
                'descripcion_ia' => 'Entrega y verificación de dotación al personal de mantenimiento/todero.',
                'actividad_template' => 'Inspeccionar la dotación del personal de mantenimiento (todero)',
            ],
            // ========== Simulacros / Brigadistas ==========
            [
                'slug' => 'preparacion-simulacro', 'label' => 'Preparación Simulacro', 'group' => 'Simulacros',
                'icon' => 'fa-clipboard-check',
                'table' => 'tbl_preparacion_simulacro', 'date_col' => 'fecha_simulacro',
                'list_route' => 'inspecciones/preparacion-simulacro', 'create_route' => 'inspecciones/preparacion-simulacro/create', 'view_route' => 'inspecciones/preparacion-simulacro/view',
                'keywords' => ['preparación simulacro', 'planeación simulacro', 'guión simulacro', 'briefing simulacro', 'cronograma simulacro', 'previo al simulacro'],
                'descripcion_ia' => 'Planeación y preparación previa (guión, roles, cronograma) al simulacro de evacuación.',
                'actividad_template' => 'Asesorar y preparar la ejecución del simulacro de emergencias',
            ],
            [
                'slug' => 'simulacro', 'label' => 'Evaluación Simulacro', 'group' => 'Simulacros',
                'icon' => 'fa-person-running',
                'table' => 'tbl_evaluacion_simulacro', 'date_col' => 'fecha',
                'list_route' => 'inspecciones/simulacro', 'create_route' => 'inspecciones/simulacro', 'view_route' => 'inspecciones/simulacro/view',
                'keywords' => ['simulacro', 'evaluación simulacro', 'simulacro de evacuación', 'simulacro nacional', 'simulacro de incendio', 'simulacro sismo', 'debriefing simulacro', 'lecciones aprendidas'],
                'descripcion_ia' => 'Ejecución y evaluación del simulacro (tiempos, lecciones aprendidas, plan de mejora).',
                'actividad_template' => 'Evaluar la ejecución del simulacro de emergencias',
            ],
            [
                'slug' => 'hv-brigadista', 'label' => 'Hoja de Vida Brigadista', 'group' => 'Simulacros',
                'icon' => 'fa-id-card',
                'table' => 'tbl_hv_brigadista', 'date_col' => 'fecha_registro',
                'list_route' => 'inspecciones/hv-brigadista', 'create_route' => 'inspecciones/hv-brigadista', 'view_route' => 'inspecciones/hv-brigadista/view',
                'keywords' => ['hoja de vida brigadista', 'hv brigadista', 'ficha brigadista', 'datos brigadista', 'inscripción brigadista', 'registro brigadista'],
                'descripcion_ia' => 'Hoja de vida y datos personales de cada miembro de la brigada de emergencia.',
                'actividad_template' => 'Diligenciar hoja de vida de los brigadistas',
            ],
            // ========== Capacitaciones ==========
            [
                'slug' => 'acta-capacitacion', 'label' => 'Reporte de Capacitación QR', 'group' => 'Capacitaciones',
                'icon' => 'fa-graduation-cap',
                'table' => 'tbl_acta_capacitacion', 'date_col' => 'fecha_capacitacion',
                'list_route' => 'inspecciones/acta-capacitacion', 'create_route' => 'inspecciones/acta-capacitacion/create', 'view_route' => 'inspecciones/acta-capacitacion/view',
                'keywords' => ['acta capacitación', 'reporte capacitación', 'capacitación QR', 'asistencia capacitación', 'evidencia capacitación', 'registro capacitación', 'lista de asistencia'],
                'descripcion_ia' => 'Reporte de la capacitación con asistencia firmada (vía QR) — evidencia de ejecución.',
                'actividad_template' => 'Realizar capacitación del programa anual de capacitación en SG-SST',
            ],
            [
                'slug' => 'evaluacion-capacitacion', 'label' => 'Evaluación Capacitación', 'group' => 'Capacitaciones',
                'icon' => 'fa-pen-fancy',
                'table' => 'tbl_evaluaciones', 'date_col' => 'created_at', 'estado_col' => null,
                'list_route' => 'inspecciones/evaluacion-capacitacion', 'create_route' => 'inspecciones/evaluacion-capacitacion/create', 'view_route' => 'inspecciones/evaluacion-capacitacion/view',
                'keywords' => ['evaluación capacitación', 'examen capacitación', 'prueba capacitación', 'test inducción', 'verificación de aprendizaje', 'evaluación de aprendizaje'],
                'descripcion_ia' => 'Evaluación de aprendizaje aplicada al final de una capacitación o inducción.',
                'actividad_template' => 'Evaluar el aprendizaje de la capacitación del programa anual en SG-SST',
            ],
            // ========== Saneamiento Básico ==========
            [
                'slug' => 'limpieza-desinfeccion', 'label' => 'Programa Limpieza y Desinfección', 'group' => 'Saneamiento Básico',
                'icon' => 'fa-pump-soap',
                'table' => 'tbl_programa_limpieza', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/limpieza-desinfeccion', 'create_route' => 'inspecciones/limpieza-desinfeccion/create', 'view_route' => 'inspecciones/limpieza-desinfeccion/view',
                'keywords' => ['programa limpieza y desinfección', 'programa de aseo', 'protocolo limpieza', 'manual de aseo', 'POES limpieza', 'plan de limpieza documental'],
                'descripcion_ia' => 'Elaboración/revisión del DOCUMENTO Programa de Limpieza y Desinfección (no la ejecución).',
                'actividad_template' => 'Elaborar/actualizar el Programa de Limpieza y Desinfección',
            ],
            [
                'slug' => 'residuos-solidos', 'label' => 'Programa Residuos Sólidos', 'group' => 'Saneamiento Básico',
                'icon' => 'fa-recycle',
                'table' => 'tbl_programa_residuos', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/residuos-solidos', 'create_route' => 'inspecciones/residuos-solidos/create', 'view_route' => 'inspecciones/residuos-solidos/view',
                'keywords' => ['programa residuos sólidos', 'PGIRS', 'gestión integral residuos', 'manejo de basuras', 'plan de residuos documental', 'separación en la fuente'],
                'descripcion_ia' => 'Elaboración/revisión del DOCUMENTO Programa de Residuos Sólidos (PGIRS).',
                'actividad_template' => 'Elaborar/actualizar el Programa de Manejo Integral de Residuos Sólidos (PGIRS)',
            ],
            [
                'slug' => 'control-plagas', 'label' => 'Control Integrado de Plagas', 'group' => 'Saneamiento Básico',
                'icon' => 'fa-bug',
                'table' => 'tbl_programa_plagas', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/control-plagas', 'create_route' => 'inspecciones/control-plagas/create', 'view_route' => 'inspecciones/control-plagas/view',
                'keywords' => ['control integrado de plagas', 'CIP', 'manejo de plagas documental', 'plan de plagas', 'programa de plagas', 'manual control plagas'],
                'descripcion_ia' => 'Elaboración/revisión del DOCUMENTO Programa de Control Integrado de Plagas (no el servicio de fumigación).',
                'actividad_template' => 'Elaborar/actualizar el Programa de Control Integrado de Plagas',
            ],
            [
                'slug' => 'agua-potable', 'label' => 'Agua Potable', 'group' => 'Saneamiento Básico',
                'icon' => 'fa-tint',
                'table' => 'tbl_programa_agua_potable', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/agua-potable', 'create_route' => 'inspecciones/agua-potable/create', 'view_route' => 'inspecciones/agua-potable/view',
                'keywords' => ['programa agua potable', 'plan agua potable', 'documento agua potable', 'aseguramiento calidad de agua', 'gestión del agua documental'],
                'descripcion_ia' => 'DOCUMENTO programa de aseguramiento de calidad del agua potable (no el KPI ni el lavado).',
                'actividad_template' => 'Elaborar/actualizar el Programa de abastecimiento y control de agua potable',
            ],
            [
                'slug' => 'auditoria-zona-residuos', 'label' => 'Auditoría Zona Residuos', 'group' => 'Saneamiento Básico',
                'icon' => 'fa-dumpster',
                'table' => 'tbl_auditoria_zona_residuos', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/auditoria-zona-residuos', 'create_route' => 'inspecciones/auditoria-zona-residuos/create', 'view_route' => 'inspecciones/auditoria-zona-residuos/view',
                'keywords' => ['auditoría zona residuos', 'shut basuras', 'cuarto de basura', 'área de residuos', 'inspección zona residuos', 'cuarto de residuos'],
                'descripcion_ia' => 'Auditoría in situ del shut/cuarto de basuras y zona de almacenamiento de residuos.',
                'actividad_template' => 'Elaborar el informe de inspección del cuarto de residuos',
            ],
            [
                'slug' => 'plan-saneamiento', 'label' => 'Plan de Saneamiento', 'group' => 'Saneamiento Básico',
                'icon' => 'fa-soap',
                'table' => 'tbl_plan_saneamiento', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/plan-saneamiento', 'create_route' => 'inspecciones/plan-saneamiento/create', 'view_route' => 'inspecciones/plan-saneamiento/view',
                'keywords' => ['plan de saneamiento', 'plan de saneamiento básico', 'PSB', 'consolidado saneamiento', 'plan saneamiento documento'],
                'descripcion_ia' => 'Documento consolidado del Plan de Saneamiento Básico de la copropiedad (agrupa los 4 programas).',
                'actividad_template' => 'Diseñar y definir el Plan de Saneamiento Básico (agua potable, manejo de residuos y control de plagas)',
            ],
            // ========== Planes de Contingencia ==========
            [
                'slug' => 'contingencia-plagas', 'label' => 'Contingencia Plagas', 'group' => 'Contingencias',
                'icon' => 'fa-bug',
                'table' => 'tbl_plan_contingencia_plagas', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/contingencia-plagas', 'create_route' => 'inspecciones/contingencia-plagas/create', 'view_route' => 'inspecciones/contingencia-plagas/view',
                'keywords' => ['plan contingencia plagas', 'contingencia plagas', 'emergencia por plagas', 'invasión de plagas', 'brote plagas', 'plan respuesta plagas'],
                'descripcion_ia' => 'Plan de respuesta ante emergencia/brote de plagas (no el programa regular ni el servicio).',
                'actividad_template' => 'Elaborar el Plan de Contingencia ante invasión/brote de plagas',
            ],
            [
                'slug' => 'contingencia-limpieza-desinfeccion', 'label' => 'Contingencia Limpieza y Desinfección', 'group' => 'Contingencias',
                'icon' => 'fa-spray-can-sparkles',
                'table' => 'tbl_plan_contingencia_limpieza_desinfeccion', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/contingencia-limpieza-desinfeccion', 'create_route' => 'inspecciones/contingencia-limpieza-desinfeccion/create', 'view_route' => 'inspecciones/contingencia-limpieza-desinfeccion/view',
                'keywords' => ['contingencia limpieza y desinfección', 'plan contingencia sanitaria', 'limpieza emergencia', 'desinfección emergencia', 'brote sanitario', 'covid limpieza'],
                'descripcion_ia' => 'Plan de contingencia sanitaria por brote o emergencia de limpieza/desinfección.',
                'actividad_template' => 'Elaborar el Plan de Contingencia sanitaria de limpieza y desinfección',
            ],
            [
                'slug' => 'contingencia-agua', 'label' => 'Contingencia Sin Agua', 'group' => 'Contingencias',
                'icon' => 'fa-tint-slash',
                'table' => 'tbl_plan_contingencia_agua', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/contingencia-agua', 'create_route' => 'inspecciones/contingencia-agua/create', 'view_route' => 'inspecciones/contingencia-agua/view',
                'keywords' => ['contingencia agua', 'contingencia sin agua', 'corte de agua', 'desabastecimiento agua', 'plan ante falta de agua', 'emergencia hídrica'],
                'descripcion_ia' => 'Plan de contingencia ante desabastecimiento o falta de agua.',
                'actividad_template' => 'Elaborar el Plan de Contingencia ante desabastecimiento de agua',
            ],
            [
                'slug' => 'contingencia-basura', 'label' => 'Contingencia Basura', 'group' => 'Contingencias',
                'icon' => 'fa-trash-alt',
                'table' => 'tbl_plan_contingencia_basura', 'date_col' => 'fecha_programa',
                'list_route' => 'inspecciones/contingencia-basura', 'create_route' => 'inspecciones/contingencia-basura/create', 'view_route' => 'inspecciones/contingencia-basura/view',
                'keywords' => ['contingencia basura', 'contingencia residuos', 'paro recolección', 'acumulación basuras', 'no recolección', 'huelga recolección'],
                'descripcion_ia' => 'Plan de contingencia ante paro o falla en recolección de basuras.',
                'actividad_template' => 'Elaborar el Plan de Contingencia ante falla en recolección de basuras',
            ],
            // ========== Certificados de Servicio ==========
            [
                'slug' => 'lavado-tanques', 'label' => 'Lavado de Tanques', 'group' => 'Certificados de Servicio',
                'icon' => 'fa-water',
                'table' => 'tbl_certificado_servicio', 'date_col' => 'created_at', 'estado_col' => null,
                'extra_where' => ['id_mantenimiento' => 2],
                'list_route' => 'inspecciones/lavado-tanques', 'create_route' => 'inspecciones/lavado-tanques/create', 'view_route' => 'inspecciones/lavado-tanques/view',
                'keywords' => ['lavado de tanques', 'lavado tanque agua', 'desinfección tanque', 'certificado lavado tanque', 'limpieza tanque almacenamiento', 'lavado de tanque'],
                'descripcion_ia' => 'Certificado del servicio externo de lavado y desinfección de tanques de agua.',
                'actividad_template' => 'Registrar el lavado de tanques con soporte correspondiente',
            ],
            [
                'slug' => 'fumigacion', 'label' => 'Fumigación', 'group' => 'Certificados de Servicio',
                'icon' => 'fa-spray-can',
                'table' => 'tbl_certificado_servicio', 'date_col' => 'created_at', 'estado_col' => null,
                'extra_where' => ['id_mantenimiento' => 3],
                'list_route' => 'inspecciones/fumigacion', 'create_route' => 'inspecciones/fumigacion/create', 'view_route' => 'inspecciones/fumigacion/view',
                'keywords' => ['fumigación', 'certificado fumigación', 'aspersión', 'control químico', 'visita fumigador', 'servicio fumigación'],
                'descripcion_ia' => 'Certificado del servicio externo de fumigación (servicio ejecutado, no el programa).',
                'actividad_template' => 'Registrar la fumigación con soporte correspondiente',
            ],
            [
                'slug' => 'desratizacion', 'label' => 'Desratización', 'group' => 'Certificados de Servicio',
                'icon' => 'fa-mouse',
                'table' => 'tbl_certificado_servicio', 'date_col' => 'created_at', 'estado_col' => null,
                'extra_where' => ['id_mantenimiento' => 4],
                'list_route' => 'inspecciones/desratizacion', 'create_route' => 'inspecciones/desratizacion/create', 'view_route' => 'inspecciones/desratizacion/view',
                'keywords' => ['desratización', 'control de roedores', 'certificado desratización', 'cebos roedores', 'trampas ratones', 'servicio desratización'],
                'descripcion_ia' => 'Certificado del servicio externo de desratización (servicio ejecutado).',
                'actividad_template' => 'Registrar la desratización con soporte correspondiente',
            ],
            // ========== KPIs Saneamiento ==========
            [
                'slug' => 'kpi-limpieza', 'label' => 'KPI Limpieza', 'group' => 'KPIs Saneamiento',
                'icon' => 'fa-chart-line',
                'table' => 'tbl_kpi_limpieza', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/kpi-limpieza', 'create_route' => 'inspecciones/kpi-limpieza/create', 'view_route' => 'inspecciones/kpi-limpieza/view',
                'keywords' => ['kpi limpieza', 'indicador limpieza', 'medición limpieza', 'seguimiento limpieza', 'cumplimiento limpieza', 'verificación periódica limpieza'],
                'descripcion_ia' => 'INDICADOR periódico de cumplimiento del programa de limpieza (no el programa documental).',
                'actividad_template' => 'Realizar seguimiento periódico a los indicadores del Programa de limpieza y desinfección',
            ],
            [
                'slug' => 'kpi-residuos', 'label' => 'KPI Residuos', 'group' => 'KPIs Saneamiento',
                'icon' => 'fa-chart-bar',
                'table' => 'tbl_kpi_residuos', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/kpi-residuos', 'create_route' => 'inspecciones/kpi-residuos/create', 'view_route' => 'inspecciones/kpi-residuos/view',
                'keywords' => ['kpi residuos', 'indicador residuos', 'medición residuos', 'seguimiento residuos', 'cumplimiento PGIRS', 'aprovechamiento residuos'],
                'descripcion_ia' => 'INDICADOR periódico de cumplimiento del programa de residuos sólidos.',
                'actividad_template' => 'Realizar seguimiento periódico a los indicadores del Programa de manejo integral de residuos sólidos',
            ],
            [
                'slug' => 'kpi-plagas', 'label' => 'KPI Plagas', 'group' => 'KPIs Saneamiento',
                'icon' => 'fa-chart-pie',
                'table' => 'tbl_kpi_plagas', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/kpi-plagas', 'create_route' => 'inspecciones/kpi-plagas/create', 'view_route' => 'inspecciones/kpi-plagas/view',
                'keywords' => ['kpi plagas', 'indicador plagas', 'avistamiento plagas', 'monitoreo plagas', 'seguimiento de plagas'],
                'descripcion_ia' => 'INDICADOR periódico de monitoreo de avistamientos/control de plagas.',
                'actividad_template' => 'Realizar seguimiento periódico a los indicadores del Programa de control integrado de plagas',
            ],
            [
                'slug' => 'kpi-agua-potable', 'label' => 'KPI Agua Potable', 'group' => 'KPIs Saneamiento',
                'icon' => 'fa-chart-area',
                'table' => 'tbl_kpi_agua_potable', 'date_col' => 'fecha_inspeccion',
                'list_route' => 'inspecciones/kpi-agua-potable', 'create_route' => 'inspecciones/kpi-agua-potable/create', 'view_route' => 'inspecciones/kpi-agua-potable/view',
                'keywords' => ['kpi agua potable', 'indicador agua', 'medición cloro', 'seguimiento calidad de agua', 'pH agua', 'monitoreo agua potable'],
                'descripcion_ia' => 'INDICADOR periódico de calidad del agua potable (cloro residual, pH, turbidez).',
                'actividad_template' => 'Realizar seguimiento periódico a los indicadores del Programa de abastecimiento y control de agua potable',
            ],
            // ========== Otros ==========
            [
                'slug' => 'planilla-seg-social', 'label' => 'Planilla Seg. Social', 'group' => 'Otros',
                'icon' => 'fa-file-invoice',
                'table' => 'tbl_planilla_ss_inspeccion', 'date_col' => 'created_at', 'estado_col' => null,
                'list_route' => 'inspecciones/planilla-seg-social', 'create_route' => 'inspecciones/planilla-seg-social/create', 'view_route' => 'inspecciones/planilla-seg-social/edit',
                'keywords' => ['planilla seguridad social', 'planilla SS', 'PILA', 'aportes seguridad social', 'verificación pago seguridad social', 'salud pensión riesgos'],
                'descripcion_ia' => 'Verificación del pago de aportes a seguridad social (PILA) de los empleados.',
                'actividad_template' => 'Revisar la planilla de Seguridad Social del administrador, empresa de vigilancia y empresa de aseo',
            ],
            [
                'slug' => 'carta-vigia', 'label' => 'Carta Vigía', 'group' => 'Otros',
                'icon' => 'fa-envelope-open-text',
                'table' => 'tbl_carta_vigia', 'date_col' => 'created_at', 'estado_col' => null,
                'list_route' => 'inspecciones/carta-vigia', 'create_route' => 'inspecciones/carta-vigia', 'view_route' => 'inspecciones/carta-vigia/view',
                'keywords' => ['carta vigía', 'vigía SST', 'designación vigía', 'carta de designación vigía', 'comunicado vigía', 'rol del vigía'],
                'descripcion_ia' => 'Carta de designación o comunicación del Vigía de Seguridad y Salud en el Trabajo.',
                'actividad_template' => 'Asignar el vigía de SST mediante carta firmada por el representante legal',
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
