<?php

namespace App\Traits;

/**
 * Trait compartido para comprimir fotos al subir y al generar PDFs.
 * Usado por TODOS los controllers de inspecciones que manejan fotos.
 */
trait ImagenCompresionTrait
{
    /**
     * Comprime una imagen en disco: redimensiona a maxWidth y aplica quality JPEG.
     * Llamar DESPUÉS de $file->move().
     */
    protected function comprimirImagen(string $path, int $maxWidth = 1200, int $quality = 70): void
    {
        $info = @getimagesize($path);
        if (!$info) return;

        $mime = $info['mime'];
        $origW = $info[0];
        $origH = $info[1];

        $src = null;
        if ($mime === 'image/jpeg') {
            $src = @imagecreatefromjpeg($path);
        } elseif ($mime === 'image/png') {
            $src = @imagecreatefrompng($path);
        } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
            $src = @imagecreatefromwebp($path);
        }
        if (!$src) return;

        if ($origW > $maxWidth) {
            $newW = $maxWidth;
            $newH = (int) round($origH * ($maxWidth / $origW));
        } else {
            $newW = $origW;
            $newH = $origH;
        }

        $dst = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagejpeg($dst, $path, $quality);

        imagedestroy($src);
        imagedestroy($dst);
    }

    /**
     * Comprime una imagen en memoria y retorna JPEG binario (para base64 en PDFs).
     * Reduce tamaño drásticamente: 3MB foto → ~80KB en PDF.
     */
    protected function comprimirParaPdf(string $path, int $maxWidth = 800, int $quality = 55): ?string
    {
        $info = @getimagesize($path);
        if (!$info) return null;

        $mime = $info['mime'];
        $origW = $info[0];
        $origH = $info[1];

        $src = null;
        if ($mime === 'image/jpeg') {
            $src = @imagecreatefromjpeg($path);
        } elseif ($mime === 'image/png') {
            $src = @imagecreatefrompng($path);
        } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
            $src = @imagecreatefromwebp($path);
        }
        if (!$src) return null;

        if ($origW > $maxWidth) {
            $newW = $maxWidth;
            $newH = (int) round($origH * ($maxWidth / $origW));
        } else {
            $newW = $origW;
            $newH = $origH;
        }

        $dst = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        ob_start();
        imagejpeg($dst, null, $quality);
        $data = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return $data;
    }

    /**
     * Convierte un archivo de imagen a base64 comprimido para PDF.
     * Reemplaza el patrón: 'data:'.mime.';base64,'.base64_encode(file_get_contents(...))
     */
    protected function fotoABase64ParaPdf(string $path): string
    {
        $compressed = $this->comprimirParaPdf($path, 800, 55);
        if ($compressed) {
            return 'data:image/jpeg;base64,' . base64_encode($compressed);
        }
        return '';
    }

    /**
     * Sirve un PDF al navegador usando readfile() (no carga todo en memoria).
     * Reemplaza: $this->response->setBody(file_get_contents($fullPath))
     */
    protected function servirPdf(string $fullPath, string $filename): void
    {
        if (!file_exists($fullPath)) {
            header('HTTP/1.1 404 Not Found');
            echo 'PDF no encontrado';
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
}
