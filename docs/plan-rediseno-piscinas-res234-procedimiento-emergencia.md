# Plan — Rediseño Inspección Piscinas (Res 234/2026) + Procedimiento de Emergencia por Área

## Contexto

**Qué dispara el cambio.** La consultora (Gabriela) fue hoy 2026-04-22 a auditar un cliente usando `/inspecciones/piscinas/create`. El formulario quedó "desenfocado" respecto a la normatividad vigente:

- Se expidió la **Resolución 234 del 10-feb-2026 del Ministerio de Salud** que redefine calidad del agua, buenas prácticas sanitarias, IRAPI (índice de riesgo), botiquines por m² y documentación obligatoria. El módulo actual apunta solo a Ley 1209/2008 + Decreto 554/2015, que queda corto.
- Los nombres de varios campos están mal puestos ("certificado municipal vigente" no existe — el documento real es el **ensayo microbiológico** + **concepto sanitario** de la Secretaría de Salud), y hay duplicaciones (alarma de inmersión vs. alarma 80dB que son el mismo elemento).
- El cliente también pide un **procedimiento de reacción en emergencia específico para zonas húmedas** (qué hacer si tiembla con niños en la piscina, qué no hacer, quién, recursos) que hoy no existe como entregable. Después se replicará para **baño turco, sauna, gym, zona BBQ**.

**Alcance del rediseño (Opción C confirmada por el usuario):**
- Rediseñar el módulo de piscinas con captura numérica, validación contra Anexo I de Res 234, cálculo automático de IRAPI y ISL Langelier, y checklist de botiquín según tipo A/B/C por m².
- Crear un módulo nuevo `/inspecciones/procedimiento-emergencia-area/` con master-detalle (área ← N escenarios), generador IA por escenario y PDF entregable al cliente.
- Preservar histórico no aplica — Gabriela no alcanzó a guardar nada hoy; no hay registros que migrar.

**No aplica en este plan (fases posteriores, no hoy):**
- Replicar procedimiento de emergencia a baño turco, sauna, gym, zona BBQ.
- Arreglar inspección de piscinero.

---

## Fases

### Fase 1 — HOY (operativo mínimo para entregar)

**Objetivo:** que Gabriela pueda hoy mismo (a) levantar una inspección de piscinas con el nuevo formulario y generar PDF, y (b) entregar al cliente un procedimiento de emergencia para zona húmeda.

1. **Piscinas — SQL nuevo.** Migrar estructura: 1 master + 4 hijas (detalle piscina, parámetros de agua, resultados microbiológicos, checklist botiquín). Script PHP CLI en `app/SQL/`. LOCAL primero, verificar, PROD después.
2. **Piscinas — controlador y modelos.** Reescribir `InspeccionPiscinasController.php` y crear/actualizar `InspeccionPiscinasModel`, `PiscinaDetalleModel` + 3 modelos nuevos. Reusar traits existentes (`AutosaveJsonTrait`, `PreventDuplicateBorradorTrait`, `InspeccionesTransactionalTrait`, `ImagenCompresionTrait`).
3. **Piscinas — formulario `form.php` en 3 bloques.** Bloque 0 (maestro inspección): datos de establecimiento, documentación, DEA, operador certificado. Bloque 1 (por piscina, infraestructura + avisos + emergencia + higiene). Bloque 2 (por piscina, calidad del agua con valores numéricos + ensayos de laboratorio + medición in situ).
4. **Piscinas — cálculo IRAPI e ISL.** Librería nueva `App\Libraries\IrapiCalculator`. Invocada al guardar cada piscina; persiste valor IRAPI + clasificación (Óptimo/Bajo/Medio/Alto) + ISL.
5. **Piscinas — validación de rangos.** Cada parámetro capturado se compara contra los valores del Anexo I; se marca "conforme/fuera de rango" en vista y PDF.
6. **Piscinas — PDF nuevo.** Plantilla DOMPDF nueva en `app/Views/inspecciones/piscinas/pdf.php`. Portada con IRAPI como hallazgo principal. Marco normativo actualizado a **Ley 1209/2008 + Decreto 554/2015 + Resolución 234/2026 Minsalud**.
7. **Procedimiento de emergencia — SQL nuevo.** 2 tablas: `tbl_procedimiento_emergencia_area` (master: área, cliente, fecha, consultor, datos de contexto) + `tbl_procedimiento_emergencia_escenario` (detalle: escenario, qué hacer, qué no hacer, cuándo, quién, recursos, generado_con_ia).
8. **Procedimiento de emergencia — controlador + librería IA.** Crear `ProcedimientoEmergenciaAreaController.php` con CRUD + generador IA. Crear librería `App\Libraries\EmergencyProcedureIAService` siguiendo patrón de `PlanEmergenciaIAService`.
9. **Procedimiento de emergencia — formulario + PDF.** Vistas nuevas con 6 escenarios pre-cargados para el área "piscina / zona húmeda": sismo, ahogamiento, electrocución, trueno-rayos, desmayo-hipoglucemia, herida grave o golpe de cabeza. PDF entregable al cliente.
10. **Rutas.** Agregar entradas a `app/Config/Routes.php` para los 2 módulos.
11. **Deploy Fase 1.** LOCAL → pruebas → PROD con credenciales vía `DB_PROD_PASS`.

