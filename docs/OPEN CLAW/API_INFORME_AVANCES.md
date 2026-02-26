# API Informe de Avances — Documentacion OpenClaw

> Modulo de Informe de Avances del SG-SST expuesto como API REST para orquestacion desde OpenClaw Gateway.

---

## Arquitectura de integracion

```
+----------------------------------+          +-----------------------------------+
|     OpenClaw Gateway             |          |     enterprisesstph (CI4)         |
|     claw.cycloidtalent.com       |          |     phorizontal.cycloidtalent.com |
|                                  |          |                                   |
|  Workflow/Skill config (.json)   |  HTTPS   |  /ext-api/informe-avances/*       |
|  ──────────────────────────────► |────────► |  AuthOrApiKeyFilter               |
|  X-API-Key: sst_4f48...         |          |  InformeAvancesController         |
|                                  |          |                                   |
|  Recibe JSON response            | ◄──────  |  MetricasInformeService           |
|  (informe_id, pdf_url, email)    |          |  IADocumentacionService (OpenAI)  |
|                                  |          |  DOMPDF (PDF FT-SST-205)          |
|                                  |          |  SendGrid (email + adjunto)       |
+----------------------------------+          +-----------------------------------+
       ↑                                                     |
       | Unidireccional                                      | Email directo
       | OpenClaw → nuestra API                              ↓
       | (no requiere auth inversa)              Cliente (correo_cliente)
```

**Flujo**: OpenClaw orquesta → nuestra API calcula, genera y envia → cliente recibe email con PDF.

**Config**: Archivo `openclaw_informe_avances.json` en este mismo directorio define los tools/skills.

---

## Autenticacion

Todos los endpoints requieren el header `X-API-Key`.

```
X-API-Key: sst_4f480e90e9c67de074c6aa2091e55f9892732f346eb86b20c1a2ea83aecac53c
```

| Escenario | HTTP Status | Respuesta |
|-----------|-------------|-----------|
| Sin header `X-API-Key` y sin sesion | `401` | `{"success": false, "error": "Autenticacion requerida"}` |
| API Key invalida | `403` | `{"success": false, "error": "API Key invalida"}` |
| API Key valida | `200` | Respuesta del endpoint |
| Sesion web activa (cookie) | `200` | Tambien funciona (filtro dual) |

### Filtro: `AuthOrApiKeyFilter`

Ubicacion: `app/Filters/AuthOrApiKeyFilter.php`

Logica de prioridad:
1. Si hay sesion activa (`isLoggedIn`) → pasa
2. Si hay header `X-API-Key` valido (`hash_equals` contra `APP_API_KEY` en `.env`) → pasa
3. Si ninguno → rechaza con 401/403

La API Key se configura en `.env`:
```
APP_API_KEY=sst_4f480e90e9c67de074c6aa2091e55f9892732f346eb86b20c1a2ea83aecac53c
```

---

## Base URL

| Entorno | Base URL |
|---------|----------|
| Local | `http://localhost/enterprisesstph/public/ext-api/informe-avances/` |
| Produccion | `https://phorizontal.cycloidtalent.com/ext-api/informe-avances/` |

> Prefijo `ext-api/` (NO `api/`) para evitar conflicto con el filtro `auth` global que protege `api/*`.

---

## Regla de negocio fundamental

> **Sin visita no hay informe.** Solo se puede generar un Informe de Avances para clientes que tuvieron al menos una Acta de Visita (`tbl_acta_visita`) en el periodo del informe. Si OpenClaw intenta generar un informe para un cliente sin visita, la API rechaza con error explicativo.

---

## Endpoints

### 1. Listar todos los clientes activos

```
GET /ext-api/informe-avances/clientes
```

**Parametros**: Ninguno

**Respuesta** `200`:
```json
[
    {
        "id_cliente": "51",
        "nombre_cliente": "AGRUPACION DE VIVIENDA ACACIA - PH",
        "nit_cliente": "900687420"
    }
]
```

**Uso**: Lista todos los clientes con contrato activo (sin filtrar por visita). Util para consultas generales.

---

### 2. Listar clientes con visita en el periodo (USAR PARA INFORMES)

```
GET /ext-api/informe-avances/clientes-con-visita
```

**Parametros query** (opcionales):

| Parametro | Default | Descripcion |
|-----------|---------|-------------|
| `fecha_desde` | Hace 3 meses | Inicio del periodo (YYYY-MM-DD) |
| `fecha_hasta` | Hoy | Fin del periodo (YYYY-MM-DD) |

