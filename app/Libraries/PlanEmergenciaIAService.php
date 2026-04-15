<?php

namespace App\Libraries;

/**
 * PlanEmergenciaIAService
 * ------------------------------------------------------------
 * Servicio de generacion de contenido personalizado por IA (Claude)
 * para el Plan de Emergencia.
 *
 * Patron: cURL directo a api.anthropic.com/v1/messages
 * (siguiendo el patron de SendGrid usado en ActaVisitaController).
 *
 * Variables de entorno requeridas en .env:
 *   ANTHROPIC_API_KEY  -- API key Claude
 *   ANTHROPIC_MODEL    -- modelo (default: claude-sonnet-4-6)
 *
 * Metodos publicos:
 *   - enriquecerPONs($contextoCliente, $ponesCanonicos)
 *   - generarDiagramaActuacion($contextoCliente)
 *   - generarMatrizResponsables($contextoCliente)
 *   - generarBrigadaPersonalizada($datosBrigada, $contextoCliente)
 *   - generarSimulacrosPersonalizado($datosSimulacros, $contextoCliente)
 *
 * Todos los metodos retornan array con [ok=>bool, data=>mixed, error=>string].
 *
 * Fase 2 - Plan de Emergencia
 */
class PlanEmergenciaIAService
{
    private string $apiKey;
    private string $model;
    private string $endpoint = 'https://api.anthropic.com/v1/messages';
    private int $timeout = 120;

    public function __construct()
    {
        $this->apiKey = getenv('ANTHROPIC_API_KEY') ?: '';
        $this->model  = getenv('ANTHROPIC_MODEL') ?: 'claude-sonnet-4-6';
    }

    /**
     * Test de conexion basico (smoke test).
     * Hace una llamada minima al API y retorna ok/error.
     */
    public function ping(): array
    {
        if (empty($this->apiKey)) {
            return ['ok' => false, 'error' => 'ANTHROPIC_API_KEY no configurada en .env'];
        }

        $payload = [
            'model'      => $this->model,
            'max_tokens' => 50,
            'messages'   => [
                ['role' => 'user', 'content' => 'Responde unicamente con la palabra: OK'],
            ],
        ];

        $resp = $this->request($payload);
        if (!$resp['ok']) {
            return $resp;
        }

        $texto = $resp['data']['content'][0]['text'] ?? '';
        return [
            'ok'    => true,
            'data'  => [
                'modelo'   => $this->model,
                'respuesta' => trim($texto),
                'tokens_in'  => $resp['data']['usage']['input_tokens'] ?? 0,
                'tokens_out' => $resp['data']['usage']['output_tokens'] ?? 0,
            ],
        ];
    }

