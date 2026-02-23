# 09 - Diseno del PDF Acta de Visita (DOMPDF)

## Estado actual: REQUIERE MEJORAS

El PDF generado actualmente tiene problemas visuales que lo hacen ver poco profesional. Este documento define el diseno objetivo y las restricciones tecnicas de DOMPDF.

---

## Problemas detectados (v1 actual)

### 1. Encabezado sin logo
- Cuando el cliente no tiene logo en `tbl_clientes.logo`, se muestra el nombre en texto plano dentro de la celda. Se ve desproporcional.
- **Fix aplicado:** La ruta del logo era incorrecta (`FCPATH . $cliente['logo']` → `FCPATH . 'uploads/' . $cliente['logo']`). Clientes con logo ahora lo muestran correctamente.
- **Pendiente:** Para clientes sin logo, mostrar un placeholder visual (iniciales del cliente o logo generico de Cycloid).

### 2. Numeracion de secciones inconsistente
- Salta de `1. INTEGRANTES` a `TEMAS ABIERTOS` (sin numero) a `2. TEMAS` a `4. OBSERVACIONES` a `5. COMPROMISOS`.
- Se eliminaron Cartera (seccion 3) y Proxima Reunion pero no se renumeraron las secciones restantes.

### 3. Tabla de datos generales sin bordes
- La tabla MOTIVO/CLIENTE/FECHA no tiene bordes, se ve suelta y desalineada.
- Las columnas de label (MOTIVO:, CLIENTE:) no tienen ancho fijo consistente.

### 4. Firmas en tabla de integrantes
- Las imagenes de firma dentro de la tabla de integrantes son muy grandes.
- Se desbordan visualmente de las celdas.

### 5. Secciones vacias con texto verde
- "Sin mantenimientos por vencer" en verde se ve fuera de contexto en un documento formal.
- Mejor: texto gris neutro o simplemente omitir la seccion si esta vacia.

### 6. Espaciado general
- Margenes muy ajustados, el contenido se siente apretado.
- Falta padding en body.

### 7. Firmas al pie
- Bloque de firmas finales usa `display: inline-block` que DOMPDF soporta con limitaciones.
- Mejor usar tabla para garantizar alineacion.

---

## Diseno objetivo

### Estructura del PDF (orden de secciones)

```
┌─────────────────────────────────────────────────────────┐
│  [LOGO]  │  SISTEMA DE GESTION DE SEGURIDAD  │ Codigo  │
│          │  Y SALUD EN EL TRABAJO             │ FT-SST  │
│          │  ACTA DE REUNION                   │ Version │
│          │                                     │ Fecha   │
├─────────────────────────────────────────────────────────┤
│                                                         │
│          ACTA DE VISITA Y SEGUIMIENTO AL SISTEMA        │
│                                                         │
├─────────────────────────────────────────────────────────┤
│  MOTIVO: xxx          │  HORARIO: xx:xx AM              │
│  CLIENTE: xxx         │  FECHA: dd/mm/yyyy              │
│  MODALIDAD: xxx       │                                 │
├─────────────────────────────────────────────────────────┤
│  1. INTEGRANTES                                         │
│  ┌──────────────┬──────────────┬──────────────┐         │
│  │ NOMBRE       │ CARGO        │ ROL          │         │
│  ├──────────────┼──────────────┼──────────────┤         │
│  │ xxx          │ xxx          │ xxx          │         │
│  └──────────────┴──────────────┴──────────────┘         │
├─────────────────────────────────────────────────────────┤
│  2. TEMAS ABIERTOS Y VENCIDOS                           │
│     MANTENIMIENTOS:                                     │
│     ┌─────────────────────┬─────────────┐               │
│     │ MANTENIMIENTO       │ VENCIMIENTO │               │
│     └─────────────────────┴─────────────┘               │
│     PENDIENTES ABIERTOS:                                │
│     ┌──────────────┬──────────┬──────┬──────┬──────┐    │
│     │ ACTIVIDAD    │ RESP.    │ ASIG.│CIERRE│ DIAS │    │
│     └──────────────┴──────────┴──────┴──────┴──────┘    │
├─────────────────────────────────────────────────────────┤
│  3. TEMAS TRATADOS                                      │
│     TEMA 1: xxx                                         │
│     DETALLE: xxx                                        │
├─────────────────────────────────────────────────────────┤
│  4. OBSERVACIONES                                       │
│     xxx                                                 │
├─────────────────────────────────────────────────────────┤
│  5. COMPROMISOS                                         │
│  ┌──────────────┬──────────────┬──────────────┐         │
│  │ ACTIVIDAD    │ FECHA CIERRE │ RESPONSABLE  │         │
│  └──────────────┴──────────────┴──────────────┘         │
├─────────────────────────────────────────────────────────┤
│  FIRMAS                                                 │
│                                                         │
│  _______________  _______________  _______________       │
│  ADMINISTRADOR    VIGIA SST        CONSULTOR            │
│  Nombre           Nombre           Nombre               │
└─────────────────────────────────────────────────────────┘
```

### Cambios vs version actual

