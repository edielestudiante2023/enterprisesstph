# MAPA DE BASE DE DATOS — enterprisesstph

**Fecha de auditoría:** 2026-04-04
**Motor:** MySQL 8 (DigitalOcean Managed)
**Host:** db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com:25060
**Base de datos:** propiedad_horizontal
**Tamaño total:** 18.31 MB
**SSL:** Required
**Certificado:** /www/ca/ca-certificate_cycloid.crt

---

## Usuarios de base de datos

| Usuario | Permisos | Uso |
|---------|----------|-----|
| **cycloid_userdb** | Full access | Aplicación principal (CRUD) |
| **cycloid_readonly** | SELECT only (vistas v_* + tablas maestras) | Portal cliente (Chat Otto) |

---

## Resumen: 107 tablas + 79 vistas

- **107 tablas** (BASE TABLE)
- **79 vistas** (VIEW) — la mayoría prefijo `v_` para el portal cliente readonly
- **72 foreign keys** definidas

---

## Tablas por módulo funcional

### Núcleo del sistema

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_clientes | 60 | 0.05 MB | Clientes (copropiedades) |
| tbl_usuarios | 61 | 0.02 MB | Usuarios del sistema |
| tbl_usuario_roles | 61 | 0.02 MB | Relación usuario-rol |
| tbl_roles | 3 | 0.02 MB | Roles (admin, consultant, client) |
| tbl_consultor | 4 | 0.02 MB | Consultores SST |
| tbl_sesiones_usuario | 220 | 0.08 MB | Sesiones activas |
| tbl_chat_log | 302 | 0.11 MB | Log de Chat Otto |

### Contratos

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_contratos | 70 | 0.08 MB | Contratos de servicio |

### Actas de visita

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_acta_visita | 28 | 0.02 MB | Actas de visita (master) |
| tbl_acta_visita_fotos | 2 | 0.02 MB | Fotos adjuntas |
| tbl_acta_visita_integrantes | 58 | 0.02 MB | Asistentes a la visita |
| tbl_acta_visita_pta | 402 | 0.05 MB | Actividades PTA revisadas |
| tbl_acta_visita_temas | 68 | 0.02 MB | Temas tratados |

### Plan de trabajo anual (PTA)

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_pta_cliente | 5,189 | 1.48 MB | Actividades del PTA (principal) |
| tbl_pta_cliente_audit | 4,108 | 2.52 MB | Auditoría de cambios PTA |
| tbl_pta_cliente_old | 1,483 | 0.33 MB | PTA anterior (legacy) |
| tbl_pta_transiciones | 200 | 0.05 MB | Log de transiciones de estado |
| tbl_inventario_actividades_plandetrabajo | 146 | 0.05 MB | Inventario estándar de actividades |

### Evaluación estándares mínimos

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| evaluacion_inicial_sst | 2,044 | 2.52 MB | Evaluación Decreto 1072 |
| estandares | 4 | 0.02 MB | Estándares base |
| estandares_accesos | 88 | 0.02 MB | Accesos a estándares |
| historial_resumen_estandares | 765 | 0.20 MB | Historial de puntajes |
| historial_resumen_plan_trabajo | 333 | 0.09 MB | Historial puntajes PTA |

### Reportes y documentos

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_reporte | 3,282 | 1.08 MB | Reportes generados (PDFs, inspecciones) |
| report_type_table | 18 | 0.02 MB | Tipos de reporte |
| detail_report | 34 | 0.02 MB | Detalle de tipos |
| document_versions | 1,428 | 0.20 MB | Versionado de documentos |
| tbl_listado_maestro_documentos | 28 | 0.02 MB | Listado maestro Decreto 1072 |

### Pendientes y seguimiento

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_pendientes | 487 | 0.11 MB | Compromisos/pendientes |
| tbl_log_conteo_dias | 361 | 0.06 MB | Conteo de días de pendientes |
| tbl_seguimiento_clientes | 0 | 0.02 MB | Seguimiento comercial |
| tbl_seguimiento_historial | 2 | 0.02 MB | Historial de seguimiento |

### Informe de avances

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_informe_avances | 29 | 1.52 MB | Informes mensuales (con imágenes base64) |

