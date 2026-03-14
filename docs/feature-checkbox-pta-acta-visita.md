# Feature: Checkbox PTA en Acta de Visita

**Fecha:** 2026-03-14
**Branch:** cycloid

---

## Problema

Cuando un consultor visitaba un cliente y diligenciaba el Acta de Visita, no existia vinculacion con el Plan de Trabajo Anual (PTA). El consultor debia:

1. Crear el acta de visita manualmente
2. Ir por separado al modulo PTA y cerrar una por una las actividades que ejecuto
3. No quedaba evidencia documental de cuales actividades del PTA se gestionaron en esa visita
4. Las actividades que NO se cerraban no tenian justificacion registrada

---

## Solucion implementada

Se agrego una nueva seccion al formulario de Acta de Visita que carga automaticamente las actividades del PTA (Plan de Trabajo Anual) correspondientes al mes de la visita, permitiendo al consultor marcar con checkbox las que cerro y justificar las que no.

### Flujo completo

```
Consultor selecciona CLIENTE + FECHA DE VISITA
        |
        v
AJAX carga actividades PTA donde:
  - id_cliente = cliente seleccionado
  - MONTH(fecha_propuesta) <= mes de fecha_visita (incluye rezagadas de meses anteriores)
  - YEAR(fecha_propuesta) = anio de fecha_visita
  - estado_actividad = 'ABIERTA'
  - Las actividades de meses anteriores se muestran con badge "REZAGADA"
        |
        v
Se muestran como CHECKBOXES en seccion accordion
        |
        v
Consultor marca las que cerro ✓
        |
        v
Al hacer SUBMIT (borrador o ir a firmas):
        |
        +-- Si hay actividades NO marcadas:
        |       |
        |       v
        |   SweetAlert pide justificacion por cada una
        |   (todas obligatorias, no puede dejar vacias)
        |       |
        |       v
        |   Justificacion se CONCATENA a campo 'observaciones'
        |   en tbl_pta_cliente con formato:
        |   [Acta Visita #45 - 2026-03-14] No cerrada: texto del consultor
        |
        +-- Actividades MARCADAS:
                |
                v
            En tbl_pta_cliente:
              - estado_actividad = 'CERRADA'
              - porcentaje_avance = 100
              - fecha_cierre = fecha_visita del acta
            + Auditoria completa (PtaAuditService + PtaTransicionesService)
```

### Donde aparecen las actividades cerradas

- **Formulario de edicion:** Checkboxes pre-marcados y deshabilitados (ya cerradas)
- **Vista de solo lectura:** Card "ACTIVIDADES PTA GESTIONADAS" con badge verde
- **PDF generado:** Seccion 4 "ACTIVIDADES PTA GESTIONADAS" con tabla numeral/actividad/fecha/estado

---

## Archivos creados

### 1. `app/SQL/migrate_acta_visita_pta.php`
Script de migracion CLI para crear la tabla `tbl_acta_visita_pta`.

**Uso:**
```bash
# Local
php app/SQL/migrate_acta_visita_pta.php

# Produccion
DB_PROD_PASS=xxx php app/SQL/migrate_acta_visita_pta.php production
```

### 2. `app/Models/ActaVisitaPtaModel.php`
Modelo CodeIgniter para la tabla de vinculacion.

**Metodos:**
- `getByActa(int $idActa)` — Todas las actividades PTA vinculadas a un acta (con JOIN a tbl_pta_cliente)
- `getCerradasByActa(int $idActa)` — Solo las marcadas como cerradas (para PDF y vista)

---

## Archivos modificados

### 3. `app/Models/PtaClienteNuevaModel.php`
**Cambio:** Se agrego metodo `getAbiertosByClienteYMes(int $idCliente, string $fechaVisita)`

Consulta actividades donde:
- `id_cliente` = cliente dado
- `estado_actividad` = 'ABIERTA'
- `MONTH(fecha_propuesta)` <= mes de la fecha de visita (incluye rezagadas)
- `YEAR(fecha_propuesta)` = anio de la fecha de visita

Ordenadas por `fecha_propuesta ASC`, luego `numeral_plandetrabajo ASC`.

### 4. `app/Config/Routes.php`
**Cambio:** Se agrego 1 ruta nueva dentro del grupo `inspecciones`:

