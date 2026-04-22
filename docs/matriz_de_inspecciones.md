# Matriz de Inspecciones — Documentación técnica

**Fecha creación del módulo:** 2026-04-21 / 2026-04-22
**Ruta de acceso:** `/inspecciones/matriz`
**Card en dashboard:** `/inspecciones` → "Matriz de Inspecciones" (gradient dorado)

---

## Propósito

Vista ejecutiva por cliente que cruza, en un solo lugar:

1. Todas las **inspecciones ejecutables** del catálogo (43 tipos) — qué se hizo, qué falta, qué no aplica.
2. Las **actividades del PTA** (`tbl_pta_cliente`) vinculadas a cada tipo — permite al consultor ver qué está planeado, qué está vencido, qué está próximo.
3. Acciones rápidas durante una visita: marcar N/A, vincular un PTA existente, crear un PTA nuevo con asistencia de IA.

El flujo objetivo es que un consultor parado en la copropiedad abra la matriz y en 10 segundos entienda qué tiene pendiente, qué está atrasado y qué puede planear.

---

## Flujo de usuario

### Pantalla 1 — Selector de cliente
`GET /inspecciones/matriz`
- Select2 con todos los clientes.
- Filtro de año (default = año actual).
- Botón "Ver matriz" → navega a pantalla 2.

### Pantalla 2 — Detalle por cliente
`GET /inspecciones/matriz/{id_cliente}?anio=YYYY`

Header:
- Nombre + NIT del cliente.
- Selector de año (recarga al cambiar).

KPIs clickeables (filtran la tabla):
- 🔵 **Todas** — total de tipos del catálogo.
- 🟢 **Hechas** — con ≥1 inspección ejecutada en el año (estado='completo').
- 🟡 **Pendientes** — sin ejecución aún, sin fecha planeada vencida.
- 🔴 **Atrasadas** — todas las fechas PTA planeadas ya pasaron (incluido hoy) sin ejecución.
- ⚪ **No Aplica** — tipos marcados como no aplicables al cliente (piscinas para edificio sin piscina, etc.).

Barra de cobertura: `verde / (verde + amarillo + rojo)`, excluye N/A y Sin Match.

Tabla con:
- **Grupo** — categoría del tipo (SST, Saneamiento, Plan Emergencia, etc.).
- **Tipo de Inspección** — icono + label (ej: 🔥 Extintores).
- **Fechas en {año}** — ejecuciones + pill colapsable "N planeadas" que al expandir muestra cada PTA con fecha, numeral, estado y texto truncado.
- **Estado** — badge del color del semáforo.
- **Acciones** — Plus (nueva inspección), Listar todas, Vincular PTA (🔗), Crear PTA (📅+), Marcar N/A (🚫) o Quitar N/A (↶).

Filtros por columna en el `thead` (Grupo, Tipo, Estado), DataTable con `stateSave=true` — los filtros persisten en localStorage.

---

## Arquitectura de archivos

| Archivo | Rol |
|---|---|
| `app/Controllers/Inspecciones/MatrizInspeccionesController.php` | Controlador con 9 métodos (index, detalle, marcarNoAplica, quitarNoAplica, listarPtaPorSlug, vincularPta, desvincularPta, crearPta, generarDetallesPta). |
| `app/Libraries/InspeccionTypes.php` | Catálogo central de 43 tipos de inspección en 10 grupos. Cada tipo declara: slug, label, group, icon, table, date_col, estado_col, estado_value, extra_where, list_route, create_route, view_route. |
| `app/Models/InspeccionNoAplicaModel.php` | CRUD para `tbl_inspeccion_no_aplica` (cliente × tipo marcados como no aplicables). |
| `app/Models/PtaInspeccionMatchModel.php` | CRUD para `tbl_pta_inspeccion_match` (vínculos PTA ↔ tipo, con método 'ai' o 'manual'). |
| `app/Views/inspecciones/matriz/selector.php` | Pantalla 1. Select2 + año. |
| `app/Views/inspecciones/matriz/detalle.php` | Pantalla 2. KPIs + tabla + modal vincular + SweetAlert crear. |
| `app/Config/Routes.php` (líneas ~908-920) | 8 rutas del módulo. |

### Rutas