**Respuesta** `200`:
```json
{
    "success": true,
    "periodo": {
        "desde": "2025-12-01",
        "hasta": "2026-02-26"
    },
    "total": 12,
    "clientes": [
        {
            "id_cliente": "51",
            "nombre_cliente": "AGRUPACION DE VIVIENDA ACACIA - PH",
            "nit_cliente": "900687420",
            "correo_cliente": "admin@acacia.com",
            "total_visitas": "2",
            "ultima_visita": "2026-02-15"
        }
    ]
}
```

**Uso**: **Endpoint principal para OpenClaw.** Devuelve solo clientes que tuvieron acta de visita en el periodo (cualquier estado: borrador, pendiente_firma o completo). Si existe el registro del acta, la visita ocurrio. Iterar sobre esta lista para generar informes.

**Filtro SQL**: `tbl_acta_visita.fecha_visita BETWEEN fecha_desde AND fecha_hasta AND contrato activo`.

---

### 3. Calcular metricas de un cliente

```
GET /ext-api/informe-avances/metricas/{idCliente}
```

**Parametros URL**: `idCliente` (int) — ID del cliente

**Respuesta** `200`:
```json
{
    "success": true,
    "metricas": {
        "puntaje_actual": 72.5,
        "puntaje_anterior": 65.0,
        "diferencia_neta": 7.5,
        "estado_avance": "AVANCE SIGNIFICATIVO",
        "indicador_plan_trabajo": 45.2,
        "indicador_capacitacion": 80.0,
        "actividades_abiertas": "1. Actualizar matriz de riesgos\n2. Programar capacitacion...",
        "actividades_cerradas_periodo": "- Inspeccion locativa (Numeral 2.1, HACER) - 2026-02-15\n...",
        "enlace_dashboard": "https://lookerstudio.google.com/...",
        "fecha_desde": "2026-01-01"
    }
}
```

**Fuentes de datos auto-calculados**:

| Metrica | Tabla fuente | Calculo |
|---------|-------------|---------|
| `puntaje_actual` | `evaluacion_inicial_sst` | `SUM(valor) / SUM(puntaje_cuantitativo) * 100` |
| `puntaje_anterior` | `tbl_informe_avances` | Ultimo informe completo del mismo cliente |
| `diferencia_neta` | Calculado | `puntaje_actual - puntaje_anterior` |
| `estado_avance` | Calculado | Ver tabla de estados abajo |
| `indicador_plan_trabajo` | `tbl_pta_cliente` | `COUNT(estado=CERRADA) / COUNT(*) * 100` |
| `indicador_capacitacion` | `tbl_cronog_capacitacion` | `EJECUTADA / (PROGRAMADA + EJECUTADA) * 100` |
| `actividades_abiertas` | `tbl_pendientes` | WHERE `estado='ABIERTA'` AND `id_cliente=?` |
| `actividades_cerradas_periodo` | `tbl_pta_transiciones` + `tbl_pta_cliente` | JOIN, filtro por fecha y estado_nuevo CERRADA |
| `fecha_desde` | `tbl_informe_avances` | Dia siguiente al ultimo informe (o inicio contrato) |

**Reglas del estado de avance**:

| Diferencia | Estado |
|-----------|--------|
| > 5 | `AVANCE SIGNIFICATIVO` |
| 1 a 5 | `AVANCE MODERADO` |
| 0 | `ESTABLE` |
| < 0 | `REINICIO DE CICLO PHVA - BAJA PUNTAJE` |

---

### 4. Generar resumen con IA

```
POST /ext-api/informe-avances/generar-resumen
```

**Body** (form-data o JSON):
```json
{
    "id_cliente": 51,
    "fecha_desde": "2026-01-01",
    "fecha_hasta": "2026-02-26"
}
```

**Respuesta** `200`:
```json
{
    "success": true,
    "resumen": "Durante el periodo comprendido entre el 1 de enero y el 26 de febrero de 2026, el Sistema de Gestion de Seguridad y Salud en el Trabajo de AGRUPACION DE VIVIENDA ACACIA - PH presento un avance significativo..."
}
```

**Motor IA**: OpenAI `gpt-4o-mini` via `IADocumentacionService.php` (servicio existente).

**Prompt**: Se construye automaticamente con las metricas y actividades del periodo. Genera un resumen ejecutivo profesional de maximo 4 parrafos en prosa continua.

---

### 5. Flujo completo: Generar y Enviar (ENDPOINT PRINCIPAL)

```
POST /ext-api/informe-avances/generar-y-enviar/{idCliente}
```

**Parametros URL**: `idCliente` (int) — ID del cliente (debe tener acta de visita en el periodo)

**Body**: Ninguno (todo se auto-calcula)

**Respuesta** `200` (exito):
```json
{
    "success": true,
    "informe_id": 42,
    "pdf_url": "https://phorizontal.cycloidtalent.com/uploads/informe-avances/pdfs/informe_42_1709xxx.pdf",
    "email": {
        "success": true,
        "destinatario": "admin@conjuntoacacia.com"
    }
}
```

