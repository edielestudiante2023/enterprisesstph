<?php

namespace App\Libraries;

/**
 * BotiquinCatalogo
 * ------------------------------------------------------------
 * Contenido exigido del botiquín de primeros auxilios para piscinas,
 * según Anexo Técnico III de la Resolución 234/2026 Minsalud.
 *
 *  - Tipo A (<500 m²)         — 13 ítems
 *  - Tipo B (500-2000 m²)     — 32 ítems
 *  - Tipo C (>2000 m²)        — 44 ítems (contenido con cantidades mayores)
 *
 * Uso:
 *   $items = BotiquinCatalogo::items('A');  // array de ['codigo','nombre','unidad','cantidad']
 *   $tipo  = BotiquinCatalogo::tipoPorSuperficie(750.0); // 'B'
 */
class BotiquinCatalogo
{
    /**
     * Determina el tipo de botiquín según la superficie del establecimiento (m²).
     */
    public static function tipoPorSuperficie(?float $m2): string
    {
        if ($m2 === null || $m2 <= 0) return 'NINGUNO';
        if ($m2 < 500)     return 'A';
        if ($m2 <= 2000)   return 'B';
        return 'C';
    }

    /**
     * Devuelve la lista de ítems exigidos para el tipo indicado.
     *
     * @return array<int, array{codigo:string, nombre:string, unidad:string, cantidad:int}>
     */
    public static function items(string $tipo): array
    {
        $tipo = strtoupper($tipo);
        return match ($tipo) {
            'A' => self::tipoA(),
            'B' => self::tipoB(),
            'C' => self::tipoC(),
            default => [],
        };
    }

    private static function tipoA(): array
    {
        return [
            ['codigo' => 'gasas_limpias',          'nombre' => 'Gasas limpias',                     'unidad' => 'Paquete x 20',  'cantidad' => 1],
            ['codigo' => 'esparadrapo_tela_4',     'nombre' => 'Esparadrapo de tela rollo de 4"',   'unidad' => 'Unidad',        'cantidad' => 1],
            ['codigo' => 'bajalenguas',            'nombre' => 'Bajalenguas',                       'unidad' => 'Paquete x 20',  'cantidad' => 1],
            ['codigo' => 'guantes_latex',          'nombre' => 'Guantes de látex para examen',      'unidad' => 'Caja x 100',    'cantidad' => 1],
            ['codigo' => 'venda_elastica_2x5',     'nombre' => 'Venda elástica 2 x 5 yardas',       'unidad' => 'Unidad',        'cantidad' => 1],
            ['codigo' => 'venda_elastica_3x5',     'nombre' => 'Venda elástica 3 x 5 yardas',       'unidad' => 'Unidad',        'cantidad' => 1],
            ['codigo' => 'venda_elastica_5x5',     'nombre' => 'Venda elástica 5 x 5 yardas',       'unidad' => 'Unidad',        'cantidad' => 1],
            ['codigo' => 'venda_algodon_3x5',      'nombre' => 'Venda de algodón 3 x 5 yardas',     'unidad' => 'Unidad',        'cantidad' => 1],
            ['codigo' => 'tijeras_corta_todo',     'nombre' => 'Tijeras corta-todo',                'unidad' => 'Unidad',        'cantidad' => 1],
            ['codigo' => 'yodopovidona',           'nombre' => 'Yodopovidona (jabón quirúrgico)',   'unidad' => 'Frasco x 120 ml','cantidad' => 1],
            ['codigo' => 'solucion_salina',        'nombre' => 'Solución salina 250 cc ó 500 cc',   'unidad' => 'Unidad',        'cantidad' => 2],
            ['codigo' => 'termometro_digital',     'nombre' => 'Termómetro digital',                'unidad' => 'Unidad',        'cantidad' => 1],
            ['codigo' => 'alcohol_antiseptico',    'nombre' => 'Alcohol antiséptico frasco x 275 ml','unidad' => 'Unidad',       'cantidad' => 1],
        ];
    }

