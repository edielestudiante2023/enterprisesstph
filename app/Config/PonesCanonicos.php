<?php
/**
 * PONs canonicos para Plan de Emergencia — propiedad horizontal residencial.
 * Cada PON sigue la estructura canonica: codigo, titulo, amenaza_ref, objetivo,
 * alcance, definiciones, responsables, procedimiento, medidas_preventivas, recomendaciones.
 *
 * amenaza_ref: clave de $ultimaProb (tbl_inspeccion_probabilidad_peligros) con la que
 * se enlaza cada PON para que Fase 2 (IA) pueda personalizar con la probabilidad del cliente.
 *
 * Campo responsables: estructura asociativa con tres subgrupos —
 *   internos              : actores dentro del conjunto que ejecutan el procedimiento
 *   contratistas_externos : empresas con contrato activo (puede ser array vacio)
 *   organismos_socorro    : lineas publicas de emergencia
 *
 * Referencia cruzada a la Nota Aclaratoria: en cada PON, la primera mencion de
 * "brigada", "brigadistas", "coordinador de evacuacion" o "grupos funcionales"
 * dentro de objetivo, alcance, procedimiento, medidas_preventivas o recomendaciones
 * incluye una unica vez la frase
 *   (ver Nota Aclaratoria al inicio de los Procedimientos Operativos Normalizados)
 *
 * Fuentes: Ley 1523/2012, Decreto 2157/2017, Decreto 1072/2015 art. 2.2.4.6.25,
 * NTC 1700, Manual UNGRD, guias IDRD Bogota, Cruz Roja Colombiana, Res. 0256/2014.
 */

