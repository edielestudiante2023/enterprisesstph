# Implementacion Modulo Informe de Avances — Bitacora

> Registro completo de la implementacion del modulo "Informe de Avances" (ex "Informe Cierre de Mes" de AppSheet), incluyendo decisiones de diseno, archivos creados y estado actual.

---

## Origen

El modulo reemplaza el formulario "INFORME CIERRE DE MES" de AppSheet (28 columnas, formato FT-SST-205). Se renombra a "Informe de Avances" porque hay clientes mensuales, bimensuales y trimestrales.

**Diferencias clave vs AppSheet:**
- Puntajes auto-calculados desde las tablas del sistema (no ingreso manual)
- Periodo auto-calculado desde ultimo informe del cliente hasta hoy
- Resumen de avance generado con asistencia de IA (OpenAI gpt-4o-mini)
- Indicadores renderizados como barras de progreso en el PDF (no screenshots)
- Soportes opcionales con imagen (hasta 4 pares texto+imagen)
- API REST para orquestacion desde OpenClaw

---

## Fase 1: Migracion SQL — COMPLETADA

**Archivo:** `app/SQL/migrate_informe_avances.php`

**Tabla creada:** `tbl_informe_avances` (33 columnas)

Columnas principales:
- `id`, `id_cliente`, `id_consultor`
- Periodo: `fecha_desde`, `fecha_hasta`, `anio`
- Metricas snapshot: `puntaje_anterior`, `puntaje_actual`, `diferencia_neta`, `estado_avance`
- Indicadores: `indicador_plan_trabajo`, `indicador_capacitacion`
- Screenshots opcionales: `img_cumplimiento_estandares`, `img_indicador_plan_trabajo`, `img_indicador_capacitacion`
- Contenido: `resumen_avance`, `observaciones`, `actividades_abiertas`, `actividades_cerradas_periodo`
- Enlaces: `enlace_dashboard`, `acta_visita_url`
- Soportes: 4 pares (`soporte_N_texto` + `soporte_N_imagen`)
- Sistema: `ruta_pdf`, `estado` (borrador/completo), timestamps

**detail_report:** Registro id=37, nombre "INFORME DE AVANCES" insertado en tabla `detail_report`.

**Ejecutada en:**
- LOCAL: OK (2 queries, 0 errores)
- PRODUCCION: OK (2 queries, 0 errores) — via `php app/SQL/migrate_informe_avances.php production`

---

## Fase 2: Modelo — COMPLETADA

**Archivo:** `app/Models/InformeAvancesModel.php`

Metodos:
- `getByConsultor($idConsultor, $estado?)` — JOIN tbl_clientes
- `getPendientesByConsultor($idConsultor)` — borradores del consultor
- `getAllPendientes()` — admin ve todos los borradores
- `getByCliente($idCliente)` — historial del cliente
- `getUltimoByCliente($idCliente)` — ultimo informe completo (para puntaje_anterior y fecha_desde)

---

## Fase 3: Servicio de metricas — COMPLETADA

**Archivo:** `app/Libraries/MetricasInformeService.php`

| Metodo | Fuente | Calculo |
|--------|--------|---------|
| `calcularCumplimientoEstandares()` | `evaluacion_inicial_sst` | `SUM(valor) / SUM(puntaje_cuantitativo) * 100` |
| `getPuntajeAnterior()` | `tbl_informe_avances` | Puntaje del informe previo del cliente |
| `getFechaDesde()` | `tbl_informe_avances` | Dia siguiente al ultimo informe o inicio contrato |
| `calcularIndicadorPlanTrabajo()` | `tbl_pta_cliente` | `COUNT(CERRADA) / COUNT(*) * 100` |
| `calcularIndicadorCapacitacion()` | `tbl_cronog_capacitacion` | `EJECUTADA / (PROGRAMADA + EJECUTADA) * 100` |
| `getActividadesAbiertas()` | `tbl_pendientes` | WHERE estado='ABIERTA' AND id_cliente=? |
| `getActividadesCerradasPeriodo()` | `tbl_pta_transiciones` + `tbl_pta_cliente` | JOIN, filtro fecha y estado_nuevo CERRADA |
| `calcularEstadoAvance()` | Calculado | Segun diferencia (ver reglas) |
| `getEnlaceDashboard()` | `tbl_clientes` | Campo enlace_dashboard del cliente |
| `recopilarActividadesPeriodo()` | Multiples tablas | Agrega actas, capacitaciones, PTA cerradas, pendientes cerrados |
| `calcularTodas()` | Todos los anteriores | Retorna array completo de metricas |

**Reglas estado_avance:**
- diferencia > 5 → AVANCE SIGNIFICATIVO
- diferencia 1..5 → AVANCE MODERADO
- diferencia = 0 → ESTABLE
- diferencia < 0 → REINICIO DE CICLO PHVA - BAJA PUNTAJE

---

## Fase 4: Controller — COMPLETADA

**Archivo:** `app/Controllers/InformeAvancesController.php`

### Metodos publicos (14)

