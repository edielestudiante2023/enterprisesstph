# Vistas Web de Inspecciones para Clientes - Guía de Implementación

## Objetivo

Permitir que cada cliente vea sus inspecciones completadas como vistas web interactivas (no solo PDFs) desde su dashboard independiente. El cliente es 100% read-only — no edita ni modifica nada.

---

## Prerequisitos

- Módulo de inspecciones ya funcional (consultores crean, editan, finalizan)
- Tablas de inspecciones con campo `id_cliente` (FK) y `estado` (enum con valor `completo`)
- Dashboard de cliente existente con autenticación por sesión (`session('role') === 'client'`, `session('user_id')` = id del cliente)
- Modelos existentes para cada tipo de inspección

---

## Arquitectura del Patrón

```
app/
├── Controllers/
│   └── ClientInspeccionesController.php    ← NUEVO (1 controller, todos los tipos)
├── Views/
│   └── client/
│       └── inspecciones/                   ← NUEVO directorio
│           ├── layout.php                  ← Layout wrapper tema cliente
│           ├── dashboard.php               ← Hub: cards por tipo con conteo
│           ├── list.php                    ← Lista reutilizable por tipo
│           ├── acta_visita_view.php        ← Vista detalle read-only
│           ├── locativa_view.php           ← Vista detalle read-only
│           ├── senalizacion_view.php       ← Vista detalle read-only
│           ├── botiquin_view.php           ← Vista detalle read-only (32 items fijos)
│           ├── extintores_view.php         ← Vista detalle read-only (N extintores dinámico)
│           ├── comunicaciones_view.php     ← Vista detalle read-only (8 equipos fijos)
│           ├── gabinetes_view.php          ← Vista detalle read-only (N gabinetes + detectores)
│           ├── carta_vigia_list.php        ← Lista dedicada (solo firmadas, sin detalle individual)
│           ├── mantenimientos_list.php     ← Lista dedicada (todos los estados, con filtros)
│           ├── matriz_vulnerabilidad_view.php ← Vista detalle read-only (25 criterios + puntaje)
│           ├── probabilidad_peligros_view.php ← Vista detalle read-only (12 peligros + %)
│           ├── recursos_seguridad_view.php ← Vista detalle read-only (6 recursos + fotos)
│           ├── hv_brigadista_view.php     ← Vista detalle read-only (datos personales + cuestionario médico)
│           ├── plan_emergencia_view.php   ← Vista detalle read-only (20+ secciones plan emergencia)
│           ├── simulacro_view.php         ← Vista detalle read-only (cronograma + evaluación)
│           ├── limpieza_view.php          ← Vista detalle read-only (items limpieza + estado)
│           ├── dotacion_vigilante_view.php ← Vista detalle read-only (EPP vigilante)
│           ├── dotacion_aseadora_view.php  ← Vista detalle read-only (EPP aseadora)
│           ├── dotacion_todero_view.php    ← Vista detalle read-only (EPP todero 16 items)
│           ├── auditoria_zona_residuos_view.php ← Vista detalle read-only (12 items zona)
│           ├── asistencia_induccion_view.php ← Vista detalle read-only (sesión + asistentes)
│           ├── reporte_capacitacion_view.php ← Vista detalle read-only (capacitación + cobertura)
│           ├── preparacion_simulacro_view.php ← Vista detalle read-only (preparación + cronograma)
│           ├── residuos_view.php             ← Vista detalle read-only (programa residuos FT-SST-226)
│           ├── plagas_view.php               ← Vista detalle read-only (programa plagas FT-SST-227)
│           ├── agua_potable_view.php         ← Vista detalle read-only (programa agua FT-SST-228)
│           ├── saneamiento_view.php          ← Vista detalle read-only (plan saneamiento FT-SST-219)
│           ├── kpi_limpieza_view.php         ← Vista detalle read-only (KPI limpieza + evidencias)
│           ├── kpi_residuos_view.php         ← Vista detalle read-only (KPI residuos + evidencias)
│           ├── kpi_plagas_view.php           ← Vista detalle read-only (KPI plagas + evidencias)
│           └── kpi_agua_potable_view.php     ← Vista detalle read-only (KPI agua potable + evidencias)
├── Config/
│   └── Routes.php                          ← MODIFICADO (agregar grupo rutas)
└── Views/
    └── client/
        └── dashboard.php                   ← MODIFICADO (agregar botón)
```

---

## Paso 1: Crear el Controller

**Archivo:** `app/Controllers/ClientInspeccionesController.php`

### Estructura del controller

```php
<?php
namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
// + todos los modelos de inspección que necesites
use CodeIgniter\Controller;

class ClientInspeccionesController extends Controller
{
    // Helper privado para validar sesión cliente
    private function getClientId()
    {
        $session = session();
        if ($session->get('role') !== 'client') {
            return null;
        }
        return $session->get('user_id');
    }
```

### Método dashboard() — Hub principal

```php
    public function dashboard()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $client = (new ClientModel())->find($clientId);
        if (!$client) {
            return redirect()->to('/login')->with('error', 'Cliente no encontrado.');
        }

        // Construir array de tipos con conteo y última fecha
        $tipos = [
            [
                'nombre'      => 'Actas de Visita',
                'icono'       => 'fa-file-signature',
                'color'       => '#1c2437',
                'url'         => base_url('client/inspecciones/actas-visita'),
                'conteo'      => $actaModel->where('id_cliente', $clientId)
                                           ->where('estado', 'completo')
                                           ->countAllResults(false),
                'ultima'      => $actaModel->where('id_cliente', $clientId)
                                           ->where('estado', 'completo')
                                           ->orderBy('fecha_visita', 'DESC')
                                           ->first(),
                'campo_fecha' => 'fecha_visita',
            ],
            // ... repetir para cada tipo de inspección
        ];

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Mis Inspecciones',
            'content' => view('client/inspecciones/dashboard', ['tipos' => $tipos]),
        ]);
    }
```

