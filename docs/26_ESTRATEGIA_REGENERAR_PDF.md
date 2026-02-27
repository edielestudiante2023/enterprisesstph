# 26 - Estrategia de Regeneracion de PDF

## Fecha: 2026-02-27

---

## 1. Resumen del patron

Cada controlador de inspeccion tiene **3 metodos** relacionados con PDF:

```
finalizar($id)        → Primera generacion (borrador → completo)
generatePdf($id)      → Visualizacion (siempre regenera y sirve inline)
regenerarPdf($id)     → Regeneracion explicita (actualiza BD + reportes)
```

Todos llaman internamente a `generarPdfInterno($id)` que es el metodo privado que:
1. Carga datos frescos de la BD
2. Renderiza la vista `pdf.php` con DOMPDF
3. **Elimina** el PDF anterior del disco (`unlink`)
4. Guarda el nuevo PDF con timestamp en el nombre
5. Retorna la ruta relativa del nuevo archivo

---

## 2. Flujo de cada metodo

### 2.1 `finalizar($id)` — Primera generacion

```
Usuario toca "Finalizar"
    |
    v
Valida precondiciones (firmas, campos obligatorios)
    |
    v
generarPdfInterno($id) → genera PDF fresco
    |
    v
UPDATE tabla SET estado='completo', ruta_pdf='nuevo_path'   ← ACTUALIZA BD
    |
    v
uploadToReportes() → copia a uploads/{nit}/ + INSERT tbl_reporte
    |
    v
Retorna JSON {success: true, pdf_url: '...'}
```

**Estado despues:** `ruta_pdf` en BD = archivo real en disco. Todo sincronizado.

### 2.2 `generatePdf($id)` — Visualizacion inline

```
Usuario toca "Ver PDF" (ruta: /inspecciones/{tipo}/pdf/{id})
    |
    v
generarPdfInterno($id) → genera PDF fresco
    |    Borra el archivo viejo
    |    Crea nuevo archivo con timestamp diferente
    |
    v
UPDATE tabla SET ruta_pdf='nuevo_path'   ← ACTUALIZA BD (fix 2026-02-27)
    |
    v
Sirve el PDF inline via Response (Content-Type: application/pdf)
```

**Estado despues:** `ruta_pdf` en BD = archivo real en disco. Sincronizado.
> **Nota:** `uploadToReportes()` NO se llama desde generatePdf — solo desde `finalizar()` y `regenerarPdf()`.

### 2.3 `regenerarPdf($id)` — Regeneracion explicita

```
Usuario toca "Regenerar PDF" (ruta: /inspecciones/{tipo}/regenerar/{id})
    |
    v
Valida estado = 'completo'
    |
    v
generarPdfInterno($id) → genera PDF fresco
    |
    v
UPDATE tabla SET ruta_pdf='nuevo_path'   ← ACTUALIZA BD
    |
    v
uploadToReportes() → actualiza enlace en tbl_reporte
    |
    v
Redirect a view/{id} con mensaje "PDF regenerado exitosamente"
```

**Estado despues:** `ruta_pdf` en BD = archivo real en disco. Todo sincronizado.

---

## 3. Bug corregido: ruta_pdf desincronizada (fix 2026-02-27)

### Escenario que causaba el bug (ANTES del fix)

```
1. Consultor finaliza inspeccion
   → ruta_pdf = 'uploads/inspecciones/pdfs/locativa_5_20260227_100000.pdf'  ✓

2. Consultor ve el PDF (generatePdf)
   → generarPdfInterno() BORRA locativa_5_20260227_100000.pdf
   → Crea locativa_5_20260227_103015.pdf
   → Sirve al navegador OK
   → ANTES: ruta_pdf en BD seguia = 'locativa_5_20260227_100000.pdf'  ✗
   → AHORA: ruta_pdf en BD = 'locativa_5_20260227_103015.pdf'  ✓ (fix aplicado)
```

### Fix aplicado

Se agrego `$this->MODEL->update($id, ['ruta_pdf' => $pdfPath])` al metodo
`generatePdf()` de los **29 controladores de inspecciones**, justo despues
de `generarPdfInterno()`. Esto garantiza que `ruta_pdf` en BD siempre
apunta al archivo real en disco.

