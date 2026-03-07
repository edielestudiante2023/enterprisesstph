# Agente Chat Embebido — Plan V1

> Widget de chat con IA embebido en phorizontal.cycloidtalent.com, con dos perfiles: Consultor y Cliente. Conectado a OpenClaw como backend de IA.

---

## Problema que Resuelve

| Situacion actual | Con el agente |
|-----------------|---------------|
| Cliente llama a Edison: "Donde quedo la inspeccion de botiquines?" | Cliente abre el chat: "Donde esta mi inspeccion de botiquines?" → Agente responde con link al PDF |
| Consultor abre 5 modulos para saber como va un cliente | Consultor pregunta: "Como va Jacaranda?" → Agente resume todo en 1 respuesta |
| Cliente no sabe cuando es su proxima capacitacion | "Cuando es mi proxima capacitacion?" → "El 15 de marzo: Trabajo en alturas" |
| Consultor quiere saber que clientes estan atrasados | "Que clientes tienen PTA sin mover?" → Lista priorizada |

**Resultado:** Menos llamadas, autoservicio para clientes, consultores mas eficientes.

---

## Arquitectura

```
phorizontal.cycloidtalent.com
  |
  +-- Widget flotante (boton esquina inferior derecha)
  |     +-- Detecta sesion activa (role + id)
  |     +-- Abre panel de chat
  |
  +-- Frontend (iframe o JS SDK)
  |     +-- Envia mensaje + contexto de sesion
  |     +-- Recibe respuesta + renderiza
  |
  +-- OpenClaw API (backend IA)
        +-- Recibe: mensaje + role + id_usuario
        +-- Determina perfil (consultor o cliente)
        +-- Llama endpoints API de la plataforma segun necesidad
        +-- Genera respuesta en lenguaje natural
        +-- Retorna al widget
```

---

## Dos Perfiles de Agente

### Perfil 1: Agente Consultor

**Se activa cuando:** El usuario logueado tiene `role = consultor` o `role = admin`

**Contexto que recibe:** `id_consultor`, nombre, lista de clientes asignados

**Capacidades:**

| Pregunta ejemplo | Endpoint que consulta | Respuesta |
|-----------------|----------------------|-----------|
| "Como va Jacaranda?" | GET /ext-api/metricas/{id}?anio=2026 | "Jacaranda tiene 45.5% en estandares, subio 5.7pp. PTA al 3.1%, capacitaciones en 0%. Recomiendo priorizar capacitaciones." |
| "Que clientes tienen capacitaciones pendientes?" | GET /ext-api/clientes + metricas por lote | "5 clientes con 0% de ejecucion: Jacaranda, Altavista, Castaño, Pinar, Lucerna" |
| "Cuantas actividades PTA abiertas tiene El Huerto?" | GET /ext-api/metricas/33?anio=2026 | "El Huerto tiene 62 actividades abiertas de 64 totales (96.9% abiertas). Solo 2 cerradas." |
| "Que compromisos lleva mas de 30 dias abiertos?" | Consulta tbl_pendientes filtrado | Lista de compromisos con dias abiertos > 30 |
| "Generame el informe de Jacaranda de febrero" | POST /ext-api/generar-y-enviar/{id} | "Informe generado y enviado al correo del cliente." |
| "Que clientes visite este mes?" | GET /ext-api/clientes-con-visita | Lista de clientes con fecha de ultima visita |
| "Resumeme los 5 clientes mas criticos" | Consulta multiple + analisis IA | Parrafo ejecutivo con los clientes que peor van |

**Restricciones:**
- Puede ver todos los clientes activos (no solo los asignados)
- Puede ejecutar acciones (generar informes, liquidar)
- No puede modificar datos directamente (solo a traves de endpoints existentes)

### Perfil 2: Agente Cliente

**Se activa cuando:** El usuario logueado tiene `role = cliente`

**Contexto que recibe:** `id_cliente`, nombre_cliente, nit_cliente

**Capacidades:**

