# PROMPT: Limpiar inconsistencias en tbl_reporte (fechas y títulos)

Copia y pega esto en un nuevo chat de Claude Code:

---

## Contexto

El 2026-03-25 se perdieron todos los archivos de uploads de producción por un `git clean -fd`. Se recuperaron 2,956 registros desde Google Takeout + regeneración de PDFs. Pero hay inconsistencias:

Lee `docs/INCIDENTE-PERDIDA-UPLOADS-20260325.md` para el contexto completo.

**NO preguntes nada. Diagnostica, propón y ejecuta.**

## Problemas detectados

### 1. Fechas incorrectas
Muchos registros tienen `created_at = 2026-03-26` o `2026-03-27` (fecha de la recarga) en vez de la fecha real del documento. Ejemplo:

- Registro 8949: `INFORME DE CIERRE DE MES // LA ALEGRIA 4 // DIC 2023` tiene `created_at = 2026-03-27 03:16:45` pero debería ser `2023-12-15`
- La fecha real está en el `titulo_reporte` en muchos casos

**Formatos de fecha en títulos que hay que parsear:**
- `___dd/mm/yyyy` (AppSheets: `ACTA DE VISITA__CONJUNTO___20/02/2026`)
- `dd/mm/yyyy` suelto en el título
- `dd_mm_yyyy` con guión bajo
- `MES YYYY` o `MES-YYYY` (español: `JUNIO 2024`, `NOVIEMBRE-2025`)
- `MES YYYY` (inglés: `Jan-2026`, `Feb-2026`)
- `// MES YYYY` (formato viejo: `// DIC 2023`, `// MARZO 2025`)
- `YYYY-MM-DD` al final del título (formato ISO de regeneración)
- `dd/mm/yyyy` (formato mes/día invertido como `10/22/2025` que es 22 octubre)

**Regla:** Solo corregir registros con `DATE(created_at) >= '2026-03-26'`. Los demás ya tienen fecha correcta.

### 2. Títulos con formatos inconsistentes
Los títulos vienen de diferentes fuentes y tienen formatos mixtos:
- `ACTA DE VISITA__CONJUNTO RESIDENCIAL X___20/02/2026` (formato AppSheets con `__` y `___`)
- `INFORME DE CIERRE DE MES // LA ALEGRIA 4 // DIC 2023` (formato viejo con `//`)
- `ACTA DE VISITA - CONJUNTO RESIDENCIAL X - 2026-03-13` (formato regeneración)
- `REPORTE_DE_CAPACITACIÓN_PRATUM_22/04/2025` (guión bajo como separador)

**Limpiar:**
- Reemplazar `__` por ` - ` (doble guión bajo → separador limpio)
- Reemplazar `___` y todo lo que siga por nada (quitar la fecha del título)
- Reemplazar `//` por `-`
- Reemplazar `_` por ` ` cuando es separador de palabras (NO en fechas)
- Quitar `_Per User Settings[]` que aparece en algunos
- Quitar `Audit Log - ` del inicio si existe

### 3. Archivos no-PDF registrados como reportes
Hay **600 registros** cuyo enlace apunta a archivos `.html`, `.png`, `.jpg` o `.docx`:
- Los `.html` son emails del Takeout que se subieron como archivo (no son documentos reales)
- Los `.png` son logos inline de los emails
- Los `.docx` son documentos Word (estos SÍ pueden ser válidos)

**Acción:**
- Eliminar registros que apunten a `.html` o `.png` (no son documentos)
- Conservar `.docx`, `.jpg` y `.jpeg` (pueden ser documentos escaneados o plantillas)
- Contar cuántos se eliminan antes de ejecutar

### 4. Tipos de reporte (id_report_type) potencialmente incorrectos
El script de emparejamiento huérfano asignó tipos basándose en el subject del email con match parcial. Algunos pueden estar mal clasificados. Los tipos válidos son:

```
1   Inspecciones
2   Reportes
3   Proveedor Aseo
4   Proveedor Vigilancia
5   Gestión Ambiental
6   Actas de Visita
7   Soportes de capacitaciones
8   Soportes curso de 50 horas
9   Reporte Estándares mínimos
10  Reportes de cierre de mes
11  Plan de Emergencias, Simulacros
12  Gestión de Otros Proveedores
13  Secretaria de Salud
14  Lavado de Tanques
15  Locales Comerciales
16  Fumigaciones
17  Documentación Normativa
19  Contrato y Acuerdo de Confidencialidad
20  Saneamiento Básico
21  Documentos del consultor
```

