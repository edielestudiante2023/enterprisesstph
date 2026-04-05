# AUDITORIA DEL SERVIDOR — GENERAL (TRANSVERSAL)

**Fecha de auditoría:** 2026-04-04
**Servidor:** 66.29.154.174 (server1.cycloidtalent.com)
**Acceso:** SSH como root (llave SSH configurada)

> Este documento aplica a TODOS los aplicativos alojados en este servidor. La información específica de cada aplicativo se documenta por separado.

---

## 1. Información del sistema

| Campo | Valor |
|-------|-------|
| **SO** | Ubuntu 24.04.3 LTS (Noble Numbat) |
| **Kernel** | 6.8.0-85-generic x86_64 |
| **PHP CLI** | 8.2.28 |
| **PHP-FPM** | 8.4 (servicio activo: php-fpm-84) |
| **MariaDB local** | 10.11.13 |
| **BD principal** | DigitalOcean Managed MySQL (externa) |
| **Nginx** | 1.24.0 |
| **RAM** | 1.9 GB total — 1.1 GB usada — 826 MB disponible |
| **Swap** | 1.0 GB (100% usada) |
| **Disco** | 40 GB total — 23 GB usados (62%) — 15 GB disponibles |
| **Panel** | aaPanel (BT Panel) — `/www/server/panel/` |

**ALERTA:** Swap al 100%. El servidor está al límite de memoria con 1.9 GB RAM para ~14 sitios web.

---

## 2. Sitios web alojados

| Sitio | Ruta | Tamaño | Subdominio |
|-------|------|--------|------------|
| **phorizontal** | /www/wwwroot/phorizontal/ | 4.0 GB | phorizontal.cycloidtalent.com |
| **auditorias** | /www/wwwroot/auditorias/ | 1.5 GB | auditorias.cycloidtalent.com |
| **limesurvey** | /www/wwwroot/limesurvey/ | 711 MB | forms.cycloidtalent.com |
| **dashboard** | /www/wwwroot/dashboard/ | 504 MB | dashboard.cycloidtalent.com |
| **psirysk** | /www/wwwroot/psirysk/ | 168 MB | psirysk.cycloidtalent.com |
| **tat_cycloid** | /www/wwwroot/tat_cycloid/ | 117 MB | tat.cycloidtalent.com |
| **cycloidtalent** | /www/wwwroot/cycloidtalent/ | 105 MB | cycloidtalent.com |
| **kpi** | /www/wwwroot/kpi/ | 71 MB | kpi.cycloidtalent.com |
| **gestor** | /www/wwwroot/gestor/ | 59 MB | gestor.cycloidtalent.com |
| **cycloidmanagement** | /www/wwwroot/cycloidmanagement/ | 54 MB | management.cycloidtalent.com |
| **heroicos** | /www/wwwroot/heroicos/ | 40 MB | heroicos.cycloidtalent.com |
| **info** | /www/wwwroot/info/ | 12 MB | info.cycloidtalent.com |
| **cycloidweb** | /www/wwwroot/cycloidweb/ | 144 KB | — |

**Total en disco de sitios:** ~7.2 GB

---

## 3. Certificados SSL

| Subdominio | Tipo | Vencimiento |
|-----------|------|-------------|
| phorizontal.cycloidtalent.com | CloudFlare Origin | Ago 2037 |
| dashboard.cycloidtalent.com | CloudFlare Origin | Ago 2037 |
| kpi.cycloidtalent.com | CloudFlare Origin | Ago 2037 |
| cycloidtalent.com | CloudFlare Origin | Ago 2037 |
| app.cycloidtalent.com | CloudFlare Origin | Ago 2037 |
| afilogro.cycloidtalent.com | CloudFlare Origin | Ago 2037 |
| forms.cycloidtalent.com | CloudFlare Origin | Ago 2037 |
| gestor.cycloidtalent.com | CloudFlare Origin | Ago 2037 |
| info.cycloidtalent.com | CloudFlare Origin | Ago 2037 |
| management.cycloidtalent.com | CloudFlare Origin | Ago 2037 |
| mirobot.cycloidtalent.com | CloudFlare Origin | Ago 2037 |
| n8n.cycloidtalent.com | CloudFlare Origin | Ago 2037 |
| sst-ph.cycloidtalent.com | CloudFlare Origin | Ago 2037 |
| ventas.cycloidtalent.com | CloudFlare Origin | Ago 2037 |
| auditorias.cycloidtalent.com | Let's Encrypt | May 2026 |
| heroicos.cycloidtalent.com | Let's Encrypt | May 2026 |
| psirysk.cycloidtalent.com | Let's Encrypt | Jun 2026 |
| tat.cycloidtalent.com | Let's Encrypt | Jul 2026 |

