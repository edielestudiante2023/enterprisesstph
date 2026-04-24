# Plan — Fase 3: Inspecciones de Zonas Complementarias

**Fecha:** 2026-04-23
**Responsable:** Consultor SST (usuario) + Claude
**Repo:** `c:/xampp/htdocs/enterprisesstph` rama `cycloid`
**Producción:** https://phorizontal.cycloidtalent.com

## 1. Alcance

Construir 3 módulos de inspección nuevos, reusando el patrón consolidado en piscinas/piscinero/procedimiento-emergencia-area:

| # | Módulo | Código | Ruta | Tipo |
|---|--------|--------|------|------|
| 1 | Gimnasio (riesgos locativos) | FT-SST-250 | `/inspecciones/gimnasio` | PLANO puro |
| 2 | Baño Turco + Sauna + Jacuzzi | FT-SST-249 | `/inspecciones/turco-sauna` | HÍBRIDO (flags aplica_turco / aplica_sauna / aplica_jacuzzi) |
| 3 | Zona BBQ | FT-SST-251 | `/inspecciones/zona-bbq` | PLANO |

**Orden de construcción:** Gimnasio → Turco+Sauna → Zona BBQ. Un deploy por módulo.

## 2. Marco normativo (investigado 2026-04-23)

**Hallazgo clave:** en Colombia NO existe norma específica para baños turcos, saunas, gimnasios de copropiedad ni zonas BBQ. Se aplican por **analogía y criterio técnico SST**:

### Normas transversales (aplican a las 3 áreas)
- **Ley 675 de 2001** — régimen de propiedad horizontal (bienes comunes, reglamento de uso).
- **Ley 9 de 1979** — Código Sanitario Nacional.
- **Resolución 2400 de 1979** — disposiciones sobre vivienda, higiene y seguridad (aplica al operario/instructor, no a los usuarios).
- **Decreto 1072 de 2015** — SG-SST para personal del servicio (si hay operario, instructor, vigilante).
- **Ley 1523 de 2012** (derogó Decreto 919/1989) — Sistema Nacional de Gestión del Riesgo de Desastres.
- **NTC 4595** (actualización 2025) — planeamiento y diseño de instalaciones y ambientes escolares (aplicable por analogía a áreas recreativas comunes).
- **NTC 1700** — higiene y seguridad, medidas de seguridad en edificaciones, medios de evacuación.
- **NFPA 101** — Life Safety Code (salidas de emergencia, rutas).

### Específicas por área

**Gimnasio (residencial copropiedad):**
- Ley 2395 de 2024 — aplica si hay prestación de servicio (instructor). En copropiedad sin instructor, no aplica.
- NTC EN 957 — equipos estacionarios de ejercicio (serie, sin traducción oficial ICONTEC).
- Reglamento interno de convivencia (obligatorio por Ley 675).

**Baño turco + sauna:**
- No hay decreto específico. Protocolo de Secretaría Distrital de Salud (Salud Capital) cita piscinas, baños turcos y saunas juntos — aplicable como guía.
- Parámetros técnicos de fabricante (Steamist, Amerec, Tylo): temperatura turco 43–46°C humedad 100%, sauna 80–95°C humedad 10–20%, sesiones 15–20 min máx, intervalos de hidratación.

**Zona BBQ:**
- NTC 2505 — instalaciones para suministro de gas.
- NFPA 58 — código del gas licuado de petróleo (GLP).
- Reglamento técnico del sector GLP (MinEnergía).
- Reglamento interno de convivencia (horario, reserva, supervisión infantil).

> **Regla del consultor:** si al hacer el checklist no hay base normativa directa, lo marcamos como "Criterio SST" o "NFPA" en la columna `fundamento_legal` de la tabla de detalle, no como "Resolución fantasma".

## 3. Arquitectura común

### 3.1. Patrón de archivos (por módulo)

```
app/Controllers/Inspeccion<Modulo>Controller.php
app/Models/Inspeccion<Modulo>Model.php
app/Models/<Modulo>DetalleModel.php            (si hay tabla detalle)
app/Models/<Modulo>EvidenciaMaestroModel.php   (catálogo 6 slots)
app/Models/<Modulo>DetalleEvidenciaModel.php   (evidencias subidas)
app/Views/inspecciones/<slug>/index.php        (listado)
app/Views/inspecciones/<slug>/form.php         (edición)
app/Views/inspecciones/<slug>/pdf.php          (DOMPDF)
app/SQL/migrate_inspeccion_<slug>.php          (migración idempotente)
app/Config/Routes.php                          (agregar bloque de rutas)
```

