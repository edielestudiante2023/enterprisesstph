# 25 - Fix: Editar y Eliminar en Todos los Módulos de Inspección

## Fecha: 2026-02-26

## Problema

El proyecto de inspecciones fue rechazado porque **no permite editar ni eliminar registros finalizados (estado='completo')**. Los revisores exigen que TODOS los registros tengan opción de editar y eliminar, independientemente de su estado.

---

## Diagnóstico

### A. Módulos con edit/update/delete que BLOQUEAN estado='completo' (27 módulos)

Estos 27 controladores ya tienen los métodos `edit()`, `update()` y `delete()`, pero BLOQUEAN la operación cuando `estado === 'completo'`:

| # | Módulo | Controlador | Bloqueo edit | Bloqueo delete | Bloqueo en list.php |
|---|--------|-------------|--------------|----------------|---------------------|
| 1 | acta_visita | ActaVisitaController | edit() redirige a view | delete() rechaza | Solo borrador |
| 2 | senalizacion | InspeccionSenalizacionController | edit() redirige a view | delete() rechaza | Solo borrador |
| 3 | inspeccion_locativa | InspeccionLocativaController | edit() redirige a view | delete() rechaza | Solo borrador |
| 4 | extintores | InspeccionExtintoresController | edit() redirige a view | delete() rechaza | Solo borrador |
| 5 | botiquin | InspeccionBotiquinController | edit() redirige a view | delete() rechaza | Solo borrador |
| 6 | gabinetes | InspeccionGabineteController | edit() redirige a view | delete() rechaza | Solo borrador |
| 7 | comunicaciones | InspeccionComunicacionController | edit() redirige a view | delete() rechaza | Solo borrador |
| 8 | recursos-seguridad | InspeccionRecursosSeguridadController | edit() redirige a view | delete() rechaza | Solo borrador |
| 9 | probabilidad-peligros | ProbabilidadPeligrosController | edit() redirige a view | delete() rechaza | Solo borrador |
| 10 | matriz-vulnerabilidad | MatrizVulnerabilidadController | edit() redirige a view | delete() rechaza | Solo borrador |
| 11 | plan-emergencia | PlanEmergenciaController | edit() redirige a view | delete() rechaza | Solo borrador |
| 12 | dotacion-vigilante | DotacionVigilanteController | edit() redirige a view | delete() rechaza | Solo borrador |
| 13 | dotacion-aseadora | DotacionAseadoraController | edit() redirige a view | delete() rechaza | Solo borrador |
| 14 | dotacion-todero | DotacionToderoController | edit() redirige a view | delete() rechaza | Solo borrador |
| 15 | auditoria-zona-residuos | AuditoriaZonaResiduosController | edit() redirige a view | delete() rechaza | Solo borrador |
| 16 | reporte-capacitacion | ReporteCapacitacionController | edit() redirige a view | delete() rechaza | Solo borrador |
| 17 | preparacion-simulacro | PreparacionSimulacroController | edit() redirige a view | delete() rechaza | Solo borrador |
| 18 | asistencia-induccion | AsistenciaInduccionController | edit() redirige a view | delete() rechaza | Solo borrador |
| 19 | limpieza-desinfeccion | ProgramaLimpiezaController | edit() redirige a view | delete() rechaza | Solo borrador |
| 20 | residuos-solidos | ProgramaResiduosController | edit() redirige a view | delete() rechaza | Solo borrador |
| 21 | control-plagas | ProgramaPlagasController | edit() redirige a view | delete() rechaza | Solo borrador |
| 22 | agua-potable | ProgramaAguaPotableController | edit() redirige a view | delete() rechaza | Solo borrador |
| 23 | plan-saneamiento | PlanSaneamientoController | edit() redirige a view | delete() rechaza | Solo borrador |
| 24 | kpi-limpieza | KpiLimpiezaController | edit() redirige a view | NO bloquea (bug) | Solo borrador |
| 25 | kpi-residuos | KpiResiduosController | edit() redirige a view | NO bloquea (bug) | Solo borrador |
| 26 | kpi-plagas | KpiPlagasController | edit() redirige a view | NO bloquea (bug) | Solo borrador |
| 27 | kpi-agua-potable | KpiAguaPotableController | edit() redirige a view | NO bloquea (bug) | Solo borrador |

### B. Módulos SIN edit/update (3 módulos)

