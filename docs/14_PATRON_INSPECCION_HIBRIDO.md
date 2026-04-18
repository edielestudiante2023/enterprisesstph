# 14 - Patron: Inspeccion HIBRIDA (Checklist fijo + N-FOTOS dinamicas)

## Resumen

Hibrido de los patrones 12 (PLANO) y 13 (N-ITEMS). Se usa cuando la inspeccion es una **lista de chequeo con preguntas fijas con calificacion cualitativa** (C / CP / NC / NA) PERO ademas requiere **N fotos dinamicas con observacion por foto** como evidencia.

A diferencia del patron PLANO puro, este incluye una tabla detalle SOLO para fotos. A diferencia del patron N-ITEMS puro, los criterios del checklist NO son filas dinamicas sino columnas fijas.

**Modulos que usan este patron:** Productos Quimicos (Fase 11).

---

## Diferencias clave con los otros patrones

| Aspecto              | PLANO (12)               | N-ITEMS (13)                | HIBRIDO (14)                         |
|----------------------|--------------------------|-----------------------------|---------------------------------------|
| Checklist            | Columnas fijas           | Filas dinamicas             | Columnas fijas (C/CP/NC/NA)          |
| Fotos                | Columnas fijas           | 1 por fila dinamica         | Tabla detalle dinamica (N fotos)     |
| Tablas               | 1                        | 2 (master + detalle)        | 2 (master + fotos)                   |
| Modelos              | 1                        | 2                           | 2                                     |
| JS buildRow          | NO                       | SI (para criterios)         | SI (solo para fotos)                 |
| Score / %            | Opcional                 | Opcional                    | SI (semaforo 90/70)                  |
| Toggle preguntas     | NO                       | NO                          | SI (seccion condicional)             |

---

## Modelo de calificacion cualitativa

### Escala

| Calificacion      | Sigla | Factor | Uso operativo                     |
|-------------------|-------|--------|-----------------------------------|
| Cumple            | C     | 1.0    | No requiere accion                |
| Cumple Parcial    | CP    | 0.5    | Requiere mejora                   |
| No Cumple         | NC    | 0.0    | Requiere accion inmediata         |
| No Aplica         | NA    | excl.  | Se excluye del calculo            |

### Formula

```
% Cumplimiento = (Suma de factores aplicables / Items aplicables) * 100
```

Items con `NA` no suman al numerador ni al denominador.

### Semaforo de interpretacion

| Rango       | Nivel   | Color  | Etiqueta              |
|-------------|---------|--------|-----------------------|
| 90% - 100%  | Alto    | verde  | Control adecuado      |
| 70% - 89%   | Medio   | ambar  | Requiere mejoras      |
| < 70%       | Bajo    | rojo   | Riesgo significativo  |

En el modelo se persiste en:
- `porcentaje_cumplimiento DECIMAL(5,2)` al finalizar
- `nivel_riesgo ENUM('alto','medio','bajo')` al finalizar

En el form se calcula en vivo con JS (feedback mientras el usuario marca).

---

## Estructura de archivos

```
app/SQL/migrate_inspeccion_{modulo}.php                          -- 2 tablas
app/Models/Inspeccion{Modulo}Model.php                           -- master
app/Models/Inspeccion{Modulo}FotoModel.php                       -- detalle fotos
app/Controllers/Inspecciones/Inspeccion{Modulo}Controller.php    -- controlador CRUD+PDF+score
app/Views/inspecciones/{modulo}/list.php                         -- listado DataTables
app/Views/inspecciones/{modulo}/form.php                         -- checklist + fotos dinamicas
app/Views/inspecciones/{modulo}/view.php                         -- read-only
app/Views/inspecciones/{modulo}/pdf.php                          -- template DOMPDF
```

Modificados: `Routes.php`, `inspecciones/dashboard.php`, `InspeccionesController.php`.

---

## Migracion SQL

### Tabla master