    /**
     * Enriquece los 10 PONs canonicos con un adendo personalizado por cliente.
     *
     * @param array $contextoCliente Datos del cliente y sus inspecciones (Matriz Vuln, Prob Peligros, etc).
     * @param array $ponesCanonicos  Array de PONs base (de PonesCanonicos.php).
     * @return array Array asociativo: clave del PON => texto adendo personalizado.
     */
    public function enriquecerPONs(array $contextoCliente, array $ponesCanonicos, string $contextoAdicional = ''): array
    {
        $contextoTxt = $this->serializarContextoCliente($contextoCliente);
        $contextoExtraBlock = $this->bloqueContextoAdicional($contextoAdicional);
        $listaPons = [];
        foreach ($ponesCanonicos as $key => $pon) {
            $listaPons[] = sprintf(
                '- %s | codigo %s | titulo: %s | amenaza_ref: %s',
                $key,
                $pon['codigo'],
                $pon['titulo'],
                $pon['amenaza_ref'] ?? 'universal'
            );
        }

        $prompt = <<<PROMPT
Eres un experto en planes de emergencia para propiedad horizontal residencial en Colombia, con conocimiento profundo de Decreto 1072/2015 art. 2.2.4.6.25, Ley 1523/2012, Decreto 2157/2017, NTC 1700, NSR-10 y guias UNGRD.

Tu tarea: para cada uno de los siguientes Procedimientos Operativos Normalizados (PON) canonicos, generar un breve ADENDO PERSONALIZADO (60 a 120 palabras cada uno) que adapte el procedimiento a la realidad especifica del cliente segun los datos suministrados.

CONTEXTO DEL CLIENTE:
{$contextoTxt}
{$contextoExtraBlock}
LISTA DE PONS A PERSONALIZAR:
{$this->joinList($listaPons)}

REGLAS DE REDACCION DEL ADENDO:
1. Tono formal tecnico-legal, sin tildes (compatibilidad DOMPDF), sin emojis.
2. ESPANOL COLOMBIANO 100%: prohibido usar extranjerismos, anglicismos o siglas en ingles. Ejemplos de lo que NO se debe escribir: 'drop-cover-hold', 'lockdown', 'emergency kit', 'safe zone', 'panic button', 'standby', 'briefing', 'workflow'. Usar siempre su equivalente en espanol: 'agacharse, cubrirse y sujetarse', 'confinamiento', 'kit de emergencia', 'zona segura', 'boton de panico', 'en espera', 'reunion informativa', 'flujo de trabajo'.
3. NO repitas el procedimiento general - solo los aspectos especificos del cliente.
4. Menciona caracteristicas reales: numero de torres o bloques, casas vs apartamentos, parqueadero subterraneo si aplica, presencia de gabinetes hidraulicos, brigada conformada o no, etc.
5. Si la amenaza tiene probabilidad ALTA o MEDIA en este cliente, enfatizalo. Si es BAJA, mencionalo brevemente. Si NULL, omite mencion de probabilidad.
6. Refiere recursos especificos detectados en las inspecciones (cantidad de extintores, ubicacion del botiquin, sistema de alarma, etc) cuando aporte valor.
7. Maximo 120 palabras por adendo. Minimo 60.

FORMATO DE RESPUESTA:
Devuelve EXCLUSIVAMENTE un objeto JSON valido con esta estructura, sin texto adicional ni markdown:
{
  "pon_01_incendio": "texto del adendo personalizado...",
  "pon_02_sismo": "texto del adendo personalizado...",
  ...
  "pon_10_emergencia_medica": "texto del adendo personalizado..."
}
PROMPT;

        $payload = [
            'model'      => $this->model,
            'max_tokens' => 6000,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        $resp = $this->request($payload);
        if (!$resp['ok']) {
            return $resp;
        }

        $texto = $resp['data']['content'][0]['text'] ?? '';
        $json  = $this->extraerJSON($texto);
        if ($json === null) {
            return ['ok' => false, 'error' => 'Respuesta IA no es JSON valido', 'raw' => $texto];
        }

        return [
            'ok'   => true,
            'data' => $json,
            'tokens' => [
                'in'  => $resp['data']['usage']['input_tokens'] ?? 0,
                'out' => $resp['data']['usage']['output_tokens'] ?? 0,
            ],
        ];
    }

    /**
     * Genera el arbol de decision del Diagrama de Actuacion como JSON estructurado.
     *
     * @return array ['ok'=>bool, 'data'=>['inicio'=>..., 'nodos'=>[...]], 'error'=>...]
     */
    public function generarDiagramaActuacion(array $contextoCliente, string $contextoAdicional = ''): array
    {
        $contextoTxt = $this->serializarContextoCliente($contextoCliente);
        $contextoExtraBlock = $this->bloqueContextoAdicional($contextoAdicional);

        $prompt = <<<PROMPT
Eres un experto en planes de emergencia para propiedad horizontal en Colombia. Tu tarea: generar un arbol de decision del DIAGRAMA DE ACTUACION en caso de emergencia, personalizado para el siguiente cliente.

CONTEXTO DEL CLIENTE:
{$contextoTxt}
{$contextoExtraBlock}
INSTRUCCIONES:
- Generar un arbol de decision con un nodo de inicio y entre 5 y 8 ramas principales correspondientes a las amenazas mas relevantes del cliente.
- Cada rama debe tener entre 3 y 5 pasos de accion concretos.
- Tono tecnico, sin tildes, sin emojis.
- ESPANOL COLOMBIANO 100%: prohibido usar extranjerismos, anglicismos o siglas en ingles. Ejemplos de lo que NO se debe escribir: 'drop-cover-hold', 'lockdown', 'emergency kit', 'safe zone', 'panic button', 'briefing'. Usar siempre el equivalente en espanol: 'agacharse, cubrirse y sujetarse', 'confinamiento', 'kit de emergencia', 'zona segura', 'boton de panico', 'reunion informativa'.

FORMATO DE RESPUESTA:
Devuelve EXCLUSIVAMENTE un objeto JSON valido con esta estructura:
{
  "inicio": "DETECCION DE EMERGENCIA",
  "ramas": [
    {
      "tipo": "INCENDIO",
      "pasos": [
        "Paso 1...",
        "Paso 2...",
        "Paso 3..."
      ]
    },
    {
      "tipo": "SISMO",
      "pasos": ["..."]
    }
  ]
}
PROMPT;

        $payload = [
            'model'      => $this->model,
            'max_tokens' => 4000,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        $resp = $this->request($payload);
        if (!$resp['ok']) {
            return $resp;
        }

        $texto = $resp['data']['content'][0]['text'] ?? '';
        $json  = $this->extraerJSON($texto);
        if ($json === null) {
            return ['ok' => false, 'error' => 'Respuesta IA no es JSON valido', 'raw' => $texto];
        }

        return [
            'ok'     => true,
            'data'   => $json,
            'tokens' => [
                'in'  => $resp['data']['usage']['input_tokens'] ?? 0,
                'out' => $resp['data']['usage']['output_tokens'] ?? 0,
            ],
        ];
    }

    /**
     * Genera la matriz de responsables del Plan personalizada.
     */
    public function generarMatrizResponsables(array $contextoCliente, string $contextoAdicional = ''): array
    {
        $contextoTxt = $this->serializarContextoCliente($contextoCliente);
        $contextoExtraBlock = $this->bloqueContextoAdicional($contextoAdicional);

        $prompt = <<<PROMPT
Eres un experto en SG-SST y planes de emergencia para propiedad horizontal en Colombia (Decreto 1072/2015 art. 2.2.4.6.25, Decreto 2157/2017).

Genera la MATRIZ DE RESPONSABLES DEL PLAN DE EMERGENCIA para el siguiente cliente:

{$contextoTxt}
{$contextoExtraBlock}
INSTRUCCIONES:
- Tono formal, sin tildes, sin emojis.
- ESPANOL COLOMBIANO 100%: prohibido extranjerismos o anglicismos. Usar siempre terminos en espanol.
- 6 a 10 filas con roles realistas para propiedad horizontal residencial.
- Cada fila: rol, responsabilidad principal y frecuencia de revision.
- Considera: representante legal, administrador, consejo, jefe de brigada (si aplica), vigilancia, brigadistas, comite de convivencia (si aplica).

FORMATO DE RESPUESTA:
Devuelve EXCLUSIVAMENTE un JSON valido:
{
  "filas": [
    {
      "rol": "Representante Legal de la copropiedad",
      "responsabilidad": "Aprobacion, vigencia y financiamiento del Plan...",
      "frecuencia": "Anual"
    },
    ...
  ]
}
PROMPT;

        $payload = [
            'model'      => $this->model,
            'max_tokens' => 2500,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        $resp = $this->request($payload);
        if (!$resp['ok']) {
            return $resp;
        }

        $texto = $resp['data']['content'][0]['text'] ?? '';
        $json  = $this->extraerJSON($texto);
        if ($json === null) {
            return ['ok' => false, 'error' => 'Respuesta IA no es JSON valido', 'raw' => $texto];
        }

        return [
            'ok'     => true,
            'data'   => $json,
            'tokens' => [
                'in'  => $resp['data']['usage']['input_tokens'] ?? 0,
                'out' => $resp['data']['usage']['output_tokens'] ?? 0,
            ],
        ];
    }

    /**
     * Genera el texto personalizado de las secciones Brigada y Simulacros
     * a partir de la inspeccion de Brigada+Simulacros del cliente.
     *
     * @param array $contextoCliente debe contener 'cliente', 'inspeccion', 'brigadaSimulacros'
     * @return array ['ok'=>bool, 'data'=>['brigada_texto'=>..., 'simulacros_texto'=>...], 'error'=>...]
     */
    public function generarBrigadaSimulacros(array $contextoCliente, string $contextoAdicional = ''): array
    {
        $cliente = $contextoCliente['cliente']['nombre_cliente'] ?? 'el conjunto';
        $brigada = $contextoCliente['brigadaSimulacros'] ?? [];
        $contextoExtraBlock = $this->bloqueContextoAdicional($contextoAdicional);

        $existeBrigada    = $brigada['existe_brigada']         ?? 'no';
        $numBrigadistas   = $brigada['numero_brigadistas']     ?? 0;
        $ultimoSimulacro  = $brigada['fecha_ultimo_simulacro'] ?? 'sin registro';
        $tipoSimulacro    = $brigada['tipo_simulacro']         ?? 'sin registro';
        $capacitaciones   = $brigada['capacitaciones_12m']     ?? 'sin registro';
        $observaciones    = $brigada['observaciones']          ?? '';

        $prompt = <<<PROMPT
Eres un experto en SG-SST y planes de emergencia para propiedad horizontal residencial en Colombia (Decreto 1072/2015 art. 2.2.4.6.25, Resolucion 0312/2019, Resolucion 0256/2014).

Tu tarea: generar dos secciones personalizadas para el Plan de Emergencia del cliente {$cliente}, basadas en la inspeccion real de su Brigada y Simulacros.

DATOS REGISTRADOS POR EL CONSULTOR:
- Existe brigada constituida: {$existeBrigada}
- Numero de brigadistas: {$numBrigadistas}
- Fecha del ultimo simulacro: {$ultimoSimulacro}
- Tipo del ultimo simulacro: {$tipoSimulacro}
- Capacitaciones realizadas en los ultimos 12 meses: {$capacitaciones}
- Observaciones del consultor: {$observaciones}
{$contextoExtraBlock}
INSTRUCCIONES:
1. Tono formal tecnico-legal, sin tildes (compatibilidad DOMPDF), sin emojis.
2. ESPANOL COLOMBIANO 100%: prohibido extranjerismos, anglicismos o siglas en ingles. Usar siempre terminos en espanol.
3. Genera dos textos:
   a) BRIGADA (200 a 300 palabras): conformacion y estado actual de la brigada en {$cliente}.
      - Si NO existe brigada, explica el plan de conformacion en 90 dias.
      - Si existe pero esta inactiva o con pocos brigadistas, plan de reactivacion.
      - Cita Decreto 1072/2015 art. 2.2.4.6.25 y Resolucion 0256/2014.
   b) SIMULACROS (200 a 300 palabras): programa de capacitacion y simulacros para los proximos 12 meses.
      - Si el ultimo simulacro fue hace mucho o no hay registro, prioriza simulacro general en los proximos 90 dias.
      - Incluye recomendacion de participar en el Simulacro Nacional de Evacuacion anual.
      - Lista 4 a 6 temas de capacitacion priorizados.
4. Personaliza con datos reales del cliente, no uses texto generico.

FORMATO DE RESPUESTA:
Devuelve EXCLUSIVAMENTE un objeto JSON valido, sin markdown ni texto adicional:
{
  "brigada_texto": "texto completo de la seccion Brigada...",
  "simulacros_texto": "texto completo de la seccion Capacitacion y Simulacros..."
}
PROMPT;

        $payload = [
            'model'      => $this->model,
            'max_tokens' => 3000,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        $resp = $this->request($payload);
        if (!$resp['ok']) {
            return $resp;
        }

        $texto = $resp['data']['content'][0]['text'] ?? '';
        $json  = $this->extraerJSON($texto);
        if ($json === null) {
            return ['ok' => false, 'error' => 'Respuesta IA no es JSON valido', 'raw' => $texto];
        }

        return [
            'ok'     => true,
            'data'   => $json,
            'tokens' => [
                'in'  => $resp['data']['usage']['input_tokens'] ?? 0,
                'out' => $resp['data']['usage']['output_tokens'] ?? 0,
            ],
        ];
    }

    // ============================================================
    // METODOS PRIVADOS
    // ============================================================

    /**
     * Serializa el contexto del cliente en un texto para el prompt.
     */
    private function serializarContextoCliente(array $ctx): string
    {
        $lineas = [];
        $cliente = $ctx['cliente'] ?? [];
        $insp    = $ctx['inspeccion'] ?? [];
        $prob    = $ctx['ultimaProb'] ?? null;
        $matriz  = $ctx['ultimaMatriz'] ?? null;
        $ext     = $ctx['ultimaExt'] ?? null;
        $bot     = $ctx['ultimaBot'] ?? null;
        $loc     = $ctx['ultimaLocativa'] ?? null;
        $rec     = $ctx['ultimaRec'] ?? null;
        $com     = $ctx['ultimaCom'] ?? null;
        $gab     = $ctx['ultimaGab'] ?? null;

        $lineas[] = 'Nombre: ' . ($cliente['nombre_cliente'] ?? '-');
        $lineas[] = 'Direccion: ' . ($cliente['direccion_cliente'] ?? '-');
        $lineas[] = 'Ciudad: ' . ($insp['ciudad'] ?? '-');
        $lineas[] = 'Tipo de inmueble: ' . ($insp['casas_o_apartamentos'] ?? '-');
        if (($insp['casas_o_apartamentos'] ?? '') === 'apartamentos') {
            $lineas[] = 'Numero de torres: ' . ($insp['numero_torres'] ?? '-');
        }
        $lineas[] = 'Anio construccion: ' . ($insp['anio_construccion'] ?? '-');
        $lineas[] = 'Sismo resistente: ' . ($insp['sismo_resistente'] ?? '-');
        $lineas[] = 'Unidades habitacionales: ' . ($insp['numero_unidades_habitacionales'] ?? '-');
        $lineas[] = 'Tiene gabinetes hidraulicos: ' . ($insp['tiene_gabinetes_hidraulico'] ?? '-');
        $lineas[] = 'Tanque de agua: ' . ($insp['tanque_agua'] ?? '-');
        $lineas[] = 'Planta electrica: ' . ($insp['planta_electrica'] ?? '-');
        $lineas[] = 'Sistema de alarma: ' . ($insp['sistema_alarma'] ?? '-');
        $lineas[] = 'Personal de vigilancia: ' . ($insp['personal_vigilancia'] ?? '-');
        $lineas[] = 'Personal de aseo: ' . ($insp['personal_aseo'] ?? '-');

        if ($prob) {
            $lineas[] = 'PROBABILIDAD DE PELIGROS:';
            $campos = [
                'sismos' => 'Sismos',
                'inundaciones' => 'Inundaciones',
                'vendavales' => 'Vendavales',
                'atentados' => 'Atentados',
                'asalto_hurto' => 'Asalto/hurto',
                'vandalismo' => 'Vandalismo',
                'incendios' => 'Incendios',
                'explosiones' => 'Explosiones',
                'inhalacion_gases' => 'Inhalacion gases',
                'falla_estructural' => 'Falla estructural',
                'intoxicacion_alimentos' => 'Intoxicacion alimentos',
                'densidad_poblacional' => 'Densidad poblacional',
            ];
            foreach ($campos as $k => $label) {
                $v = $prob[$k] ?? null;
                if ($v) $lineas[] = "  - {$label}: {$v}";
            }
        }

        if ($ext) {
            $lineas[] = 'EXTINTORES: total ' . ($ext['numero_extintores_totales'] ?? 0)
                . ' (ABC ' . ($ext['cantidad_abc'] ?? 0) . ', CO2 ' . ($ext['cantidad_co2'] ?? 0)
                . ', Solkaflam ' . ($ext['cantidad_solkaflam'] ?? 0) . ', Agua ' . ($ext['cantidad_agua'] ?? 0) . ')';
        }
        if ($bot) {
            $lineas[] = 'BOTIQUIN: ubicacion ' . ($bot['ubicacion_botiquin'] ?? '-')
                . ', estado ' . ($bot['estado_botiquin'] ?? '-');
        }
        if ($matriz) {
            $lineas[] = 'MATRIZ DE VULNERABILIDAD: presente';
        }
        if ($loc) $lineas[] = 'INSPECCION LOCATIVA: completa';
        if ($rec) $lineas[] = 'RECURSOS DE SEGURIDAD: presente';
        if ($com) $lineas[] = 'COMUNICACIONES: presente';
        if ($gab) $lineas[] = 'GABINETES HIDRAULICOS: presente';

        return implode("\n", $lineas);
    }

    /**
     * Junta una lista de strings con saltos de linea para inyectar en prompt.
     */
    private function joinList(array $items): string
    {
        return implode("\n", $items);
    }

    /**
     * Genera el bloque opcional "CONTEXTO ADICIONAL DEL CONSULTOR" que se inyecta
     * en los prompts cuando el profesional escribe notas complementarias desde la
     * vista de revision IA. Devuelve string vacio si no hay contexto.
     */
    private function bloqueContextoAdicional(string $contextoAdicional): string
    {
        $txt = trim($contextoAdicional);
        if ($txt === '') {
            return '';
        }
        return "\nCONTEXTO ADICIONAL DEL CONSULTOR (tomar en cuenta y enfatizar estos aspectos especificos):\n{$txt}\n";
    }

    /**
     * Extrae un objeto JSON del texto de respuesta de la IA.
     * Maneja casos donde la IA envuelve el JSON en markdown ```json ... ```.
     */
    private function extraerJSON(string $texto): ?array
    {
        $texto = trim($texto);
        // Remover bloque markdown si existe
        if (preg_match('/```(?:json)?\s*(\{.*\}|\[.*\])\s*```/s', $texto, $m)) {
            $texto = $m[1];
        }
        // Buscar el primer { hasta el ultimo }
        $inicio = strpos($texto, '{');
        $fin    = strrpos($texto, '}');
        if ($inicio === false || $fin === false || $fin < $inicio) {
            return null;
        }
        $json = substr($texto, $inicio, $fin - $inicio + 1);
        $data = json_decode($json, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Llamada cURL al endpoint de Anthropic.
     */
    private function request(array $payload): array
    {
        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => [
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01',
                'content-type: application/json',
            ],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => $this->timeout,
        ]);

        $body = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false) {
            return ['ok' => false, 'error' => 'cURL error: ' . $err];
        }
        if ($code !== 200) {
            return ['ok' => false, 'error' => 'HTTP ' . $code . ': ' . $body];
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            return ['ok' => false, 'error' => 'Respuesta no es JSON valido'];
        }
        return ['ok' => true, 'data' => $data];
    }
}
