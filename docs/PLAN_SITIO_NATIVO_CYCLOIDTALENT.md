# Plan de Proyecto — cycloidtalent.com (Sitio Nativo)

**Fecha:** 2026-03-21
**Estado:** 🟢 En ejecución — Fase 2 en progreso
**Objetivo:** Reemplazar Joomla con un sitio propio en CodeIgniter 4, moderno, administrable igual que los demás proyectos del ecosistema Cycloid.

---

## 1. Decisión tecnológica

### Stack

| Capa | Tecnología | Razón |
|------|-----------|-------|
| Framework | **CodeIgniter 4 v4.7.0** | Mismo stack que enterprisesstph, enterprisesst |
| CSS | **TailwindCSS v3** | Utilidades, diseño moderno, fácil de mantener |
| JS | **Alpine.js v3** | Interactividad ligera (menú móvil, carrusel, acordeón) |
| Email | **SendGrid API v3** | Ya lo usan en los otros proyectos |
| Base de datos | **Ninguna** | Sitio brochure — contenido en vistas PHP |
| Blog | **Archivos PHP estáticos** | 7 artículos + nuevos. Un archivo por artículo en `app/Views/blog/articulos/` |
| Hosting | **Mismo servidor** (66.29.154.174) | Nuevo vhost Apache, misma IP |
| Git | **GitHub** — `edielestudiante2023/cycloidtalent` | Mismo flujo: commit → push → deploy |

### Decisiones confirmadas

- **Clientes:** solo grid de logos (sin testimonios)
- **Blog:** activo — 7 artículos migrados + seguirán publicando nuevos
- **Sin base de datos** — decisión de arquitectura, no cambiar sin consultar
- **Redirects 301** desde URLs Joomla — obligatorios para preservar SEO

---

## 2. Inventario de contenido a migrar

### Páginas (14 en total)

| Ruta nueva | Ruta Joomla actual | Estado |
|-----------|-------------------|--------|
| `/` | `/` | ✅ Hecha |
| `/nosotros` | `/inicio/mision-y-vision` | ⬜ Pendiente |
| `/servicios/consultoria-sst` | `/consultoria-sst` | ⬜ Pendiente |
| `/servicios/riesgo-psicosocial` | `/riesgo-psicosocial` | ⬜ Pendiente |
| `/servicios/propiedad-horizontal` | `/sg-sst-propiedad-horizontal` | ⬜ Pendiente |
| `/servicios/brigada-emergencia` | `/inicio/brigada-emergencia` | ⬜ Pendiente |
| `/servicios/auditoria-proveedores` | `/auditoria-sg-sst-proveedores` | ⬜ Pendiente |
| `/servicios/vigia-sst` | `/vigia-sst` | ⬜ Pendiente |
| `/clientes` | `/clientes-cycloid-talent` | ⬜ Pendiente |
| `/blog` | (blog Joomla) | ⬜ Pendiente |
| `/blog/:slug` | (artículos Joomla) | ⬜ Pendiente (7 archivos) |
| `/contacto` | (formulario Joomla) | ⬜ Pendiente |
| `/legal/reglamento-interno` | `/inicio/reglamento-interno-de-trabajo` | ⬜ Pendiente |
| `/legal/reglamento-higiene` | `/inicio/life-style` | ⬜ Pendiente |

### Assets — estado

| Asset | Estado |
|-------|--------|
| Fotos equipo (Edison, Diana, Eleyson, Natalia) | ✅ Copiados a `public/assets/img/team/` |
| Logos Cycloid (azul, blanco) | ✅ Copiados a `public/assets/img/logos/` |
| Logos clientes (Meltec, Polux, client1-4) | ✅ Copiados a `public/assets/img/clients/` |
| Imágenes RPS 2026 (16 fotos) | ✅ Copiados a `public/assets/img/services/rps-2026/` |
| Imágenes metodología Psicloid (4) | ✅ Copiados a `public/assets/img/services/` |
| PDF Portafolio RPS 2026 | ⚠️ No encontrado en backup — pendiente |
| Contenido 7 artículos blog | ⚠️ Están en DB servidor — pendiente extraer |
| YouTube URL | ⚠️ Pendiente — no proporcionado |

### Datos corporativos

```
Razón social:  CYCLOID TALENT SAS
NIT:           901653912-2
Dirección:     Calle 13 # 31-106, Soacha, Cundinamarca
Teléfono:      3229074371
Email:         diana.cuestas@cycloidtalent.com
Facebook:      https://www.facebook.com/CycloidTalent
LinkedIn:      https://co.linkedin.com/company/cycloid-talent
Instagram:     https://www.instagram.com/cycloid_talent
TikTok:        https://www.tiktok.com/@cycloid_talent
YouTube:       ⚠️ pendiente
```

---

## 3. Diseño

### Identidad visual