### 3.2. Tabla maestro — columnas comunes

Todas las tablas maestro tienen:
```sql
id INT AUTO_INCREMENT PRIMARY KEY,
id_cliente INT NOT NULL,
id_consultor INT NOT NULL,
fecha_inspeccion DATE NOT NULL,
estado ENUM('borrador','completo') DEFAULT 'borrador',
introduccion TEXT NULL,
alcance TEXT NULL,
justificacion TEXT NULL,
observaciones_generales TEXT NULL,
recomendaciones_generales TEXT NULL,
marco_normativo TEXT NULL,    -- congelado al finalizar
ruta_pdf VARCHAR(500) NULL,
created_at TIMESTAMP NULL,
updated_at TIMESTAMP NULL,
FOREIGN KEY (id_cliente) REFERENCES tbl_clientes(id_cliente)
```

### 3.3. Evidencias

**6 slots fijos** (patrón piscinas), inputs:
- `item_evidencia_<i>[]`
- `item_evidencia_categoria_<i>[]`
- `item_evidencia_descripcion_<i>[]`

Catálogo de categorías específico por módulo (ver sección por módulo).

### 3.4. Integración con procedimiento-emergencia-area

En la vista `form.php` de cada módulo, bloque al final:

```
[🚨 Crear procedimiento de emergencia para esta área]
```

Botón que abre `/inspecciones/procedimiento-emergencia-area/nuevo?id_cliente=X&area=Y`. Los valores `area` actuales del ENUM (verificado en `app/SQL/migrate_procedimiento_emergencia_area.php:74` — **sin Ñ**):
- Gimnasio → `area=GYM`
- Turco → `area=BANO_TURCO`
- Sauna → `area=SAUNA`
- BBQ → `area=ZONA_BBQ`

**⚠ Cambio pendiente**: agregar `JACUZZI` como 6° valor del ENUM. Script independiente `app/SQL/migrate_enum_area_add_jacuzzi.php` antes de construir el módulo 2.

Cuando turco+sauna+jacuzzi, mostramos 1 botón por recinto que aplique (máximo 3 botones).

### 3.5. Dashboard

Nueva sección "**Zonas complementarias**" en `app/Views/inspecciones/dashboard.php`, con tarjetas:
- FT-SST-249 Turco + Sauna + Jacuzzi
- FT-SST-250 Gimnasio
- FT-SST-251 Zona BBQ

La sección se crea al construir el módulo 1 (con tarjetas "próximamente" para los módulos 2 y 3 que se habilitan al desplegarse).

Color de sección sugerido: naranja/amarillo para diferenciarlas de piscinas (azul).

### 3.6. Librerías reutilizables a crear

- `app/Libraries/ChecklistCatalogos.php` — devuelve checklists estandarizados por área (método `forGimnasio()`, `forTurco()`, `forSauna()`, `forBbq()`). Cada item: `{codigo, descripcion, fundamento_legal, nivel_riesgo}`.
- Reuso de `ImagenCompresionTrait`, `AutosaveJsonTrait`, `PreventDuplicateBorradorTrait`, `InspeccionesTransactionalTrait`.

### 3.7. Convenciones confirmadas
- **File inputs SIN `capture="environment"`** (permitir cámara o galería).
- Select2 clientes vía `/inspecciones/api/clientes`.
- `initAutosave(...)` con `data-multi-name="1"` en inputs multi-foto.
- DOMPDF 3.0.0, `@page margin` en `px` (NO `cm`), `isRemoteEnabled(true)`, `isHtml5ParserEnabled(true)`.
- Logos cliente: `FCPATH . 'uploads/' . $cliente['logo']` convertidos a base64 con `file_get_contents` + `mime_content_type`.

## 4. Módulo 1 — Gimnasio (FT-SST-250) — SOLO RIESGOS LOCATIVOS

**Decisión del usuario (2026-04-23)**: este módulo se reduce a riesgos locativos/infraestructura. NO captura dotación EPP del instructor, NO gestiona mantenimiento de equipos. Si mañana se necesita capturar equipos, va aparte en un nuevo módulo.

### 4.1. Tablas (3 tablas — sin detalle de equipos)

