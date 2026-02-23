# 11 - Input File: Camara y Galeria en Movil

## Estado: RESUELTO (2026-02-23)

Bug compartido entre Acta de Visita e Inspeccion Locativa.

---

## El Bug

Al tocar el input de foto en el celular, se abria **solo la camara** sin dar opcion de seleccionar desde la **galeria de fotos**.

### Causa raiz

El atributo HTML `capture="environment"` le dice al navegador movil que abra directamente la camara trasera, saltandose el chooser nativo del sistema operativo.

```html
<!-- MAL: abre solo la camara, no permite galeria -->
<input type="file" accept="image/*" capture="environment">
```

### Comportamiento de `capture` por plataforma

| Valor | Android Chrome | iOS Safari |
|-------|---------------|------------|
| `capture="environment"` | Abre camara trasera directo | Abre camara directo |
| `capture="user"` | Abre camara frontal directo | Abre camara directo |
| **Sin `capture`** | **Muestra chooser: Camara / Archivos / Galeria** | **Muestra chooser: Camara / Galeria** |

---

## La Solucion

Quitar el atributo `capture`. Con solo `accept="image/*"` el navegador presenta el chooser nativo que permite elegir entre camara y galeria.

```html
<!-- BIEN: muestra chooser con opcion de camara Y galeria -->
<input type="file" accept="image/*">
```

### Para seleccion multiple (fotos de acta):

```html
<input type="file" accept="image/*" multiple>
```

---

## Archivos corregidos

| Archivo | Ocurrencias removidas |
|---------|----------------------|
| `app/Views/inspecciones/acta_visita/form.php` | 1 (fotos[]) |
| `app/Views/inspecciones/inspeccion_locativa/form.php` | 6 (hallazgo_imagen[], hallazgo_correccion[] x3 bloques: PHP existente, JS template nuevo, JS template autoguardado) |

---

## Regla para futuros modulos

**NUNCA usar `capture="environment"` ni `capture="user"`** en inputs de foto de inspecciones. El consultor necesita poder:

1. **Tomar foto nueva** con la camara (en campo)
2. **Seleccionar de galeria** (fotos tomadas antes, screenshots, documentos)

Solo `accept="image/*"` garantiza ambas opciones en todos los navegadores moviles.

---

## Cuando SI usar capture

El unico caso valido seria si el input es **exclusivamente** para captura en vivo (ej: firma digital, scanner QR). En inspecciones, siempre se necesitan ambas opciones.