| Elemento | Valor |
|----------|-------|
| Color principal | `#0345BF` (azul Cycloid) |
| Color oscuro | `#0A1628` (navy) |
| Color acento | `#00C6FF` (celeste) |
| Color texto | `#1E293B` |
| Color fondo | `#F8FAFC` |
| Tipografía | Inter (Google Fonts) |
| Estilo | Moderno, minimalista, B2B |

---

## 4. Arquitectura real del proyecto

```
c:\xampp\htdocs\cycloidtalent\        ← directorio local
├── app/
│   ├── Controllers/
│   │   ├── HomeController.php         ✅
│   │   ├── NosotrosController.php     ✅
│   │   ├── ServiciosController.php    ✅
│   │   ├── BlogController.php         ✅
│   │   ├── ContactoController.php     ✅
│   │   └── LegalController.php        ✅
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── main.php               ✅
│   │   │   └── legal.php              ⬜ pendiente
│   │   ├── partials/
│   │   │   ├── navbar.php             ✅
│   │   │   ├── footer.php             ✅
│   │   │   └── contact_cta.php        ⬜ pendiente
│   │   ├── home/index.php             ✅
│   │   ├── nosotros/index.php         ⬜
│   │   ├── servicios/
│   │   │   ├── consultoria-sst.php    ⬜
│   │   │   ├── riesgo-psicosocial.php ⬜
│   │   │   ├── propiedad-horizontal.php ⬜
│   │   │   ├── brigada-emergencia.php ⬜
│   │   │   ├── auditoria-proveedores.php ⬜
│   │   │   └── vigia-sst.php          ⬜
│   │   ├── blog/
│   │   │   ├── index.php              ⬜
│   │   │   └── articulos/             ⬜ (7 archivos)
│   │   ├── clientes/index.php         ⬜
│   │   ├── contacto/index.php         ⬜
│   │   └── legal/
│   │       ├── reglamento-interno.php ⬜
│   │       └── reglamento-higiene.php ⬜
│   └── Config/Routes.php              ✅ (todas las rutas + 7 redirects 301)
├── public/
│   ├── assets/
│   │   ├── css/input.css + output.css ✅
│   │   ├── js/main.js (Alpine.js)     ✅
│   │   ├── img/team/                  ✅ (4 fotos)
│   │   ├── img/clients/               ✅ (6 logos)
│   │   ├── img/logos/                 ✅ (Cycloid logos)
│   │   ├── img/services/              ✅ (RPS 2026 + Psicloid)
│   │   └── pdf/                       ⬜ (portafolio-rps-2026.pdf)
│   └── .htaccess                      ✅
├── .env                               ✅ (baseURL local)
├── .gitignore                         ✅
├── package.json                       ✅ (npm run dev / build)
└── tailwind.config.js                 ✅
```

---

## 5. Rutas (todas implementadas)

```php
// Principales
GET  /                               → HomeController::index         ✅
GET  /nosotros                       → NosotrosController::index
GET  /servicios/consultoria-sst      → ServiciosController::consultoriaSst
GET  /servicios/riesgo-psicosocial   → ServiciosController::riesgoPsicosocial
GET  /servicios/propiedad-horizontal → ServiciosController::propiedadHorizontal
GET  /servicios/brigada-emergencia   → ServiciosController::brigadaEmergencia
GET  /servicios/auditoria-proveedores→ ServiciosController::auditoriaProveedores
GET  /servicios/vigia-sst            → ServiciosController::vigilaSst
GET  /clientes                       → HomeController::clientes
GET  /blog                           → BlogController::index
GET  /blog/(:segment)                → BlogController::articulo/$1
GET  /contacto                       → ContactoController::index
POST /contacto/enviar                → ContactoController::enviar
GET  /legal/reglamento-interno       → LegalController::reglamentoInterno
GET  /legal/reglamento-higiene       → LegalController::reglamentoHigiene

// Redirects 301 SEO (URLs viejas de Joomla)
GET /inicio/mision-y-vision          → 301 /nosotros
GET /consultoria-sst                 → 301 /servicios/consultoria-sst
GET /riesgo-psicosocial              → 301 /servicios/riesgo-psicosocial
GET /sg-sst-propiedad-horizontal     → 301 /servicios/propiedad-horizontal
GET /clientes-cycloid-talent         → 301 /clientes
GET /auditoria-sg-sst-proveedores    → 301 /servicios/auditoria-proveedores
GET /vigia-sst                       → 301 /servicios/vigia-sst
```

---

## 6. Formulario de contacto

SendGrid API v3 vía cURL — implementado en `ContactoController::enviar()`:

```
Campos:    Nombre, Empresa, Teléfono, Servicio de interés, Mensaje
Destino:   diana.cuestas@cycloidtalent.com
Remitente: noreply@cycloidtalent.com
API Key:   env('SENDGRID_API_KEY') en .env del servidor
```