```sql
CREATE TABLE tbl_inspeccion_{modulo} (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_inspeccion DATE NOT NULL,
    ubicacion VARCHAR(255) NULL,

    -- Toggle para seccion condicional
    tiene_{condicion} TINYINT(1) NOT NULL DEFAULT 0,

    -- N items del checklist como columnas fijas
    cal_item_01 ENUM('C','CP','NC','NA') NULL,
    cal_item_02 ENUM('C','CP','NC','NA') NULL,
    -- ... N items
    cal_item_NN ENUM('C','CP','NC','NA') NULL,

    -- Score calculado al finalizar
    porcentaje_cumplimiento DECIMAL(5,2) NULL,
    nivel_riesgo ENUM('alto','medio','bajo') NULL,

    observaciones_finales TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_insp_{tag}_cliente FOREIGN KEY (id_cliente) REFERENCES tbl_clientes(id_cliente),
    CONSTRAINT fk_insp_{tag}_consultor FOREIGN KEY (id_consultor) REFERENCES tbl_consultor(id_consultor),
    INDEX idx_insp_{tag}_cliente (id_cliente),
    INDEX idx_insp_{tag}_consultor (id_consultor),
    INDEX idx_insp_{tag}_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Tabla fotos (detalle dinamico)

```sql
CREATE TABLE tbl_inspeccion_{modulo}_foto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_inspeccion INT NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    foto VARCHAR(255) NULL,
    observacion TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_{tag}_foto_insp
        FOREIGN KEY (id_inspeccion) REFERENCES tbl_inspeccion_{modulo}(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX idx_{tag}_foto_insp (id_inspeccion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Controlador

### Constante ITEMS

Define las preguntas del checklist:

```php
public const ITEMS = [
    1  => ['label' => 'Pregunta 1...', 'grupo' => 'general'],
    2  => ['label' => 'Pregunta 2...', 'grupo' => 'general'],
    // ...
    16 => ['label' => 'Pregunta condicional 1...', 'grupo' => 'condicional'],
    17 => ['label' => 'Pregunta condicional 2...', 'grupo' => 'condicional'],
];
```

### Factores de calificacion

```php
private const FACTORES = ['C' => 1.0, 'CP' => 0.5, 'NC' => 0.0, 'NA' => null];
```

### Metodos

Identicos a PLANO (10 publicos) + privados:
- `calcularCumplimiento(array $inspeccion): array` -- devuelve `[pct, nivel]`
- `saveFotos(int $idInspeccion): array` -- copia del `saveExtintores()` de N-ITEMS pero solo con foto + observacion + orden
- `getInspeccionPostData()` -- incluye los `cal_item_NN` iterando constante ITEMS

### calcularCumplimiento

```php
private function calcularCumplimiento(array $inspeccion): array
{
    $aplicables = 0;
    $suma = 0.0;
    foreach (self::ITEMS as $num => $cfg) {
        // Saltar preguntas condicionales si el toggle esta apagado
        if ($cfg['grupo'] === 'condicional' && empty($inspeccion['tiene_{condicion}'])) {
            continue;
        }
        $cal = $inspeccion['cal_item_' . str_pad($num, 2, '0', STR_PAD_LEFT)] ?? null;
        if ($cal === null || $cal === 'NA') continue;
        $factor = self::FACTORES[$cal] ?? null;
        if ($factor === null) continue;
        $aplicables++;
        $suma += $factor;
    }
    if ($aplicables === 0) return ['pct' => 0.0, 'nivel' => 'bajo'];
    $pct = round(($suma / $aplicables) * 100, 2);
    $nivel = $pct >= 90 ? 'alto' : ($pct >= 70 ? 'medio' : 'bajo');
    return ['pct' => $pct, 'nivel' => $nivel];
}
```

### saveFotos (identico a saveExtintores de N-ITEMS, simplificado)

```php
private function saveFotos(int $idInspeccion): array
{
    $obs = $this->request->getPost('foto_obs') ?? [];
    $fotoIds = $this->request->getPost('foto_id') ?? [];

    $existentes = [];
    $existentesPorOrden = [];
    foreach ($this->fotoModel->getByInspeccion($idInspeccion) as $f) {
        $existentes[$f['id']] = $f;
        $existentesPorOrden[(int)$f['orden']] = $f;
    }
    $this->fotoModel->deleteByInspeccion($idInspeccion);

    $dir = FCPATH . 'uploads/inspecciones/{modulo}/fotos/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $files = $this->request->getFiles();
    $newIds = [];

    foreach ($obs as $i => $observacion) {
        $existenteId = $fotoIds[$i] ?? null;
        $existente = $existenteId ? ($existentes[$existenteId] ?? null) : null;
        if (!$existente) $existente = $existentesPorOrden[$i + 1] ?? null;

        $fotoPath = $existente['foto'] ?? null;
        if (isset($files['foto_file'][$i]) && $files['foto_file'][$i]->isValid() && !$files['foto_file'][$i]->hasMoved()) {
            $file = $files['foto_file'][$i];
            $fileName = $file->getRandomName();
            $file->move($dir, $fileName);
            $this->comprimirImagen($dir . $fileName);
            $fotoPath = 'uploads/inspecciones/{modulo}/fotos/' . $fileName;
        }

        $this->fotoModel->insert([
            'id_inspeccion' => $idInspeccion,
            'orden'         => $i + 1,
            'foto'          => $fotoPath,
            'observacion'   => $observacion,
        ]);
        $newIds[] = $this->fotoModel->getInsertID();
    }
    return $newIds;
}
```

---

## Vistas

### form.php

Tres secciones accordion:

1. **Datos Generales** -- cliente (Select2), fecha, ubicacion, toggle condicional.
2. **Checklist** -- por cada item en `ITEMS`, una fila con 4 botones radio (C/CP/NC/NA). Los items del grupo `condicional` ocultos hasta marcar el toggle.
3. **Fotos de Evidencia** -- N filas dinamicas con: input file + textarea observacion + boton eliminar. Boton "Agregar foto" al final.

En el header, badge en vivo con `% Cumplimiento` y color del semaforo (actualiza con JS al cambiar cualquier radio).

```js
function recalcCumplimiento() {
    let aplicables = 0, suma = 0;
    const factores = {C:1, CP:0.5, NC:0, NA:null};
    const tiene = document.querySelector('[name="tiene_{condicion}"]').checked;
    document.querySelectorAll('.checklist-item').forEach(row => {
        const num = row.dataset.itemNum;
        const grupo = row.dataset.grupo;
        if (grupo === 'condicional' && !tiene) return;
        const checked = row.querySelector('input[type=radio]:checked');
        if (!checked) return;
        const factor = factores[checked.value];
        if (factor === null) return;  // NA excluido
        aplicables++;
        suma += factor;
    });
    const pct = aplicables > 0 ? (suma / aplicables) * 100 : 0;
    const nivel = pct >= 90 ? 'alto' : pct >= 70 ? 'medio' : 'bajo';
    const color = {alto:'#28a745', medio:'#ffc107', bajo:'#dc3545'}[nivel];
    document.getElementById('scorePct').textContent = pct.toFixed(1) + '%';
    document.getElementById('scoreBadge').style.background = color;
}
```

### JS buildFotoRow

```js
function buildFotoRow(num, data) {
    data = data || {};
    return `
    <div class="card mb-2 foto-row" style="border-left:3px solid #17a2b8;">
        <div class="card-body p-2">
            <input type="hidden" name="foto_id[]" value="${data.id || ''}">
            <div class="d-flex justify-content-between">
                <strong>Foto #<span class="foto-num">${num}</span></strong>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-foto"><i class="fas fa-times"></i></button>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-6">
                    <input type="file" name="foto_file[]" class="file-preview" accept="image/*">
                    <div class="preview-img"></div>
                </div>
                <div class="col-6">
                    <textarea name="foto_obs[]" class="form-control form-control-sm" rows="3" placeholder="Observacion sobre la foto">${(data.observacion||'').replace(/</g,'&lt;')}</textarea>
                </div>
            </div>
        </div>
    </div>`;
}
```

### Autosave

Usa `autosave_server.js` con `detailRowSelector: '.foto-row'` y `detailIdInputName: 'foto_id[]'` (identico a extintores).

### view.php

- Datos generales + badge semaforo con `%`
- Tabla del checklist: Pregunta | Calificacion (con color por valor)
- Galeria de fotos con observacion debajo de cada una
- Observaciones finales

### pdf.php (DOMPDF)

- Header corporativo FT-SST-XXX
- Intro (texto fundamentacion)
- Tabla datos
- Tabla checklist (17 filas con calificacion coloreada)
- Box resaltado con `% Cumplimiento` + semaforo + interpretacion
- Galeria fotos en tabla 2 cols (base64) con observacion debajo

---

## Integracion dashboard + rutas

Igual al patron PLANO:

- **InspeccionesController::dashboard()**: total (completos) + pendientes (borradores) del modelo master.
- **dashboard.php**: card en el grid de modulos + seccion de pendientes con "Continuar editando".
- **Routes.php**: 10 rutas estandar (`list, create, create/:num, store, edit, update, view, pdf, regenerar, finalizar, delete, enviar-email`).

---

## Tabla de fases (patron HIBRIDO)

| Fase | Modulo             | Tabla master                         | Detalle fotos                            | PDF         | id_detailreport | Tag            |
|------|--------------------|--------------------------------------|------------------------------------------|-------------|-----------------|----------------|
| 11   | Productos Quimicos | tbl_inspeccion_productos_quimicos    | tbl_inspeccion_productos_quimicos_foto   | FT-SST-220  | 17              | insp_pq_id     |

---

## Checklist de implementacion

1. [ ] Migracion SQL (2 tablas, local primero, production despues)
2. [ ] Modelo master con `allowedFields` incluyendo los `cal_item_NN`
3. [ ] Modelo fotos con `getByInspeccion`, `deleteByInspeccion`
4. [ ] Controlador con constante ITEMS + FACTORES + 10 metodos publicos + 4 privados
5. [ ] Views: list (DataTables), form (3 secciones, score live, N-fotos dinamicas), view, pdf
6. [ ] Routes.php: 12 rutas
7. [ ] InspeccionesController::dashboard(): total + pendientes
8. [ ] dashboard.php: card en grid + pendientes
9. [ ] Verificar email + uploadToReportes (id_detailreport correcto)
10. [ ] Probar autosave con filas dinamicas de fotos (fallback por orden)
