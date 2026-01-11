<?php

namespace App\Libraries;

/**
 * AccessLibrary - Catálogo estático de accesos/menús del dashboard
 *
 * Define los accesos disponibles en el sistema de Propiedad Horizontal.
 * Cada acceso representa un documento/funcionalidad del ciclo PHVA.
 *
 * IMPORTANTE: Este archivo reemplaza la tabla 'accesos' en BD
 * Las URLs corresponden a las rutas configuradas en Routes.php
 */
class AccessLibrary
{
    /**
     * Obtiene un acceso específico por ID
     */
    public static function getAccess($accessId)
    {
        $accesses = self::getAllAccesses();
        return $accesses[$accessId] ?? null;
    }

    /**
     * Obtiene todos los accesos del sistema
     * Datos migrados desde la tabla accesos de BD
     */
    public static function getAllAccesses()
    {
        return [
            // PLANEAR
            1 => ['id_acceso' => 1, 'nombre' => '1.1.1 Asignación de Responsable', 'url' => '/asignacionResponsable/1', 'dimension' => 'Planear'],
            2 => ['id_acceso' => 2, 'nombre' => '1.1.2 Asignación de Responsabilidades', 'url' => '/asignacionResponsabilidades/1', 'dimension' => 'Planear'],
            9 => ['id_acceso' => 9, 'nombre' => '1.1.9 Formato de Acuerdo de Confidencialidad COCOLAB', 'url' => '/confidencialidadCocolab/1', 'dimension' => 'Planear'],
            15 => ['id_acceso' => 15, 'nombre' => '1.2.1 Programa de Capacitación y Entrenamiento', 'url' => '/prgCapacitacion/1', 'dimension' => 'Planear'],
            16 => ['id_acceso' => 16, 'nombre' => '1.2.2 Programa de Inducción y Reinducción', 'url' => '/prgInduccion/1', 'dimension' => 'Planear'],
            18 => ['id_acceso' => 18, 'nombre' => '2.1.1 Política de Seguridad y Salud en el Trabajo', 'url' => '/politicaSst/1', 'dimension' => 'Planear'],
            19 => ['id_acceso' => 19, 'nombre' => '2.1.2 Política de No Alcohol, Drogas ni Tabaco', 'url' => '/politicaAlcohol/1', 'dimension' => 'Planear'],
            20 => ['id_acceso' => 20, 'nombre' => '2.1.3 Política de Prevención, Preparación y Respuesta ante Emergencias', 'url' => '/politicaEmergencias/1', 'dimension' => 'Planear'],
            21 => ['id_acceso' => 21, 'nombre' => '2.1.4 Política para elementos de protección personal', 'url' => '/politicaEpps/1', 'dimension' => 'Planear'],
            22 => ['id_acceso' => 22, 'nombre' => '2.1.5 Política de Seguridad Vial', 'url' => '/politicaPesv/1', 'dimension' => 'Planear'],
            23 => ['id_acceso' => 23, 'nombre' => '2.1.6 Reglamento de Higiene y Seguridad Industrial', 'url' => '/regHigsegind/1', 'dimension' => 'Planear'],
            24 => ['id_acceso' => 24, 'nombre' => '2.1.7 Objetivos del Sistema de Gestión de la Seguridad y Salud en el Trabajo (SG-SST)', 'url' => '/oBjetivos/1', 'dimension' => 'Planear'],
            25 => ['id_acceso' => 25, 'nombre' => '2.5.1 Procedimiento para el Control y Conservación de Documentos del SG-SST', 'url' => '/documentosSgsst/1', 'dimension' => 'Planear'],
            26 => ['id_acceso' => 26, 'nombre' => '2.5.2 Procedimiento de Rendición de Cuentas en Seguridad y Salud en el Trabajo (SG-SST)', 'url' => '/rendicionCuentas/1', 'dimension' => 'Planear'],
            34 => ['id_acceso' => 34, 'nombre' => '2.12.0 Formato de Asignación de Responsable del PESV', 'url' => '/responsablePesv/1', 'dimension' => 'Planear'],
            41 => ['id_acceso' => 41, 'nombre' => 'Plan de Saneamiento Básico', 'url' => '/saneamientoBasico/1', 'dimension' => 'Planear'],

            // HACER
            3 => ['id_acceso' => 3, 'nombre' => '1.1.3 Asignación de Vigía', 'url' => '/asignacionVigia/1', 'dimension' => 'Hacer'],
            5 => ['id_acceso' => 5, 'nombre' => '1.1.5 Registro de Asistencia a Capacitación', 'url' => '/registroAsistencia/1', 'dimension' => 'Hacer'],
            6 => ['id_acceso' => 6, 'nombre' => '1.1.6 Acta de Reunión Copasst', 'url' => '/actaCopasst/1', 'dimension' => 'Hacer'],
            7 => ['id_acceso' => 7, 'nombre' => '1.1.7 Formato de Inscripción al Copasst', 'url' => '/inscripcionCopasst/1', 'dimension' => 'Hacer'],
            8 => ['id_acceso' => 8, 'nombre' => '1.1.8 Formato de Asistencia (No aplica para capacitaciones)', 'url' => '/formatoAsistencia/1', 'dimension' => 'Hacer'],
            10 => ['id_acceso' => 10, 'nombre' => '1.1.10 Formato de Inscripción al COCOLAB', 'url' => '/inscripcionCocolab/1', 'dimension' => 'Hacer'],
            11 => ['id_acceso' => 11, 'nombre' => '1.1.10.1 Acta de Reunión COCOLAB', 'url' => '/actaCocolab/1', 'dimension' => 'Hacer'],
            12 => ['id_acceso' => 12, 'nombre' => '1.1.11 Formato quejas de situaciones que pueden constituir acoso laboral', 'url' => '/quejaCocolab/1', 'dimension' => 'Hacer'],
            27 => ['id_acceso' => 27, 'nombre' => '2.5.3 Procedimiento de Comunicación Interna y Externa en Seguridad y Salud en el Trabajo (SG-SST)', 'url' => '/comunicacionInterna/1', 'dimension' => 'Hacer'],
            28 => ['id_acceso' => 28, 'nombre' => '2.5.4 Manual de Contratación para Proveedores y Contratistas en Seguridad y Salud en el Trabajo (SST)', 'url' => '/manProveedores/1', 'dimension' => 'Hacer'],
            31 => ['id_acceso' => 31, 'nombre' => '2.5.0 Procedimiento de Reporte de Accidentes e Incidentes de Trabajo', 'url' => '/reporteAccidente/1', 'dimension' => 'Hacer'],
            32 => ['id_acceso' => 32, 'nombre' => '2.9.0 Procedimiento de Inspecciones Planeadas y No Planeadas en Seguridad y Salud en el Trabajo', 'url' => '/inspeccionPlanynoplan/1', 'dimension' => 'Hacer'],
            33 => ['id_acceso' => 33, 'nombre' => '2.11.0 Procedimiento de Entrega y Reposición de EPP y Dotación', 'url' => '/entregaDotacion/1', 'dimension' => 'Hacer'],
            37 => ['id_acceso' => 37, 'nombre' => '4.1.1 Procedimiento de Revisión por la Alta Dirección del SG-SST', 'url' => '/revisionAltagerencia/1', 'dimension' => 'Hacer'],
            38 => ['id_acceso' => 38, 'nombre' => '4.1.2 Procedimiento para Acciones Correctivas, Preventivas y de Mejora (ACPM)', 'url' => '/accionCorrectiva/1', 'dimension' => 'Hacer'],
            39 => ['id_acceso' => 39, 'nombre' => '4.1.3 Programa de Pausas Activas', 'url' => '/pausasActivas/1', 'dimension' => 'Hacer'],
            40 => ['id_acceso' => 40, 'nombre' => '4.1.4 Procedimiento de Identificación, Evaluación y Actualización de Requisitos Legales', 'url' => '/requisitosLegales/1', 'dimension' => 'Hacer'],

            // VERIFICAR
            4 => ['id_acceso' => 4, 'nombre' => '1.1.4 Exoneración de Comité de Convivencia Laboral', 'url' => '/exoneracionCocolab/1', 'dimension' => 'Verificar'],
            13 => ['id_acceso' => 13, 'nombre' => '1.1.12 Manual de Convivencia Laboral', 'url' => '/manconvivenciaLaboral/1', 'dimension' => 'Verificar'],
            14 => ['id_acceso' => 14, 'nombre' => '1.1.13 Conformación y Funcionamiento del comité de Convivencia Laboral', 'url' => '/prcCocolab/1', 'dimension' => 'Verificar'],
            17 => ['id_acceso' => 17, 'nombre' => '1.2.3 Evaluación de la Inducción y/o Reinducción', 'url' => '/ftevaluacionInduccion/1', 'dimension' => 'Verificar'],
            29 => ['id_acceso' => 29, 'nombre' => '2.2.0 Procedimiento para la Toma de Exámenes Médicos Ocupacionales', 'url' => '/examenMedico/1', 'dimension' => 'Verificar'],
            30 => ['id_acceso' => 30, 'nombre' => '2.2.1 Programa de Medicina Preventiva y del Trabajo', 'url' => '/medPreventiva/1', 'dimension' => 'Verificar'],
            35 => ['id_acceso' => 35, 'nombre' => '2.14.0 Divulgación de Recomendaciones Médicas', 'url' => '/responsabilidadesSalud/1', 'dimension' => 'Verificar'],
            36 => ['id_acceso' => 36, 'nombre' => '4.1.0 Procedimiento para la Identificación de Peligros y Valoración de Riesgos', 'url' => '/indentPeligros/1', 'dimension' => 'Verificar'],

            // INDICADORES
            42 => ['id_acceso' => 42, 'nombre' => 'Cumplimiento del plan de trabajo anual', 'url' => '/planDeTrabajoKpi/1', 'dimension' => 'Indicadores'],
            43 => ['id_acceso' => 43, 'nombre' => 'Cumplimiento de medidas de intervención de la matriz de identificación de peligros, valoración de riesgos y determinación de controles', 'url' => '/mipvrdcKpi/1', 'dimension' => 'Indicadores'],
            44 => ['id_acceso' => 44, 'nombre' => 'Cumplimiento de los programas de riesgos prioritarios', 'url' => '/gestionriesgoKpi/1', 'dimension' => 'Indicadores'],
            45 => ['id_acceso' => 45, 'nombre' => 'Cumplimiento de los programas de vigilancia epidemiológica', 'url' => '/vigepidemiologicaKpi/1', 'dimension' => 'Indicadores'],
            46 => ['id_acceso' => 46, 'nombre' => 'Evaluación del sistema', 'url' => '/evinicialKpi/1', 'dimension' => 'Indicadores'],
            47 => ['id_acceso' => 47, 'nombre' => 'Eficacia de las acciones', 'url' => '/accpreventivaKpi/1', 'dimension' => 'Indicadores'],
            48 => ['id_acceso' => 48, 'nombre' => 'Cumplimiento legal', 'url' => '/cumplilegalKpi/1', 'dimension' => 'Indicadores'],
            49 => ['id_acceso' => 49, 'nombre' => 'Cumplimiento al programa de capacitación', 'url' => '/capacitacionKpi/1', 'dimension' => 'Indicadores'],
            50 => ['id_acceso' => 50, 'nombre' => 'Estructura del SG-SST', 'url' => '/estructuraKpi/1', 'dimension' => 'Indicadores'],
            51 => ['id_acceso' => 51, 'nombre' => 'Reporte, investigación y análisis estadístico de incidentes, accidentes de trabajo y enfermedades laborales', 'url' => '/atelKpi/1', 'dimension' => 'Indicadores'],
            52 => ['id_acceso' => 52, 'nombre' => 'Frecuencia de accidentes laborales', 'url' => '/indicefrecuenciaKpi/1', 'dimension' => 'Indicadores'],
            53 => ['id_acceso' => 53, 'nombre' => 'Severidad de accidentes laborales', 'url' => '/indiceseveridadKpi/1', 'dimension' => 'Indicadores'],
            54 => ['id_acceso' => 54, 'nombre' => 'Proporción de accidentes mortales', 'url' => '/mortalidadKpi/1', 'dimension' => 'Indicadores'],
            55 => ['id_acceso' => 55, 'nombre' => 'Prevalencia de enfermedades laborales', 'url' => '/prevalenciaKpi/1', 'dimension' => 'Indicadores'],
            56 => ['id_acceso' => 56, 'nombre' => 'Incidencia de enfermedades laborales', 'url' => '/incidenciaKpi/1', 'dimension' => 'Indicadores'],
            57 => ['id_acceso' => 57, 'nombre' => 'Resultados de los programas de rehabilitación', 'url' => '/rehabilitacionKpi/1', 'dimension' => 'Indicadores'],
            58 => ['id_acceso' => 58, 'nombre' => 'Ausentismo', 'url' => '/ausentismoKpi/1', 'dimension' => 'Indicadores'],
            59 => ['id_acceso' => 59, 'nombre' => 'Resultado', 'url' => '/todoslosKpi/1', 'dimension' => 'Indicadores'],
        ];
    }