| Pregunta ejemplo | Endpoint que consulta | Respuesta |
|-----------------|----------------------|-----------|
| "Donde esta mi inspeccion de botiquines?" | GET reportes filtrado por tipo | "Su ultima inspeccion de botiquines fue el 15/02/2026. Aqui puede descargarla: [link PDF]" |
| "Donde esta mi plan de emergencia?" | GET reportes filtrado por tipo | Link al PDF del plan de emergencia |
| "Cuando es mi proxima capacitacion?" | GET tbl_cronog_capacitacion | "Su proxima capacitacion es el 15/03/2026: Trabajo en Alturas, programada de 2:00 a 4:00 PM" |
| "Que compromisos tengo pendientes?" | GET tbl_pendientes | "Tiene 3 compromisos abiertos: 1) Señalizacion escaleras (15 dias), 2) Recarga extintor piso 2 (8 dias), 3) Actualizar lista vigias (5 dias)" |
| "Como va mi SG-SST?" | GET metricas del cliente | "Su conjunto tiene 45.5% de cumplimiento en estandares minimos. Plan de trabajo al 3.1%. Capacitaciones pendientes de iniciar." |
| "Cuando fue la ultima visita del consultor?" | GET tbl_acta_visita | "La ultima visita fue el 25/02/2026. El acta esta disponible aqui: [link]" |
| "Que mantenimientos tengo vencidos?" | GET vencimientos | "Tiene 2 mantenimientos vencidos: Extintor piso 3 (vencio 01/02/2026) y Botiquin recepcion (vencio 15/01/2026)" |
| "Descarga mi ultimo informe de avances" | GET tbl_informe_avances | Link al PDF del ultimo informe completo |

**Restricciones:**
- SOLO ve informacion de SU conjunto/empresa (filtro por id_cliente siempre)
- NO puede modificar datos
- NO puede ver informacion de otros clientes
- NO puede ejecutar acciones (solo consultar)
- Si pregunta algo fuera de alcance: "Esa solicitud debe gestionarla con su consultor asignado: [nombre] ([correo])"

---

## Endpoints API Necesarios

### Ya existentes (listos para usar)

| Endpoint | Que devuelve |
|---------|-------------|
| GET /ext-api/informe-avances/metricas/{id} | Metricas completas del cliente |
| GET /ext-api/informe-avances/clientes-con-visita | Clientes con visita en periodo |
| GET /ext-api/informe-avances/clientes | Todos los clientes activos |
| POST /ext-api/informe-avances/generar-y-enviar/{id} | Genera y envia informe completo |

### Por construir

| Endpoint | Que devuelve | Esfuerzo |
|---------|-------------|----------|
| GET /ext-api/cliente/{id}/reportes | PDFs del cliente por tipo (inspecciones, actas, planes) | 2 horas |
| GET /ext-api/cliente/{id}/capacitaciones | Cronograma con proximas y ejecutadas | 1 hora |
| GET /ext-api/cliente/{id}/pendientes | Compromisos abiertos con dias | 1 hora |
| GET /ext-api/cliente/{id}/vencimientos | Mantenimientos vencidos y proximos | 1 hora (ya existe logica) |
| GET /ext-api/cliente/{id}/actas | Actas de visita con fechas y links | 1 hora |
| GET /ext-api/cliente/{id}/resumen | Resumen consolidado (metricas + pendientes + vencimientos) | 2 horas |
| GET /ext-api/consultor/{id}/dashboard | Vista general de todos sus clientes | 2-3 horas |

**Total endpoints nuevos: ~10-12 horas de desarrollo**

---

## Implementacion del Widget

### Opcion A: Widget propio (recomendado para control total)

