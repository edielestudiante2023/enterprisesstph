# PROMPT PARA AGENTE: Regeneración masiva de PDFs del módulo Inspecciones

Copia y pega esto en un nuevo chat de Claude Code:

---

## Contexto crítico

El 2026-03-25 se perdieron TODOS los archivos PDF generados por el módulo de inspecciones debido a un `git clean -fd` en producción. Los datos en la base de datos están INTACTOS. Solo se perdieron los archivos físicos (PDFs, fotos).

Ya se migró la arquitectura: los uploads ahora van a `UPLOADS_PATH` (definido en `app/Config/Constants.php`, apunta a `/www/soportes-clientes/` en producción y `c:/xampp/soportes-clientes/` en local). Los controladores ya fueron migrados para usar `UPLOADS_PATH` en vez de `ROOTPATH . 'public/uploads/'`.

Lee el archivo `docs/INCIDENTE-PERDIDA-UPLOADS-20260325.md` para el contexto completo del incidente.

## Tarea 1: Crear comando Spark para regenerar TODOS los PDFs

Necesito un comando `php spark regenerar:pdfs` que haga lo siguiente:

### Lógica general

Para CADA módulo de inspección:
1. Consultar TODOS los registros de la tabla fuente del módulo
2. Para cada registro, llamar al método `generarPdfInterno($id)` del controlador correspondiente (este método genera el PDF con DOMPDF y lo guarda en `uploads/inspecciones/{modulo}/pdfs/`)
3. Luego llamar al método `uploadToReportes()` que copia el PDF a `UPLOADS_PATH/{nit}/` y crea/actualiza el registro en `tbl_reporte`

### Módulos y sus controladores/tablas

Cada controlador de inspección tiene esta estructura:
- `generarPdfInterno($id)` → genera el PDF y retorna la ruta relativa del archivo
- `uploadToReportes($inspeccion, $pdfPath)` → copia a carpeta NIT + inserta en tbl_reporte

| Módulo | Controlador | Tabla fuente | Método PDF |
|--------|------------|-------------|------------|
| Extintores | InspeccionExtintoresController | tbl_inspeccion_extintores | generarPdfInterno() |
| Locativa | InspeccionLocativaController | tbl_inspeccion_locativa | generarPdfInterno() |
| Señalización | InspeccionSenalizacionController | tbl_inspeccion_senalizacion | generarPdfInterno() |
| Botiquín | InspeccionBotiquinController | tbl_inspeccion_botiquin | generarPdfInterno() |
| Gabinetes | InspeccionGabineteController | tbl_inspeccion_gabinete | generarPdfInterno() |
| Comunicaciones | InspeccionComunicacionController | tbl_inspeccion_comunicacion | generarPdfInterno() |
| Recursos Seguridad | InspeccionRecursosSeguridadController | tbl_inspeccion_recursos_seguridad | generarPdfInterno() |
| Acta de Visita | ActaVisitaController | tbl_acta_visita | generarPdfInterno() |
| Reporte Capacitación | ReporteCapacitacionController | tbl_reporte_capacitacion | generarPdfInterno() |
| Asistencia Inducción | AsistenciaInduccionController | tbl_asistencia_induccion | generarPdfInterno() |
| Dotación Vigilante | DotacionVigilanteController | tbl_dotacion_vigilante | generarPdfInterno() |
| Dotación Todero | DotacionToderoController | tbl_dotacion_todero | generarPdfInterno() |
| Dotación Aseadora | DotacionAseadoraController | tbl_dotacion_aseadora | generarPdfInterno() |
| Evaluación Simulacro | EvaluacionSimulacroController | tbl_evaluacion_simulacro | generarPdfInterno() |
| Preparación Simulacro | PreparacionSimulacroController | tbl_preparacion_simulacro | generarPdfInterno() |
| HV Brigadista | HvBrigadistaController | tbl_hv_brigadista | generarPdfInterno() |
| Carta Vigía | CartaVigiaPwaController | tbl_carta_vigia | generarPdf() (privado) |
| Probabilidad Peligros | ProbabilidadPeligrosController | tbl_probabilidad_peligros | generarPdfInterno() |
| Matriz Vulnerabilidad | MatrizVulnerabilidadController | tbl_matriz_vulnerabilidad | generarPdfInterno() |
| Plan Emergencia | PlanEmergenciaController | tbl_plan_emergencia | generarPdfInterno() |
| KPI Residuos | KpiResiduosController | tbl_kpi_residuos | generarPdfInterno() |
| KPI Plagas | KpiPlagasController | tbl_kpi_plagas | generarPdfInterno() |
| KPI Limpieza | KpiLimpiezaController | tbl_kpi_limpieza | generarPdfInterno() |
| KPI Agua Potable | KpiAguaPotableController | tbl_kpi_agua_potable | generarPdfInterno() |
| Programa Residuos | ProgramaResiduosController | tbl_programa_residuos | generarPdfInterno() |
| Programa Plagas | ProgramaPlagasController | tbl_programa_plagas | generarPdfInterno() |
| Programa Limpieza | ProgramaLimpiezaController | tbl_programa_limpieza | generarPdfInterno() |
| Programa Agua Potable | ProgramaAguaPotableController | tbl_programa_agua_potable | generarPdfInterno() |
| Plan Saneamiento | PlanSaneamientoController | tbl_plan_saneamiento | generarPdfInterno() |
| Contingencia Plagas | PlanContingenciaPlagasController | tbl_contingencia_plagas | generarPdfInterno() |
| Contingencia Basura | PlanContingenciaBasuraController | tbl_contingencia_basura | generarPdfInterno() |
| Contingencia Agua | PlanContingenciaAguaController | tbl_contingencia_agua | generarPdfInterno() |
| Auditoría Zona Residuos | AuditoriaZonaResiduosController | tbl_auditoria_zona_residuos | generarPdfInterno() |
| Certificado Servicio | CertificadoServicioController | tbl_certificado_servicio | generarPdfInterno() |
| Planilla SS | PlanillaSSController | tbl_planilla_ss | (upload directo, no genera PDF) |