```sql
CREATE TABLE tbl_inspeccion_gimnasio (
  -- columnas comunes (ver 3.2) +
  aforo_maximo INT NULL,
  horario_operacion VARCHAR(100) NULL,
  tiene_botiquin TINYINT(1) DEFAULT 0,
  tiene_extintor TINYINT(1) DEFAULT 0,
  tiene_plano_evacuacion TINYINT(1) DEFAULT 0,
  tiene_ventilacion_mecanica TINYINT(1) DEFAULT 0,
  tiene_reglamento_visible TINYINT(1) DEFAULT 0,
  piso_antideslizante TINYINT(1) DEFAULT 0,
  tiene_punto_hidratacion TINYINT(1) DEFAULT 0,
  tiene_pulsador_emergencia TINYINT(1) DEFAULT 0,
  espejos_seguros TINYINT(1) DEFAULT 0,
  vestier_ordenado TINYINT(1) DEFAULT 0,
  salida_emergencia_libre TINYINT(1) DEFAULT 0,
  iluminacion_adecuada TINYINT(1) DEFAULT 0,
  -- checklist locativa + observaciones
);

CREATE TABLE tbl_gimnasio_evidencia_maestro ( ... );  -- 6 slots fijos
CREATE TABLE tbl_gimnasio_detalle_evidencia ( ... );
```

### 4.2. Checklist de riesgos LOCATIVOS (Gimnasio)

| Código | Descripción | Fundamento |
|--------|-------------|------------|
| GYM-01 | Aforo máximo señalizado | Ley 675 + Reglamento interno |
| GYM-02 | Reglamento de uso visible | Ley 675 + Criterio SST |
| GYM-03 | Piso antideslizante / amortiguado | Res 2400/1979 art 205 + NTC 1700 |
| GYM-04 | Ventilación natural o mecánica adecuada | Res 2400/1979 art 63 |
| GYM-05 | Iluminación ≥ 300 lux en zona de ejercicio | Res 2400/1979 art 79 |
| GYM-06 | Extintor multipropósito ABC vigente y señalizado | Decreto 1072/2015 + NTC 1700 |
| GYM-07 | Botiquín primeros auxilios visible y dotado | Decreto 1072/2015 |
| GYM-08 | Plano de evacuación visible | NFPA 101 + NTC 1700 |
| GYM-09 | Espejos instalados con seguridad (no bordes vivos, anclados) | Res 2400/1979 + Criterio SST |
| GYM-10 | Punto de hidratación disponible | Res 2400/1979 art 44 |
| GYM-11 | Vestier limpio y con orden | Decreto 1072/2015 + Ley 9/1979 |
| GYM-12 | Salida de emergencia libre de obstrucciones | NFPA 101 |
| GYM-13 | Pulsador de emergencia / intercom funcional | Criterio SST |

### 4.3. Categorías de evidencia
- `aforo`, `reglamento`, `extintor_botiquin`, `hallazgo`, `plano_evacuacion`, `ventilacion`, `vestier`, `salida_emergencia`, `general`

## 5. Módulo 2 — Turco + Sauna + Jacuzzi (FT-SST-249)

**Decisión del usuario (2026-04-23)**: se adiciona jacuzzi como 3er recinto del módulo (criterio J1 = locativo puro, sin análisis químico de agua; ese caso seguiría usando el módulo de piscinas existente).

**Precondición**: agregar `JACUZZI` al ENUM `area` de `tbl_procedimiento_emergencia_area` mediante `app/SQL/migrate_enum_area_add_jacuzzi.php` antes de construir este módulo.

### 5.1. Tablas