| # | Módulo | Controlador | Tiene form.php | Tiene create/store | Tiene edit/update |
|---|--------|-------------|----------------|--------------------|-------------------|
| 12 | simulacro | EvaluacionSimulacroController | NO | NO | NO |
| 13 | hv-brigadista | HvBrigadistaController | NO | NO | NO |
| 30 | carta_vigia | CartaVigiaPwaController | SÍ (solo crear) | SÍ | NO |

### C. Patrón en list.php (30 módulos)

**Todos** los list.php usan este patrón que oculta editar/eliminar para registros completados:

```php
<?php if ($insp['estado'] === 'borrador'): ?>
    <a href="edit/...">Editar</a>
    <a href="delete/...">Eliminar</a>
<?php else: ?>
    <a href="view/...">Ver</a>
    <a href="pdf/...">PDF</a>
<?php endif; ?>
```

### D. Patrón en controladores (27 módulos)

```php
// edit() - bloquea completados
if ($inspeccion['estado'] === 'completo') {
    return redirect()->to('/inspecciones/{modulo}/view/' . $id);
}

// update() - bloquea completados
if (!$inspeccion || $inspeccion['estado'] === 'completo') {
    return redirect()->to('/inspecciones/{modulo}')->with('error', '...');
}

// delete() - bloquea completados
if ($inspeccion['estado'] === 'completo') {
    return redirect()->to('/inspecciones/{modulo}')->with('error', '...');
}
```

---

## Solución Implementada

### Paso 1: Controladores — Desbloquear edit/update/delete (27 módulos)

**En `edit()`:** Eliminar el redirect a view cuando estado='completo'. Cuando un registro completo se edita, su estado revierte a 'borrador' para que pueda re-finalizarse.

**En `update()`:** Eliminar la condición `$inspeccion['estado'] === 'completo'` del guard. Si se edita un registro completo, se revierte a borrador.

**En `delete()`:** Eliminar la condición `$inspeccion['estado'] === 'completo'` del guard. Permitir eliminar cualquier registro con confirmación SweetAlert2.

### Paso 2: Vistas list.php — Mostrar botones para TODOS los registros (30 módulos)

Reemplazar el patrón condicional por uno que muestre TODOS los botones siempre:

```php
<a href="edit/...">Editar</a>
<a href="delete/...">Eliminar</a>
<?php if ($insp['estado'] === 'completo'): ?>
    <a href="view/...">Ver</a>
    <a href="pdf/...">PDF</a>
<?php endif; ?>
```

### Paso 3: Carta Vigía — Agregar edit/update

CartaVigiaPwaController ya tiene `create()`/`store()` y `form.php`. Se agregan métodos `edit()` y `update()`, y se modifica el form.php para soportar modo edición.

### Paso 4: Simulacro y HV Brigadista — Pendiente

Estos 2 módulos **NO tienen formulario de creación ni edición** (no existe form.php). Los registros se ingresan por un flujo diferente. Crear formularios de edición completos para estos módulos requiere un esfuerzo mayor y se documentará como tarea separada.

---

## Archivos Modificados

### Controladores (27 archivos)
- Eliminada condición `estado === 'completo'` en `edit()`, `update()`, `delete()`
- `update()` revierte estado a 'borrador' al editar registro completado

### Vistas list.php (30 archivos)
- Botones editar/eliminar visibles para TODOS los registros
- Botones ver/PDF visibles solo cuando estado='completo'

### CartaVigiaPwaController + carta_vigia/form.php
- Nuevos métodos: `edit()`, `update()`
- Nueva ruta: `carta-vigia/edit/(:num)`, `carta-vigia/update/(:num)`
- form.php actualizado para soportar modo edición

### Patcher: `app/SQL/patch_editar_eliminar_global.php`
- Script CLI que realiza todas las modificaciones de forma idempotente

---

## Comportamiento Después del Fix

| Acción | Antes | Después |
|--------|-------|---------|
| Editar borrador | Permitido | Permitido |
| Editar completo | BLOQUEADO (redirige a view) | Permitido (revierte a borrador) |
| Eliminar borrador | Permitido | Permitido |
| Eliminar completo | BLOQUEADO | Permitido (con confirmación) |
| Ver PDF completo | Permitido | Permitido |
| Regenerar PDF completo | Permitido | Permitido |

**Flujo al editar un registro completo:**
1. Usuario ve lista → click "Editar" en registro completo
2. Se abre formulario con datos pre-cargados
3. Usuario modifica campos y guarda
4. Estado revierte a 'borrador'
5. Usuario puede re-finalizar para generar nuevo PDF
