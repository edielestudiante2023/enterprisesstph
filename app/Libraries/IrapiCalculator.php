<?php

namespace App\Libraries;

/**
 * IrapiCalculator
 * ------------------------------------------------------------
 * Cálculos de calidad del agua de piscinas según Resolución 234/2026
 * del Ministerio de Salud (Anexos Técnicos I y II).
 *
 *  - IRAPI (Índice de Riesgo del Agua) — Anexo II
 *    IRAPI = VCM(45%) + VCR(20%) + VAC(30%) + VCT(5%)
 *    Clasificación: 0-10 Óptimo, 10.1-35 Bajo, 35.1-75 Medio, 75.1-100 Alto
 *
 *  - ISL (Índice de Saturación de Langelier) — Anexo I
 *    ISL = pH + CT + CD + CA − 12.1
 *    Interpretación: 0 balanceada, >0 tendencia corrosiva, <0 tendencia incrustante
 *
 *  - Validación de rangos contra Anexo I (pH, cloro, turbidez, ORP, etc.)
 *
 * Uso:
 *   $calc = new IrapiCalculator();
 *   $r = $calc->calcularIrapi($mediciones, 'PISCINAS');
 *   // $r = ['valor'=>float, 'clasificacion'=>string, 'VCM'=>float, 'VCR'=>float, ...]
 *
 *   $isl = $calc->calcularIsl($pH, $temp, $durezaCa, $alcTotal);
 */
class IrapiCalculator
{
    // ---------- Rangos aceptables (Anexo I Res 234/2026) ----------

    public const RANGOS_PISCINAS = [
        'pH'                => ['min' => 6.8, 'max' => 7.3],
        'cloro_libre'       => ['min' => 1.5, 'max' => 3.5],
        'cloro_combinado'   => ['min' => 0.0, 'max' => 0.3],
        'temperatura'       => ['min' => null, 'max' => 40.0],
        'turbidez'          => ['min' => 0.0, 'max' => 1.0],   // ideal < 1 UNT
        'orp'               => ['min' => null, 'max' => 700.0],
        'tds'               => ['min' => 1000.0, 'max' => 1200.0],
        'conductividad'     => ['min' => 2000.0, 'max' => 2400.0],
        'acido_cianurico'   => ['min' => null, 'max' => 150.0],
        'dureza_calcica'    => ['min' => 200.0, 'max' => 700.0],
        'alcalinidad_total' => ['min' => 60.0, 'max' => 150.0],
        'bromo_total'       => ['min' => 2.0, 'max' => 4.0],
    ];

    public const RANGOS_ESTRUCTURAS_SIMILARES = [
        'pH'                => ['min' => 6.8, 'max' => 7.3],
        'cloro_libre'       => ['min' => 2.0, 'max' => 3.5],
        'cloro_combinado'   => ['min' => 0.0, 'max' => 0.4],
        'temperatura'       => ['min' => null, 'max' => 40.0],
        'turbidez'          => ['min' => 0.0, 'max' => 1.0],
        'orp'               => ['min' => null, 'max' => 700.0],
        'tds'               => ['min' => 1000.0, 'max' => 1200.0],
        'conductividad'     => ['min' => 2000.0, 'max' => 2400.0],
        'acido_cianurico'   => ['min' => null, 'max' => 150.0],
        'dureza_calcica'    => ['min' => 200.0, 'max' => 700.0],
        'alcalinidad_total' => ['min' => 60.0, 'max' => 150.0],
        'bromo_total'       => ['min' => 2.0, 'max' => 5.0],
    ];

    public const RANGOS_MICROBIOLOGICOS = [
        'heterotrofos_ufc'              => ['max' => 200.0],
        'coliformes_termotolerantes_ufc' => ['max' => 0.0],
        'ecoli_ufc'                     => ['max' => 0.0],
        'pseudomonas_ufc'               => ['max' => 0.0],
        'legionella_ufc'                => ['max' => 0.0],
    ];

    // ---------- Tabla de coeficientes ISL (Anexo I Res 234/2026) ----------

    public const COEF_TEMPERATURA = [
        5 => 0.130, 10 => 0.257, 15 => 0.376, 17 => 0.422, 19 => 0.466,
        20 => 0.487, 21 => 0.509, 22 => 0.529, 23 => 0.550, 24 => 0.570,
        25 => 0.590, 26 => 0.610, 27 => 0.629, 28 => 0.648, 29 => 0.667,
        30 => 0.685, 31 => 0.703, 32 => 0.721, 33 => 0.738, 34 => 0.755,
        35 => 0.772, 36 => 0.788, 37 => 0.805, 38 => 0.820,
    ];

    public const COEF_DUREZA = [
        5 => 0.305, 10 => 0.606, 15 => 0.762, 25 => 1.004, 50 => 1.306,
        75 => 1.482, 100 => 1.607, 125 => 1.704, 150 => 1.784, 175 => 1.851,
        200 => 1.909, 225 => 1.959, 250 => 2.004, 275 => 2.047, 300 => 2.085,
        350 => 2.152, 400 => 2.210, 450 => 2.261, 500 => 2.307, 550 => 2.348,
        600 => 2.386, 650 => 2.421, 700 => 2.453, 750 => 2.483, 800 => 2.511,
    ];

