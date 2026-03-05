# Evaluación de Inducción SST — Documentación técnica

> Estado: **Implementado v2** | Fecha: 2026-03-05
> Patrón: **Mini-universo independiente** — reutilizable para Riesgo Locativo, Primeros Auxilios, etc.

---

## 1. Qué resuelve

Reemplaza Google Forms con un módulo propio para evaluar asistentes de inducciones SST. Es un módulo **independiente** dentro de `/inspecciones/` con su propio CRUD (list/create/edit/view/delete), formulario público, y QR para compartir.

---

## 2. Tablas en BD (`propiedad_horizontal`)

### `tbl_evaluacion_induccion` — Sesión de evaluación
| Columna | Tipo | Descripción |
|---|---|---|
| id | INT UNSIGNED PK | |
| id_asistencia_induccion | INT UNSIGNED NULL | FK opcional (no obligatoria) |
| id_cliente | INT UNSIGNED | Conjunto/cliente |
| titulo | VARCHAR(255) | Título visible en el formulario |
| token | VARCHAR(64) UNIQUE | Token para enlace público |
| estado | ENUM('activo','cerrado') | activo = acepta respuestas |
| created_at / updated_at | DATETIME | |

### `tbl_evaluacion_induccion_respuesta` — Respuestas individuales
| Columna | Tipo | Descripción |
|---|---|---|
| id | INT UNSIGNED PK | |
| id_evaluacion | INT UNSIGNED | FK a tbl_evaluacion_induccion |
| nombre | VARCHAR(255) | |
| cedula | VARCHAR(30) | |
| whatsapp | VARCHAR(30) | |
| empresa_contratante | VARCHAR(255) | |
| cargo | VARCHAR(100) | |
| id_cliente_conjunto | INT UNSIGNED NULL | Conjunto donde trabaja (Select2) |
| acepta_tratamiento | TINYINT(1) | 1 = acepta Ley 1581/2012 |
| respuestas | JSON | `{"0":"c","1":"d",...}` — índice → letra |
| calificacion | DECIMAL(5,2) | Porcentaje (0-100) |
| created_at / updated_at | DATETIME | |

---

## 3. Arquitectura — Mini-universo

### Archivos
| Archivo | Descripción |
|---|---|
| `app/Models/EvaluacionInduccionModel.php` | Modelo + constante PREGUNTAS + calcularCalificacion() |
| `app/Models/EvaluacionInduccionRespuestaModel.php` | Modelo respuestas |
| `app/Controllers/Inspecciones/EvaluacionInduccionController.php` | CRUD admin + formulario público + API |
| `app/Views/inspecciones/evaluacion-induccion/list.php` | Listado de evaluaciones |
| `app/Views/inspecciones/evaluacion-induccion/form.php` | Create/Edit evaluación (admin) |
| `app/Views/inspecciones/evaluacion-induccion/view.php` | Ver resultados + QR + enlace (admin) |
| `app/Views/inspecciones/evaluacion-induccion/form-publico.php` | Formulario público (sin auth) |
| `app/Views/inspecciones/evaluacion-induccion/gracias.php` | Pantalla post-envío |
| `app/Views/inspecciones/evaluacion-induccion/cerrado.php` | Evaluación cerrada/no encontrada |

---

## 4. Rutas

### Admin (dentro de grupo `/inspecciones/`, requiere auth)
```
GET  evaluacion-induccion                          → list
GET  evaluacion-induccion/create                   → create
POST evaluacion-induccion/store                    → store
GET  evaluacion-induccion/edit/{id}                → edit
POST evaluacion-induccion/update/{id}              → update
GET  evaluacion-induccion/view/{id}                → view (QR + enlace + resultados)
GET  evaluacion-induccion/delete/{id}              → delete
GET  evaluacion-induccion/toggle/{id}              → toggleEstado (activo↔cerrado)
GET  evaluacion-induccion/api-resultados-fecha     → API JSON para ReporteCapacitacion
```

### Públicas (sin auth)
```
GET  /evaluar/{token}          → formulario público
POST /evaluar/{token}/submit   → procesar respuestas
GET  /evaluar/{token}/gracias  → pantalla de gracias
```

---

## 5. Flujo completo

```
1. Consultor abre /inspecciones/evaluacion-induccion → listado
2. Click "Nueva" → selecciona cliente, título → "Crear evaluación"
3. Se genera token único → se muestra view con:
   a. QR grande (70vw, max 280px) para escanear desde celular
   b. Enlace copiable
   c. Botón abrir formulario
4. Consultor comparte QR o enlace por WhatsApp
5. Asistentes abren el link → formulario público:
   a. Autorización Ley 1581 de 2012 (obligatoria)
   b. Datos personales (nombre*, cédula*, WhatsApp, conjunto Select2, empresa, cargo)
   c. 10 preguntas SST con opción múltiple
6. Al enviar → calificacion = (correctas/10)*100 → pantalla de gracias con resultado
7. Consultor ve resultados en tiempo real en /view/{id}
8. En ReporteCapacitacion → sección read-only carga automáticamente
   resultados si hay evaluación para el mismo cliente+fecha (sin checkbox)
```

---

## 6. QR Code

- Librería: `chillerlan/php-qrcode` v5 (ya en composer)
- Generación: **base64 inline** en el controller (`generarQrBase64()`)
- Se embebe como `data:image/png;base64,...` en el `<img>` — no hace request HTTP separado
- Tamaño responsive: `width:70vw; max-width:280px` — grande en celular, contenido en desktop
- Scale: 10, ECC Level: H (alta tolerancia a daños)

---

## 7. Preguntas — Respuestas correctas

Hardcodeadas en `EvaluacionInduccionModel::PREGUNTAS`

| # | Correcta | Tema |
|---|---|---|
| 1 | c | Objetivo SG-SST |
| 2 | d | Quién implementa SG-SST en PH |
| 3 | c | Definición de peligro |
| 4 | d | Diferencia peligro/riesgo |
| 5 | b | Brigada de emergencia |
| 6 | b | FURAT |
| 7 | d | Dotaciones EPP |
| 8 | c | Política consumo alcohol/drogas |
| 9 | c | Política prevención emergencias |
| 10 | d | Tipo de emergencia |

---

## 8. Integración con ReporteCapacitacion

- **Read-only** — sin checkbox. La sección "RESULTADOS EVALUACIÓN INDUCCIÓN SST" se carga automáticamente via AJAX cuando hay cliente+fecha seleccionados.
- API: `GET /inspecciones/evaluacion-induccion/api-resultados-fecha?id_cliente=N&fecha=Y-m-d`
- Busca evaluaciones por `id_cliente` y `DATE(created_at)` = fecha
- Si no hay resultados, muestra mensaje informativo (sin error)

---

## 9. Patrón para reutilizar

Para crear otro tipo de evaluación (Riesgo Locativo, Primeros Auxilios, etc.):

1. Crear `EvaluacionXxxModel.php` con su propio `const PREGUNTAS = [...]`
2. Crear `EvaluacionXxxRespuestaModel.php` (copiar del actual)
3. Crear `EvaluacionXxxController.php` — copiar el actual, cambiar modelos y preguntas
4. Copiar views `evaluacion-induccion/` → `evaluacion-xxx/`
5. Agregar tabla `tbl_evaluacion_xxx` + `tbl_evaluacion_xxx_respuesta`
6. Agregar rutas CRUD en el grupo `/inspecciones/`
7. Agregar rutas públicas `/evaluar-xxx/{token}`
8. Documentar aquí

> La lógica de token, QR base64, calificación automática y autorización Ley 1581 es idéntica.