```
GET  /inspecciones/matriz                                → index (selector)
GET  /inspecciones/matriz/{id_cliente}                   → detalle (pantalla 2)
POST /inspecciones/matriz/no-aplica                      → marcarNoAplica (cliente × tipo)
POST /inspecciones/matriz/quitar-no-aplica               → quitarNoAplica
GET  /inspecciones/matriz/pta-list/{id_cliente}?slug=X&anio=YYYY&cerradas=1 → listarPtaPorSlug (modal vincular)
POST /inspecciones/matriz/vincular-pta                   → vincularPta (sync de checkboxes del modal)
POST /inspecciones/matriz/desvincular-pta                → desvincularPta (quitar 1 vínculo)
POST /inspecciones/matriz/crear-pta                      → crearPta (insert en tbl_pta_cliente + auto-vínculo)
POST /inspecciones/matriz/generar-pta-ia                 → generarDetallesPta (Claude Haiku autocompletar)
```

---

## Esquema de base de datos

### `tbl_inspeccion_no_aplica` (creada en este módulo)

```sql
CREATE TABLE tbl_inspeccion_no_aplica (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    tipo_inspeccion VARCHAR(50) NOT NULL,  -- slug del catálogo
    motivo VARCHAR(255) NULL,
    marcado_por INT NULL,
    fecha_marcado DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cliente_tipo (id_cliente, tipo_inspeccion)
);
```

Scripts: `app/SQL/create_tbl_inspeccion_no_aplica.sql`, `app/SQL/apply_migration_inspeccion_no_aplica.php`.

### `tbl_pta_inspeccion_match` (compartida con Semáforo PTA)

```sql
CREATE TABLE tbl_pta_inspeccion_match (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_ptacliente INT NOT NULL,           -- FK a tbl_pta_cliente
    slug_inspeccion VARCHAR(60) NOT NULL,
    score DECIMAL(4,3) DEFAULT 0.000,
    method ENUM('ai','manual','confirmed') DEFAULT 'ai',
    reasoning TEXT NULL,
    ai_model VARCHAR(60) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_pta_slug (id_cliente, id_ptacliente, slug_inspeccion)
);
```

Scripts: `app/SQL/create_tbl_pta_inspeccion_match.sql`, `app/SQL/apply_migration_pta_match.php`.

**Valores de `method`:**
- `ai` — generado en lote por `PtaClassifier` (Claude Haiku 4.5) sobre el texto de la actividad PTA.
- `manual` — vinculado por el consultor desde el botón 🔗 de la matriz, o auto-vinculado al crear PTA nuevo.
- `confirmed` — reservado para cuando un match IA es confirmado manualmente (no usado aún).

### Tabla consumida: `tbl_pta_cliente`

Columnas relevantes (existentes en el sistema):
- `id_ptacliente` PK
- `id_cliente` FK
- `phva_plandetrabajo` (PLANEAR/HACER/VERIFICAR/ACTUAR)
- `numeral_plandetrabajo` (ej: "1.2.3")
- `actividad_plandetrabajo` (texto libre)
- `fecha_propuesta`, `fecha_cierre`
- `estado_actividad` (ABIERTA, GESTIONANDO, CERRADA, etc.)
- `responsable_sugerido_plandetrabajo`, `responsable_definido_paralaactividad`
- `porcentaje_avance`, `observaciones`, `semana`
- `created_at`, `updated_at`

---

## Lógica del semáforo (estado por fila)

En `MatrizInspeccionesController::detalle()`:

```php
if ($marcadoNoAplica) {
    $estado = 'no_aplica';
} elseif ($totalInspeccionesEnAnio > 0) {
    $estado = 'hecha';
} elseif ($hayFechasPasadas && !$hayFechasFuturas) {
    // Todas las planeadas ya vencieron sin ejecutar
    $estado = 'atrasada';
} else {
    $estado = 'pendiente';
}
```

Donde:
- `fecha > hoy` ⇒ futura (próxima planeada).
- `fecha <= hoy` ⇒ pasada / vencida (incluye hoy — si planeaste para hoy y no ejecutaste, ya es atrasada).

Se consideran solo PTAs con `YEAR(fecha_propuesta) = anio_seleccionado`.

---

## Integración con IA (Claude Haiku 4.5)

### Uso 1 — Clasificación batch (heredada del Semáforo PTA)

