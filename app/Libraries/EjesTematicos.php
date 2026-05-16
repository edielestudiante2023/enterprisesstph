<?php

namespace App\Libraries;

/**
 * Catálogo de actividades SST agrupadas por EJE TEMÁTICO (gestión estratégica).
 *
 * Este catálogo es paralelo a InspeccionTypes — los ejes cubren la dimensión
 * documental/estratégica del SG-SST (políticas, planes, autoevaluaciones,
 * reglamentos…) mientras que InspeccionTypes cubre inspecciones físicas
 * (extintores, botiquín, locativa…). Las PTAs creadas desde este catálogo van
 * a tbl_pta_cliente pero NO entran a tbl_pta_inspeccion_match.
 *
 * Consumido por PtaClienteNuevaController::generarEjes() / generarEjesGuardar().
 *
 * Campos por eje:
 *  - slug: identificador del eje (kebab-case).
 *  - titulo: nombre visible del eje.
 *  - icon: clase FontAwesome.
 *  - actividades: array de actividades. Cada una con:
 *      - id: identificador único global (slug+contador). Se usa como valor del checkbox.
 *      - componente: agrupador secundario opcional (sub-tema dentro del eje); visual.
 *      - tarea: redacción que se inserta literal en tbl_pta_cliente.actividad_plandetrabajo.
 */