### Patrón para list + view (repetir por cada tipo)

```php
    // LISTAR — solo estado='completo', filtrado por id_cliente
    public function listActas()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login');

        $client = (new ClientModel())->find($clientId);
        $inspecciones = (new ActaVisitaModel())
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_visita', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Actas de Visita',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'acta_visita',       // identificador
                'titulo'       => 'Actas de Visita',   // título visible
                'campo_fecha'  => 'fecha_visita',       // campo de fecha en la tabla
                'base_url'     => 'client/inspecciones/actas-visita', // para links
            ]),
        ]);
    }

    // VER DETALLE — validar que id_cliente coincida
    public function viewActa($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login');

        $acta = (new ActaVisitaModel())->find($id);

        // SEGURIDAD: verificar que pertenece al cliente logueado
        if (!$acta || (int)$acta['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'No encontrada.');
        }

        // Cargar datos relacionados con los modelos existentes
        $data = [
            'acta'        => $acta,
            'cliente'     => (new ClientModel())->find($acta['id_cliente']),
            'consultor'   => (new ConsultantModel())->find($acta['id_consultor']),
            'integrantes' => (new ActaVisitaIntegranteModel())->getByActa($id),
            'temas'       => (new ActaVisitaTemaModel())->getByActa($id),
            'fotos'       => (new ActaVisitaFotoModel())->getByActa($id),
            'compromisos' => (new PendientesModel())->where('id_acta_visita', $id)->findAll(),
        ];

        return view('client/inspecciones/layout', [
            'client'  => (new ClientModel())->find($clientId),
            'title'   => 'Acta de Visita',
            'content' => view('client/inspecciones/acta_visita_view', $data),
        ]);
    }
```

### Principios clave del controller

1. **`getClientId()`** centraliza la validación de sesión — si no es `client`, retorna `null`
2. **Siempre filtrar por `estado = 'completo'`** en los listados
3. **Siempre validar `id_cliente`** en las vistas de detalle (evitar que un cliente vea datos de otro)
4. **Reusar modelos existentes** — no crear modelos nuevos, usar los mismos `getByActa()`, `getByInspeccion()`, etc.
5. **Patrón de layout wrapper**: el controller renderiza `layout.php` pasando `$content` como vista interna pre-renderizada

---

## Paso 2: Crear el Layout

**Archivo:** `app/Views/client/inspecciones/layout.php`

Este layout replica el tema visual del dashboard del cliente (no el de la PWA del consultor).

### Elementos clave

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Bootstrap 5.3 + Font Awesome 6 -->
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
        .navbar { /* mismos estilos del dashboard: 3 logos, borde dorado */ }
        .page-header { /* gradiente dark-blue, border-radius 15px */ }
        .btn-back { /* botón transparente para volver */ }
        .card { /* sin borde, border-radius 12px, sombra suave */ }
        .btn-pdf { /* gradiente rojo para descargar PDF */ }
    </style>
</head>
<body>
    <nav class="navbar"><!-- 3 logos: empresa, SST, Cycloid --></nav>
    <div class="content-wrapper">
        <div class="container">
            <?= $content ?>  <!-- Aquí se inyecta la vista específica -->
        </div>
    </div>
    <footer><!-- Copyright --></footer>
    <script src="bootstrap.bundle.min.js"></script>
</body>
</html>
```

### Patrón layout → content

El controller siempre llama:
```php
return view('client/inspecciones/layout', [
    'client'  => $client,        // datos del cliente (para navbar si se necesita)
    'title'   => 'Título página', // <title> del HTML
    'content' => view('client/inspecciones/VISTA_ESPECIFICA', $data),
]);
```

La vista específica se pre-renderiza y se pasa como string en `$content`.

---

## Paso 3: Crear la Vista Dashboard (Hub)

**Archivo:** `app/Views/client/inspecciones/dashboard.php`

Recibe `$tipos` (array de arrays con nombre, icono, color, url, conteo, ultima, campo_fecha).

### Estructura

```
┌──────────────────────────────────────────────────┐
│  [← Dashboard]         Mis Inspecciones          │
├──────────────────────────────────────────────────┤
│                                                    │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐        │
│  │  🔖      │  │  🏢      │  │  🪧      │        │
│  │  Actas   │  │ Locativas│  │ Señaliz. │        │
│  │    5     │  │    3     │  │    2     │        │
│  │ Últ: 20/02│  │ Últ: 15/02│  │ Últ: 10/02│     │
│  └──────────┘  └──────────┘  └──────────┘        │
└──────────────────────────────────────────────────┘
```

Cada card es un link a la lista del tipo correspondiente. Si el conteo total es 0, muestra mensaje vacío.

---

## Paso 4: Crear la Vista Lista (Reutilizable)

**Archivo:** `app/Views/client/inspecciones/list.php`

Vista genérica que funciona para cualquier tipo de inspección. Recibe:

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `$inspecciones` | array | Registros de la BD |
| `$tipo` | string | Identificador (31 tipos): `acta_visita`, `locativa`, `senalizacion`, `botiquin`, `extintores`, `comunicaciones`, `gabinetes`, `matriz_vulnerabilidad`, `probabilidad_peligros`, `recursos_seguridad`, `hv_brigadista`, `plan_emergencia`, `simulacro`, `limpieza`, `dotacion_vigilante`, `dotacion_aseadora`, `dotacion_todero`, `auditoria_residuos`, `asistencia_induccion`, `reporte_capacitacion`, `preparacion_simulacro`, `residuos`, `plagas`, `agua_potable`, `plan_saneamiento`, `kpi_limpieza`, `kpi_residuos`, `kpi_plagas`, `kpi_agua_potable` + 2 listas dedicadas: `carta_vigia`, `mantenimientos` |
| `$titulo` | string | Título visible |
| `$campo_fecha` | string | Nombre del campo de fecha en la tabla |
| `$base_url` | string | URL base para los links de detalle |

### Lógica condicional por tipo

```php
<?php if ($tipo === 'senalizacion' && isset($insp['calificacion'])): ?>
    <!-- Mostrar calificación con color -->
