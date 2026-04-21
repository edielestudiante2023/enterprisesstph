<?php

namespace App\Libraries;

/**
 * Clasifica actividades del Plan de Trabajo Anual (PTA) contra el catalogo
 * InspeccionTypes usando Claude Haiku 4.5 via Anthropic Messages API.
 *
 * Devuelve, por cada actividad PTA, un array de matches [{slug, score, reasoning}].
 * El caller persiste los matches en tbl_pta_inspeccion_match.
 */
class PtaClassifier
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const MODEL = 'claude-haiku-4-5-20251001';
    private const BATCH_SIZE = 20;
    private const MAX_MATCHES_PER_ACT = 3;
    private const MIN_SCORE = 0.50;

    private string $apiKey;
    private array $catalog;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?: (getenv('ANTHROPIC_API_KEY') ?: '');
        if (empty($this->apiKey)) {
            throw new \RuntimeException('ANTHROPIC_API_KEY no esta definida en .env o entorno.');
        }
        $this->catalog = InspeccionTypes::all();
    }

    /**
     * Clasifica un lote de actividades. Devuelve:
     *   [ id_ptacliente => [ {slug, score, reasoning}, ... ], ... ]
     */
    public function classifyBatch(array $actividades): array
    {
        $chunks = array_chunk($actividades, self::BATCH_SIZE);
        $out = [];
        foreach ($chunks as $i => $chunk) {
            $result = $this->callApi($chunk);
            foreach ($result as $idPta => $matches) {
                $out[$idPta] = $matches;
            }
        }
        return $out;
    }

    private function callApi(array $chunk): array
    {
        $catalogText = $this->buildCatalogBlock();
        $actsText = $this->buildActividadesBlock($chunk);

        $systemPrompt = "Eres un clasificador experto en Sistemas de Gestion de Seguridad y Salud en el Trabajo (SST) y propiedad horizontal en Colombia. Tu tarea es mapear actividades del Plan de Trabajo Anual (PTA) contra un catalogo de tipos de inspeccion ya ejecutables en el sistema. Piensas en terminos del Decreto 1072 de 2015 y del ciclo PHVA. Respondes SIEMPRE JSON valido, sin texto adicional.";

        $userPrompt = <<<PROMPT
CATALOGO DE TIPOS DE INSPECCION DISPONIBLES:
{$catalogText}

ACTIVIDADES PTA A CLASIFICAR:
{$actsText}

INSTRUCCIONES:
- Para cada actividad, devuelve 0 a 3 matches con los slugs del catalogo.
- Solo mapea cuando hay una relacion CLARA y operativa (la actividad PTA describe algo que podria evidenciarse ejecutando ese tipo de inspeccion).
- Ignora actividades administrativas puras (definir politicas, establecer presupuestos, convocar comites) salvo que el catalogo tenga un tipo equivalente.
- Score entre 0.00 y 1.00. Usa >=0.85 para match inequivoco, 0.70-0.84 bueno, 0.50-0.69 debil, <0.50 no reportar.
- Reasoning: 1 frase en espanol, max 120 caracteres.

FORMATO DE SALIDA (JSON estricto):
{
  "matches": [
    { "id_ptacliente": 123, "slug": "extintores", "score": 0.92, "reasoning": "Mantenimiento preventivo de equipos contra incendio" },
    { "id_ptacliente": 124, "slug": "piscinas", "score": 0.88, "reasoning": "Control de calidad de agua en piscinas" }
  ]
}

Si una actividad no tiene ningun match con score >= 0.50, no la incluyas en el array.
PROMPT;

        $payload = [
            'model' => self::MODEL,
            'max_tokens' => 4096,
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ];

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 120,
        ]);

        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($resp === false) {
            throw new \RuntimeException("cURL error: {$err}");
        }

        if ($httpCode !== 200) {
            throw new \RuntimeException("Anthropic API HTTP {$httpCode}: " . substr($resp, 0, 500));
        }

        $data = json_decode($resp, true);
        $content = $data['content'][0]['text'] ?? '';
        if ($content === '') {
            return [];
        }

        $content = $this->stripCodeFences($content);
        $parsed = json_decode($content, true);
        if (!is_array($parsed) || !isset($parsed['matches'])) {
            return [];
        }

        $bucket = [];
        foreach ($parsed['matches'] as $m) {
            $idPta = (int) ($m['id_ptacliente'] ?? 0);
            $slug = (string) ($m['slug'] ?? '');
            $score = (float) ($m['score'] ?? 0);
            $reasoning = (string) ($m['reasoning'] ?? '');

            if ($idPta <= 0 || $slug === '' || $score < self::MIN_SCORE) {
                continue;
            }
            if (InspeccionTypes::bySlug($slug) === null) {
                continue;
            }
            $bucket[$idPta][] = [
                'slug' => $slug,
                'score' => round($score, 3),
                'reasoning' => mb_substr($reasoning, 0, 250),
            ];
        }

        foreach ($bucket as $idPta => &$matches) {
            usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);
            $matches = array_slice($matches, 0, self::MAX_MATCHES_PER_ACT);
        }
        unset($matches);

        return $bucket;
    }

    public function getModel(): string
    {
        return self::MODEL;
    }

    private function buildCatalogBlock(): string
    {
        $lines = [];
        foreach ($this->catalog as $t) {
            $lines[] = sprintf('- %s | grupo: %s | %s', $t['slug'], $t['group'] ?? 'Otros', $t['label']);
        }
        return implode("\n", $lines);
    }

    private function buildActividadesBlock(array $chunk): string
    {
        $lines = [];
        foreach ($chunk as $a) {
            $lines[] = sprintf(
                "id_ptacliente=%d | PHVA=%s | numeral=%s | actividad=%s",
                (int) $a['id_ptacliente'],
                $a['phva_plandetrabajo'] ?? '',
                $a['numeral_plandetrabajo'] ?? '',
                trim($a['actividad_plandetrabajo'] ?? '')
            );
        }
        return implode("\n", $lines);
    }

    private function stripCodeFences(string $text): string
    {
        $text = trim($text);
        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```(json)?\s*/i', '', $text);
            $text = preg_replace('/\s*```\s*$/', '', $text);
        }
        return trim($text);
    }
}