```html
<!-- En el layout principal de la plataforma -->
<div id="cycloid-chat-widget"></div>
<script>
  CycloidChat.init({
    apiUrl: 'https://claw.cycloidtalent.com/api/chat',
    user: {
      id: <?= session()->get('user_id') ?>,
      role: '<?= session()->get('role') ?>',
      name: '<?= session()->get('user_name') ?>',
      clienteId: <?= session()->get('id_cliente') ?? 'null' ?>,
      consultorId: <?= session()->get('id_consultor') ?? 'null' ?>
    },
    theme: {
      primary: '#1c2437',
      accent: '#bd9751',
      position: 'bottom-right'
    }
  });
</script>
```

Componentes:
- Boton flotante con icono de chat (esquina inferior derecha)
- Panel deslizable con historial de conversacion
- Indicador de "escribiendo..."
- Links clicables a PDFs y reportes en las respuestas
- Boton de "Hablar con soporte humano" como fallback

### Opcion B: Integracion con plataforma existente

Si OpenClaw ya tiene SDK o widget embebible, usar ese directamente pasandole el contexto de sesion.

---

## Seguridad

| Aspecto | Implementacion |
|---------|---------------|
| Autenticacion | El widget envia el token de sesion del usuario logueado |
| Autorizacion por rol | OpenClaw recibe el role y filtra endpoints segun perfil |
| Filtro por cliente | Perfil cliente SIEMPRE incluye WHERE id_cliente = {su_id} |
| Rate limiting | Maximo 30 mensajes por minuto por usuario |
| Datos sensibles | No exponer NIT, correos de otros clientes, datos financieros |
| Fallback | Si OpenClaw no puede responder: "No tengo esa informacion. Contacte a su consultor." |
| Logs | Guardar historial de conversaciones para auditoria y mejora |

---

## Configuracion de OpenClaw

### System prompt — Agente Consultor

```
Eres el asistente de Cycloid Talent para consultores de SG-SST.
Tienes acceso a los datos de todos los clientes activos de la plataforma.
Respondes en español colombiano, tono profesional pero cercano.
Cuando te pregunten por un cliente, consulta sus metricas en tiempo real.
Si no tienes datos suficientes, dilo honestamente.
Nunca inventes datos. Si un indicador es 0%, dilo.
Puedes ejecutar acciones como generar informes si el consultor lo pide.
Formato: respuestas concisas, usa negritas para datos clave, links para documentos.
```

### System prompt — Agente Cliente

```
Eres el asistente de Cycloid Talent para clientes de propiedad horizontal.
Solo tienes acceso a la informacion del conjunto {nombre_cliente} (ID: {id_cliente}).
Respondes en español colombiano, tono amable y claro.
Ayudas a encontrar documentos (inspecciones, actas, planes, informes).
Informas sobre capacitaciones, compromisos pendientes y vencimientos.
Si te preguntan algo que no puedes resolver, indica que contacten a su consultor: {nombre_consultor} ({correo_consultor}).
Nunca compartas informacion de otros clientes.
Nunca inventes datos.
```

### Tools/Skills de OpenClaw

```json
{
  "tools": [
    {
      "name": "consultar_metricas_cliente",
      "description": "Obtiene metricas SG-SST de un cliente",
      "endpoint": "GET /ext-api/informe-avances/metricas/{id_cliente}?anio={anio}"
    },
    {
      "name": "consultar_reportes_cliente",
      "description": "Lista PDFs y reportes de un cliente por tipo",
      "endpoint": "GET /ext-api/cliente/{id_cliente}/reportes?tipo={tipo}"
    },
    {
      "name": "consultar_pendientes",
      "description": "Lista compromisos pendientes de un cliente",
      "endpoint": "GET /ext-api/cliente/{id_cliente}/pendientes"
    },
    {
      "name": "consultar_capacitaciones",
      "description": "Cronograma de capacitaciones de un cliente",
      "endpoint": "GET /ext-api/cliente/{id_cliente}/capacitaciones"
    },
    {
      "name": "consultar_vencimientos",
      "description": "Mantenimientos vencidos o proximos a vencer",
      "endpoint": "GET /ext-api/cliente/{id_cliente}/vencimientos"
    },
    {
      "name": "consultar_actas_visita",
      "description": "Actas de visita del cliente con links",
      "endpoint": "GET /ext-api/cliente/{id_cliente}/actas"
    },
    {
      "name": "generar_informe",
      "description": "Genera y envia informe de avances (solo consultores)",
      "endpoint": "POST /ext-api/informe-avances/generar-y-enviar/{id_cliente}",
      "roles": ["consultor", "admin"]
    },
    {
      "name": "dashboard_consultor",
      "description": "Resumen de todos los clientes del consultor",
      "endpoint": "GET /ext-api/consultor/{id_consultor}/dashboard",
      "roles": ["consultor", "admin"]
    }
  ]
}
```

