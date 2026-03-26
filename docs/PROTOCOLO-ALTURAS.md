# Protocolo de Notificación de Trabajo en Alturas

## Objetivo

Exonerar a Cycloid Talent SAS de responsabilidad por accidentes graves en trabajo en alturas que no hayan sido notificados formalmente por la copropiedad. El administrador (representante legal) firma digitalmente un documento donde acepta conocer el protocolo.

## Objetivo secundario

Recuperar la firma digital del representante legal (`tbl_clientes.firma_representante_legal`) que se perdió en el incidente del 2026-03-25.

## Fundamento Legal

- **Resolución 4272 de 2021** — Todo trabajo a 1.50m+ requiere curso de alturas, EPS, ARL, pensión, permiso de trabajo y EPP certificados.
- **Código Penal** — Responsabilidad del administrador si sabía del riesgo y no actuó.
- El administrador como representante legal de la copropiedad responde civil y penalmente si autoriza trabajo en alturas con personal no calificado ni afiliado.

## Flujo

### Envío masivo (una vez, para clientes existentes)

```bash
# Dry run — ver a quiénes se enviaría
php spark firmas:protocolo-alturas --dry-run

# Enviar a todos los activos que no han firmado
php spark firmas:protocolo-alturas

# Enviar a un cliente específico
php spark firmas:protocolo-alturas --id 42
```

### Envío automático (nuevos clientes)

Cuando un prospecto firma su contrato y se activa (`ContractController`, línea ~1290), se envía automáticamente el protocolo de alturas via `FirmaAlturasController::enviarProtocolo($clientId)`.

### Recordatorio al consultor

```bash
# Notifica al consultor de cada cliente que no ha firmado
php spark firmas:protocolo-alturas --recordatorio
```

Para cron semanal en el servidor:
```
0 8 * * 1 cd /www/wwwroot/phorizontal/enterprisesstph && php spark firmas:protocolo-alturas --recordatorio >> writable/logs/cron.log 2>&1
```

## Flujo del cliente

1. Recibe email con explicación del protocolo y botón "Firmar protocolo"
2. Entra a `https://phorizontal.cycloidtalent.com/protocolo-alturas/firmar/{token}`
3. Lee el protocolo en la vista
4. Dibuja su firma en el canvas
5. Confirma con SweetAlert2 (preview de la firma)
6. La firma se guarda como PNG en `uploads/firmas-representantes/firma_rep_legal_{id}_{timestamp}.png`
7. Se actualiza `tbl_clientes`:
   - `firma_representante_legal` → ruta del PNG
   - `protocolo_alturas_firmado` → 1
   - `firma_alturas_fecha` → timestamp
   - `firma_alturas_ip` → IP del firmante
   - `token_firma_alturas` → NULL (invalidado)
8. Se notifica al consultor asignado por email

## Archivos

| Archivo | Descripción |
|---------|-------------|
| `app/Controllers/FirmaAlturasController.php` | Controlador: vista firma, procesar firma, enviar email |
| `app/Views/firma_alturas/form.php` | Vista pública con canvas de firma |
| `app/Views/firma_alturas/error.php` | Vista de error (token inválido/expirado) |
| `app/Commands/ProtocoloAlturas.php` | Comando Spark para envío masivo y recordatorios |
| `app/SQL/add_token_firma_alturas.php` | Migración: columnas en tbl_clientes |
| `app/Config/Routes.php` | Rutas públicas `protocolo-alturas/*` |

## Columnas agregadas a tbl_clientes

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `token_firma_alturas` | VARCHAR(128) | Token único para el enlace de firma |
| `token_firma_alturas_exp` | DATETIME | Expiración del token (30 días) |
| `firma_alturas_fecha` | DATETIME | Fecha/hora en que firmó |
| `firma_alturas_ip` | VARCHAR(45) | IP desde donde firmó |
| `protocolo_alturas_firmado` | TINYINT(1) | 0=pendiente, 1=firmado |

## Email

**Asunto:** Actualización SG-SST: Protocolo obligatorio de notificación de trabajos en alturas

**Contenido:** Explica la Resolución 4272, los riesgos legales para el administrador, y el protocolo de notificación. Incluye botón verde para firmar.

**Destinatarios:** Correo del cliente + CC al consultor asignado.

## Integración con onboarding

En `ContractController`, cuando un prospecto se activa al firmar el contrato, se llama automáticamente a `FirmaAlturasController::enviarProtocolo($clientId)`. No requiere acción manual.
