<?php

/**
 * OttoTableMap — Mapa semántico de tablas para Otto
 *
 * Cada entrada documenta:
 *   - La tabla real en la base de datos
 *   - Qué contiene en términos de negocio
 *   - Para qué preguntas del usuario sirve
 *   - Columnas clave
 *   - Relaciones con otras tablas
 *   - Notas de uso
 *
 * DIRECTIVA GLOBAL (aplica a todas las tablas):
 *   - Por defecto filtrar año actual + estado ABIERTA y/o VENCIDO
 *   - Si el usuario requiere años anteriores o items CERRADOS, debe pedirlo explícitamente
 *   - Cuando se aplique este filtro automático, informar al usuario en la respuesta
 */
class OttoTableMap
{
    /**
     * Directiva global de filtrado que Otto debe aplicar en toda consulta.
     */
    public static function getGlobalDirectives(): string
    {
        return <<<TXT
## DIRECTIVA GLOBAL DE FILTRADO
- Por defecto, **todas las consultas se limitan al año actual** y a registros con estado **ABIERTA** y/o **VENCIDO**.
- Si el usuario necesita consultar **años anteriores** o registros **CERRADOS / históricos**, debe pedirlo de forma **explícita y taxativa**.
- Cuando apliques este filtro automático, **informa al usuario** con un mensaje como: *"Te muestro solo las actividades del año actual y estado abierta. Si necesitas consultar años anteriores o ítems cerrados, indícamelo."*
TXT;
    }

    /**
     * Retorna el mapa en formato compacto para el system prompt de OpenAI.
     * Formato: tabla | descripción | columnas clave | notas críticas
     */
    public static function getPromptBlock(): string
    {
        $map   = self::getMap();
        $lines = [
            "## TABLAS DE NEGOCIO",
            "⚠ REGLA DE VISTAS: Para SELECT usa siempre la vista v_* si existe — ya resuelve IDs a nombres legibles (nombre_cliente, nombre_consultor, etc.). Para INSERT/UPDATE/DELETE usa la tabla tbl_* directamente.",
            "Formato: TABLA(SELECT) / TABLA(WRITE) | qué contiene | columnas clave | notas",
            "",
        ];

        foreach ($map as $e) {
            $cols  = implode(', ', $e['key_columns'] ?? []);
            $notes = $e['notes'] ?? '';
            $pri   = !empty($e['priority']) ? ' ' . $e['priority'] : '';
            $view  = $e['view'] ?? null;

            if ($view) {
                $tableLabel = "`{$view}`(SELECT)/`{$e['table']}`(WRITE)";
            } else {
                $tableLabel = "`{$e['table']}`";
            }

            $line = "{$tableLabel}{$pri} | {$e['description']}";
            if ($cols)  $line .= " | Cols: {$cols}";
            if ($notes) $line .= " | ⚠ {$notes}";
            $lines[] = $line;
        }

        return implode("\n", $lines);
    }