**Criterio de cierre de Fase 1:** Gabriela genera una inspección real de piscina + un procedimiento de emergencia, ambos con PDF enviado por email. IRAPI se calcula automático. El cliente recibe los dos PDFs.

### Fase 2 — Próximos días (refinamiento piscinas)

- Afinar rangos de validación por tipo de estanque (piscina vs. estructura similar) según detalle del Anexo I.
- Integrar dashboard de tendencia histórica por piscina (IRAPI trimestral).
- Validaciones cruzadas: si climatizada=SI entonces ventilación obligatoria; si m²<500 botiquín debe ser Tipo A; etc.
- Pre-carga del contenido del último libro diario y último ensayo microbiológico desde archivos adjuntos.

### Fase 3 — Replicar procedimiento de emergencia a otras áreas

Extender el módulo `/inspecciones/procedimiento-emergencia-area/` con escenarios predefinidos para:
- **Baño turco** — quemaduras por vapor, desmayo por calor, claustrofobia, falla eléctrica del generador de vapor.
- **Sauna** — quemaduras por contacto con piedras/madera, deshidratación, desmayo, incendio.
- **Gym** — caída de mancuerna, lesión de espalda, desmayo por esfuerzo, descarga de máquina eléctrica.
- **Zona BBQ** — quemaduras, incendio, inhalación de humo, intoxicación por gas.

El modelo de datos ya soporta las 5 áreas desde Fase 1 (enum) — Fase 3 solo agrega seeders de escenarios y ajusta el prompt IA al área específica. **Sin refactor**.

### Fase 4 — Arreglar inspección de piscinero

Revisión del módulo `/inspecciones/piscinero/` (controller, modelo, form, PDF). Alcance concreto se define cuando llegue la fase. No entra en este plan.

---

## Arquitectura

### Piscinas — tablas nuevas