### Cómo ejecutar la regeneración

El comando Spark debe:
1. Instanciar cada controlador
2. Consultar todos los IDs de la tabla fuente
3. Para cada ID:
   a. Llamar `generarPdfInterno($id)` para generar el PDF
   b. Obtener el registro completo de la inspección
   c. Llamar `uploadToReportes($inspeccion, $pdfPath)` para copiarlo al NIT
4. Mostrar progreso: `[extintores 3/15] OK` o `[extintores 3/15] ERROR: ...`
5. Al final, mostrar resumen de cuántos se regeneraron y cuántos fallaron

**IMPORTANTE:** Los métodos `generarPdfInterno()` y `uploadToReportes()` son `private` en los controladores. Necesitas hacerlos `public` temporalmente o usar Reflection, o mejor: crear métodos públicos `regenerar($id)` en cada controlador que llamen a ambos internamente.

La opción más limpia es agregar un método público `regenerarPdf($id)` en cada controlador que llame a `generarPdfInterno()` + `uploadToReportes()` y retorne un array con `['success' => bool, 'message' => string]`.

### Estructura del comando Spark

Crear en: `app/Commands/RegenerarPdfs.php`

```php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RegenerarPdfs extends BaseCommand
{
    protected $group       = 'Mantenimiento';
    protected $name        = 'regenerar:pdfs';
    protected $description = 'Regenera todos los PDFs de inspecciones desde la BD';
    protected $usage       = 'regenerar:pdfs [--modulo=extintores] [--id=5] [--dry-run]';

    public function run(array $params) { ... }
}
```

Lee los controladores existentes para entender exactamente cómo funcionan `generarPdfInterno()` y `uploadToReportes()` en cada uno antes de crear el comando.

## Tarea 2: Desactivar envío de email SendGrid en ReportController

Cuando se regeneren los reportes masivamente, NO deben enviarse emails a los clientes.

Busca en `app/Controllers/ReportController.php` el método que envía emails por SendGrid cuando se crea o actualiza un reporte. Puede estar en un método `sendReport()`, `enviarReporte()`, o dentro de `store()`/`update()`.

**Acción:** Agregar un flag o condición que desactive el envío. Puede ser:
- Una variable de entorno `DISABLE_REPORT_EMAILS=true` en `.env`
- O un parámetro en el método

La forma más limpia: en el método que envía el email, agregar al inicio:
```php
if (env('DISABLE_REPORT_EMAILS', false)) {
    return; // Desactivado temporalmente para regeneración masiva
}
```

Y en el `.env` del servidor agregar `DISABLE_REPORT_EMAILS = true` durante la regeneración, y quitarlo después.

## Tarea 3: También regenerar contratos e informes de avance

Además de inspecciones:

### Contratos (ContractController)
- Tabla: `tbl_contratos` WHERE `contrato_generado = 1`
- Método: `generatePDF($id)` en ContractController
- Los genera con TCPDF (ContractPDFGenerator library)

### Informes de Avance (InformeAvancesController)
- Tabla: `tbl_informe_avances`
- Método: `generatePdf($id)` + `uploadToReportes()`

## Resultado esperado

Al finalizar:
- Todos los PDFs regenerables estarán en `/www/soportes-clientes/{nit}/`
- Los registros en `tbl_reporte` tendrán los enlaces actualizados
- Los PDFs de inspecciones estarán en `/www/soportes-clientes/inspecciones/{modulo}/pdfs/`
- No se habrán enviado emails a clientes

## Archivos clave que debes leer antes de empezar

1. `docs/INCIDENTE-PERDIDA-UPLOADS-20260325.md` — contexto del incidente
2. `app/Config/Constants.php` — definición de UPLOADS_PATH
3. `app/Controllers/FileServerController.php` — cómo se sirven los archivos
4. `app/Controllers/Inspecciones/InspeccionExtintoresController.php` — patrón típico de generarPdfInterno + uploadToReportes
5. `app/Controllers/ReportController.php` — encontrar el envío de emails por SendGrid
6. `app/Controllers/ContractController.php` — regeneración de contratos

---
