<?php

namespace Config;

/**
 * Catalogo de items del "Inventario de Fotos de Choque".
 * Cada item representa una foto minima a tomar en sitio.
 * Se auto-insertan como filas en tbl_inventario_choque_items al crear un inventario.
 */
class InventarioChoqueItems
{
    /**
     * Estructura: categoria => [items]
     */
    public static function catalog(): array
    {
        return [
            'LOCATIVA' => [
                'Locativa (minimo una foto)',
            ],
            'EXTINTOR' => [
                'Extintor (minimo una foto)',
            ],
            'BOTIQUIN' => [
                'Foto del botiquin 1',
                'Foto del botiquin 2',
                'Tabla espinal',
                'Collares',
                'Inmovilizadores',
            ],
            'GABINETES' => [
                'Gabinetes (minimo una foto)',
            ],
            'RECURSOS PARA LA SEGURIDAD' => [
                'Lamparas de emergencia',
                'Antideslizantes',
                'Pasamanos',
                'Camaras CCTV',
                'Luminarias exteriores',
            ],
            'BRIGADA' => [
                'Brigada 1',
                'Brigada 2',
                'Dotacion brigada',
                'Soporte simulacro',
            ],
            'PLAN DE EMERGENCIA' => [
                'Foto fachada',
                '1 - Torres / Casas',
                '2 - Torres / Casas',
                'Parqueadero carros',
                'Parqueadero motos',
                'Oficina de administracion',
                'Zona de circulacion vehicular',
                'Zona de circulacion peatonal 1',
                'Zona de circulacion peatonal 2',
                'Salida de emergencia 1',
                'Salida de emergencia 2',
                'Ingresos peatonales',
                'Acceso vehicular 1',
                'Acceso vehicular 2',
                'Ruta de evacuacion 1',
                'Ruta de evacuacion 2',
                'Punto de encuentro 1',
                'Punto de encuentro 2',
            ],
            'CUARTO BASURAS' => [
                'Acceso',
                'Techo, pared y pisos',
                'Ventilacion',
                'Prevencion y control de incendios',
                'Drenajes',
                'Proliferacion de plagas',
                'Recipientes',
                'Reciclaje',
                'Luminarias',
                'Senalizacion',
                'Limpieza y desinfeccion',
                'Poceta',
            ],
            'KPI LIMPIEZA' => [
                'Planilla rutinas',
                'Escobas - Traperos',
                'Recogedor - Mopa',
            ],
            'KPI RESIDUOS' => [
                'Limpieza cuarto basura',
                'Reciclaje',
            ],
            'KPI PLAGAS' => [
                'Fumigacion',
                'Desratizacion',
            ],
            'KPI AGUA' => [
                'Lavado de tanques',
            ],
            'COMUNICACION' => [
                'Foto de pantallas CCTV',
                'Foto de camaras CCTV',
            ],
        ];
    }

    /**
     * Devuelve las filas listas para insercion masiva en tbl_inventario_choque_items.
     */
    public static function rowsFor(int $idInventario): array
    {
        $rows = [];
        $orden = 0;
        foreach (self::catalog() as $categoria => $items) {
            foreach ($items as $item) {
                $orden++;
                $rows[] = [
                    'id_inventario' => $idInventario,
                    'categoria'     => $categoria,
                    'item'          => $item,
                    'orden'         => $orden,
                    'marcado'       => 0,
                ];
            }
        }
        return $rows;
    }

    /**
     * Numero total de items del catalogo.
     */
    public static function total(): int
    {
        $n = 0;
        foreach (self::catalog() as $items) {
            $n += count($items);
        }
        return $n;
    }
}
