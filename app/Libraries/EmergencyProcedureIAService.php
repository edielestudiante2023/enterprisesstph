<?php

namespace App\Libraries;

/**
 * EmergencyProcedureIAService
 * ------------------------------------------------------------
 * Servicio de generación de procedimientos de reacción en emergencia
 * para áreas específicas de una copropiedad (piscina/zona húmeda,
 * baño turco, sauna, gym, zona BBQ).
 *
 * Patrón: cURL directo a api.anthropic.com/v1/messages (mismo patrón
 * que PlanEmergenciaIAService).
 *
 * Modelo por defecto: claude-haiku-4-5-20251001
 *   (rápido y barato; ~300-500 tokens out por escenario).
 *
 * Variables de entorno requeridas:
 *   ANTHROPIC_API_KEY
 *   ANTHROPIC_MODEL_HAIKU (opcional; default claude-haiku-4-5-20251001)
 *
 * Uso:
 *   $svc = new EmergencyProcedureIAService();
 *   $r = $svc->generarEscenario($area, $escenario, $contextoCliente);
 *   // $r = ['ok'=>bool, 'data'=>['que_hacer'=>..., 'que_no_hacer'=>..., 'cuando'=>..., 'quien'=>..., 'recursos'=>...]]
 *
 * Todos los métodos devuelven array con ['ok'=>bool, 'data'=>mixed, 'error'=>string].
 */
class EmergencyProcedureIAService
{
    private string $apiKey;
    private string $model;
    private string $endpoint = 'https://api.anthropic.com/v1/messages';
    private int $timeout = 60;

    public function __construct()
    {
        $this->apiKey = getenv('ANTHROPIC_API_KEY') ?: '';
        $this->model  = getenv('ANTHROPIC_MODEL_HAIKU') ?: 'claude-haiku-4-5-20251001';
    }

    /**
     * Extrae campos de un ensayo de laboratorio (microbiologico o fisicoquimico)
     * a partir del PDF enviado por el consultor. Usa la API Claude con contenido
     * tipo "document" (PDF nativo).
     *
     * @param string $pdfAbsolutePath Ruta absoluta al PDF.
     * @param string $tipoHint         'MICROBIOLOGICO' o 'FISICOQUIMICO' para guiar el parseo.
     * @return array ['ok'=>bool, 'data'=>array con campos extraidos, 'error'=>string]
     */
    public function extraerEnsayoDesdePDF(string $pdfAbsolutePath, string $tipoHint = 'MICROBIOLOGICO'): array
    {
        if (empty($this->apiKey)) return ['ok'=>false,'error'=>'ANTHROPIC_API_KEY no configurada'];
        if (!file_exists($pdfAbsolutePath)) return ['ok'=>false,'error'=>'PDF no encontrado: ' . $pdfAbsolutePath];

        $pdfB64 = base64_encode(file_get_contents($pdfAbsolutePath));

        $tipoLbl = $tipoHint === 'FISICOQUIMICO' ? 'fisicoquimico' : 'microbiologico';
        $prompt = <<<PROMPT
Eres un lector experto de informes de laboratorio de calidad de agua para piscinas en Colombia, bajo la Resolucion 234/2026 del Minsalud.

Analiza el PDF adjunto (informe de ensayo {$tipoLbl}) y extrae los siguientes campos.
Si un campo no aparece claramente, devuelve cadena vacia — NO inventes ni adivines.

NORMAS VALIDAS EN ESTE DOMINIO (lista blanca — solo estas son aceptables como `norma_citada`):
  - "Resolucion 234 de 2026 del Ministerio de Salud y Proteccion Social" (vigente)
  - "Resolucion 1618 de 2010 del Ministerio de la Proteccion Social" (derogada por Res 234/2026 pero aun aparece en informes viejos)
  - "Decreto 780 de 2016 del Ministerio de Salud" (compilatorio)
  - "Ley 9 de 1979 Codigo Sanitario Nacional"

CRITICO — lectura cuidadosa del numero de resolucion:
  - NO son normas validas para calidad de agua de piscinas: Res 1411/2010 (afiliados SGSSS), Res 1441/2010 (habilitacion prestadores), Res 1510/2011, Res 4113/2012.
  - Si el informe cita Resolucion 1618 de 2010 pero la firmas leen 1411, 1441, 1618 — privilegia 1618 porque es la UNICA de esa serie que regula agua de piscinas.
  - Si el numero leido no esta en la lista blanca arriba, verifica el OCR. Si persistes, devuelve "norma_citada" vacia y agrega en "observaciones" algo como "OCR ambiguo: leyo Res XXX/YYYY — verificar manualmente".

Campos a extraer:
- fecha_toma            (formato YYYY-MM-DD o vacio)
- fecha_emision         (formato YYYY-MM-DD o vacio)
- laboratorio           (razon social)
- laboratorio_nit       (NIT con guiones si aparece)
- numero_informe        (codigo del informe, ej A25-0799)
- norma_citada          (UNA de la lista blanca arriba; vacia si no es reconocida)
- heterotrofos_ufc      (numero decimal, ej 1, 10, 200)
- coliformes_termotolerantes_ufc
- ecoli_ufc
- pseudomonas_ufc
- legionella_ufc
- conforme_global       ("SI", "NO" o "PARCIAL" segun diga el informe)
- observaciones         (alerta si el informe cita una resolucion derogada antes de 2026; o nota de OCR ambiguo)

Convierte "<1" a 0. Convierte "Ausencia" / "Presencia" a 0 y 1 respectivamente.

Devuelve EXCLUSIVAMENTE un objeto JSON valido con esas claves. Sin texto adicional ni markdown.
PROMPT;

        $payload = [
            'model'      => $this->model,
            'max_tokens' => 1500,
            'messages'   => [[
                'role' => 'user',
                'content' => [
                    ['type' => 'document', 'source' => ['type'=>'base64', 'media_type'=>'application/pdf', 'data'=>$pdfB64]],
                    ['type' => 'text', 'text' => $prompt],
                ],
            ]],
        ];

        $resp = $this->request($payload);
        if (!$resp['ok']) return $resp;

        $texto = $resp['data']['content'][0]['text'] ?? '';
        $json  = $this->extraerJSON($texto);
        if ($json === null) return ['ok'=>false, 'error'=>'Respuesta IA no es JSON valido', 'raw'=>$texto];

        return [
            'ok'     => true,
            'data'   => $json,
            'modelo' => $this->model,
            'tokens' => [
                'in'  => $resp['data']['usage']['input_tokens'] ?? 0,
                'out' => $resp['data']['usage']['output_tokens'] ?? 0,
            ],
        ];
    }