```php
$routes->get('acta-visita/api/pta-actividades', 'ActaVisitaController::getPtaActividades');
```

### 5. `app/Controllers/Inspecciones/ActaVisitaController.php`
**Cambios:**

| Cambio | Descripcion |
|--------|-------------|
| Imports | Se agregaron `ActaVisitaPtaModel`, `PtaClienteNuevaModel`, `PtaAuditService`, `PtaTransicionesService` |
| `getPtaActividades()` | Endpoint AJAX interno (dentro del grupo autenticado `inspecciones`). Recibe `id_cliente`, `fecha_visita`, `id_acta` (opcional). Retorna JSON con actividades abiertas (incluye rezagadas) y estado previo de checkboxes |
| `savePtaActividades()` | Nuevo metodo privado. Valida que cada `id_ptacliente` pertenezca al `id_cliente` del acta (seguridad). Procesa checkboxes del POST, cierra actividades marcadas en PTA, concatena justificaciones en observaciones para las no marcadas |
| `store()` | Agrego llamada a `savePtaActividades($idActa)` (solo en submit real, NO en autosave) |
| `update()` | Agrego llamada a `savePtaActividades($id)` (solo en submit real, NO en autosave) |
| `edit()` | Agrego `ptaLinks` al array `$data` para pre-cargar estado de checkboxes |
| `view()` | Agrego `ptaCerradas` al array `$data` |
| `generarPdfInterno()` | Agrego `ptaCerradas` al array `$data` pasado al template PDF |

### 6. `app/Views/inspecciones/acta_visita/form.php`
**Cambios:**

**HTML:**
- Nueva seccion accordion "Actividades PTA del Mes" con contador, entre "Temas Abiertos" y "Observaciones"
- Contenedor `#ptaContent` donde se renderizan los checkboxes via AJAX

**JavaScript:**
- `loadPtaActividades()` — Funcion que hace fetch al endpoint API cuando cambia cliente o fecha
- Renderiza checkboxes con `name="pta_actividad_checked[]"` y hidden `name="pta_actividad_id[]"`
- Actividades ya cerradas en edicion anterior: checked + disabled + badge verde
- `askPtaJustifications(callback)` — SweetAlert con textarea por cada actividad no marcada, todas obligatorias
- Interceptor de `submit` event: si hay items PTA, previene submit, pide justificaciones, inyecta hidden inputs `pta_justificacion[{id}]`, luego hace `requestSubmit()`
- Boton "Ir a firmas" tambien pasa por el flujo de justificaciones antes de submit
- Flag `_ptaProcessed` evita doble interceptacion
- Autosave NO se ve afectado (usa fetch directo, no dispara evento submit)

### 7. `app/Views/inspecciones/acta_visita/pdf.php`
**Cambios:**
- Nueva seccion **4. ACTIVIDADES PTA GESTIONADAS** con tabla: Numeral | Actividad | Fecha Propuesta | Estado (verde CERRADA)
- Secciones renumeradas: Observaciones paso a 5, Compromisos a 6, Registro Fotografico a 7

### 8. `app/Views/inspecciones/acta_visita/view.php`
**Cambios:**
- Nueva card "ACTIVIDADES PTA GESTIONADAS" antes de Observaciones
- Muestra cada actividad con numeral, nombre, badge verde "CERRADA" y fecha propuesta

---

## Base de datos

### Nueva tabla: `tbl_acta_visita_pta`

```sql
CREATE TABLE tbl_acta_visita_pta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_acta_visita INT NOT NULL,
    id_ptacliente INT NOT NULL,
    cerrada TINYINT(1) NOT NULL DEFAULT 0,
    justificacion_no_cierre TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_acta_visita) REFERENCES tbl_acta_visita(id) ON DELETE CASCADE,
    FOREIGN KEY (id_ptacliente) REFERENCES tbl_pta_cliente(id_ptacliente) ON DELETE CASCADE,
    UNIQUE KEY uk_acta_pta (id_acta_visita, id_ptacliente)
);
```