```
tbl_inspeccion_piscinas (master — reemplaza la actual)
  id, id_cliente, id_consultor, fecha_inspeccion
  empresa_mantenimiento, nit_empresa_mantenimiento, contacto_empresa
  superficie_total_establecimiento_m2         -- decide tipo botiquín
  concepto_sanitario ENUM('favorable','desfavorable','no_emitido')
  concepto_sanitario_fecha, concepto_sanitario_observaciones
  dea_presente ENUM('SI','NO','NA'), dea_personal_capacitado_cantidad
  operador_certificado_nombre, operador_certificado_entidad, operador_certificado_vigencia
  plan_saneamiento_completo ENUM('SI','NO','PARCIAL')  -- 5 programas Art. 17
  documentacion_art15_completa ENUM('SI','NO','PARCIAL')  -- 8 procedimientos
  manejo_quimicos_conforme ENUM('SI','NO','NA')  -- fichas, SDS, EPP, GHS
  area_residuos_conforme ENUM('SI','NO','NA')
  contenedores_codificados_color ENUM('SI','NO','NA')
  tablero_publico_resultados ENUM('SI','NO','NA')
  recomendaciones_generales TEXT
  marco_normativo TEXT  -- congelado en finalizar
  total_piscinas, ruta_pdf, estado, timestamps

tbl_piscina_detalle (N piscinas por inspección — reemplaza actual)
  id, id_inspeccion, orden, identificador
  tipo ENUM('ADULTOS','NINOS','JACUZZI','CHAPOTEADERO','OTRA')
  uso ENUM('COLECTIVO_PUBLICO','RESTRINGIDO')
  climatizada ENUM('SI','NO')
  superficie_piscina_m2, volumen_agua_m3
  perfil_profundidad ENUM('UNIFORME','VARIABLE')
  profundidad_max_m, profundidad_min_m
  aforo_piscina_max, aforo_deck_max
  -- Infraestructura (Ley 1209) 18 SI/NO/NA:
  cerramiento_perimetral, puerta_control_acceso,
  alarma_inmersion_80db, boton_parada_emergencia,       -- consolidados
  drenaje_antiatrapamiento, minimo_dos_drenajes, sistema_liberacion_vacio,
  senalizacion_profundidad, baldosas_cambio_profundidad,
  escaleras_acceso_antideslizantes, baranda_escaleras,
  iluminacion_adecuada, ventilacion_adecuada,
  -- Avisos 7 SI/NO/NA:
  aviso_menores_12, aviso_reglamento, aviso_horario,
  aviso_ducharse_antes, aviso_prohibido_zapatos,
  aviso_telefonos_emergencia, aviso_aforo_visible,
  -- Emergencia:
  botiquin_tipo ENUM('A','B','C','NINGUNO'),
  camilla_rescate, flotadores_circulares_min_2, baston_con_gancho, citofono_24h,
  -- Higiene:
  cubiculos_duchas_mujeres INT, cubiculos_duchas_hombres INT,
  baranda_apoyo_duchas, lavapies_funcional,
  -- Dosificación (Art. 5):
  dosificacion_independiente, sistema_seguridad_flujo, no_dosificacion_manual_con_publico,
  -- Libro de registro (Art. 16):
  libro_registro_existe, libro_ultima_semana_fecha,
  -- Resultado:
  irapi_valor DECIMAL(5,2), irapi_clasificacion ENUM('SIN_RIESGO','BAJO','MEDIO','ALTO'),
  isl_valor DECIMAL(5,2),
  foto, observaciones, timestamps

tbl_piscina_parametro_agua (mediciones in situ — una fila por parámetro por piscina)
  id, id_piscina_detalle, parametro, valor DECIMAL(8,2), unidad,
  conforme ENUM('SI','NO','NA'), rango_referencia, observaciones
  -- parametros: pH, cloro_libre, cloro_combinado, temperatura,
  --             turbidez, orp, tds, conductividad, acido_cianurico,
  --             dureza_calcica, alcalinidad_total, bromo_total

tbl_piscina_ensayo_laboratorio (ensayo microbiológico / fisicoquímico último)
  id, id_piscina_detalle, tipo ENUM('MICROBIOLOGICO','FISICOQUIMICO'),
  fecha_toma, laboratorio, norma_citada,
  heterotrofos_ufc, coliformes_termotolerantes_ufc, ecoli_ufc,
  pseudomonas_ufc, legionella_ufc,  -- nullable si no aplica
  conforme_global ENUM('SI','NO'),
  archivo_adjunto VARCHAR(255)

tbl_piscina_botiquin_item (checklist Anexo III por piscina)
  id, id_piscina_detalle, item_codigo, item_nombre,
  cantidad_exigida INT, cantidad_observada INT,
  presente ENUM('SI','NO','PARCIAL'), observaciones
```

### Procedimiento de emergencia por área — tablas nuevas