### Inspecciones

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_inspeccion_locativa | 1 | 0.02 MB | Inspección locativa (master) |
| tbl_hallazgo_locativo | 3 | 0.02 MB | Hallazgos locativos (detalle) |
| tbl_inspeccion_extintores | 4 | 0.02 MB | Inspección extintores (master) |
| tbl_extintor_detalle | 9 | 0.02 MB | Detalle extintores |
| tbl_inspeccion_botiquin | 4 | 0.02 MB | Inspección botiquín (master) |
| tbl_elemento_botiquin | 125 | 0.02 MB | Elementos del botiquín (detalle) |
| tbl_inspeccion_gabinetes | 0 | 0.02 MB | Inspección gabinetes (master) |
| tbl_gabinete_detalle | 0 | 0.02 MB | Detalle gabinetes |
| tbl_inspeccion_comunicaciones | 0 | 0.02 MB | Inspección comunicaciones |
| tbl_inspeccion_senalizacion | 0 | 0.02 MB | Inspección señalización (master) |
| tbl_item_senalizacion | 0 | 0.02 MB | Items señalización (detalle) |
| tbl_inspeccion_recursos_seguridad | 0 | 0.02 MB | Inspección recursos de seguridad |

### Capacitaciones

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| capacitaciones_sst | 31 | 0.02 MB | Catálogo de capacitaciones |
| tbl_cronog_capacitacion | 434 | 0.11 MB | Cronograma de capacitaciones |
| tbl_cronog_capacitacion_old | 230 | 0.05 MB | Cronograma anterior (legacy) |
| tbl_reporte_capacitacion | 13 | 0.02 MB | Reportes de capacitación |
| tbl_asistencia_induccion | 13 | 0.02 MB | Asistencia inducción (master) |
| tbl_asistencia_induccion_asistente | 87 | 0.02 MB | Asistentes a inducción (detalle) |

### Evaluaciones

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_evaluaciones | 1 | 0.02 MB | Evaluaciones (master) |
| tbl_evaluacion_pregunta | 10 | 0.02 MB | Preguntas |
| tbl_evaluacion_opcion | 40 | 0.02 MB | Opciones de respuesta |
| tbl_evaluacion_respuestas | 63 | 0.02 MB | Respuestas registradas |
| tbl_evaluacion_sesiones | 7 | 0.02 MB | Sesiones de evaluación |
| tbl_evaluacion_tema | 0 | 0.02 MB | Temas de evaluación |
| tbl_evaluacion_simulacro | 0 | 0.02 MB | Evaluación de simulacros |

### KPIs e indicadores

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_kpis | 17 | 0.02 MB | KPIs definidos |
| tbl_kpi_definition | 17 | 0.02 MB | Definiciones de KPI |
| tbl_kpi_type | 4 | 0.02 MB | Tipos de KPI |
| tbl_kpi_policy | 0 | 0.02 MB | Políticas de KPI |
| tbl_client_kpi | 34 | 0.05 MB | KPIs por cliente |
| tbl_variable_numerator | 15 | 0.02 MB | Variables numerador |
| tbl_variable_denominator | 15 | 0.02 MB | Variables denominador |
| tbl_measurement_period | 2 | 0.02 MB | Períodos de medición |
| tbl_objectives_policy | 3 | 0.02 MB | Objetivos de política |
| tbl_data_owner | 0 | 0.02 MB | Responsables de datos |

### Plan de saneamiento básico

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_kpi_agua_potable | 0 | 0.02 MB | KPI agua potable |
| tbl_kpi_limpieza | 11 | 0.02 MB | KPI limpieza |
| tbl_kpi_plagas | 2 | 0.02 MB | KPI plagas |
| tbl_kpi_residuos | 8 | 0.02 MB | KPI residuos |
| tbl_programa_agua_potable | 9 | 0.02 MB | Programa agua potable |
| tbl_programa_limpieza | 9 | 0.02 MB | Programa limpieza |
| tbl_programa_plagas | 9 | 0.02 MB | Programa plagas |
| tbl_programa_residuos | 10 | 0.02 MB | Programa residuos |
| tbl_plan_saneamiento | 5 | 0.02 MB | Plan de saneamiento |
| tbl_plan_contingencia_agua | 0 | 0.02 MB | Contingencia agua |
| tbl_plan_contingencia_basura | 0 | 0.02 MB | Contingencia basura |
| tbl_plan_contingencia_plagas | 0 | 0.02 MB | Contingencia plagas |
| tbl_auditoria_zona_residuos | 3 | 0.02 MB | Auditoría zona residuos |

