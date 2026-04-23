# Plan — Fase 3: Inspecciones de Zonas Complementarias

**Fecha:** 2026-04-23
**Responsable:** Consultor SST (usuario) + Claude
**Repo:** `c:/xampp/htdocs/enterprisesstph` rama `cycloid`
**Producción:** https://phorizontal.cycloidtalent.com

## 1. Alcance

Construir 3 módulos de inspección nuevos, reusando el patrón consolidado en piscinas/piscinero/procedimiento-emergencia-area:

| # | Módulo | Código | Ruta | Tipo |
|---|--------|--------|------|------|
| 1 | Gimnasio | FT-SST-250 | `/inspecciones/gimnasio` | PLANO con dotación |
| 2 | Baño Turco + Sauna | FT-SST-249 | `/inspecciones/turco-sauna` | HÍBRIDO (flags aplica_turco / aplica_sauna) |
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

Botón que abre `/inspecciones/procedimiento-emergencia-area/nuevo?id_cliente=X&area=Y`. Los valores `area` ya soportados en el ENUM:
- Gimnasio → `area=GYM`
- Turco → `area=BAÑO_TURCO`
- Sauna → `area=SAUNA`
- BBQ → `area=ZONA_BBQ`

Cuando es turco+sauna, mostramos 2 botones si ambos aplican.

### 3.5. Dashboard

Nueva sección "**Zonas complementarias**" en `app/Views/inspecciones/dashboard.php`, con tarjetas:
- FT-SST-249 Turco + Sauna (icono 🧖)
- FT-SST-250 Gimnasio (icono 🏋️)
- FT-SST-251 Zona BBQ (icono 🔥)

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

## 4. Módulo 1 — Gimnasio (FT-SST-250)

### 4.1. Tablas

```sql
CREATE TABLE tbl_inspeccion_gimnasio (
  -- columnas comunes (ver 3.2) +
  aforo_maximo INT NULL,
  horario_operacion VARCHAR(100) NULL,
  tiene_instructor TINYINT(1) DEFAULT 0,
  tiene_botiquin TINYINT(1) DEFAULT 0,
  tiene_extintor TINYINT(1) DEFAULT 0,
  tiene_plano_evacuacion TINYINT(1) DEFAULT 0,
  tiene_ventilacion_mecanica TINYINT(1) DEFAULT 0,
  tiene_reglamento_visible TINYINT(1) DEFAULT 0,
  piso_antideslizante TINYINT(1) DEFAULT 0,
  ultima_fumigacion DATE NULL,
  ...
);

CREATE TABLE tbl_gimnasio_equipo (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_inspeccion INT NOT NULL,
  tipo_equipo ENUM('CARDIO','PESO_LIBRE','MAQUINA_GUIADA','FUNCIONAL','OTRO') NOT NULL,
  descripcion VARCHAR(200) NOT NULL,
  estado ENUM('operativo','dañado','requiere_mant') NOT NULL,
  fecha_ultimo_mant DATE NULL,
  observaciones TEXT NULL,
  orden INT NOT NULL DEFAULT 0,
  FOREIGN KEY (id_inspeccion) REFERENCES tbl_inspeccion_gimnasio(id) ON DELETE CASCADE
);

CREATE TABLE tbl_gimnasio_evidencia_maestro ( ... );  -- 6 slots
CREATE TABLE tbl_gimnasio_detalle_evidencia ( ... );
```

### 4.2. Checklist de riesgos (Gimnasio)

| Código | Descripción | Fundamento |
|--------|-------------|------------|
| GYM-01 | Aforo máximo señalizado y respetado | Reglamento interno (Ley 675) |
| GYM-02 | Reglamento de uso visible | Ley 675 + Criterio SST |
| GYM-03 | Piso antideslizante / amortiguado | Res 2400/1979 art 205 + NTC 1700 |
| GYM-04 | Ventilación natural o mecánica adecuada | Res 2400/1979 art 63 |
| GYM-05 | Iluminación ≥ 300 lux en zona de ejercicio | Res 2400/1979 art 79 |
| GYM-06 | Extintor multipropósito ABC vigente y señalizado | Decreto 1072/2015 + NTC 1700 |
| GYM-07 | Botiquín primeros auxilios visible y dotado | Decreto 1072/2015 |
| GYM-08 | Plano de evacuación visible | NFPA 101 + NTC 1700 |
| GYM-09 | Equipos cardiovasculares con mantenimiento vigente | Criterio SST + NTC EN 957 |
| GYM-10 | Anclajes y tornillería de equipos de peso libre revisados | Criterio SST + NTC EN 957 |
| GYM-11 | Espejos instalados con seguridad (no bordes vivos) | Res 2400/1979 + Criterio SST |
| GYM-12 | Punto de hidratación disponible | Res 2400/1979 art 44 |
| GYM-13 | Casilleros/vestier limpios y con orden | Decreto 1072/2015 + Ley 9/1979 |
| GYM-14 | Salida de emergencia libre de obstrucciones | NFPA 101 |
| GYM-15 | Pulsador de emergencia / intercom funcional | Criterio SST |

### 4.3. Categorías de evidencia
- `aforo` (señal capacidad)
- `reglamento`
- `extintor_botiquin`
- `equipo_cardio`
- `equipo_peso`
- `hallazgo` (para deficiencias)
- `plano_evacuacion`
- `ventilacion`
- `general`

