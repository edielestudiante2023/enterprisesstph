# Implementación de Generación Automática de Contratos PDF

## Resumen
Se ha implementado exitosamente un sistema completo de generación automática de contratos en PDF con envío automático por email a edison.cuervo@cycloidtalent.com usando SendGrid.

---

## Componentes Implementados

### 1. Base de Datos
**Archivo:** `database/migrations/add_contract_generation_fields.sql`

Se agregaron 24 nuevos campos a la tabla `tbl_contratos`:

**Datos del Cliente (EL CONTRATANTE):**
- `nombre_rep_legal_cliente` - Nombre del representante legal
- `cedula_rep_legal_cliente` - Cédula del representante legal
- `direccion_cliente` - Dirección del cliente
- `telefono_cliente` - Teléfono del cliente
- `email_cliente` - Email del cliente

**Datos de Cycloid Talent (EL CONTRATISTA):**
- `nombre_rep_legal_contratista` - Default: "DIANA PATRICIA CUESTAS NAVIA"
- `cedula_rep_legal_contratista` - Default: "52.425.982"
- `email_contratista` - Default: "Diana.cuestas@cycloidtalent.com"

**Responsable SG-SST:**
- `nombre_responsable_sgsst` - Default: "Edison Ernesto Cuervo Salazar"
- `cedula_responsable_sgsst` - Default: "80.039.147"
- `licencia_responsable_sgsst` - Default: "4241"
- `email_responsable_sgsst` - Default: "Edison.cuervo@cycloidtalent.com"

**Detalles de Pago:**
- `valor_mensual` - Valor calculado automáticamente
- `numero_cuotas` - Default: 12
- `frecuencia_visitas` - Opciones: MENSUAL, BIMENSUAL, TRIMESTRAL, PROYECTO
- `cuenta_bancaria` - Default: "108900260762"
- `banco` - Default: "Davivienda"
- `tipo_cuenta` - Default: "Ahorros"

**Control de Generación:**
- `contrato_generado` - Booleano (0/1)
- `fecha_generacion_contrato` - Timestamp de generación
- `ruta_pdf_contrato` - Ruta del archivo PDF
- `contrato_enviado` - Booleano (0/1)
- `fecha_envio_contrato` - Timestamp de envío
- `email_envio_contrato` - Email al que se envió

### 2. Librería de Generación de PDF
**Archivo:** `app/Libraries/ContractPDFGenerator.php`

Librería completa usando TCPDF con:
- Plantilla completa del contrato con 13 cláusulas
- Introducción con datos de ambas partes
- Formato profesional con encabezados y secciones
- Conversión de números a palabras
- Conversión de fechas a formato español
- Métodos públicos:
  - `generateContract($contractData)` - Genera el PDF
  - `save($filePath)` - Guarda a archivo
  - `download($fileName)` - Fuerza descarga
  - `getString()` - Obtiene PDF como string

### 3. Controlador
**Archivo:** `app/Controllers/ContractController.php`

Tres métodos nuevos implementados:

#### `editContractData($idContrato)`
- Muestra formulario para editar datos antes de generar PDF
- Pre-llena datos existentes del contrato y cliente
- Valida permisos de acceso

#### `saveAndGeneratePDF($idContrato)`
- Guarda datos del formulario
- Genera PDF usando ContractPDFGenerator
- Crea directorio `/public/uploads/contratos/` si no existe
- Guarda PDF con nombre: `contrato_[numero]_[timestamp].pdf`
- Actualiza base de datos con información de generación
- Envía email con PDF adjunto usando SendGrid
- Actualiza base de datos con información de envío
- Retorna mensajes de éxito/error apropiados

#### `sendContractEmail($contract, $filePath, $fileName)` (privado)
- Envía email usando SendGrid API
- Email HTML profesional con tabla de datos del contrato
- Adjunta PDF al email
- Destinatario fijo: edison.cuervo@cycloidtalent.com
- Remitente: notificacion.cycloidtalent@cycloidtalent.com
- Registra logs de éxito/error

#### `downloadPDF($idContrato)`
- Permite descargar PDF generado
- Valida existencia del archivo
- Valida permisos de acceso
- Fuerza descarga del archivo

### 4. Vistas

#### `app/Views/contracts/edit_contract_data.php`
Formulario completo con 5 secciones:

1. **Datos del Contrato**
   - Número de contrato (readonly)
   - Fecha de inicio
   - Fecha de finalización
   - Valor total del contrato
   - Número de cuotas
   - Valor mensual (calculado automáticamente)
   - Frecuencia de visitas (MENSUAL, BIMENSUAL, TRIMESTRAL, PROYECTO)

