# Seguridad — cycloidtalent.com

**Fecha diagnóstico:** 2026-03-21
**Servidor:** 66.29.154.174 (server1.cycloidtalent.com) — Ubuntu 24.04.3 LTS
**Estado actual:** 🟡 EN PROCESO DE REMEDIACIÓN

---

## 1. Síntomas reportados

| Herramienta | Alerta |
|-------------|--------|
| **FortiGuard IPS** | `http://app.cycloidtalent.com/` → Categoría: **Malicious Websites** |
| **Kaspersky Endpoint** | `https://cycloidtalent.com` → **"Dominio cuyo certificado no es de confianza"** |

> La advertencia de Kaspersky NO es por el certificado SSL (está válido, Google Trust Services, vence 2026-05-31). Es porque el dominio está en **lista negra de malware**.

---

## 2. Causa raíz confirmada — 4 Webshells activos

El atacante plantó **4 puertas traseras idénticas** en septiembre 2023 dentro de directorios vendor/tmp de Joomla, camufladas como carpetas con nombres legítimos (`cloudflare`, `nextjs`, `post-catalog`).

### Cómo funcionaba cada webshell

```
1. Login protegido por contraseña (SHA-256 hardcodeado)
2. Descargaba payload desde GitHub disfrazado como imagen .jpg
3. Ejecutaba el código con: eval(base64_decode($body))
4. El atacante tenía ejecución remota de código como usuario www
```

### Los 4 webshells encontrados

| # | Ruta | Plantado |
|---|------|----------|
| 1 | `libraries/vendor/symfony/service-contracts/Test/cloudflare/index.php` | sep 25 2023 |
| 2 | `plugins/webservices/config/src/Extension/nextjs/index.php` | sep 25 2023 |
| 3 | `libraries/vendor/joomla/database/src/post-catalog/index.php` | sep 25 2023 |
| 4 | `tmp/install_68d467a5a4660/admin/sql/uninstall/post-catalog/index.php` | sep 25 2023 |

**Todos eliminados el 2026-03-21. Backups en `/root/webshell[1-4]_backup_2026-03-21.php`**

---

## 3. Superficie de ataque identificada

| Puerto | Servicio | Estado | Riesgo |
|--------|----------|--------|--------|
| **888** | phpMyAdmin | ✅ Cerrado (2026-03-21) | Era crítico |
| **7800** | aaPanel admin | ⚠️ Aún público | Crítico |
| **8080** | HTTP sin uso | ⚠️ Aún público | Medio |
| **20/21** | FTP | ⚠️ Abierto pero sin servicio escuchando | Medio |
| **25/110/143/465/587/993/995** | SMTP/IMAP/POP3 | ⚠️ Abiertos pero sin servicio escuchando | Medio |
| **39000-40000** | FTP pasivo | ⚠️ Abierto pero sin servicio escuchando | Medio |
| **111** | rpcbind | ⚠️ Aún público | Bajo |
| **22** | SSH | OK — fail2ban activo | Normal |
| **80/443** | Apache/Web | OK | Normal |
| **3306** | MariaDB | OK — solo localhost | Normal |

> **Nota importante:** Los puertos FTP y SMTP/IMAP están abiertos en el firewall pero **ningún servicio está escuchando** en ellos. Son reglas obsoletas que se pueden eliminar sin riesgo.

### aaPanel (BT Panel)

Panel de administración de origen chino en `/www/server/panel/`. Puerto 7800 accesible desde cualquier IP del mundo. Fortinet y Kaspersky lo asocian con infraestructura de riesgo por su origen.

---

## 4. Plan de remediación

### FASE 1 — Contención inmediata

- [x] **1.1** 4 webshells eliminados — 2026-03-21
- [x] **1.2** Puerto 888 (phpMyAdmin) cerrado — no se usa (se usa DBeaver) — 2026-03-21
- [ ] **1.3** Puerto 7800 (aaPanel) — ⚠️ **PENDIENTE admin de servidores.** Debe definir desde qué IP(s) accede antes de restringir.
- [ ] **1.4** Cambiar contraseña aaPanel — ⚠️ **PENDIENTE admin de servidores.**
- [ ] **1.5** Cambiar contraseña admin Joomla — ⚠️ **PENDIENTE.** Hay intentos de brute force activos hoy (ver sección 5).

### FASE 2 — Auditoría de daños

- [x] **2.1** ClamAV scan completo post-limpieza: sin detecciones adicionales
- [x] **2.2** Búsqueda en todos los sitios del servidor: sin webshells en otros proyectos (dashboard, kpi, heroicos, phorizontal, gestor, psirysk, limesurvey, afilogro)
- [x] **2.3** Logs Joomla revisados — hay brute force activo hoy (ver sección 5)
- [ ] **2.4** Verificar usuarios admin Joomla en DB — ⚠️ **PENDIENTE.** Confirmar que el atacante no creó usuarios admin adicionales:

  ```sql
  -- Conectar a sql_cycloid con usuario sql_cycloid
  SELECT id, name, username, email, block, registerDate, lastvisitDate
  FROM m2sx6_users ORDER BY registerDate;
  ```

- [ ] **2.5** Verificar scripts cron con nombres hash (son de aaPanel, pero confirmar):

  ```
  /www/server/cron/89caf35494b865565ed4ca0e613ad365
  /www/server/cron/3ab48c27ec99cb9787749c362afae517
  /www/server/cron/a53805fa1680ad2bc5482f9abad20ebf
  /www/server/cron/9bda4865cfe8cf26266e558f46fc4f84
  /www/server/cron/a50afd8f7d0e95dc2fd7bbbcfdcccd82
  /www/server/cron/b251d846b322849d199d7e44b40e828a
  ```

### FASE 3 — Hardening