    private static function tipoB(): array
    {
        return [
            ['codigo' => 'gasas_limpias',          'nombre' => 'Gasas limpias',                       'unidad' => 'Paquete x 100', 'cantidad' => 1],
            ['codigo' => 'gasas_esteriles',        'nombre' => 'Gasas estériles',                     'unidad' => 'Paquete x 3',   'cantidad' => 20],
            ['codigo' => 'aposito_compresas',      'nombre' => 'Apósito o compresas no estériles',    'unidad' => 'Unidad',        'cantidad' => 4],
            ['codigo' => 'esparadrapo_tela_4',     'nombre' => 'Esparadrapo de tela rollo 4"',        'unidad' => 'Unidad',        'cantidad' => 2],
            ['codigo' => 'bajalenguas',            'nombre' => 'Bajalenguas',                         'unidad' => 'Paquete x 20',  'cantidad' => 2],
            ['codigo' => 'venda_elastica_2x5',     'nombre' => 'Venda elástica 2 x 5 yardas',         'unidad' => 'Unidad',        'cantidad' => 2],
            ['codigo' => 'venda_elastica_3x5',     'nombre' => 'Venda elástica 3 x 5 yardas',         'unidad' => 'Unidad',        'cantidad' => 2],
            ['codigo' => 'venda_elastica_5x5',     'nombre' => 'Venda elástica 5 x 5 yardas',         'unidad' => 'Unidad',        'cantidad' => 2],
            ['codigo' => 'venda_algodon_3x5',      'nombre' => 'Venda de algodón 3 x 5 yardas',       'unidad' => 'Unidad',        'cantidad' => 2],
            ['codigo' => 'venda_algodon_5x5',      'nombre' => 'Venda de algodón 5 x 5 yardas',       'unidad' => 'Unidad',        'cantidad' => 2],
            ['codigo' => 'clorhexidina_yodo',      'nombre' => 'Clorhexidina o yodopovidona (jabón quirúrgico)', 'unidad' => 'Galón', 'cantidad' => 1],
            ['codigo' => 'solucion_salina',        'nombre' => 'Solución salina 250 cc ó 500 cc',     'unidad' => 'Unidad',        'cantidad' => 5],
            ['codigo' => 'guantes_latex',          'nombre' => 'Guantes de látex para examen',        'unidad' => 'Caja x 100',    'cantidad' => 1],
            ['codigo' => 'termometro_digital',     'nombre' => 'Termómetro digital',                  'unidad' => 'Unidad',        'cantidad' => 1],
            ['codigo' => 'alcohol_antiseptico',    'nombre' => 'Alcohol antiséptico frasco x 275 ml', 'unidad' => 'Unidad',        'cantidad' => 1],
            ['codigo' => 'tijeras',                'nombre' => 'Tijeras',                             'unidad' => 'Unidad',        'cantidad' => 1],
            ['codigo' => 'linterna',               'nombre' => 'Linterna',                            'unidad' => 'Unidad',        'cantidad' => 1],
            ['codigo' => 'pilas_repuesto',         'nombre' => 'Pilas de repuesto',                   'unidad' => 'Par',           'cantidad' => 4],
            ['codigo' => 'tabla_espinal',          'nombre' => 'Tabla espinal larga',                 'unidad' => 'Unidad',        'cantidad' => 1],
            ['codigo' => 'collar_cervical_adulto', 'nombre' => 'Collar cervical adulto',              'unidad' => 'Unidad',        'cantidad' => 2],
            ['codigo' => 'collar_cervical_nino',   'nombre' => 'Collar cervical niño',                'unidad' => 'Unidad',        'cantidad' => 2],
            ['codigo' => 'inmov_sup_adulto',       'nombre' => 'Inmovilizadores/férula miembros superiores (adulto)', 'unidad' => 'Unidad', 'cantidad' => 1],
            ['codigo' => 'inmov_inf_adulto',       'nombre' => 'Inmovilizadores/férula miembros inferiores (adulto)', 'unidad' => 'Unidad', 'cantidad' => 1],
            ['codigo' => 'inmov_sup_nino',         'nombre' => 'Inmovilizadores/férula miembros superiores (niño)',   'unidad' => 'Unidad', 'cantidad' => 1],
            ['codigo' => 'inmov_inf_nino',         'nombre' => 'Inmovilizadores/férula miembros inferiores (niño)',   'unidad' => 'Unidad', 'cantidad' => 1],
            ['codigo' => 'vasos_desechables',      'nombre' => 'Vasos desechables',                   'unidad' => 'Paquete x 25',  'cantidad' => 1],
            ['codigo' => 'tensiometro',            'nombre' => 'Tensiómetro',                         'unidad' => 'Unidad',        'cantidad' => 1],
            ['codigo' => 'fonendoscopio',          'nombre' => 'Fonendoscopio',                       'unidad' => 'Unidad',        'cantidad' => 1],
            ['codigo' => 'acetaminofen_500',       'nombre' => 'Acetaminofén tabletas por 500 mg',    'unidad' => 'Sobre x 10',    'cantidad' => 2],
            ['codigo' => 'hidroxido_aluminio',     'nombre' => 'Hidróxido de aluminio tabletas',      'unidad' => 'Sobre x 10',    'cantidad' => 1],
            ['codigo' => 'asa_100',                'nombre' => 'ASA tabletas por 100 mg',             'unidad' => 'Sobre x 10',    'cantidad' => 1],
            ['codigo' => 'barrera_rcp',            'nombre' => 'Elemento de barrera o máscara para RCP','unidad' => 'Unidad',      'cantidad' => 2],
        ];
    }