    /**
     * Obtiene accesos filtrados por dimensión
     */
    public static function getAccessesByDimension($dimension)
    {
        $all = self::getAllAccesses();
        return array_filter($all, function($access) use ($dimension) {
            return $access['dimension'] === $dimension;
        });
    }

    /**
     * Obtiene accesos por estándar (mensual, bimensual, trimestral, proyecto)
     * Basado en el mapeo de estandares_accesos en BD
     */
    public static function getAccessesByStandard($standardName)
    {
        // Mapeo basado en los datos de estandares_accesos (id_estandar = 1 = Mensual)
        $standardMappings = [
            'Mensual' => [1, 2, 3, 4, 5, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25, 26, 28, 31, 36, 41],
            'Bimensual' => [1, 2, 3, 4, 5, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25, 26, 28, 31, 36, 41],
            'Trimestral' => [1, 2, 3, 4, 5, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25, 26, 28, 31, 36, 41],
            'Proyecto' => [1, 2, 3, 4, 5, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25, 26, 28, 31, 36, 41]
        ];

        $accessIds = $standardMappings[$standardName] ?? [];
        $all = self::getAllAccesses();

        $result = [];
        foreach ($accessIds as $id) {
            if (isset($all[$id])) {
                $result[] = $all[$id];
            }
        }

        return $result;
    }

    /**
     * Verifica si un acceso existe
     */
    public static function exists($accessId)
    {
        return self::getAccess($accessId) !== null;
    }
}
