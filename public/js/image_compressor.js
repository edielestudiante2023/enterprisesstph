/**
 * image_compressor.js — Compresión cliente de fotos antes del upload.
 *
 * Expone window.compressImage(file) -> Promise<File>.
 *
 * - Solo comprime image/jpeg, image/png, image/webp.
 * - Short-circuit: si file.size < SMALL_BYTES, devuelve el file original.
 * - Escala proporcional a MAX_DIM.
 * - Respeta orientación EXIF (usa createImageBitmap si está disponible;
 *   si no, parser EXIF manual + transform en canvas).
 * - Procesa secuencialmente (el caller puede usar for-of con await para evitar OOM en iOS).
 */
(function () {
    'use strict';

    var MAX_DIM = 1600;
    var QUALITY = 0.72;
    var SMALL_BYTES = 500 * 1024;

    function isCompressible(file) {
        if (!file || typeof file !== 'object') return false;
        var type = (file.type || '').toLowerCase();
        return type === 'image/jpeg' || type === 'image/jpg' ||
               type === 'image/png'  || type === 'image/webp';
    }

    // Lee primeros bytes para extraer tag Orientation (0x0112) del APP1/Exif.
    // Retorna 1..8 (Exif Orientation) o 1 si no se encontró.
    function readExifOrientation(file) {
        return new Promise(function (resolve) {
            var reader = new FileReader();
            reader.onload = function (e) {
                try {
                    var view = new DataView(e.target.result);
                    if (view.byteLength < 4 || view.getUint16(0, false) !== 0xFFD8) return resolve(1);
                    var len = view.byteLength;
                    var offset = 2;
                    while (offset < len) {
                        if (offset + 2 > len) return resolve(1);
                        var marker = view.getUint16(offset, false);
                        offset += 2;
                        if (marker === 0xFFE1) {
                            if (offset + 8 > len) return resolve(1);
                            if (view.getUint32(offset + 2, false) !== 0x45786966) return resolve(1);
                            var little = view.getUint16(offset + 8, false) === 0x4949;
                            var ifdOffset = offset + 8 + view.getUint32(offset + 12, false);
                            if (ifdOffset + 2 > len) return resolve(1);
                            var tags = view.getUint16(ifdOffset, little);
                            for (var i = 0; i < tags; i++) {
                                var entry = ifdOffset + 2 + i * 12;
                                if (entry + 10 > len) break;
                                if (view.getUint16(entry, little) === 0x0112) {
                                    return resolve(view.getUint16(entry + 8, little));
                                }
                            }
                            return resolve(1);
                        } else if ((marker & 0xFF00) !== 0xFF00) {
                            return resolve(1);
                        } else {
                            if (offset + 2 > len) return resolve(1);
                            offset += view.getUint16(offset, false);
                        }
                    }
                    resolve(1);
                } catch (err) { resolve(1); }
            };
            reader.onerror = function () { resolve(1); };
            reader.readAsArrayBuffer(file.slice(0, 131072));
        });
    }

    function applyOrientation(ctx, orientation, w, h) {
        switch (orientation) {
            case 2: ctx.transform(-1, 0, 0, 1, w, 0); break;
            case 3: ctx.transform(-1, 0, 0, -1, w, h); break;
            case 4: ctx.transform(1, 0, 0, -1, 0, h); break;
            case 5: ctx.transform(0, 1, 1, 0, 0, 0); break;
            case 6: ctx.transform(0, 1, -1, 0, h, 0); break;
            case 7: ctx.transform(0, -1, -1, 0, h, w); break;
            case 8: ctx.transform(0, -1, 1, 0, 0, w); break;
        }
    }

    function loadImage(file) {
        return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () { URL.revokeObjectURL(url); resolve(img); };
            img.onerror = function (e) { URL.revokeObjectURL(url); reject(e); };
            img.src = url;
        });
    }

    function computeTargetSize(srcW, srcH) {
        if (srcW <= MAX_DIM && srcH <= MAX_DIM) return { w: srcW, h: srcH };
        var ratio = Math.min(MAX_DIM / srcW, MAX_DIM / srcH);
        return { w: Math.round(srcW * ratio), h: Math.round(srcH * ratio) };
    }

    function canvasToFile(canvas, origName) {
        return new Promise(function (resolve) {
            canvas.toBlob(function (blob) {
                if (!blob) return resolve(null);
                var name = (origName || 'photo').replace(/\.[^.]+$/, '') + '.jpg';
                try {
                    resolve(new File([blob], name, { type: 'image/jpeg', lastModified: Date.now() }));
                } catch (e) {
                    blob.name = name;
                    resolve(blob);
                }
            }, 'image/jpeg', QUALITY);
        });
    }

    async function compressImage(file) {
        try {
            if (!isCompressible(file)) return file;
            if (file.size < SMALL_BYTES) return file;

            var orientation = 1;
            var bitmap = null;
            if (typeof createImageBitmap === 'function') {
                try {
                    bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
                } catch (_) { bitmap = null; }
            }

            var srcW, srcH, drawSource;
            if (bitmap) {
                srcW = bitmap.width; srcH = bitmap.height; drawSource = bitmap;
            } else {
                orientation = await readExifOrientation(file);
                var img = await loadImage(file);
                srcW = img.naturalWidth; srcH = img.naturalHeight; drawSource = img;
            }

            var size = computeTargetSize(srcW, srcH);
            var swap = (orientation >= 5 && orientation <= 8);
            var canvas = document.createElement('canvas');
            canvas.width  = swap ? size.h : size.w;
            canvas.height = swap ? size.w : size.h;
            var ctx = canvas.getContext('2d');
            if (!bitmap && orientation !== 1) applyOrientation(ctx, orientation, size.w, size.h);
            ctx.drawImage(drawSource, 0, 0, size.w, size.h);

            if (bitmap && bitmap.close) bitmap.close();

            var out = await canvasToFile(canvas, file.name);
            if (!out) return file;
            if (out.size >= file.size) return file;
            return out;
        } catch (err) {
            return file;
        }
    }

    window.compressImage = compressImage;
})();