```
tbl_procedimiento_emergencia_area (master)
  id, id_cliente, id_consultor, fecha_elaboracion,
  area ENUM('PISCINA','BAÑO_TURCO','SAUNA','GYM','ZONA_BBQ'),
  nombre_area_descriptivo VARCHAR(150),  -- ej. "Piscina adultos Club House"
  responsable_area_nombre, responsable_area_cargo, responsable_area_contacto,
  horario_operacion, aforo_maximo,
  telefonos_emergencia TEXT,
  recursos_disponibles TEXT,  -- DEA, botiquín, camilla, radio, etc.
  observaciones_contexto TEXT,
  ruta_pdf VARCHAR(255), estado ENUM('borrador','completo'),
  marco_normativo TEXT, timestamps

tbl_procedimiento_emergencia_escenario (N escenarios por área)
  id, id_procedimiento, orden, escenario_codigo,
  escenario_nombre VARCHAR(200),  -- "Sismo con niños en la piscina"
  que_hacer TEXT, que_no_hacer TEXT,
  cuando TEXT, quien TEXT, recursos TEXT,
  generado_con_ia TINYINT,  -- 0 si manual, 1 si Haiku lo generó
  aprobado_por_consultor TINYINT,
  observaciones TEXT, timestamps
```

### Escenarios pre-cargados para área PISCINA (Fase 1)

1. Sismo con bañistas en el agua
2. Ahogamiento / semi-ahogamiento
3. Electrocución por equipo eléctrico de la piscina
4. Tormenta eléctrica / rayos (piscinas abiertas)
5. Desmayo / hipoglucemia / emergencia médica
6. Herida grave o golpe en la cabeza (con o sin inmovilidad)
7. Liberación fecal o de fluidos corporales en el agua
8. Escape / derrame de producto químico del cuarto de bombas

---

## Archivos críticos

### A crear

- [app/SQL/migrate_inspeccion_piscinas_v2.php](app/SQL/migrate_inspeccion_piscinas_v2.php) — migración de las 5 tablas nuevas de piscinas. Drop de las dos anteriores (no hay datos).
- [app/SQL/migrate_procedimiento_emergencia_area.php](app/SQL/migrate_procedimiento_emergencia_area.php) — 2 tablas del módulo nuevo.
- [app/SQL/seed_piscina_botiquin_items.php](app/SQL/seed_piscina_botiquin_items.php) — script CLI que inserta plantillas de los 13/32/40+ ítems de Anexo III.
- [app/Models/PiscinaParametroAguaModel.php](app/Models/PiscinaParametroAguaModel.php)
- [app/Models/PiscinaEnsayoLaboratorioModel.php](app/Models/PiscinaEnsayoLaboratorioModel.php)
- [app/Models/PiscinaBotiquinItemModel.php](app/Models/PiscinaBotiquinItemModel.php)
- [app/Models/ProcedimientoEmergenciaAreaModel.php](app/Models/ProcedimientoEmergenciaAreaModel.php)
- [app/Models/ProcedimientoEmergenciaEscenarioModel.php](app/Models/ProcedimientoEmergenciaEscenarioModel.php)
- [app/Libraries/IrapiCalculator.php](app/Libraries/IrapiCalculator.php) — cálculo IRAPI + ISL Langelier a partir de valores medidos. Constantes con tabla de coeficientes de Langelier (Anexo I).
- [app/Libraries/EmergencyProcedureIAService.php](app/Libraries/EmergencyProcedureIAService.php) — generador Haiku por escenario. Reusa patrón de `PlanEmergenciaIAService` pero con `claude-haiku-4-5-20251001` (rápido + barato).
- [app/Controllers/Inspecciones/ProcedimientoEmergenciaAreaController.php](app/Controllers/Inspecciones/ProcedimientoEmergenciaAreaController.php)
- [app/Views/inspecciones/procedimiento-emergencia-area/list.php, form.php, view.php, pdf.php](app/Views/inspecciones/procedimiento-emergencia-area/)
- [app/Views/inspecciones/piscinas/form.php](app/Views/inspecciones/piscinas/form.php) — reescritura completa.
- [app/Views/inspecciones/piscinas/pdf.php](app/Views/inspecciones/piscinas/pdf.php) — reescritura completa.

### A modificar