**Respuesta error: cliente sin visita en el periodo**:
```json
{
    "success": false,
    "error": "No se puede generar informe: el cliente no tiene actas de visita en el periodo 2026-01-01 a 2026-02-26",
    "cliente": "AGRUPACION DE VIVIENDA ACACIA - PH",
    "periodo": {
        "desde": "2026-01-01",
        "hasta": "2026-02-26"
    }
}
```

**Respuesta error: cliente sin correo** (informe se crea pero email falla):
```json
{
    "success": true,
    "informe_id": 42,
    "pdf_url": "https://...",
    "email": {
        "success": false,
        "error": "Cliente sin correo"
    }
}
```

**Flujo interno completo** (8 pasos automaticos):

```
1. Buscar cliente en BD
2. Calcular periodo (desde ultimo informe o inicio contrato)
3. VALIDAR que existe al menos 1 acta de visita en el periodo → si no hay, rechazar
4. Calcular metricas (MetricasInformeService::calcularTodas)
5. Generar resumen con IA (OpenAI gpt-4o-mini)
6. Crear registro en tbl_informe_avances (estado: borrador → completo)
7. Generar PDF con DOMPDF (formato FT-SST-205)
8. Subir a tbl_reporte + enviar email con PDF adjunto via SendGrid
```

**Email enviado**: HTML con branding Cycloid Talent + PDF adjunto.
- From: `notificacion.cycloidtalent@cycloidtalent.com`
- Subject: `Informe de Avances SG-SST - {CLIENTE} - {PERIODO}`
- Adjunto: `Informe_Avances_{CLIENTE}.pdf`

---

### 6. Enviar informe existente por email

```
POST /ext-api/informe-avances/enviar/{idInforme}
```

**Parametros URL**: `idInforme` (int) — ID del informe ya creado

**Requisitos**: El informe debe tener `estado = 'completo'` y `ruta_pdf` no vacio.

**Respuesta** `200`:
```json
{
    "success": true,
    "message": "Informe enviado a admin@conjunto.com",
    "destinatario": "admin@conjunto.com"
}
```

---

## Flujo recomendado para OpenClaw

### Caso 1: Envio masivo a clientes con visita (FLUJO PRINCIPAL)

```
1. GET /ext-api/informe-avances/clientes-con-visita
   → Obtener SOLO clientes que tuvieron acta de visita en los ultimos 3 meses
   → Respuesta incluye: total_visitas, ultima_visita, correo_cliente

2. Para cada cliente en la lista:
   POST /ext-api/informe-avances/generar-y-enviar/{idCliente}
   → Genera metricas + resumen IA + PDF + envia email
   → Esperar respuesta antes del siguiente (rate limits SendGrid/OpenAI)
   → Si success=false, loguear error y continuar con el siguiente
```

### Caso 2: Solo consultar metricas (sin generar informe)

```
1. GET /ext-api/informe-avances/clientes-con-visita
2. GET /ext-api/informe-avances/metricas/{idCliente}
```

### Caso 3: Generar informe para un cliente especifico

```
POST /ext-api/informe-avances/generar-y-enviar/{idCliente}
(Si el cliente no tiene visita en el periodo, la API rechaza con error explicativo)
```

---

## Ejemplo cURL completo

```bash
API_KEY="sst_4f480e90e9c67de074c6aa2091e55f9892732f346eb86b20c1a2ea83aecac53c"
BASE="https://phorizontal.cycloidtalent.com/ext-api/informe-avances"

# 1. Listar clientes que tuvieron visita (ultimos 3 meses por defecto)
curl -s -H "X-API-Key: $API_KEY" "$BASE/clientes-con-visita"

# 1b. Listar con periodo personalizado
curl -s -H "X-API-Key: $API_KEY" "$BASE/clientes-con-visita?fecha_desde=2026-01-01&fecha_hasta=2026-02-28"

# 2. Calcular metricas del cliente 51
curl -s -H "X-API-Key: $API_KEY" "$BASE/metricas/51"

# 3. Flujo completo: generar + enviar para cliente 51
curl -s -X POST -H "X-API-Key: $API_KEY" "$BASE/generar-y-enviar/51"

# 4. Listar todos los clientes activos (sin filtro de visita)
curl -s -H "X-API-Key: $API_KEY" "$BASE/clientes"
```

---

## Archivos de la implementacion

### Nuevos (10 archivos)