**Controllers corregidos (29):**
- ActaVisita (`actaModel`), HvBrigadista (`hvModel`), EvaluacionSimulacro (`evalModel`)
- MatrizVulnerabilidad (`matrizModel`), PlanEmergencia (`model`)
- InspeccionLocativa, Senalizacion, Extintores, Botiquin, Gabinete, Comunicacion,
  RecursosSeguridad, ProbabilidadPeligros, PreparacionSimulacro, AuditoriaZonaResiduos,
  DotacionVigilante, DotacionAseadora, DotacionTodero, ReporteCapacitacion,
  AsistenciaInduccion, ProgramaResiduos, ProgramaPlagas, ProgramaLimpieza,
  ProgramaAguaPotable, PlanSaneamiento (todos `inspeccionModel`)
- KpiLimpieza, KpiResiduos, KpiPlagas, KpiAguaPotable (todos `model`)

**InformeAvancesController** ya tenia el fix desde su creacion (linea 212).

---

## 4. Solucion aplicada (Opcion A) ✓

`generatePdf()` ahora actualiza `ruta_pdf` en BD despues de regenerar.
Fix aplicado el 2026-02-27 a los 29 controladores de inspecciones.

```php
public function generatePdf($id)
{
    $inspeccion = $this->model->find($id);
    if (!$inspeccion) { return redirect()->back()->with('error', '...'); }

    $pdfPath = $this->generarPdfInterno($id);
    $this->model->update($id, ['ruta_pdf' => $pdfPath]);  // ← FIX

    $fullPath = FCPATH . $pdfPath;
    return $this->response
        ->setHeader('Content-Type', 'application/pdf')
        ->setBody(file_get_contents($fullPath));
}
```

> **Nota:** `generatePdf()` NO llama `uploadToReportes()`. Para sincronizar
> tambien `tbl_reporte`, usar `regenerarPdf()` que si actualiza ambos.

---

## 5. Estructura de archivos PDF

### Rutas de almacenamiento

| Ubicacion | Quien la usa | Gestionada por |
|-----------|-------------|----------------|
| `uploads/inspecciones/pdfs/{tipo}_{id}_{timestamp}.pdf` | Referencia interna (`ruta_pdf`) | `generarPdfInterno()` |
| `uploads/{nit_cliente}/{tipo}_{id}_{timestamp}.pdf` | Sistema de reportes (`tbl_reporte.enlace`) | `uploadToReportes()` |

### Nombre del archivo

```
{tipo}_{id}_{timestamp}.pdf

Ejemplos:
  acta_visita_42_20260227_153045.pdf
  locativa_15_20260227_160000.pdf
  informe_avances_8_20260227_120000.pdf
```

El timestamp (`date('Ymd_His')`) garantiza unicidad al regenerar.

---

## 6. generarPdfInterno() — Estructura comun

Todos los 29 controladores + InformeAvancesController siguen el mismo patron:

```php
private function generarPdfInterno(int $id): ?string
{
    // 1. Cargar datos frescos
    $inspeccion = $this->model->find($id);
    $cliente = (new ClientModel())->find($inspeccion['id_cliente']);
    $consultor = (new ConsultantModel())->find($inspeccion['id_consultor']);

    // 2. Logo a base64 (DOMPDF no soporta URLs remotas de forma fiable)
    $logoBase64 = '';
    if (!empty($cliente['logo'])) {
        $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
        if (file_exists($logoPath)) {
            $logoMime = mime_content_type($logoPath);
            $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
    }

    // 3. Fotos/firmas a base64 (especifico de cada tipo)
    // ...

    // 4. Renderizar HTML
    $html = view('inspecciones/{tipo}/pdf', $data);

    // 5. DOMPDF
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('letter', 'portrait');
    $dompdf->render();

    // 6. Guardar en disco
    $pdfDir = 'uploads/inspecciones/pdfs/';
    if (!is_dir(FCPATH . $pdfDir)) {
        mkdir(FCPATH . $pdfDir, 0755, true);
    }
    $pdfFileName = '{tipo}_' . $id . '_' . date('Ymd_His') . '.pdf';
    $pdfPath = $pdfDir . $pdfFileName;

    // 7. Eliminar PDF anterior
    if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
        unlink(FCPATH . $inspeccion['ruta_pdf']);
    }

    file_put_contents(FCPATH . $pdfPath, $dompdf->output());

    return $pdfPath;
}
```

