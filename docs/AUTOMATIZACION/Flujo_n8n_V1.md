# Flujo Automatizado Informe de Avances — Plan V1

> Arquitectura n8n + Backend PHP + OpenAI para la generacion automatica mensual de informes de avances SG-SST.

---

## Resumen Ejecutivo

Automatizar la generacion mensual de informes de avances para los 46+ clientes activos, eliminando el trabajo repetitivo del consultor pero manteniendo un punto de control humano antes de generar.

**Costo estimado:** ~$0.50 USD/mes en tokens OpenAI (gpt-4o-mini, 46 informes).

---

## Arquitectura

```
n8n (orquestador, self-hosted, gratis)
  |
  +-- Cron: Dia 8 de cada mes
  +-- GET /ext-api/informe-avances/clientes-con-visita
  +-- Agrupa clientes por consultor
  +-- Email a cada consultor: "Tienes X informes pendientes. Todo listo?"
  |     +-- [Confirmar] --> Webhook n8n --> genera informes
  |     +-- [Necesito tiempo] --> Webhook n8n --> espera
  +-- Dia 10: Reintento para no confirmados
  +-- Dia 12: Alerta al admin para no confirmados
  |
  +-- Para cada cliente confirmado:
        POST /ext-api/informe-avances/generar-y-enviar/{id}
          |
          Backend PHP (ya construido):
            +-- Snapshot individual (liquidar metricas)
            +-- Calcular metricas desde tablas reales
            +-- Recopilar actividades del periodo
            +-- Llamar OpenAI API (gpt-4o-mini) --> resumen ejecutivo
            +-- Generar PDF con DOMPDF (formato FT-SST-205)
            +-- Enviar email al cliente via SendGrid
            +-- Registrar en tbl_informe_avances + reportes
```

---

## Componentes Existentes (ya construidos)

| Componente | Archivo | Estado |
|-----------|---------|--------|
| Snapshot individual por cliente | InformeAvancesController::liquidarSnapshot | OK |
| Calculo de metricas por anio PHVA | MetricasInformeService::calcularTodas | OK |
| Generacion resumen con IA | InformeAvancesController::generarResumen | OK |
| Prompt profesional SST | InformeAvancesController::buildResumenPrompt | OK |
| Generacion PDF FT-SST-205 | InformeAvancesController::generarPdfInterno | OK |
| Envio email con PDF adjunto | InformeAvancesController::enviarInterno | OK |
| Flujo completo API | InformeAvancesController::apiGenerarYEnviar | OK |
| Clientes con visita en periodo | InformeAvancesController::getClientesConVisita | OK |
| Autenticacion API Key | AuthOrApiKeyFilter | OK |

---

## Componentes Por Construir

### 1. Integrar snapshot en apiGenerarYEnviar (backend)

**Esfuerzo:** 30 min

El endpoint `apiGenerarYEnviar` debe ejecutar el snapshot del cliente antes de calcular metricas. Actualmente calcula metricas sin tomar foto previa.

```php
// Dentro de apiGenerarYEnviar(), antes de calcularTodas():
$this->ejecutarSnapshotCliente($idCliente); // reutilizar logica de liquidarSnapshot
```

### 2. Endpoint de confirmacion con token (backend)

**Esfuerzo:** 2-3 horas

Nueva tabla y endpoints para el flujo de confirmacion:

```sql
CREATE TABLE tbl_confirmacion_informe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_consultor INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    anio INT NOT NULL,
    mes INT NOT NULL,
    estado ENUM('pendiente', 'confirmado', 'rechazado') DEFAULT 'pendiente',
    clientes_json TEXT,          -- IDs de clientes pendientes
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    confirmed_at DATETIME NULL
);
```

Endpoints:

```
POST /ext-api/informe-avances/solicitar-confirmacion
  - Crea registros por consultor con token unico
  - Retorna lista de consultores + tokens para que n8n envie emails

GET /ext-api/informe-avances/confirmar/{token}
  - El consultor hace clic en el email
  - Marca como confirmado
  - Retorna pagina HTML de confirmacion
  - Opcion: webhook callback a n8n

GET /ext-api/informe-avances/confirmaciones-pendientes
  - n8n consulta cuales consultores no han confirmado
  - Para reintentos y escalamiento
```

### 3. Workflow n8n

**Esfuerzo:** 2-3 horas