| Metodo | Ruta | Tipo |
|--------|------|------|
| `list()` | GET /informe-avances | Web |
| `create($idCliente?)` | GET /informe-avances/create | Web |
| `store()` | POST /informe-avances/store | Web |
| `edit($id)` | GET /informe-avances/edit/{id} | Web |
| `update($id)` | POST /informe-avances/update/{id} | Web |
| `view($id)` | GET /informe-avances/view/{id} | Web |
| `finalizar($id)` | POST /informe-avances/finalizar/{id} | Web |
| `generatePdf($id)` | GET /informe-avances/pdf/{id} | Web |
| `delete($id)` | GET /informe-avances/delete/{id} | Web |
| `calcularMetricas($id)` | GET /informe-avances/api/metricas/{id} | AJAX |
| `generarResumen()` | POST /informe-avances/generar-resumen | AJAX+IA |
| `getClientes()` | GET /informe-avances/api/clientes | AJAX |
| `getClientesConVisita()` | GET /ext-api/.../clientes-con-visita | API |
| `enviar($id)` | POST /informe-avances/enviar/{id} | Web+API |
| `apiGenerarYEnviar($id)` | POST /ext-api/.../generar-y-enviar/{id} | API |

### Metodos privados (5)

- `getInformePostData()` — recoger datos del POST
- `uploadFoto()` — patron estandar para imagenes de soportes
- `generarPdfInterno()` — DOMPDF + guardar en uploads/informe-avances/pdfs/
- `uploadToReportes()` — id_report_type=6, id_detailreport=37
- `buildResumenPrompt()` — construir prompt IA para resumen ejecutivo
- `enviarInterno()` — envio email reutilizable (SendGrid + PDF adjunto)

### Regla de negocio: sin visita no hay informe

`apiGenerarYEnviar()` valida que el cliente tenga al menos 1 registro en `tbl_acta_visita` en el periodo antes de proceder. Si no hay visita, rechaza con error explicativo. La existencia del acta confirma la visita (independiente de su estado de firma).

---

## Fase 5: Vistas — COMPLETADAS

| Archivo | Descripcion |
|---------|-------------|
| `app/Views/informe_avances/list.php` | DataTables con filtros Select2 cliente + Select anio, badges de estado, acciones |
| `app/Views/informe_avances/form.php` | 7 secciones: datos generales, metricas auto-calc, resumen IA, act. cerradas, act. abiertas, observaciones, soportes (4 pares). JS auto-carga metricas al seleccionar cliente. |
| `app/Views/informe_avances/view.php` | Vista read-only con metric boxes, barras de progreso, soportes con modal zoom |
| `app/Views/informe_avances/pdf.php` | Template DOMPDF formato FT-SST-205: header corporativo, metricas en tabla, barras de progreso con tablas coloreadas, secciones resumen/cerradas/abiertas/observaciones/soportes |

---

## Fase 6: PDF — COMPLETADA

Template DOMPDF siguiendo el formato FT-SST-205:
- Header corporativo: logo cliente + "SG-SST" + "Codigo: FT-SST-205 / Version: 001"
- Indicadores con barras de progreso (tablas coloreadas, compatible DOMPDF)
- Estado de avance con badge de color
- Secciones: resumen, actividades cerradas, abiertas, observaciones, soportes
- CSS: @page margin en px (no cm), tablas para layout (no flexbox/grid)

---

## Fase 7: Rutas — COMPLETADAS

**Archivo:** `app/Config/Routes.php`

### Rutas web (sesion auth)
```
/informe-avances/*  →  filter: auth  →  14 rutas
```

### Rutas API (sesion OR API Key)
```
/ext-api/informe-avances/*  →  filter: authOrApiKey  →  6 rutas
```

Prefijo `ext-api/` (NO `api/`) para evitar conflicto con `$filters['auth']['api/*']` en Filters.php.

---

## Fase 8: Autenticacion API — COMPLETADA

### Archivos creados

| Archivo | Funcion |
|---------|---------|
| `app/Filters/ApiKeyFilter.php` | Filtro solo API Key (header X-API-Key, hash_equals) |
| `app/Filters/AuthOrApiKeyFilter.php` | Filtro dual: sesion primero, API Key como fallback |

### Registro en Filters.php

```php
'apikey'        => ApiKeyFilter::class,
'authOrApiKey'  => AuthOrApiKeyFilter::class,
```

### API Key

```
APP_API_KEY=sst_4f480e90e9c67de074c6aa2091e55f9892732f346eb86b20c1a2ea83aecac53c
```

Configurada en `.env` tanto LOCAL como PRODUCCION (linea 83 del .env del servidor).

### Verificacion (cURL local)

| Test | HTTP | Resultado |
|------|------|-----------|
| Sin auth + Accept: application/json | 401 | `{"error": "Autenticacion requerida"}` |
| API Key invalida | 403 | `{"error": "API Key invalida"}` |
| API Key valida → GET /clientes | 200 | JSON con 35 clientes |

---

## Fase 9: SendGrid Email — COMPLETADA (codigo)

