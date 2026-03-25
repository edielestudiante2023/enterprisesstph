# Baja de Joomla — cycloidtalent.com
**Fecha:** 2026-03-21
**Responsable:** Equipo Cycloid Talent
**Servidor:** 66.29.154.174

---

## Resumen

El sitio web `cycloidtalent.com` fue migrado de **Joomla** a un sitio nativo en **CodeIgniter 4**. El directorio original de Joomla fue renombrado a `cycloidtalent_old` y el nuevo sitio fue desplegado y verificado en producción el mismo día.

---

## ¿Por qué se dio de baja Joomla?

### 1. Compromiso de seguridad previo
Se detectaron **4 webshells** en el directorio de Joomla (`/www/wwwroot/cycloidtalent/`). Estos archivos maliciosos habían sido cargados a través de vulnerabilidades en extensiones o el núcleo de Joomla, otorgando a atacantes acceso remoto al sistema de archivos del servidor. Los webshells fueron eliminados, pero la superficie de ataque permanecía abierta.

### 2. Base de datos expuesta innecesariamente
Joomla requería una base de datos MySQL (`sql_cycloid`) y un usuario con credenciales almacenadas en disco (`configuration.php`). Para un sitio de tipo brochure que no maneja transacciones ni usuarios, esto representaba un riesgo injustificado.

### 3. Mantenimiento continuo de CMS
Joomla requiere actualizaciones frecuentes del núcleo y de extensiones. Cada versión desactualizada abre una ventana de explotación. El equipo no disponía de un proceso formal de actualización, por lo que el sitio acumulaba deuda de seguridad con el tiempo.

### 4. Extensiones de terceros como vector de ataque
Las extensiones/plugins de Joomla son históricamente el principal vector de infección en sitios WordPress/Joomla. Eliminar el CMS elimina completamente esta superficie de ataque.

### 5. Peso y complejidad innecesarios
El directorio de Joomla ocupaba aproximadamente **500 MB** en disco. El nuevo sitio CI4 ocupa menos de 10 MB (sin vendor). Para un sitio de 12 páginas estáticas, esto era desproporcionado.

---

## Qué se eliminó

| Elemento | Estado |
|----------|--------|
| Directorio `/www/wwwroot/cycloidtalent/` (Joomla) | Renombrado a `cycloidtalent_old` — pendiente de eliminar |
| 4 webshells en el directorio | Ya eliminados previamente |
| Base de datos `sql_cycloid` | Pendiente de eliminar |
| Usuario MySQL `sql_cycloid` | Pendiente de eliminar |

---

## Qué NO cambió

| Elemento | Estado |
|----------|--------|
| Dominio `cycloidtalent.com` | Sin cambios |
| Subdominios (`dashboard.`, `phorizontal.`, `kpi.`, etc.) | Sin cambios |
| Certificado SSL | Sin cambios (mismo certbot) |
| Servidor físico | Sin cambios |
| Correo `diana.cuestas@cycloidtalent.com` | Sin cambios |
| SEO | Preservado con redirects 301 desde URLs antiguas de Joomla |

---

## Nuevo stack

| Capa | Tecnología |
|------|-----------|
| Framework | CodeIgniter 4 |
| CSS | TailwindCSS v3 (compilado, sin build en servidor) |
| JS | Alpine.js (CDN) |
| Base de datos | **Ninguna** — sitio brochure, contenido en vistas PHP |
| Email | SendGrid API v3 |
| Deploy | `git pull` desde GitHub (`edielestudiante2023/cycloidtalent`) |

---

## Ruta en servidor

```
/www/wwwroot/cycloidtalent/          ← nuevo sitio CI4 (ACTIVO)
/www/wwwroot/cycloidtalent_old/      ← Joomla (INACTIVO — eliminar cuando se confirme estabilidad)
```

---

## Pasos pendientes para el administrador

1. **Verificar estabilidad** del nuevo sitio durante 7 días.
2. **Eliminar directorio Joomla** una vez confirmado:
   ```bash
   rm -rf /www/wwwroot/cycloidtalent_old/
   ```
3. **Eliminar base de datos y usuario Joomla**:
   ```sql
   DROP DATABASE sql_cycloid;
   DROP USER 'sql_cycloid'@'localhost';
   FLUSH PRIVILEGES;
   ```
4. **Confirmar** que ningún subdominio o script depende de la carpeta `cycloidtalent_old`.

---

## Flujo de deploy del nuevo sitio

```bash
# En servidor de producción:
cd /www/wwwroot/cycloidtalent
git pull origin main
# No requiere composer install salvo que cambien dependencias
```

---

*Documento generado por el equipo técnico de Cycloid Talent — 2026-03-21*
