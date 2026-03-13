<?php

namespace App\Traits;

/**
 * Trait compartido para comprimir fotos al subir y al generar PDFs.
 * Usado por TODOS los controllers de inspecciones que manejan fotos.
 */
trait ImagenCompresionTrait
{
    /**
     * Corrige la orientación EXIF de una imagen GD.
     * Las fotos de celular guardan la rotación como metadata EXIF,
     * pero GD ignora esta metadata al cargar la imagen.
     */
    private function corregirOrientacionExif(string $path, $src)
    {
        if (!function_exists('exif_read_data')) {
            return $src;
        }

        $exif = @exif_read_data($path);
        if (!$exif || empty($exif['Orientation'])) {
            return $src;
        }

        switch ($exif['Orientation']) {
            case 3: // 180°
                $src = imagerotate($src, 180, 0);
                break;
            case 6: // 90° CW (celular en vertical, foto más común)
                $src = imagerotate($src, -90, 0);
                break;
            case 8: // 90° CCW
                $src = imagerotate($src, 90, 0);
                break;
        }

        return $src;
    }

    /**
     * Carga una imagen desde archivo y corrige su orientación EXIF.
     * Retorna [resource $src, int $width, int $height] o null si falla.
     */
    private function cargarImagenConExif(string $path): ?array
    {
        $info = @getimagesize($path);
        if (!$info) return null;

        $mime = $info['mime'];

        $src = null;
        if ($mime === 'image/jpeg') {
            $src = @imagecreatefromjpeg($path);
        } elseif ($mime === 'image/png') {
            $src = @imagecreatefrompng($path);
        } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
            $src = @imagecreatefromwebp($path);
        }
        if (!$src) return null;

        // Corregir orientación EXIF (solo JPEG tiene EXIF)
        if ($mime === 'image/jpeg') {
            $src = $this->corregirOrientacionExif($path, $src);
        }

        // Después de rotar, las dimensiones pueden haber cambiado
        $w = imagesx($src);
        $h = imagesy($src);

        return [$src, $w, $h];
    }

    /**
     * Comprime una imagen en disco: redimensiona a maxWidth y aplica quality JPEG.
     * Corrige orientación EXIF automáticamente.
     * Llamar DESPUÉS de $file->move().
     */
    protected function comprimirImagen(string $path, int $maxWidth = 1200, int $quality = 70): void
    {
        $loaded = $this->cargarImagenConExif($path);
        if (!$loaded) return;

        [$src, $origW, $origH] = $loaded;

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
     * Corrige orientación EXIF automáticamente.
     * Reduce tamaño drásticamente: 3MB foto → ~80KB en PDF.
     */
    protected function comprimirParaPdf(string $path, int $maxWidth = 800, int $quality = 55): ?string
    {
        $loaded = $this->cargarImagenConExif($path);
        if (!$loaded) return null;

        [$src, $origW, $origH] = $loaded;

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
