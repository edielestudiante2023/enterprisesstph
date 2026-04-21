<?php

namespace App\Libraries;

/**
 * Catalogo central de tipos de inspeccion del modulo /inspecciones.
 * Consumido por MatrizInspeccionesController.
 *
 * Cada tipo declara:
 *  - slug: identificador canonico (coincide con el path de la ruta)
 *  - label: titulo visible
 *  - icon: clase FontAwesome
 *  - table: tabla maestra en BD
 *  - date_col: columna de fecha de la inspeccion
 *  - list_route: URL relativa al listado del tipo
 *  - create_route: URL relativa para crear un nuevo registro
 *  - view_route: URL relativa para ver (acepta /{id} appended)
 */
class InspeccionTypes
{
    public static function all(): array
    {
        return [
            [
                'slug'         => 'acta-visita',
                'label'        => 'Acta de Visita',
                'icon'         => 'fa-clipboard-list',
                'table'        => 'tbl_acta_visita',
                'date_col'     => 'fecha_visita',
                'list_route'   => 'inspecciones/acta-visita',
                'create_route' => 'inspecciones/acta-visita/create',
                'view_route'   => 'inspecciones/acta-visita/view',
            ],
            [
                'slug'         => 'inspeccion-locativa',
                'label'        => 'Locativa',
                'icon'         => 'fa-hard-hat',
                'table'        => 'tbl_inspeccion_locativa',
                'date_col'     => 'fecha_inspeccion',
                'list_route'   => 'inspecciones/inspeccion-locativa',
                'create_route' => 'inspecciones/inspeccion-locativa/create',
                'view_route'   => 'inspecciones/inspeccion-locativa/view',
            ],
            [
                'slug'         => 'senalizacion',
                'label'        => 'Señalización',
                'icon'         => 'fa-search',
                'table'        => 'tbl_inspeccion_senalizacion',
                'date_col'     => 'fecha_inspeccion',
                'list_route'   => 'inspecciones/senalizacion',
                'create_route' => 'inspecciones/senalizacion/create',
                'view_route'   => 'inspecciones/senalizacion/view',
            ],
            [
                'slug'         => 'extintores',
                'label'        => 'Extintores',
                'icon'         => 'fa-fire-extinguisher',
                'table'        => 'tbl_inspeccion_extintores',
                'date_col'     => 'fecha_inspeccion',
                'list_route'   => 'inspecciones/extintores',
                'create_route' => 'inspecciones/extintores/create',
                'view_route'   => 'inspecciones/extintores/view',
            ],
            [
                'slug'         => 'botiquin',
                'label'        => 'Botiquín',
                'icon'         => 'fa-first-aid',
                'table'        => 'tbl_inspeccion_botiquin',
                'date_col'     => 'fecha_inspeccion',
                'list_route'   => 'inspecciones/botiquin',
                'create_route' => 'inspecciones/botiquin/create',
                'view_route'   => 'inspecciones/botiquin/view',
            ],
            [
                'slug'         => 'gabinetes',
                'label'        => 'Gabinetes',
                'icon'         => 'fa-shower',
                'table'        => 'tbl_inspeccion_gabinetes',
                'date_col'     => 'fecha_inspeccion',
                'list_route'   => 'inspecciones/gabinetes',
                'create_route' => 'inspecciones/gabinetes/create',
                'view_route'   => 'inspecciones/gabinetes/view',
            ],
            [
                'slug'         => 'comunicaciones',
                'label'        => 'Comunicaciones',
                'icon'         => 'fa-walkie-talkie',
                'table'        => 'tbl_inspeccion_comunicaciones',
                'date_col'     => 'fecha_inspeccion',
                'list_route'   => 'inspecciones/comunicaciones',
                'create_route' => 'inspecciones/comunicaciones/create',
                'view_route'   => 'inspecciones/comunicaciones/view',
            ],
            [
                'slug'         => 'recursos-seguridad',
                'label'        => 'Recursos de Seguridad',
                'icon'         => 'fa-shield-alt',
                'table'        => 'tbl_inspeccion_recursos_seguridad',
                'date_col'     => 'fecha_inspeccion',
                'list_route'   => 'inspecciones/recursos-seguridad',
                'create_route' => 'inspecciones/recursos-seguridad/create',
                'view_route'   => 'inspecciones/recursos-seguridad/view',
            ],
            [
                'slug'         => 'productos-quimicos',
                'label'        => 'Productos Químicos',
                'icon'         => 'fa-flask',
                'table'        => 'tbl_inspeccion_productos_quimicos',
                'date_col'     => 'fecha_inspeccion',
                'list_route'   => 'inspecciones/productos-quimicos',
                'create_route' => 'inspecciones/productos-quimicos/create',
                'view_route'   => 'inspecciones/productos-quimicos/view',
            ],
            [
                'slug'         => 'brigada-simulacros',
                'label'        => 'Brigada y Simulacros',
                'icon'         => 'fa-people-carry',
                'table'        => 'tbl_inspeccion_brigada_simulacros',
                'date_col'     => 'fecha_inspeccion',
                'list_route'   => 'inspecciones/brigada-simulacros',
                'create_route' => 'inspecciones/brigada-simulacros/create',
                'view_route'   => 'inspecciones/brigada-simulacros/view',
            ],
            [
                'slug'         => 'ascensores',
                'label'        => 'Ascensores',
                'icon'         => 'fa-elevator',
                'table'        => 'tbl_inspeccion_ascensores',
                'date_col'     => 'fecha_inspeccion',
                'list_route'   => 'inspecciones/ascensores',
                'create_route' => 'inspecciones/ascensores/create',
                'view_route'   => 'inspecciones/ascensores/view',
            ],
            [
                'slug'         => 'piscinas',
                'label'        => 'Piscinas',
                'icon'         => 'fa-water-ladder',
                'table'        => 'tbl_inspeccion_piscinas',
                'date_col'     => 'fecha_inspeccion',
                'list_route'   => 'inspecciones/piscinas',
                'create_route' => 'inspecciones/piscinas/create',
                'view_route'   => 'inspecciones/piscinas/view',
            ],
            [
                'slug'         => 'piscinero',
                'label'        => 'Piscinero',
                'icon'         => 'fa-person-swimming',
                'table'        => 'tbl_inspeccion_piscinero',
                'date_col'     => 'fecha_inspeccion',
                'list_route'   => 'inspecciones/piscinero',
                'create_route' => 'inspecciones/piscinero/create',
                'view_route'   => 'inspecciones/piscinero/view',
            ],
        ];
    }

    public static function slugs(): array
    {
        return array_column(self::all(), 'slug');
    }

    public static function bySlug(string $slug): ?array
    {
        foreach (self::all() as $t) {
            if ($t['slug'] === $slug) {
                return $t;
            }
        }
        return null;
    }
}