2. **Datos del Cliente (EL CONTRATANTE)**
   - Nombre/Razón social (readonly - viene de tbl_clientes)
   - NIT (readonly)
   - Nombre del representante legal
   - Cédula del representante legal
   - Dirección
   - Email
   - Teléfono

3. **Datos de Cycloid Talent (EL CONTRATISTA)**
   - Representante legal (pre-llenado: Diana Patricia Cuestas Navia)
   - Cédula (pre-llenado: 52.425.982)
   - Email (pre-llenado: Diana.cuestas@cycloidtalent.com)

4. **Responsable SG-SST**
   - Nombre completo (pre-llenado: Edison Ernesto Cuervo Salazar)
   - Cédula (pre-llenado: 80.039.147)
   - Licencia (pre-llenado: 4241)
   - Email (pre-llenado: Edison.cuervo@cycloidtalent.com)

5. **Datos Bancarios**
   - Banco (pre-llenado: Davivienda)
   - Tipo de cuenta (Ahorros/Corriente)
   - Número de cuenta (pre-llenado: 108900260762)

**JavaScript incorporado:**
- Cálculo automático del valor mensual al cambiar valor total o número de cuotas
- Formateo de moneda en tiempo real

#### `app/Views/contracts/view.php` (actualizada)
- Botón "Generar Contrato PDF" (amarillo si no está generado)
- Botón "Regenerar Contrato PDF" (gris si ya está generado)
- Botón "Descargar PDF Generado" (solo visible si está generado)
- Muestra fecha de generación del contrato
- Muestra mensajes de éxito/error del proceso

### 5. Rutas
**Archivo:** `app/Config/Routes.php`

```php
$routes->get('/contracts/edit-contract-data/(:num)', 'ContractController::editContractData/$1');
$routes->post('/contracts/save-and-generate/(:num)', 'ContractController::saveAndGeneratePDF/$1');
$routes->get('/contracts/download-pdf/(:num)', 'ContractController::downloadPDF/$1');
```

### 6. Dependencias
**Archivo:** `composer.json`

Se agregó:
```json
"tecnickcom/tcpdf": "^6.7"
```

Ya existía:
```json
"sendgrid/sendgrid": "^8.1"
```

---

## Flujo de Trabajo Completo

### Paso 1: Ver Contrato
Usuario navega a `/contracts/view/[id]` y ve los detalles del contrato.

### Paso 2: Iniciar Generación
Usuario hace clic en "Generar Contrato PDF" → Redirige a `/contracts/edit-contract-data/[id]`

### Paso 3: Editar/Verificar Datos
- Sistema pre-llena datos desde `tbl_contratos` y `tbl_clientes`
- Usuario verifica/completa datos faltantes:
  - Representante legal del cliente
  - Cédula del representante legal
  - Dirección, email, teléfono
  - Valor del contrato y número de cuotas
  - Frecuencia de visitas
- JavaScript calcula valor mensual automáticamente
- Datos de Cycloid Talent y SG-SST ya vienen pre-llenados

### Paso 4: Generar y Enviar
Usuario hace clic en "Guardar y Generar Contrato PDF"
- Sistema POST a `/contracts/save-and-generate/[id]`
- Guarda datos en base de datos
- Genera PDF usando TCPDF
- Crea archivo en `/public/uploads/contratos/contrato_[numero]_[timestamp].pdf`
- Actualiza BD: `contrato_generado=1`, `fecha_generacion_contrato`, `ruta_pdf_contrato`
- Envía email a edison.cuervo@cycloidtalent.com con PDF adjunto
- Actualiza BD: `contrato_enviado=1`, `fecha_envio_contrato`, `email_envio_contrato`
- Redirige a vista del contrato con mensaje de éxito

### Paso 5: Descargar (opcional)
Usuario puede descargar PDF en cualquier momento desde `/contracts/download-pdf/[id]`

---

## Estructura del PDF Generado

El PDF contiene:

### Encabezado
- Logo de Cycloid Talent
- Título: "CONTRATO DE PRESTACIÓN DE SERVICIOS PROFESIONALES PARA EL DISEÑO E IMPLEMENTACIÓN DEL SG-SST"
- Número de contrato

### Introducción
- Identificación de EL CONTRATANTE (cliente)
- Identificación de EL CONTRATISTA (Cycloid Talent)

### 13 Cláusulas
1. **PRIMERA: OBJETO DEL CONTRATO**
2. **SEGUNDA: EJECUCIÓN DEL CONTRATO**
3. **TERCERA: OBLIGACIONES DE EL CONTRATANTE**
4. **CUARTA: OBLIGACIONES DE EL CONTRATISTA**
5. **QUINTA: VALOR DEL CONTRATO Y FORMA DE PAGO**
6. **SEXTA: VIGENCIA DEL CONTRATO**
7. **SÉPTIMA: INDEPENDENCIA DE LAS PARTES**
8. **OCTAVA: LUGAR DE EJECUCIÓN**
9. **NOVENA: INHABILIDADES E INCOMPATIBILIDADES**
10. **DÉCIMA: MODIFICACIONES**
11. **DÉCIMA PRIMERA: CAUSALES DE TERMINACIÓN**
12. **DÉCIMA SEGUNDA: SOLUCIÓN DE CONTROVERSIAS**
13. **DÉCIMA TERCERA: DOMICILIO CONTRACTUAL**