    private static function tipoC(): array
    {
        // Tipo C: mismo listado que B con cantidades mayores (aproximadamente duplicadas según Anexo III).
        return [
            ['codigo' => 'gasas_limpias',          'nombre' => 'Gasas limpias',                       'unidad' => 'Paquete x 100', 'cantidad' => 2],
            ['codigo' => 'gasas_esteriles',        'nombre' => 'Gasas estériles',                     'unidad' => 'Paquete x 3',   'cantidad' => 20],
            ['codigo' => 'aposito_compresas',      'nombre' => 'Apósito o compresas no estériles',    'unidad' => 'Unidad',        'cantidad' => 8],
            ['codigo' => 'esparadrapo_tela_4',     'nombre' => 'Esparadrapo de tela rollo 4"',        'unidad' => 'Unidad',        'cantidad' => 4],
            ['codigo' => 'bajalenguas',            'nombre' => 'Bajalenguas',                         'unidad' => 'Paquete x 20',  'cantidad' => 4],
            ['codigo' => 'venda_elastica_2x5',     'nombre' => 'Venda elástica 2 x 5 yardas',         'unidad' => 'Unidad',        'cantidad' => 4],
            ['codigo' => 'venda_elastica_3x5',     'nombre' => 'Venda elástica 3 x 5 yardas',         'unidad' => 'Unidad',        'cantidad' => 4],
            ['codigo' => 'venda_elastica_5x5',     'nombre' => 'Venda elástica 5 x 5 yardas',         'unidad' => 'Unidad',        'cantidad' => 4],
            ['codigo' => 'venda_algodon_3x5',      'nombre' => 'Venda de algodón 3 x 5 yardas',       'unidad' => 'Unidad',        'cantidad' => 4],
            ['codigo' => 'venda_algodon_5x5',      'nombre' => 'Venda de algodón 5 x 5 yardas',       'unidad' => 'Unidad',        'cantidad' => 4],
            ['codigo' => 'clorhexidina_yodo',      'nombre' => 'Clorhexidina o yodopovidona (jabón quirúrgico)', 'unidad' => 'Galón', 'cantidad' => 2],
            ['codigo' => 'solucion_salina',        'nombre' => 'Solución salina 250 cc ó 500 cc',     'unidad' => 'Unidad',        'cantidad' => 10],
            ['codigo' => 'guantes_latex',          'nombre' => 'Guantes de látex para examen',        'unidad' => 'Caja x 100',    'cantidad' => 2],
            ['codigo' => 'termometro_digital',     'nombre' => 'Termómetro digital',                  'unidad' => 'Unidad',        'cantidad' => 2],
            ['codigo' => 'alcohol_antiseptico',    'nombre' => 'Alcohol antiséptico frasco x 275 ml', 'unidad' => 'Unidad',        'cantidad' => 2],
            ['codigo' => 'tijeras',                'nombre' => 'Tijeras',                             'unidad' => 'Unidad',        'cantidad' => 2],
            ['codigo' => 'linterna',               'nombre' => 'Linterna',                            'unidad' => 'Unidad',        'cantidad' => 4],
            ['codigo' => 'pilas_repuesto',         'nombre' => 'Pilas de repuesto',                   'unidad' => 'Par',           'cantidad' => 4],
            ['codigo' => 'tabla_espinal',          'nombre' => 'Tabla espinal larga',                 'unidad' => 'Unidad',        'cantidad' => 1],
            ['codigo' => 'collar_cervical_adulto', 'nombre' => 'Collar cervical adulto',              'unidad' => 'Unidad',        'cantidad' => 4],
            ['codigo' => 'collar_cervical_nino',   'nombre' => 'Collar cervical niño',                'unidad' => 'Unidad',        'cantidad' => 4],
            ['codigo' => 'inmov_sup_adulto',       'nombre' => 'Inmovilizadores/férula miembros superiores (adulto)', 'unidad' => 'Unidad', 'cantidad' => 2],
            ['codigo' => 'inmov_inf_adulto',       'nombre' => 'Inmovilizadores/férula miembros inferiores (adulto)', 'unidad' => 'Unidad', 'cantidad' => 2],
            ['codigo' => 'inmov_sup_nino',         'nombre' => 'Inmovilizadores/férula miembros superiores (niño)',   'unidad' => 'Unidad', 'cantidad' => 2],
            ['codigo' => 'inmov_inf_nino',         'nombre' => 'Inmovilizadores/férula miembros inferiores (niño)',   'unidad' => 'Unidad', 'cantidad' => 2],
            ['codigo' => 'vasos_desechables',      'nombre' => 'Vasos desechables',                   'unidad' => 'Paquete x 25',  'cantidad' => 2],
            ['codigo' => 'tensiometro',            'nombre' => 'Tensiómetro',                         'unidad' => 'Unidad',        'cantidad' => 2],
            ['codigo' => 'fonendoscopio',          'nombre' => 'Fonendoscopio',                       'unidad' => 'Unidad',        'cantidad' => 2],
            ['codigo' => 'acetaminofen_500',       'nombre' => 'Acetaminofén tabletas por 500 mg',    'unidad' => 'Sobre x 10',    'cantidad' => 4],
            ['codigo' => 'hidroxido_aluminio',     'nombre' => 'Hidróxido de aluminio tabletas',      'unidad' => 'Sobre x 10',    'cantidad' => 4],
            ['codigo' => 'asa_100',                'nombre' => 'ASA tabletas por 100 mg',             'unidad' => 'Sobre x 10',    'cantidad' => 2],
            ['codigo' => 'barrera_rcp',            'nombre' => 'Elemento de barrera o máscara para RCP','unidad' => 'Unidad',      'cantidad' => 2],
        ];
    }
}