    public const COEF_ALCALINIDAD = [
        10 => 1.006, 20 => 1.307, 30 => 1.551, 40 => 1.609, 45 => 1.660,
        50 => 1.706, 55 => 1.747, 60 => 1.785, 65 => 1.820, 70 => 1.852,
        75 => 1.881, 80 => 1.910, 85 => 1.937, 90 => 1.961, 95 => 1.985,
        100 => 2.007, 105 => 2.028, 110 => 2.049, 120 => 2.087, 130 => 2.121,
        140 => 2.154, 150 => 2.184, 200 => 2.311,
    ];

    // ---------- Umbrales IRAPI ----------

    public const IRAPI_VCM_PORCENTAJE = 45.0;
    public const IRAPI_VCR_POR_ENCIMA = 10.0;
    public const IRAPI_VCR_POR_DEBAJO = 20.0;
    public const IRAPI_VAC_PORCENTAJE = 30.0;
    public const IRAPI_VCT_PORCENTAJE = 5.0;

    /**
     * Clasifica el valor IRAPI (0-100) según Anexo II.
     */
    public function clasificarIrapi(float $valor): string
    {
        if ($valor <= 10.0)  return 'SIN_RIESGO';
        if ($valor <= 35.0)  return 'BAJO';
        if ($valor <= 75.0)  return 'MEDIO';
        return 'ALTO';
    }

    /**
     * Calcula IRAPI a partir del mapa de mediciones.
     *
     * @param array $mediciones Mapa parametro => valor numérico.
     *                          Claves esperadas:
     *                          pH, cloro_libre, cloro_combinado, temperatura,
     *                          turbidez, orp, acido_cianurico,
     *                          heterotrofos_ufc, coliformes_termotolerantes_ufc,
     *                          ecoli_ufc, pseudomonas_ufc.
     * @param string $tipoEstanque 'PISCINAS' o 'ESTRUCTURAS_SIMILARES'.
     *                             Afecta los rangos de cloro libre/combinado.
     *
     * @return array ['valor'=>float, 'clasificacion'=>string,
     *                'VCM'=>float, 'VCR'=>float, 'VAC'=>float, 'VCT'=>float,
     *                'detalle'=>array de parametros fuera de rango]
     */
    public function calcularIrapi(array $mediciones, string $tipoEstanque = 'PISCINAS'): array
    {
        $rangos = ($tipoEstanque === 'ESTRUCTURAS_SIMILARES')
            ? self::RANGOS_ESTRUCTURAS_SIMILARES
            : self::RANGOS_PISCINAS;

        $detalle = [];

        // --- VCM: microbiológicos (binario: 0% o 45%) ---
        $vcm = 0.0;
        $microParams = ['heterotrofos_ufc', 'coliformes_termotolerantes_ufc', 'ecoli_ufc', 'pseudomonas_ufc'];
        foreach ($microParams as $p) {
            if (!isset($mediciones[$p])) continue;
            $max = self::RANGOS_MICROBIOLOGICOS[$p]['max'] ?? null;
            if ($max !== null && (float)$mediciones[$p] > $max) {
                $vcm = self::IRAPI_VCM_PORCENTAJE;
                $detalle[$p] = 'fuera_de_rango';
            }
        }

        // --- VCR: residual desinfectante (cloro libre). 0%/10%/20% ---
        $vcr = 0.0;
        if (isset($mediciones['cloro_libre'])) {
            $cl = (float)$mediciones['cloro_libre'];
            $min = $rangos['cloro_libre']['min'];
            $max = $rangos['cloro_libre']['max'];
            if ($cl > $max) { $vcr = self::IRAPI_VCR_POR_ENCIMA; $detalle['cloro_libre'] = 'por_encima'; }
            elseif ($cl < $min) { $vcr = self::IRAPI_VCR_POR_DEBAJO; $detalle['cloro_libre'] = 'por_debajo'; }
        }

        // --- VAC: pH + ORP + Ácido cianúrico (binario grupal: 0% o 30%) ---
        $vac = 0.0;
        $vacParams = ['pH', 'orp', 'acido_cianurico'];
        foreach ($vacParams as $p) {
            if (!isset($mediciones[$p])) continue;
            $valor = (float)$mediciones[$p];
            $min = $rangos[$p]['min'];
            $max = $rangos[$p]['max'];
            $fueraMin = ($min !== null && $valor < $min);
            $fueraMax = ($max !== null && $valor > $max);
            if ($fueraMin || $fueraMax) {
                $vac = self::IRAPI_VAC_PORCENTAJE;
                $detalle[$p] = $fueraMin ? 'por_debajo' : 'por_encima';
            }
        }

        // --- VCT: turbidez (binario: 0% o 5%) ---
        $vct = 0.0;
        if (isset($mediciones['turbidez'])) {
            $t = (float)$mediciones['turbidez'];
            $ideal = $rangos['turbidez']['max']; // <1 UNT
            if ($ideal !== null && $t > $ideal) {
                $vct = self::IRAPI_VCT_PORCENTAJE;
                $detalle['turbidez'] = 'por_encima';
            }
        }

        $irapi = $vcm + $vcr + $vac + $vct;
        $irapi = round($irapi, 2);

        return [
            'valor'         => $irapi,
            'clasificacion' => $this->clasificarIrapi($irapi),
            'VCM'           => $vcm,
            'VCR'           => $vcr,
            'VAC'           => $vac,
            'VCT'           => $vct,
            'detalle'       => $detalle,
        ];
    }

