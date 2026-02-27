# 24 - Análisis de Brechas: 5 Documentos de Estrategia vs Implementación

## Fecha: 2026-02-26

## Resumen Ejecutivo

Se analizaron 5 documentos de estrategia contra la implementación real en los 33 módulos de inspección. Hallazgos principales:

| Doc | Estrategia | Estado | Cobertura |
|-----|-----------|--------|-----------|
| 03 | PWA Layout | **IMPLEMENTADO** | 33/33 módulos (100%) |
| 07 | PDF Upload a Reportes | **IMPLEMENTADO** | 26/29 controladores con PDF (90%) |
| 08 | Autoguardado localStorage | **PARCIAL** | 23/31 formularios (74%) |
| 09 | Diseño PDF DOMPDF | **IMPLEMENTADO** | 30/33 módulos con PDF (100% de los que aplica) |
| 05 | Modo Offline (IndexedDB) | **NO IMPLEMENTADO** | 0% |

---

## 1. Doc 03 — PWA Layout (`03_PWA_LAYOUT.md`)

### Estado: COMPLETAMENTE IMPLEMENTADO

**Qué exige el documento:**
- `manifest_inspecciones.json` con scope `/`, display standalone, íconos 192+512
- Service Worker (`sw_inspecciones.js`) con cache-first para CDN, network-first para app
- `layout_pwa.php` con nav superior fijo, nav inferior con 5 íconos, padding 60px/70px
- Registro del SW desde login.php Y layout_pwa.php
- Meta tags Apple (mobile-web-app-capable, status-bar-style, touch-icon)
- Diseño mobile-first, cards con sombra, border-radius 12px, borde izquierdo dorado

**Implementación real:**
- `public/manifest_inspecciones.json` — OK, con scope `/`, íconos 192+512, standalone
- `public/sw_inspecciones.js` — OK, cache `inspecciones-v3`, CDN cache-first, network-first app
- `app/Views/inspecciones/layout_pwa.php` — OK, es el ÚNICO layout usado
- SW registrado en `login.php` (línea 747) y `layout_pwa.php` (línea 351)
- **33/33 módulos usan layout_pwa** (arquitectura de fragmentos, no `$this->extend()`)

**Brechas: NINGUNA**

---

## 2. Doc 07 — Estrategia PDF Upload (`07_ESTRATEGIA_PDF_UPLOAD.md`)

### Estado: IMPLEMENTADO con 3 brechas menores

**Qué exige el documento:**
- Método privado `uploadToReportes()` en cada controlador con PDF
- Inserción automática en `tbl_reporte` al finalizar inspección
- Usar `id_report_type=6` (GESTION SST) + `id_detailreport` único por módulo
- Prevención de duplicados con marcador `origen_id:{id}` en observaciones
- Copia del PDF a `uploads/{nit_cliente}/`
- Opcionalmente: extraer a trait/helper `ReporteAutoUpload` reutilizable

**Implementación real — 26 controladores CON uploadToReportes:**

| # | Controlador | id_detailreport |
|---|------------|-----------------|
| 1 | ActaVisitaController | 9 |
| 2 | InspeccionLocativaController | 10 |
| 3 | InspeccionSenalizacionController | 11 |
| 4 | InspeccionExtintoresController | 12 |
| 5 | InspeccionBotiquinController | 13 |
| 6 | InspeccionGabineteController | 14 |
| 7 | InspeccionComunicacionController | 15 |
| 8 | InspeccionRecursosSeguridadController | 16 |
| 9 | ProbabilidadPeligrosController | 17 |
| 10 | MatrizVulnerabilidadController | 18 |
| 11 | PlanEmergenciaController | 19 |
| 12 | EvaluacionSimulacroController | **21** |
| 13 | HvBrigadistaController | **22** |
| 14 | DotacionVigilanteController | **24** |
| 15 | DotacionToderoController | 25 |
| 16 | DotacionAseadoraController | 26 |
| 17 | AuditoriaZonaResiduosController | 27 |
| 18 | PreparacionSimulacroController | **28** |
| 19 | ReporteCapacitacionController | **21** |
| 20 | AsistenciaInduccionController | **22** y **23** (dinámico, 2 PDFs) |
| 21 | ProgramaLimpiezaController | **28** |
| 22 | ProgramaResiduosController | 29 |
| 23 | ProgramaPlagasController | 30 |
| 24 | ProgramaAguaPotableController | **24** |
| 25 | PlanSaneamientoController | 32 |
| 26 | KpiLimpiezaController | 33 (const DETAIL_ID) |
| 27 | KpiResiduosController | 34 (const DETAIL_ID) |
| 28 | KpiPlagasController | 35 (const DETAIL_ID) |
| 29 | KpiAguaPotableController | 36 (const DETAIL_ID) |

### Brecha 1: Controladores SIN uploadToReportes (3)