| Columna | Tipo | Descripcion |
|---------|------|-------------|
| `id` | INT PK | Auto-increment |
| `id_acta_visita` | INT FK | Referencia al acta de visita |
| `id_ptacliente` | INT FK | Referencia a la actividad PTA |
| `cerrada` | TINYINT(1) | 1 = actividad cerrada en esta visita, 0 = no cerrada |
| `justificacion_no_cierre` | TEXT | Razon por la que no se cerro (solo si cerrada=0) |
| `created_at` | DATETIME | Timestamp de creacion |

**Constraint UNIQUE** `uk_acta_pta`: Impide duplicados de la misma actividad PTA en la misma acta.

**CASCADE:** Si se elimina el acta o la actividad PTA, los links se eliminan automaticamente.

**Migracion ejecutada:** LOCAL y PRODUCCION el 2026-03-14.

---

## Consideraciones tecnicas

### Autosave
El procesamiento de PTA (cerrar actividades, guardar justificaciones) **solo ocurre en submit real**, nunca en autosave. El autosave usa fetch AJAX directo y no dispara el evento `submit` del formulario.

### Race condition
Si dos consultores tienen actas del mismo mes para el mismo cliente, una actividad podria aparecer en ambos formularios. El metodo `savePtaActividades` verifica que `estado_actividad === 'ABIERTA'` antes de cerrar, evitando cerrar una actividad ya cerrada.

### Observaciones concatenadas (decision de diseño)
Las justificaciones se guardan en DOS lugares:
1. **`tbl_acta_visita_pta.justificacion_no_cierre`** — dato estructurado, vinculado al acta
2. **`tbl_pta_cliente.observaciones`** — concatenado al texto existente

La duplicacion es **intencional**: el modulo PTA (`/pta-cliente-nueva/list`) muestra la columna `observaciones` directamente sin hacer JOIN a la tabla de actas. Si solo se guardara en `tbl_acta_visita_pta`, el consultor que revisa el PTA no veria la justificacion sin navegar al acta. Formato:
```
[Acta Visita #45 - 2026-03-14] No cerrada: El administrador no tenia los documentos listos
```
Esto permite historial acumulativo de multiples visitas.

### Actividades rezagadas
La consulta trae actividades ABIERTAS del mes de la visita **y de meses anteriores del mismo anio**. Ejemplo: si la visita es en marzo 2026, trae actividades con `fecha_propuesta` de enero, febrero y marzo 2026 que sigan abiertas. Las de meses anteriores se muestran con badge amarillo "REZAGADA" para distinguirlas visualmente.

### Seguridad: validacion de pertenencia
`savePtaActividades()` valida que cada `id_ptacliente` recibido por POST pertenezca al `id_cliente` del acta. Esto previene que un usuario malintencionado inyecte IDs de actividades PTA de otro cliente via manipulacion del formulario.

### No hay reversa desde el acta
Si un consultor cerro una actividad por error, **no puede reabrirla desde el acta de visita**. Debe ir al modulo PTA (`/pta-cliente-nueva/list`) y cambiar el estado manualmente. Esta decision es intencional: el cierre es una accion con consecuencias de auditoria y no debe deshacerse casualmente.

### Auditoria
Cada cierre de actividad genera:
- 3 registros en `tbl_pta_cliente_audit` (estado, porcentaje, fecha_cierre)
- 1 registro en `tbl_pta_transiciones` (ABIERTA → CERRADA)
- Metodo registrado: `ActaVisitaController::savePtaActividades`

Cada justificacion genera:
- 1 registro en `tbl_pta_cliente_audit` (observaciones)

---

## Resumen de archivos

| Archivo | Accion | Lineas aprox |
|---------|--------|--------------|
| `app/SQL/migrate_acta_visita_pta.php` | CREADO | 55 |
| `app/Models/ActaVisitaPtaModel.php` | CREADO | 45 |
| `app/Models/PtaClienteNuevaModel.php` | MODIFICADO | +15 |
| `app/Config/Routes.php` | MODIFICADO | +1 |
| `app/Controllers/Inspecciones/ActaVisitaController.php` | MODIFICADO | +120 |
| `app/Views/inspecciones/acta_visita/form.php` | MODIFICADO | +130 |
| `app/Views/inspecciones/acta_visita/pdf.php` | MODIFICADO | +20 |
| `app/Views/inspecciones/acta_visita/view.php` | MODIFICADO | +15 |