---

## 4. Servicios corriendo

| Servicio | Descripción |
|----------|-------------|
| **nginx** | Servidor web |
| **php-fpm-84** | PHP 8.4 FastCGI Process Manager |
| **memcached** | Cache en memoria |
| **clamav-freshclam** | Antivirus (actualizador de firmas) |
| **fail2ban** | Protección contra brute force |
| **containerd** | Runtime de contenedores (Docker/n8n) |
| **ssh** | Acceso remoto |
| **cron** | Tareas programadas |
| **BT-FirewallServices** | Firewall del panel aaPanel |

---

## 5. Configuración de Nginx

- **Config principal:** `/etc/nginx/nginx.conf`
- **Virtual hosts:** `/www/server/panel/vhost/nginx/`
- **Worker processes:** auto
- **Worker connections:** 768
- **Gzip:** activado
- **SSL:** TLSv1, TLSv1.1, TLSv1.2, TLSv1.3

**ALERTA DE SEGURIDAD:** TLSv1 y TLSv1.1 están habilitados. Estos protocolos son inseguros y deberían deshabilitarse.

---

## 6. Virtual hosts configurados

- 0.default.conf
- auditorias.cycloidtalent.com.conf
- cycloidtalent.com.conf
- dashboard.cycloidtalent.com.conf
- forms.cycloidtalent.com.conf
- gestor.cycloidtalent.com.conf
- heroicos.cycloidtalent.com.conf
- info.cycloidtalent.com.conf
- kpi.cycloidtalent.com.conf
- management.cycloidtalent.com.conf
- phorizontal.cycloidtalent.com.conf
- psirysk.cycloidtalent.com.conf
- tat.cycloidtalent.com.conf

---

## 7. Cron jobs del panel (aaPanel)

7 cron jobs con hashes como nombre — probablemente backups, limpieza de logs y tareas de mantenimiento del sistema gestionados desde el panel.

---

## 8. Cron jobs por aplicativo

### enterprisesstph (phorizontal)

| Frecuencia | Comando |
|------------|---------|
| Lunes 8 AM | `curl localhost/enterprisesstph/public/cron/send-emails` |
| Lunes 7 AM | `php spark contratos:resumen-semanal` |
| Diario 7 AM | `php spark auditoria:revisar-visitas-diario` |
| L-V 7 AM | `php spark auditoria:recordatorio-sin-agendar` |
| Diario 7 AM | `php spark actas:notificaciones` |
| Día 1 y 16, 8 AM | `php spark pendientes:recordatorio` |
| Diario 5 PM | `php spark inspecciones:resumen-pendientes` |
| Diario 3 PM | `php spark seguimiento:agenda-diario` |
| Diario 3 PM | `php spark visitas:recordatorio` |
| Diario 7 AM | `php spark firmas:protocolo-alturas --reporte` |

### kpi

| Frecuencia | Comando |
|------------|---------|
| Diario 7 AM | `php spark actividades:resumen-diario` |
| Diario 9 AM y 2 PM | `php spark actividades:recordatorio-revision` |
| Diario 7 AM | `php spark bitacora:resumen-diario` |
| Cada 10 min | `php spark bitacora:notificar-activas` |

### dashboard (empresas SST)

| Frecuencia | Comando |
|------------|---------|
| Día 1 y 16, 7 AM | `php spark pendientes:resumen` |

---

## 9. Archivos compartidos fuera de git

| Ruta | Tamaño | Contenido |
|------|--------|-----------|
| `/www/soportes-clientes/` | 2.9 MB | Uploads dinámicos de clientes (firmas_consultores) |
| `/www/ca/ca-certificate_cycloid.crt` | — | Certificado SSL para conexión a BD DigitalOcean |

---

## 10. Hallazgos críticos (transversales)

| # | Hallazgo | Severidad |
|---|----------|-----------|
| 1 | Swap al 100% — servidor al límite de memoria | ALTA |
| 2 | TLSv1 y TLSv1.1 habilitados en nginx | ALTA |
| 3 | 14 sitios web en un solo servidor de 1.9 GB RAM | ALTA |
| 4 | PHP CLI 8.2 vs PHP-FPM 8.4 (versiones distintas) | BAJA |
| 5 | Panel aaPanel como punto único de administración | MEDIA |