Script CLI `app/SQL/classify_pta.php` (del módulo Semáforo) procesa todas las actividades PTA existentes y crea matches en `tbl_pta_inspeccion_match` con `method='ai'`. Ejecutado el 2026-04-21:

- LOCAL: 1,317 matches para 53 clientes.
- PRODUCCIÓN: 3,662 matches para 56 clientes (costo ≈ USD 0.15 total).

Comando:
```bash
php app/SQL/classify_pta.php local all
DB_PROD_PASS='xxx' php app/SQL/classify_pta.php production all [--reclassify]
```

**Esto es lo que "enciende" la matriz**: sin los matches IA preexistentes, cada tipo aparecería como huérfano.

### Uso 2 — Autocompletar al crear PTA (nuevo en este módulo)

Endpoint `POST /inspecciones/matriz/generar-pta-ia` invocado desde el SweetAlert "Crear actividad en PTA":

- Input: actividad (texto), slug del tipo de inspección.
- Output: JSON con `numeral`, `phva`, `responsable_sugerido`, `observaciones`.
- Modelo: `claude-haiku-4-5-20251001`.
- Costo: ~USD 0.00008 por llamada.

Se invoca via cURL directo (patrón del proyecto, no SDK). Requiere `ANTHROPIC_API_KEY` en `.env`.

---

## Funcionalidades por rol

### Consultor (flujo normal de visita)

1. Abrir `/inspecciones/matriz` y seleccionar la copropiedad.
2. Filtrar por card "Atrasadas" para ver qué es urgente.
3. Expandir pills "N planeadas" para leer el texto de la actividad PTA.
4. Click en el `+` de una fila → crea una nueva inspección del tipo.
5. Si aparece un tipo huérfano que corresponde a un PTA existente → botón 🔗 → modal → marcar PTAs → guardar.
6. Si aparece un tipo huérfano que NO tiene PTA → botón 📅+ → SweetAlert con IA → crea PTA y se auto-vincula.
7. Si un tipo claramente no aplica → botón 🚫 N/A.

### Marcar N/A (simplificado)

Un solo click abre SweetAlert de confirmación (sin campo de motivo — se pidió UX rápida). Motivo default: "El edificio no tiene / No aplica al cliente" (o texto libre si se ingresa).

---

## Vínculo con el módulo Semáforo PTA (hermano dormido)

- Comparten la misma tabla `tbl_pta_inspeccion_match`.
- Comparten `PtaInspeccionMatchModel`, `InspeccionTypes`.
- El Semáforo PTA (ruta `/inspecciones/pta-semaforo`) está **oculto del dashboard** (commit `a17a5fa`) pero con toda la infra viva. Ver `docs/../memory/pta_semaforo_pausado.md`.
- Si un día se reactiva el Semáforo, los matches manuales creados desde la Matriz **ya aparecerán ahí** sin migración adicional.

---

## Datos operativos clave

- Tipos de inspección total: **43** (definidos en `InspeccionTypes::all()`).
- Grupos: SST, Plan Emergencia, Infraestructura Especializada, Dotaciones, Simulacros, Capacitaciones, Saneamiento Básico, Contingencias, Certificados de Servicio, KPIs Saneamiento, Otros.
- Persistencia de filtros: DataTables `stateSave: true, stateDuration: -1` (localStorage indefinido).
- Ordenamiento columna Fechas: usa `data-order` con la fecha efectiva (ejecutada > próxima > vencida > '9999-99-99' como fallback).
- Compatibilidad: tipos sin `id_cliente`, sin `fecha_propuesta`, o sin `estado_col` se manejan defensivamente con `tableExists()` + `getFieldNames()`.

---

## Commits relevantes (orden cronológico)

| Commit | Qué trajo |
|---|---|
| `c3dc6cc` | Expande catálogo a 43 tipos + DataTables + cards clickeables. |
| `305d788` | Certificados usan `created_at` + filtros persistentes `stateSave`. |
| `b96d8cc` | Vincular PTA: modal con checkboxes, endpoints list/vincular/desvincular. |
| `3ee2fc3` | Vínculos PTA filtrados por año seleccionado. |
| `6c52a4d` | Badges PTA colapsables con `<details>`. |
| `29c2c68` | Estado "Atrasada" + "Próxima: DD/MM" / "Vencida desde DD/MM". |
| `1ca03db` | Incluir hoy como vencida (`fp <= hoy`). |
| `254a66b` | Columna Fechas sortable + crear PTA desde huérfanas. |
| `8c68d7b` | Generador IA (Haiku) para numeral/PHVA/responsable/observaciones. |
| `fff1712` | Quitar campo Semana del form (sin valor). |
| `0481332` | Al expandir pill mostrar texto de actividad PTA con numeral. |

