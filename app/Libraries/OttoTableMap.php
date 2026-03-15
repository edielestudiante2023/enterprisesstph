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
            "Formato: TABLA | qué contiene | columnas clave | notas",
            "",
        ];

        foreach ($map as $e) {
            $cols  = implode(', ', $e['key_columns'] ?? []);
            $notes = $e['notes'] ?? '';
            $pri   = !empty($e['priority']) ? ' ' . $e['priority'] : '';
            $line  = "`{$e['table']}`{$pri} | {$e['description']}";
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
                    'estado (ENUM: activo, inactivo, pendiente)',
                    'id_consultor',
                    'estandares (FK → estandares.id — define frecuencia de visita)',
                    'fecha_fin_contrato',
                    'correo_cliente',
                ],
                'relations'   => ['estandares → estandares.id', 'tbl_clientes.id_consultor → tbl_consultor.id_consultor'],
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
                'key_columns' => ['id_consultor', 'nombre_consultor', 'correo_consultor', 'estado'],
                'relations'   => ['tbl_clientes.id_consultor → tbl_consultor.id_consultor'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_pendientes',
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
                    'id',
                    'id_cliente',
                    'id_acta_visita (FK → tbl_acta_visita.id)',
                    'detalle_mantenimiento (descripción del pendiente)',
                    'estado (ABIERTA / CERRADA / VENCIDA)',
                    'fecha_compromiso',
                    'fecha_cierre',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'id_acta_visita → tbl_acta_visita.id'],
                'notes'       => 'Aplicar directiva global: año actual + estado ABIERTA/VENCIDA por defecto.',
            ],

            [
                'table'       => 'tbl_reporte',
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
                    'id_reporte',
                    'id_cliente',
                    'id_report_type (tipo de reporte)',
                    'id_detailreport',
                    'fecha_reporte',
                    'archivo (ruta del PDF/documento)',
                    'estado',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'id_detailreport identifica el subtipo: 9=actas, 10=locativa, 11=señalización, 12=extintores, 13=botiquín, 14=gabinetes, 15=comunicaciones.',
            ],

            [
                'table'       => 'tbl_vencimientos_mantenimientos',
                'priority'    => '(6ª en importancia — mantenimientos vencidos o por vencer)',
                'description' => 'Tabla de mantenimientos con sus fechas de vencimiento. Se debe priorizar lo vencido o próximo a vencer.',
                'use_for'     => [
                    '¿qué mantenimientos están vencidos?',
                    '¿qué mantenimientos vencen pronto?',
                    'alertas de mantenimiento',
                    'mantenimientos próximos a vencer de X cliente',
                ],
                'key_columns' => [
                    'id',
                    'id_cliente',
                    'id_mantenimiento (FK → tbl_mantenimientos.id)',
                    'fecha_vencimiento',
                    'estado (ABIERTA / CERRADA / VENCIDA)',
                    'observaciones',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'id_mantenimiento → tbl_mantenimientos.id'],
                'notes'       => 'PRIORIZAR siempre estado VENCIDA y registros con fecha_vencimiento <= hoy o próximos 30 días. Aplicar directiva global.',
            ],

            // ═══════════════════════════════════════════════════════════
            // PLAN DE TRABAJO
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_pta_cliente',
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
                    'estado_actividad (ENUM: ABIERTA / CERRADA / GESTIONANDO / CERRADA SIN EJECUCIÓN / CERRADA POR FIN CONTRATO)',
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
                'description' => 'Huella de cambios del plan de trabajo. Registra cada modificación hecha a una actividad del plan de trabajo.',
                'use_for'     => [
                    '¿cuándo se cerró X actividad de X cliente?',
                    'historial de cambios en el plan de trabajo',
                    'auditoría de modificaciones',
                ],
                'key_columns' => ['id', 'id_pta_cliente', 'campo_modificado', 'valor_anterior', 'valor_nuevo', 'usuario', 'fecha_cambio'],
                'relations'   => ['id_pta_cliente → tbl_pta_cliente.id'],
                'notes'       => 'Tabla de auditoría — no contiene el estado actual sino el historial de cambios.',
            ],

            [
                'table'       => 'tbl_pta_transiciones',
                'description' => 'Huella de cambio de estatus de actividades del plan de trabajo (de ABIERTA a CERRADA, etc.). Muy útil para saber exactamente cuándo se cerró una actividad.',
                'use_for'     => [
                    '¿cuándo se cerró X actividad?',
                    'fecha de cierre de una actividad',
                    'transiciones de estado del plan de trabajo',
                ],
                'key_columns' => ['id', 'id_pta_cliente', 'estado_anterior', 'estado_nuevo', 'fecha_transicion', 'usuario'],
                'relations'   => ['id_pta_cliente → tbl_pta_cliente.id'],
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
                'key_columns' => ['id', 'actividad', 'phva', 'numeral', 'descripcion'],
                'relations'   => [],
                'notes'       => 'Es un catálogo/maestro — no tiene id_cliente. Usarlo para sugerencias y construcción de planes.',
            ],

            [
                'table'       => 'historial_resumen_plan_trabajo',
                'description' => 'Fotografía histórica mensual del avance del plan de trabajo de cada cliente (cuántas actividades abiertas/cerradas por período).',
                'use_for'     => [
                    '¿cómo ha avanzado el plan de trabajo de X históricamente?',
                    'comparar avance mes a mes',
                    'tendencia de cierre de actividades',
                ],
                'key_columns' => ['id_cliente', 'mes', 'año', 'actividades_abiertas', 'actividades_cerradas'],
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
                'key_columns' => ['id', 'nombre_estandar'],
                'relations'   => ['tbl_clientes.estandares → estandares.id'],
                'notes'       => 'No tiene prefijo tbl_.',
            ],

            [
                'table'       => 'evaluacion_inicial_sst',
                'description' => 'Calificación actual de los Estándares Mínimos del SG-SST para cada cliente. Es el instrumento de medición en tiempo real.',
                'use_for'     => [
                    '¿cuál es la calificación de estándares mínimos de X cliente?',
                    '¿qué actividades no han sido calificadas?',
                    '¿cuáles no cumplen?',
                    'porcentaje de cumplimiento SST',
                ],
                'key_columns' => ['id_cliente', 'item', 'calificacion', 'cumple', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'No tiene prefijo tbl_.',
            ],

            [
                'table'       => 'historial_resumen_estandares',
                'description' => 'Fotografía histórica mensual del avance de cada cliente en la evaluación de Estándares Mínimos.',
                'use_for'     => [
                    '¿cómo ha evolucionado X en estándares mínimos?',
                    'comparar calificación mes anterior vs actual',
                    'tendencia de cumplimiento SST',
                ],
                'key_columns' => ['id_cliente', 'mes', 'año', 'porcentaje_cumplimiento'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'No tiene prefijo tbl_. Para calificación actual usar evaluacion_inicial_sst.',
            ],

            // ═══════════════════════════════════════════════════════════
            // VISITAS Y ACTAS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_acta_visita',
                'description' => 'Datos de las actas de visita realizadas a los clientes.',
                'use_for'     => [
                    '¿cuándo fue la última visita a X?',
                    'historial de visitas',
                    'actas de visita del mes',
                ],
                'key_columns' => ['id', 'id_cliente', 'fecha_visita', 'id_consultor', 'estado'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_acta_visita_temas.id_acta_visita → id'],
                'notes'       => 'Aplicar directiva global: año actual por defecto.',
            ],

            [
                'table'       => 'tbl_acta_visita_temas',
                'description' => 'Tabla hija de tbl_acta_visita. Contiene los temas tratados en cada acta de visita.',
                'use_for'     => ['¿qué temas se trataron en la visita de X?', 'detalle de acta'],
                'key_columns' => ['id', 'id_acta_visita', 'tema', 'descripcion'],
                'relations'   => ['id_acta_visita → tbl_acta_visita.id'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // AGENDAMIENTO Y CICLOS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_agendamientos',
                'description' => 'Clientes con fecha concertada para visita. Es la agenda futura de visitas.',
                'use_for'     => [
                    '¿qué clientes tengo agendados?',
                    '¿cuándo tengo visita con X?',
                    'agenda de la semana / del mes',
                    'próximas visitas',
                ],
                'key_columns' => ['id', 'id_cliente', 'fecha_agendamiento', 'id_consultor', 'estado'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_ciclos_visita',
                'description' => 'Estado del ciclo de visita de cada cliente: si tiene agendamiento, si fue visitado, según la frecuencia del contrato.',
                'use_for'     => [
                    '¿qué clientes no han sido visitados este mes?',
                    'clientes pendientes de visita',
                    'ciclo de visitas',
                ],
                'key_columns' => ['id', 'id_cliente', 'id_agendamiento', 'visitado', 'fecha_ciclo'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'id_agendamiento → tbl_agendamientos.id'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // CAPACITACIONES
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_cronog_capacitacion',
                'description' => 'Cronograma de capacitaciones SST programadas para cada cliente. Tabla importante para seguimiento de formación.',
                'use_for'     => [
                    '¿qué capacitaciones tiene programadas X?',
                    'cronograma de capacitaciones',
                    '¿qué capacitaciones faltan por ejecutar?',
                    'capacitaciones del año',
                ],
                'key_columns' => ['id', 'id_cliente', 'tema', 'fecha_programada', 'estado', 'responsable'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'Aplicar directiva global: año actual + ABIERTA/pendiente por defecto.',
            ],

            [
                'table'       => 'tbl_reporte_capacitacion',
                'description' => 'Capacitaciones que efectivamente sí se realizaron y fueron reportadas para los clientes.',
                'use_for'     => [
                    '¿cuántas capacitaciones se han hecho en X?',
                    'capacitaciones ejecutadas',
                    '¿ya se reportó la capacitación de X tema?',
                ],
                'key_columns' => ['id', 'id_cliente', 'tema', 'fecha_ejecucion', 'asistentes', 'soporte'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_asistencia_induccion',
                'description' => 'Listado de personas que asistieron a inducción SST en un cliente.',
                'use_for'     => [
                    '¿cuántas personas han recibido inducción en X?',
                    'lista de asistentes a inducción',
                    'inducciones realizadas',
                ],
                'key_columns' => ['id', 'id_cliente', 'nombre_persona', 'cargo', 'fecha_induccion', 'firma'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_evaluacion_induccion',
                'description' => 'Datos generales de las evaluaciones de inducción realizadas a personas de un cliente.',
                'use_for'     => ['evaluaciones de inducción', '¿se evaluó a X persona?', 'resultados de inducción'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'nombre_evaluado', 'cargo', 'puntaje'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_evaluacion_induccion_respuesta.id_evaluacion → id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_evaluacion_induccion_respuesta',
                'description' => 'Respuestas registradas por los usuarios en las evaluaciones de inducción.',
                'use_for'     => ['detalle de respuestas de evaluación de inducción', '¿qué respondió X en la evaluación?'],
                'key_columns' => ['id', 'id_evaluacion', 'pregunta', 'respuesta', 'correcta'],
                'relations'   => ['id_evaluacion → tbl_evaluacion_induccion.id'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // INSPECCIONES
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_inspeccion_locativa',
                'description' => 'Datos de las inspecciones locativas realizadas en las instalaciones de un cliente.',
                'use_for'     => ['inspección locativa', '¿cuándo fue la última inspección locativa de X?', 'estado de las instalaciones'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'calificacion_general', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_hallazgo_locativo.id_inspeccion_locativa → id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_hallazgo_locativo',
                'description' => 'Hallazgos identificados durante las inspecciones locativas.',
                'use_for'     => ['hallazgos locativos', '¿qué hallazgos se encontraron en X?', 'estado de hallazgos'],
                'key_columns' => ['id', 'id_inspeccion_locativa', 'descripcion', 'estado', 'foto'],
                'relations'   => ['id_inspeccion_locativa → tbl_inspeccion_locativa.id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_inspeccion_extintores',
                'description' => 'Datos de las inspecciones realizadas a los extintores de un cliente.',
                'use_for'     => ['inspección de extintores', '¿cuándo fue la última inspección de extintores de X?'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'estado_general', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_extintor_detalle.id_inspeccion → id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_extintor_detalle',
                'description' => 'Datos detallados de cada extintor registrado en el sistema (uno por fila).',
                'use_for'     => ['detalle de extintores', '¿cuántos extintores tiene X?', 'estado de cada extintor'],
                'key_columns' => ['id', 'id_inspeccion', 'numero_extintor', 'tipo', 'capacidad', 'fecha_vencimiento', 'estado'],
                'relations'   => ['id_inspeccion → tbl_inspeccion_extintores.id'],
                'notes'       => 'Aplicar directiva global para vencimientos.',
            ],

            [
                'table'       => 'tbl_inspeccion_gabinetes',
                'description' => 'Datos de las inspecciones realizadas a los gabinetes contra incendio.',
                'use_for'     => ['inspección de gabinetes', '¿cuándo fue la última inspección de gabinetes de X?'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'estado_general', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_gabinete_detalle.id_inspeccion → id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_gabinete_detalle',
                'description' => 'Datos detallados de cada gabinete contra incendio registrado.',
                'use_for'     => ['detalle de gabinetes contra incendio', '¿cuántos gabinetes tiene X?'],
                'key_columns' => ['id', 'id_inspeccion', 'numero_gabinete', 'ubicacion', 'estado'],
                'relations'   => ['id_inspeccion → tbl_inspeccion_gabinetes.id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_inspeccion_botiquin',
                'description' => 'Datos de las inspecciones realizadas a los botiquines.',
                'use_for'     => ['inspección de botiquín', '¿cuándo fue la última inspección de botiquín de X?'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'estado_general', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_elemento_botiquin.id_inspeccion_botiquin → id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_elemento_botiquin',
                'description' => 'Elementos o componentes que conforman el botiquín para su control e inspección.',
                'use_for'     => ['elementos del botiquín', '¿qué le falta al botiquín de X?', 'inventario botiquín'],
                'key_columns' => ['id', 'id_inspeccion_botiquin', 'elemento', 'cantidad', 'estado', 'fecha_vencimiento'],
                'relations'   => ['id_inspeccion_botiquin → tbl_inspeccion_botiquin.id'],
                'notes'       => 'Aplicar directiva para vencimientos de elementos.',
            ],

            [
                'table'       => 'tbl_inspeccion_senalizacion',
                'description' => 'Datos de las inspecciones realizadas a la señalización de seguridad.',
                'use_for'     => ['inspección de señalización', '¿cuándo se inspeccionó la señalización de X?'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'estado_general', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_inspeccion_comunicaciones',
                'description' => 'Datos de las inspecciones relacionadas con equipos o medios de comunicación para emergencias.',
                'use_for'     => ['inspección de comunicaciones', 'equipos de comunicación para emergencias', '¿estado de los radios o medios de comunicación de X?'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'estado_general', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_inspeccion_recursos_seguridad',
                'description' => 'Datos de las inspecciones de los recursos o elementos de seguridad disponibles en el cliente.',
                'use_for'     => ['recursos de seguridad', 'inspección de elementos de seguridad', '¿qué recursos de seguridad tiene X?'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'estado_general', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_auditoria_zona_residuos',
                'description' => 'Inspección / auditoría de la zona de residuos (cuarto de basuras) de un cliente.',
                'use_for'     => ['zona de residuos', 'cuarto de basuras', 'auditoría de residuos'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'calificacion', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // DOTACIONES
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_dotacion_aseadora',
                'description' => 'Datos de las inspecciones de las dotaciones de las aseadoras.',
                'use_for'     => ['dotación de aseadoras', '¿ya se revisó la dotación de aseadoras de X?', 'inspección dotación aseadora'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'nombre_aseadora', 'items_entregados', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_dotacion_todero',
                'description' => 'Datos de las inspecciones de las dotaciones de los toderos.',
                'use_for'     => ['dotación de toderos', '¿ya se revisó la dotación del todero de X?', 'inspección dotación todero'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'nombre_todero', 'items_entregados', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_dotacion_vigilante',
                'description' => 'Datos de las inspecciones de las dotaciones de los vigilantes.',
                'use_for'     => ['dotación de vigilantes', '¿ya se revisó la dotación del vigilante de X?', 'inspección dotación vigilante'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'nombre_vigilante', 'items_entregados', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // SIMULACROS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_preparacion_simulacro',
                'description' => 'Datos de preparación y planeación de los simulacros de emergencia.',
                'use_for'     => ['planificación de simulacro', '¿está planeado el simulacro de X?', 'preparación simulacro'],
                'key_columns' => ['id', 'id_cliente', 'fecha_programada', 'tipo_emergencia', 'estado', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_evaluacion_simulacro',
                'description' => 'Datos de evaluación de los simulacros ejecutados.',
                'use_for'     => ['resultado del simulacro', '¿cómo le fue a X en el simulacro?', 'calificación simulacro'],
                'key_columns' => ['id', 'id_cliente', 'fecha_ejecucion', 'calificacion', 'fortalezas', 'oportunidades_mejora'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // PLANES Y PROGRAMAS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_plan_emergencia',
                'description' => 'Datos correspondientes al plan de emergencia de cada cliente.',
                'use_for'     => ['plan de emergencia', '¿tiene plan de emergencia X?', '¿está actualizado el plan de emergencia?'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'version', 'estado', 'archivo'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_plan_saneamiento',
                'description' => 'Datos correspondientes al plan de saneamiento básico de cada cliente.',
                'use_for'     => ['plan de saneamiento', 'saneamiento básico', '¿tiene plan de saneamiento X?'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'estado', 'archivo'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_programa_agua_potable',
                'description' => 'Datos correspondientes al programa de agua potable.',
                'use_for'     => ['programa de agua potable', '¿tiene programa de agua potable X?'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'estado'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'Ver indicador en tbl_kpi_agua_potable.',
            ],

            [
                'table'       => 'tbl_programa_limpieza',
                'description' => 'Datos correspondientes al programa de limpieza y desinfección.',
                'use_for'     => ['programa de limpieza', 'programa de desinfección', '¿tiene programa de limpieza X?'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'estado'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'Ver indicador en tbl_kpi_limpieza.',
            ],

            [
                'table'       => 'tbl_programa_plagas',
                'description' => 'Datos correspondientes al programa de control de plagas.',
                'use_for'     => ['control de plagas', 'programa de plagas', 'fumigación'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'estado'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'Ver indicador en tbl_kpi_plagas.',
            ],

            [
                'table'       => 'tbl_programa_residuos',
                'description' => 'Datos correspondientes al programa de gestión de residuos.',
                'use_for'     => ['gestión de residuos', 'programa de residuos', 'manejo de basuras'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'estado'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // KPIs / INDICADORES
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_kpi_agua_potable',
                'description' => 'Indicadores del programa de agua potable.',
                'use_for'     => ['indicador agua potable', 'KPI agua', '¿cómo va el programa de agua de X?'],
                'key_columns' => ['id', 'id_cliente', 'mes', 'año', 'valor', 'meta'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_kpi_limpieza',
                'description' => 'Indicadores del programa de limpieza.',
                'use_for'     => ['indicador de limpieza', 'KPI limpieza', '¿cómo va el programa de limpieza de X?'],
                'key_columns' => ['id', 'id_cliente', 'mes', 'año', 'valor', 'meta'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_kpi_plagas',
                'description' => 'Indicadores del programa de control de plagas.',
                'use_for'     => ['indicador control de plagas', 'KPI plagas', '¿cómo va el control de plagas de X?'],
                'key_columns' => ['id', 'id_cliente', 'mes', 'año', 'valor', 'meta'],
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
                'key_columns' => ['id', 'nombre_mantenimiento', 'descripcion'],
                'relations'   => ['tbl_vencimientos_mantenimientos.id_mantenimiento → id'],
                'notes'       => 'Es un maestro/catálogo. Para estado y vencimientos, ver tbl_vencimientos_mantenimientos.',
            ],

            // ═══════════════════════════════════════════════════════════
            // PRESUPUESTO SST
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_presupuesto_sst',
                'description' => 'Datos generales de los presupuestos de Seguridad y Salud en el Trabajo de cada cliente.',
                'use_for'     => ['presupuesto SST', '¿tiene presupuesto definido X?', 'presupuesto de seguridad'],
                'key_columns' => ['id', 'id_cliente', 'año', 'valor_total', 'estado'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_presupuesto_detalle.id_presupuesto → id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_presupuesto_detalle',
                'description' => 'Detalles o conceptos asociados a cada presupuesto SST.',
                'use_for'     => ['detalle del presupuesto SST', '¿en qué se va a invertir?'],
                'key_columns' => ['id', 'id_presupuesto', 'concepto', 'valor', 'id_categoria'],
                'relations'   => ['id_presupuesto → tbl_presupuesto_sst.id', 'id_categoria → tbl_presupuesto_categorias.id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_presupuesto_items',
                'description' => 'Ítems o elementos que conforman los presupuestos.',
                'use_for'     => ['ítems de presupuesto', 'detalle de ítems presupuestados'],
                'key_columns' => ['id', 'id_presupuesto', 'item', 'valor_unitario', 'cantidad'],
                'relations'   => ['id_presupuesto → tbl_presupuesto_sst.id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_presupuesto_categorias',
                'description' => 'Categorías utilizadas para clasificar los presupuestos SST.',
                'use_for'     => ['categorías de presupuesto'],
                'key_columns' => ['id', 'nombre_categoria'],
                'relations'   => [],
                'notes'       => 'Tabla maestra/catálogo.',
            ],

            // ═══════════════════════════════════════════════════════════
            // SEGURIDAD Y RIESGOS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_probabilidad_peligros',
                'description' => 'Datos de valoración o probabilidad de ocurrencia de los peligros identificados en un cliente.',
                'use_for'     => ['peligros identificados', 'matriz de peligros', 'probabilidad de accidente', 'valoración de riesgos'],
                'key_columns' => ['id', 'id_cliente', 'peligro', 'probabilidad', 'consecuencia', 'nivel_riesgo'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_matrices',
                'description' => 'Registro de qué clientes ya tienen el archivo Excel de matriz de riesgos y matriz de EPPs cargado.',
                'use_for'     => ['¿tiene la matriz de riesgos X?', '¿ya está la matriz de EPPs de X?', 'matrices del cliente'],
                'key_columns' => ['id', 'id_cliente', 'tipo_matriz', 'archivo', 'fecha_carga', 'estado'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_matriz_vulnerabilidad',
                'description' => 'Datos de la matriz de vulnerabilidad por amenazas de cada cliente.',
                'use_for'     => ['matriz de vulnerabilidad', 'amenazas identificadas', 'vulnerabilidad del cliente'],
                'key_columns' => ['id', 'id_cliente', 'amenaza', 'nivel_vulnerabilidad', 'fecha'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // BRIGADISTAS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_hv_brigadista',
                'description' => 'Hojas de vida e información general de los brigadistas de cada cliente.',
                'use_for'     => ['brigadistas', '¿cuántos brigadistas tiene X?', 'hoja de vida de brigadista', '¿están capacitados los brigadistas?'],
                'key_columns' => ['id', 'id_cliente', 'nombre', 'cargo', 'tipo_brigada', 'fecha_capacitacion', 'estado'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // INFORMES Y SOPORTES
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_informe_avances',
                'description' => 'Informes de avance de actividades o compromisos del cliente.',
                'use_for'     => ['informe de avances', '¿se ha generado informe de avances para X?', 'reporte de avances'],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'periodo', 'archivo', 'estado'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_planillas_seguridad_social',
                'description' => 'Soportes o registros de las planillas de seguridad social de los clientes.',
                'use_for'     => ['planilla de seguridad social', '¿ya se revisó la planilla de X?', 'soporte de seguridad social'],
                'key_columns' => ['id', 'id_cliente', 'mes', 'año', 'archivo', 'estado'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_contratos',
                'description' => 'Historial de contratos de los clientes.',
                'use_for'     => ['contratos', '¿cuándo vence el contrato de X?', 'historial de contratos', '¿está activo el contrato?'],
                'key_columns' => ['id', 'id_cliente', 'fecha_inicio', 'fecha_fin', 'valor', 'estado'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // USUARIOS Y ACCESO
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_sesiones_usuario',
                'description' => 'Registro de sesiones de usuarios. Muy útil para saber cuándo entró por última vez un cliente a la plataforma. Los clientes a veces dicen que no hacemos el trabajo, pero en realidad ellos nunca entran a revisar la información.',
                'use_for'     => [
                    '¿cuándo entró por última vez el cliente X a la plataforma?',
                    '¿el cliente revisa la plataforma?',
                    'última conexión de X',
                    'actividad de usuarios',
                ],
                'key_columns' => ['id', 'id_usuario', 'fecha_inicio', 'fecha_fin', 'ip_address'],
                'relations'   => ['id_usuario → tbl_usuarios.id_usuario'],
                'notes'       => 'SOLO LECTURA — no se puede modificar ni eliminar. Muy útil para demostrar al cliente que sí se está trabajando.',
            ],

            [
                'table'       => 'tbl_usuarios',
                'description' => 'Tabla de usuarios del sistema.',
                'use_for'     => ['¿quién es el usuario de X cliente?', 'usuarios registrados'],
                'key_columns' => ['id_usuario', 'nombre', 'correo', 'id_rol', 'id_cliente', 'estado'],
                'relations'   => ['id_rol → tbl_roles.id', 'id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'SOLO LECTURA — no se puede modificar ni eliminar.',
            ],

            [
                'table'       => 'tbl_roles',
                'description' => 'Roles de acceso del sistema. Define quién puede acceder a qué.',
                'use_for'     => ['roles del sistema', '¿qué rol tiene X usuario?'],
                'key_columns' => ['id', 'nombre_rol', 'descripcion'],
                'relations'   => [],
                'notes'       => 'SOLO LECTURA — neurálgico para la seguridad del sistema. Nunca modificar.',
            ],

        ];
    }
}