```sql
CREATE TABLE tbl_inspeccion_turco_sauna (
  -- columnas comunes (3.2) +
  aplica_turco TINYINT(1) DEFAULT 0,
  aplica_sauna TINYINT(1) DEFAULT 0,
  aplica_jacuzzi TINYINT(1) DEFAULT 0,
  aforo_maximo_turco INT NULL,
  aforo_maximo_sauna INT NULL,
  aforo_maximo_jacuzzi INT NULL,
  horario_operacion VARCHAR(100) NULL,
  tiene_reglamento_visible TINYINT(1) DEFAULT 0,
  reglamento_prohibe_menores TINYINT(1) DEFAULT 0,
  tiene_cronometro TINYINT(1) DEFAULT 0,
  tiene_timbre_emergencia TINYINT(1) DEFAULT 0,
  punto_hidratacion TINYINT(1) DEFAULT 0,
  CONSTRAINT chk_aplica CHECK (aplica_turco = 1 OR aplica_sauna = 1 OR aplica_jacuzzi = 1),
  ...
);

CREATE TABLE tbl_turco_sauna_detalle (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_inspeccion INT NOT NULL,
  recinto ENUM('TURCO','SAUNA','JACUZZI') NOT NULL,

  -- Comunes a todos los recintos
  material_interno VARCHAR(100) NULL,
  fuente_calor VARCHAR(100) NULL,
  temperatura_operacion VARCHAR(50) NULL,
  sistema_ventilacion VARCHAR(100) NULL,
  piso_antideslizante TINYINT(1) DEFAULT 0,
  iluminacion_adecuada TINYINT(1) DEFAULT 0,
  aislamiento_electrico_ok TINYINT(1) DEFAULT 0,
  control_temp_protegido TINYINT(1) DEFAULT 0,

  -- Turco/Sauna específicos (pueden quedar NULL para jacuzzi)
  puerta_abre_hacia_fuera TINYINT(1) DEFAULT 0,
  puerta_polarizada_visible_exterior TINYINT(1) DEFAULT 0,

  -- Jacuzzi específicos (pueden quedar NULL para turco/sauna)
  tiene_cobertura_cuando_no_usado TINYINT(1) DEFAULT 0,
  tiene_agarraderas TINYINT(1) DEFAULT 0,
  tiene_gfci_rcd TINYINT(1) DEFAULT 0,
  profundidad_senalizada TINYINT(1) DEFAULT 0,
  desague_funcional TINYINT(1) DEFAULT 0,
  profundidad_m DECIMAL(3,2) NULL,
  temperatura_agua_c DECIMAL(4,1) NULL,

  observaciones TEXT NULL,
  orden INT NOT NULL DEFAULT 0,
  FOREIGN KEY (id_inspeccion) REFERENCES tbl_inspeccion_turco_sauna(id) ON DELETE CASCADE
);

CREATE TABLE tbl_turco_sauna_evidencia_maestro ( ... );
CREATE TABLE tbl_turco_sauna_detalle_evidencia ( ... );
```

### 5.2. Comportamiento de los flags aplica_*
- Al menos uno debe estar marcado (CHECK en BD + validación server + JS).
- Si `aplica_X=0`, la sección X se colapsa, sus inputs se convierten en no-required, no se crea fila en `tbl_turco_sauna_detalle` con `recinto=X`.
- El PDF solo imprime los recintos que aplican.
- El botón "Crear procedimiento de emergencia" se muestra 1–3 veces según recintos activos, con `area=BANO_TURCO | SAUNA | JACUZZI`.

### 5.3. Checklist de riesgos (Turco + Sauna + Jacuzzi)

| Código | Descripción | Fundamento | Aplica |
|--------|-------------|------------|--------|
| TS-01 | Reglamento de uso visible (prohibe menores sin acompañante, mayores 18a, tiempo máximo 15–20 min, prohibido bajo efectos de alcohol) | Ley 675 + Criterio SST | TODOS |
| TS-02 | Aforo máximo señalizado | Ley 675 + Reglamento interno | TODOS |
| TS-03 | Timbre/pulsador de emergencia funcional comunicado con recepción | Criterio SST + NFPA 101 | TODOS |
| TS-04 | Punto de hidratación cercano | Criterio SST | TODOS |
| TS-05 | Control de temperatura protegido contra intervención | Criterio SST | TODOS |
| TS-06 | Puerta abre hacia afuera y con visualización exterior | NFPA 101 + Criterio SST | TURCO/SAUNA |
| TS-07 | Piso antideslizante (interior y salida / deck perimetral en jacuzzi) | Res 2400/1979 + Criterio SST | TODOS |
| TS-08 | Iluminación adecuada y protegida para alta humedad | Res 2400/1979 + RETIE | TODOS |
| TS-09 | Sistema de ventilación / rendijas | Ley 9/1979 + Criterio SST | TURCO/SAUNA |
| TS-10 | Desagüe funcional en piso | Ley 9/1979 + Criterio SST | TURCO/JACUZZI |
| TS-11 | Generador de vapor con mantenimiento vigente | NTC 2505 (si gas) + Criterio SST | TURCO |
| TS-12 | Hornillo/piedras aislado del área de asiento | Criterio SST (manual fabricante) | SAUNA |
| TS-13 | Madera interna sin daños, sin tornillos expuestos | Criterio SST | SAUNA |
| TS-14 | Prohibición de aceites/productos inflamables visible | Criterio SST | SAUNA |
| TS-15 | Alarma de humo en área adyacente | NFPA 72 + Ley 1523/2012 | TURCO/SAUNA |
| TS-16 | Cronómetro visible para control de tiempo de exposición | Criterio SST | TODOS |
| TS-17 | Agarraderas/pasamanos de acceso | Criterio SST | JACUZZI |
| TS-18 | GFCI/RCD en circuito eléctrico del jacuzzi | RETIE + NFPA 70 | JACUZZI |
| TS-19 | Profundidad señalizada en borde | Criterio SST + Ley 1209 | JACUZZI |
| TS-20 | Cobertura/tapa cuando no está en uso | Criterio SST | JACUZZI |
| TS-21 | Cartel "prohibido menores sin adulto", "no usar bajo efectos alcohol" | Ley 675 + Criterio SST | JACUZZI |