### Sección de Firmas
- Espacio para firma de EL CONTRATANTE
- Espacio para firma de EL CONTRATISTA
- Datos del responsable de SG-SST asignado

---

## Email Enviado

### Configuración
- **De:** notificacion.cycloidtalent@cycloidtalent.com (Cycloid Talent)
- **Para:** edison.cuervo@cycloidtalent.com (Edison Cuervo)
- **Asunto:** Nuevo Contrato Generado - [Número de Contrato]

### Contenido HTML
Tabla profesional con:
- Número de Contrato
- Cliente
- NIT
- Fecha de Inicio
- Fecha de Finalización
- Valor del Contrato
- Responsable SG-SST
- Timestamp de generación

### Adjunto
PDF del contrato generado

---

## Sistema de Seguridad

1. **Validación de Permisos:**
   - Consultores solo pueden generar contratos de sus clientes
   - Administradores pueden generar cualquier contrato

2. **Validación de Datos:**
   - Campos requeridos marcados con asterisco (*)
   - Validación en cliente (HTML5 required)
   - Validación en servidor antes de guardar

3. **Manejo de Errores:**
   - Try-catch en generación de PDF
   - Try-catch en envío de email
   - Logs detallados de errores en CodeIgniter
   - Mensajes amigables al usuario

4. **Archivos:**
   - PDFs guardados en `/public/uploads/contratos/`
   - Nombres únicos con timestamp
   - Verificación de existencia antes de descargar

---

## Variables de Entorno Requeridas

En `.env`:
```
SENDGRID_API_KEY=your_sendgrid_api_key_here
```

---

## Instalación

1. **Ejecutar migración de base de datos:**
```sql
source database/migrations/add_contract_generation_fields.sql
```

2. **Instalar dependencia TCPDF:**
```bash
composer require tecnickcom/tcpdf
```

3. **Configurar SendGrid API Key en `.env`:**
```
SENDGRID_API_KEY=SG.xxxxxxxxxxxxx
```

4. **Crear directorio de uploads (se crea automáticamente si no existe):**
```bash
mkdir -p public/uploads/contratos
chmod 755 public/uploads/contratos
```

---

## Uso

1. Navegar a un contrato existente: `/contracts/view/[id]`
2. Hacer clic en "Generar Contrato PDF"
3. Verificar/completar datos en el formulario
4. Hacer clic en "Guardar y Generar Contrato PDF"
5. Sistema genera PDF y envía email automáticamente a edison.cuervo@cycloidtalent.com
6. Descargar PDF desde el botón "Descargar PDF Generado"

---

## Regeneración de Contratos

Los contratos pueden regenerarse en cualquier momento:
- Editar datos desde `/contracts/edit-contract-data/[id]`
- Generar nuevamente sobrescribe registro anterior
- Nuevo PDF se genera con timestamp actual
- Nuevo email se envía automáticamente

---

## Logs

Todos los eventos se registran en logs de CodeIgniter:
- `log_message('info')` para envíos exitosos
- `log_message('error')` para errores en generación o envío
- Códigos de respuesta de SendGrid
- Excepciones capturadas con stack trace

---

## Campos Calculados Automáticamente

- **Valor Mensual:** `valor_contrato / numero_cuotas`
- **Nombre del Archivo PDF:** `contrato_[numero_contrato]_[timestamp].pdf`
- **Fecha de Generación:** Timestamp actual al generar
- **Fecha de Envío:** Timestamp actual al enviar email

---

## Mejoras Futuras Posibles

1. Enviar copia del email al cliente (`email_cliente`)
2. Notificación al representante legal del cliente
3. Historial de regeneraciones de contratos
4. Versionado de PDFs generados
5. Firma electrónica del contrato
6. Recordatorios automáticos de renovación
7. Dashboard de contratos generados/pendientes
8. Reportes de contratos por período
9. Integración con sistema de facturación
10. Generación masiva de contratos

---

## Soporte Técnico

Para reportar errores o solicitar nuevas funcionalidades, contactar a:
- Edison Cuervo - edison.cuervo@cycloidtalent.com
- Sistema de gestión de contratos Cycloid Talent

---

**Fecha de Implementación:** Enero 2026
**Versión:** 1.0
**Estado:** Producción