    /**
     * Ping de prueba.
     */
    public function ping(): array
    {
        if (empty($this->apiKey)) {
            return ['ok' => false, 'error' => 'ANTHROPIC_API_KEY no configurada'];
        }
        $payload = [
            'model' => $this->model,
            'max_tokens' => 20,
            'messages' => [['role' => 'user', 'content' => 'Responde solo: OK']],
        ];
        return $this->request($payload);
    }

    /**
     * Genera un escenario de emergencia completo para un área específica.
     *
     * @param string $area             Código del área: PISCINA, BANO_TURCO, SAUNA, GYM, ZONA_BBQ.
     * @param string $escenarioNombre  Ej: "Sismo con bañistas en el agua".
     * @param array  $contextoCliente  Datos del cliente: nombre, direccion, copropiedad,
     *                                 responsable_area, horario, aforo, recursos, etc.
     * @return array ['ok'=>bool, 'data'=>['que_hacer','que_no_hacer','cuando','quien','recursos']]
     */
    public function generarEscenario(string $area, string $escenarioNombre, array $contextoCliente = []): array
    {
        if (empty($this->apiKey)) {
            return ['ok' => false, 'error' => 'ANTHROPIC_API_KEY no configurada en .env'];
        }

        $areaLabel = $this->etiquetaArea($area);
        $ctxTxt = $this->serializarContexto($contextoCliente);

        $prompt = <<<PROMPT
Eres un experto en planes y procedimientos de emergencia para copropiedades colombianas bajo Decreto 1072/2015 (SG-SST), Ley 1523/2012 (gestion del riesgo), NTC 1700, y Resolucion 234/2026 Minsalud cuando aplica.

Tu tarea: redactar un PROCEDIMIENTO DE REACCION EN EMERGENCIA para el area "{$areaLabel}" y el escenario "{$escenarioNombre}".

CONTEXTO DE LA COPROPIEDAD:
{$ctxTxt}

POBLACIONES VULNERABLES (INCLUIR SIEMPRE):
En una copropiedad residencial conviven poblaciones vulnerables que requieren manejo diferencial en emergencia:
  - Ninos de brazos (0-2 anos): no pueden agarrarse ni seguir instrucciones
  - Ninos entre 3 y 12 anos: requieren un adulto que los acompane y los contenga
  - Mujeres embarazadas: riesgo de caida, estres fisiologico, no cargar pesos
  - Adultos mayores (60+): movilidad reducida, tiempos de respuesta mas largos
  - Personas en condicion de discapacidad motriz (silla de ruedas, muletas): requieren ayuda fisica para evacuar
  - Personas con discapacidad cognitiva, visual, auditiva: requieren instrucciones adaptadas y acompanante

DEBES incluir en "que_hacer" pasos explicitos para estas poblaciones cuando el escenario las afecte. En "quien" nombra a quien queda a cargo de asistir a cada poblacion (vecino mas cercano, brigadista de piso, administrador). NO invisibilices estas poblaciones en el procedimiento.

REGLAS DE REDACCION:
1. Espanol colombiano 100%. Sin tildes (compatibilidad DOMPDF). Sin emojis. Sin anglicismos.
2. Tono formal tecnico, operativo, accionable. Como lo leeria un vigilante o administrador bajo presion.
3. NO describas la amenaza ni des teoria — ve directo a que hacer en los primeros minutos del evento.
4. Acciones concretas, verbos en imperativo o tercera persona del plural. Evita generalidades.
5. Especifico para el area (piscina vs gym vs turco). Contempla caracteristicas del agua, vapor, temperatura, electricidad, quimicos, segun corresponda.
6. 5 bloques, cada uno 60-150 palabras aproximadamente (mas largo que antes porque debe contemplar poblaciones vulnerables):
   - que_hacer: secuencia numerada de acciones inmediatas en los primeros 5 minutos. Incluir sub-acciones para poblaciones vulnerables aplicables al escenario.
   - que_no_hacer: errores graves que agravarian la emergencia, incluyendo errores con poblaciones vulnerables (ej: no dejar a un nino de brazos en el piso, no forzar a un adulto mayor a correr).
   - cuando: disparadores / momento del dia / condiciones que activan el procedimiento. Identifica momentos de mayor afluencia de poblaciones vulnerables (ej: fines de semana, horario familiar).
   - quien: roles responsables (vigilante, administrador, brigadista, operario, familiares) + asignacion clara de quien asiste a cada poblacion vulnerable.
   - recursos: equipos, dotacion, comunicaciones y documentos necesarios. Incluir recursos para poblaciones vulnerables (flotador infantil, silla portatil, camilla, linterna, etc.) segun aplique.

FORMATO DE RESPUESTA:
Devuelve EXCLUSIVAMENTE un objeto JSON valido sin markdown ni texto adicional:
{
  "que_hacer": "...",
  "que_no_hacer": "...",
  "cuando": "...",
  "quien": "...",
  "recursos": "..."
}
PROMPT;

        $payload = [
            'model'      => $this->model,
            'max_tokens' => 2500,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ];

        $resp = $this->request($payload);
        if (!$resp['ok']) return $resp;

        $texto = $resp['data']['content'][0]['text'] ?? '';
        $json  = $this->extraerJSON($texto);
        if ($json === null) {
            return ['ok' => false, 'error' => 'Respuesta IA no es JSON valido', 'raw' => $texto];
        }

        $clavesEsperadas = ['que_hacer', 'que_no_hacer', 'cuando', 'quien', 'recursos'];
        foreach ($clavesEsperadas as $k) {
            if (!isset($json[$k])) $json[$k] = '';
        }

        return [
            'ok'     => true,
            'data'   => $json,
            'modelo' => $this->model,
            'tokens' => [
                'in'  => $resp['data']['usage']['input_tokens'] ?? 0,
                'out' => $resp['data']['usage']['output_tokens'] ?? 0,
            ],
        ];
    }

