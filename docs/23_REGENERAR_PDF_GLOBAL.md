# 23 - Regenerar PDF Global + Seed detail_report

## Fecha: 2026-02-26

## Contexto
Al intentar finalizar inspecciones, se producía el error FK #1452:
```
Cannot add or update a child row: a foreign key constraint fails
("propiedad_horizontal"."tbl_reporte", CONSTRAINT "fk_detailreport"
FOREIGN KEY ("id_detailreport") REFERENCES "detail_report")
```

La tabla `detail_report` no tenía todos los registros necesarios.
Además, las inspecciones finalizadas no permitían regenerar el PDF si la plantilla se actualizaba.

---

## Cambios realizados

### 1. Seed de detail_report (33 tipos de inspección)

**Script:** `app/SQL/migrate_detail_report_seed.php`

| ID | Nombre | Ruta |
|----|--------|------|
| 1 | Actas de Visita | /inspecciones/acta-visita |
| 2 | Señalización | /inspecciones/senalizacion |
| 3 | Locativas | /inspecciones/inspeccion-locativa |
| 4 | Extintores | /inspecciones/extintores |
| 5 | Botiquín | /inspecciones/botiquin |
| 6 | Gabinetes | /inspecciones/gabinetes |
| 7 | Comunicaciones | /inspecciones/comunicaciones |
| 8 | Rec. Seguridad | /inspecciones/recursos-seguridad |
| 9 | Prob. Peligros | /inspecciones/probabilidad-peligros |
| 10 | Matriz Vuln. | /inspecciones/matriz-vulnerabilidad |
| 11 | Plan Emergencia | /inspecciones/plan-emergencia |
| 12 | Ev. Simulacro | /inspecciones/simulacro |
| 13 | HV Brigadista | /inspecciones/hv-brigadista |
| 14 | Dot. Vigilante | /inspecciones/dotacion-vigilante |
| 15 | Dot. Aseadora | /inspecciones/dotacion-aseadora |
| 16 | Dot. Todero | /inspecciones/dotacion-todero |
| 17 | Zona Residuos | /inspecciones/auditoria-zona-residuos |
| 18 | Capacitaciones | /inspecciones/reporte-capacitacion |
| 19 | Prep. Simulacro | /inspecciones/preparacion-simulacro |
| 20 | Asistencia | /inspecciones/asistencia-induccion |
| 21 | Limpieza y Des. | /inspecciones/limpieza-desinfeccion |
| 22 | Residuos Sólidos | /inspecciones/residuos-solidos |
| 23 | Control Plagas | /inspecciones/control-plagas |
| 24 | Agua Potable | /inspecciones/agua-potable |
| 25 | Plan Saneamiento | /inspecciones/plan-saneamiento |
| 26 | KPI Limpieza | /inspecciones/kpi-limpieza |
| 27 | KPI Residuos | /inspecciones/kpi-residuos |
| 28 | KPI Plagas | /inspecciones/kpi-plagas |
| 29 | KPI Agua Potable | /inspecciones/kpi-agua-potable |
| 30 | Carta Vigía | /inspecciones/carta-vigia |
| 31 | Mantenimientos | /inspecciones/mantenimientos |
| 32 | Pendientes | /inspecciones/pendientes |
| 33 | Accesos Rápidos | /inspecciones/urls |

**Ejecución:**
- LOCAL: 33 registros insertados
- PRODUCCION: 11 insertados, 22 ya existían (con nombres diferentes)

**Nota:** En producción los IDs 1-23 tienen nombres diferentes a esta tabla (datos preexistentes). Los IDs 24-33 y 37 coinciden.

### 2. Corrección PDF Agua Potable

**Template:** `app/Views/inspecciones/agua-potable/pdf.php`

Cambios para coincidir con `z_aguapotable.txt`:
- Labels tanque: agregado "(LITROS)" a capacidad individual y total
- Sección 1.11: "se evalúa el siguiente indicador de gestión" (singular)
- Numeración de indicadores: agregado 1️⃣ y 2️⃣
- Agregado label "Interpretación:" antes del texto explicativo

**Controller:** `app/Controllers/Inspecciones/ProgramaAguaPotableController.php`
- `id_detailreport`: 31 → 24 (corregido de "Mantenimientos" a "Agua Potable")

### 3. Botón "Regenerar PDF" en 29 módulos

**Script patcher:** `app/SQL/patch_regenerar_pdf.php`

Aplicado automáticamente a los 29 módulos con generación de PDF:

| Archivo | Cambio |
|---------|--------|
| `app/Views/inspecciones/*/view.php` (29) | Botón "Regenerar PDF" cuando estado = completo |
| `app/Controllers/Inspecciones/*Controller.php` (29) | Método `regenerarPdf($id)` |
| `app/Config/Routes.php` | 29 rutas GET `{modulo}/regenerar/(:num)` |

**Flujo del método `regenerarPdf()`:**
1. Verifica que la inspección existe y estado = completo
2. Llama a `generarPdfInterno($id)` con la plantilla actual
3. Actualiza `ruta_pdf` en la BD
4. Re-sube al sistema de reportes vía `uploadToReportes()` (si aplica)
5. Redirige con mensaje de éxito

**Módulos parchados:**
acta-visita, senalizacion, inspeccion-locativa, extintores, botiquin,
gabinetes, comunicaciones, recursos-seguridad, probabilidad-peligros,
matriz-vulnerabilidad, plan-emergencia, simulacro, hv-brigadista,
dotacion-vigilante, dotacion-aseadora, dotacion-todero,
auditoria-zona-residuos, reporte-capacitacion, preparacion-simulacro,
asistencia-induccion, limpieza-desinfeccion, residuos-solidos,
control-plagas, agua-potable, plan-saneamiento, kpi-limpieza,
kpi-residuos, kpi-plagas, kpi-agua-potable

---

## Verificación

- Todos los archivos pasan `php -l` sin errores de sintaxis
- Scripts de migración son idempotentes (INSERT con verificación previa)
- Patcher es idempotente (verifica si ya existe antes de insertar)