---

## 7. Plan de ejecución — estado actual

### Fase 1 — Setup local ✅ COMPLETA

- [x] 1.1 Crear proyecto CI4 v4.7.0 en `c:\xampp\htdocs\cycloidtalent\`
- [x] 1.2 Configurar Tailwind v3 + Alpine.js v3
- [x] 1.3 Layout base `main.php` con navbar y footer
- [x] 1.4 GitHub: `edielestudiante2023/cycloidtalent` — rama `main`
- [x] 1.5 URL local: `http://localhost/cycloidtalent/public/` (sin vhost, igual que enterprisesstph)

### Fase 2 — Componentes y Home 🟡 EN PROGRESO

- [x] 2.1 Navbar responsive (dropdown + menú hamburguesa Alpine.js)
- [x] 2.2 Footer (links, redes sociales, datos legales)
- [x] 2.3 Home completa (hero, servicios, clientes, por qué elegirnos, blog reciente, CTA)
- [ ] 2.4 Página Nosotros (equipo, misión, visión, principios)

### Fase 3 — Páginas de servicios ⬜ PENDIENTE

- [ ] 3.1 Consultoría SG-SST
- [ ] 3.2 Riesgo Psicosocial (+ portafolio descargable RPS 2026)
- [ ] 3.3 Propiedad Horizontal (+ links a phorizontal.cycloidtalent.com y Chamilo)
- [ ] 3.4 Brigada de Emergencia
- [ ] 3.5 Auditoría Proveedores (checklist 28 ítems)
- [ ] 3.6 Vigía SST (+ link a Google Form de registro)

### Fase 4 — Páginas complementarias ⬜ PENDIENTE

- [ ] 4.1 Página Clientes (grid de logos ~11)
- [ ] 4.2 Blog — listado de artículos
- [ ] 4.3 Blog — 7 artículos individuales (extraer contenido de DB Joomla)
- [ ] 4.4 Formulario de Contacto (vista + prueba SendGrid)
- [ ] 4.5 Reglamento Interno de Trabajo (22 capítulos)
- [ ] 4.6 Reglamento de Higiene y Seguridad (8 artículos)

### Fase 5 — Deploy y eliminación de Joomla ⬜ PENDIENTE

- [ ] 5.1 Crear vhost en servidor: `cycloidtalent.com` → `/www/wwwroot/cycloidtalent/public/`
- [ ] 5.2 Clonar repo en servidor: `git clone https://github.com/edielestudiante2023/cycloidtalent.git`
- [ ] 5.3 Instalar dependencias en servidor: `composer install --no-dev` + `npm run build`
- [ ] 5.4 Configurar `.env` de producción (baseURL, SendGrid key)
- [ ] 5.5 Verificar SSL (certbot ya configurado)
- [ ] 5.6 Probar todas las rutas y redirects 301
- [ ] 5.7 Backup Joomla: `tar -czf joomla_backup_2026-03-21.tar.gz /www/wwwroot/cycloidtalent/`
- [ ] 5.8 Apuntar dominio al nuevo proyecto
- [ ] 5.9 Verificar sitio en vivo
- [ ] 5.10 Eliminar Joomla: `rm -rf /www/wwwroot/cycloidtalent/` (solo después de verificar)
- [ ] 5.11 Eliminar DB: `DROP DATABASE sql_cycloid; DROP USER 'sql_cycloid'@'localhost';`

---

## 8. Flujo de trabajo

```
Editar en VS Code → http://localhost/cycloidtalent/public/
    ↓
npm run build  (recompilar Tailwind si se agregan clases nuevas)
    ↓
git add . → git commit → git push origin main
    ↓
ssh -i ~/.ssh/id_ed25519 root@66.29.154.174
"cd /www/wwwroot/cycloidtalent && git pull origin main"
    ↓
cycloidtalent.com actualizado
```

---

## 9. Lo que desaparece al eliminar Joomla

| Se elimina | Impacto |
|------------|---------|
| 4 webshells (ya eliminados) | Puerta trasera cerrada |
| Base de datos `sql_cycloid` | -1 DB expuesta |
| Usuario MySQL `sql_cycloid` | -1 usuario con credenciales en disco |
| ~500MB de archivos Joomla | Servidor más limpio |
| Superficie de ataque PHP pública | Eliminada |
| Necesidad de actualizar Joomla | -0 mantenimiento CMS |

---

## 10. Lo que NO cambia

- Dominio `cycloidtalent.com`
- Subdominios: `dashboard.`, `phorizontal.`, `kpi.`, etc.
- Correo `diana.cuestas@cycloidtalent.com`
- El servidor físico (66.29.154.174)
- El SEO (redirects 301 preservan posicionamiento)

---

*Documento actualizado con Claude Code — 2026-03-21*