### 5.4. Categorías de evidencia
- `turco_interior`, `turco_desague`, `turco_generador`
- `sauna_interior`, `sauna_hornillo`, `sauna_puerta`
- `jacuzzi_interior`, `jacuzzi_agarradera`, `jacuzzi_gfci`, `jacuzzi_cobertura`
- `reglamento`, `aforo`, `control_temp`, `punto_hidratacion`, `hallazgo`, `general`

## 6. Módulo 3 — Zona BBQ (FT-SST-251)

### 6.1. Tablas

```sql
CREATE TABLE tbl_inspeccion_zona_bbq (
  -- columnas comunes (3.2) +
  numero_asadores INT DEFAULT 1,
  tipo_combustible ENUM('GAS_LP','GAS_NATURAL','LEÑA','CARBON','ELECTRICO','MIXTO') NOT NULL,
  aforo_maximo INT NULL,
  horario_operacion VARCHAR(100) NULL,
  sistema_reserva TINYINT(1) DEFAULT 0,
  tiene_reglamento_visible TINYINT(1) DEFAULT 0,
  tiene_extintor_cercano TINYINT(1) DEFAULT 0,
  tipo_extintor VARCHAR(50) NULL,
  tiene_punto_agua TINYINT(1) DEFAULT 0,
  tiene_punto_electrico_seguro TINYINT(1) DEFAULT 0,
  distancia_a_vegetacion_m DECIMAL(4,1) NULL,
  distancia_a_vivienda_m DECIMAL(4,1) NULL,
  ventilacion_adecuada TINYINT(1) DEFAULT 0,
  supervision_menores_obligatoria TINYINT(1) DEFAULT 0,
  ...
);

CREATE TABLE tbl_zona_bbq_asador (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_inspeccion INT NOT NULL,
  numero VARCHAR(10) NOT NULL,          -- "1", "A", etc.
  estado_parrilla ENUM('operativo','dañado','requiere_mant') NOT NULL,
  estado_conexion_gas ENUM('operativo','fuga_detectada','sin_conexion','no_aplica') NULL,
  fecha_ultima_prueba_fuga DATE NULL,
  observaciones TEXT NULL,
  orden INT NOT NULL DEFAULT 0,
  FOREIGN KEY (id_inspeccion) REFERENCES tbl_inspeccion_zona_bbq(id) ON DELETE CASCADE
);

CREATE TABLE tbl_zona_bbq_evidencia_maestro ( ... );
CREATE TABLE tbl_zona_bbq_detalle_evidencia ( ... );
```

### 6.2. Checklist de riesgos (Zona BBQ)