---

## Decisiones de diseño relevantes

1. **Reutilizar `tbl_pta_inspeccion_match`** en vez de crear una tabla nueva para vínculos manuales. Menos esquemas, el dato de "origen" queda en la columna `method`.

2. **Validación server-side estricta** en `vincularPta()`: todos los `id_ptacliente` enviados deben pertenecer al cliente Y al año del scope. 403 si alguno no cumple. Esto protege contra manipulación del request desde el cliente.

3. **Sincronización scoped al año** en `vincularPta()`: al guardar, solo se agregan/remueven vínculos dentro del año seleccionado. Vincular en 2025 no borra los de 2026 y viceversa.

4. **Claude Haiku en vez de Sonnet** para el autocompletar: costo/velocidad, la tarea es acotada. Si mañana la precisión no basta, cambiar a Sonnet son 2 líneas.

5. **Native `<details>`/`<summary>` en vez de JS** para colapsar badges: accesible, sin librería extra, funciona sin Bootstrap collapse.

6. **Matriz usa su propia `tbl_inspeccion_no_aplica` (cliente × tipo)** distinta de `tbl_pta_no_aplica` (cliente × PTA) del Semáforo. No mezclar: son dimensiones diferentes.

---

## Qué NO hace el módulo (intencional)

- No envía notificaciones por vencimientos atrasados — lo muestra pero no alerta.
- No genera PDFs ni reportes imprimibles — queda en UI.
- No permite editar el texto de una actividad PTA desde la matriz — solo crear nuevas (las existentes se editan desde `/pta-cliente-nueva/list`).
- No propaga cambios entre años — cada año es independiente.
- No bloquea vincular un PTA CERRADO a un tipo sin ejecución — solo lo muestra con badge verde al expandir.

---

## Cómo agregar un nuevo tipo de inspección

1. Crear tabla + modelo + controlador + vistas (patrón estándar del módulo Inspecciones).
2. Agregar entrada en `InspeccionTypes::all()`:

```php
[
    'slug' => 'nuevo-tipo',
    'label' => 'Nombre Visible',
    'group' => 'Grupo correspondiente',
    'icon' => 'fa-icon-class',
    'table' => 'tbl_inspeccion_nuevo_tipo',
    'date_col' => 'fecha_inspeccion',
    'estado_col' => 'estado',          // opcional, default 'estado'
    'estado_value' => 'completo',       // opcional, default 'completo'
    'extra_where' => [],                // opcional
    'list_route' => 'inspecciones/nuevo-tipo',
    'create_route' => 'inspecciones/nuevo-tipo/create',
    'view_route' => 'inspecciones/nuevo-tipo/view',
],
```

3. (Opcional) Re-correr `classify_pta.php --reclassify` para que la IA mapee las actividades PTA al nuevo slug.

---

## Cómo reactivar el Semáforo PTA (dormido)

1. Editar `app/Views/inspecciones/dashboard.php` y descomentar el bloque marcado como "Semaforo PTA — acceso desactivado temporalmente".
2. Deploy normal (6+1 pasos).

La infra (rutas, controlador `PtaSemaforoController`, modelos, tablas) está intacta.

---

## Dependencias externas

- **Claude Haiku 4.5** via Anthropic Messages API (`claude-haiku-4-5-20251001`).
  - Endpoint: `https://api.anthropic.com/v1/messages`.
  - Header: `x-api-key: {ANTHROPIC_API_KEY}`, `anthropic-version: 2023-06-01`.
  - API key en `.env` (ignorado por Git).

---

## Referencias

- Memoria: `memory/pta_semaforo_pausado.md` — detalle del hermano dormido.
- Código IA batch: `app/Libraries/PtaClassifier.php`, `app/SQL/classify_pta.php`.
- Estructura PTA original: generado por `ContractLibrary::autoGenerateWorkPlan()` leyendo `PTA2026.csv` (96 actividades) al activar contrato.