---

## Cronograma Sugerido

| Fase | Tarea | Tiempo |
|------|-------|--------|
| 1 | Construir endpoints API para cliente (reportes, capacitaciones, pendientes, vencimientos, actas) | 6-8 horas |
| 2 | Construir endpoint resumen consolidado + dashboard consultor | 4-5 horas |
| 3 | Configurar agentes en OpenClaw (prompts, tools, perfiles) | 3-4 horas |
| 4 | Desarrollar widget de chat (frontend JS + CSS) | 6-8 horas |
| 5 | Integracion widget con OpenClaw API | 3-4 horas |
| 6 | Pruebas con consultor real | 2-3 horas |
| 7 | Pruebas con cliente real | 2-3 horas |
| **Total** | | **~26-35 horas** |

---

## Metricas de Exito

| Metrica | Antes | Objetivo |
|---------|-------|----------|
| Llamadas de clientes preguntando por documentos | ~20/semana | < 5/semana |
| Tiempo del consultor buscando info de un cliente | ~10 min | < 30 seg (1 pregunta al chat) |
| Clientes que descargan sus propios reportes | ~10% | > 60% |
| Satisfaccion del cliente (acceso a info) | Depende del consultor | Autoservicio 24/7 |

---

## Valor Diferencial Comercial

Este widget convierte la plataforma de una herramienta interna del consultor en un **canal de servicio para el cliente**. El administrador de la propiedad horizontal ya no depende del consultor para saber como va su SG-SST.

Esto se puede presentar comercialmente como:
- "Su conjunto tiene un asistente de SST disponible 24/7"
- "Consulte el estado de su sistema de gestion cuando quiera"
- "Acceda a todos sus documentos con una sola pregunta"

Es un diferenciador frente a otras consultoras SST que solo entregan PDFs por email.

---

## Capacidad de Escritura: Agente que Gestiona el PTA

El agente consultor no solo consulta — puede ejecutar acciones sobre la base de datos con un flujo de doble confirmacion.

### Niveles de autonomia (ambos activos)

| Nivel | Comportamiento | Cuando se usa |
|-------|---------------|---------------|
| **Sugiere** | El agente detecta actividades con evidencia y propone cerrarlas. El consultor revisa y aprueba. | Cuando el agente identifica coincidencias automaticamente |
| **Ejecuta con confirmacion** | El consultor pide cerrar actividades especificas. El agente muestra resumen y pide confirmacion antes de escribir. | Cuando el consultor da la instruccion directa |

### Flujo completo: Cierre de actividades PTA via chat

```
Consultor: "Tráeme las actividades abiertas de febrero de Jacaranda"

Agente:    Encontré 8 actividades abiertas:
           1. [2.4.1] Diseñar Plan Anual de Trabajo
           2. [2.3.1] Diligenciar autoevaluación inicial
           3. [1.1.2] Asignación de responsable SST
           ...

Consultor: "Busca en los documentos cargados este año cuáles
            se relacionan con esas actividades"

Agente:    Encontré coincidencias:
           - Actividad 2.4.1  <->  "Plan_Trabajo_2026.pdf" (cargado 20/02/2026)
           - Actividad 2.3.1  <->  "Autoevaluacion_inicial.pdf" (cargado 22/02/2026)
           - Actividad 1.1.2  ->  Sin documento relacionado

Consultor: "Cierra la 2.4.1 y 2.3.1 con fecha de la última visita"

Agente:    Voy a ejecutar lo siguiente:

           +-------------------------------------------+
           |  CERRAR actividad 2.4.1                   |
           |  Fecha cierre: 25/02/2026 (ultima visita) |
           |  Evidencia: Plan_Trabajo_2026.pdf         |
           |                                           |
           |  CERRAR actividad 2.3.1                   |
           |  Fecha cierre: 25/02/2026 (ultima visita) |
           |  Evidencia: Autoevaluacion_inicial.pdf    |
           +-------------------------------------------+

           Confirma? [Ejecutar] [Cancelar]

Consultor: [Ejecutar]

Agente:    2 actividades cerradas. PTA actualizado.
           Indicador plan de trabajo: 3.1% -> 6.3%
```