| Código | Descripción | Fundamento |
|--------|-------------|------------|
| BBQ-01 | Reglamento de uso visible (horario, reserva, supervisión menores) | Ley 675 |
| BBQ-02 | Extintor cercano (multipropósito ABC mínimo 10 lb) vigente y señalizado | NTC 2505 + NFPA 58 + Decreto 1072/2015 |
| BBQ-03 | Asador alejado ≥1.5 m de vegetación y material combustible | NFPA 58 + Criterio SST |
| BBQ-04 | Asador alejado ≥3 m de fachadas/ventanas de vivienda | NFPA 58 + Criterio SST |
| BBQ-05 | Conexión de gas con prueba de fugas ≤ 12 meses | NTC 2505 art 7 + NFPA 58 |
| BBQ-06 | Válvula de corte de gas accesible y señalizada | NTC 2505 + NFPA 58 |
| BBQ-07 | Cilindro GLP en exterior, ventilado, lejos de fuentes de ignición | NFPA 58 + Reglamento GLP MinEnergía |
| BBQ-08 | Ventilación adecuada (no en espacio confinado) | Res 2400/1979 + NFPA 58 |
| BBQ-09 | Punto de agua accesible (manguera o llave) | Criterio SST |
| BBQ-10 | Punto eléctrico con GFCI/protección contra humedad | RETIE + Criterio SST |
| BBQ-11 | Superficie de piso no combustible debajo del asador | NFPA 58 + Criterio SST |
| BBQ-12 | Señalización: prohibido menores sin supervisión adulta | Ley 675 + Criterio SST |
| BBQ-13 | Señalización: riesgo de quemadura / no tocar superficies calientes | Criterio SST |
| BBQ-14 | Dispositivo de encendido / mecheros no dejados al alcance | Criterio SST |
| BBQ-15 | Recipiente metálico para residuos calientes (cenizas, carbón) | Criterio SST |
| BBQ-16 | Alarma de humo en zona cubierta adyacente | NFPA 72 + Ley 1523/2012 |
| BBQ-17 | Plan de emergencia específico documentado | Ley 1523/2012 |

### 6.3. Categorías de evidencia
- `asador`, `extintor`, `cilindro_gas`, `valvula_corte`, `conexion_gas`
- `punto_agua`, `punto_electrico`, `señalizacion`, `vegetacion_cercana`
- `hallazgo`, `reglamento`, `general`

## 7. Flujo de ejecución (estricto)

Para cada módulo, en este orden:

1. **Migración SQL idempotente** (`app/SQL/migrate_inspeccion_<slug>.php`):
   - Parámetro `[local|production]`.
   - `CREATE TABLE IF NOT EXISTS ... ENGINE=InnoDB`.
   - Si encuentra datos existentes en tablas a droppear, aborta y pide confirmación (`--force`).
   - Ejecutar LOCAL → verificar → PRODUCTION con `DB_PROD_PASS=xxx php ... production`.
2. **Modelos** (CodeIgniter 4, `$allowedFields`, validación básica).
3. **Controller** con traits reusables (`AutosaveJsonTrait`, `InspeccionesTransactionalTrait`, `ImagenCompresionTrait`, `PreventDuplicateBorradorTrait`).
4. **Vistas**: `index.php`, `form.php`, `pdf.php`.
5. **Rutas**: agregar bloque en `app/Config/Routes.php`.
6. **Dashboard**: tarjeta en sección "Zonas complementarias".
7. **Commit único por módulo** (con subject `feat(inspecciones): <modulo> FT-SST-XXX`).
8. **Deploy** con el flujo estricto:
   - `git add . && git status && git commit`
   - `git checkout main && git merge cycloid && git push origin main && git checkout cycloid`
   - SSH servidor: `bash deploy.sh` (NUNCA `git clean -fd`).
9. **Verificación post-deploy**: abrir módulo, crear borrador, generar PDF, integrar con procedimiento-emergencia-area.

## 8. Riesgos y mitigaciones

| Riesgo | Mitigación |
|--------|------------|
| Checklist sin base legal clara | Columna `fundamento_legal` permite "Criterio SST" — honesto con el consultor final |
| Usuario nuevo a los campos del form | Tooltips + Label claros, reusar UX de piscinas |
| PDF grande con múltiples fotos | Compresión cliente (image_compressor.js) + servidor (ImagenCompresionTrait) — patrón probado |
| Conflicto de rutas con módulos existentes | Prefijo consistente `/inspecciones/<slug>/...` — grepear antes de asignar |
| ENUM del procedimiento-emergencia-area ya soporta 5 valores | Confirmado ✓ — no tocar |

## 9. Pendientes confirmados

