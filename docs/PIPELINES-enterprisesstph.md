# PIPELINES CI/CD — enterprisesstph

> Plataforma: **Gitea** con **Gitea Runner** (act_runner)
> Ubicacion de workflows: `.gitea/workflows/`

---

## Resumen

| Pipeline | Archivo | Trigger | Que hace |
|----------|---------|---------|----------|
| **Validar + Deploy QA** | `validate-and-deploy-qa.yml` | Push/PR a develop o feature/* | Tests + Trivy + Semgrep + Deploy a LXC Dev/QA |
| **Cutover Produccion** | `cutover-production.yml` | Push a main | Validacion final + Deploy a LXC Produccion (Hetzner) |

---

## Pipeline 1: Validar y Deploy a Dev/QA

**Archivo:** `.gitea/workflows/validate-and-deploy-qa.yml`

### Trigger
- Push a `develop` o `feature/*`
- Pull Request hacia `develop`

### Jobs (en orden)

```
git push → Gitea → Runner → Tests + Trivy + Semgrep → Deploy SSH → LXC (Dev/QA)
```

| # | Job | Que hace | Bloquea si falla |
|---|-----|---------|-----------------|
| 1 | **test** | `php -l` en todos los .php de app/ | Si |
| 2 | **trivy** | Escaneo de vulnerabilidades en dependencias y filesystem | Si |
| 3 | **semgrep** | Analisis estatico de seguridad (reglas PHP + secrets + security-audit) | Si |
| 4 | **secrets-scan** | Busca API keys hardcodeadas (SendGrid, OpenAI, Anthropic, DB passwords) | Si |
| 5 | **deploy-qa** | SSH al servidor QA y ejecuta deploy.sh | Solo se ejecuta en push a develop |

### Detalle de cada escaneo

**Trivy** (aquasecurity/trivy):
- Escanea el filesystem completo
- Busca vulnerabilidades HIGH y CRITICAL en dependencias
- Ignora vulnerabilidades sin fix disponible
- Si encuentra algo critico, **bloquea el deploy**

**Semgrep** (returntocorp/semgrep):
- Reglas aplicadas:
  - `p/php` — errores comunes en PHP
  - `p/security-audit` — patrones de seguridad (SQL injection, XSS, etc.)
  - `p/secrets` — credenciales hardcodeadas
- Si encuentra algo, **bloquea el deploy**

**Secrets Scan** (custom):
- Patrones buscados:
  - `SG.` + 20 chars → SendGrid API key
  - `sk-proj-` + 20 chars → OpenAI API key
  - `sk-ant-` + 20 chars → Anthropic API key
  - `AVNS_` + 10 chars → DigitalOcean DB password
- Ignora placeholders (`SG.xxxxxx`)

---

## Pipeline 2: Cutover a Produccion

**Archivo:** `.gitea/workflows/cutover-production.yml`

### Trigger
- Push a `main` (solo despues de merge de PR desde develop)

### Jobs (en orden)

```
PR develop → main → Validacion final → Trivy → Semgrep → Deploy SSH → LXC Produccion (Hetzner)
                                                                    → Verificacion post-deploy
```

| # | Job | Que hace |
|---|-----|---------|
| 1 | **validate** | Sintaxis PHP + busqueda de credenciales |
| 2 | **trivy** | Escaneo vulnerabilidades (paralelo con semgrep) |
| 3 | **semgrep** | Analisis estatico seguridad (paralelo con trivy) |
| 4 | **deploy-production** | SSH al servidor Hetzner + deploy.sh + verificacion post-deploy |

### Verificacion post-deploy
Despues del deploy, el pipeline verifica automaticamente:
- `php spark --version` responde correctamente
- HTTP responde 200 o 302 en localhost

Si la verificacion falla, el pipeline se marca como fallido y se debe investigar.

---

## Secrets necesarios en Gitea

Configurar en Gitea → Repositorio → Settings → Actions → Secrets:

### Para Dev/QA

| Secret | Descripcion |
|--------|-------------|
| `QA_HOST` | IP del servidor PVE local (Dev/QA) |
| `QA_USER` | Usuario SSH del LXC de QA |
| `QA_SSH_KEY` | Llave privada SSH para QA |
| `QA_PATH` | Ruta del proyecto en el LXC de QA |

### Para Produccion

| Secret | Descripcion |
|--------|-------------|
| `PROD_HOST` | IP del servidor Hetzner |
| `PROD_USER` | Usuario SSH del LXC de produccion |
| `PROD_SSH_KEY` | Llave privada SSH para produccion |
| `PROD_PATH` | Ruta del proyecto en el LXC de produccion |

---

## Flujo completo

```
Desarrollador trabaja en feature/xxx
        |
        v
Push a feature/xxx
        |
        v
Pipeline "Validar" se ejecuta (Tests + Trivy + Semgrep)
        |
   +----+----+
   | Falla   | Pasa
   |         v
   |    Crear PR: feature/xxx → develop
   |         |
   |         v
   |    Pipeline "Validar" se ejecuta de nuevo
   |         |
   |    +----+----+
   |    | Falla   | Pasa
   |    |         v
   |    |    Merge a develop
   |    |         |
   |    |         v
   |    |    Pipeline "Deploy QA" se ejecuta
   |    |         |
   |    |         v
   |    |    Codigo en LXC Dev/QA  <--- PROBAR AQUI
   |    |         |
   |    |    (Pruebas manuales o automatizadas en QA)
   |    |         |
   |    |         v
   |    |    Crear PR: develop → main
   |    |         |
   |    |         v
   |    |    Pipeline "Cutover Produccion" se ejecuta
   |    |         |
   |    |    +----+----+
   |    |    | Falla   | Pasa
   |    |    |         v
   |    |    |    Deploy a Hetzner LXC Produccion
   |    |    |         |
   |    |    |         v
   |    |    |    Verificacion post-deploy
   |    |    |         |
   |    |    |         v
   |    |    |    EN PRODUCCION
   |    |    |
   v    v    v
Corregir y volver a intentar
```

---

## Que hacer cuando falla un pipeline

| Error | Accion |
|-------|--------|
| **Sintaxis PHP** | Leer el log, corregir el archivo, push de nuevo |
| **Trivy (vulnerabilidad)** | Actualizar la dependencia afectada con `composer update paquete` |
| **Semgrep (seguridad)** | Leer la regla violada, corregir el patron inseguro |
| **Secrets scan** | Reemplazar hardcoded por `getenv()`/`env()`, rotar la credencial |
| **Deploy SSH falla** | Verificar secrets en Gitea, verificar acceso SSH al servidor |
| **Post-deploy falla** | Conectar manualmente al servidor y diagnosticar |
