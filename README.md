# enterprisesstph — Plataforma SST para Propiedad Horizontal

Sistema de gestión de Seguridad y Salud en el Trabajo (SST) para copropiedades y propiedad horizontal, desarrollado por **Cycloid Talent**.

## Stack tecnologico

| Componente | Tecnologia |
|-----------|------------|
| Backend | PHP 8.2 + CodeIgniter 4 |
| Base de datos | MySQL 8 (DigitalOcean Managed) |
| Servidor web | Nginx 1.24 (Ubuntu 24.04) |
| Email | SendGrid API v3 (por migrar a SMTP2Go) |
| PDF | TCPDF (contratos) + DOMPDF 3.0.0 (certificados, actas, inspecciones) |
| Excel | PhpSpreadsheet |
| IA | OpenAI GPT-4o-mini (Chat Otto, generacion de textos) |
| PWA | Modulo inspecciones (manifest + service worker) |
| Analytics | Looker Studio (embeds) |

## Modulos principales

| Modulo | Descripcion |
|--------|-------------|
| **Contratos** | Ciclo completo: creacion, firma digital, PDF, renovacion, cancelacion |
| **Plan de Trabajo Anual (PTA)** | Actividades PHVA por cliente, edicion inline, exportacion Excel, auditoria de cambios |
| **Evaluacion Estandares Minimos** | Decreto 1072, evaluacion por ciclo, historial de puntajes |
| **Actas de Visita** | Registro con fotos, firma digital, PDF, notificaciones |
| **Inspecciones (PWA)** | Locativa, extintores, botiquin, gabinetes, senalizacion, comunicaciones, recursos seguridad |
| **Capacitaciones** | Cronograma, asistencia induccion, evaluacion, reportes |
| **KPIs** | 20+ indicadores SST (frecuencia, severidad, mortalidad, ausentismo, etc.) |
| **Pendientes** | Compromisos con conteo de dias, recordatorios automaticos |
| **Plan de Saneamiento** | Limpieza, residuos, plagas, agua potable, contingencias, KPIs |
| **Documentos SGSST** | 40+ documentos normativos (politicas, programas, formatos, procedimientos) |
| **Matrices** | Riesgos, vulnerabilidad, EPP (generacion automatica desde plantillas Excel) |
| **Chat Otto (IA)** | Asistente IA con function calling, consultas SQL readonly, 3 capas de seguridad |
| **Presupuesto SST** | Categorias, items, detalle de ejecucion |
| **Informes de Avances** | Reportes mensuales con metricas e imagenes |
| **Firmas Digitales** | Firma electronica via token por email (contratos + protocolo alturas) |
| **Portal Cliente** | Dashboard readonly, chat Otto, inspecciones, reportes, pendientes |

## Roles de usuario

| Rol | Acceso |
|-----|--------|
| **admin** | Todo el sistema + gestion de usuarios + configuracion |
| **consultant** | Gestion de clientes asignados + inspecciones + chat IA completo |
| **client** | Portal readonly + chat Otto (solo SELECT) |

## Estructura del proyecto

```
enterprisesstph/
├── app/
│   ├── Commands/          # 10 comandos spark (cron jobs)
│   ├── Config/            # Routes.php, Database.php, Filters.php
│   ├── Controllers/       # ~160 controladores
│   │   └── Inspecciones/  # ~40 controladores de inspecciones (PWA)
│   ├── Filters/           # AuthFilter, ApiKeyFilter, AuthOrApiKeyFilter
│   ├── Libraries/         # 17 librerias de logica de negocio
│   ├── Models/            # ~100 modelos
│   ├── Services/          # IADocumentacionService
│   ├── SQL/               # Scripts de migracion
│   ├── Templates/         # Plantillas Excel para matrices
│   └── Views/             # Vistas PHP (Blade-like)
├── docs/                  # Documentacion tecnica
├── public/                # Punto de entrada web (index.php)
├── tools/                 # Scripts utilitarios (clasificacion PDFs, limpieza)
├── tests/                 # Tests PHPUnit
├── writable/              # Logs, cache, sesiones
├── .env                   # Variables de entorno (NO commitear)
├── deploy.sh              # Script de deploy seguro
├── composer.json          # Dependencias PHP
└── spark                  # CLI de CodeIgniter
```