    /**
     * Mapa completo. Ordenado por importancia de negocio y luego alfabético.
     */
    public static function getMap(): array
    {
        return [

            // ═══════════════════════════════════════════════════════════
            // TABLAS MAESTRAS — Base de todo el sistema
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_clientes',
                'priority'    => '(1ª en importancia — FK principal de todo el sistema)',
                'description' => 'Tabla maestra de clientes: conjuntos residenciales, edificios y copropiedades que gestiona Cycloid Talent. Casi todas las demás tablas tienen llave foránea hacia aquí.',
                'use_for'     => [
                    'listar clientes activos/inactivos',
                    'buscar un cliente por nombre',
                    'consultor asignado a un cliente',
                    'tipo de servicio (mensual/bimensual/trimestral/proyecto)',
                    'estado del contrato',
                ],
                'key_columns' => [
                    'id_cliente',
                    'nombre_cliente',
                    'estado (ENUM: activo/inactivo/pendiente)',
                    'id_consultor',
                    'estandares (text — define frecuencia de visita)',
                    'fecha_fin_contrato',
                    'correo_cliente',
                    'ciudad_cliente',
                ],
                'relations'   => ['tbl_clientes.id_consultor → tbl_consultor.id_consultor'],
                'notes'       => 'Buscar siempre con LIKE \'%nombre%\' en nombre_cliente (insensible a mayúsculas). Es la FK raíz: cuando filtres por cliente, siempre parte de aquí.',
            ],

            [
                'table'       => 'tbl_consultor',
                'priority'    => '(2ª en importancia — lista de consultores)',
                'description' => 'Tabla de consultores que visitan los clientes y tienen cuentas asignadas. Es la segunda tabla más importante del sistema.',
                'use_for'     => [
                    'listar consultores',
                    '¿qué clientes tiene asignados X consultor?',
                    '¿quién es el consultor de X cliente?',
                    'cartera de clientes por consultor',
                ],
                'key_columns' => ['id_consultor', 'nombre_consultor', 'correo_consultor', 'rol (ENUM: consultant/admin)'],
                'relations'   => ['tbl_clientes.id_consultor → tbl_consultor.id_consultor'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_pendientes',
                'view'        => 'v_tbl_pendientes',
                'priority'    => '(3ª en importancia — compromisos de visita)',
                'description' => 'Tabla de pendientes o compromisos del cliente registrados durante las visitas del consultor. Es la tercera tabla más importante.',
                'use_for'     => [
                    '¿qué pendientes tiene X cliente?',
                    'compromisos de visita',
                    'seguimiento a pendientes',
                    'items vencidos o próximos a vencer',
                    '¿cuántos pendientes abiertos tiene X?',
                ],
                'key_columns' => [
                    'id_pendientes (PK)',
                    'id_cliente',
                    'id_acta_visita (FK → tbl_acta_visita.id)',
                    'tarea_actividad (text — descripción del pendiente)',
                    'responsable',
                    'estado (ENUM: ABIERTA/CERRADA/SIN RESPUESTA DEL CLIENTE/CERRADA POR FIN CONTRATO)',
                    'fecha_asignacion',
                    'fecha_cierre',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'id_acta_visita → tbl_acta_visita.id'],
                'notes'       => 'Columna de descripción es tarea_actividad (NO detalle_mantenimiento). Aplicar directiva global: año actual + estado ABIERTA por defecto.',
            ],

            [
                'table'       => 'tbl_reporte',
                'view'        => 'v_tbl_reporte',
                'priority'    => '(4ª en importancia — núcleo de documentación)',
                'description' => 'Núcleo de la documentación del sistema. Cuando se hace una inspección o se carga un soporte PDF de un cliente, aquí se almacena el registro. Contiene todos los reportes e informes generados.',
                'use_for'     => [
                    '¿ya tenemos el informe de X cargado?',
                    '¿se subió el soporte de la dotación del todero/vigilante?',
                    'documentos cargados de X cliente',
                    'reportes del mes',
                    '¿qué inspecciones ya están reportadas?',
                ],
                'key_columns' => [
                    'id_reporte (PK)',
                    'id_cliente',
                    'id_report_type (tipo de reporte)',
                    'id_detailreport',
                    'titulo_reporte',
                    'report_url',
                    'estado (ENUM: ABIERTO/GESTIONANDO/CERRADO)',
                    'tag',
                    'created_at',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'id_detailreport identifica el subtipo: 9=actas, 10=locativa, 11=señalización, 12=extintores, 13=botiquín, 14=gabinetes, 15=comunicaciones.',
            ],

            [
                'table'       => 'tbl_vencimientos_mantenimientos',
                'view'        => 'v_tbl_vencimientos_mantenimientos',
                'priority'    => '(6ª en importancia — mantenimientos vencidos o por vencer)',
                'description' => 'Tabla de mantenimientos con sus fechas de vencimiento. Se debe priorizar lo vencido o próximo a vencer.',
                'use_for'     => [
                    '¿qué mantenimientos están vencidos?',
                    '¿qué mantenimientos vencen pronto?',
                    'alertas de mantenimiento',
                    'mantenimientos próximos a vencer de X cliente',
                ],
                'key_columns' => [
                    'id_vencimientos_mmttos (PK)',
                    'id_cliente',
                    'id_mantenimiento (FK → tbl_mantenimientos.id_mantenimiento)',
                    'fecha_vencimiento',
                    'estado_actividad (ENUM: sin ejecutar/ejecutado/CERRADA POR FIN CONTRATO)',
                    'fecha_realizacion',
                    'observaciones',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'id_mantenimiento → tbl_mantenimientos.id_mantenimiento'],
                'notes'       => 'ENUM es sin ejecutar/ejecutado (NO ABIERTA/VENCIDA). PRIORIZAR registros con fecha_vencimiento <= hoy o próximos 30 días. Aplicar directiva global.',
            ],

            // ═══════════════════════════════════════════════════════════
            // PLAN DE TRABAJO
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_pta_cliente',
                'view'        => 'v_tbl_pta_cliente',
                'priority'    => '(tabla principal del plan de trabajo)',
                'description' => 'La tabla principal del plan de trabajo SST de cada cliente. Aquí están todas las actividades programadas.',
                'use_for'     => [
                    '¿qué actividades tiene abiertas X cliente?',
                    'plan de trabajo',
                    'actividades pendientes / en gestión',
                    '¿qué llevo en el plan de trabajo de X?',
                    'porcentaje de avance de actividades',
                ],
                'key_columns' => [
                    'id_ptacliente (PK)',
                    'id_cliente',
                    'actividad_plandetrabajo',
                    'estado_actividad (ENUM: ABIERTA/CERRADA/GESTIONANDO/CERRADA SIN EJECUCIÓN/CERRADA POR FIN CONTRATO)',
                    'fecha_propuesta',
                    'fecha_cierre',
                    'porcentaje_avance',
                    'responsable_definido_paralaactividad',
                    'phva_plandetrabajo',
                    'numeral_plandetrabajo',
                    'tipo_servicio',
                    'semana',
                    'observaciones',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'Aplicar directiva global: año actual + ABIERTA/GESTIONANDO por defecto. Cuando el usuario diga "mañana tengo visita", NO filtrar por fecha — mostrar todas las abiertas. PK es id_ptacliente (no id).',
            ],

            [
                'table'       => 'tbl_pta_cliente_audit',
                'view'        => 'v_tbl_pta_cliente_audit',
                'description' => 'Huella de cambios del plan de trabajo. Registra cada modificación hecha a una actividad del plan de trabajo.',
                'use_for'     => [
                    '¿cuándo se cerró X actividad de X cliente?',
                    'historial de cambios en el plan de trabajo',
                    'auditoría de modificaciones',
                ],
                'key_columns' => ['id_audit (PK)', 'id_ptacliente', 'id_cliente', 'campo_modificado', 'valor_anterior', 'valor_nuevo', 'nombre_usuario', 'rol_usuario', 'fecha_accion'],
                'relations'   => ['id_ptacliente → tbl_pta_cliente.id_ptacliente'],
                'notes'       => 'Tabla de auditoría — no contiene el estado actual sino el historial de cambios.',
            ],

            [
                'table'       => 'tbl_pta_transiciones',
                'view'        => 'v_tbl_pta_transiciones',
                'description' => 'Huella de cambio de estatus de actividades del plan de trabajo (de ABIERTA a CERRADA, etc.). Muy útil para saber exactamente cuándo se cerró una actividad.',
                'use_for'     => [
                    '¿cuándo se cerró X actividad?',
                    'fecha de cierre de una actividad',
                    'transiciones de estado del plan de trabajo',
                ],
                'key_columns' => ['id_transicion (PK)', 'id_ptacliente', 'id_cliente', 'estado_anterior', 'estado_nuevo', 'nombre_usuario', 'fecha_transicion'],
                'relations'   => ['id_ptacliente → tbl_pta_cliente.id_ptacliente'],
                'notes'       => 'Consultar aquí cuando se necesite la fecha exacta de cierre de una actividad, no en tbl_pta_cliente.',
            ],

            [
                'table'       => 'tbl_inventario_actividades_plandetrabajo',
                'description' => 'Inventario maestro de actividades disponibles para el plan de trabajo. Muy útil cuando Otto debe sugerir un plan de trabajo o decir qué se puede hacer para un cliente.',
                'use_for'     => [
                    '¿qué actividades se pueden incluir en el plan de trabajo?',
                    'sugerir plan de trabajo para X cliente',
                    '¿qué actividades existen para el numeral X?',
                ],
                'key_columns' => ['id_inventario_actividades_plandetrabajo (PK)', 'phva_plandetrabajo', 'numeral_plandetrabajo', 'actividad_plandetrabajo', 'responsable_sugerido_plandetrabajo'],
                'relations'   => [],
                'notes'       => 'Es un catálogo/maestro — no tiene id_cliente. Usarlo para sugerencias y construcción de planes.',
            ],

            [
                'table'       => 'historial_resumen_plan_trabajo',
                'view'        => 'v_historial_resumen_plan_trabajo',
                'description' => 'Fotografía histórica mensual del avance del plan de trabajo de cada cliente (cuántas actividades abiertas/cerradas por período).',
                'use_for'     => [
                    '¿cómo ha avanzado el plan de trabajo de X históricamente?',
                    'comparar avance mes a mes',
                    'tendencia de cierre de actividades',
                ],
                'key_columns' => ['id_cliente', 'nombre_cliente', 'total_actividades', 'actividades_abiertas', 'porcentaje_abiertas', 'fecha_extraccion'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'No refleja estado actual — para eso usar tbl_pta_cliente. NO tiene prefijo tbl_.',
            ],

            // ═══════════════════════════════════════════════════════════
            // ESTÁNDARES MÍNIMOS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'estandares',
                'description' => 'Tabla maestra que define la categoría de servicio: mensual, bimensual, trimestral o proyecto. Se referencia desde tbl_clientes.',
                'use_for'     => ['¿qué tipo de servicio tiene X cliente?', 'frecuencia de visita', 'categoría del contrato'],
                'key_columns' => ['id_estandar (PK)', 'nombre'],
                'relations'   => ['tbl_clientes.estandares referencia estandares.id_estandar'],
                'notes'       => 'No tiene prefijo tbl_.',
            ],

            [
                'table'       => 'evaluacion_inicial_sst',
                'view'        => 'v_evaluacion_inicial_sst',
                'description' => 'Calificación actual de los Estándares Mínimos del SG-SST para cada cliente. Es el instrumento de medición en tiempo real.',
                'use_for'     => [
                    '¿cuál es la calificación de estándares mínimos de X cliente?',
                    '¿qué actividades no han sido calificadas?',
                    '¿cuáles no cumplen?',
                    'porcentaje de cumplimiento SST',
                ],
                'key_columns' => ['id_ev_ini (PK)', 'id_cliente', 'item', 'criterio', 'calificacion (decimal)', 'nivel_de_evaluacion', 'observaciones', 'puntaje_cuantitativo'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'No tiene prefijo tbl_.',
            ],

            [
                'table'       => 'historial_resumen_estandares',
                'view'        => 'v_historial_resumen_estandares',
                'description' => 'Fotografía histórica mensual del avance de cada cliente en la evaluación de Estándares Mínimos.',
                'use_for'     => [
                    '¿cómo ha evolucionado X en estándares mínimos?',
                    'comparar calificación mes anterior vs actual',
                    'tendencia de cumplimiento SST',
                ],
                'key_columns' => ['id_cliente', 'nombre_cliente', 'porcentaje_cumplimiento (decimal)', 'total_valor', 'total_puntaje', 'fecha_extraccion'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'No tiene prefijo tbl_. Para calificación actual usar evaluacion_inicial_sst.',
            ],

            // ═══════════════════════════════════════════════════════════
            // VISITAS Y ACTAS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_acta_visita',
                'view'        => 'v_tbl_acta_visita',
                'description' => 'Datos de las actas de visita realizadas a los clientes.',
                'use_for'     => [
                    '¿cuándo fue la última visita a X?',
                    'historial de visitas',
                    'actas de visita del mes',
                ],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_visita', 'motivo', 'modalidad', 'estado (ENUM: borrador/pendiente_firma/completo)', 'created_at'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_acta_visita_temas.id_acta_visita → id'],
                'notes'       => 'Aplicar directiva global: año actual por defecto.',
            ],

            [
                'table'       => 'tbl_acta_visita_temas',
                'view'        => 'v_tbl_acta_visita_temas',
                'description' => 'Tabla hija de tbl_acta_visita. Contiene los temas tratados en cada acta de visita.',
                'use_for'     => ['¿qué temas se trataron en la visita de X?', 'detalle de acta'],
                'key_columns' => ['id', 'id_acta_visita', 'descripcion (text)', 'orden'],
                'relations'   => ['id_acta_visita → tbl_acta_visita.id'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // AGENDAMIENTO Y CICLOS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_agendamientos',
                'view'        => 'v_tbl_agendamientos',
                'description' => 'Clientes con fecha concertada para visita. Es la agenda futura de visitas.',
                'use_for'     => [
                    '¿qué clientes tengo agendados?',
                    '¿cuándo tengo visita con X?',
                    'agenda de la semana / del mes',
                    'próximas visitas',
                ],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_visita', 'frecuencia (ENUM: mensual/bimensual/trimestral)', 'estado (ENUM: pendiente/confirmado/completado/cancelado)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_ciclos_visita',
                'view'        => 'v_tbl_ciclos_visita',
                'description' => 'Estado del ciclo de visita de cada cliente: si tiene agendamiento, si fue visitado, según la frecuencia del contrato.',
                'use_for'     => [
                    '¿qué clientes no han sido visitados este mes?',
                    'clientes pendientes de visita',
                    'ciclo de visitas',
                ],
                'key_columns' => ['id', 'id_cliente', 'nombre_cliente', 'id_consultor', 'nombre_consultor', 'anio', 'mes_esperado', 'estandar (frecuencia de visita: mensual/bimensual/trimestral/etc)', 'id_agendamiento', 'id_acta', 'estatus_agenda (ENUM: pendiente/cumple/incumple)', 'estatus_mes (ENUM: pendiente/cumple/incumple)', 'fecha_visita'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'id_agendamiento → tbl_agendamientos.id'],
                'use_for'     => ['¿qué clientes no han sido visitados este mes?', 'clientes pendientes de visita', 'ciclo de visitas', 'frecuencia de visita de un cliente', '¿cada cuánto visitan a X?'],
                'notes'       => 'La columna estandar contiene la frecuencia de visita (mensual, bimensual, trimestral). Para consultar la frecuencia de un cliente usar: SELECT nombre_cliente, estandar FROM v_tbl_ciclos_visita WHERE nombre_cliente LIKE \'%nombre%\' LIMIT 1',
            ],

            // ═══════════════════════════════════════════════════════════
            // CAPACITACIONES
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_cronog_capacitacion',
                'view'        => 'v_tbl_cronog_capacitacion',
                'description' => 'Cronograma de capacitaciones SST programadas para cada cliente. Tabla importante para seguimiento de formación.',
                'use_for'     => [
                    '¿qué capacitaciones tiene programadas X?',
                    'cronograma de capacitaciones',
                    '¿qué capacitaciones faltan por ejecutar?',
                    'capacitaciones del año',
                ],
                'key_columns' => ['id_cronograma_capacitacion (PK)', 'id_cliente', 'nombre_capacitacion', 'objetivo_capacitacion', 'fecha_programada', 'fecha_de_realizacion', 'estado (ENUM: PROGRAMADA/EJECUTADA/CANCELADA POR EL CLIENTE/REPROGRAMADA/CERRADA POR FIN CONTRATO)', 'nombre_del_capacitador', 'numero_de_asistentes_a_capacitacion'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'Aplicar directiva global: año actual + PROGRAMADA/REPROGRAMADA por defecto.',
            ],

            [
                'table'       => 'tbl_reporte_capacitacion',
                'view'        => 'v_tbl_reporte_capacitacion',
                'description' => 'Capacitaciones que efectivamente sí se realizaron y fueron reportadas para los clientes.',
                'use_for'     => [
                    '¿cuántas capacitaciones se han hecho en X?',
                    'capacitaciones ejecutadas',
                    '¿ya se reportó la capacitación de X tema?',
                ],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'nombre_capacitacion', 'fecha_capacitacion', 'numero_asistentes', 'ruta_pdf', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_asistencia_induccion',
                'view'        => 'v_tbl_asistencia_induccion',
                'description' => 'Listado de personas que asistieron a inducción SST en un cliente.',
                'use_for'     => [
                    '¿cuántas personas han recibido inducción en X?',
                    'lista de asistentes a inducción',
                    'inducciones realizadas',
                ],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_sesion', 'tema', 'tipo_charla (ENUM: induccion_reinduccion/reunion/charla/capacitacion/otros_temas)', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_evaluacion_induccion',
                'view'        => 'v_tbl_evaluacion_induccion',
                'description' => 'Datos generales de las evaluaciones de inducción realizadas a personas de un cliente.',
                'use_for'     => ['evaluaciones de inducción', '¿se evaluó a X persona?', 'resultados de inducción'],
                'key_columns' => ['id', 'id_asistencia_induccion', 'id_cliente', 'titulo', 'token', 'estado (ENUM: activo/cerrado)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_evaluacion_induccion_respuesta.id_evaluacion → id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_evaluacion_induccion_respuesta',
                'view'        => 'v_tbl_evaluacion_induccion_respuesta',
                'description' => 'Respuestas registradas por los usuarios en las evaluaciones de inducción.',
                'use_for'     => ['detalle de respuestas de evaluación de inducción', '¿qué respondió X en la evaluación?'],
                'key_columns' => ['id', 'id_evaluacion', 'nombre', 'cedula', 'cargo', 'empresa_contratante', 'respuestas (json)', 'calificacion'],
                'relations'   => ['id_evaluacion → tbl_evaluacion_induccion.id'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // INSPECCIONES
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_inspeccion_locativa',
                'view'        => 'v_tbl_inspeccion_locativa',
                'description' => 'Datos de las inspecciones locativas realizadas en las instalaciones de un cliente.',
                'use_for'     => ['inspección locativa', '¿cuándo fue la última inspección locativa de X?', 'estado de las instalaciones'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'observaciones', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_hallazgo_locativo.id_inspeccion → id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_hallazgo_locativo',
                'view'        => 'v_tbl_hallazgo_locativo',
                'description' => 'Hallazgos identificados durante las inspecciones locativas.',
                'use_for'     => ['hallazgos locativos', '¿qué hallazgos se encontraron en X?', 'estado de hallazgos'],
                'key_columns' => ['id', 'id_inspeccion (FK → tbl_inspeccion_locativa.id)', 'descripcion (text)', 'imagen', 'imagen_correccion', 'estado', 'fecha_hallazgo', 'fecha_correccion', 'orden'],
                'relations'   => ['id_inspeccion → tbl_inspeccion_locativa.id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_inspeccion_extintores',
                'view'        => 'v_tbl_inspeccion_extintores',
                'description' => 'Datos de las inspecciones realizadas a los extintores de un cliente.',
                'use_for'     => ['inspección de extintores', '¿cuándo fue la última inspección de extintores de X?'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'numero_extintores_totales', 'cantidad_abc', 'cantidad_co2', 'recomendaciones_generales', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_extintor_detalle.id_inspeccion → id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_extintor_detalle',
                'view'        => 'v_tbl_extintor_detalle',
                'description' => 'Datos detallados de cada extintor registrado en el sistema (uno por fila).',
                'use_for'     => ['detalle de extintores', '¿cuántos extintores tiene X?', 'estado de cada extintor'],
                'key_columns' => ['id', 'id_inspeccion (FK → tbl_inspeccion_extintores.id)', 'presion', 'manometro', 'fecha_vencimiento', 'foto', 'observaciones', 'orden'],
                'relations'   => ['id_inspeccion → tbl_inspeccion_extintores.id'],
                'notes'       => 'Aplicar directiva global para vencimientos.',
            ],

            [
                'table'       => 'tbl_inspeccion_gabinetes',
                'view'        => 'v_tbl_inspeccion_gabinetes',
                'description' => 'Datos de las inspecciones realizadas a los gabinetes contra incendio.',
                'use_for'     => ['inspección de gabinetes', '¿cuándo fue la última inspección de gabinetes de X?'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'tiene_gabinetes (SI/NO)', 'cantidad_gabinetes', 'tiene_detectores (SI/NO)', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_gabinete_detalle.id_inspeccion → id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_gabinete_detalle',
                'view'        => 'v_tbl_gabinete_detalle',
                'description' => 'Datos detallados de cada gabinete contra incendio registrado.',
                'use_for'     => ['detalle de gabinetes contra incendio', '¿cuántos gabinetes tiene X?'],
                'key_columns' => ['id', 'id_inspeccion (FK → tbl_inspeccion_gabinetes.id)', 'numero', 'ubicacion', 'tiene_manguera (SI/NO)', 'tiene_extintor (SI/NO)', 'estado', 'foto'],
                'relations'   => ['id_inspeccion → tbl_inspeccion_gabinetes.id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_inspeccion_botiquin',
                'view'        => 'v_tbl_inspeccion_botiquin',
                'description' => 'Datos de las inspecciones realizadas a los botiquines.',
                'use_for'     => ['inspección de botiquín', '¿cuándo fue la última inspección de botiquín de X?'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'ubicacion_botiquin', 'tipo_botiquin', 'estado_botiquin', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_elemento_botiquin.id_inspeccion → id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_elemento_botiquin',
                'view'        => 'v_tbl_elemento_botiquin',
                'description' => 'Elementos o componentes que conforman el botiquín para su control e inspección.',
                'use_for'     => ['elementos del botiquín', '¿qué le falta al botiquín de X?', 'inventario botiquín'],
                'key_columns' => ['id', 'id_inspeccion (FK → tbl_inspeccion_botiquin.id)', 'clave', 'cantidad', 'estado', 'fecha_vencimiento', 'orden'],
                'relations'   => ['id_inspeccion → tbl_inspeccion_botiquin.id'],
                'notes'       => 'Aplicar directiva para vencimientos de elementos.',
            ],

            [
                'table'       => 'tbl_inspeccion_senalizacion',
                'view'        => 'v_tbl_inspeccion_senalizacion',
                'description' => 'Datos de las inspecciones realizadas a la señalización de seguridad.',
                'use_for'     => ['inspección de señalización', '¿cuándo se inspeccionó la señalización de X?'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'calificacion (decimal)', 'descripcion_cualitativa', 'conteo_no_aplica', 'conteo_no_cumple', 'conteo_total', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_inspeccion_comunicaciones',
                'view'        => 'v_tbl_inspeccion_comunicaciones',
                'description' => 'Datos de las inspecciones relacionadas con equipos o medios de comunicación para emergencias.',
                'use_for'     => ['inspección de comunicaciones', 'equipos de comunicación para emergencias', '¿estado de los radios o medios de comunicación de X?'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'observaciones_finales', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_inspeccion_recursos_seguridad',
                'view'        => 'v_tbl_inspeccion_recursos_seguridad',
                'description' => 'Datos de las inspecciones de los recursos o elementos de seguridad disponibles en el cliente.',
                'use_for'     => ['recursos de seguridad', 'inspección de elementos de seguridad', '¿qué recursos de seguridad tiene X?'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'obs_lamparas', 'obs_pasamanos', 'obs_iluminacion', 'observaciones', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_auditoria_zona_residuos',
                'view'        => 'v_tbl_auditoria_zona_residuos',
                'description' => 'Inspección / auditoría de la zona de residuos (cuarto de basuras) de un cliente.',
                'use_for'     => ['zona de residuos', 'cuarto de basuras', 'auditoría de residuos'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'observaciones', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // DOTACIONES
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_dotacion_aseadora',
                'view'        => 'v_tbl_dotacion_aseadora',
                'description' => 'Datos de las inspecciones de las dotaciones de las aseadoras.',
                'use_for'     => ['dotación de aseadoras', '¿ya se revisó la dotación de aseadoras de X?', 'inspección dotación aseadora'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'contratista', 'nombre_cargo', 'concepto_final', 'observaciones', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_dotacion_todero',
                'view'        => 'v_tbl_dotacion_todero',
                'description' => 'Datos de las inspecciones de las dotaciones de los toderos.',
                'use_for'     => ['dotación de toderos', '¿ya se revisó la dotación del todero de X?', 'inspección dotación todero'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'contratista', 'nombre_cargo', 'concepto_final', 'observaciones', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_dotacion_vigilante',
                'view'        => 'v_tbl_dotacion_vigilante',
                'description' => 'Datos de las inspecciones de las dotaciones de los vigilantes.',
                'use_for'     => ['dotación de vigilantes', '¿ya se revisó la dotación del vigilante de X?', 'inspección dotación vigilante'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'contratista', 'nombre_cargo', 'estado_uniforme', 'estado_calzado', 'concepto_final', 'observaciones', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // SIMULACROS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_preparacion_simulacro',
                'view'        => 'v_tbl_preparacion_simulacro',
                'description' => 'Datos de preparación y planeación de los simulacros de emergencia.',
                'use_for'     => ['planificación de simulacro', '¿está planeado el simulacro de X?', 'preparación simulacro'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_simulacro', 'evento_simulado (ENUM: sismo)', 'alcance_simulacro (ENUM: total/parcial)', 'nombre_brigadista_lider', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_evaluacion_simulacro',
                'view'        => 'v_tbl_evaluacion_simulacro',
                'description' => 'Datos de evaluación de los simulacros ejecutados.',
                'use_for'     => ['resultado del simulacro', '¿cómo le fue a X en el simulacro?', 'calificación simulacro'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'evento_simulado (ENUM: Sismo/Incendio/Evacuación)', 'alcance_simulacro (ENUM: Total/Parcial)', 'evaluacion_cuantitativa', 'evaluacion_cualitativa', 'observaciones', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // PLANES Y PROGRAMAS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_plan_emergencia',
                'view'        => 'v_tbl_plan_emergencia',
                'description' => 'Datos correspondientes al plan de emergencia de cada cliente.',
                'use_for'     => ['plan de emergencia', '¿tiene plan de emergencia X?', '¿está actualizado el plan de emergencia?'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_visita', 'observaciones', 'ruta_pdf', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_plan_saneamiento',
                'view'        => 'v_tbl_plan_saneamiento',
                'description' => 'Datos correspondientes al plan de saneamiento básico de cada cliente.',
                'use_for'     => ['plan de saneamiento', 'saneamiento básico', '¿tiene plan de saneamiento X?'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_programa', 'nombre_responsable', 'ruta_pdf', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_programa_agua_potable',
                'view'        => 'v_tbl_programa_agua_potable',
                'description' => 'Datos correspondientes al programa de agua potable.',
                'use_for'     => ['programa de agua potable', '¿tiene programa de agua potable X?'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_programa', 'nombre_responsable', 'cantidad_tanques', 'capacidad_total', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'Ver indicador en tbl_kpi_agua_potable.',
            ],

            [
                'table'       => 'tbl_programa_limpieza',
                'view'        => 'v_tbl_programa_limpieza',
                'description' => 'Datos correspondientes al programa de limpieza y desinfección.',
                'use_for'     => ['programa de limpieza', 'programa de desinfección', '¿tiene programa de limpieza X?'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_programa', 'nombre_responsable', 'ruta_pdf', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'Ver indicador en tbl_kpi_limpieza.',
            ],

            [
                'table'       => 'tbl_programa_plagas',
                'view'        => 'v_tbl_programa_plagas',
                'description' => 'Datos correspondientes al programa de control de plagas.',
                'use_for'     => ['control de plagas', 'programa de plagas', 'fumigación'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_programa', 'nombre_responsable', 'ruta_pdf', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'Ver indicador en tbl_kpi_plagas.',
            ],

            [
                'table'       => 'tbl_programa_residuos',
                'view'        => 'v_tbl_programa_residuos',
                'description' => 'Datos correspondientes al programa de gestión de residuos.',
                'use_for'     => ['gestión de residuos', 'programa de residuos', 'manejo de basuras'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_programa', 'nombre_responsable', 'ruta_pdf', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // KPIs / INDICADORES
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_kpi_agua_potable',
                'view'        => 'v_tbl_kpi_agua_potable',
                'description' => 'Indicadores del programa de agua potable.',
                'use_for'     => ['indicador agua potable', 'KPI agua', '¿cómo va el programa de agua de X?'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'indicador', 'cumplimiento (decimal)', 'valor_numerador', 'valor_denominador', 'calificacion_cualitativa', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_kpi_limpieza',
                'view'        => 'v_tbl_kpi_limpieza',
                'description' => 'Indicadores del programa de limpieza.',
                'use_for'     => ['indicador de limpieza', 'KPI limpieza', '¿cómo va el programa de limpieza de X?'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'indicador', 'cumplimiento (decimal)', 'valor_numerador', 'valor_denominador', 'calificacion_cualitativa', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_kpi_plagas',
                'view'        => 'v_tbl_kpi_plagas',
                'description' => 'Indicadores del programa de control de plagas.',
                'use_for'     => ['indicador control de plagas', 'KPI plagas', '¿cómo va el control de plagas de X?'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'indicador', 'cumplimiento (decimal)', 'valor_numerador', 'valor_denominador', 'calificacion_cualitativa', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // MANTENIMIENTOS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_mantenimientos',
                'description' => 'Tabla maestra de ítems de mantenimiento (catálogo de tipos de mantenimiento).',
                'use_for'     => ['¿qué tipos de mantenimiento existen?', 'catálogo de mantenimientos'],
                'key_columns' => ['id_mantenimiento (PK)', 'detalle_mantenimiento'],
                'relations'   => ['tbl_vencimientos_mantenimientos.id_mantenimiento → id_mantenimiento'],
                'notes'       => 'Es un maestro/catálogo. Para estado y vencimientos, ver tbl_vencimientos_mantenimientos.',
            ],

            // ═══════════════════════════════════════════════════════════
            // PRESUPUESTO SST
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_presupuesto_sst',
                'view'        => 'v_tbl_presupuesto_sst',
                'description' => 'Datos generales de los presupuestos de Seguridad y Salud en el Trabajo de cada cliente.',
                'use_for'     => ['presupuesto SST', '¿tiene presupuesto definido X?', 'presupuesto de seguridad'],
                'key_columns' => ['id_presupuesto (PK)', 'id_cliente', 'anio', 'mes_inicio', 'estado (ENUM: borrador/aprobado/cerrado)', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_presupuesto_items.id_presupuesto → id_presupuesto'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_presupuesto_detalle',
                'view'        => 'v_tbl_presupuesto_detalle',
                'description' => 'Detalles o conceptos asociados a cada presupuesto SST.',
                'use_for'     => ['detalle del presupuesto SST', '¿en qué se va a invertir?'],
                'key_columns' => ['id_detalle (PK)', 'id_item', 'mes', 'anio', 'presupuestado (decimal)', 'ejecutado (decimal)', 'notas'],
                'relations'   => ['id_item → tbl_presupuesto_items.id_item'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_presupuesto_items',
                'view'        => 'v_tbl_presupuesto_items',
                'description' => 'Ítems o elementos que conforman los presupuestos.',
                'use_for'     => ['ítems de presupuesto', 'detalle de ítems presupuestados'],
                'key_columns' => ['id_item (PK)', 'id_presupuesto', 'id_categoria', 'codigo_item', 'actividad', 'orden'],
                'relations'   => ['id_presupuesto → tbl_presupuesto_sst.id_presupuesto', 'id_categoria → tbl_presupuesto_categorias.id_categoria'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_presupuesto_categorias',
                'description' => 'Categorías utilizadas para clasificar los presupuestos SST.',
                'use_for'     => ['categorías de presupuesto'],
                'key_columns' => ['id_categoria (PK)', 'codigo', 'nombre', 'orden'],
                'relations'   => [],
                'notes'       => 'Tabla maestra/catálogo.',
            ],

            // ═══════════════════════════════════════════════════════════
            // SEGURIDAD Y RIESGOS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_probabilidad_peligros',
                'view'        => 'v_tbl_probabilidad_peligros',
                'description' => 'Datos de valoración o probabilidad de ocurrencia de los peligros identificados en un cliente.',
                'use_for'     => ['peligros identificados', 'matriz de peligros', 'probabilidad de accidente', 'valoración de riesgos'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'sismos', 'inundaciones', 'incendios', 'explosiones', 'asalto_hurto (cada uno ENUM: poco_probable/probable/muy_probable)', 'observaciones', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_matrices',
                'view'        => 'v_tbl_matrices',
                'description' => 'Registro de qué clientes ya tienen el archivo Excel de matriz de riesgos y matriz de EPPs cargado.',
                'use_for'     => ['¿tiene la matriz de riesgos X?', '¿ya está la matriz de EPPs de X?', 'matrices del cliente'],
                'key_columns' => ['id_matriz (PK)', 'id_cliente', 'tipo', 'descripcion', 'enlace (text)', 'created_at'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_matriz_vulnerabilidad',
                'view'        => 'v_tbl_matriz_vulnerabilidad',
                'description' => 'Datos de la matriz de vulnerabilidad por amenazas de cada cliente.',
                'use_for'     => ['matriz de vulnerabilidad', 'amenazas identificadas', 'vulnerabilidad del cliente'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'c1..c25 (cada uno ENUM: a/b/c — nivel de vulnerabilidad)', 'observaciones', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // BRIGADISTAS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_hv_brigadista',
                'view'        => 'v_tbl_hv_brigadista',
                'description' => 'Hojas de vida e información general de los brigadistas de cada cliente.',
                'use_for'     => ['brigadistas', '¿cuántos brigadistas tiene X?', 'hoja de vida de brigadista', '¿están capacitados los brigadistas?'],
                'key_columns' => ['id', 'id_cliente', 'nombre_completo', 'documento_identidad', 'fecha_inscripcion', 'eps', 'rh', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // INFORMES Y SOPORTES
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_informe_avances',
                'view'        => 'v_tbl_informe_avances',
                'description' => 'Informes de avance de actividades o compromisos del cliente.',
                'use_for'     => ['informe de avances', '¿se ha generado informe de avances para X?', 'reporte de avances'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_desde', 'fecha_hasta', 'anio', 'puntaje_anterior', 'puntaje_actual', 'diferencia_neta', 'ruta_pdf', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_planillas_seguridad_social',
                'description' => 'Soportes o registros de las planillas de seguridad social de los clientes.',
                'use_for'     => ['planilla de seguridad social', 'soporte de seguridad social', 'aportes'],
                'key_columns' => ['id', 'mes_aportes', 'archivo_pdf', 'fecha_cargue', 'cantidad_envios', 'estado_envio (ENUM: sin_enviar/enviado)', 'notas'],
                'relations'   => [],
                'notes'       => 'Esta tabla NO tiene columna id_cliente — es un repositorio global de planillas, no segmentado por cliente.',
            ],

            [
                'table'       => 'tbl_contratos',
                'view'        => 'v_tbl_contratos',
                'description' => 'Historial de contratos de los clientes.',
                'use_for'     => ['contratos', '¿cuándo vence el contrato de X?', 'historial de contratos', '¿está activo el contrato?'],
                'key_columns' => ['id_contrato (PK)', 'id_cliente', 'numero_contrato', 'fecha_inicio', 'fecha_fin', 'valor_contrato (decimal)', 'tipo_contrato (ENUM: inicial/renovacion/ampliacion)', 'estado (ENUM: activo/vencido/cancelado/renovado)', 'estado_firma (ENUM: sin_enviar/pendiente_firma/firmado)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // USUARIOS Y ACCESO
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_sesiones_usuario',
                'view'        => 'v_tbl_sesiones_usuario',
                'description' => 'Registro de sesiones de usuarios. Muy útil para saber cuándo entró por última vez un cliente a la plataforma. Los clientes a veces dicen que no hacemos el trabajo, pero en realidad ellos nunca entran a revisar la información.',
                'use_for'     => [
                    '¿cuándo entró por última vez el cliente X a la plataforma?',
                    '¿el cliente revisa la plataforma?',
                    'última conexión de X',
                    'actividad de usuarios',
                ],
                'key_columns' => ['id_sesion (PK)', 'id_usuario', 'inicio_sesion', 'fin_sesion', 'duracion_segundos', 'ip_address', 'estado (ENUM: activa/cerrada/expirada)'],
                'relations'   => ['id_usuario → tbl_usuarios.id_usuario'],
                'notes'       => 'SOLO LECTURA — no se puede modificar ni eliminar. Muy útil para demostrar al cliente que sí se está trabajando.',
            ],

            [
                'table'       => 'tbl_usuarios',
                'description' => 'Tabla de usuarios del sistema.',
                'use_for'     => ['¿quién es el usuario de X cliente?', 'usuarios registrados'],
                'key_columns' => ['id_usuario (PK)', 'email', 'nombre_completo', 'tipo_usuario (ENUM: admin/consultant/client)', 'id_entidad (int — FK a cliente o consultor según tipo)', 'estado (ENUM: activo/inactivo/pendiente/bloqueado)'],
                'relations'   => ['id_entidad → tbl_clientes.id_cliente (cuando tipo_usuario=client)'],
                'notes'       => 'SOLO LECTURA — no se puede modificar ni eliminar.',
            ],

            [
                'table'       => 'tbl_roles',
                'description' => 'Roles de acceso del sistema. Define quién puede acceder a qué.',
                'use_for'     => ['roles del sistema', '¿qué rol tiene X usuario?'],
                'key_columns' => ['id_rol (PK)', 'nombre_rol', 'descripcion', 'permisos (json)'],
                'relations'   => [],
                'notes'       => 'SOLO LECTURA — neurálgico para la seguridad del sistema. Nunca modificar.',
            ],

            // ═══════════════════════════════════════════════════════════
            // ACTA VISITA — tablas hijas
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_acta_visita_fotos',
                'view'        => 'v_tbl_acta_visita_fotos',
                'description' => 'Fotos adjuntadas a las actas de visita.',
                'use_for'     => ['fotos del acta', '¿qué fotos tiene el acta X?'],
                'key_columns' => ['id', 'id_acta_visita (FK → tbl_acta_visita.id)', 'ruta_foto', 'descripcion', 'orden'],
                'relations'   => ['id_acta_visita → tbl_acta_visita.id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_acta_visita_integrantes',
                'view'        => 'v_tbl_acta_visita_integrantes',
                'description' => 'Personas que participaron en cada acta de visita.',
                'use_for'     => ['integrantes del acta', '¿quiénes participaron en la visita de X?'],
                'key_columns' => ['id', 'id_acta_visita', 'nombre_integrante', 'cargo', 'empresa', 'firma'],
                'relations'   => ['id_acta_visita → tbl_acta_visita.id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_acta_visita_pta',
                'view'        => 'v_tbl_acta_visita_pta',
                'description' => 'Actividades del plan de trabajo tratadas / vinculadas a un acta de visita.',
                'use_for'     => ['actividades tratadas en visita', '¿qué actividades se revisaron en la visita de X?'],
                'key_columns' => ['id', 'id_acta_visita', 'id_ptacliente (FK → tbl_pta_cliente.id_ptacliente)'],
                'relations'   => ['id_acta_visita → tbl_acta_visita.id', 'id_ptacliente → tbl_pta_cliente.id_ptacliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // INDUCCIÓN — tabla hija de asistencias
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_asistencia_induccion_asistente',
                'view'        => 'v_tbl_asistencia_induccion_asistente',
                'description' => 'Personas que asistieron individualmente a cada sesión de inducción.',
                'use_for'     => ['listado de asistentes a inducción', '¿quién asistió a la inducción de X?'],
                'key_columns' => ['id', 'id_asistencia (FK → tbl_asistencia_induccion.id)', 'nombre_completo', 'documento', 'cargo', 'firma'],
                'relations'   => ['id_asistencia → tbl_asistencia_induccion.id'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // SEÑALIZACIÓN — tabla hija
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_item_senalizacion',
                'view'        => 'v_tbl_item_senalizacion',
                'description' => 'Ítems individuales de señalización evaluados en cada inspección de señalización.',
                'use_for'     => ['ítems de señalización', 'detalle señales de seguridad', '¿qué señales tiene X?'],
                'key_columns' => ['id', 'id_inspeccion (FK → tbl_inspeccion_senalizacion.id)', 'descripcion', 'cumplimiento (ENUM: cumple/no_cumple/no_aplica)', 'foto', 'orden'],
                'relations'   => ['id_inspeccion → tbl_inspeccion_senalizacion.id'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // SEGURIDAD SOCIAL / VIGILANCIA
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_carta_vigia',
                'view'        => 'v_tbl_carta_vigia',
                'description' => 'Cartas de vigía generadas para los clientes (comunicado formal de hallazgos o novedades).',
                'use_for'     => ['carta vigía', '¿se generó carta vigía para X?', 'comunicados formales al cliente'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha', 'contenido (text)', 'estado (ENUM: borrador/enviado)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_vigias',
                'view'        => 'v_tbl_vigias',
                'description' => 'Registro de vigías o personas de vigilancia de los clientes.',
                'use_for'     => ['vigías del cliente', '¿cuántos vigías tiene X?', 'personal de vigilancia'],
                'key_columns' => ['id', 'id_cliente', 'nombre_completo', 'documento', 'cargo', 'estado'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // LOOKER STUDIO / KPI RESIDUOS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_lookerstudio',
                'view'        => 'v_tbl_lookerstudio',
                'description' => 'URLs de dashboards de Looker Studio asociados a cada cliente.',
                'use_for'     => ['dashboard de X', 'Looker Studio', 'enlace de indicadores del cliente'],
                'key_columns' => ['id', 'id_cliente', 'url (text)', 'descripcion', 'created_at'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_kpi_residuos',
                'view'        => 'v_tbl_kpi_residuos',
                'description' => 'Indicadores del programa de gestión de residuos.',
                'use_for'     => ['KPI residuos', 'indicador residuos', '¿cómo va el manejo de residuos de X?'],
                'key_columns' => ['id', 'id_cliente', 'id_consultor', 'fecha_inspeccion', 'indicador', 'cumplimiento (decimal)', 'calificacion_cualitativa', 'estado (ENUM: borrador/completo)'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

        ];
    }
}