## 5. Módulo 2 — Turco + Sauna (FT-SST-249)

### 5.1. Tablas

```sql
CREATE TABLE tbl_inspeccion_turco_sauna (
  -- columnas comunes (3.2) +
  aplica_turco TINYINT(1) DEFAULT 0,
  aplica_sauna TINYINT(1) DEFAULT 0,
  aforo_maximo_turco INT NULL,
  aforo_maximo_sauna INT NULL,
  horario_operacion VARCHAR(100) NULL,
  tiene_reglamento_visible TINYINT(1) DEFAULT 0,
  reglamento_prohibe_menores TINYINT(1) DEFAULT 0,
  tiene_cronometro TINYINT(1) DEFAULT 0,
  tiene_timbre_emergencia TINYINT(1) DEFAULT 0,
  punto_hidratacion TINYINT(1) DEFAULT 0,
  CONSTRAINT chk_aplica CHECK (aplica_turco = 1 OR aplica_sauna = 1),
  ...
);

CREATE TABLE tbl_turco_sauna_detalle (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_inspeccion INT NOT NULL,
  recinto ENUM('TURCO','SAUNA') NOT NULL,
  material_interno VARCHAR(100) NULL,       -- cerámica, madera aislada, etc.
  fuente_calor VARCHAR(100) NULL,           -- generador vapor, hornillo piedras, eléctrico
  temperatura_operacion VARCHAR(50) NULL,   -- rango °C
  sistema_ventilacion VARCHAR(100) NULL,
  piso_antideslizante TINYINT(1) DEFAULT 0,
  iluminacion_adecuada TINYINT(1) DEFAULT 0,
  puerta_abre_hacia_fuera TINYINT(1) DEFAULT 0,
  puerta_polarizada_visible_exterior TINYINT(1) DEFAULT 0,
  aislamiento_electrico_ok TINYINT(1) DEFAULT 0,
  control_temp_protegido TINYINT(1) DEFAULT 0,
  observaciones TEXT NULL,
  orden INT NOT NULL DEFAULT 0,
  FOREIGN KEY (id_inspeccion) REFERENCES tbl_inspeccion_turco_sauna(id) ON DELETE CASCADE
);

CREATE TABLE tbl_turco_sauna_evidencia_maestro ( ... );
CREATE TABLE tbl_turco_sauna_detalle_evidencia ( ... );
```

### 5.2. Comportamiento de aplica_turco / aplica_sauna
- Al menos uno debe estar marcado (CHECK en BD + validación server + JS).
- Si `aplica_turco=0`, la sección turco se colapsa, sus inputs se convierten en no-required, no se crea fila en `tbl_turco_sauna_detalle`.
- El PDF solo imprime la sección del recinto que aplica.

### 5.3. Checklist de riesgos (Turco + Sauna)

| Código | Descripción | Fundamento | Aplica |
|--------|-------------|------------|--------|
| TS-01 | Reglamento de uso visible (prohibe menores sin acompañante, mayores 18a, tiempo máximo 15–20 min) | Ley 675 + Criterio SST | AMBOS |
| TS-02 | Aforo máximo señalizado | Ley 675 + Reglamento interno | AMBOS |
| TS-03 | Timbre/pulsador de emergencia funcional comunicado con recepción | Criterio SST + NFPA 101 | AMBOS |
| TS-04 | Punto de hidratación cercano | Criterio SST | AMBOS |
| TS-05 | Control de temperatura protegido contra intervención | Criterio SST | AMBOS |
| TS-06 | Puerta abre hacia afuera y con visualización exterior | NFPA 101 + Criterio SST | AMBOS |
| TS-07 | Piso antideslizante (interior y salida) | Res 2400/1979 + Criterio SST | AMBOS |
| TS-08 | Iluminación adecuada y protegida para alta humedad | Res 2400/1979 + RETIE | AMBOS |
| TS-09 | Sistema de ventilación / rendijas | Ley 9/1979 + Criterio SST | AMBOS |
| TS-10 | Desagüe funcional en piso | Ley 9/1979 + Criterio SST | TURCO |
| TS-11 | Generador de vapor con mantenimiento vigente | NTC 2505 (si gas) + Criterio SST | TURCO |
| TS-12 | Hornillo/piedras aislado del área de asiento | Criterio SST (manual fabricante) | SAUNA |
| TS-13 | Madera interna sin daños, sin tornillos expuestos | Criterio SST | SAUNA |
| TS-14 | Prohibición de aceites/productos inflamables visible | Criterio SST | SAUNA |
| TS-15 | Alarma de humo en área adyacente | NFPA 72 + Ley 1523/2012 | AMBOS |
| TS-16 | Cronómetro visible para control de tiempo de exposición | Criterio SST | AMBOS |

### 5.4. Categorías de evidencia
- `turco_interior`, `turco_desague`, `turco_generador`
- `sauna_interior`, `sauna_hornillo`, `sauna_puerta`
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

- [ ] Usuario confirma este plan.
- [ ] Usuario confirma que el ENUM `area` en `tbl_procedimiento_emergencia_area` tiene: `PISCINA, BAÑO_TURCO, SAUNA, GYM, ZONA_BBQ`.
- [ ] Usuario confirma que no hay otro módulo FT-SST-249/250/251 en pipeline.
- [ ] Inicio módulo 1 (Gimnasio).

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