### Flujo sugerencia automatica (nivel Sugiere)

```
Consultor: "Revisa si Jacaranda tiene actividades listas para cerrar"

Agente:    Analicé las 8 actividades abiertas contra los documentos
           cargados en 2026. Encontré 3 con evidencia suficiente:

           1. [2.4.1] Diseñar Plan Anual de Trabajo
              Evidencia: Plan_Trabajo_2026.pdf (20/02/2026)

           2. [2.3.1] Diligenciar autoevaluación inicial
              Evidencia: Autoevaluacion_inicial.pdf (22/02/2026)

           3. [3.1.1] Evaluación de peligros y riesgos
              Evidencia: Matriz_Peligros_2026.xlsx (18/02/2026)

           Las otras 5 no tienen documento relacionado.
           Quiere que cierre estas 3? [Si, cerrar las 3] [Déjame revisar]
```

### Endpoints necesarios para escritura

| Endpoint | Metodo | Funcion | Esfuerzo |
|---------|--------|---------|----------|
| /ext-api/plan-trabajo/actividades-abiertas/{id_cliente} | GET | Lista actividades abiertas filtradas por periodo | 1-2 horas |
| /ext-api/cliente/{id}/documentos | GET | Documentos cargados por anio y tipo | 2 horas |
| /ext-api/plan-trabajo/match-evidencia/{id_cliente} | GET | Cruza actividades PTA con documentos cargados | 3-4 horas |
| /ext-api/plan-trabajo/cerrar-actividades | PUT | Cierra actividades por IDs + fecha + evidencia | 2-3 horas |
| /ext-api/cliente/{id}/ultima-visita | GET | Fecha de la ultima acta de visita | 30 min |

**Total adicional: ~9-12 horas**

### Auditoria y seguridad

| Aspecto | Implementacion |
|---------|---------------|
| Log de cambios | Cada cierre registra: quien pidio, via que canal (chat), fecha/hora, actividades afectadas |
| Tabla de auditoria | `tbl_agente_acciones` con id_consultor, accion, payload JSON, timestamp |
| Rollback | El consultor puede pedir "Deshaz el ultimo cierre" dentro de las 24 horas |
| Solo consultores | El perfil Cliente NUNCA puede ejecutar acciones de escritura |
| Confirmacion obligatoria | El agente SIEMPRE muestra resumen y pide confirmacion antes de escribir en la BD |
| Doble confirmacion para lotes > 5 | Si son mas de 5 actividades, pide confirmacion dos veces |

### Impacto en productividad del consultor

| Sin agente | Con agente |
|-----------|-----------|
| Abrir modulo PTA, filtrar cliente, buscar actividades, abrir cada una, cambiar estado, guardar | "Cierra 2.4.1 y 2.3.1 con fecha de ultima visita" → confirmar → listo |
| Tiempo: ~3 min por actividad x 10 actividades = 30 min | Tiempo: ~2 min total para las 10 |
| Buscar manualmente que documento corresponde a que actividad | El agente cruza automaticamente documentos con actividades |
| 46 clientes x 30 min = 23 horas/mes en cierre de PTA | 46 clientes x 2 min = 1.5 horas/mes |