Verificar que no haya registros con `id_report_type` que no exista en esta lista.

## Tarea 1: Script SQL de diagnóstico (dry-run)

Crear `tools/diagnostico_reportlist.php` que:

1. Cuente registros con `DATE(created_at) >= '2026-03-26'` que tienen fecha extraíble del título
2. Cuente registros con enlaces a `.html` y `.png`
3. Muestre 10 ejemplos de cada problema
4. NO modifique nada — solo diagnostique

## Tarea 2: Script de corrección de fechas

Crear `tools/fix_fechas_reportlist.php` que:

1. Recorra todos los registros con `DATE(created_at) >= '2026-03-26'`
2. Extraiga la fecha real del `titulo_reporte` usando TODOS los formatos listados arriba
3. Actualice `created_at` y `updated_at` con la fecha extraída
4. Para los que no se pueda extraer fecha: dejarlos como están
5. Mostrar resumen: cuántos corregidos, cuántos sin fecha

**Meses en español para el parser:**
```
ENERO=1, FEBRERO=2, MARZO=3, ABRIL=4, MAYO=5, JUNIO=6,
JULIO=7, AGOSTO=8, SEPTIEMBRE=9, OCTUBRE=10, NOVIEMBRE=11, DICIEMBRE=12
```

**Meses abreviados (formato viejo `// DIC 2023`):**
```
ENE=1, FEB=2, MAR=3, ABR=4, MAY=5, JUN=6,
JUL=7, AGO=8, SEP=9, OCT=10, NOV=11, DIC=12
```

## Tarea 3: Script de limpieza de títulos

Crear `tools/fix_titulos_reportlist.php` que:

1. Recorra TODOS los registros de `tbl_reporte`
2. Limpie el `titulo_reporte`:
   - `___` y todo lo que siga → eliminar (es la fecha duplicada)
   - `__` → ` - `
   - `//` → `-`
   - `_Per User Settings[]` → eliminar
   - `Audit Log - ` al inicio → eliminar
   - Múltiples espacios → un espacio
   - Trim
3. Solo UPDATE si el título cambió
4. Mostrar resumen

## Tarea 4: Eliminar registros no-PDF

Crear `tools/eliminar_no_pdf.php` que:

1. Cuente y liste registros con enlace terminando en `.html` o `.png`
2. Con flag `--execute`: elimine esos registros
3. Sin flag: solo muestre lo que eliminaría (dry-run por defecto)
4. **NO eliminar** `.docx`, `.jpg`, `.jpeg` — solo `.html` y `.png`

## Base de datos

- **Host:** db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com
- **Port:** 25060
- **Database:** propiedad_horizontal
- **User:** cycloid_userdb
- **Password:** usar `getenv('DB_PROD_PASS')`
- **SSL:** Requerido — en servidor usar `/www/ca/ca-certificate_cycloid.crt`, en local no usar CA

## Ejecución

```bash
# Desde el servidor:
cd /www/wwwroot/phorizontal/enterprisesstph

# 1. Diagnóstico
DB_PROD_PASS=xxx php tools/diagnostico_reportlist.php

# 2. Corregir fechas
DB_PROD_PASS=xxx php tools/fix_fechas_reportlist.php

# 3. Limpiar títulos
DB_PROD_PASS=xxx php tools/fix_titulos_reportlist.php

# 4. Eliminar no-PDFs (primero dry-run, luego --execute)
DB_PROD_PASS=xxx php tools/eliminar_no_pdf.php
DB_PROD_PASS=xxx php tools/eliminar_no_pdf.php --execute
```

## Archivos que debes leer

1. `docs/INCIDENTE-PERDIDA-UPLOADS-20260325.md` — contexto completo
2. `app/Models/ReporteModel.php` — estructura del modelo
3. `app/Controllers/ReportController.php` — cómo se crean reportes

## NO preguntes nada. Crea los scripts, ejecuta el diagnóstico, muestra resultados, y pide confirmación solo para la ejecución destructiva (eliminar no-PDFs).

---
