<?php

/**
 * Convierte una ruta de BD (serve-file/...) a ruta física absoluta.
 *
 * La BD almacena rutas como "serve-file/firmas/archivo.png" que son URLs
 * para el navegador. Los generadores de PDF (TCPDF/DOMPDF) necesitan
 * la ruta física real en UPLOADS_PATH.
 */
function resolve_upload_path(string $dbPath): string
{
    if (strpos($dbPath, 'serve-file/') === 0) {
        return UPLOADS_PATH . substr($dbPath, strlen('serve-file/'));
    }

    return FCPATH . $dbPath;
}