<?php endif; ?>

<?php if ($tipo === 'acta_visita' && !empty($insp['motivo'])): ?>
    <!-- Mostrar motivo truncado -->
<?php endif; ?>
```

Cada card linkea a `base_url('$base_url/' . $insp['id'])`.

---

## Paso 5: Crear las Vistas de Detalle

Crear una vista por cada tipo de inspección. Cada una muestra los datos read-only con el tema del cliente.

### Patrón común de cada vista de detalle

```
1. page-header con título + botón "Volver" a la lista
2. Card "DATOS GENERALES" (tabla: cliente, consultor, fecha)
3. Cards específicas del tipo (hallazgos, temas, items, etc.)
4. Card "OBSERVACIONES GENERALES" (si hay)
5. Botón "Descargar PDF" (si ruta_pdf existe)
6. Modal para fotos ampliadas (Bootstrap modal)
7. Script openPhoto() para el modal
```

### Vista Acta de Visita (`acta_visita_view.php`)

Secciones: Datos Generales → Integrantes → Temas Tratados → Observaciones → Cartera → Compromisos → Registro Fotográfico → PDF

### Vista Locativa (`locativa_view.php`)

Secciones: Datos Generales → Hallazgos (con estado badge ABIERTO/CERRADO, fotos antes/después, fechas hallazgo/corrección) → Observaciones → PDF

### Vista Señalización (`senalizacion_view.php`)

Secciones: Datos Generales → Card Calificación (% grande color-coded + descripción cualitativa + conteos NA/NC/CP/CT) → Accordion de items agrupados por categoría (cada grupo con badge cumple/total) → Observaciones → PDF

### Vista Botiquín (`botiquin_view.php`)

Secciones: Datos Generales (+ ubicación, tipo, estado botiquín) → Fotos del Botiquín (2 fotos) → Condiciones Generales (4 SI/NO color-coded) → Accordion de elementos agrupados por categoría (tabla con Cant/Min/Estado/Vencimiento) → Sección especial "Equipos de inmovilización" (fotos tabla espinal, collares, inmovilizadores) → Recomendaciones → Pendientes Generados → PDF

**Nota especial:** El botiquín usa una constante `ELEMENTOS` (32 items fijos) definida en `InspeccionBotiquinController::ELEMENTOS`. La vista del cliente la importa desde ahí — no duplicar la constante.

### Vista Extintores (`extintores_view.php`)

Secciones: Datos Generales (+ vencimiento global, total extintores) → Inventario (tipos ABC/CO2/Solkaflam/Agua + distribución por ubicación) → Accordion de extintores individuales (cada uno con tabla de 12 criterios color-coded + foto + observaciones) → Recomendaciones Generales → PDF

**Lógica de colores para criterios:**
- BUENO/CARGADO/NO → verde (text-success)
- REGULAR → amarillo (text-warning)
- MALO/SI/DESCARGADO → rojo (text-danger)
- NO APLICA/NO TIENE → gris (text-muted)

### Vista Comunicaciones (`comunicaciones_view.php`)

Secciones: Datos Generales → 8 Equipos de Comunicación (cards con ícono, label, cantidad color-coded, observaciones) → Fotos Evidencia (2 fotos) → Observaciones Finales → PDF

**Nota:** Usa la constante `InspeccionComunicacionController::EQUIPOS` (8 items fijos con `label` e `icon`). Los campos en BD son `cant_{key}` y `obs_{key}` para cada equipo.

### Vista Gabinetes (`gabinetes_view.php`)

Secciones: Datos Generales → Gabinetes Contra Incendio (SI/NO tiene gabinetes, entregados por constructora, cantidad, elementos, ubicación, señalización) → Fotos Gabinetes (2 fotos) → Observaciones Gabinetes → Accordion de Gabinetes Individuales (cada uno con 6 SI/NO: manguera, hacha, extintor, válvula, boquilla, llave spanner + estado general + señalización + foto + observaciones) → Detectores de Humo (SI/NO existe, entregados, cantidad, ubicación) → Fotos Detectores (2 fotos) → Observaciones Detectores → PDF

**Lógica de colores para gabinetes:**
- SI → verde | NO → rojo (para campos SI/NO)
- BUENO → verde | REGULAR → amarillo | MALO/NO TIENE → rojo (para estados)

**Nota:** Usa `InspeccionGabineteController::CRITERIOS` y `GabineteDetalleModel::getByInspeccion($id)` para gabinetes individuales.

### Vista Cartas de Vigía (`carta_vigia_list.php`)

**Tipo especial: Solo lista, sin vista de detalle individual.** Muestra cartas firmadas del cliente (estado_firma='firmado'). Cada card muestra: nombre vigía, CC, email, teléfono, fecha de firma, código de verificación, enlace PDF.

**Nota:** No usa `list.php` genérica porque la estructura es fundamentalmente distinta (no tiene fecha_inspeccion ni id estándar). Usa `CartaVigiaModel` directamente.

### Vista Mantenimientos (`mantenimientos_list.php`)

**Tipo especial: Solo lista, sin vista de detalle individual.** Muestra TODOS los mantenimientos (no filtra por estado) con filtros JS por categoría (Todos/Pendientes/Ejecutados/Cerrados). Cada card muestra: detalle mantenimiento (join con tbl_mantenimientos), fecha vencimiento, días restantes, estado con color-coded badge, observaciones.

**Lógica de colores:**
- Vencido (diff < 0) → rojo | Próximo (diff ≤ 15) → amarillo | Vigente → dorado | Ejecutado → verde | Cerrado → gris

**Nota:** Usa `VencimientosMantenimientoModel` con JOIN a `MantenimientoModel` para obtener `detalle_mantenimiento`. Primary key es `id_vencimientos_mmttos`.

### Vista Matriz Vulnerabilidad (`matriz_vulnerabilidad_view.php`)

Secciones: Datos Generales → Resultado de Evaluación (puntaje /100, barra de progreso, clasificación color-coded) → 25 Criterios Evaluados (cada uno con badge A/B/C y texto de opción seleccionada) → Observaciones → PDF

**Lógica de puntaje:** Cada criterio vale: A=1.0, B=0.5, C=0.0. Puntaje = suma × 4 (máx 100).
**Clasificación:** 91-100 = Vulnerabilidad mínima (verde) | 71-90 = Baja (azul) | 51-70 = Media-alta (amarillo) | 0-50 = Alta (rojo)

**Nota:** Usa `MatrizVulnerabilidadController::CRITERIOS`, `::PUNTAJES`, `::CLASIFICACION`. Métodos `calcularPuntaje()` y `getClasificacion()` son públicos.

### Vista Probabilidad de Peligros (`probabilidad_peligros_view.php`)

Secciones: Datos Generales → 3 Grupos de Peligros (Naturales/Sociales/Tecnológicos con 12 items, cada uno con badge de frecuencia) → Resultados Consolidados (3 barras: Poco Probable %, Probable %, Muy Probable %) → Observaciones → PDF

**Lógica de colores frecuencia:** Poco Probable → verde | Probable → amarillo | Muy Probable → rojo

**Nota:** Usa `ProbabilidadPeligrosController::PELIGROS` y `::FRECUENCIAS`. El cálculo de porcentajes se replica en el controller del cliente.

### Vista Recursos de Seguridad (`recursos_seguridad_view.php`)

Secciones: Datos Generales → 6 Recursos de Seguridad (Lámparas emergencia, Antideslizantes, Pasamanos, Vigilancia, Iluminación exterior, Planes respuesta — cada uno con ícono, hint, observación, foto ampliable) → Observaciones Generales → PDF

**Nota:** Usa `InspeccionRecursosSeguridadController::RECURSOS` (6 items con label, icon, hint, tiene_foto). Los campos en BD son `obs_{key}` y `foto_{key}`.

### Vista HV Brigadista (`hv_brigadista_view.php`)

Secciones: Datos Personales (foto, nombre, documento, fecha nacimiento, edad, email, teléfono, dirección, EPS, RH, peso, estatura) → Estudios (loop 1-3 con institución y año) → Información de Salud (enfermedades, medicamentos) → Cuestionario Médico (14 preguntas SI/NO con badges color-coded) → Restricciones y Actividad (restricciones médicas, deportes) → Firma (imagen) → PDF

**Variable:** Usa `$hv` (no `$inspeccion`). Campo fecha: `created_at`.

**Lógica de colores cuestionario:** SI → amarillo (#ffc107) | NO → verde (#28a745) | otro → gris (#6c757d)

**Nota:** Usa `HvBrigadistaModel`. No tiene `estado` — se muestran todos los registros del cliente. No requiere constantes de controller.

### Vista Plan de Emergencia (`plan_emergencia_view.php`)

Vista muy extensa con 20+ secciones: Datos Generales → Fachada/Panorama (fotos) → Descripción del Inmueble (área terreno, pisos, tipo construcción, material, unidades) → Parqueaderos (tipo, capacidad, cubierto) → Áreas Comunes (salón social, zonas verdes, parque infantil, portería, otros) → Servicios del Conjunto (gas, acueducto, energía, ascensor, shut basura, citófono, CCTV, control acceso) → Circulaciones (loop 5 secciones: piso, material, señalización, estado, foto) → Concepto del Consultor → Entorno → Proveedores → Control de Visitantes → Comunicaciones → Ruta de Evacuación → Puntos de Encuentro → Sistemas de Alarma → Administración → Teléfonos de Emergencia (tabla con datos Bogotá/Soacha) → Gabinetes → Servicios Generales (empresas de aseo) → Observaciones Finales → PDF

**Variable:** Usa `$inspeccion`. Campo fecha: `fecha_visita`.

**Nota:** Usa `PlanEmergenciaModel`, `PlanEmergenciaController::TELEFONOS` (array Bogotá/Soacha con entidades y números), `PlanEmergenciaController::EMPRESAS_ASEO` (6 items). Vista más grande del sistema (~600 líneas).

### Vista Simulacro (`simulacro_view.php`)

Secciones: Identificación (copropiedad, NIT, fecha, dirección) → Información General (evento simulado, alcance, tipo evacuación, personal no evacua, tipo alarma, puntos encuentro, recurso humano, equipos emergencia) → Brigadista Líder (nombre, email, WhatsApp, distintivos) → Cronograma del Simulacro (9 pasos con hora + tiempo total) → Conteo de Evacuados (hombres, mujeres, niños, adultos mayores, discapacidad, mascotas + total) → Evaluación del Simulacro (5 criterios con /10 y barra progreso color-coded) → Evaluación Cuantitativa/Cualitativa → Evidencias (observaciones + 2 fotos) → PDF

**Variable:** Usa `$eval` (no `$inspeccion`). Campo fecha: `fecha`.

**Lógica de colores evaluación:** >=8 → verde (#28a745) | >=5 → amarillo (#ffc107) | <5 → rojo (#dc3545)

**Nota:** Usa `EvaluacionSimulacroModel`. Los 5 criterios de evaluación son: alarma_efectiva, orden_evacuacion, liderazgo_brigadistas, organizacion_punto_encuentro, participacion_general.

### Vista Limpieza y Desinfección (`limpieza_view.php`)

Secciones: Datos Generales → Items de Limpieza (cada uno con estado badge color-coded + foto) → Observaciones → PDF

**Nota:** Usa `ProgramaLimpiezaModel`. Campo fecha: `fecha_inspeccion`.

### Vista Dotación Vigilante (`dotacion_vigilante_view.php`)

Secciones: Datos Generales (contratista, servicio, nombre/cargo, actividades) → Registro Fotográfico (cuerpo completo, cuarto almacenamiento) → Estado de Dotación EPP (items con badge color-coded) → Concepto Final → Observaciones → PDF

**Lógica de colores EPP:** Bueno → verde (#28a745) | Regular → amarillo (#ffc107) | Deficiente → rojo (#dc3545) | No tiene → gris (#6c757d) | No aplica → gris claro (#adb5bd)

**Nota:** Usa `DotacionVigilanteModel`, `DotacionVigilanteController::ITEMS_EPP`, `::ESTADOS_EPP`. Campo fecha: `fecha_inspeccion`.

### Vista Dotación Aseadora (`dotacion_aseadora_view.php`)

Secciones: Idénticas a Dotación Vigilante, diferente ícono (fa-broom) y título.

**Nota:** Usa `DotacionAseadoraModel`, `DotacionAseadoraController::ITEMS_EPP`, `::ESTADOS_EPP`. Campo fecha: `fecha_inspeccion`.

### Vista Dotación Todero (`dotacion_todero_view.php`)

Secciones: Idénticas a Dotación Vigilante, diferente ícono (fa-hard-hat) y título. Tiene 16 items EPP.

**Nota:** Usa `DotacionToderoModel`, `DotacionToderoController::ITEMS_EPP`, `::ESTADOS_EPP`. Campo fecha: `fecha_inspeccion`.

### Vista Auditoría Zona Residuos (`auditoria_zona_residuos_view.php`)

Secciones: Datos Generales → Items de Inspección (cada item con tipo enum/texto_libre: enum muestra badge color-coded, texto_libre muestra texto + foto por item) → Observaciones → PDF

**Lógica de colores zona:** Bueno → verde | Regular → amarillo | Malo → naranja (#fd7e14) | Deficiente → rojo | No tiene → gris | No aplica → gris claro

**Nota:** Usa `AuditoriaZonaResiduosModel`, `AuditoriaZonaResiduosController::ITEMS_ZONA`, `::ESTADOS_ZONA`. Campo fecha: `fecha_inspeccion`. Items con tipo `enum` usan campo `estado_{key}`, items con tipo `texto_libre` usan campo directo `{key}`.

### Vista Asistencia Inducción (`asistencia_induccion_view.php`)

Secciones: Datos Generales (fecha sesión) → Información de la Sesión (tema, lugar, objetivo, capacitador, tipo de charla badge, material, tiempo horas) → Asistentes (tabla con #, nombre, cédula, cargo, firma imagen) → Observaciones → PDF Asistencia

**Nota:** Usa `AsistenciaInduccionModel` + `AsistenciaInduccionAsistenteModel` (para tabla asistentes). `AsistenciaInduccionController::TIPOS_CHARLA` para labels de tipo de charla. Campo fecha: `fecha_sesion`. Campo PDF: `ruta_pdf_asistencia`.

### Vista Reporte Capacitación (`reporte_capacitacion_view.php`)

Secciones: Datos Generales (fecha capacitación) → Información de la Capacitación (nombre, objetivo, perfil asistentes badges, capacitador, duración horas) → Asistencia y Evaluación (asistentes, programados, evaluados, % cobertura color-coded, promedio calificaciones) → Registro Fotográfico (5 fotos: listado asistencia, capacitación, evaluación, otros 1, otros 2) → Observaciones → PDF

**Nota:** Usa `ReporteCapacitacionModel`, `ReporteCapacitacionController::PERFILES_ASISTENTES`. Cobertura = (asistentes/programados)×100. Campo fecha: `fecha_capacitacion`.

### Vista Preparación Simulacro (`preparacion_simulacro_view.php`)

Secciones: Datos Generales (fecha, ubicación, dirección) → Configuración del Simulacro (evento simulado, alcance, tipo evacuación, personal no evacua) → Alarma y Distintivos (badges multi-select para tipo alarma + distintivos brigadistas) → Logística (puntos encuentro, recurso humano + equipos emergencia badges multi-select) → Brigadista Líder (nombre, email, WhatsApp) → Cronograma (9 pasos con hora + tiempo total calculado inicio→cierre) → Evidencias (2 fotos) → Evaluación (entrega formato) → Observaciones → PDF

**Nota:** Usa `PreparacionSimulacroModel`, constantes del controller: `::OPCIONES_ALARMA`, `::OPCIONES_DISTINTIVOS`, `::OPCIONES_EQUIPOS`, `::CRONOGRAMA_ITEMS`. Campos multi-select (tipo_alarma, distintivos_brigadistas, equipos_emergencia) almacenados como comma-separated en BD, renderizados como arrays de badges. Campo fecha: `fecha_simulacro`.

### Vista Programa Residuos Sólidos (`residuos_view.php`)

Secciones: Datos Generales (cliente, fecha programa, responsable, consultor, documento FT-SST-226) → PDF

**Nota:** Usa `ProgramaResiduosModel`. Vista compacta — datos generales + botón PDF. Campo fecha: `fecha_programa`. Tabla: `tbl_programa_residuos`.

### Vista Programa Control de Plagas (`plagas_view.php`)

Secciones: Datos Generales (cliente, fecha programa, responsable, consultor, documento FT-SST-227) → PDF

**Nota:** Usa `ProgramaPlagasModel`. Vista compacta — misma estructura que residuos. Campo fecha: `fecha_programa`. Tabla: `tbl_programa_plagas`.

### Vista Programa Agua Potable (`agua_potable_view.php`)

Secciones: Datos Generales (cliente, fecha programa, responsable, consultor, cantidad tanques, capacidad individual, capacidad total, documento FT-SST-228) → PDF

**Nota:** Usa `ProgramaAguaPotableModel`. Campos adicionales vs los otros programas: `cantidad_tanques`, `capacidad_individual`, `capacidad_total`. Campo fecha: `fecha_programa`. Tabla: `tbl_programa_agua_potable`.

### Vista Plan de Saneamiento Básico (`saneamiento_view.php`)

Secciones: Datos Generales (cliente, fecha plan, responsable, consultor, documento FT-SST-219) → PDF

**Nota:** Usa `PlanSaneamientoModel`. Vista compacta — misma estructura que los programas. Campo fecha: `fecha_programa`. Tabla: `tbl_plan_saneamiento`.

### Vista KPI Limpieza y Desinfección (`kpi_limpieza_view.php`)

Secciones: Datos del KPI (fecha, responsable, indicador, cumplimiento %) → Evidencias (hasta 4 fotos: registro_formato_1 a registro_formato_4) → PDF

**Nota:** Usa `KpiLimpiezaModel`. Los 4 KPIs comparten la misma estructura de vista. Campo fecha: `fecha_inspeccion`. Tabla: `tbl_kpi_limpieza`. Ruta PDF: `/inspecciones/kpi-limpieza/pdf/{id}`.

### Vista KPI Residuos Sólidos (`kpi_residuos_view.php`)

Secciones: Idénticas a KPI Limpieza, diferente título.

**Nota:** Usa `KpiResiduosModel`. Tabla: `tbl_kpi_residuos`. Ruta PDF: `/inspecciones/kpi-residuos/pdf/{id}`.

### Vista KPI Control de Plagas (`kpi_plagas_view.php`)

Secciones: Idénticas a KPI Limpieza, diferente título.

**Nota:** Usa `KpiPlagasModel`. Tabla: `tbl_kpi_plagas`. Ruta PDF: `/inspecciones/kpi-plagas/pdf/{id}`.

### Vista KPI Agua Potable (`kpi_agua_potable_view.php`)

Secciones: Idénticas a KPI Limpieza, diferente título.

**Nota:** Usa `KpiAguaPotableModel`. Tabla: `tbl_kpi_agua_potable`. Ruta PDF: `/inspecciones/kpi-agua-potable/pdf/{id}`.

### Lógica de colores para calificación

```php
$califColor = '#28a745'; // verde por defecto
if ($calif <= 40) $califColor = '#dc3545';     // rojo - Crítico
elseif ($calif <= 60) $califColor = '#fd7e14';  // naranja - Bajo
elseif ($calif <= 80) $califColor = '#ffc107';  // amarillo - Medio
// >80 queda verde - Bueno/Excelente
```

### Modal de fotos (copiar en cada vista que tenga fotos)

```html
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0 py-1">
                <small class="text-light" id="photoDesc"></small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-1 text-center">
                <img id="photoFull" src="" class="img-fluid" style="max-height:80vh;">
            </div>
        </div>
    </div>
