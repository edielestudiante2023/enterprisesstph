# PROMPT PARA AGENTE: Limpiar reportes con enlaces rotos (404)

Copia y pega esto en un nuevo chat de Claude Code:

---

## Contexto

El 2026-03-25 se perdieron archivos de uploads en producción. La base de datos tiene 3,238+ registros en `tbl_reporte` con enlaces a archivos que ya no existen. Necesito un comando Spark que verifique cada enlace y elimine los registros cuyos archivos no existen.

Lee `docs/INCIDENTE-PERDIDA-UPLOADS-20260325.md` para el contexto completo.

**NO preguntes nada. No pidas confirmación. Solo ejecuta.**

## Arquitectura actual

- Los uploads ahora van a `UPLOADS_PATH` (definido en `app/Config/Constants.php`)
- Producción: `UPLOADS_PATH = /www/soportes-clientes/`
- Local: `UPLOADS_PATH = ROOTPATH . '../soportes-clientes/'`
- Los enlaces en BD pueden tener dos formatos:
  - URLs completas: `https://phorizontal.cycloidtalent.com/uploads/901103223/archivo.pdf`
  - URLs con serve-file: `https://phorizontal.cycloidtalent.com/serve-file/901103223/archivo.pdf`
  - Rutas relativas: `uploads/contratos/archivo.pdf` o `serve-file/contratos/archivo.pdf`

## Tarea: Crear comando `php spark reportes:limpiar-404`

Archivo: `app/Commands/LimpiarReportes404.php`

### Lógica

1. Consultar TODOS los registros de `tbl_reporte` que tengan `enlace` no vacío
2. Para cada registro:
   a. Extraer la ruta relativa del archivo desde el enlace (quitar dominio y prefijo `uploads/` o `serve-file/`)
   b. Verificar si el archivo existe en DOS ubicaciones:
      - `UPLOADS_PATH . $rutaRelativa` (nueva ubicación)
      - `FCPATH . 'uploads/' . $rutaRelativa` (ubicación antigua, por si acaso)
   c. Si el archivo NO existe en ninguna de las dos → marcar para eliminar
3. Mostrar resumen: cuántos registros tienen archivo OK, cuántos son 404
4. Eliminar los registros 404 de `tbl_reporte`
5. Mostrar log final

### Detalles de implementación

```php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ReporteModel;

class LimpiarReportes404 extends BaseCommand
{
    protected $group       = 'Mantenimiento';
    protected $name        = 'reportes:limpiar-404';
    protected $description = 'Elimina registros de tbl_reporte cuyos archivos no existen';
    protected $usage       = 'reportes:limpiar-404 [--dry-run] [--verbose]';

    public function run(array $params)
    {
        $dryRun = CLI::getOption('dry-run') !== null;
        $verbose = CLI::getOption('verbose') !== null;

        $reporteModel = new ReporteModel();
        $reportes = $reporteModel->where('enlace IS NOT NULL')->where('enlace !=', '')->findAll();

        $total = count($reportes);
        $ok = 0;
        $missing = 0;
        $deleted = 0;
        $errors = [];

        CLI::write("Verificando $total reportes...", 'yellow');

        foreach ($reportes as $i => $reporte) {
            $enlace = $reporte['enlace'];

            // Extraer ruta relativa
            $rutaRelativa = $this->extraerRutaRelativa($enlace);

            if (!$rutaRelativa) {
                if ($verbose) CLI::write("  [{$reporte['id_reporte']}] SKIP - No se pudo parsear: $enlace", 'light_gray');
                continue;
            }

            // Verificar existencia en ambas ubicaciones
            $existeNueva = file_exists(UPLOADS_PATH . $rutaRelativa);
            $existeVieja = file_exists(FCPATH . 'uploads/' . $rutaRelativa);

            if ($existeNueva || $existeVieja) {
                $ok++;
                if ($verbose) CLI::write("  [{$reporte['id_reporte']}] OK: $rutaRelativa", 'green');
            } else {
                $missing++;
                if ($verbose) CLI::write("  [{$reporte['id_reporte']}] 404: $rutaRelativa", 'red');

                if (!$dryRun) {
                    $reporteModel->delete($reporte['id_reporte']);
                    $deleted++;
                }
            }

            // Progreso cada 100
            if (($i + 1) % 100 === 0) {
                CLI::write("  Progreso: " . ($i + 1) . "/$total", 'light_gray');
            }
        }

        CLI::newLine();
        CLI::write("========================================", 'white');
        CLI::write("  RESUMEN", 'white');
        CLI::write("========================================", 'white');
        CLI::write("Total verificados: $total", 'white');
        CLI::write("Archivos OK:      $ok", 'green');
        CLI::write("Archivos 404:     $missing", 'red');

        if ($dryRun) {
            CLI::write("(DRY RUN - no se eliminó nada)", 'yellow');
        } else {
            CLI::write("Eliminados:       $deleted", 'red');
        }
    }

    private function extraerRutaRelativa(string $enlace): ?string
    {
        // URL completa con dominio
        // https://phorizontal.cycloidtalent.com/uploads/901103223/archivo.pdf
        // https://phorizontal.cycloidtalent.com/serve-file/901103223/archivo.pdf
        if (preg_match('#/(?:uploads|serve-file)/(.+)$#', $enlace, $m)) {
            return $m[1];
        }

        // Ruta relativa
        // uploads/contratos/archivo.pdf o serve-file/contratos/archivo.pdf
        if (preg_match('#^(?:uploads|serve-file)/(.+)$#', $enlace, $m)) {
            return $m[1];
        }

        return null;
    }
}
```

### También verificar estas otras tablas

Además de `tbl_reporte`, verificar y limpiar enlaces rotos en:

1. **`tbl_contratos`** campo `ruta_pdf_contrato`:
   - Extraer ruta relativa (quitar prefijo `uploads/` o `serve-file/`)
   - Verificar si existe en `UPLOADS_PATH` o `FCPATH . 'uploads/'`
   - Si no existe, poner el campo en NULL y `contrato_generado = 0` (para que se pueda regenerar)

2. **`tbl_contratos`** campo `firma_cliente_imagen`:
   - Verificar si existe
   - Si no existe, poner en NULL (la firma se perdió, necesita re-firmarse)

3. **`tbl_matrices`** campo `enlace`:
   - Verificar si existe
   - Si no existe, eliminar el registro (se pueden regenerar)

4. **`tbl_informe_avances`** campos de imágenes (`img_cumplimiento_estandares`, `img_indicador_plan_trabajo`, `img_indicador_capacitacion`, `soporte_1_imagen`, `soporte_2_imagen`, `soporte_3_imagen`, `soporte_4_imagen`):
   - Verificar si cada imagen existe
   - Si no existe, poner el campo en NULL

### Ejecución

```bash
# Primero dry-run para ver qué se eliminaría
php spark reportes:limpiar-404 --dry-run --verbose

# Luego ejecutar de verdad
php spark reportes:limpiar-404
```

### Modelo ReporteModel

El modelo ya existe en `app/Models/ReporteModel.php`. Léelo para ver la estructura. La tabla es `tbl_reporte` y la primary key es `id_reporte`.

## Archivos clave que debes leer

1. `app/Models/ReporteModel.php` — estructura del modelo
2. `app/Config/Constants.php` — UPLOADS_PATH y UPLOADS_URL_PREFIX
3. `app/Controllers/FileServerController.php` — cómo se resuelven las rutas
4. `docs/INCIDENTE-PERDIDA-UPLOADS-20260325.md` — contexto del incidente

## NO preguntes nada. Crea el comando, pruébalo con --dry-run en local, y muestra el resultado.

---