- [x] **3.1** Cerrar puertos FTP/SMTP/8080 obsoletos — ejecutado 2026-03-21. Firewall quedó con solo 22/80/443/7800:

  ```bash
  ufw delete allow 20/tcp
  ufw delete allow 21/tcp
  ufw delete allow 25/tcp
  ufw delete allow 25/udp
  ufw delete allow 110/tcp
  ufw delete allow 110/udp
  ufw delete allow 143/tcp
  ufw delete allow 143/udp
  ufw delete allow 465/tcp
  ufw delete allow 465/udp
  ufw delete allow 587/tcp
  ufw delete allow 587/udp
  ufw delete allow 993/tcp
  ufw delete allow 993/udp
  ufw delete allow 995/tcp
  ufw delete allow 995/udp
  ufw delete allow 39000:40000/tcp
  ufw delete allow 8080
  ```

- [ ] **3.2** Corregir fail2ban joomla-auth — está configurado pero NO está funcionando (0 bans pese a intentos activos). Verificar que el filtro apunte al log correcto:

  ```bash
  fail2ban-client status joomla-auth
  # El log que monitorea: /www/wwwroot/cycloidtalent/administrator/logs/error.php
  # Verificar formato del filtro en /etc/fail2ban/filter.d/joomla-auth.conf
  ```

- [ ] **3.3** Actualizar Joomla — versión actual: **5.3.4**. Verificar si es la última en joomla.org.

- [ ] **3.4** Auditar y eliminar extensiones Joomla no usadas (superficie de ataque).

- [ ] **3.5** Deshabilitar rpcbind si no se usa NFS:

  ```bash
  systemctl disable rpcbind --now
  ```

- [ ] **3.6** Habilitar 2FA en aaPanel.

- [ ] **3.7** Limpiar directorio `/www/wwwroot/cycloidtalent/tmp/` — contiene instalaciones antiguas.

### FASE 4 — Recuperación de reputación

> Hacer DESPUÉS de completar Fases 1-3. Si se solicita antes de limpiar, la re-evaluación fallará.

- [ ] **4.1** FortiGuard — solicitar re-evaluación: <https://www.fortiguard.com/webfilter>
- [ ] **4.2** Kaspersky — reportar: <https://opentip.kaspersky.com/>
- [ ] **4.3** Google Safe Browsing: <https://transparencyreport.google.com/safe-browsing/search?url=cycloidtalent.com>
- [ ] **4.4** VirusTotal: <https://www.virustotal.com/gui/domain/cycloidtalent.com>

---

## 5. Actividad sospechosa activa (hoy 2026-03-21)

Hay **brute force activo contra el admin de Joomla** en este momento:

| Hora UTC | IP atacante | Evento |
|----------|-------------|--------|
| 18:44:15 | 146.70.237.146 | Login fallido Joomla admin |
| 19:00:01 | 185.90.60.220 | Login fallido Joomla admin |

fail2ban tiene jail `joomla-auth` configurado pero tiene **0 bans** — no está funcionando correctamente. Debe corregirse (ítem 3.2).

---

## 6. Información técnica del sistema

```
Joomla:     5.3.4 — en /www/wwwroot/cycloidtalent/
Joomla DB:  host=localhost, user=sql_cycloid, db=sql_cycloid, prefix=m2sx6_
Apache:     OK (80, 443)
MariaDB:    OK — solo localhost
Memcached:  OK — solo localhost
aaPanel:    Puerto 7800 público (pendiente restringir)
phpMyAdmin: Puerto 888 CERRADO (2026-03-21)
fail2ban:   Activo — jails: apache-badbots, ftpd, joomla-auth, sshd
ClamAV:     1.4.3 activo con freshclam
```

---

## 7. Línea de tiempo de la intrusión

| Fecha | Evento |
|-------|--------|
| Sep 7-25, 2023 | Atacante planta 4 webshells en Joomla |
| Oct 8, 2023 | Alguien escanea el sitio (`/root/scan_cycloidtalent.log`) — ¿fue detectado y no se actuó? |
| Oct 8, 2023 | Existe `/root/ystemctl stop fail2ban` — posible rastro del atacante intentando detener fail2ban (typo en el comando) |
| Mar 21, 2026 | Admin de redes reporta bloqueo en Fortinet y Kaspersky |
| Mar 21, 2026 | Diagnóstico, 4 webshells eliminados, puerto 888 cerrado |

---

## 8. Pendientes por responsable

### El desarrollador puede hacer ahora

| # | Tarea | Prioridad |
|---|-------|-----------|
| 3.1 | Cerrar puertos FTP/SMTP/8080 obsoletos | Alta |
| 2.4 | Verificar usuarios admin Joomla en DB | Alta |
| 4.1-4.4 | Solicitar re-evaluación Fortinet/Kaspersky/Google/VirusTotal | Alta |
| 3.7 | Limpiar directorio tmp/ de Joomla | Media |

### Requiere coordinación con admin de servidores

| # | Tarea | Prioridad |
|---|-------|-----------|
| 1.3 | Cerrar puerto 7800 (aaPanel) | Alta |
| 1.4 | Cambiar contraseña aaPanel | Alta |
| 3.2 | Corregir fail2ban joomla-auth | Alta |
| 3.5 | Deshabilitar rpcbind | Baja |
| 3.6 | 2FA en aaPanel | Media |

### Requiere acceso a Joomla admin

| # | Tarea | Prioridad |
|---|-------|-----------|
| 1.5 | Cambiar contraseña admin Joomla | Alta |
| 3.3 | Actualizar Joomla 5.3.4 a última versión | Media |
| 3.4 | Auditar extensiones instaladas | Media |

---

*Documento generado con Claude Code — 2026-03-21*