</div>
<script>
function openPhoto(src, desc) {
    document.getElementById('photoFull').src = src;
    document.getElementById('photoDesc').textContent = desc || '';
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}
</script>
```

---

## Paso 6: Agregar Rutas

**Archivo:** `app/Config/Routes.php`

```php
// Client Inspections (read-only web views) — 62 rutas GET, 31 módulos
$routes->group('client/inspecciones', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'ClientInspeccionesController::dashboard');
    // --- Inspecciones operativas (Fase 1-3) ---
    $routes->get('actas-visita', 'ClientInspeccionesController::listActas');
    $routes->get('actas-visita/(:num)', 'ClientInspeccionesController::viewActa/$1');
    $routes->get('locativas', 'ClientInspeccionesController::listLocativas');
    $routes->get('locativas/(:num)', 'ClientInspeccionesController::viewLocativa/$1');
    $routes->get('senalizacion', 'ClientInspeccionesController::listSenalizacion');
    $routes->get('senalizacion/(:num)', 'ClientInspeccionesController::viewSenalizacion/$1');
    $routes->get('botiquin', 'ClientInspeccionesController::listBotiquin');
    $routes->get('botiquin/(:num)', 'ClientInspeccionesController::viewBotiquin/$1');
    $routes->get('extintores', 'ClientInspeccionesController::listExtintores');
    $routes->get('extintores/(:num)', 'ClientInspeccionesController::viewExtintores/$1');
    $routes->get('comunicaciones', 'ClientInspeccionesController::listComunicaciones');
    $routes->get('comunicaciones/(:num)', 'ClientInspeccionesController::viewComunicacion/$1');
    $routes->get('gabinetes', 'ClientInspeccionesController::listGabinetes');
    $routes->get('gabinetes/(:num)', 'ClientInspeccionesController::viewGabinete/$1');
    $routes->get('carta-vigia', 'ClientInspeccionesController::listCartasVigia');
    $routes->get('mantenimientos', 'ClientInspeccionesController::listMantenimientos');
    $routes->get('matriz-vulnerabilidad', 'ClientInspeccionesController::listMatrizVulnerabilidad');
    $routes->get('matriz-vulnerabilidad/(:num)', 'ClientInspeccionesController::viewMatrizVulnerabilidad/$1');
    $routes->get('probabilidad-peligros', 'ClientInspeccionesController::listProbabilidadPeligros');
    $routes->get('probabilidad-peligros/(:num)', 'ClientInspeccionesController::viewProbabilidadPeligros/$1');
    $routes->get('recursos-seguridad', 'ClientInspeccionesController::listRecursosSeguridad');
    $routes->get('recursos-seguridad/(:num)', 'ClientInspeccionesController::viewRecursosSeguridad/$1');
    $routes->get('hv-brigadista', 'ClientInspeccionesController::listHvBrigadista');
    $routes->get('hv-brigadista/(:num)', 'ClientInspeccionesController::viewHvBrigadista/$1');
    $routes->get('plan-emergencia', 'ClientInspeccionesController::listPlanEmergencia');
    $routes->get('plan-emergencia/(:num)', 'ClientInspeccionesController::viewPlanEmergencia/$1');
    $routes->get('simulacro', 'ClientInspeccionesController::listSimulacro');
    $routes->get('simulacro/(:num)', 'ClientInspeccionesController::viewSimulacro/$1');
    // --- Programas ambientales + limpieza (Fase 4) ---
    $routes->get('limpieza-desinfeccion', 'ClientInspeccionesController::listLimpieza');
    $routes->get('limpieza-desinfeccion/(:num)', 'ClientInspeccionesController::viewLimpieza/$1');
    $routes->get('residuos-solidos', 'ClientInspeccionesController::listResiduos');
    $routes->get('residuos-solidos/(:num)', 'ClientInspeccionesController::viewResiduos/$1');
    $routes->get('control-plagas', 'ClientInspeccionesController::listPlagas');
    $routes->get('control-plagas/(:num)', 'ClientInspeccionesController::viewPlagas/$1');
    $routes->get('agua-potable', 'ClientInspeccionesController::listAguaPotable');
    $routes->get('agua-potable/(:num)', 'ClientInspeccionesController::viewAguaPotable/$1');
    $routes->get('plan-saneamiento', 'ClientInspeccionesController::listSaneamiento');
    $routes->get('plan-saneamiento/(:num)', 'ClientInspeccionesController::viewSaneamiento/$1');
    // --- Dotaciones y capacitación (Fase 4 cont.) ---
    $routes->get('dotacion-vigilante', 'ClientInspeccionesController::listDotacionVigilante');
    $routes->get('dotacion-vigilante/(:num)', 'ClientInspeccionesController::viewDotacionVigilante/$1');
    $routes->get('dotacion-aseadora', 'ClientInspeccionesController::listDotacionAseadora');
    $routes->get('dotacion-aseadora/(:num)', 'ClientInspeccionesController::viewDotacionAseadora/$1');
    $routes->get('dotacion-todero', 'ClientInspeccionesController::listDotacionTodero');
    $routes->get('dotacion-todero/(:num)', 'ClientInspeccionesController::viewDotacionTodero/$1');
    $routes->get('auditoria-zona-residuos', 'ClientInspeccionesController::listAuditoriaResiduos');
    $routes->get('auditoria-zona-residuos/(:num)', 'ClientInspeccionesController::viewAuditoriaResiduos/$1');
    $routes->get('asistencia-induccion', 'ClientInspeccionesController::listAsistenciaInduccion');
    $routes->get('asistencia-induccion/(:num)', 'ClientInspeccionesController::viewAsistenciaInduccion/$1');
    $routes->get('reporte-capacitacion', 'ClientInspeccionesController::listReporteCapacitacion');
    $routes->get('reporte-capacitacion/(:num)', 'ClientInspeccionesController::viewReporteCapacitacion/$1');
    $routes->get('preparacion-simulacro', 'ClientInspeccionesController::listPreparacionSimulacro');
    $routes->get('preparacion-simulacro/(:num)', 'ClientInspeccionesController::viewPreparacionSimulacro/$1');
    // --- KPIs (Fase 5) ---
    $routes->get('kpi-limpieza', 'ClientInspeccionesController::listKpiLimpieza');
    $routes->get('kpi-limpieza/(:num)', 'ClientInspeccionesController::viewKpiLimpieza/$1');
    $routes->get('kpi-residuos', 'ClientInspeccionesController::listKpiResiduos');
    $routes->get('kpi-residuos/(:num)', 'ClientInspeccionesController::viewKpiResiduos/$1');
    $routes->get('kpi-plagas', 'ClientInspeccionesController::listKpiPlagas');
    $routes->get('kpi-plagas/(:num)', 'ClientInspeccionesController::viewKpiPlagas/$1');
    $routes->get('kpi-agua-potable', 'ClientInspeccionesController::listKpiAguaPotable');
    $routes->get('kpi-agua-potable/(:num)', 'ClientInspeccionesController::viewKpiAguaPotable/$1');
});
```

### Patrón de URLs

```
/client/inspecciones                    → Hub (dashboard)
/client/inspecciones/{tipo}             → Lista por tipo
/client/inspecciones/{tipo}/{id}        → Vista detalle
```

Solo rutas GET. No hay POST, PUT ni DELETE — el cliente es read-only.

### Rutas completas actuales (62 rutas GET)

Las rutas actualizadas están documentadas en el bloque de arriba (Paso 6). Resumen por fase:

- **Fase 1-3** (Inspecciones operativas): 15 módulos → 29 rutas (13 list+view + 2 solo-lista)
- **Fase 4** (Programas + Dotaciones + Capacitación): 12 módulos → 24 rutas
- **Fase 5** (KPIs): 4 módulos → 8 rutas
- **Total**: 31 módulos, 62 rutas GET + 1 dashboard = 63 endpoints

---

## Paso 7: Agregar Botón en Dashboard Cliente

**Archivo:** `app/Views/client/dashboard.php`

Agregar un botón en la sección "Quick Access":

```html
<div class="col-lg-3 col-md-6 mb-3">
    <a href="<?= base_url('client/inspecciones') ?>" class="btn w-100"
       style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none;">
        <i class="fas fa-clipboard-check me-2"></i> Inspecciones
    </a>
