<?php

namespace App\Models;

use CodeIgniter\Model;

class EncuestaCaracterizacionRespuestaModel extends Model
{
    protected $table      = 'tbl_encuesta_caracterizacion_respuestas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_encuesta',
        'empresa_control_roedores',
        'equipos_telefono_fijo',
        'equipos_telefonia_celular',
        'equipos_radio_onda_corta',
        'equipos_software_citofonia',
        'equipos_sistemas_megafonia',
        'equipos_cctv_audio',
        'equipos_alarma_comunicacion',
        'equipos_voip',
        'cantidad_tanques',
        'capacidad_individual_tanque',
        'capacidad_total_almacenamiento',
        'cuarto_basuras_abierto',
        'estructura_sismo_resistente',
        'anio_construccion',
        'total_unidades_habitacionales',
        'numero_torres_casas',
        'parqueaderos_carros_residentes',
        'parqueaderos_carros_visitantes',
        'parqueaderos_motos_residentes',
        'parqueaderos_motos_visitantes',
        'propietarios_parqueadero_privado',
        'cantidad_salones_comunales',
        'cantidad_locales_comerciales',
        'tiene_oficina_administracion',
        'cuenta_planta_electrica',
        'proveedor_vigilancia',
        'proveedor_aseo',
        'otros_proveedores',
        'registro_visitantes_descripcion',
        'registro_visitantes_emergencia',
        'cuenta_megafono',
        'nombre_administrador',
        'horarios_administracion',
        'cantidad_personal_aseo',
        'cantidad_personal_vigilancia',
        'ip_registro',
        'user_agent',
    ];
    protected $useTimestamps = true;

    public function getByEncuesta(int $idEncuesta): array
    {
        return $this->where('id_encuesta', $idEncuesta)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