- [app/Controllers/Inspecciones/InspeccionPiscinasController.php](app/Controllers/Inspecciones/InspeccionPiscinasController.php) — reescribir constantes `ZONAS`, métodos privados `savePiscinas`, `collectMasterFields`, integrar `IrapiCalculator`, nuevos métodos para ítems de botiquín y ensayos.
- [app/Models/InspeccionPiscinasModel.php](app/Models/InspeccionPiscinasModel.php) — nuevos `allowedFields` del master.
- [app/Models/PiscinaDetalleModel.php](app/Models/PiscinaDetalleModel.php) — nuevos `allowedFields`, más campos, relaciones a hijas.
- [app/Config/Routes.php](app/Config/Routes.php) — rutas de `procedimiento-emergencia-area/*`.
- [app/Views/inspecciones/piscinas/view.php](app/Views/inspecciones/piscinas/view.php) — adaptar read-only al nuevo modelo.
- [app/Views/inspecciones/piscinas/list.php](app/Views/inspecciones/piscinas/list.php) — mostrar clasificación IRAPI en el listado.

### Referencia (no modificar)

- [app/Controllers/Inspecciones/PlanEmergenciaController.php](app/Controllers/Inspecciones/PlanEmergenciaController.php) — patrón de integración IA + pantalla `ia-review`.
- [app/Libraries/PlanEmergenciaIAService.php](app/Libraries/PlanEmergenciaIAService.php) — patrón exacto del cliente cURL a Anthropic, `request()`, validación de respuesta.
- [app/Controllers/Inspecciones/MatrizInspeccionesController.php](app/Controllers/Inspecciones/MatrizInspeccionesController.php) — método `generarDetallesPta()` = ejemplo compacto del uso de Haiku con JSON estructurado.
- [app/Controllers/Inspecciones/InspeccionExtintoresController.php](app/Controllers/Inspecciones/InspeccionExtintoresController.php) — patrón canónico de N-ITEMS con fotos por ítem.

---

## Patrones existentes a reusar

- **Traits para el controlador de piscinas:** `AutosaveJsonTrait`, `PreventDuplicateBorradorTrait`, `InspeccionesTransactionalTrait`, `ImagenCompresionTrait` — todos ya en uso por el controlador actual, se mantienen.
- **Subida a tbl_reportes (panel cliente):** método `uploadToReportes()` del controlador actual, mismo `id_report_type=6`, `id_detailreport=46` para piscinas. Se mantiene. Para procedimiento de emergencia por área: nuevo `id_detailreport` (definir al migrar — propuesta 47).
- **Notificación por email al finalizar:** `InspeccionEmailNotifier::enviar()`. Se mantiene en ambos módulos.
- **Cliente HTTP Anthropic:** copiar el método privado `request()` de `PlanEmergenciaIAService.php` al nuevo `EmergencyProcedureIAService.php`. Mantener el mismo patrón de `['ok', 'data', 'error']`.
- **Parseo JSON robusto:** clonar la lógica de `generarDetallesPta()` del `MatrizInspeccionesController` para limpiar fences de markdown antes de `json_decode`.
- **Ícono/UI de generación IA:** reusar la estética del botón "IA: autocompletar" del modal de PTA en la vista de escenarios.

---

## Prompt base para el generador IA del procedimiento

System prompt (fijo):
> Eres experto en planes de emergencia para copropiedades colombianas bajo Decreto 1072/2015 y Resolución 234/2026 del Minsalud. Generas procedimientos de reacción claros, operables y específicos para el área indicada. Respondes en JSON puro sin markdown.

User prompt (inyectado): área, escenario, nombre del cliente, tipo de copropiedad, datos contextuales del área (aforo, horario, recursos disponibles, responsable). Se piden 5 campos: `que_hacer`, `que_no_hacer`, `cuando`, `quien`, `recursos`.

Modelo: `claude-haiku-4-5-20251001` (rápido + barato, ~300-500 tokens out). API key vía `getenv('ANTHROPIC_API_KEY')`.

---

## Verificación

**Local (Fase 1, orden):**