</div>
```

Sin `target="_blank"` — se abre en la misma pestaña porque es parte del sistema.

---

## Seguridad — Checklist

- [ ] `getClientId()` verifica `role === 'client'` en TODOS los métodos
- [ ] Cada vista detalle valida `id_cliente` de la inspección vs `session('user_id')`
- [ ] Solo se muestran inspecciones con `estado = 'completo'`
- [ ] No hay botones de editar, eliminar ni crear
- [ ] Las rutas usan `filter => 'auth'` para requerir sesión activa
- [ ] Los PDFs se acceden por la ruta del consultor (`/inspecciones/{tipo}/pdf/{id}`) — si necesitas restringir, agregar validación de `id_cliente` también en el endpoint PDF

---

## Para Replicar en Otro Aplicativo

### Lo que necesitas adaptar

1. **Nombres de tablas y modelos** — cambiar según tu esquema
2. **Campos de cada inspección** — las vistas de detalle dependen de la estructura de datos
3. **Tema visual del layout** — cambiar colores, logos, navbar según el aplicativo gemelo
4. **Tipos de inspección** — agregar o quitar tipos en el array `$tipos` del dashboard y crear sus vistas
5. **Campo de fecha** — puede ser `fecha_visita`, `fecha_inspeccion`, etc.
6. **Rutas de fotos** — depende de dónde se almacenan los uploads

### Lo que se mantiene igual (patrón)

1. Controller único con `getClientId()` centralizado
2. Patrón layout → content con vista pre-renderizada
3. Vista lista reutilizable con variables dinámicas
4. Modal de fotos con `openPhoto()`
5. Estructura de rutas: `group → dashboard / list / view`
6. Validación de propiedad en cada vista detalle
7. Solo mostrar `estado = 'completo'`

---

## Flujo Completo del Usuario

```
Login como cliente
    ↓