| Controlador | Razón probable |
|------------|----------------|
| CartaVigiaPwaController | Genera PDF pero no lo sube a reportes |
| MantenimientosPwaController | No genera PDF (solo formulario + lista) |
| PendientesPwaController | No genera PDF (solo formulario + lista) |
| UrlsPwaController | No genera PDF (solo links) |
| InspeccionesController | Dashboard principal, no genera PDFs |

**CartaVigia es el único que genera PDF pero NO tiene uploadToReportes.** Los demás no aplican.

### Brecha 2: IDs duplicados (CONFLICTOS)

| id_detailreport | Controladores que lo usan | Problema |
|-----------------|--------------------------|----------|
| **21** | EvaluacionSimulacroController + ReporteCapacitacionController | Ambos suben con mismo ID → reportes mezclados |
| **24** | DotacionVigilanteController + ProgramaAguaPotableController | Ambos suben con mismo ID → reportes mezclados |
| **28** | PreparacionSimulacroController + ProgramaLimpiezaController | Ambos suben con mismo ID → reportes mezclados |

### Brecha 3: IDs faltantes (sin asignar)

Los IDs **20, 23, 31** no son usados por ningún controlador:
- ID 20: Debería ser Asistencia (pero AsistenciaInduccion usa 22 y 23)
- ID 23: Usado por AsistenciaInduccion como segundo PDF
- ID 31: No asignado a ningún controlador

**Nota:** El trait/helper `ReporteAutoUpload` sugerido en el documento NUNCA se creó. Cada controlador tiene su propia implementación copy-paste de `uploadToReportes()`.

---

## 3. Doc 08 — Autoguardado localStorage (`08_ESTRATEGIA_AUTOGUARDADO.md`)

### Estado: PARCIALMENTE IMPLEMENTADO (74%)

**Qué exige el documento:**
- localStorage con clave `{modulo}_draft_{id|new}`
- Guardado cada 30 segundos (setInterval) + 2 segundos tras input (debounce)
- Restauración al cargar: SweetAlert2 pregunta "¿Restaurar borrador?"
- Expiración a 24 horas
- Limpieza al enviar formulario
- Indicador visual "Guardado HH:MM:SS"

**Formularios CON autoguardado (23/31):**

| # | Módulo | Patrón |
|---|--------|--------|
| 1 | acta_visita | Complejo (debounce + arrays dinámicos) |
| 2 | senalizacion | Complejo |
| 3 | inspeccion_locativa | Complejo |
| 4 | botiquin | Complejo |
| 5 | probabilidad-peligros | Complejo |
| 6 | matriz-vulnerabilidad | Complejo |
| 7 | agua-potable | Simple (setInterval 30s + debounce 2s) |
| 8 | asistencia-induccion | Simple |
| 9 | auditoria-zona-residuos | Simple |
| 10 | comunicaciones | Simple |
| 11 | control-plagas | Simple |
| 12 | dotacion-aseadora | Simple |
| 13 | dotacion-todero | Simple |
| 14 | dotacion-vigilante | Simple |
| 15 | extintores | Simple |
| 16 | gabinetes | Simple |
| 17 | limpieza-desinfeccion | Simple |
| 18 | plan-emergencia | Simple |
| 19 | plan-saneamiento | Simple |
| 20 | preparacion-simulacro | Simple |
| 21 | recursos-seguridad | Simple |
| 22 | reporte-capacitacion | Simple |
| 23 | residuos-solidos | Simple |

**Formularios SIN autoguardado (8/31):**

| # | Módulo | Razón probable |
|---|--------|----------------|
| 1 | **carta_vigia** | Formulario simple de una sola vez |
| 2 | **kpi-limpieza** | Formulario KPI (pocos campos) |
| 3 | **kpi-residuos** | Formulario KPI (pocos campos) |
| 4 | **kpi-plagas** | Formulario KPI (pocos campos) |
| 5 | **kpi-agua-potable** | Formulario KPI (pocos campos) |
| 6 | **mantenimientos** | Formulario auxiliar |
| 7 | **pendientes** | Formulario auxiliar |
| 8 | **urls** | Solo URLs, no necesita draft |

**Justificación de la brecha:** Los 4 KPIs tienen formularios cortos (3-5 campos numéricos) donde el autoguardado aporta poco valor. `mantenimientos`, `pendientes` y `urls` son módulos auxiliares sin formularios complejos. `carta_vigia` genera una carta con datos mínimos.

---

## 4. Doc 09 — Diseño PDF DOMPDF (`09_DISENO_PDF_ACTA.md`)

### Estado: IMPLEMENTADO (para los módulos que aplica)