| Archivo | Funcion |
|---------|---------|
| `app/SQL/migrate_informe_avances.php` | Migracion SQL (tabla + detail_report) |
| `app/Models/InformeAvancesModel.php` | Modelo CRUD con JOINs |
| `app/Libraries/MetricasInformeService.php` | Auto-calculo de todas las metricas |
| `app/Controllers/InformeAvancesController.php` | Controller CRUD + PDF + IA + Email + API |
| `app/Views/informe_avances/list.php` | Listado DataTables (panel consultor) |
| `app/Views/informe_avances/form.php` | Formulario con auto-calc + boton IA |
| `app/Views/informe_avances/view.php` | Vista read-only |
| `app/Views/informe_avances/pdf.php` | Template DOMPDF formato FT-SST-205 |
| `app/Filters/ApiKeyFilter.php` | Filtro API Key puro |
| `app/Filters/AuthOrApiKeyFilter.php` | Filtro dual (sesion OR API Key) |

### Modificados (4 archivos)

| Archivo | Cambio |
|---------|--------|
| `app/Config/Routes.php` | Rutas web `/informe-avances/*` + API `/ext-api/informe-avances/*` |
| `app/Config/Filters.php` | Registro de filtros `apikey` y `authOrApiKey` |
| `app/Views/consultant/admindashboard.php` | Boton acceso rapido "Informe de Avances" |
| `app/Views/consultant/dashboard.php` | Boton acceso rapido "Informe de Avances" |

### Dependencias existentes reutilizadas

| Componente | Ubicacion |
|------------|-----------|
| IADocumentacionService | `app/Services/IADocumentacionService.php` — OpenAI gpt-4o-mini |
| PtaTransicionesModel | `app/Models/PtaTransicionesModel.php` — Query actividades cerradas |
| SendGrid SDK | `vendor/sendgrid/` — Envio email con adjuntos |
| DOMPDF | `vendor/dompdf/` — Generacion PDF |

---

## Variables de entorno requeridas

```env
# Ya existentes
SENDGRID_API_KEY=SG.xxxxx
OPENAI_API_KEY=sk-proj-xxxxx

# Nuevo (agregado con este modulo)
APP_API_KEY=sst_4f480e90e9c67de074c6aa2091e55f9892732f346eb86b20c1a2ea83aecac53c
```

---

## Tabla SQL: `tbl_informe_avances`

```sql
CREATE TABLE tbl_informe_avances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_desde DATE NOT NULL,
    fecha_hasta DATE NOT NULL,
    anio SMALLINT NOT NULL,
    puntaje_anterior DECIMAL(5,2) NULL,
    puntaje_actual DECIMAL(5,2) NULL,
    diferencia_neta DECIMAL(5,2) NULL,
    estado_avance VARCHAR(60) NOT NULL DEFAULT 'ESTABLE',
    indicador_plan_trabajo DECIMAL(5,2) NULL,
    indicador_capacitacion DECIMAL(5,2) NULL,
    img_cumplimiento_estandares VARCHAR(500) NULL,
    img_indicador_plan_trabajo VARCHAR(500) NULL,
    img_indicador_capacitacion VARCHAR(500) NULL,
    resumen_avance LONGTEXT NULL,
    observaciones TEXT NULL,
    actividades_abiertas TEXT NULL,
    actividades_cerradas_periodo TEXT NULL,
    enlace_dashboard VARCHAR(500) NULL,
    acta_visita_url VARCHAR(500) NULL,
    soporte_1_texto TEXT NULL,
    soporte_1_imagen VARCHAR(500) NULL,
    soporte_2_texto TEXT NULL,
    soporte_2_imagen VARCHAR(500) NULL,
    soporte_3_texto TEXT NULL,
    soporte_3_imagen VARCHAR(500) NULL,
    soporte_4_texto TEXT NULL,
    soporte_4_imagen VARCHAR(500) NULL,
    ruta_pdf VARCHAR(500) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_infavance_cliente (id_cliente),
    INDEX idx_infavance_consultor (id_consultor),
    INDEX idx_infavance_estado (estado),
    INDEX idx_infavance_fecha (fecha_hasta)
);
```

Migracion ejecutada en LOCAL y PRODUCCION: `php app/SQL/migrate_informe_avances.php`

---

## Notas tecnicas

- **Prefijo `ext-api/`**: Se usa en lugar de `api/` porque `Filters.php` tiene un patron global `'api/*' => auth` que forzaria autenticacion por sesion en todas las rutas `api/*`, bloqueando el acceso por API Key.
- **PDF**: Generado con DOMPDF 3.0.0. Usa tablas para layout (no flexbox/grid). Margenes en `px` (no `cm`). Logos convertidos a base64.
- **Rate limits**: Al hacer envio masivo, considerar pausas entre clientes para respetar limits de SendGrid y OpenAI.
- **Email from**: `notificacion.cycloidtalent@cycloidtalent.com` — dominio verificado en SendGrid.
- **`detail_report`**: El informe se registra en `tbl_reporte` con `id_report_type=6`, `id_detailreport=37` ("INFORME DE AVANCES").