- [x] Usuario aprueba arquitectura (Opción C + C1) — 2026-04-23.
- [x] Usuario aprueba códigos FT-SST-249/250/251 — 2026-04-23.
- [x] Usuario aprueba orden de construcción (Gym → Turco+Sauna+Jacuzzi → BBQ) — 2026-04-23.
- [x] Usuario adiciona **jacuzzi** al módulo 2 (criterio J1 locativo puro) — 2026-04-23.
- [x] Usuario confirma gym **solo riesgos locativos** (sin tabla de equipos, sin dotación, sin mantenimientos) — 2026-04-23.
- [x] Usuario aprueba **ALTER TABLE** del ENUM `area` en local y producción — 2026-04-23.
- [x] ENUM actual verificado en `app/SQL/migrate_procedimiento_emergencia_area.php:74` (sin Ñ).
- [ ] Ejecutar módulo 1 (Gimnasio) — siguiente paso.

## 9.B. Convenciones de estilo para los PDFs (aplicar tal cual)

Durante la iteración de piscinas el usuario empujó varias veces hacia un PDF "texto-texto denso". Consolidar estos lineamientos al crear cualquier PDF nuevo (turco, sauna, gym, bbq):

### CSS base (copiar de `app/Views/inspecciones/piscinas/pdf.php`)

```css
@page { margin: 28px 28px 36px 28px; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #222; }
h1 { font-size: 16px; margin: 0 0 4px 0; color: #1c2437; }
h2 { font-size: 12px; margin: 6px 0 3px 0; color: #1c2437; border-bottom: 1px solid #bd9751; padding-bottom: 2px; }
h3 { font-size: 10.5px; margin: 4px 0 2px 0; color: #1c2437; }
```

### Reglas de layout (explícitas, no negociables)

1. **Prohibido `<div class="pagebreak"></div>` manuales.** DOMPDF corta natural donde llene la página. Saltos forzados dejan huecos grandes al final de la sección previa.
2. **Prohibido cajas anidadas con `border` + `background` por bloque** (tipo "CUANDO / QUE HACER / QUE NO HACER" con cada uno en una caja coloreada). Generan saltos irregulares. Reemplazar por **párrafos con label en bold coloreado** al inicio de cada línea:
   ```html
   <p><span style="color:#1b7e3f;font-weight:700;">QUE HACER:</span> texto...</p>
   <p><span style="color:#c0392b;font-weight:700;">QUE NO HACER:</span> texto...</p>
   ```
3. **Prohibido `<table class="dualcol">` 2-cols** para QUE HACER/QUE NO HACER lado a lado. DOMPDF calcula mal el ancho cuando el contenido es desigual → huecos.
4. **`page-break-inside: avoid` SOLO** en el contenedor del "detalle" (una piscina, una zona del gym, etc.). No en `.escenario`, no en párrafos.
5. **Hallazgo crítico / resumen ejecutivo** no debe ser caja con fondo coloreado — usar línea horizontal con border-bottom color + texto grande. Ejemplo:
   ```css
   .hallazgo-critico { color: <?= $peorColor ?>; padding: 2px 0; margin: 4px 0 8px 0;
       border-bottom: 2px solid <?= $peorColor ?>; }
   .hallazgo-critico .hc-label { font-size: 10px; font-weight: 600; color: #555; }
   .hallazgo-critico .hc-value { font-size: 14px; font-weight: 700; }
   ```
6. **Introducción compacta**: párrafos con `margin: 0 0 3px 0` (no el `<p>` default de 16px). 4 párrafos densos. `font-size: 9.5px; line-height: 1.35;`.
7. **Tablas de datos** (`.kv`): `padding: 1px 3px; font-size: 9.5px;` para datos clave-valor.
8. **Evidencias fotográficas**: aplanar todas las fotos en un **grid 3-col** usando `<table><tr><td>...</td></tr></table>`, no un bloque por categoría. Cada celda: label bold 8.5px + img + descripción opcional 8px gris.
9. **Marco normativo**: sí usar tablas (6.1 jerarquía + 6.2 artículos evaluados) — aquí las tablas están justificadas porque es información estructurada. Reusar el patrón exacto de `piscinas/pdf.php`.

### Validación de normas citadas (ya implementado en piscinas, replicar)

Si el PDF muestra una norma citada por un ensayo de laboratorio u otro elemento, clasificarla y mostrar warning si aplica:

```php
function clasificarNorma(?string $norma): string {
    if (empty($norma)) return '';
    $n = strtolower($norma);
    if (preg_match('/\b234[^\d]*2026\b/', $n)) return 'vigente';
    if (preg_match('/\b1618[^\d]*2010\b/', $n)) return 'derogada';
    if (preg_match('/\b780[^\d]*2016\b/', $n)) return 'otra_legal';
    if (preg_match('/\bley\s*9[^\d]*1979\b/', $n)) return 'otra_legal';
    if (preg_match('/\bres(?:oluci[oó]n)?[^\d]*\d{3,4}/i', $norma)) return 'sospechosa';
    return 'otra_legal';
}
```

