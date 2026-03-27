# Rescatar Firmas de Representantes Legales

## Contexto

El 2026-03-25, el incidente `git clean -fd` eliminó los archivos PNG de firmas de representantes legales. La BD tiene el nombre del archivo en `tbl_clientes.firma_representante_legal` pero el archivo físico no existe.

## Fase 1: Recuperación masiva (COMPLETADA)

Se envió email a 47 clientes activos con el **Protocolo de Notificación de Trabajo en Alturas** (Resolución 4272 de 2021). El email incluye un link con token único para firmar digitalmente (canvas o subir imagen).

### Estado actual
- 47 emails enviados el 2026-03-26
- Click tracking de SendGrid desactivado por email
- BCC a edison.cuervo@cycloidtalent.com en cada envío
- Vista con 2 tabs: dibujar firma / subir imagen de firma
- La firma se guarda en `tbl_clientes.firma_representante_legal` como PNG en `uploads/firmas-alturas/`
- Token expira en 30 días

### Monitoreo
```bash
php spark firmas:protocolo-alturas --reporte
```
Envía a Edison un resumen de quién firmó y quién no.

### Reenvío a pendientes
```bash
php spark firmas:protocolo-alturas --recordatorio
```
Reenvía solo a los que NO han firmado.

## Fase 2: Guardar firma en tbl_clientes (PENDIENTE)

### Qué hacer
Cuando el representante legal firma el protocolo de alturas, la firma ya se guarda en `tbl_clientes.firma_representante_legal`. Verificar que:

1. El campo `firma_representante_legal` se actualiza con la ruta del archivo PNG
2. Los controladores que generan PDFs (contratos, actas, informes) lean la firma desde `tbl_clientes.firma_representante_legal`
3. Los PDFs que antes mostraban la firma del representante legal ahora la tomen de la ruta actualizada

### Archivos a revisar
- `app/Controllers/FirmaAlturasController.php` → método `procesarFirma()` — ya guarda en `firma_representante_legal`
- `app/Controllers/ContractController.php` → `generatePDF()` — verificar que lea desde `tbl_clientes.firma_representante_legal`
- `app/Libraries/ContractPDFGenerator.php` → verificar ruta de firma

## Fase 3: Envío automático post-firma de contrato (PENDIENTE)

### Qué hacer
Cuando un cliente nuevo firma su contrato de prestación de servicios, enviar automáticamente el email del protocolo de alturas.

### Implementación
En `app/Controllers/FirmaElectronicaController.php`, en el método que procesa la firma del contrato (después de guardar exitosamente la firma del contrato):

```php
// Después de guardar firma del contrato exitosamente
$firmaAlturasController = new \App\Controllers\FirmaAlturasController();
$firmaAlturasController->enviarProtocolo($idCliente);
```

### Flujo esperado
1. Cliente recibe link de firma de contrato
2. Cliente firma el contrato
3. Sistema guarda firma del contrato
4. **Automáticamente** se envía email del protocolo de alturas
5. Cliente firma el protocolo de alturas
6. Sistema guarda firma del representante legal en `tbl_clientes`

### Archivos a modificar
- `app/Controllers/FirmaElectronicaController.php` → agregar envío después de firma exitosa
- Verificar que `enviarProtocolo()` no duplique si el cliente ya firmó el protocolo

## Fase 4: Cron de recordatorio (PENDIENTE)

### Qué hacer
Cron diario o semanal que revise clientes activos sin firma de protocolo de alturas y notifique al consultor asignado.

### Implementación
```bash
# Cron semanal (lunes 8am)
0 8 * * 1 cd /www/wwwroot/phorizontal/enterprisesstph && php spark firmas:protocolo-alturas --recordatorio >> writable/logs/cron.log 2>&1
```

### El recordatorio debe:
- Enviar solo a clientes que NO han firmado
- Notificar al consultor asignado, no al cliente directamente
- Incluir lista de sus clientes pendientes de firma
- No enviar si todos ya firmaron

## Columnas relevantes en tbl_clientes

| Columna | Tipo | Uso |
|---------|------|-----|
| `firma_representante_legal` | varchar(255) | Ruta del archivo PNG de firma |
| `token_firma_alturas` | varchar(255) | Token único para link de firma |
| `token_firma_alturas_exp` | datetime | Expiración del token (30 días) |

## Notas técnicas

- SendGrid: usar siempre `ClickTracking->setEnable(false)` para evitar reescritura de links
- BCC: edison.cuervo@cycloidtalent.com en todo envío
- NO incluir CC al consultor en el email del protocolo (solo cliente + BCC Edison)
- Firmas se guardan en `FCPATH . 'uploads/firmas-alturas/'` como PNG