### Opciones DOMPDF obligatorias

| Opcion | Valor | Razon |
|--------|-------|-------|
| `isRemoteEnabled` | `true` | Para cargar imagenes base64 y CSS inline |
| `isHtml5ParserEnabled` | `true` | Parser HTML5 mas tolerante |
| Paper | `letter`, `portrait` | Estandar carta US |

### Restricciones de DOMPDF 3.0.0

- `@page margin` con `cm` **NO funciona** — usar siempre `px`
- Flexbox y Grid **NO soportados** — usar tablas para layout
- Imagenes deben ser **base64** (no URLs remotas)
- Logos: ruta correcta es `FCPATH . 'uploads/' . $cliente['logo']`

---

## 7. uploadToReportes() — Auto-cargue a sistema de reportes

### Patron comun

```php
private function uploadToReportes(array $inspeccion, string $pdfPath): bool
{
    $reporteModel = new ReporteModel();
    $cliente = (new ClientModel())->find($inspeccion['id_cliente']);
    if (!$cliente) return false;

    $nitCliente = $cliente['nit_cliente'];

    // Buscar duplicado por marcador en observaciones
    $existente = $reporteModel
        ->where('id_cliente', $inspeccion['id_cliente'])
        ->where('id_report_type', 6)
        ->where('id_detailreport', $ID_DETAIL)
        ->like('observaciones', '{tipo}_id:' . $inspeccion['id'])
        ->first();

    // Copiar PDF a uploads/{nit}/
    $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);

    $fileName = '{tipo}_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
    copy(FCPATH . $pdfPath, $destDir . '/' . $fileName);

    $data = [
        'titulo_reporte'  => 'TIPO - ' . $cliente['nombre_cliente'] . ' - ' . $fecha,
        'id_detailreport' => $ID_DETAIL,
        'id_report_type'  => 6,     // GESTION SST
        'id_cliente'      => $inspeccion['id_cliente'],
        'estado'          => 'Activo',
        'observaciones'   => 'Generado automaticamente. {tipo}_id:' . $inspeccion['id'],
        'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
        'updated_at'      => date('Y-m-d H:i:s'),
    ];

    if ($existente) {
        return $reporteModel->update($existente['id_reporte'], $data);
    }
    $data['created_at'] = date('Y-m-d H:i:s');
    return $reporteModel->save($data);
}
```

### Deduplicacion

Se usa `{tipo}_id:{id}` en el campo `observaciones` como marcador unico. Al regenerar:
- Si ya existe reporte con ese marcador → `UPDATE` (enlace apunta al nuevo PDF)
- Si no existe → `INSERT`

### id_detailreport por tipo

| ID | Tipo de inspeccion |
|----|--------------------|
| 1 | Actas de Visita |
| 2 | Senalizacion |
| 3 | Locativas |
| 4 | Extintores |
| 5 | Botiquin |
| 6 | Gabinetes |
| 7 | Comunicaciones |
| 8 | Recursos Seguridad |
| 9 | Probabilidad Peligros |
| 10 | Matriz Vulnerabilidad |
| 11 | Plan Emergencia |
| 12 | Evaluacion Simulacro |
| 13 | HV Brigadista |
| 14 | Dotacion Vigilante |
| 15 | Dotacion Aseadora |
| 16 | Dotacion Todero |
| 17 | Auditoria Zona Residuos |
| 18 | Reporte Capacitacion |
| 19 | Preparacion Simulacro |
| 20 | Asistencia Induccion |
| 21 | Limpieza y Desinfeccion |
| 22 | Residuos Solidos |
| 23 | Control Plagas |
| 24 | Agua Potable |
| 25 | Plan Saneamiento |
| 26 | KPI Limpieza |
| 27 | KPI Residuos |
| 28 | KPI Plagas |
| 29 | KPI Agua Potable |
| 37 | Informe de Avances |