class EjesTematicos
{
    public static function all(): array
    {
        return [
            [
                'slug' => 'componente-estrategico',
                'titulo' => 'Componente Estratégico SG-SST',
                'icon' => 'fa-chess',
                'actividades' => [
                    ['id' => 'cesg-001', 'componente' => 'Política SST', 'tarea' => 'Elaborar, actualizar, socializar y verificar la publicación de la Política de Seguridad y Salud en el Trabajo conforme a las actividades y riesgos de la copropiedad.'],
                    ['id' => 'cesg-002', 'componente' => 'Objetivos SST', 'tarea' => 'Diseñar y documentar los objetivos del SG-SST alineados con la política, los riesgos identificados y los requisitos legales aplicables a la copropiedad.'],
                    ['id' => 'cesg-003', 'componente' => 'Plan Anual de Trabajo', 'tarea' => 'Elaborar, estructurar y realizar seguimiento al Plan Anual de Trabajo del SG-SST incluyendo actividades, responsables, cronogramas y evidencias de ejecución.'],
                    ['id' => 'cesg-004', 'componente' => 'Asignación de responsabilidades', 'tarea' => 'Definir y formalizar las responsabilidades del SG-SST para administración, vigía SST, trabajadores, contratistas y proveedores críticos de la copropiedad.'],
                    ['id' => 'cesg-005', 'componente' => 'Presupuesto SST', 'tarea' => 'Elaborar el presupuesto anual del SG-SST contemplando capacitaciones, inspecciones, emergencias, señalización, elementos de protección y actividades de cumplimiento legal.'],
                    ['id' => 'cesg-006', 'componente' => 'Autoevaluación inicial', 'tarea' => 'Realizar la autoevaluación inicial de estándares mínimos conforme a la Resolución 0312 de 2019, identificando brechas y oportunidades de mejora del sistema.'],
                    ['id' => 'cesg-007', 'componente' => 'Autorreporte de estándares mínimos', 'tarea' => 'Gestionar el diligenciamiento, validación y consolidación del autorreporte anual de estándares mínimos del SG-SST ante la plataforma correspondiente del Ministerio del Trabajo.'],
                    ['id' => 'cesg-008', 'componente' => 'Revisión anual por dirección', 'tarea' => 'Ejecutar la revisión anual del SG-SST junto con la administración de la copropiedad, evaluando indicadores, cumplimiento normativo, hallazgos y acciones de mejora.'],
                    ['id' => 'cesg-009', 'componente' => 'Informe de gestión anual', 'tarea' => 'Elaborar y presentar el informe anual de gestión del SG-SST consolidando resultados, indicadores, cumplimiento de actividades y estado general del sistema.'],
                ],
            ],
            [
                'slug' => 'gestion-documental',
                'titulo' => 'Gestión Documental y Administrativa',
                'icon' => 'fa-folder-open',
                'actividades' => [
                    ['id' => 'gda-001', 'componente' => 'Gestión Documental y Administrativa', 'tarea' => 'Diseñar, actualizar y organizar la estructura documental del SG-SST de la copropiedad conforme a la normatividad vigente.'],
                    ['id' => 'gda-002', 'componente' => 'Gestión Documental y Administrativa', 'tarea' => 'Elaborar y mantener actualizado el procedimiento de gestión documental y retención de registros del SG-SST.'],
                    ['id' => 'gda-003', 'componente' => 'Gestión Documental y Administrativa', 'tarea' => 'Verificar el correcto archivo físico y digital de evidencias, actas, formatos, inspecciones y soportes del sistema.'],
                    ['id' => 'gda-004', 'componente' => 'Gestión Documental y Administrativa', 'tarea' => 'Formalizar mediante soportes escritos la designación del responsable del SG-SST y del vigía SST de la copropiedad.'],
                    ['id' => 'gda-005', 'componente' => 'Gestión Documental y Administrativa', 'tarea' => 'Verificar y archivar los certificados vigentes de capacitación de 50 o 20 horas en SG-SST del responsable y/o vigía SST.'],
                    ['id' => 'gda-006', 'componente' => 'Gestión Documental y Administrativa', 'tarea' => 'Validar y mantener actualizada la documentación legal obligatoria del SG-SST exigida para propiedad horizontal.'],
                    ['id' => 'gda-007', 'componente' => 'Gestión Documental y Administrativa', 'tarea' => 'Elaborar informes mensuales de avance posteriores a las visitas de consultoría realizadas en la copropiedad.'],
                    ['id' => 'gda-008', 'componente' => 'Gestión Documental y Administrativa', 'tarea' => 'Consolidar y presentar informes periódicos de cumplimiento, hallazgos y seguimiento del SG-SST a la administración.'],
                    ['id' => 'gda-009', 'componente' => 'Gestión Documental y Administrativa', 'tarea' => 'Gestionar el control de versiones, vigencias y firmas de documentos estratégicos y operativos del sistema.'],
                    ['id' => 'gda-010', 'componente' => 'Gestión Documental y Administrativa', 'tarea' => 'Verificar la disponibilidad y trazabilidad documental requerida ante auditorías, ARL o visitas de entes de control.'],
                ],
            ],
            [
                'slug' => 'capacitacion-sgsst',
                'titulo' => 'Capacitación SG-SST',
                'icon' => 'fa-chalkboard-teacher',
                'actividades' => [
                    ['id' => 'csgs-001', 'componente' => 'Capacitación SG-SST', 'tarea' => 'Elaborar el programa anual de capacitación del SG-SST conforme a los riesgos y actividades de la copropiedad.'],
                ],
            ],
            [
                'slug' => 'induccion-reinduccion',
                'titulo' => 'Inducción y Reinducción SG-SST',
                'icon' => 'fa-user-plus',
                'actividades' => [
                    ['id' => 'irsg-001', 'componente' => 'Inducción y Reinducción SG-SST', 'tarea' => 'Programar y ejecutar la inducción en SG-SST para personal nuevo vinculado a la copropiedad.'],
                    ['id' => 'irsg-002', 'componente' => 'Inducción y Reinducción SG-SST', 'tarea' => 'Realizar procesos de reinducción periódica en Seguridad y Salud en el Trabajo conforme a cambios operativos o normativos.'],
                    ['id' => 'irsg-003', 'componente' => 'Inducción y Reinducción SG-SST', 'tarea' => 'Socializar durante la inducción la Política SST, reglamentos, riesgos, responsabilidades y procedimientos de emergencia.'],
                    ['id' => 'irsg-004', 'componente' => 'Inducción y Reinducción SG-SST', 'tarea' => 'Verificar y archivar los soportes y registros correspondientes a las actividades de inducción y reinducción realizadas.'],
                    ['id' => 'irsg-005', 'componente' => 'Inducción y Reinducción SG-SST', 'tarea' => 'Capacitar al personal sobre el procedimiento de reporte de accidentes, incidentes y actos inseguros.'],
                    ['id' => 'irsg-006', 'componente' => 'Inducción y Reinducción SG-SST', 'tarea' => 'Establecer y documentar el procedimiento para el reporte de accidentes e incidentes de trabajo en la copropiedad.'],
                    ['id' => 'irsg-007', 'componente' => 'Inducción y Reinducción SG-SST', 'tarea' => 'Socializar el procedimiento de reporte de accidentes de trabajo durante procesos de inducción y capacitación del SG-SST.'],
                ],
            ],
            [
                'slug' => 'gestion-emergencias',
                'titulo' => 'Gestión de Emergencias y Brigadas',
                'icon' => 'fa-fire-extinguisher',
                'actividades' => [
                    ['id' => 'geb-001', 'componente' => 'Gestión de Emergencias y Brigadas', 'tarea' => 'Definir rutas de evacuación, puntos de encuentro y recursos de respuesta ante emergencias.'],
                    ['id' => 'geb-002', 'componente' => 'Gestión de Emergencias y Brigadas', 'tarea' => 'Asesorar la conformación y organización de la brigada de emergencias de la copropiedad.'],
                    ['id' => 'geb-003', 'componente' => 'Gestión de Emergencias y Brigadas', 'tarea' => 'Verificar el estado, conformación y capacitación de los brigadistas.'],
                    ['id' => 'geb-004', 'componente' => 'Gestión de Emergencias y Brigadas', 'tarea' => 'Programar, coordinar y acompañar la ejecución de simulacros de emergencias.'],
                    ['id' => 'geb-005', 'componente' => 'Gestión de Emergencias y Brigadas', 'tarea' => 'Evaluar los resultados de los simulacros y generar acciones de mejora.'],
                    ['id' => 'geb-006', 'componente' => 'Gestión de Emergencias y Brigadas', 'tarea' => 'Gestionar el reporte de ejecución de simulacros ante las entidades correspondientes cuando aplique.'],
                ],
            ],
            [
                'slug' => 'gestion-riesgos',
                'titulo' => 'Gestión de Riesgos y Peligros',
                'icon' => 'fa-triangle-exclamation',
                'actividades' => [
                    ['id' => 'grp-001', 'componente' => 'Gestión de Riesgos y Peligros', 'tarea' => 'Identificar los peligros presentes en las actividades administrativas, operativas y de servicios de la copropiedad.'],
                    ['id' => 'grp-002', 'componente' => 'Gestión de Riesgos y Peligros', 'tarea' => 'Elaborar y actualizar la matriz de identificación de peligros, evaluación y valoración de riesgos conforme a la metodología definida.'],
                    ['id' => 'grp-003', 'componente' => 'Gestión de Riesgos y Peligros', 'tarea' => 'Clasificar los riesgos laborales asociados a actividades de vigilancia, aseo, mantenimiento, jardinería y administración.'],
                    ['id' => 'grp-004', 'componente' => 'Gestión de Riesgos y Peligros', 'tarea' => 'Definir medidas de intervención y controles para los riesgos identificados en la copropiedad.'],
                ],
            ],
            [
                'slug' => 'contratistas-proveedores',
                'titulo' => 'Contratistas y Proveedores',
                'icon' => 'fa-handshake',
                'actividades' => [
                    ['id' => 'cp-001', 'componente' => 'Contratistas y Proveedores', 'tarea' => 'Realizar auditoría SG-SST al contratista de vigilancia conforme a los requisitos definidos por la copropiedad.'],
                    ['id' => 'cp-002', 'componente' => 'Contratistas y Proveedores', 'tarea' => 'Realizar auditoría SG-SST al contratista de aseo verificando cumplimiento documental y operativo.'],
                    ['id' => 'cp-003', 'componente' => 'Contratistas y Proveedores', 'tarea' => 'Elaborar y actualizar el manual de contratistas de la copropiedad conforme a los lineamientos del SG-SST.'],
                    ['id' => 'cp-004', 'componente' => 'Contratistas y Proveedores', 'tarea' => 'Mantener actualizado el listado y control documental de contratistas y proveedores críticos de la copropiedad.'],
                    ['id' => 'cp-005', 'componente' => 'Contratistas y Proveedores', 'tarea' => 'Verificar periódicamente las afiliaciones y planillas de seguridad social del personal contratista vinculado a la operación de la copropiedad.'],
                    ['id' => 'cp-006', 'componente' => 'Contratistas y Proveedores', 'tarea' => 'Verificar periódicamente las afiliaciones y planillas de seguridad social del personal de administración vinculado a la representación legal de la copropiedad y atención administrativa a residentes.'],
                    ['id' => 'cp-007', 'componente' => 'Contratistas y Proveedores', 'tarea' => 'Validar los soportes SST exigidos a empresas contratistas previo al ingreso o ejecución de actividades en la copropiedad.'],
                    ['id' => 'cp-008', 'componente' => 'Contratistas y Proveedores', 'tarea' => 'Generar observaciones y recomendaciones derivadas de auditorías o revisiones realizadas a contratistas y proveedores.'],
                ],
            ],
            [
                'slug' => 'dotacion-epp',
                'titulo' => 'Dotación y Elementos de Protección Personal (EPP)',
                'icon' => 'fa-helmet-safety',
                'actividades' => [
                    ['id' => 'epp-001', 'componente' => 'Dotación y EPP', 'tarea' => 'Elaborar y actualizar la matriz de elementos de protección personal conforme a las actividades desarrolladas en la copropiedad.'],
                ],
            ],
            [
                'slug' => 'cumplimiento-normativo',
                'titulo' => 'Cumplimiento Normativo y Entidades de Control',
                'icon' => 'fa-gavel',
                'actividades' => [
                    ['id' => 'cnec-001', 'componente' => 'Cumplimiento Normativo y Entidades de Control', 'tarea' => 'Realizar el diligenciamiento y reporte anual de estándares mínimos del SG-SST conforme a la Resolución 0312 de 2019 y lineamientos del Ministerio del Trabajo.'],
                    ['id' => 'cnec-002', 'componente' => 'Cumplimiento Normativo y Entidades de Control', 'tarea' => 'Revisar los requerimientos y resultados de visitas realizadas por entidades de control relacionadas con SST y condiciones sanitarias.'],
                    ['id' => 'cnec-003', 'componente' => 'Cumplimiento Normativo y Entidades de Control', 'tarea' => 'Gestionar el seguimiento a observaciones, hallazgos o solicitudes de corrección emitidas por entidades de control.'],
                ],
            ],
            [
                'slug' => 'reglamentos-formalizacion',
                'titulo' => 'Reglamentos y Formalización SST',
                'icon' => 'fa-scroll',
                'actividades' => [
                    ['id' => 'rfs-001', 'componente' => 'Reglamentos y Formalización SST', 'tarea' => 'Elaborar y actualizar el Reglamento de Higiene y Seguridad Industrial conforme a las actividades y riesgos de la copropiedad.'],
                    ['id' => 'rfs-002', 'componente' => 'Reglamentos y Formalización SST', 'tarea' => 'Formalizar mediante soportes escritos la designación del responsable del SG-SST de la copropiedad.'],
                    ['id' => 'rfs-003', 'componente' => 'Reglamentos y Formalización SST', 'tarea' => 'Formalizar la designación del vigía de Seguridad y Salud en el Trabajo conforme a la normatividad vigente.'],
                    ['id' => 'rfs-004', 'componente' => 'Reglamentos y Formalización SST', 'tarea' => 'Gestionar la firma, publicación y conservación documental de reglamentos, políticas y documentos obligatorios del SG-SST.'],
                    ['id' => 'rfs-005', 'componente' => 'Reglamentos y Formalización SST', 'tarea' => 'Formalizar la exoneración del Comité de Convivencia Laboral cuando aplique según el número de trabajadores de la copropiedad.'],
                    ['id' => 'rfs-006', 'componente' => 'Reglamentos y Formalización SST', 'tarea' => 'Verificar la vigencia y disponibilidad de soportes documentales relacionados con designaciones y formalizaciones del SG-SST.'],
                    ['id' => 'rfs-007', 'componente' => 'Reglamentos y Formalización SST', 'tarea' => 'Mantener actualizados los documentos formales requeridos para evidenciar la implementación del SG-SST ante auditorías o visitas de inspección.'],
                ],
            ],
        ];
    }

    /**
     * Devuelve una sola actividad por su id global, junto con el slug del eje al que pertenece.
     * Retorna ['eje_slug' => ..., 'eje_titulo' => ..., 'id' => ..., 'componente' => ..., 'tarea' => ...] o null.
     */
    public static function getActividadById(string $id): ?array
    {
        foreach (self::all() as $eje) {
            foreach ($eje['actividades'] as $act) {
                if ($act['id'] === $id) {
                    return [
                        'eje_slug'   => $eje['slug'],
                        'eje_titulo' => $eje['titulo'],
                        'id'         => $act['id'],
                        'componente' => $act['componente'],
                        'tarea'      => $act['tarea'],
                    ];
                }
            }
        }
        return null;
    }

    /**
     * Devuelve TODAS las tareas (texto literal) en un array plano — útil para
     * comparar contra tbl_pta_cliente.actividad_plandetrabajo en duplicate-detection.
     */
    public static function flatTareas(): array
    {
        $out = [];
        foreach (self::all() as $eje) {
            foreach ($eje['actividades'] as $act) {
                $out[] = $act['tarea'];
            }
        }
        return $out;
    }
}