| Seccion | Antes | Despues |
|---------|-------|---------|
| Numeracion | 1, (sin num), 2, 4, 5 | 1, 2, 3, 4, 5 |
| Integrantes | Incluye columna FIRMA | Quitar firma de integrantes, mover a seccion final |
| Integrantes | Nombre + Rol | Nombre + Cargo + Rol (3 columnas) |
| Pendientes | Actividad, Responsable, Dias | Actividad, Responsable, F.Asignacion, F.Cierre, Dias |
| Temas | Solo descripcion | Tema + Detalle separados |
| Firmas | inline-block divs | Tabla 3 columnas con nombre debajo |
| Datos generales | Sin bordes | Con bordes completos |
| Secciones vacias | Texto verde | Texto gris o seccion omitida |

---

## Restricciones tecnicas de DOMPDF

### CSS soportado
- Box model completo (margin, padding, border)
- `border-collapse: collapse` para tablas
- `page-break-before: always` / `page-break-after: always`
- `display: block`, `inline`, `inline-block` (con limitaciones)
- `font-weight`, `font-style`, `text-align`, `vertical-align`
- `background-color`, `color`
- `width`, `height`, `max-width`, `max-height` (en px, %, em)
- `float: left/right` (con limitaciones)

### CSS NO soportado
- **Flexbox** (`display: flex`) - NO USAR
- **Grid** (`display: grid`) - NO USAR
- **CSS Variables** (`--var`) - NO USAR
- **calc()** - NO USAR
- **box-shadow** - NO USAR
- **border-radius** - Soporte parcial, evitar
- **opacity** - Soporte parcial
- **transform** - NO USAR

### Regla de oro
**Usar TABLAS para todo el layout.** DOMPDF renderiza tablas de forma confiable. Los divs con float o inline-block pueden dar resultados impredecibles.

### Fuentes
- `DejaVu Sans` (incluida con DOMPDF, soporta UTF-8 completo)
- `Helvetica`, `Arial` (web-safe, disponibles)
- Fuentes custom requieren instalacion manual en DOMPDF

### Imagenes
- **Base64 inline** - Metodo mas confiable (es lo que usamos)
- Las rutas de archivo absoluto tambien funcionan
- `isRemoteEnabled: true` necesario para URLs externas
- Formato: PNG, JPEG. SVG tiene soporte limitado.

### Tamano de papel
- Usamos `letter` (carta 8.5x11") — consistente con los demas PDFs del sistema
- Margenes default de DOMPDF: ~1cm por lado

---

## Paleta de colores (consistente con el sistema)

```css
/* Encabezados de seccion */
#1c2437    /* Azul marino oscuro - titulo de seccion */

/* Encabezados de tabla */
#e8e8e8    /* Gris claro - fondo th */
#aaa       /* Gris medio - bordes th */

/* Texto */
#333       /* Gris oscuro - texto principal */
#555       /* Gris medio - labels */
#888       /* Gris claro - texto secundario */

/* Bordes */
#333       /* Bordes de header */
#ccc       /* Bordes de celdas de datos */
#d1d5db    /* Bordes sutiles */

/* Estados */
#28a745    /* Verde - sin pendientes (usado con moderacion) */
#dc3545    /* Rojo - alertas/vencidos */
```

---

## Configuracion DOMPDF en el controlador

```php
// ActaVisitaController::generarPdfInterno()
$options = new \Dompdf\Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new \Dompdf\Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();
```

### Datos que recibe el template

```php
$data = [
    'acta'               => [],  // tbl_actas_visita row
    'cliente'            => [],  // tbl_clientes row (incluye logo)
    'consultor'          => [],  // tbl_consultor row
    'integrantes'        => [],  // tbl_acta_visita_integrantes rows
    'temas'              => [],  // tbl_acta_visita_temas rows
    'compromisos'        => [],  // tbl_pendientes rows (where id_acta_visita = X)
    'pendientesAbiertos' => [],  // tbl_pendientes rows (estado=ABIERTA, sin este acta)
    'mantenimientos'     => [],  // tbl_vencimientos_mantenimientos + detalle
    'firmas'             => [],  // ['administrador' => base64, 'vigia' => base64, 'consultor' => base64]
    'logoBase64'         => '',  // Logo del cliente en base64
];
```

---

## Ruta del logo - Fix documentado

**Bug:** `FCPATH . $cliente['logo']` buscaba el archivo en la raiz del proyecto.
**Fix:** `FCPATH . 'uploads/' . $cliente['logo']` — los logos estan en `public/uploads/`.
**Referencia:** `FirmaElectronicaController.php:376` usa la ruta correcta.

Los logos en la BD son solo el filename (ej: `1736474559_f5b66b4b5d9f2f2d36e7.png`), no la ruta completa.

---

## Archivos involucrados

| Archivo | Rol |
|---------|-----|
| `app/Views/inspecciones/acta_visita/pdf.php` | Template HTML del PDF |
| `app/Controllers/Inspecciones/ActaVisitaController.php` | Metodo `generarPdfInterno()` — carga datos, genera PDF |
| `public/uploads/` | Carpeta donde estan los logos de clientes |
| `uploads/inspecciones/firmas/` | Firmas digitales PNG |
| `uploads/inspecciones/pdfs/` | PDFs generados |