    /**
     * Calcula el Índice de Saturación de Langelier (ISL).
     * ISL = pH + CT + CD + CA − 12.1
     *
     * @return array ['valor'=>float, 'interpretacion'=>string] o null si insumos faltan.
     */
    public function calcularIsl(
        ?float $pH,
        ?float $temperaturaC,
        ?float $durezaCalcicaMgL,
        ?float $alcalinidadTotalMgL
    ): ?array {
        if ($pH === null || $temperaturaC === null || $durezaCalcicaMgL === null || $alcalinidadTotalMgL === null) {
            return null;
        }

        $ct = $this->interpolar(self::COEF_TEMPERATURA, $temperaturaC);
        $cd = $this->interpolar(self::COEF_DUREZA, $durezaCalcicaMgL);
        $ca = $this->interpolar(self::COEF_ALCALINIDAD, $alcalinidadTotalMgL);

        if ($ct === null || $cd === null || $ca === null) {
            return null;
        }

        $isl = $pH + $ct + $cd + $ca - 12.1;
        $isl = round($isl, 2);

        $interp = 'BALANCEADA';
        if ($isl > 0.5)       $interp = 'CORROSIVA';
        elseif ($isl < -0.5)  $interp = 'INCRUSTANTE';

        return ['valor' => $isl, 'interpretacion' => $interp];
    }

    /**
     * Interpolación lineal en una tabla de coeficientes por llave numérica.
     * Si el valor está fuera del rango, usa el coeficiente extremo más cercano.
     */
    private function interpolar(array $tabla, float $valor): ?float
    {
        if (empty($tabla)) return null;
        $keys = array_keys($tabla);
        sort($keys, SORT_NUMERIC);

        $min = $keys[0];
        $max = $keys[count($keys) - 1];
        if ($valor <= $min) return $tabla[$min];
        if ($valor >= $max) return $tabla[$max];

        // Buscar par de llaves que encierran el valor.
        for ($i = 0; $i < count($keys) - 1; $i++) {
            $k1 = $keys[$i];
            $k2 = $keys[$i + 1];
            if ($valor >= $k1 && $valor <= $k2) {
                $c1 = $tabla[$k1];
                $c2 = $tabla[$k2];
                if ($k2 == $k1) return $c1;
                $ratio = ($valor - $k1) / ($k2 - $k1);
                return round($c1 + ($c2 - $c1) * $ratio, 3);
            }
        }

        return null;
    }

    /**
     * Valida un parámetro contra su rango aceptable.
     *
     * @return array ['conforme'=>'SI'|'NO'|'NA', 'rango'=>string, 'observacion'=>string]
     */
    public function validarParametro(string $parametro, $valor, string $tipoEstanque = 'PISCINAS'): array
    {
        $rangos = ($tipoEstanque === 'ESTRUCTURAS_SIMILARES')
            ? self::RANGOS_ESTRUCTURAS_SIMILARES
            : self::RANGOS_PISCINAS;

        if (!isset($rangos[$parametro]) || $valor === null || $valor === '') {
            return ['conforme' => 'NA', 'rango' => '', 'observacion' => ''];
        }

        $r = $rangos[$parametro];
        $v = (float)$valor;
        $rangoStr = $this->rangoToString($r);

        $fueraMin = ($r['min'] !== null && $v < $r['min']);
        $fueraMax = ($r['max'] !== null && $v > $r['max']);

        if ($fueraMin) {
            return ['conforme' => 'NO', 'rango' => $rangoStr, 'observacion' => 'Por debajo del mínimo'];
        }
        if ($fueraMax) {
            return ['conforme' => 'NO', 'rango' => $rangoStr, 'observacion' => 'Por encima del máximo'];
        }
        return ['conforme' => 'SI', 'rango' => $rangoStr, 'observacion' => ''];
    }

    private function rangoToString(array $r): string
    {
        $min = $r['min'] ?? null;
        $max = $r['max'] ?? null;
        if ($min === null && $max !== null) return '≤ ' . $max;
        if ($min !== null && $max === null) return '≥ ' . $min;
        if ($min !== null && $max !== null) return $min . ' — ' . $max;
        return '';
    }
}