Todos usan `id_report_type = 6` (GESTION SST).

---

## 8. Modulos con PDF — Inventario completo (30 controllers)

### 29 Controllers de inspecciones

Ubicacion: `app/Controllers/Inspecciones/`

Todos siguen el patron identico con los 3 metodos:
- `finalizar()` → genera + update ruta_pdf + uploadToReportes
- `generatePdf()` → regenera + sirve inline (⚠️ NO actualiza ruta_pdf)
- `regenerarPdf()` → regenera + update ruta_pdf + uploadToReportes

### 1 Controller de InformeAvances

Ubicacion: `app/Controllers/InformeAvancesController.php`

Diferencia clave: `generatePdf()` SI actualiza `ruta_pdf` y SI llama `uploadToReportes()`.

---

## 9. Rutas de PDF

Patron de rutas para los 29 modulos de inspecciones:

```
GET /inspecciones/{tipo}/pdf/(:num)        → generatePdf($id)   — ver PDF inline
GET /inspecciones/{tipo}/regenerar/(:num)  → regenerarPdf($id)  — regenerar + actualizar BD
POST /inspecciones/{tipo}/finalizar/(:num) → finalizar($id)     — primera generacion
```

Para Informe de Avances:
```
GET /informe-avances/pdf/(:num)            → generatePdf($id)
POST /informe-avances/finalizar/(:num)     → finalizar($id)
```

---

## 10. Diagnostico: "Edito y no se carga el PDF"

### Checklist de diagnostico

1. **¿El archivo existe en disco?**
   ```
   ls -la uploads/inspecciones/pdfs/{tipo}_{id}_*.pdf
   ```
   Si no existe → `generarPdfInterno()` fallo o `generatePdf()` borro el anterior sin crear nuevo.

2. **¿ruta_pdf en BD apunta al archivo correcto?**
   ```sql
   SELECT id, ruta_pdf FROM tbl_{tabla} WHERE id = {id};
   ```
   Si `ruta_pdf` apunta a un archivo que no existe → desincronizacion por `generatePdf()`.

3. **¿Hay error de DOMPDF?**
   Revisar `writable/logs/` por errores PHP. DOMPDF puede fallar silenciosamente si:
   - Una imagen base64 es invalida
   - El HTML tiene tags mal cerrados
   - El CSS usa propiedades no soportadas (flexbox, grid)

4. **¿Cache del navegador?**
   El PDF se sirve con `Content-Disposition: inline`. Algunos navegadores cachean agresivamente.
   Solucion: agregar cache-busting header:
   ```php
   ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
   ->setHeader('Pragma', 'no-cache')
   ```

5. **¿El update() regenera automaticamente?**
   Solo si `estado === 'completo'` (verificar que el metodo `update()` del controller tiene esta logica).

### Solucion rapida

Usar el boton **"Regenerar PDF"** en la vista de detalle. Este siempre:
- Regenera desde el template actual
- Actualiza `ruta_pdf` en la BD
- Re-sube al sistema de reportes

---

## 11. Resumen de decision

| Aspecto | Decision | Razon |
|---------|----------|-------|
| Motor PDF | DOMPDF 3.0.0 | Ya integrado, sin dependencias externas |
| Regeneracion | Siempre desde template (nunca cache) | Refleja cambios de datos y template |
| Almacenamiento | Doble (inspecciones/pdfs/ + uploads/{nit}/) | PWA + sistema reportes |
| Deduplicacion | Marcador `{tipo}_id:{id}` en observaciones | Evita duplicados en tbl_reporte |
| Nombres | `{tipo}_{id}_{timestamp}.pdf` | Unicidad al regenerar |
| Imagenes | Base64 incrustado en HTML | DOMPDF no carga URLs remotas de forma fiable |
| Margenes | Siempre en `px` (no `cm`) | Bug conocido DOMPDF 3.0.0 |