Si es `derogada` → `⚠ Derogada. [Norma vigente] es la actual.`
Si es `sospechosa` → `⚠ Norma no reconocida — verificar con el laboratorio.`

### Prompts IA con whitelist de normas

El patrón de `extraerEnsayoDesdePDF` en `EmergencyProcedureIAService.php` incluye una sección "NORMAS VALIDAS EN ESTE DOMINIO — lista blanca" para reducir alucinaciones. Replicar este patrón en cualquier nuevo prompt que extraiga referencias normativas:

- Listar explícitamente las normas aceptables.
- Listar las normas con números parecidos que NO aplican (para que Haiku las descarte).
- Instruir: "si no está en la lista blanca, devuelve vacío y anota OCR ambiguo en observaciones".

### Encabezados con código FT-SST

Tabla 3-col con logo cliente a la izquierda rowspan=2, sistema + código a la derecha, formato + fecha abajo. Copiar exacto de `piscinas/pdf.php` cambiando el número.

```html
<table class="header-corp">
    <tr>
        <td class="header-logo" rowspan="2"><img src="<?= $logoBase64 ?>"></td>
        <td class="header-sist">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</td>
        <td class="header-code">Codigo: FT-SST-###<br>Version: 001</td>
    </tr>
    <tr>
        <td class="header-sist" style="font-size:10px;">FORMATO DE INSPECCION DE {AREA}</td>
        <td class="header-code">Fecha: <?= date('d/m/Y', ...) ?></td>
    </tr>
</table>
```

### Códigos FT-SST asignados en Fase 3

- FT-SST-249 — Inspección Baño Turco / Sauna
- FT-SST-250 — Inspección Gimnasio
- FT-SST-251 — Inspección Zona BBQ

(Versión 001 para los 3 nuevos.)

## 10. Fuentes consultadas

- [Resolución 2400 de 1979 (MinVivienda)](https://minvivienda.gov.co/sites/default/files/normativa/2400%20-%201979.pdf)
- [Seguridad en propiedad horizontal 2026 (G4S)](https://www.g4s.com/es-co/insightsroom/2026/03/27/1)
- [NTC 4595:2025 (MinEducación)](https://www.mineducacion.gov.co/1780/articles-355996_recurso_14.pdf)
- [Ley 2395 de 2024 (Alcaldía Bogotá)](https://www.alcaldiabogota.gov.co/sisjur/normas/Norma1.jsp?i=160361)
- [Ley 675 de 2001 (Alcaldía Bogotá)](https://www.alcaldiabogota.gov.co/sisjur/normas/Norma1.jsp?i=4162)
- [Gimnasios en copropiedad — Metrocuadrado](https://www.metrocuadrado.com/noticias/guia-de-propiedad-horizontal/el-uso-del-gimnasio-puede-reglamentarse-755)
- [Reglamento sauna/turco — AyR](https://ayrcopropiedades.com/wp-content/uploads/2020/09/REGLAMENTO-SAUNA.pdf)
- [Parámetros vapor/sauna — H2OTek](https://h2otek.com/tienda/normativas-y-requisitos-en-uso-de-bano-de-vapor-o-sauna-humeda-para-seguridad-de-las-personas/)
- [Protocolo Piscinas, Baños Turcos y Saunas — Salud Capital Bogotá](https://www.saludcapital.gov.co/sitios/VigilanciaSaludPublica/Protocolos%20de%20Vigilancia%20en%20Salud%20Publica/Piscinas%20Ba%C3%B1os%20Turcos%20y%20Saunas.pdf)
- [NTC 2505 (UGC)](https://www.ugc.edu.co/pages/juridica/documentos/institucionales/NTC_2505_Instalaciones_Suministro_De_Gas.pdf)
- [Reglamento técnico sector GLP (MinEnergía)](https://www.minenergia.gov.co/documents/2610/REQUISITOS_REGLAMENTACI%C3%93N_T%C3%89CNICA_SECTOR_GLP_-_TEMAS_VARIOS.pdf)
- [NFPA 58 GLP (NFPA)](https://www.nfpa.org/product/nfpa-58-liquefied-petroleum-gas-code/p0058code)