    /**
     * Etiqueta legible del área para inyectar en prompts.
     */
    private function etiquetaArea(string $area): string
    {
        return match (strtoupper($area)) {
            'PISCINA'     => 'Piscina / zona humeda',
            'BANO_TURCO'  => 'Bano turco',
            'SAUNA'       => 'Sauna',
            'GYM'         => 'Gimnasio',
            'ZONA_BBQ'    => 'Zona BBQ',
            default       => ucfirst(strtolower(str_replace('_', ' ', $area))),
        };
    }

    private function serializarContexto(array $ctx): string
    {
        if (empty($ctx)) return '(sin datos adicionales suministrados)';
        $lineas = [];
        foreach ($ctx as $k => $v) {
            if ($v === null || $v === '') continue;
            $lineas[] = '- ' . ucfirst(str_replace('_', ' ', (string)$k)) . ': ' . (is_array($v) ? json_encode($v) : (string)$v);
        }
        return empty($lineas) ? '(sin datos adicionales suministrados)' : implode("\n", $lineas);
    }

    /**
     * Escenarios predefinidos por área (Fase 1 trae solo PISCINA; resto en Fase 3).
     */
    public static function escenariosPorArea(string $area): array
    {
        $area = strtoupper($area);
        return match ($area) {
            'PISCINA' => [
                ['codigo' => 'sismo_banistas_agua',      'nombre' => 'Sismo con banistas en el agua'],
                ['codigo' => 'ahogamiento',              'nombre' => 'Ahogamiento o semiahogamiento'],
                ['codigo' => 'electrocucion',            'nombre' => 'Electrocucion por equipo electrico de la piscina'],
                ['codigo' => 'tormenta_electrica',       'nombre' => 'Tormenta electrica y rayos (piscinas abiertas)'],
                ['codigo' => 'desmayo_hipoglucemia',     'nombre' => 'Desmayo, hipoglucemia o emergencia medica'],
                ['codigo' => 'herida_golpe_cabeza',      'nombre' => 'Herida grave o golpe en la cabeza'],
                ['codigo' => 'liberacion_fecal',         'nombre' => 'Liberacion fecal o de fluidos corporales en el agua'],
                ['codigo' => 'derrame_quimico',          'nombre' => 'Escape o derrame de producto quimico del cuarto de bombas'],
            ],
            'BANO_TURCO' => [
                ['codigo' => 'quemadura_vapor',          'nombre' => 'Quemaduras por vapor'],
                ['codigo' => 'desmayo_calor',            'nombre' => 'Desmayo por calor extremo'],
                ['codigo' => 'claustrofobia',            'nombre' => 'Crisis de claustrofobia'],
                ['codigo' => 'falla_generador',          'nombre' => 'Falla electrica del generador de vapor'],
            ],
            'SAUNA' => [
                ['codigo' => 'quemadura_contacto',       'nombre' => 'Quemaduras por contacto con piedras o madera'],
                ['codigo' => 'deshidratacion',           'nombre' => 'Deshidratacion severa'],
                ['codigo' => 'desmayo_calor',            'nombre' => 'Desmayo por calor'],
                ['codigo' => 'incendio_sauna',           'nombre' => 'Incendio interior de la sauna'],
            ],
            'GYM' => [
                ['codigo' => 'caida_mancuerna',          'nombre' => 'Caida de mancuerna o disco'],
                ['codigo' => 'lesion_espalda',           'nombre' => 'Lesion de espalda o columna por carga'],
                ['codigo' => 'desmayo_esfuerzo',         'nombre' => 'Desmayo por esfuerzo o paro cardiorrespiratorio'],
                ['codigo' => 'descarga_maquina',         'nombre' => 'Descarga electrica de maquina cardiovascular'],
            ],
            'ZONA_BBQ' => [
                ['codigo' => 'quemadura_bbq',            'nombre' => 'Quemaduras por parrilla o brasas'],
                ['codigo' => 'incendio_bbq',             'nombre' => 'Incendio de la zona BBQ'],
                ['codigo' => 'inhalacion_humo',          'nombre' => 'Inhalacion de humo o monoxido'],
                ['codigo' => 'fuga_gas',                 'nombre' => 'Fuga de gas propano'],
            ],
            default => [],
        };
    }

    // ---------- Helpers privados (clonados del patrón PlanEmergenciaIAService) ----------

    private function extraerJSON(string $texto): ?array
    {
        $texto = trim($texto);
        if (preg_match('/```(?:json)?\s*(\{.*\}|\[.*\])\s*```/s', $texto, $m)) {
            $texto = $m[1];
        }
        $inicio = strpos($texto, '{');
        $fin    = strrpos($texto, '}');
        if ($inicio === false || $fin === false || $fin < $inicio) {
            return null;
        }
        $json = substr($texto, $inicio, $fin - $inicio + 1);
        $data = json_decode($json, true);
        return is_array($data) ? $data : null;
    }

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