### Emergencias y brigadistas

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_plan_emergencia | 0 | 0.02 MB | Plan de emergencia |
| tbl_preparacion_simulacro | 0 | 0.02 MB | Preparación simulacros |
| tbl_hv_brigadista | 0 | 0.02 MB | Hoja de vida brigadistas |
| tbl_probabilidad_peligros | 0 | 0.02 MB | Probabilidad de peligros |

### Dotaciones y vigías

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_dotacion_vigilante | 3 | 0.02 MB | Dotación vigilantes |
| tbl_dotacion_todero | 0 | 0.02 MB | Dotación toderos |
| tbl_dotacion_aseadora | 0 | 0.02 MB | Dotación aseadoras |
| tbl_vigias | 15 | 0.02 MB | Vigías SST |
| tbl_carta_vigia | 0 | 0.02 MB | Cartas de designación vigía |

### Presupuesto SST

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_presupuesto_sst | 0 | 0.02 MB | Presupuesto (master) |
| tbl_presupuesto_categorias | 7 | 0.02 MB | Categorías |
| tbl_presupuesto_items | 22 | 0.02 MB | Items del presupuesto |
| tbl_presupuesto_detalle | 160 | 0.02 MB | Detalle de ejecución |

### Matrices y mantenimientos

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_matrices | 43 | 0.02 MB | Matrices de riesgo |
| tbl_matriz_vulnerabilidad | 0 | 0.02 MB | Matriz de vulnerabilidad |
| tbl_mantenimientos | 18 | 0.02 MB | Tipos de mantenimiento |
| tbl_vencimientos_mantenimientos | 277 | 0.02 MB | Vencimientos por cliente |

### Otros

| Tabla | Registros | Tamaño | Descripción |
|-------|-----------|--------|-------------|
| tbl_agendamientos | 27 | 0.02 MB | Agendamiento de visitas |
| tbl_ciclos_visita | 56 | 0.02 MB | Ciclos de visita |
| tbl_lookerstudio | 60 | 0.02 MB | URLs de Looker Studio |
| tbl_urls | 11 | 0.02 MB | URLs del sistema |
| client_policies | 1,496 | 0.09 MB | Políticas por cliente |
| policy_types | 44 | 0.02 MB | Tipos de política |
| dashboard_items | 62 | 0.02 MB | Items del dashboard |
| accesos | 59 | 0.02 MB | Registro de accesos |
| tbl_planilla_ss_inspeccion | 0 | 0.02 MB | Planilla seguridad social |
| tbl_planillas_seguridad_social | 0 | 0.02 MB | Planillas SS |
| tbl_proveedor_servicio | 0 | 0.02 MB | Proveedores |
| tbl_certificado_servicio | 0 | 0.02 MB | Certificados de servicio |
| tbl_matricescycloid | 0 | 0.02 MB | Matrices Cycloid |
| tbl_tests | 0 | 0.02 MB | Tabla de pruebas |
| prueba | 2 | 0.02 MB | Tabla de prueba |

---

## Relaciones (Foreign Keys)

### tbl_clientes es la tabla central — 40+ tablas dependen de ella

```
tbl_clientes (id_cliente)
├── tbl_contratos
├── tbl_acta_visita
├── tbl_pendientes (via tbl_acta_visita)
├── tbl_pta_cliente
├── tbl_reporte
├── tbl_informe_avances
├── tbl_client_kpi
├── tbl_inspeccion_locativa
├── tbl_inspeccion_extintores
├── tbl_inspeccion_botiquin
├── tbl_inspeccion_gabinetes
├── tbl_inspeccion_comunicaciones
├── tbl_inspeccion_senalizacion
├── tbl_inspeccion_recursos_seguridad
├── tbl_cronog_capacitacion_old
├── tbl_reporte_capacitacion
├── tbl_asistencia_induccion
├── tbl_evaluacion_simulacro
├── tbl_kpi_agua_potable / limpieza / plagas / residuos
├── tbl_programa_agua_potable / limpieza / plagas / residuos
├── tbl_plan_saneamiento
├── tbl_plan_emergencia
├── tbl_preparacion_simulacro
├── tbl_matrices
├── tbl_matriz_vulnerabilidad
├── tbl_probabilidad_peligros
├── tbl_dotacion_vigilante / todero / aseadora
├── tbl_vigias
├── tbl_carta_vigia
├── tbl_hv_brigadista
├── tbl_lookerstudio
├── tbl_vencimientos_mantenimientos
├── tbl_auditoria_zona_residuos
└── tbl_certificado_servicio (sin FK pero usa id_cliente)
```

