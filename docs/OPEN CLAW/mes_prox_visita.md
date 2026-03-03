# Modulo Auditoria de Visitas — tbl_ciclos_visita

## Proposito

Controlar y auditar si los consultores cumplen con las visitas agendadas a cada cliente.
Cruza automaticamente tbl_agendamientos (citas programadas) con tbl_acta_visita (visitas reales).

## Dos niveles de cumplimiento

### Nivel 1: Agenda (dia especifico)

El consultor se comprometio a visitar al cliente en una fecha exacta.
Un cron diario revisa al dia siguiente:

- Si hay acta del mismo dia: estatus_agenda = cumple
- Si NO hay acta: estatus_agenda = incumple, se envia email alerta

### Nivel 2: Mes (periodo mensual)

El cliente tiene un mes esperado de visita (mes_esperado).
Al cierre: si existe ALGUNA acta en ese mes, el mes cumple aunque la agenda del dia haya fallado.

### Ejemplo

| Cliente | Agendado | Acta real | Agenda dia | Mes |
| --- | --- | --- | --- | --- |
| Conjunto X | 10/03 | 10/03 | cumple | cumple |
| Conjunto Y | 10/03 | 15/03 | incumple | cumple |
| Conjunto Z | 10/03 | nunca | incumple | incumple |

## Tabla: tbl_ciclos_visita

```sql
CREATE TABLE tbl_ciclos_visita (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  id_consultor INT NOT NULL,
  anio INT NOT NULL,
  mes_esperado INT NOT NULL,
  estandar VARCHAR(50) NULL,
  fecha_agendada DATE NULL,
  id_agendamiento INT NULL,
  fecha_acta DATE NULL,
  id_acta INT NULL,
  estatus_agenda ENUM('pendiente','cumple','incumple') DEFAULT 'pendiente',
  estatus_mes ENUM('pendiente','cumple','incumple') DEFAULT 'pendiente',
  alerta_enviada TINYINT(1) DEFAULT 0,
  confirmacion_enviada TINYINT(1) DEFAULT 0,
  observaciones TEXT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_cliente (id_cliente),
  INDEX idx_consultor (id_consultor),
  INDEX idx_mes_anio (mes_esperado, anio),
  INDEX idx_estatus_agenda (estatus_agenda),
  INDEX idx_estatus_mes (estatus_mes)
);
```

Campos clave:

- id_cliente, id_consultor: FKs a tablas maestras
- anio, mes_esperado: periodo en que el cliente debe ser visitado
- estandar: copia del estandar del cliente al momento de crear el ciclo (Mensual/Bimensual/Trimestral/Proyecto)
- fecha_agendada: se llena cuando hay un agendamiento para ese periodo
- fecha_acta: se llena cuando se completa una acta de visita
- estatus_agenda: cumplimiento de la cita exacta
- estatus_mes: cumplimiento del mes completo
- alerta_enviada / confirmacion_enviada: flags para no enviar emails duplicados

## Modelo: CicloVisitaModel

Archivo: app/Models/CicloVisitaModel.php

Metodos principales:

- getAllConJoins(): JOIN con tbl_clientes + tbl_consultor
- getByConsultor($id): filtrado por consultor
- getByMesAnio($mes, $anio): filtrado por periodo
- getAgendadosAyer(): registros con fecha_agendada = ayer sin alerta/confirmacion
- generarSiguienteCiclo($idCliente, $fechaActa, $estandar): crea nueva fila segun frecuencia

## Controller: AuditoriaVisitasController

Archivo: app/Controllers/AuditoriaVisitasController.php

Rutas:

- GET consultant/auditoria-visitas -> index (tabla principal)
- GET consultant/auditoria-visitas/edit/{id} -> edit (formulario edicion)
- POST consultant/auditoria-visitas/update/{id} -> update
- POST consultant/auditoria-visitas/delete/{id} -> delete

Vista: desktop consultant (DataTables + Bootstrap 4), NO PWA.

Columnas de la tabla:

1. Cliente
2. Consultor
3. Consultor Externo
4. Mes Esperado
5. Fecha Agendada
6. Fecha Acta
7. Estatus Agenda (badge color)
8. Estatus Mes (badge color)
9. Acciones (editar, eliminar)

## Comando Cron: auditoria:revisar-visitas-diario

Archivo: app/Commands/AuditoriaVisitasCron.php

Ejecucion: php spark auditoria:revisar-visitas-diario

Logica diaria (se ejecuta a las 7 AM):

1. Busca ciclos con fecha_agendada = ayer
2. Para cada uno, busca acta en tbl_acta_visita (id_cliente, fecha_visita, estado=completo)
3. Si NO hay acta:
   - estatus_agenda = incumple
   - Email alerta a: correo del consultor + edison.cuervo@cycloidtalent.com + diana.cuestas@cycloidtalent.com
   - alerta_enviada = 1
4. Si SI hay acta:
   - estatus_agenda = cumple, estatus_mes = cumple
   - Email confirmacion a los mismos destinatarios
   - confirmacion_enviada = 1
   - Auto-genera siguiente ciclo segun estandar

Cron en produccion:

```bash
0 7 * * * cd /www/wwwroot/phorizontal/enterprisesstph && php spark auditoria:revisar-visitas-diario >> writable/logs/cron.log 2>&1
```

## Auto-generacion de siguiente ciclo

Cuando una visita se completa (acta finalizada), se genera automaticamente el siguiente ciclo:

- Mensual: mes_acta + 1
- Bimensual: mes_acta + 2
- Trimestral: mes_acta + 3
- Proyecto: no genera (queda con la misma fecha)

Esto ocurre en dos puntos:

1. Hook en ActaVisitaController::finalizar() (tiempo real)
2. Cron diario (como respaldo)

## Hook en ActaVisitaController::finalizar()

Cuando un acta pasa a estado completo:

1. Buscar ciclo pendiente en tbl_ciclos_visita para ese cliente + mes del acta
2. Actualizar: fecha_acta, id_acta, recalcular estatus_agenda y estatus_mes
3. Auto-generar siguiente ciclo

## Decision arquitectonica

NO se usa tbl_clientes para datos operativos. El campo mes_prox_visita fue eliminado de tbl_clientes y migrado a tbl_ciclos_visita. Razon: tbl_clientes es tabla maestra referenciada por muchas FKs, no debe recibir UPDATEs frecuentes por operaciones cotidianas.

## Emails via SendGrid

Patron: SendGrid PHP SDK via CI4 BaseCommand.
From: notificacion.cycloidtalent@cycloidtalent.com
Destinatarios alertas: consultor + edison.cuervo@cycloidtalent.com + diana.cuestas@cycloidtalent.com
