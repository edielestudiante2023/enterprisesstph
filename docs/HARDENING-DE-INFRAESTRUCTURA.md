# HARDENING DE INFRAESTRUCTURA

## Propuesta de Migración y Hardening — Cycloid Talent 2026

Consultoría técnica para transformar la infraestructura web de un estado comprometido a un entorno seguro, controlado y escalable.

---

## Estado actual

### Infraestructura comprometida
- Servidor principal hackeado con webshell activo desde ~2023
- Todo el código, PDFs y credenciales vivían en el mismo servidor
- 1 droplet adicional (n8n + OpenClaw) + base de datos gestionada en DigitalOcean

### Deuda técnica severa
- CMS Joomla desactualizado fue el vector de ataque
- Sin entorno de desarrollo separado — se editaba código directamente en producción
- Repositorios de código en GitHub público

### Costos sin control
- $321.832 COP/mes entre DigitalOcean, SendGrid, Namecheap y un LMS externo pagado que no se usa
- Sin WAF, sin backups verificados, sin CI/CD

---

## Riesgos en juego

### Datos expuestos ~2 años
- Datos personales de trabajadores, residentes y empresas clientes potencialmente accesibles desde 2023
- Credenciales de todas las aplicaciones comprometidas

### Continuidad del negocio
- Sin infraestructura segura, cada incidente pone en riesgo la operación de los clientes que dependen de los sistemas SST

### Nota legal
- **Ley 1581 de 2012** — Colombia: las brechas de datos personales deben reportarse a la SIC
- El módulo de Riesgo Psicosocial está adicionalmente regulado por la **Resolución 2404 de 2019** del Ministerio del Trabajo

---

## Nueva arquitectura — Dos nodos, cero puntos únicos de falla

| Capa | Descripción |
|------|-------------|
| **Capa Externa** | Internet → Cloudflare WAF y CDN protegen tráfico |
| **Capa de Aplicación** | Hetzner (DE): SST, Gestión Propiedades, Riesgo Psicosocial |
| **Capa de Soporte** | Oracle Cloud ARM: Moodle, encuestas y automatización |
| **Entorno Local** | Servidor físico para desarrollo y pruebas |

---

## Herramientas seleccionadas

| Capa | Herramienta | Beneficio clave | Exposición pública |
|------|-------------|-----------------|-------------------|
| Seguridad | Cloudflare Tunnel + WAF | IP del servidor invisible. DDoS gratis | No |
| Seguridad aplicación | BunkerWeb (WAF L7) | Bloqueo de ataques web antes de llegar al código | No |
| Cómputo y aislamiento | Incus (contenedores) | Cada app en su propio espacio — un fallo no afecta a las demás | No |
| Automatización | n8n | Hub de integraciones: LimeSurvey → apps, OpenClaw → clientes | No |
| Sitio corporativo | Ghost CMS | Panel admin sin código. SEO excelente de base. Elimina Joomla | Sí |

---

## Comparación de costos

### ANTES — $321.832 COP/mes
- DigitalOcean: $140.000/mes
- SendGrid: $61.000/mes
- Namecheap hosting: $35.416/mes
- LMS externo: $80.000/mes

*Sin WAF. Sin backups. Sin control.*

### DESPUÉS — ~$57.416 COP/mes
- Hetzner (servidor principal): ~$50.000/mes
- Oracle Cloud ARM (servicios soporte): $0
- Cloudflare (WAF + CDN + Tunnel): $0
- SMTP2Go (correo transaccional): $0

*Con doble WAF. Con backups diarios. Con control total.*

### Ahorro de ~$3.17 millones COP al año

---

## Cronograma — 10 semanas sin interrumpir el servicio