return [

    // ============================================================
    'pon_01_incendio' => [
        'codigo'      => '01',
        'titulo'      => 'Incendio en areas comunes o unidades privadas',
        'amenaza_ref' => 'incendios',
        'flowchart' => [
            'pasos' => [
                ['tipo'=>'accion',   'texto'=>'Quien detecte el conato activa la alarma y avisa a porteria', 'responsable'=>'Primer respondiente'],
                ['tipo'=>'accion',   'texto'=>'Porteria confirma ubicacion y llama a Bomberos linea 119',     'responsable'=>'Vigilancia'],
                ['tipo'=>'accion',   'texto'=>'Si el conato es menor, brigadista usa extintor metodo PASS',   'responsable'=>'Brigada'],
                ['tipo'=>'decision', 'texto'=>'Conato controlado?',                                            'salida_si'=>'Cierre del evento, reporte y reposicion de extintores'],
                ['tipo'=>'accion',   'texto'=>'Activar evacuacion ordenada por rutas senalizadas',              'responsable'=>'Brigada evacuacion'],
                ['tipo'=>'accion',   'texto'=>'Acordonar el area y restringir accesos a personal no autorizado','responsable'=>'Vigilancia'],
                ['tipo'=>'accion',   'texto'=>'Recibir a Bomberos y delegar el manejo del incidente',          'responsable'=>'Jefe de brigada'],
                ['tipo'=>'fin',      'texto'=>'Fin de emergencia, verificacion y evaluacion post-evento',      'responsable'=>'Coordinador'],
            ],
        ],
        'objetivo'    => 'Establecer las acciones estandarizadas que la brigada de emergencia (ver Nota Aclaratoria al inicio de los Procedimientos Operativos Normalizados), el personal de vigilancia en porteria y los residentes deben ejecutar ante la presencia de fuego incipiente o declarado en zonas comunes o unidades privadas del conjunto, con el fin de proteger la vida, controlar el evento en su fase inicial cuando sea posible y coordinar la intervencion del Cuerpo Oficial de Bomberos conforme a la Ley 1523 de 2012.',
        'alcance'     => 'Aplica a la totalidad del personal de vigilancia, administracion, brigada de la copropiedad, contratistas, residentes, propietarios y visitantes que se encuentren dentro de las areas comunes y privadas del conjunto residencial al momento de presentarse un conato o incendio declarado de cualquier clase (A, B, C o K).',
        'definiciones' => [
            'Conato de incendio' => 'Fuego de pequenas proporciones que puede ser controlado de forma inmediata con un extintor portatil por personal capacitado, sin requerir intervencion de bomberos.',
            'Incendio declarado' => 'Fuego que ha superado la fase incipiente, presenta propagacion activa y requiere intervencion del Cuerpo Oficial de Bomberos y evacuacion total o parcial de la edificacion.',
            'Clases de fuego' => 'Clase A solidos comunes, Clase B liquidos y gases inflamables, Clase C equipos energizados, Clase K aceites y grasas de cocina, conforme a NTC 2885.',
            'Punto de encuentro' => 'Sitio externo previamente definido y senalizado donde se reunen los ocupantes evacuados para realizar conteo y verificacion.',
            'PASS' => 'Tecnica de operacion del extintor portatil: Halar el pasador, Apuntar a la base del fuego, Apretar la palanca, Sacudir de lado a lado.',
        ],
        'responsables' => [
            'internos' => [
                'Personal de vigilancia en porteria (primera respuesta)',
                'Brigada de la copropiedad o residentes/contratistas capacitados',
                'Administrador del conjunto (notificacion y coordinacion)',
            ],
            'contratistas_externos' => [],
            'organismos_socorro' => [
                'Bomberos — linea 119',
            ],
        ],
        'procedimiento' => [
            '1. Quien detecte el evento debe dar aviso inmediato a la porteria por intercomunicador o presencialmente, indicando ubicacion exacta (torre, piso, apartamento o area), tipo de material involucrado y presencia de personas.',
            '2. El personal de vigilancia en porteria activa el sistema de alarma sonora del conjunto y anuncia por el canal de comunicacion interna la clave establecida (Codigo Rojo) sin generar panico, y registra la hora exacta en la minuta.',
            '3. La brigada de la copropiedad se desplaza al sitio con el equipo basico de control (extintor adecuado a la clase de fuego, linterna y radio), evalua si se trata de conato controlable o incendio declarado y notifica al Administrador.',
            '4. Si es conato y se cuenta con extintor adecuado a la clase de fuego, un brigadista o residente capacitado en uso de extintores procede a la extincion aplicando la tecnica PASS, manteniendo siempre una via de escape a la espalda.',
            '5. Si el fuego involucra equipos energizados (Clase C), se debe desenergizar el area desde el tablero general antes de cualquier intervencion y utilizar unicamente extintor de CO2 o polvo quimico seco multiproposito.',
            '6. Si el fuego es declarado o supera la capacidad de control inicial, el personal de vigilancia o el Administrador activa de inmediato la linea 119 (Bomberos), suministrando direccion exacta, punto de referencia, tipo de evento y numero estimado de personas.',
            '7. El personal disponible de la brigada y la vigilancia orientan la salida ordenada de los ocupantes por las rutas senalizadas conforme a NTC 1700, priorizando personas con movilidad reducida, ninos, gestantes y adultos mayores, sin uso de ascensores.',
            '8. El personal de vigilancia despeja el acceso vehicular y peatonal para facilitar el ingreso de las maquinas de bomberos, y mantiene libre el hidrante mas cercano.',
            '9. En el punto de encuentro se realiza el conteo de ocupantes y se reporta a los organismos de socorro cualquier persona faltante o atrapada.',
            '10. Una vez controlado el evento, ninguna persona puede reingresar hasta que Bomberos certifique que la edificacion es segura y se descarte riesgo de reignicion o estructural.',
            '11. El Administrador levanta informe del evento dentro de las 24 horas siguientes, incluyendo causa probable, tiempo de respuesta, danos, lesionados y acciones correctivas, y lo remite al consejo de administracion conforme al Decreto 1072/2015 art. 2.2.4.6.25.',
        ],
        'medidas_preventivas' => [
            'Realizar mantenimiento anual certificado a la red contra incendio, gabinetes y extintores conforme a NTC 2885 y NSR-10 Titulo J.',
            'Mantener libres de obstrucciones las rutas de evacuacion, escaleras de emergencia y puertas cortafuego en todo momento.',
            'Inspeccionar trimestralmente las instalaciones electricas comunes y exigir certificacion RETIE en intervenciones.',
            'Prohibir el almacenamiento de liquidos inflamables en cuartos de basura, parqueaderos y zonas de servicios.',
            'Capacitar a la brigada minimo dos veces al ano en uso de extintores y control de conatos, conforme a Resolucion 0256 de 2014.',
            'Realizar simulacro general de evacuacion por incendio al menos una vez al ano.',
        ],
        'recomendaciones' => [
            'No utilizar agua sobre fuegos Clase B, C ni K bajo ninguna circunstancia.',
            'Verificar mensualmente que los extintores se encuentren cargados, senalizados y a una altura no mayor a 1.50 m del piso.',
            'Difundir entre los residentes el codigo de emergencia y el punto de encuentro mediante carteleras y reuniones de copropietarios.',
            'Registrar en bitacora toda activacion del sistema, incluso simulacros y falsas alarmas.',
        ],
    ],

    // ============================================================
    'pon_02_sismo' => [
        'codigo'      => '02',
        'titulo'      => 'Sismo / Terremoto',
        'amenaza_ref' => 'sismos',
        'flowchart' => [
            'pasos' => [
                ['tipo'=>'accion',   'texto'=>'Durante el sismo: agacharse, cubrirse y sujetarse',            'responsable'=>'Todos los ocupantes'],
                ['tipo'=>'accion',   'texto'=>'No correr, no usar ascensores, alejarse de ventanales',         'responsable'=>'Todos los ocupantes'],
                ['tipo'=>'accion',   'texto'=>'Al cesar el movimiento, vigilancia activa alarma de evacuacion','responsable'=>'Vigilancia'],
                ['tipo'=>'accion',   'texto'=>'Inspeccion visual rapida: grietas, fugas, personas atrapadas', 'responsable'=>'Brigada'],
                ['tipo'=>'decision', 'texto'=>'Hay danos o atrapados?',                                       'salida_si'=>'Llamar Bomberos 119 y Defensa Civil 144'],
                ['tipo'=>'accion',   'texto'=>'Evacuar ordenadamente por escaleras hacia punto de encuentro', 'responsable'=>'Brigada evacuacion'],
                ['tipo'=>'accion',   'texto'=>'Conteo y censo de ocupantes en punto de encuentro',            'responsable'=>'Coordinador'],
                ['tipo'=>'fin',      'texto'=>'Esperar autorizacion oficial para reingreso al conjunto',      'responsable'=>'Administrador'],
            ],
        ],
        'objetivo'    => 'Definir las acciones de autoproteccion y respuesta que deben adoptar los ocupantes del conjunto residencial antes, durante y despues de un movimiento telurico, garantizando la proteccion de la vida, la evaluacion estructural posterior y la activacion oportuna del plan de evacuacion conforme a la Ley 1523 de 2012 y las directrices de la UNGRD.',
        'alcance'     => 'Aplica a todos los residentes, propietarios, visitantes, contratistas, integrantes de la brigada (ver Nota Aclaratoria al inicio de los Procedimientos Operativos Normalizados), personal de vigilancia y administracion presentes en cualquier area del conjunto al momento de un evento sismico, sin importar la magnitud percibida del mismo.',
        'definiciones' => [
            'Sismo' => 'Vibracion de la corteza terrestre originada por la liberacion subita de energia, que se transmite en forma de ondas y puede causar danos estructurales o personales.',
            'Replica' => 'Sismo de menor magnitud que ocurre despues del sismo principal en la misma zona, pudiendo persistir por dias o semanas.',
            'Triangulo de vida' => 'Espacio seguro que se forma junto a estructuras solidas (muros de carga, columnas) donde una persona puede protegerse de caida de elementos.',
            'Zona segura interna' => 'Sitio dentro de la edificacion alejado de ventanas, lamparas, repisas y mobiliario alto, junto a muros estructurales.',
            'Evaluacion habitabilidad' => 'Inspeccion tecnica posterior al sismo para determinar si una edificacion puede ser ocupada nuevamente, realizada por ingeniero estructural externo o equipo oficial de evaluacion de danos.',
        ],
        'responsables' => [
            'internos' => [
                'Personal de vigilancia en porteria',
                'Brigada de la copropiedad (residentes y contratistas capacitados)',
                'Administrador del conjunto (notificacion)',
            ],
            'contratistas_externos' => [],
            'organismos_socorro' => [
                'Bomberos — linea 119 (busqueda y rescate tecnico)',
                'Defensa Civil — linea 144 (busqueda y rescate)',
                'Cruz Roja — linea 132 (atencion a heridos)',
            ],
        ],
        'procedimiento' => [
            '1. Durante el sismo, ningun ocupante debe intentar evacuar mientras dure el movimiento. Se debe ubicar la zona segura interna mas cercana, alejada de ventanas, fachadas, lamparas y mobiliario que pueda caer.',
            '2. Aplicar la tecnica de autoproteccion: agacharse, cubrir cabeza y cuello con ambos brazos y sujetarse de un elemento estable, o ubicarse en triangulo de vida junto a muros estructurales.',
            '3. Si la persona se encuentra en cama, permanecer en ella cubriendo la cabeza con almohada. Si esta en silla de ruedas, frenar la silla y proteger cabeza y cuello.',
            '4. En parqueaderos o vias internas, detener el vehiculo lejos de postes, arboles, fachadas y puentes; permanecer dentro con cinturon de seguridad puesto.',
            '5. Una vez cesado el movimiento, el personal de vigilancia y los brigadistas disponibles verifican novedades en las areas comunes. La vigilancia desenergiza el ascensor y cierra las llaves generales de gas si estan accesibles y seguras.',
            '6. La brigada y la vigilancia orientan la salida controlada por las escaleras hacia el punto de encuentro externo, NUNCA por ascensores, atendiendo prioritariamente a personas con movilidad reducida.',
            '7. La brigada realiza inspeccion visual rapida de columnas, muros estructurales, escaleras y juntas de dilatacion en busca de fisuras, desplomes o desprendimientos. Cualquier dano significativo se reporta de inmediato.',
            '8. En el punto de encuentro se realiza censo de ocupantes. Las personas faltantes se reportan a Defensa Civil (144) y Bomberos (119), quienes son los responsables oficiales de la busqueda y rescate tecnico; la brigada de la copropiedad NO ejecuta rescate en estructuras colapsadas.',
            '9. Se atienden lesionados con el botiquin de primeros auxilios, priorizando hemorragias, fracturas y crisis emocionales. Los casos graves se trasladan o se solicita ambulancia al 123.',
            '10. Ningun ocupante puede reingresar a la edificacion hasta que un ingeniero estructural externo o el equipo oficial de evaluacion verifique la habitabilidad de la estructura.',
            '11. La administracion mantiene informados a los residentes sobre replicas, instrucciones de las autoridades y horarios de reingreso, utilizando los canales oficiales de comunicacion.',
        ],
        'medidas_preventivas' => [
            'Asegurar al muro biblioteca, estantes altos, televisores y elementos pesados en zonas comunes y, mediante recomendacion, en unidades privadas.',
            'Identificar y senalizar las zonas seguras internas y rutas de evacuacion en cada piso conforme a NTC 1700.',
            'Realizar simulacro nacional de sismo cada ano coordinado con la UNGRD.',
            'Mantener actualizado el censo de residentes con condicion especial de salud o movilidad reducida.',
            'Garantizar el cumplimiento de la NSR-10 en cualquier intervencion estructural.',
        ],
        'recomendaciones' => [
            'No correr, no gritar, no usar ascensores durante ni despues del sismo.',
            'Tener en cada unidad un kit basico de emergencia con linterna, radio a pilas, agua y silbato.',
            'Conocer la ubicacion de las llaves generales de agua, gas y energia de la unidad privada.',
            'Atender unicamente la informacion oficial de UNGRD, IDIGER o Cruz Roja, evitando rumores en redes sociales.',
        ],
    ],

    // ============================================================
    'pon_03_asalto_hurto' => [
        'codigo'      => '03',
        'titulo'      => 'Asalto, hurto o intrusion armada',
        'amenaza_ref' => 'asalto_hurto',
        'flowchart' => [
            'pasos' => [
                ['tipo'=>'accion',   'texto'=>'Vigilancia detecta el evento o recibe alerta silenciosa',       'responsable'=>'Vigilancia'],
                ['tipo'=>'accion',   'texto'=>'NO confrontar. Obedecer indicaciones del agresor',             'responsable'=>'Todos los presentes'],
                ['tipo'=>'accion',   'texto'=>'Activar codigo silencioso, no alarma sonora',                   'responsable'=>'Vigilancia'],
                ['tipo'=>'accion',   'texto'=>'Llamar a Policia Nacional linea 123',                           'responsable'=>'Vigilancia'],
                ['tipo'=>'decision', 'texto'=>'Agresores se retiraron?',                                        'salida_si'=>'Iniciar protocolo post-evento y atencion a afectados'],
                ['tipo'=>'accion',   'texto'=>'Mantener el perimetro seguro y esperar Policia',                'responsable'=>'Vigilancia'],
                ['tipo'=>'accion',   'texto'=>'Asistir a Policia con grabacion CCTV y descripcion',            'responsable'=>'Administrador'],
                ['tipo'=>'fin',      'texto'=>'Atencion psicologica a afectados y reporte a Fiscalia',         'responsable'=>'Administrador'],
            ],
        ],
        'objetivo'    => 'Establecer el protocolo de respuesta no confrontacional ante un evento de asalto, hurto o intrusion armada en areas comunes o privadas del conjunto residencial, privilegiando la proteccion de la vida y la integridad fisica de los ocupantes sobre cualquier bien material, y coordinando oportunamente con la Policia Nacional a traves del personal de vigilancia en porteria.',
        'alcance'     => 'Aplica al personal de vigilancia, administracion, residentes, visitantes y contratistas que se encuentren en el conjunto al momento de un intento o consumacion de hurto, asalto a mano armada, secuestro o intrusion violenta de personas ajenas al conjunto.',
        'definiciones' => [
            'Hurto' => 'Apoderamiento de cosa mueble ajena con animo de aprovecharse de ella, conforme al Codigo Penal Colombiano.',
            'Asalto a mano armada' => 'Hurto agravado mediante el uso o amenaza de armas de fuego, cortopunzantes o contundentes contra una o varias victimas.',
            'Intrusion' => 'Ingreso no autorizado y por la fuerza de personas ajenas al conjunto a las areas privadas o comunes de uso restringido.',
            'Codigo silencioso' => 'Senal discreta que activa el personal de vigilancia para alertar a la central de monitoreo o porteria sin que el agresor lo perciba.',
            'Boton de panico' => 'Dispositivo fisico o digital conectado a central de monitoreo o cuadrante de Policia que permite alertar de manera inmediata.',
        ],
        'responsables' => [
            'internos' => [
                'Personal de vigilancia de la empresa de seguridad (primera y principal respuesta)',
                'Administrador del conjunto (notificacion posterior al evento)',
            ],
            'contratistas_externos' => [
                'Empresa de monitoreo o central de alarmas (si existe contrato activo)',
            ],
            'organismos_socorro' => [
                'Policia Nacional — linea 123 (llamada ejecutada por la vigilancia)',
            ],
        ],
        'procedimiento' => [
            '1. Ante la deteccion del evento, NINGUNA persona debe enfrentar a los agresores. La consigna principal es preservar la vida, obedecer las indicaciones del agresor y evitar movimientos bruscos o miradas directas.',
            '2. El personal de vigilancia, sin alertar al agresor, activa el boton de panico o el codigo silencioso establecido con la central de monitoreo. La llamada a la linea 123 la ejecuta directamente el vigilante desde la porteria, dado que el Administrador rara vez esta presente en el momento del evento.',
            '3. Si es posible y seguro, otro miembro del personal observa y memoriza caracteristicas fisicas de los agresores: cantidad, contextura, vestimenta, armas, vehiculo, placas y direccion de huida.',
            '4. Los residentes que perciban el evento desde sus apartamentos deben asegurar las puertas, alejarse de ventanas, llamar discretamente a la linea 123 e indicar direccion exacta del conjunto, torre y piso.',
            '5. Bajo ninguna circunstancia se debe activar la alarma sonora general ni anunciar el evento por intercomunicador, ya que esto puede precipitar reaccion violenta del agresor.',
            '6. El personal de vigilancia debe abstenerse de perseguir a los agresores, hacer disparos o intentar recuperar bienes. Su funcion es resguardar a las personas y esperar la llegada de la autoridad.',
            '7. Una vez los agresores abandonen el conjunto, el vigilante cierra los accesos, registra hora exacta en la minuta y comunica la situacion al Administrador y a la central de monitoreo.',
            '8. Se atienden a las victimas en sitio seguro: verificar lesiones, estado emocional y necesidades inmediatas. Si hay heridos, llamar al 123 solicitando ambulancia.',
            '9. Cuando llegue la Policia, se entrega la informacion recopilada (descripciones, videos del CCTV, testimonios) y se facilita el recorrido para inspeccion ocular y recoleccion de evidencia.',
            '10. La administracion brinda acompanamiento a las victimas para la presentacion de la denuncia ante Fiscalia o URI, y registra el evento en la bitacora de seguridad del conjunto.',
            '11. El Administrador analiza el caso posteriormente, identifica fallas y aprueba medidas correctivas (refuerzo de iluminacion, CCTV, revision de protocolos).',
        ],
        'medidas_preventivas' => [
            'Mantener operativo el sistema de CCTV con grabacion minima de 30 dias y cobertura de accesos, parqueaderos y zonas comunes criticas.',
            'Verificar identidad de visitantes con anuncio previo del residente antes de permitir el ingreso.',
            'Capacitar al personal de vigilancia en protocolos no confrontacionales y manejo del boton de panico.',
            'Coordinar con el Cuadrante de Policia Nacional rondas periodicas y frentes de seguridad ciudadana.',
            'Mantener iluminacion adecuada en accesos, parqueaderos y zonas perimetrales.',
            'Revisar periodicamente cerraduras, talanqueras, citofonos y cerca electrica.',
        ],
        'recomendaciones' => [
            'No exhibir objetos de valor en areas comunes ni en redes sociales.',
            'Evitar resistencia fisica o verbal ante agresores armados.',
            'Conocer el numero del cuadrante de Policia y tenerlo visible en porteria.',
            'Reportar a la administracion cualquier persona, vehiculo o conducta sospechosa.',
        ],
    ],

    // ============================================================
    'pon_04_inundacion' => [
        'codigo'      => '04',
        'titulo'      => 'Inundacion por lluvia desbordada o parqueadero inundado',
        'amenaza_ref' => 'inundaciones',
        'flowchart' => [
            'pasos' => [
                ['tipo'=>'accion',   'texto'=>'Detectar inundacion (lluvia desbordada o dano de tuberia)',   'responsable'=>'Primer respondiente'],
                ['tipo'=>'accion',   'texto'=>'Avisar a porteria y administrador inmediatamente',             'responsable'=>'Vigilancia'],
                ['tipo'=>'accion',   'texto'=>'Cortar energia electrica del area afectada desde tablero',     'responsable'=>'Vigilancia'],
                ['tipo'=>'decision', 'texto'=>'Hay riesgo electrico o estructural?',                           'salida_si'=>'Evacuar area y llamar Bomberos 119'],
                ['tipo'=>'accion',   'texto'=>'Iniciar achique con recursos propios (motobombas, escobillas)', 'responsable'=>'Brigada'],
                ['tipo'=>'accion',   'texto'=>'Contener punto de origen (cerrar valvula o llave principal)',   'responsable'=>'Brigada'],
                ['tipo'=>'accion',   'texto'=>'Documentar danos con fotos para aseguradora',                   'responsable'=>'Administrador'],
                ['tipo'=>'fin',      'texto'=>'Limpieza, secado y reporte a aseguradora de la copropiedad',    'responsable'=>'Administrador'],
            ],
        ],
        'objetivo'    => 'Establecer el procedimiento para atender eventos de inundacion en areas comunes del conjunto, considerando las dos causas realmente frecuentes en propiedad horizontal residencial: (a) lluvia desbordada que ingresa a sotanos, parqueaderos o primeros pisos, y (b) parqueadero inundado por acumulacion de aguas lluvia o por dano de tuberia interna. La brigada (ver Nota Aclaratoria al inicio de los Procedimientos Operativos Normalizados) garantiza la proteccion de personas, equipos electricos y la integridad estructural de la edificacion.',
        'alcance'     => 'Aplica a sotanos, parqueaderos, cuartos de bombas, cuartos electricos, ascensores, zonas comunes en general y unidades privadas afectadas. Involucra al personal de vigilancia en porteria, a la brigada de la copropiedad, a la administracion y a los residentes.',
        'definiciones' => [
            'Inundacion por lluvia desbordada' => 'Acumulacion de agua proveniente de precipitaciones intensas que supera la capacidad de bajantes, sumideros o rejillas perimetrales e ingresa a areas del conjunto.',
            'Parqueadero inundado' => 'Acumulacion de agua en sotanos o parqueaderos por lluvia que ingresa desde rampas o por rotura/fuga de tuberia interna del conjunto.',
            'Reflujo' => 'Retorno de aguas servidas desde el alcantarillado hacia las instalaciones internas por sobrecarga del sistema externo.',
            'Llave de paso general' => 'Valvula que permite cortar el suministro principal de agua del conjunto o de una unidad especifica.',
            'Riesgo electrico por inundacion' => 'Peligro de electrocucion por contacto del agua con instalaciones, tableros o equipos energizados.',
        ],
        'responsables' => [
            'internos' => [
                'Personal de vigilancia en porteria',
                'Brigada de la copropiedad',
                'Administrador del conjunto (coordinacion)',
            ],
            'contratistas_externos' => [
                'Aseguradora de la copropiedad (reclamacion posterior)',
            ],
            'organismos_socorro' => [
                'Bomberos — linea 119 (achique, contencion)',
                'Defensa Civil — linea 144',
            ],
        ],
        'procedimiento' => [
            '1. Quien detecte la inundacion debe avisar de inmediato a la porteria indicando ubicacion exacta, nivel del agua y causa aparente: lluvia desbordada ingresando por rampas/rejillas, o acumulacion en parqueadero por lluvia o por dano visible de tuberia interna.',
            '2. El personal de vigilancia registra el evento en la minuta y comunica al Administrador y a los brigadistas disponibles. Si la inundacion se presenta cerca de tableros o equipos energizados, la vigilancia ejecuta el corte de energia de las zonas afectadas desde el tablero general, maniobra que es de su responsabilidad directa.',
            '3. Si la causa es lluvia desbordada: la brigada y la vigilancia verifican sumideros, rejillas y bajantes obstruidos, retirando hojas, basura u objetos que impidan el drenaje.',
            '4. Si la causa es parqueadero inundado por dano de tuberia interna: la brigada localiza la llave de paso general o la valvula sectorizada de la zona afectada y procede a cerrarla para detener el flujo.',
            '5. Se prohibe terminantemente el ingreso de personas al area inundada hasta verificar que esta desenergizada y que el nivel del agua no represente riesgo de arrastre o caida.',
            '6. Si la inundacion afecta el foso del ascensor, la vigilancia lo desenergiza de inmediato y el equipo permanece fuera de servicio hasta inspeccion tecnica de la empresa mantenedora.',
            '7. La brigada activa las bombas de achique existentes o solicita apoyo de Bomberos (119) cuando el volumen de agua supere la capacidad propia o exista riesgo para la edificacion.',
            '8. Se evacuan los vehiculos del parqueadero afectado, siempre que el acceso sea seguro. Se prioriza el resguardo de personas sobre bienes materiales.',
            '9. Se protegen documentos, equipos electronicos y bienes de valor en las unidades afectadas, trasladandolos a niveles superiores.',
            '10. Una vez controlada la causa, la brigada realiza secado, limpieza y desinfeccion del area, y solo se reenergiza cuando se verifique que las instalaciones electricas estan secas.',
            '11. El Administrador documenta el evento con fotografias, hora, causa, danos y acciones tomadas, informa a la aseguradora y al consejo de administracion para reclamacion y acciones correctivas. Cuando la inundacion haya sido prolongada o afecte cimentacion, muros o losas, se solicita inspeccion estructural externa.',
        ],
        'medidas_preventivas' => [
            'Realizar mantenimiento semestral a bajantes, canales, sumideros y rejillas perimetrales para evitar lluvia desbordada.',
            'Inspeccionar trimestralmente tanques de almacenamiento, valvulas, bombas y red hidraulica interna para detectar fugas potenciales en parqueaderos.',
            'Mantener libres y operativas las bombas de achique en sotanos y cuartos de bombas.',
            'Verificar el estado de empaques, sellos y juntas de tuberias en cuartos comunes.',
            'Disponer de llaves para todas las valvulas sectorizadas en porteria, debidamente identificadas.',
        ],
        'recomendaciones' => [
            'No caminar sobre areas inundadas si no se ha desenergizado el sector.',
            'No utilizar el ascensor durante o despues de una inundacion en sotanos hasta certificacion tecnica.',
            'Reportar fugas menores de inmediato para evitar danos mayores.',
            'Mantener actualizado el plano hidraulico del conjunto en la oficina de administracion.',
        ],
    ],

    // ============================================================
    'pon_05_vendaval' => [
        'codigo'      => '05',
        'titulo'      => 'Vendaval, granizada o tormenta electrica',
        'amenaza_ref' => 'vendavales',
        'flowchart' => [
            'pasos' => [
                ['tipo'=>'accion',   'texto'=>'Alerta meteorologica o inicio de evento visible',               'responsable'=>'Vigilancia'],
                ['tipo'=>'accion',   'texto'=>'Informar a residentes por intercomunicador o alarma',           'responsable'=>'Vigilancia'],
                ['tipo'=>'accion',   'texto'=>'Resguardarse en zonas internas, lejos de ventanales',           'responsable'=>'Todos los ocupantes'],
                ['tipo'=>'accion',   'texto'=>'NO evacuar durante el evento, permanecer en interiores',       'responsable'=>'Todos los ocupantes'],
                ['tipo'=>'decision', 'texto'=>'Cayeron arboles, estructuras o hay heridos?',                    'salida_si'=>'Llamar Bomberos 119 y Defensa Civil 144'],
                ['tipo'=>'accion',   'texto'=>'Al cesar el evento, inspeccion visual de danos y cubiertas',    'responsable'=>'Brigada'],
                ['tipo'=>'accion',   'texto'=>'Retirar escombros y asegurar elementos sueltos',                'responsable'=>'Brigada'],
                ['tipo'=>'fin',      'texto'=>'Reporte al administrador, valoracion de danos y aseguradora',   'responsable'=>'Administrador'],
            ],
        ],
        'objetivo'    => 'Definir las acciones a seguir ante eventos meteorologicos extremos como vendavales, granizadas, tormentas electricas o lluvias torrenciales, con el fin de proteger la integridad de personas, bienes, fachadas, cubiertas y zonas comunes del conjunto residencial. La brigada de la copropiedad (ver Nota Aclaratoria al inicio de los Procedimientos Operativos Normalizados) ejecuta las acciones iniciales de resguardo.',
        'alcance'     => 'Aplica a todas las personas presentes en el conjunto durante un evento meteorologico extremo, asi como al personal de vigilancia, brigada y administracion responsables de la respuesta inicial y resguardo de los ocupantes.',
        'definiciones' => [
            'Vendaval' => 'Viento muy fuerte con velocidades superiores a 60 km/h capaz de derribar arboles, antenas y elementos no asegurados.',
            'Granizada' => 'Precipitacion de agua en estado solido que puede causar danos en cubiertas, vehiculos y vegetacion.',
            'Tormenta electrica' => 'Fenomeno atmosferico con descargas electricas (rayos) acompanado generalmente de lluvia y viento.',
            'Resguardo' => 'Accion de proteger a las personas en zonas internas del edificio, alejadas de ventanas, ventanales, fachadas y elementos colgantes.',
            'SPDA' => 'Sistema de Proteccion contra Descargas Atmosfericas, conjunto de pararrayos, bajantes y sistema de tierra conforme a NTC 4552.',
        ],
        'responsables' => [
            'internos' => [
                'Personal de vigilancia en porteria',
                'Brigada de la copropiedad',
                'Administrador del conjunto',
            ],
            'contratistas_externos' => [],
            'organismos_socorro' => [
                'Bomberos — linea 119 (caida de arboles, estructuras colapsadas)',
                'Defensa Civil — linea 144',
            ],
        ],
        'procedimiento' => [
            '1. Al detectar cambio brusco de las condiciones del tiempo, el personal de vigilancia comunica por intercomunicador a los residentes la recomendacion de resguardo al interior del edificio y de retirar elementos sueltos de balcones.',
            '2. La brigada cierra ventanas, puertas y compuertas en areas comunes, asegura toldos, sombrillas, materas, banderas y mobiliario exterior que pueda ser arrastrado por el viento.',
            '3. Se desenergizan equipos sensibles (ascensores, antenas, equipos de gimnasio) cuando la tormenta electrica sea severa, para prevenir danos por sobretension.',
            '4. Los residentes deben permanecer al interior de sus apartamentos, en zonas internas alejadas de ventanas, ventanales, espejos, balcones y elementos metalicos. No utilizar duchas ni grifos durante tormenta electrica intensa.',
            '5. Ningun ocupante debe permanecer en piscinas, zonas verdes, canchas, parques infantiles, terrazas NI en parqueaderos durante el evento. Bajo NINGUNA circunstancia se debe evacuar hacia parqueaderos o zonas abiertas durante el vendaval, pues alli estan expuestos a caida de arboles, postes, vidrios y objetos voladores. El resguardo debe hacerse SIEMPRE en zonas internas del edificio.',
            '6. El personal de vigilancia inspecciona visualmente bajantes, sumideros, antenas, fachadas y cubiertas durante y despues del evento, reportando cualquier desprendimiento o acumulacion de agua o granizo.',
            '7. Si caen arboles, postes o ramas que obstruyan vias o bloqueen accesos, se senalizan y se solicita apoyo de Bomberos (119) o de la empresa contratada para mantenimiento de zonas verdes.',
            '8. Si una persona resulta lesionada por impacto de elementos voladores o granizo, se atiende con el botiquin y se solicita ambulancia al 123 cuando sea necesario.',
            '9. Una vez cesado el evento, la brigada realiza inspeccion detallada de cubiertas, tanques elevados, tejados, ventanales y antenas en busca de danos estructurales o filtraciones.',
            '10. La administracion documenta el evento, los danos materiales y las acciones tomadas, informa a la aseguradora y solicita los mantenimientos correctivos requeridos.',
        ],
        'medidas_preventivas' => [
            'Realizar mantenimiento semestral a cubiertas, canales, bajantes y antenas de television.',
            'Verificar anualmente el sistema SPDA conforme a NTC 4552.',
            'Podar arboles en riesgo de caer sobre vias internas, vehiculos o edificaciones.',
            'Asegurar materas, mobiliario exterior y elementos colgantes en balcones de unidades privadas.',
            'Monitoreo previo del tiempo: consultar como fuentes oficiales de informacion meteorologica al IDEAM y a la UNGRD, que emiten alertas tempranas.',
        ],
        'recomendaciones' => [
            'No refugiarse bajo arboles, postes o estructuras metalicas durante tormentas electricas.',
            'Desconectar electrodomesticos sensibles cuando se anuncie tormenta electrica.',
            'No utilizar telefonos fijos durante tormenta electrica intensa.',
            'Atender unicamente la informacion oficial del IDEAM y la UNGRD, evitando rumores.',
        ],
    ],

    // ============================================================
    'pon_06_falla_estructural' => [
        'codigo'      => '06',
        'titulo'      => 'Falla estructural / colapso',
        'amenaza_ref' => 'falla_estructural',
        'flowchart' => [
            'pasos' => [
                ['tipo'=>'accion',   'texto'=>'Detectar senales: grietas mayores a 3mm, ruidos, inclinaciones','responsable'=>'Primer respondiente'],
                ['tipo'=>'accion',   'texto'=>'Avisar a porteria y evacuar el area afectada de inmediato',    'responsable'=>'Vigilancia'],
                ['tipo'=>'accion',   'texto'=>'Acordonar el area y restringir acceso a personas',             'responsable'=>'Brigada'],
                ['tipo'=>'accion',   'texto'=>'Llamar Bomberos 119 y Defensa Civil 144',                       'responsable'=>'Vigilancia'],
                ['tipo'=>'decision', 'texto'=>'Hay personas atrapadas?',                                        'salida_si'=>'NO intentar rescate propio, esperar organismos oficiales'],
                ['tipo'=>'accion',   'texto'=>'Consejo de Administracion convoca ingeniero estructural externo','responsable'=>'Administrador'],
                ['tipo'=>'accion',   'texto'=>'Notificar autoridad municipal de gestion del riesgo',           'responsable'=>'Administrador'],
                ['tipo'=>'fin',      'texto'=>'No reocupar area hasta concepto tecnico por escrito',            'responsable'=>'Consejo de Administracion'],
            ],
        ],
        'objetivo'    => 'Establecer las acciones inmediatas ante la deteccion de signos de falla estructural (fisuras nuevas mayores a 3 mm, desplomes, ruidos estructurales, pisos inclinados, caida de elementos) o ante un colapso parcial o total, garantizando la evacuacion oportuna, la activacion de organismos de socorro oficiales y la suspension del uso de la estructura comprometida. La brigada (ver Nota Aclaratoria al inicio de los Procedimientos Operativos Normalizados) apoya la evacuacion pero no ejecuta rescate tecnico.',
        'alcance'     => 'Aplica a todas las edificaciones del conjunto residencial (casas, bloque unico, varias torres, parqueaderos, zonas sociales, muros de contencion) y a todas las personas que las ocupen al momento de detectarse la falla.',
        'definiciones' => [
            'Falla estructural' => 'Comportamiento inadecuado o perdida de capacidad de soporte de un elemento estructural (columna, viga, losa, muro de carga) que compromete la estabilidad de la edificacion.',
            'Fisura estructural' => 'Grieta que atraviesa elementos estructurales con espesor mayor a 1 mm, orientacion diagonal o que progresa con el tiempo. Una fisura NUEVA mayor a 3 mm es criterio directo de evacuacion inmediata.',
            'Colapso' => 'Caida total o parcial de elementos estructurales o de la edificacion completa.',
            'Evaluacion externa' => 'Inspeccion realizada por un ingeniero civil o estructural externo contratado por el Consejo de Administracion posteriormente al evento, NO presente en el conjunto en el momento del incidente.',
            'Apuntalamiento' => 'Intervencion de emergencia para sostener elementos estructurales comprometidos mediante puntales metalicos o de madera.',
        ],
        'responsables' => [
            'internos' => [
                'Personal de vigilancia en porteria (alerta inicial)',
                'Brigada de la copropiedad (apoyo a la evacuacion)',
                'Administrador del conjunto y Consejo de Administracion (decision de evacuacion total y contratacion tecnica externa)',
            ],
            'contratistas_externos' => [
                'Ingeniero civil o estructural externo contratado por el Consejo de Administracion (respuesta diferida, no inmediata, para diagnostico y dictamen)',
                'Aseguradora de la copropiedad',
            ],
            'organismos_socorro' => [
                'Bomberos — linea 119',
                'Defensa Civil — linea 144',
                'Autoridad municipal de gestion del riesgo: IDIGER (Bogota) / Oficina de Gestion del Riesgo de Soacha (Soacha) / equivalente municipal segun jurisdiccion del cliente',
            ],
        ],
        'procedimiento' => [
            '1. Quien detecte signos de falla estructural (fisura nueva mayor a 3 mm, sonido de craqueo, desplome de elemento, asentamiento visible, piso inclinado) debe avisar de inmediato a la porteria con ubicacion exacta y descripcion del hallazgo.',
            '2. El vigilante reporta al Administrador y a los brigadistas disponibles, quienes acuden al sitio para verificacion preliminar visual y delimitacion del area afectada.',
            '3. La decision de evacuacion inmediata la toma el Administrador con base en los criterios visibles (fisuras nuevas mayores a 3 mm, desplomes, ruidos estructurales, pisos inclinados). En ausencia del Administrador, esta decision la toma el vigilante en turno, sin esperar respuesta del ingeniero estructural externo, que NO esta en el conjunto en el momento del evento.',
            '4. Si se confirma riesgo, se activa la alarma general y se ordena evacuacion inmediata del area afectada por las rutas senalizadas, NUNCA por ascensores.',
            '5. La brigada y la vigilancia conducen a los ocupantes al punto de encuentro externo, priorizando personas con movilidad reducida. Se realiza censo en el punto de encuentro.',
            '6. Se acordona y senaliza el area de riesgo con cinta de peligro, prohibiendo el acceso de personas y vehiculos hasta evaluacion tecnica.',
            '7. El Administrador llama de inmediato a Bomberos (119), Defensa Civil (144) y a la autoridad municipal de gestion del riesgo (IDIGER en Bogota, Oficina de Gestion del Riesgo en Soacha, o su equivalente) para apoyo en evaluacion y eventual rescate.',
            '8. Si hay personas atrapadas, la brigada de la copropiedad NO intenta rescate en estructuras comprometidas. Se mantiene contacto verbal con las victimas y se espera la llegada del grupo oficial de busqueda y rescate.',
            '9. Posteriormente al evento, el Consejo de Administracion contacta al ingeniero civil o estructural externo para evaluacion tecnica, levantamiento de planos de danos y emision de dictamen sobre habitabilidad. La respuesta del ingeniero es diferida, no inmediata.',
            '10. Mientras se emite el dictamen, se prohibe terminantemente el reingreso de los ocupantes a la edificacion comprometida, incluso para retirar pertenencias.',
            '11. Una vez emitido el dictamen, se ejecutan las acciones recomendadas: apuntalamiento, demolicion controlada, refuerzo estructural o desocupacion permanente segun corresponda. La administracion convoca asamblea extraordinaria para informar a los copropietarios y definir el plan de intervencion conforme a NSR-10 y a la normativa local.',
        ],
        'medidas_preventivas' => [
            'Realizar inspeccion visual estructural anual por personal idoneo externo.',
            'Evaluar tecnicamente cualquier modificacion en muros, columnas o losas en unidades privadas, exigiendo licencia de construccion.',
            'Verificar cumplimiento de NSR-10 en todas las intervenciones.',
            'Llevar registro fotografico y de mediciones de fisuras significativas.',
            'Realizar estudio de vulnerabilidad sismica si la edificacion es anterior a 1998.',
        ],
        'recomendaciones' => [
            'No realizar perforaciones, demoliciones o ampliaciones en muros sin concepto estructural.',
            'Reportar de inmediato cualquier fisura nueva, ruido extrano o asentamiento.',
            'No sobrecargar losas con tanques, jardineras, equipos o mobiliario pesado.',
            'Mantener actualizados los planos estructurales y arquitectonicos en la oficina de administracion.',
        ],
    ],

    // ============================================================
    'pon_07_ascensor' => [
        'codigo'      => '07',
        'titulo'      => 'Persona(s) atrapada(s) en ascensor',
        'amenaza_ref' => null,
        'flowchart' => [
            'pasos' => [
                ['tipo'=>'accion',   'texto'=>'Activar Codigo 7 al recibir aviso de personas atrapadas',     'responsable'=>'Vigilancia'],
                ['tipo'=>'accion',   'texto'=>'Mantener contacto verbal o por intercomunicador con ocupantes','responsable'=>'Vigilancia'],
                ['tipo'=>'accion',   'texto'=>'Llamar a empresa mantenedora del ascensor',                    'responsable'=>'Administrador'],
                ['tipo'=>'accion',   'texto'=>'Indicar a ocupantes mantener la calma, NO forzar las puertas','responsable'=>'Vigilancia'],
                ['tipo'=>'decision', 'texto'=>'Empresa mantenedora llega en 20 minutos?',                      'salida_si'=>'Rescate tecnico por mantenedora, cierre del evento'],
                ['tipo'=>'accion',   'texto'=>'Llamar a Bomberos 119 para rescate de emergencia',             'responsable'=>'Vigilancia'],
                ['tipo'=>'accion',   'texto'=>'Asistir a Bomberos en el acceso y ubicacion exacta',           'responsable'=>'Vigilancia'],
                ['tipo'=>'fin',      'texto'=>'Verificar estado fisico de ocupantes, inhabilitar ascensor',   'responsable'=>'Administrador'],
            ],
        ],
        'objetivo'    => 'Establecer el procedimiento seguro y estandarizado para la atencion de emergencias por fallas de ascensor con personas atrapadas, asegurando la proteccion de la vida, la salud y la integridad de los ocupantes, asi como la coordinacion con la empresa mantenedora del equipo como responsable tecnico del rescate conforme al contrato de mantenimiento.',
        'alcance'     => 'Aplica para todo el personal de vigilancia, administracion, brigada de la copropiedad (ver Nota Aclaratoria al inicio de los Procedimientos Operativos Normalizados), personal de mantenimiento, residentes, propietarios y visitantes que participen o se vean involucrados en una emergencia de atrapamiento en ascensor dentro del conjunto residencial.',
        'definiciones' => [
            'Falla de ascensor' => 'Cese repentino o irregular del funcionamiento del ascensor por razones mecanicas, electricas o electronicas.',
            'Rescate tecnico' => 'Intervencion de personal calificado de la empresa mantenedora o de bomberos para liberar de forma segura a las personas atrapadas.',
            'Llave de rescate' => 'Llave especial que permite la apertura manual de las puertas del ascensor por personal autorizado.',
            'Codigo 7' => 'Clave interna de comunicacion para alertar de manera discreta sobre falla de ascensor con personas en su interior.',
            'Empresa mantenedora' => 'Compania certificada responsable del mantenimiento preventivo y correctivo del equipo, conforme a NTC 5926.',
        ],
        'responsables' => [
            'internos' => [
                'Personal de vigilancia y porteria',
                'Administrador del conjunto',
            ],
            'contratistas_externos' => [
                'Empresa mantenedora del ascensor (responsable tecnico del rescate conforme al contrato de mantenimiento)',
            ],
            'organismos_socorro' => [
                'Bomberos — linea 119 (cuando la empresa mantenedora no pueda llegar en 20 minutos o cuando exista riesgo vital)',
            ],
        ],
        'procedimiento' => [
            '1. Ante el aviso de personas atrapadas, el personal que reciba la alerta anuncia por el canal de comunicacion interna: Codigo 7 activo en el piso y lugar donde se presento la novedad, y registra hora exacta en la minuta.',
            '2. El vigilante mantiene contacto verbal permanente con los ocupantes desde el exterior o por el intercomunicador del ascensor, indicando que el rescate esta en proceso y solicitando que mantengan la calma.',
            '3. Se realiza evaluacion inicial: cuantas personas estan atrapadas, presencia de menores, gestantes, adultos mayores o personas con discapacidad, y existencia de riesgo inminente (humo, agua, lesiones, falta de aire).',
            '4. Se desenergiza el ascensor desde el tablero principal, unicamente si se tiene claridad sobre el procedimiento, para evitar movimientos involuntarios durante el rescate.',
            '5. Se notifica de inmediato a la empresa mantenedora del ascensor, cuyo numero debe estar visible en porteria, indicando ubicacion del equipo, numero de personas y condiciones observadas.',
            '6. Si el tiempo de respuesta de la empresa mantenedora supera los 20 minutos o si existe riesgo vital, se activa la linea 119 (Bomberos) para apoyo en rescate.',
            '7. Bajo NINGUNA circunstancia el personal de vigilancia debe intentar abrir las puertas del ascensor por sus propios medios, ni utilizar llaves o herramientas no certificadas.',
            '8. El rescate se ejecuta unicamente por personal autorizado de la empresa mantenedora o bomberos, verificando alineacion del ascensor con el piso antes de abrir puertas.',
            '9. Una vez liberadas las personas, se verifica su condicion fisica y emocional. Se ofrecen primeros auxilios y, si es necesario, se llama al 123 para traslado a centro asistencial.',
            '10. El ascensor queda fuera de servicio y senalizado hasta que la empresa mantenedora emita certificacion escrita de funcionamiento seguro.',
            '11. El Administrador levanta informe del evento (hora, tiempo de respuesta, personal interviniente, causa probable, medidas correctivas) y lo archiva en la bitacora de mantenimiento del equipo.',
        ],
        'medidas_preventivas' => [
            'Mantener vigente el contrato de mantenimiento preventivo y correctivo conforme a NTC 5926.',
            'Verificar mensualmente el funcionamiento del intercomunicador del ascensor.',
            'Senalizar visiblemente el numero de la empresa mantenedora en porteria y dentro del ascensor.',
            'Capacitar al personal de vigilancia y brigada sobre este PON minimo una vez al ano.',
            'Llevar bitacora de todas las intervenciones tecnicas del equipo.',
        ],
        'recomendaciones' => [
            'No permitir que personal no capacitado intente rescates por sus propios medios.',
            'Verificar periodicamente la operatividad del sistema de iluminacion de emergencia del ascensor.',
            'Incluir simulacro de Codigo 7 en el programa anual de simulacros.',
            'Garantizar que las puertas de emergencia y vias de mantenimiento esten libres de obstrucciones.',
        ],
    ],

    // ============================================================
    'pon_08_fuga_gas' => [
        'codigo'      => '08',
        'titulo'      => 'Fuga de gas / explosion',
        'amenaza_ref' => 'explosiones',
        'flowchart' => [
            'pasos' => [
                ['tipo'=>'accion',   'texto'=>'Detectar olor a gas, siseo o alerta de residente',             'responsable'=>'Primer respondiente'],
                ['tipo'=>'accion',   'texto'=>'PROHIBIDO usar intercomunicador, timbres, celulares o luces',  'responsable'=>'Todos los presentes'],
                ['tipo'=>'accion',   'texto'=>'Abrir ventanas para ventilar de inmediato',                    'responsable'=>'Residentes'],
                ['tipo'=>'accion',   'texto'=>'Cerrar valvula principal de gas si es accesible y seguro',     'responsable'=>'Vigilancia'],
                ['tipo'=>'decision', 'texto'=>'Concentracion alta o explosion inminente?',                     'salida_si'=>'Evacuar toda la torre inmediatamente, linea 119'],
                ['tipo'=>'accion',   'texto'=>'Llamar empresa distribuidora de gas (linea de emergencia)',    'responsable'=>'Administrador'],
                ['tipo'=>'accion',   'texto'=>'Acordonar area, esperar tecnicos autorizados',                 'responsable'=>'Brigada'],
                ['tipo'=>'fin',      'texto'=>'Certificacion tecnica de la empresa de gas antes de reingreso','responsable'=>'Administrador'],
            ],
        ],
        'objetivo'    => 'Establecer las acciones inmediatas ante la deteccion de fuga de gas natural domiciliario, gas propano u olor sospechoso en areas comunes o privadas, asi como ante una explosion derivada, con el fin de prevenir incendio, lesiones y danos estructurales, y coordinar con la empresa distribuidora de gas y con Bomberos. La brigada (ver Nota Aclaratoria al inicio de los Procedimientos Operativos Normalizados) y el personal de vigilancia ejecutan las acciones iniciales.',
        'alcance'     => 'Aplica a todas las areas del conjunto residencial donde exista red de suministro de gas (cocinas, calentadores, areas BBQ, cuartos de gas) y a todo el personal y residentes que detecten o se vean afectados por una fuga o explosion.',
        'definiciones' => [
            'Gas natural' => 'Combustible gaseoso compuesto principalmente por metano, distribuido por red domiciliaria conforme a Reglamento Tecnico (RETIG).',
            'GLP' => 'Gas Licuado de Petroleo, mezcla de propano y butano almacenado en cilindros o tanques estacionarios.',
            'LIE' => 'Limite Inferior de Explosividad, concentracion minima de gas en el aire por debajo de la cual no hay riesgo de ignicion.',
            'Odorante' => 'Sustancia anadida al gas (mercaptano) para que sea perceptible por el olfato ante una fuga.',
            'Valvula de corte' => 'Dispositivo que permite suspender el flujo de gas hacia una unidad o hacia todo el conjunto.',
        ],
        'responsables' => [
            'internos' => [
                'Personal de vigilancia en porteria',
                'Brigada de la copropiedad',
                'Administrador del conjunto',
            ],
            'contratistas_externos' => [
                'Proveedor de vigilancia como equipo de apoyo adicional',
                'Empresa distribuidora de gas (linea comercial de emergencia — Vanti en Bogota y Soacha u operador local correspondiente)',
            ],
            'organismos_socorro' => [
                'Bomberos — linea 119',
            ],
        ],
        'procedimiento' => [
            '1. Quien detecte olor a gas debe avisar de inmediato a la porteria sin operar interruptores electricos, telefonos celulares, timbres ni cualquier elemento que pueda generar chispa, indicando ubicacion exacta.',
            '2. El vigilante registra el evento y comunica al Administrador. NO se debe activar la alarma sonora general si esta es electrica, ni utilizar intercomunicador en el area afectada.',
            '3. La brigada y el personal de vigilancia acuden al sitio sin encender luces ni equipos electricos. Se procede a abrir puertas y ventanas para permitir ventilacion natural cruzada del area.',
            '4. Se localiza la valvula de corte de gas (de la unidad afectada o del cuarto de gas comun) y se cierra manualmente. Si no es accesible o segura, NO se intenta forzar.',
            '5. Se evacua de inmediato a las personas presentes en el area afectada y en areas adyacentes, conduciendolas al punto de encuentro externo por las rutas alejadas del foco de fuga; esta labor la ejecutan la brigada y el personal de vigilancia.',
            '6. El Administrador o el vigilante en turno llama a la linea de emergencia de la empresa distribuidora de gas (Vanti u operador local correspondiente) y a Bomberos (119), indicando direccion exacta, ubicacion de la fuga y personas afectadas.',
            '7. Se acordona el area en un radio minimo de 30 metros, prohibiendo el ingreso de vehiculos, peatones, fumadores y cualquier fuente de ignicion hasta la llegada de los organismos.',
            '8. Si se produce explosion, se activa simultaneamente el PON 01 (Incendio) y PON 06 (Falla estructural) segun corresponda, priorizando el rescate de victimas por parte de los organismos oficiales y la solicitud urgente de Bomberos.',
            '9. Las personas con sintomas de intoxicacion (mareo, nausea, dificultad respiratoria) son atendidas con primeros auxilios al aire libre y trasladadas a centro asistencial llamando al 123.',
            '10. Bomberos y la empresa distribuidora certifican que el area esta segura antes de permitir el reingreso de los ocupantes y el restablecimiento del servicio.',
            '11. La administracion documenta el evento, exige informe tecnico de causa raiz a la empresa distribuidora y aplica las acciones correctivas (revision de red, cambio de empaques, recertificacion de instalacion).',
        ],
        'medidas_preventivas' => [
            'Realizar revision tecnica certificada de la red interna de gas conforme al Reglamento Tecnico de Instalaciones de Gas (RETIG).',
            'Mantener libre y ventilado el cuarto de gas comun, sin almacenamiento de combustibles ni materiales.',
            'Verificar mensualmente que no existan obstrucciones en rejillas de ventilacion de cocinas y banos.',
            'Capacitar a residentes y brigada sobre la deteccion y respuesta ante fuga de gas.',
            'Prohibir conexiones artesanales y manguera de tipo no autorizado en estufas y calentadores.',
        ],
        'recomendaciones' => [
            'No encender luces, fosforos, encendedores ni celulares ante olor a gas.',
            'No operar interruptores electricos en el area afectada.',
            'Conocer la ubicacion de la valvula de corte de gas de cada unidad privada.',
            'Ventilar siempre las cocinas durante el uso de la estufa o calentador.',
        ],
    ],

    // ============================================================
    'pon_09_atentado' => [
        'codigo'      => '09',
        'titulo'      => 'Amenaza terrorista / paquete sospechoso',
        'amenaza_ref' => 'atentados',
        'flowchart' => [
            'pasos' => [
                ['tipo'=>'accion',   'texto'=>'Detectar amenaza, llamada extorsiva o paquete sospechoso',    'responsable'=>'Primer respondiente'],
                ['tipo'=>'accion',   'texto'=>'NO tocar, mover ni fotografiar el objeto sospechoso',         'responsable'=>'Todos los presentes'],
                ['tipo'=>'accion',   'texto'=>'Vigilancia llama a Policia Nacional linea 123',               'responsable'=>'Vigilancia'],
                ['tipo'=>'accion',   'texto'=>'Acordonar perimetro minimo de 100 metros',                    'responsable'=>'Vigilancia'],
                ['tipo'=>'decision', 'texto'=>'Policia confirma amenaza real?',                                'salida_si'=>'Grupo Antiexplosivos activado via linea 123'],
                ['tipo'=>'accion',   'texto'=>'Evacuar zona comprometida alejandose de fachadas y ventanas', 'responsable'=>'Brigada evacuacion'],
                ['tipo'=>'accion',   'texto'=>'PROHIBIDO usar radios o celulares cerca del objeto',           'responsable'=>'Todos los presentes'],
                ['tipo'=>'fin',      'texto'=>'Reingreso solo con autorizacion oficial de Policia',           'responsable'=>'Administrador'],
            ],
        ],
        'objetivo'    => 'Establecer el protocolo de actuacion ante una amenaza de atentado, llamada extorsiva, hallazgo de paquete sospechoso o vehiculo abandonado dentro o en cercanias del conjunto residencial, garantizando la proteccion de la vida mediante evacuacion oportuna y la activacion inmediata de la Policia Nacional por parte del personal de vigilancia en porteria. El Grupo Antiexplosivos NO se convoca directamente sino a traves de la Policia Nacional.',
        'alcance'     => 'Aplica al personal de vigilancia, administracion, residentes y visitantes que reciban una amenaza, identifiquen un objeto, paquete o vehiculo sospechoso en areas comunes (porteria, parqueaderos, escaleras, pasillos, zonas verdes) o cercanias inmediatas del conjunto.',
        'definiciones' => [
            'Amenaza terrorista' => 'Comunicacion verbal, escrita o digital que advierte sobre la posible ejecucion de un atentado contra personas, bienes o instalaciones.',
            'Paquete sospechoso' => 'Objeto cuyo origen, contenido o ubicacion despierta dudas razonables (sin destinatario claro, con cables visibles, peso inusual, olor extrano, abandonado).',
            'Vehiculo sospechoso' => 'Automotor abandonado, sin identificacion, estacionado en lugar inusual o con caracteristicas que generan alerta.',
            'Perimetro de seguridad' => 'Distancia minima de evacuacion alrededor del objeto sospechoso, recomendada minimo 100 metros.',
            'Grupo Antiexplosivos' => 'Grupo especializado de la Policia Nacional que atiende eventos relacionados con explosivos. Se activa UNICAMENTE a traves de la linea 123 de la Policia Nacional, no mediante contacto directo.',
        ],
        'responsables' => [
            'internos' => [
                'Proveedor de vigilancia (interno de primera linea de respuesta)',
                'Administrador del conjunto (notificacion)',
            ],
            'contratistas_externos' => [],
            'organismos_socorro' => [
                'Policia Nacional — linea 123 (incluye activacion del Grupo Antiexplosivos cuando aplique)',
            ],
        ],
        'procedimiento' => [
            '1. Si la amenaza llega por llamada telefonica, el receptor debe mantener la calma, NO colgar, prolongar la conversacion, registrar todo lo posible: voz, acento, ruidos de fondo, hora, contenido textual y exigencias del comunicante.',
            '2. Inmediatamente despues de la llamada, el receptor comunica al personal de vigilancia en porteria, quien establece el perimetro inicial y ejecuta la llamada a la linea 123 de la Policia Nacional. El Grupo Antiexplosivos sera activado por la Policia a traves de esta llamada, no se contacta directamente.',
            '3. Si se trata de paquete o vehiculo sospechoso, NINGUNA persona debe tocarlo, moverlo, abrirlo, fotografiarlo de cerca ni utilizar radios, celulares o cualquier dispositivo electronico cerca de el (riesgo de detonacion remota).',
            '4. El personal de vigilancia acordona y senaliza un perimetro minimo de 100 metros alrededor del objeto, prohibiendo el ingreso de personas y vehiculos.',
            '5. Se activa la evacuacion ordenada de las areas comprometidas hacia un punto de encuentro alterno, alejado del objeto sospechoso. La evacuacion se realiza de manera silenciosa y controlada bajo la direccion de la vigilancia y los residentes disponibles.',
            '6. La evacuacion debe alejar a las personas no solo del objeto sino tambien de ventanas y fachadas que puedan resultar afectadas por onda explosiva o esquirlas.',
            '7. Se prohibe el uso de radios, celulares y equipos de comunicacion inalambrica en el area perimetral hasta que el Grupo Antiexplosivos lo autorice.',
            '8. El personal de vigilancia facilita el ingreso de los organismos especializados, suministrando planos, llaves y descripcion del hallazgo.',
            '9. Mientras se realiza la inspeccion, se mantiene a los residentes en el punto de encuentro alterno, se realiza censo y se atiende cualquier crisis emocional o lesion.',
            '10. El reingreso al conjunto solo se autoriza una vez la Policia Nacional y el Grupo Antiexplosivos certifiquen que el area esta segura.',
            '11. La administracion documenta el evento, conserva la grabacion del CCTV, presenta denuncia formal ante Fiscalia y refuerza las medidas de seguridad recomendadas por la autoridad.',
        ],
        'medidas_preventivas' => [
            'Verificar identidad y motivo de visita de toda persona que ingrese al conjunto.',
            'Inspeccionar visualmente paquetes, encomiendas y mercancias antes de su ingreso a porteria.',
            'Mantener actualizado el directorio del cuadrante de Policia.',
            'Capacitar al personal de vigilancia en deteccion de comportamientos y objetos sospechosos.',
            'Operar el sistema de CCTV con grabacion permanente en accesos y zonas comunes.',
        ],
        'recomendaciones' => [
            'No tocar, mover ni abrir paquetes sospechosos bajo ninguna circunstancia.',
            'No difundir la informacion de la amenaza por redes sociales o canales no oficiales.',
            'Atender unicamente las instrucciones de la Policia Nacional y autoridades competentes.',
            'Reportar a la administracion cualquier hallazgo o conducta inusual.',
        ],
    ],

    // ============================================================
    'pon_10_emergencia_medica' => [
        'codigo'      => '10',
        'titulo'      => 'Emergencia medica en ocupantes del conjunto',
        'amenaza_ref' => null,
        'flowchart' => [
            'pasos' => [
                ['tipo'=>'accion',   'texto'=>'Identificar persona en emergencia y avisar a porteria',       'responsable'=>'Primer respondiente'],
                ['tipo'=>'accion',   'texto'=>'Vigilancia llama a linea 123 con ubicacion exacta',           'responsable'=>'Vigilancia'],
                ['tipo'=>'accion',   'texto'=>'Personal capacitado acude con botiquin y DEA si esta disponible','responsable'=>'Residente capacitado'],
                ['tipo'=>'accion',   'texto'=>'Evaluar consciencia, respiracion y pulso (10 segundos)',      'responsable'=>'Residente capacitado'],
                ['tipo'=>'decision', 'texto'=>'La persona respira?',                                          'salida_si'=>'Posicion lateral de seguridad. Esperar ambulancia.'],
                ['tipo'=>'accion',   'texto'=>'Iniciar RCP 100-120 compresiones por minuto',                 'responsable'=>'Residente capacitado'],
                ['tipo'=>'accion',   'texto'=>'Aplicar DEA siguiendo instrucciones de voz del equipo',       'responsable'=>'Residente capacitado'],
                ['tipo'=>'accion',   'texto'=>'Despejar acceso para la ambulancia y guiar personal medico',  'responsable'=>'Vigilancia'],
                ['tipo'=>'fin',      'texto'=>'Entregar paciente al personal medico, notificar a familia',    'responsable'=>'Administrador'],
            ],
        ],
        'objetivo'    => 'Definir las acciones de respuesta ante cualquier evento que comprometa la vida o la salud de un ocupante, residente, visitante, trabajador o contratista dentro del conjunto, sin importar su origen o naturaleza clinica. La respuesta inicial estara a cargo del personal de vigilancia en porteria y de residentes o vigilantes con capacitacion basica en primeros auxilios (ver Nota Aclaratoria al inicio de los Procedimientos Operativos Normalizados), quienes deberan garantizar la atencion prehospitalaria oportuna mientras se activa la linea 123 para el envio de ambulancia y atencion medica especializada.',
        'alcance'     => 'Aplica a todo el personal de vigilancia, residentes con capacitacion en primeros auxilios, administracion, visitantes, contratistas y trabajadores presentes en el conjunto al momento de presentarse una urgencia que comprometa la vida o integridad fisica de una o mas personas, cubriendo la totalidad de escenarios clinicos posibles: cardiovasculares, traumatologicos, respiratorios, neurologicos, metabolicos, intoxicaciones, urgencias obstetricas y crisis psiquiatricas agudas.',
        'definiciones' => [
            'Emergencia medica' => 'Cualquier evento subito que comprometa la vida, la funcion de un organo vital o la integridad fisica o mental de una persona, requiriendo atencion prehospitalaria inmediata. Incluye escenarios cardiovasculares (paro, infarto, ACV, crisis hipertensiva), traumatologicos (caidas, heridas, fracturas, quemaduras, electrocucion, ahogamiento), respiratorios (crisis asmatica, anafilaxia, broncoaspiracion), neurologicos (convulsiones, sincope, perdida de conciencia), metabolicos (hipo e hiperglucemia, shock), intoxicaciones (alimentaria, medicamentosa, monoxido de carbono, quimicos), obstetricos (parto imprevisto, complicaciones) y psiquiatricos agudos (intento de suicidio, crisis psicotica).',
            'Cadena de supervivencia' => 'Secuencia de acciones que aumentan la probabilidad de supervivencia en paro cardiorrespiratorio: reconocimiento temprano, activacion linea 123, RCCP de calidad, desfibrilacion temprana y atencion avanzada.',
            'RCCP' => 'Reanimacion cerebro cardio pulmonar, tecnica de compresiones toracicas y ventilaciones que se aplica cuando una persona no respira y no tiene pulso.',
            'DEA' => 'Desfibrilador Externo Automatico, equipo portatil que analiza el ritmo cardiaco y aplica descarga electrica cuando el paciente presenta un ritmo desfibrilable.',
            'Linea 123' => 'Linea unica nacional de emergencias operada por la Policia Nacional, que coordina el envio de ambulancia y atencion medica prehospitalaria. Es la puerta de entrada unica a emergencias, integrando servicio de Policia y servicios medicos prehospitalarios.',
            'Posicion lateral de seguridad' => 'Posicion en la que se coloca a una persona inconsciente que respira para mantener la via aerea permeable y prevenir broncoaspiracion.',
            'Triage' => 'Clasificacion rapida del nivel de urgencia de uno o varios pacientes cuando concurren multiples afectados, para priorizar la atencion.',
        ],
        'responsables' => [
            'internos' => [
                'Personal de vigilancia en porteria (alerta, ubicacion y apertura de acceso)',
                'Residente o vigilante con capacitacion basica en primeros auxilios',
                'Administrador del conjunto (notificacion)',
            ],
            'contratistas_externos' => [],
            'organismos_socorro' => [
                'Linea 123 (Policia y emergencias unificadas — coordina envio de ambulancia)',
                'Cruz Roja — linea 132 (cuando aplique)',
            ],
        ],
        'procedimiento' => [
            '1. Quien identifique a una persona en emergencia medica debe avisar inmediatamente a la porteria suministrando ubicacion exacta, condicion observada (consciente/inconsciente, respira/no respira, sangrado, lesion visible, tipo de emergencia sospechada) y numero de afectados.',
            '2. El vigilante registra la hora exacta y activa de inmediato la linea 123 informando direccion completa del conjunto, torre o bloque, piso, punto de referencia y estado del paciente. La linea 123 coordinara el envio de ambulancia y, si es necesario, brindara instrucciones telefonicas de primera respuesta al personal en sitio.',
            '3. El residente o vigilante con capacitacion basica en primeros auxilios acude al sitio con el botiquin y, si esta disponible, con el DEA. NO se debe mover a la victima salvo que exista riesgo inminente para su vida (incendio, derrumbe, electrocucion, ahogamiento, via con trafico).',
            '4. Se evalua primero el estado de conciencia (sacudir suavemente y preguntar en voz alta) y la respiracion (mirar, escuchar, sentir durante 10 segundos). En funcion del hallazgo se inicia el protocolo correspondiente descrito en los pasos siguientes. NO todas las emergencias requieren RCCP; cada cuadro clinico tiene su abordaje especifico.',
            '5. Emergencia cardiovascular o paro cardiorrespiratorio: si la persona esta inconsciente y no respira o solo jadea, se inicia RCCP de inmediato con compresiones toracicas continuas a 100 a 120 por minuto y profundidad de 5 a 6 cm en adultos, manteniendo el ciclo hasta llegada de personal medico o uso del DEA.',
            '6. Uso del DEA: encender el equipo, seguir las indicaciones de voz, aplicar los parches conforme al diagrama y administrar descarga unicamente cuando el equipo lo indique. Retomar RCCP inmediatamente despues de cada descarga.',
            '7. Emergencia traumatologica (caidas, heridas, fracturas, quemaduras): si hay sangrado abundante, aplicar presion directa con apositos limpios sin retirar objetos empalados. Si hay sospecha de fractura, inmovilizar el segmento afectado sin intentar reducirla. Si hay sospecha de trauma de columna cervical, NO mover a la victima salvo riesgo inminente.',
            '8. Emergencia respiratoria (crisis asmatica, anafilaxia, broncoaspiracion): asistir al afectado para que utilice su propio inhalador o autoinyector (si dispone), aflojar ropa ajustada, mantenerlo en posicion sentada inclinado hacia adelante y activar la linea 123 de inmediato. En atragantamiento consciente, aplicar maniobra de Heimlich.',
            '9. Emergencia neurologica (convulsion, sincope, perdida de conciencia): no sujetar al afectado ni introducir objetos en la boca; protegerlo de golpes, girarlo en posicion lateral de seguridad una vez cese la convulsion y registrar la duracion del episodio.',
            '10. Emergencia metabolica (crisis diabetica, shock): si el afectado esta consciente y se sospecha hipoglucemia, ofrecer una bebida azucarada o carbohidrato de absorcion rapida. NO administrar nada por boca si esta inconsciente.',
            '11. Intoxicacion (alimentaria, medicamentosa, monoxido de carbono, quimicos): identificar la sustancia cuando sea posible, retirar al afectado de la fuente de exposicion (ventilacion en caso de gases), conservar envases o etiquetas para entregar al personal medico. NO inducir vomito salvo indicacion expresa de la linea 123.',
            '12. Si la victima esta inconsciente pero respira normalmente y no hay sospecha de trauma cervical, se coloca en posicion lateral de seguridad mientras llega la ambulancia.',
            '13. Cuando haya varios afectados simultaneos, se realiza triage basico priorizando a los pacientes en estado critico (inconsciencia, paro, hemorragia masiva, dificultad respiratoria grave) y se informa a la linea 123 el numero total de afectados.',
            '14. El personal de vigilancia despeja el acceso vehicular y peatonal para facilitar la entrada de la ambulancia y guia al personal medico hasta el sitio del paciente.',
            '15. Una vez entregado el paciente al personal medico, el Administrador comunica a los familiares, registra el evento en bitacora con datos del paciente, hora, acciones realizadas, personal interviniente, tipo de emergencia y centro asistencial de traslado.',
        ],
        'medidas_preventivas' => [
            'Mantener un botiquin de primeros auxilios completo, vigente y senalizado en porteria conforme a Resolucion 0705 de 2007.',
            'Capacitar a residentes voluntarios y personal de vigilancia en primeros auxilios basicos, RCCP, uso de DEA, maniobra de Heimlich y reconocimiento de signos de ACV, infarto, crisis asmatica y shock, al menos una vez al ano.',
            'Disponer de un DEA accesible en el conjunto cuando el numero de unidades y la poblacion residente lo justifiquen.',
            'Mantener actualizado el censo confidencial de residentes con condiciones medicas especiales (cardiopatas, diabeticos, epilepticos, asmaticos, embarazadas, adultos mayores, personas con movilidad reducida) para facilitar la respuesta.',
            'Verificar que el numero 123, la direccion exacta del conjunto y el nombre del punto de referencia mas cercano esten visibles en porteria y zonas comunes.',
            'Mantener libre y senalizado el acceso vehicular principal para permitir la entrada rapida de ambulancias a cualquier hora.',
            'Cuando el conjunto cuente con piscina, gimnasio o zonas de riesgo especial (parqueadero subterraneo, cuartos tecnicos), disponer de senalizacion de emergencia y equipamiento especifico (flotadores, manta ignifuga, detector de monoxido segun aplique).',
        ],
        'recomendaciones' => [
            'No suministrar alimentos, bebidas ni medicamentos a personas inconscientes o con alteracion del estado de conciencia.',
            'No mover a victimas con sospecha de trauma de columna salvo riesgo inminente para la vida.',
            'No inducir vomito en casos de intoxicacion salvo indicacion expresa del operador de la linea 123.',
            'Atender unicamente las instrucciones del operador del 123 mientras llega la ambulancia; el operador puede guiar paso a paso la primera respuesta.',
            'Conservar la calma, proteger la intimidad del afectado y brindar apoyo emocional a la victima y sus familiares durante y despues del evento.',
            'No difundir informacion del paciente ni imagenes por redes sociales o grupos de chat del conjunto.',
        ],
    ],
];