**Qué exige el documento:**
- Usar TABLAS para layout (no flexbox/grid)
- No usar: CSS variables, calc(), box-shadow, border-radius en DOMPDF
- Unidades en `px` (NO `cm`)
- `@page { margin: 100px 70px 80px 90px }` (aprox ICONTEC)
- Font: DejaVu Sans (UTF-8)
- Logo como base64 inline
- Fotos max 180px width con borde
- Papel Letter (8.5x11")

**Módulos con PDF (30/33):**

Todos los 30 módulos con `pdf.php` generan PDFs vía DOMPDF. Las restricciones técnicas del documento se aplican a `acta_visita` específicamente, pero el patrón se replicó en los demás módulos.

**Módulos SIN PDF (3):**
- `mantenimientos` — solo formulario/lista
- `pendientes` — solo formulario/lista
- `urls` — solo enlaces rápidos

**Brechas: NINGUNA funcional.** El documento es específico para acta_visita pero el patrón se extendió.

---

## 5. Doc 05 — Estrategia Offline (`05_ESTRATEGIA_OFFLINE.md`)

### Estado: NO IMPLEMENTADO (0%)

**Qué exige el documento:**

1. **IndexedDB** (`inspecciones_sst`) con stores:
   - `clientes` — clientes activos con logos base64
   - `pendientes` — tareas abiertas precargadas
   - `mantenimientos` — mantenimientos próximos
   - `actas_offline` — actas completas guardadas sin conexión
   - `sync_queue` — cola de operaciones pendientes de sincronización

2. **Endpoint de pre-carga:**
   - `GET /inspecciones/api/sync-data` devuelve clientes, pendientes, mantenimientos con logos base64

3. **Capacidad offline completa:**
   - Llenar formularios sin internet
   - Capturar firmas (canvas → base64)
   - Tomar fotos (comprimir a 1200px max, 0.7 JPEG)
   - Guardar en IndexedDB localmente
   - Indicador "X acta(s) pendientes de sincronización"

4. **Background Sync:**
   - Android Chrome: Background Sync API (`register('sync-actas')`)
   - iOS fallback: evento `online` + `visibilitychange` + intervalo 30s
   - Handler en SW: `sincronizarActasPendientes()`

5. **Endpoint de sincronización:**
   - `POST /inspecciones/acta-visita/sync` recibe JSON + FormData (firmas, fotos, soportes)

6. **Indicadores UI:**
   - Barra roja offline: "Sin conexión - Los datos se guardarán localmente"
   - Barra amarilla sync: "Sincronizando X acta(s)..."
   - Badges de estado: ✅ synced, 📝 draft, ⏳ pending, 🔄 syncing, ❌ error

7. **Manejo de errores:**
   - Sesión expirada → mensaje especial
   - Error de red → retry con backoff exponencial (max 5 intentos)
   - Fallas parciales → transacción atómica por acta

**Implementación real:**
- IndexedDB: **NO** (0 referencias en todo el codebase)
- Background Sync: **NO** (0 referencias)
- navigator.onLine listeners: **NO**
- Endpoint sync-data: **NO existe**
- Endpoint sync POST: **NO existe**
- Indicadores offline UI: **NO**
- Cola de sincronización: **NO**

**Lo único implementado del ecosistema offline:**
- Service Worker con cache de assets estáticos (CSS, JS, CDN)
- localStorage para borradores de formularios (doc 08, no doc 05)

**Razón de no implementación:** El documento marca explícitamente el estado como "PENDIENTE DE IMPLEMENTACIÓN". Es el feature más complejo de los 5 y requiere cambios significativos en frontend (IndexedDB, SW sync handlers, UI indicators) y backend (2 endpoints nuevos, manejo de FormData multipart con fotos comprimidas).

---

## Resumen de Acciones Requeridas

### Prioridad ALTA (Bugs/Conflictos)

| # | Acción | Esfuerzo |
|---|--------|----------|
| 1 | Corregir `id_detailreport` duplicado 21 (Simulacro vs Capacitación) | 5 min |
| 2 | Corregir `id_detailreport` duplicado 24 (Dot.Vigilante vs Agua Potable) | 5 min |
| 3 | Corregir `id_detailreport` duplicado 28 (Prep.Simulacro vs Limpieza) | 5 min |
| 4 | Agregar `uploadToReportes()` a CartaVigiaPwaController | 30 min |

### Prioridad MEDIA (Completar cobertura)

| # | Acción | Esfuerzo |
|---|--------|----------|
| 5 | Agregar autoguardado a 4 KPIs (kpi-limpieza, kpi-residuos, kpi-plagas, kpi-agua-potable) | 1 hora |
| 6 | Agregar autoguardado a carta_vigia form | 15 min |
| 7 | Crear trait `ReporteAutoUpload` para DRY (refactor opcional) | 2 horas |

### Prioridad BAJA (Feature nuevo completo)

| # | Acción | Esfuerzo |
|---|--------|----------|
| 8 | Implementar estrategia offline completa (doc 05) | 3-5 días |