| Fase | Semana | Actividad |
|------|--------|-----------|
| 1 | Semana 1 (AHORA) | Repos GitHub privados + reemplazar SendGrid por SMTP2Go → ahorro inmediato de $61.000/mes |
| 2 | Semana 2 | Construcción de la nueva infraestructura (Hetzner + Oracle Cloud) |
| 3 | Semanas 3–5 | Migración de servicios no críticos (sitio web, LMS, formularios, automatización) |
| 4 | Semanas 6–7 | Migración de herramientas internas y módulos secundarios |
| 5 | Semanas 8–9 | Migración de productos críticos (SST, Propiedad Horizontal, Psicosocial) — lo último y con mayor cuidado |
| 6 | Semana 10 | Apagado de servidores legacy — cancelar DigitalOcean y Namecheap hosting |

---

## Deploy: El código llega a producción por Git, no por SSH

### ANTES
- Acceso root directo al servidor de producción
- Edición de archivos en vivo — un error baja el sistema
- Repositorios públicos en GitHub — credenciales potencialmente expuestas
- Sin historial confiable de cambios
- Sin entorno de pruebas

### DESPUÉS
- El desarrollador solo tiene acceso a Git
- Todo cambio pasa por revisión automática (análisis de seguridad, verificación de sintaxis)
- El código llega a un entorno de pruebas local primero, luego a producción con aprobación
- Repositorios privados con protección de rama principal

---

## Entregables al finalizar

- **Infraestructura nueva en producción** — Hetzner + Oracle Cloud con aislamiento por contenedor
- **Sin exposición directa** — IP de los servidores nunca visible en internet
- **Backups diarios cifrados** — Verificados y automatizados hacia almacenamiento externo
- **CI/CD funcionando** — El código llega a producción de forma automática y controlada
- **Entorno de desarrollo local** — Servidor físico espejo de producción para pruebas sin riesgo
- **Documentación completa** — Cada componente documentado para operación y auditorías futuras

---

## Inversión

| Concepto | Valor |
|----------|-------|
| Costo de infraestructura mensual (a cargo del cliente) | ~$57.416 COP/mes |
| Honorarios de consultoría (proyecto completo) | A convenir |
| Soporte mensual (opcional) | A convenir |
| Duración estimada | 10 semanas |

**Próximo paso:** Confirmar fecha de inicio y provisionar el servidor Hetzner.

> *"El costo de no actuar ya se pagó — en datos expuestos. La pregunta ahora no es si migrar, sino cuándo y con quién."*

---
---

# SEGUNDA PRESENTACION: DE SOBREVIVIR PROGRAMANDO A TRABAJAR COMO UN PROFESIONAL

*Por qué tu código funciona... pero tu forma de trabajar no.*

---

## Cómo trabajas hoy

1. **Tu PC con XAMPP** — Desarrollas directamente en tu máquina local sin aislamiento
2. **GitHub público** — Subes código sin control, incluyendo credenciales expuestas
3. **VPS de producción** — Deploy manual directamente al entorno en producción

> El flujo es simple: **si algo funciona, se sube. Si se rompe, se arregla en producción.** No hay intermediarios, no hay controles, no hay segunda oportunidad.

---

## Problemas reales (sin filtro)

### Exposición de credenciales
Tus claves de API, bases de datos y tokens están en GitHub público. Cualquiera puede verlos con un simple `git clone`.

### Cambios sin control
Cada commit es una apuesta. No sabes exactamente qué versión está corriendo en producción en este momento.

### Producción inestable
Caídas constantes porque no hay entorno de pruebas. Tu VPS es básicamente tu entorno de pruebas.

### Sin rollback posible
Si algo se rompe, no puedes volver atrás fácilmente. Estás obligado a arreglarlo en caliente, bajo presión.

> **No tienes un sistema. Tienes una improvisación que aún no ha explotado del todo.**

---

## Riesgo de seguridad: Ya te hackearon

### Lo que pasó
- Repositorio público con credenciales expuestas
- VPS comprometido previamente
- APIs accesibles sin autenticación
- Datos sensibles en el código