- `enviar()` — endpoint web para enviar informe existente
- `apiGenerarYEnviar()` — flujo completo API (metricas → IA → PDF → email)
- `enviarInterno()` — helper privado reutilizable
- Email HTML con branding Cycloid Talent (#1c2437 + #bd9751)
- PDF adjunto como attachment base64
- From: `notificacion.cycloidtalent@cycloidtalent.com`

**Pendiente de prueba**: Requiere deploy a produccion para probar con datos reales.

---

## Fase 10: Integracion OpenClaw — COMPLETADA (documentacion)

### Documentos creados

| Archivo | Contenido |
|---------|-----------|
| `docs/OPEN CLAW/API_INFORME_AVANCES.md` | Documentacion completa de la API (6 endpoints, flujos, cURL, schema SQL) |
| `docs/OPEN CLAW/openclaw_informe_avances.json` | Config tools/skills para OpenClaw (6 tools, 3 workflows, business rules) |

### Arquitectura

```
OpenClaw (claw.cycloidtalent.com)
    → HTTPS + X-API-Key
    → phorizontal.cycloidtalent.com/ext-api/informe-avances/*
        → AuthOrApiKeyFilter
        → InformeAvancesController
        → MetricasInformeService + IADocumentacionService + DOMPDF + SendGrid
        → Email al cliente con PDF adjunto
```

### Flujo principal OpenClaw

```
1. GET /clientes-con-visita  →  Solo clientes con acta de visita en el periodo
2. Para cada cliente:
   POST /generar-y-enviar/{id}  →  Metricas → IA → PDF → Email (todo automatico)
```

---

## Fase 11: Dashboard consultor — COMPLETADA

Boton "Informe de Avances" agregado en:
- `app/Views/consultant/dashboard.php` (linea 626)
- `app/Views/consultant/admindashboard.php` (linea 689)

Estilo: gradiente verde (#11998e → #38ef7d), icono fa-chart-line.

---

## Inventario completo de archivos

### Creados (12)

| # | Archivo | Lineas |
|---|---------|--------|
| 1 | `app/SQL/migrate_informe_avances.php` | Migracion SQL |
| 2 | `app/Models/InformeAvancesModel.php` | Modelo CRUD |
| 3 | `app/Libraries/MetricasInformeService.php` | Auto-calculo metricas |
| 4 | `app/Controllers/InformeAvancesController.php` | Controller (~740 lineas) |
| 5 | `app/Views/informe_avances/list.php` | Listado DataTables |
| 6 | `app/Views/informe_avances/form.php` | Formulario auto-calc + IA |
| 7 | `app/Views/informe_avances/view.php` | Vista read-only |
| 8 | `app/Views/informe_avances/pdf.php` | Template DOMPDF FT-SST-205 |
| 9 | `app/Filters/ApiKeyFilter.php` | Filtro API Key |
| 10 | `app/Filters/AuthOrApiKeyFilter.php` | Filtro dual auth |
| 11 | `docs/OPEN CLAW/API_INFORME_AVANCES.md` | Doc API OpenClaw |
| 12 | `docs/OPEN CLAW/openclaw_informe_avances.json` | Config tools OpenClaw |

### Modificados (4)

| # | Archivo | Cambio |
|---|---------|--------|
| 1 | `app/Config/Routes.php` | +20 rutas (web + ext-api) |
| 2 | `app/Config/Filters.php` | +2 aliases (apikey, authOrApiKey) |
| 3 | `app/Views/consultant/dashboard.php` | +boton Informe de Avances |
| 4 | `app/Views/consultant/admindashboard.php` | +boton Informe de Avances |

### Configuracion (.env)

| Variable | Donde | Estado |
|----------|-------|--------|
| `APP_API_KEY` | Local .env | Configurada |
| `APP_API_KEY` | Produccion .env (linea 83) | Configurada por el usuario |
| `SENDGRID_API_KEY` | Ambos | Ya existia |
| `OPENAI_API_KEY` | Ambos | Ya existia |

---

## Estado actual y siguientes pasos

### COMPLETADO
- [x] Migracion SQL (local + produccion)
- [x] Modelo
- [x] Servicio de metricas
- [x] Controller (CRUD + PDF + IA + Email + API)
- [x] 4 vistas (list, form, view, pdf)
- [x] Filtros de autenticacion
- [x] Rutas (web + API)
- [x] Boton en dashboards
- [x] Documentacion OpenClaw
- [x] APP_API_KEY en produccion

### PENDIENTE
- [ ] **Deploy a produccion** (git merge cycloid → main, push)
- [ ] **Prueba CRUD en navegador** — crear, editar, ver, borrar informe
- [ ] **Prueba auto-calculo metricas** — seleccionar cliente con datos
- [ ] **Prueba generacion PDF** — DOMPDF con formato FT-SST-205
- [ ] **Prueba resumen IA** — boton "Generar con IA" en el form
- [ ] **Prueba envio email** — SendGrid con PDF adjunto
- [ ] **Prueba API completa** — cURL a produccion con API Key
- [ ] **Prueba OpenClaw** — flujo generar-y-enviar desde gateway