## Requisitos previos

- PHP 8.2+ con extensiones: intl, mbstring, mysqlnd, curl, gd, openssl
- Composer
- MySQL 8+ (o acceso a instancia gestionada)
- Servidor web: Nginx o Apache apuntando a `public/`
- Certificado SSL para conexion a BD (si usa DigitalOcean Managed MySQL)

## Instalacion local

```bash
# 1. Clonar el repositorio
git clone https://github.com/edielestudiante2023/enterprisesstph.git
cd enterprisesstph

# 2. Instalar dependencias
composer install

# 3. Configurar variables de entorno
cp .env.example .env
# Editar .env con las credenciales locales

# 4. Configurar servidor web
# Apuntar el virtualhost a la carpeta public/

# 5. Verificar
php spark serve
# Acceder a http://localhost:8080
```

## Variables de entorno

Ver `.env.example` para la lista completa. Las principales:

| Variable | Descripcion |
|----------|-------------|
| `CI_ENVIRONMENT` | development / production |
| `app.baseURL` | URL base de la aplicacion |
| `database.default.*` | Conexion a BD principal |
| `readonly.*` | Conexion BD readonly (portal cliente) |
| `SENDGRID_API_KEY` | API key de SendGrid para emails |
| `OPENAI_API_KEY` | API key de OpenAI para Chat Otto |
| `OPENAI_MODEL` | Modelo de OpenAI (default: gpt-4o-mini) |
| `APP_API_KEY` | Token para endpoints API internos |
| `CRON_TOKEN` | Token para endpoints cron via HTTP |
| `UPLOADS_PATH` | Ruta de uploads fuera de git |

## Cron jobs

El sistema tiene 10 tareas programadas. Ver `app/Commands/` para el detalle:

| Comando | Frecuencia | Descripcion |
|---------|-----------|-------------|
| `php spark auditoria:revisar-visitas-diario` | Diario 7 AM | Verificar visitas del dia anterior |
| `php spark actas:notificaciones` | Diario 7 AM | Notificaciones de actas |
| `php spark inspecciones:resumen-pendientes` | Diario 5 PM | Inspecciones pendientes |
| `php spark visitas:recordatorio` | Diario 3 PM | Recordatorio de visitas |
| `php spark seguimiento:agenda-diario` | Diario 3 PM | Seguimiento agenda |
| `php spark firmas:protocolo-alturas --reporte` | Diario 7 AM | Reporte firmas alturas |
| `php spark contratos:resumen-semanal` | Lunes 7 AM | Resumen contratos |
| `php spark auditoria:recordatorio-sin-agendar` | L-V 7 AM | Clientes sin agendar |
| `php spark pendientes:recordatorio` | Dia 1 y 16 | Recordatorio pendientes |
| `curl .../cron/send-emails` | Lunes 8 AM | Emails programados |

## Deploy

```bash
ssh root@SERVIDOR "cd /ruta/proyecto && bash deploy.sh"
```

El script `deploy.sh` hace: stash cambios locales → git pull origin main → pop stash.

**Prohibido en produccion:** `git clean -fd`, `git checkout -- .`, `git reset --hard`.

## Documentacion

- [Auditoria del servidor](docs/AUDITORIA-SERVIDOR-enterprisesstph.md)
- [Auditoria general (transversal)](docs/AUDITORIA-SERVIDOR-GENERAL.md)
- [Mapa de base de datos](docs/MAPA-BASE-DE-DATOS-enterprisesstph.md)
- [Inventario de API Keys](docs/INVENTARIO-API-KEYS-enterprisesstph.md)
- [Hardening de infraestructura](docs/HARDENING-DE-INFRAESTRUCTURA.md)
- Documentacion de modulos en `docs/`

## Licencia

Proyecto privado de Cycloid Talent. Todos los derechos reservados.