```
[Cron Trigger: dia 8, 10:00 AM]
    |
    v
[HTTP Request: GET /ext-api/.../clientes-con-visita]
    |
    v
[HTTP Request: POST /ext-api/.../solicitar-confirmacion]
    |
    v
[Split por consultor]
    |
    v
[Email SendGrid: "Tienes X informes pendientes"]
    |   Incluye link: /ext-api/.../confirmar/{token}
    |
    v
[Wait: 48 horas]
    |
    v
[HTTP Request: GET /ext-api/.../confirmaciones-pendientes]
    |
    v
[IF confirmado?]
    |         |
    SI        NO
    |         |
    v         v
[Loop: POST /generar-y-enviar/{id}]    [Dia 10: Reenviar email]
    |                                        |
    v                                        v
[FIN - Informes enviados]              [Wait: 48 horas]
                                             |
                                             v
                                       [IF confirmado?]
                                        |         |
                                        SI        NO
                                        |         |
                                        v         v
                                   [Generar]  [Email admin: "Edison no confirmo"]
```

### 4. Template email de confirmacion

**Esfuerzo:** 1 hora

Email HTML con branding Cycloid Talent que incluya:
- Lista de clientes pendientes de informe
- Boton "Todo actualizado, genera los informes" (link con token)
- Boton "Necesito mas tiempo" (link alternativo)
- Recordatorio de que debe haber actualizado los 5 modulos

---

## Cronograma Sugerido

| Fase | Tarea | Tiempo |
|------|-------|--------|
| 1 | Integrar snapshot en apiGenerarYEnviar | 30 min |
| 2 | Tabla + endpoints de confirmacion | 2-3 horas |
| 3 | Template email de confirmacion | 1 hora |
| 4 | Workflow n8n | 2-3 horas |
| 5 | Pruebas end-to-end | 2 horas |
| **Total** | | **~8-10 horas** |

---

## Donde Encaja OpenClaw

OpenClaw NO participa en el flujo automatizado mensual (n8n + backend es suficiente). OpenClaw agrega valor en escenarios de **inteligencia**, no de automatizacion:

### Casos de uso donde OpenClaw SI sirve

| Caso de Uso | Que hace OpenClaw | Que NO puede hacer n8n |
|------------|-------------------|----------------------|
| Chat consultor | "Como va Jacaranda este mes?" → responde con datos en tiempo real | n8n no interpreta lenguaje natural |
| Deteccion de anomalias | "Este cliente bajo 30% — posible cambio de administracion" | n8n no analiza contexto |
| Recomendaciones | "Priorizar capacitaciones: 0% de ejecucion en Q1" | n8n no genera texto inteligente |
| Analisis de tendencias | "3 meses sin mover PTA — riesgo de incumplimiento" | n8n compara numeros pero no interpreta |
| Asistente de presentacion | "Resume los 5 clientes mas criticos para la junta" | n8n no sintetiza informacion |
| Alertas inteligentes | "El consultor X tiene 8 clientes sin visita este mes" | n8n puede alertar pero no explicar por que importa |

### Arquitectura futura con OpenClaw

```
Consultor (chat o WhatsApp)
    |
    v
OpenClaw (agente conversacional)
    |
    +-- "Como va mi cliente Jacaranda?"
    |     --> GET /ext-api/metricas/20?anio=2026
    |     --> Interpreta y responde en lenguaje natural
    |
    +-- "Que clientes tienen capacitaciones atrasadas?"
    |     --> Consulta multiples endpoints
    |     --> Cruza datos y prioriza
    |
    +-- "Genera un resumen para la junta directiva"
          --> Consulta todos los clientes del consultor
          --> Sintetiza en un reporte ejecutivo
```

**Regla simple:**
- **Rutina predecible** (generar informe el dia 10) → **n8n**
- **Inteligencia bajo demanda** (preguntar, analizar, recomendar) → **OpenClaw**

---

## Stack Tecnologico Final

| Capa | Tecnologia | Costo |
|------|-----------|-------|
| Orquestacion | n8n self-hosted | Gratis |
| Backend | CodeIgniter 4 / PHP 8.2 | Ya pagado (DigitalOcean) |
| Base de datos | MySQL (DigitalOcean Managed) | Ya pagado |
| IA - Resumen informes | OpenAI API gpt-4o-mini | ~$0.50/mes (46 informes) |
| IA - Asistente consultor | OpenClaw + OpenAI | Segun uso bajo demanda |
| PDF | DOMPDF 3.0.0 | Gratis |
| Email | SendGrid | Plan actual |
| Notificaciones | SendGrid (email) | Plan actual |

---

## Riesgos y Mitigaciones

| Riesgo | Mitigacion |
|--------|-----------|
| Consultor no confirma nunca | Escalamiento automatico al admin dia 12 |
| Datos desactualizados generan informe malo | El snapshot toma foto en tiempo real; si no actualizaron, el informe refleja la realidad |
| OpenAI API caida | Fallback: generar informe sin resumen IA, campo vacio para llenar manual |
| Email de confirmacion cae en spam | Usar dominio verificado en SendGrid + SPF/DKIM |
| Token de confirmacion expira o se reutiliza | Token unico por consultor+mes, expira a fin de mes |