### La realidad
> No es cuestión de si te hackean... es cuestión de cuándo. Robots automatizados buscan credenciales expuestas 24/7.

> Cada segundo que tu código esté en público con claves es un segundo que alguien puede estar explotando tu infraestructura. Y cuando pase, no será una sorpresa... será la consecuencia de tus decisiones.

---

## Qué hace un desarrollador serio

- **Separación de entornos** — Dev / QA / Prod aislados. Cada uno con su propósito específico
- **Repositorios privados** — Control total del acceso. Solo quien debe ver, ve
- **Control de versiones** — Git bien usado, con ramas significativas y commits atómicos
- **Pruebas antes de producción** — Nada llega a producción sin pasar por validación

---

## Nueva arquitectura profesional

### Flujo: Desarrollo → Contenedor LXC → Gitea Privado → CI/CD Automático → Producción

| Paso | Componente | Descripción |
|------|-----------|-------------|
| 01 | **Desarrollo aislado** | PVE con LXC para entorno de desarrollo consistente y reproducible |
| 02 | **Código centralizado** | Gitea privado para control total del acceso y versiones |
| 03 | **Validación automática** | CI/CD ejecuta pruebas automáticamente en cada commit |
| 04 | **Deploy controlado** | Producción en Hetzner solo recibe código que pasó todas las pruebas |

### Modelo propuesto (profesional)

```
PVE (Infraestructura Local)
├── LXC Dev / QA (Ambientes controlados)
│   ├── Dev > act_runner + Docker
│   └── QA > LXC con cada proyecto
├── Gitea Privado (Repos seguros)
│   └── Pipeline CI/CD (Test + Validación)
│       └── PVE en Hetzner (Producción) — Solo código validado
```

---

## Ramas: Explicación para novatos

| Rama | Propósito |
|------|-----------|
| **main** | Código estable, listo para producción. Cada commit es una versión publicable |
| **develop** | Código en integración. Donde se unen todos los cambios antes de llegar a producción |
| **feature/** | Nuevas funcionalidades. Trabajas en aislamiento hasta que esté lista |
| **hotfix/** | Correcciones urgentes en producción. Prioridad sobre todo lo demás |

> **Si trabajas todo en una sola rama, no estás versionando... estás apostando.**

---

## CI/CD: El guardián automático

### Ciclo: Push → Pipeline → Validación → Deploy

1. **Push** — Subes tu código al repositorio
2. **Pipeline** — CI/CD ejecuta pruebas automáticamente
3. **Validación** — Si falla, no llega a producción
4. **Deploy** — Solo código aprobado se despliega

> El CI/CD es tu seguridad. Ejecuta pruebas automáticamente, valida el código y evita que errores lleguen a producción. Si tu código solo se prueba cuando lo ve el usuario, ya fallaste.

---

## Beneficios reales

- **Menos errores en producción** — Validación automática atrapa problemas antes de que lleguen a usuarios
- **Seguridad** — Repositorios privados y credenciales protegidas
- **Orden** — Ramas claras, commits significativos, historial rastreable
- **Escalabilidad** — Infraestructura que crece sin romperse
- **Menos estrés** — No trabajas en modo crisis constante

---

## Mensaje final

| El camino actual | El camino profesional |
|-----------------|----------------------|
| Comodidad temporal | Trabajo estructurado |
| Caídas constantes | Estabilidad real |
| Riesgo de seguridad | Seguridad |
| Código inconsistente | Código limpio |
| Dependencia total | Colaboración |
| Imposibilidad de escalar | Escalabilidad |

> **Seguir como estás es cómodo... hasta que deja de serlo.**
>
> Ser desarrollador no es hacer que funcione... es hacerlo **bien**. La diferencia entre un hobby y una profesión está en cómo trabajas, no en qué logras.
>
> El momento de cambiar es ahora. No cuando todo explote. No cuando pierdas datos importantes. No cuando te hackeen. **Ahora.**