### Relaciones de inspecciones (master → detalle)

```
tbl_inspeccion_extintores → tbl_extintor_detalle
tbl_inspeccion_gabinetes → tbl_gabinete_detalle
tbl_inspeccion_botiquin → tbl_elemento_botiquin
tbl_inspeccion_locativa → tbl_hallazgo_locativo
tbl_inspeccion_senalizacion → tbl_item_senalizacion
```

### Relaciones de actas

```
tbl_acta_visita
├── tbl_acta_visita_fotos
├── tbl_acta_visita_integrantes
├── tbl_acta_visita_pta → tbl_pta_cliente
└── tbl_acta_visita_temas
```

### Relaciones de usuarios

```
tbl_usuarios
├── tbl_usuario_roles → tbl_roles
└── tbl_sesiones_usuario
```

### Relaciones de presupuesto

```
tbl_presupuesto_sst
└── tbl_presupuesto_items → tbl_presupuesto_categorias
    └── tbl_presupuesto_detalle
```

---

## Vistas (79 total)

### Vistas v_* para portal cliente readonly (60)

Réplica SELECT-only de las tablas principales. Usadas por el usuario `cycloid_readonly` para el Chat Otto del portal cliente.

### Vistas de negocio (19)

| Vista | Descripción |
|-------|-------------|
| cronograma_capacitaciones_cliente | Cronograma con datos del cliente |
| evaluacion_inicial_cliente | Evaluación inicial con datos del cliente |
| evaluacion_inicial_cliente_consultor | Evaluación con datos de cliente y consultor |
| mantenimientos_por_vencer | Mantenimientos próximos a vencer |
| pendientes_abiertos_vencidos | Pendientes abiertos fuera de fecha |
| pendientes_del_cliente | Pendientes filtrados por cliente |
| plan_de_trabajo_del_cliente | PTA con datos del cliente |
| resumen_estandares_cliente | Resumen puntajes estándares |
| resumen_mensual_plan_trabajo | Resumen mensual del PTA |
| view_clientes_consultores | Clientes con su consultor |
| vista_cronograma_capacitaciones | Cronograma general |
| vw_consumo_usuarios | Consumo/actividad de usuarios |
| vw_reporte_completo | Reporte unificado completo |

---

## Tablas más grandes (por registros)

| Tabla | Registros | Tamaño |
|-------|-----------|--------|
| tbl_pta_cliente | 5,189 | 1.48 MB |
| tbl_pta_cliente_audit | 4,108 | 2.52 MB |
| tbl_reporte | 3,282 | 1.08 MB |
| evaluacion_inicial_sst | 2,044 | 2.52 MB |
| client_policies | 1,496 | 0.09 MB |
| tbl_pta_cliente_old | 1,483 | 0.33 MB |
| document_versions | 1,428 | 0.20 MB |

---

## Tablas vacías (0 registros) — 28 tablas

tbl_carta_vigia, tbl_certificado_servicio, tbl_data_owner, tbl_dotacion_aseadora, tbl_dotacion_todero, tbl_evaluacion_simulacro, tbl_evaluacion_tema, tbl_gabinete_detalle, tbl_hv_brigadista, tbl_inspeccion_comunicaciones, tbl_inspeccion_gabinetes, tbl_inspeccion_recursos_seguridad, tbl_inspeccion_senalizacion, tbl_item_senalizacion, tbl_kpi_agua_potable, tbl_kpi_policy, tbl_matriz_vulnerabilidad, tbl_matricescycloid, tbl_plan_contingencia_agua, tbl_plan_contingencia_basura, tbl_plan_contingencia_plagas, tbl_plan_emergencia, tbl_planilla_ss_inspeccion, tbl_planillas_seguridad_social, tbl_preparacion_simulacro, tbl_presupuesto_sst, tbl_probabilidad_peligros, tbl_proveedor_servicio, tbl_seguimiento_clientes, tbl_tests

> **Nota:** 28 de 107 tablas (26%) están vacías. Algunas son módulos pendientes de implementar, otras podrían ser obsoletas.
