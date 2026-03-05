# Evaluación de Inducción SST — Documentación técnica

> Estado: **Implementado** | Fecha: 2026-03-05
> Este patrón es reutilizable para: Capacitación Riesgo Locativo, Primeros Auxilios, etc.

---

## 1. Qué resuelve

Reemplaza el formulario de Google Forms con un formulario propio de la plataforma para evaluar a los asistentes de una sesión de **Inducción/Reinducción SST**. El consultor habilita el formulario desde la plataforma, comparte un enlace único con los asistentes, y los resultados quedan integrados en el **Reporte de Capacitación**.

---

## 2. Tablas en BD (`propiedad_horizontal`)

### `tbl_evaluacion_induccion` — Sesión de evaluación
| Columna | Tipo | Descripción |
|---|---|---|
| id | INT UNSIGNED PK | |
| id_asistencia_induccion | INT UNSIGNED NULL | FK a tbl_asistencia_induccion |
| id_cliente | INT UNSIGNED | |
| titulo | VARCHAR(255) | Título visible en el formulario |
| token | VARCHAR(64) UNIQUE | Token para el enlace público |
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
| id_cliente_conjunto | INT UNSIGNED NULL | Conjunto donde trabaja |
| acepta_tratamiento | TINYINT(1) | 1 = acepta Ley 1581/2012 |
| respuestas | JSON | `{"0":"c","1":"d",...}` — índice → letra |
| calificacion | DECIMAL(5,2) | Porcentaje (0-100) |
| created_at / updated_at | DATETIME | |

### Columnas agregadas a tablas existentes
- `tbl_asistencia_induccion`: `evaluacion_habilitada` TINYINT(1), `evaluacion_token` VARCHAR(64)
- `tbl_reporte_capacitacion`: `mostrar_evaluacion_induccion` TINYINT(1)

---

## 3. Archivos creados

| Archivo | Descripción |
|---|---|
| `app/Models/EvaluacionInduccionModel.php` | Modelo + constante PREGUNTAS + calcularCalificacion() |
| `app/Models/EvaluacionInduccionRespuestaModel.php` | Modelo respuestas + getByEvaluacion() + getPromedioByEvaluacion() |
| `app/Controllers/Inspecciones/EvaluacionInduccionController.php` | Controller: form público, submit, gracias, resultados admin, APIs |
| `app/Views/inspecciones/evaluacion-induccion/form-publico.php` | Formulario público (sin auth) |
| `app/Views/inspecciones/evaluacion-induccion/gracias.php` | Pantalla de confirmación con calificación |
| `app/Views/inspecciones/evaluacion-induccion/cerrado.php` | Pantalla de evaluación cerrada/no encontrada |
| `app/Views/inspecciones/evaluacion-induccion/resultados.php` | Vista admin con tabla de calificaciones |
| `app/SQL/create_evaluacion_induccion.php` | Script de migración SQL |

---

## 4. Rutas

### Públicas (sin auth)
```
GET  /evaluar/{token}          → EvaluacionInduccionController::form($token)
POST /evaluar/{token}/submit   → EvaluacionInduccionController::submit($token)
GET  /evaluar/{token}/gracias  → EvaluacionInduccionController::gracias($token)
```

### Dentro del grupo auth `/inspecciones/...`
```
GET  evaluacion-induccion/resultados/{id}       → resultados($id)
GET  evaluacion-induccion/api-resultados        → apiResultados()         [?id_asistencia=N]
GET  evaluacion-induccion/api-resultados-fecha  → apiResultadosPorFecha() [?id_cliente=N&fecha=Y-m-d]
```

---

## 5. Flujo completo

```
1. Consultor abre AsistenciaInduccion (tipo = induccion_reinduccion)
2. Marca checkbox "Habilitar evaluación"
3. Al guardar → syncEvaluacion() crea registro en tbl_evaluacion_induccion con token
4. El form muestra el enlace: https://phorizontal.cycloidtalent.com/evaluar/{token}
5. Consultor comparte el enlace por WhatsApp/QR
6. Asistentes abren el link → formulario público:
   a. Autorización Ley 1581 (checkbox obligatorio)
   b. Datos personales (nombre, cédula, WhatsApp, conjunto Select2, empresa, cargo)
   c. 10 preguntas SST con opción múltiple
7. Al enviar → calificacion = (correctas/10)*100, guardado en tbl_evaluacion_induccion_respuesta
8. Asistente ve pantalla de gracias con su calificación
9. En ReporteCapacitacion → marcar checkbox "Incluir resultados evaluación"
   → carga AJAX via api-resultados-fecha con tabla de calificaciones y promedio
```

---

## 6. Lógica de calificación

- Total preguntas: **10**
- Puntaje: (respuestas correctas / 10) × 100 = porcentaje
- Aprobado: ≥ 70%
- Las respuestas correctas están hardcodeadas en `EvaluacionInduccionModel::PREGUNTAS` (constante PHP)

---

## 7. Preguntas — Respuestas correctas

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

## 8. Archivos modificados

| Archivo | Cambio |
|---|---|
| `app/Models/AsistenciaInduccionModel.php` | + allowedFields: evaluacion_habilitada, evaluacion_token |
| `app/Controllers/Inspecciones/AsistenciaInduccionController.php` | + syncEvaluacion(), edit() pasa $evaluacion a view |
| `app/Views/inspecciones/asistencia-induccion/form.php` | + card EVALUACIÓN con checkbox y link copyable |
| `app/Models/ReporteCapacitacionModel.php` | + allowedFields: mostrar_evaluacion_induccion |
| `app/Controllers/Inspecciones/ReporteCapacitacionController.php` | + getInspeccionPostData() recoge mostrar_evaluacion_induccion |
| `app/Views/inspecciones/reporte-capacitacion/form.php` | + card RESULTADOS EVALUACIÓN + JS cargarResultadosEval() |
| `app/Config/Routes.php` | + rutas públicas /evaluar/* + rutas auth evaluacion-induccion/* |

---

## 9. Patrón para reutilizar (Capacitación Locativo, Primeros Auxilios, etc.)

Para crear otro tipo de evaluación:
1. Crear nuevo `EvaluacionXxxModel.php` con `const PREGUNTAS = [...]` específico
2. Duplicar `EvaluacionInduccionController.php` → cambiar modelo y preguntas
3. Duplicar views `evaluacion-induccion/` → `evaluacion-xxx/`
4. Agregar `evaluacion_xxx_habilitada` + `evaluacion_xxx_token` al módulo correspondiente
5. Agregar rutas públicas `/evaluar-xxx/{token}` + rutas admin
6. Documentar aquí en OPEN CLAW

> La lógica de token único, calificación automática y autorización Ley 1581 es idéntica en todos los casos.