1. Correr `DB_PROD_PASS=dummy php app/SQL/migrate_inspeccion_piscinas_v2.php local` y verificar con `DESCRIBE` de las 5 tablas.
2. Correr `php app/SQL/migrate_procedimiento_emergencia_area.php local` y verificar 2 tablas.
3. Correr `php app/SQL/seed_piscina_botiquin_items.php local` para cargar catálogo botiquín.
4. `php -l` a todos los archivos PHP modificados.
5. Abrir `http://localhost/enterprisesstph/public/inspecciones/piscinas/create` como consultor, llenar una piscina tipo adultos climatizada con pH=7.5, cloro libre=2.0, temperatura=32, turbidez=0.5, ORP=650, ácido cianúrico=30, heterótrofos=<200, E.coli=0, coliformes=0, pseudomonas=0, turbiedad en ideal. Esperar `IRAPI = 0 / Clasificación = Sin riesgo / Óptimo`.
6. Repetir con pH=8.5 (fuera de rango) → esperar VAC=30% → IRAPI=30 → Bajo.
7. Finalizar → verificar PDF generado con IRAPI visible en portada y marco normativo con 3 normas.
8. Email de `InspeccionEmailNotifier` recibido con PDF adjunto.
9. Abrir `/inspecciones/procedimiento-emergencia-area/create`, seleccionar cliente y área=PISCINA, generar con IA el escenario "Sismo con bañistas en el agua", revisar 5 campos devueltos por Haiku, aprobar, finalizar. Verificar PDF + email.

**Producción (Fase 1, al terminar local):**

1. `DB_PROD_PASS=<clave> php app/SQL/migrate_inspeccion_piscinas_v2.php production` → verificar.
2. `DB_PROD_PASS=<clave> php app/SQL/migrate_procedimiento_emergencia_area.php production` → verificar.
3. `DB_PROD_PASS=<clave> php app/SQL/seed_piscina_botiquin_items.php production`.
4. Deploy con el flujo estándar: `git add . && git commit && git checkout main && git merge cycloid && git push origin main && git checkout cycloid` + `bash deploy.sh` en el servidor.
5. Gabriela levanta inspección real + procedimiento real en PROD. Smoke test.

---

## Decisiones pendientes con default razonable

Estas quedan resueltas con un default pero pueden cambiarse antes o durante la ejecución sin refactor grande:

| # | Decisión | Default propuesto | Alternativa |
|---|---|---|---|
| 1 | Modelo IA para generar procedimientos | **Haiku** (`claude-haiku-4-5-20251001`) | Sonnet 4.6 si la calidad no alcanza |
| 2 | Cálculo IRAPI | **Automático al guardar cada piscina** | Manual con captura del valor calculado externamente |
| 3 | Validación botiquín | **Checklist ítem por ítem con cantidad observada** | Solo "Tipo A/B/C completo SI/NO" |
| 4 | Ensayo de laboratorio | **1 fila por tipo (microbiológico, fisicoquímico), con upload de PDF** | Solo fecha + laboratorio |
| 5 | Profundidad | **perfil_profundidad ENUM + max obligatoria + min condicional** | Un solo campo único libre |
| 6 | Pre-carga de escenarios | **8 escenarios hardcoded para área PISCINA** | Lista editable vía BD |
| 7 | PDFs separados | **2 PDFs (inspección piscina + procedimiento emergencia)** | PDF consolidado |

---

## Deploy strategy y seguridad

- **SQL solo por scripts PHP CLI** del repo. Nada de phpMyAdmin ni clientes gráficos.
- **Orden inviolable:** LOCAL primero → verificar OK → PROD después.
- **Credenciales prod** vía `getenv('DB_PROD_PASS')`. Nunca hardcoded (GitHub Secret Scanning bloquea el commit).
- **Sin datos viejos que migrar** — se confirma drop-and-create de `tbl_inspeccion_piscinas` y `tbl_piscina_detalle` actuales. Si producción tiene registros remanentes del esquema antiguo, pausar y confirmar con el usuario antes del drop.
- **API key Anthropic** ya existe como `ANTHROPIC_API_KEY` en el `.env` (usada por `PlanEmergenciaIAService`). No hay que agregar nada nuevo.
- **Push al repo** solo a `main` (nunca `cycloid`) siguiendo el flujo del memoria `feedback_deploy_flow.md`.
