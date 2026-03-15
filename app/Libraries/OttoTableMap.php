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
 *
 * Este mapa se inyecta en el system prompt de Otto para que genere
 * consultas correctas desde la primera vez.
 */
class OttoTableMap
{
    /**
     * Retorna el mapa como bloque de texto para el system prompt de OpenAI.
     */
    public static function getPromptBlock(): string
    {
        $map = self::getMap();
        $lines = ["## MAPA SEMÁNTICO DE TABLAS DE NEGOCIO", ""];

        foreach ($map as $entry) {
            $lines[] = "### `{$entry['table']}`";
            $lines[] = "**Qué es:** {$entry['description']}";
            if (!empty($entry['use_for'])) {
                $lines[] = "**Úsala cuando pregunten:** " . implode(' / ', $entry['use_for']);
            }
            if (!empty($entry['key_columns'])) {
                $lines[] = "**Columnas clave:** " . implode(', ', $entry['key_columns']);
            }
            if (!empty($entry['relations'])) {
                $lines[] = "**Relaciones:** " . implode('; ', $entry['relations']);
            }
            if (!empty($entry['notes'])) {
                $lines[] = "**Notas:** {$entry['notes']}";
            }
            $lines[] = "";
        }

        return implode("\n", $lines);
    }

    /**
     * Mapa completo. Agregar entradas en orden alfabético o por dominio.
     */
    public static function getMap(): array
    {
        return [

            // ─── MAESTRAS / CONFIGURACIÓN ─────────────────────────────────

            [
                'table'       => 'estandares',
                'description' => 'Tabla maestra que define la categoría de servicio de un cliente: mensual, bimensual, trimestral o proyecto.',
                'use_for'     => ['¿qué tipo de servicio tiene X cliente?', 'frecuencia de visita', 'categoría del contrato'],
                'key_columns' => ['id', 'nombre_estandar'],
                'relations'   => ['tbl_clientes.estandares → estandares.id (indica la categoría del cliente)'],
                'notes'       => '',
            ],

            // ─── CLIENTES ─────────────────────────────────────────────────

            [
                'table'       => 'tbl_clientes',
                'description' => 'Registro de todos los clientes (conjuntos residenciales, edificios, copropiedades) que gestiona Cycloid Talent.',
                'use_for'     => ['listar clientes', 'buscar un cliente por nombre', 'estado activo/inactivo', 'consultor asignado'],
                'key_columns' => ['id_cliente', 'nombre_cliente', 'estado (activo/inactivo/pendiente)', 'id_consultor', 'estandares'],
                'relations'   => ['estandares → estandares.id', 'tbl_clientes.id_cliente es la FK principal de todo el sistema'],
                'notes'       => 'Buscar siempre con LIKE \'%nombre%\' en nombre_cliente. La columna estado usa ENUM: activo, inactivo, pendiente.',
            ],

            // ─── EVALUACIÓN / ESTÁNDARES MÍNIMOS ──────────────────────────

            [
                'table'       => 'evaluacion_inicial_sst',
                'description' => 'Contiene la calificación actual de los Estándares Mínimos del SG-SST para cada cliente. Es el instrumento de medición en tiempo real.',
                'use_for'     => [
                    '¿cuál es la calificación de estándares mínimos de X cliente?',
                    '¿qué actividades no han sido calificadas?',
                    '¿cuáles no cumplen?',
                    'porcentaje de cumplimiento SST',
                ],
                'key_columns' => ['id_cliente', 'item', 'calificacion', 'cumple', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'historial_resumen_estandares',
                'description' => 'Fotografía histórica mensual del avance de cada cliente en la evaluación de Estándares Mínimos. Permite ver la evolución en el tiempo.',
                'use_for'     => [
                    '¿cómo ha evolucionado X cliente en estándares mínimos?',
                    'comparar calificación mes anterior vs actual',
                    'tendencia de cumplimiento SST',
                ],
                'key_columns' => ['id_cliente', 'mes', 'año', 'porcentaje_cumplimiento'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'No es la calificación actual — para eso usar evaluacion_inicial_sst.',
            ],

            // ─── PLAN DE TRABAJO ───────────────────────────────────────────

            [
                'table'       => 'plan_de_trabajo_del_cliente',
                'description' => 'Actividades del plan anual de trabajo SST para cada cliente. Es la tabla central para gestión de tareas y seguimiento.',
                'use_for'     => [
                    '¿qué actividades tiene abiertas X cliente?',
                    'plan de trabajo',
                    'actividades pendientes',
                    'actividades en gestión',
                    '¿qué llevo en el plan de trabajo de X?',
                    'porcentaje de avance de actividades',
                ],
                'key_columns' => [
                    'id_ptacliente',
                    'id_cliente',
                    'nombre_cliente',
                    'actividad_plandetrabajo',
                    'estado_actividad (ABIERTA / CERRADA / GESTIONANDO / CERRADA SIN EJECUCIÓN / CERRADA POR FIN CONTRATO)',
                    'fecha_propuesta',
                    'fecha_cierre',
                    'porcentaje_avance',
                    'responsable_definido_paralaactividad',
                    'phva_plandetrabajo',
                    'numeral_plandetrabajo',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'NO tiene prefijo tbl_. Cuando el usuario menciona "mañana tengo visita" NO filtrar por fecha — mostrar todas las ABIERTA. Para filtrar por cliente usar JOIN con tbl_clientes o filtrar directamente por id_cliente.',
            ],

            [
                'table'       => 'historial_resumen_plan_trabajo',
                'description' => 'Fotografía histórica mensual del avance del plan de trabajo de cada cliente. Permite ver cuántas actividades estaban abiertas/cerradas en cada período.',
                'use_for'     => [
                    '¿cómo ha avanzado el plan de trabajo de X cliente históricamente?',
                    'comparar avance mes a mes',
                    'resumen histórico de actividades',
                ],
                'key_columns' => ['id_cliente', 'mes', 'año', 'actividades_abiertas', 'actividades_cerradas'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'No refleja el estado actual — para eso usar plan_de_trabajo_del_cliente.',
            ],

            // ─── VISITAS Y ACTAS ───────────────────────────────────────────

            [
                'table'       => 'tbl_acta_visita',
                'description' => 'Datos de las actas de visita realizadas a los clientes: fecha, consultor, resumen, estado.',
                'use_for'     => [
                    '¿cuándo fue la última visita a X cliente?',
                    'historial de visitas',
                    'acta de visita',
                    'visitas realizadas en el mes',
                ],
                'key_columns' => ['id', 'id_cliente', 'fecha_visita', 'id_consultor', 'estado'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_acta_visita_temas.id_acta_visita → tbl_acta_visita.id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_acta_visita_temas',
                'description' => 'Tabla hija de tbl_acta_visita. Contiene los temas tratados en cada acta de visita.',
                'use_for'     => ['¿qué temas se trataron en la visita de X?', 'detalle de acta de visita'],
                'key_columns' => ['id', 'id_acta_visita', 'tema', 'descripcion'],
                'relations'   => ['id_acta_visita → tbl_acta_visita.id'],
                'notes'       => '',
            ],

            // ─── AGENDAMIENTO Y CICLOS ─────────────────────────────────────

            [
                'table'       => 'tbl_agendamientos',
                'description' => 'Clientes que ya tienen concertada una fecha para ser visitados. Es la agenda futura de visitas.',
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
                'description' => 'Permite saber el estado del ciclo de visita de cada cliente: si tiene agendamiento, si fue visitado, cuándo corresponde la próxima visita según su frecuencia (mensual/bimensual/trimestral).',
                'use_for'     => [
                    '¿qué clientes no han sido visitados este mes?',
                    '¿qué clientes están sin visita?',
                    'ciclo de visitas',
                    'clientes pendientes de visita',
                ],
                'key_columns' => ['id', 'id_cliente', 'id_agendamiento', 'visitado', 'fecha_ciclo'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'id_agendamiento → tbl_agendamientos.id'],
                'notes'       => '',
            ],

            // ─── INDUCCIONES ───────────────────────────────────────────────

            [
                'table'       => 'tbl_asistencia_induccion',
                'description' => 'Listado de personas que asistieron a inducción SST en un cliente.',
                'use_for'     => [
                    '¿cuántas personas han recibido inducción en X cliente?',
                    'lista de asistentes a inducción',
                    'inducciones realizadas',
                ],
                'key_columns' => ['id', 'id_cliente', 'nombre_persona', 'cargo', 'fecha_induccion', 'firma'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ─── INSPECCIONES ──────────────────────────────────────────────

            [
                'table'       => 'tbl_auditoria_zona_residuos',
                'description' => 'Inspección / auditoría de la zona de residuos (cuarto de basuras) de un cliente.',
                'use_for'     => [
                    '¿cuál es el estado del cuarto de basuras de X?',
                    'zona de residuos',
                    'auditoría de residuos',
                    'inspección cuarto de basuras',
                ],
                'key_columns' => ['id', 'id_cliente', 'fecha', 'calificacion', 'observaciones'],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

        ];
    }
}