Dashboard principal (/dashboard)
    ↓
Click botón "Inspecciones"
    ↓
Hub inspecciones (/client/inspecciones) — 31 cards
  │ --- Inspecciones operativas ---
  ├── Card "Actas de Visita (5)"
  ├── Card "Locativas (3)"
  ├── Card "Señalización (2)"
  ├── Card "Botiquín (1)"
  ├── Card "Extintores (4)"
  ├── Card "Comunicaciones (2)"
  ├── Card "Gabinetes (3)"
  ├── Card "Cartas de Vigía (4)"
  ├── Card "Mantenimientos (8)"
  ├── Card "Matriz Vulnerabilidad (1)"
  ├── Card "Probabilidad Peligros (2)"
  ├── Card "Recursos Seguridad (1)"
  ├── Card "HV Brigadista (3)"
  ├── Card "Plan de Emergencia (1)"
  ├── Card "Simulacro (2)"
  │ --- Programas ambientales ---
  ├── Card "Limpieza y Desinfección (2)"
  ├── Card "Residuos Sólidos (1)"
  ├── Card "Control Plagas (1)"
  ├── Card "Agua Potable (1)"
  ├── Card "Plan Saneamiento (1)"
  │ --- Dotaciones y capacitación ---
  ├── Card "Dotación Vigilante (4)"
  ├── Card "Dotación Aseadora (3)"
  ├── Card "Dotación Todero (2)"
  ├── Card "Auditoría Zona Residuos (1)"
  ├── Card "Asistencia Inducción (5)"
  ├── Card "Reportes Capacitación (3)"
  ├── Card "Preparación Simulacro (1)"
  │ --- KPIs ---
  ├── Card "KPI Limpieza (2)"
  ├── Card "KPI Residuos (1)"
  ├── Card "KPI Plagas (1)"
  └── Card "KPI Agua Potable (1)"
    ↓
Click en tipo
    ↓
Lista de inspecciones completadas (/client/inspecciones/{tipo})
  ├── Card #1 - 20/02/2026
  ├── Card #2 - 15/02/2026
  └── Card #3 - 01/02/2026
    ↓
Click en inspección
    ↓
Vista detalle read-only (/client/inspecciones/{tipo}/{id})
  ├── Datos generales
  ├── Contenido específico (hallazgos/temas/items)
  ├── Fotos ampliables
  └── Botón "Descargar PDF"
```
