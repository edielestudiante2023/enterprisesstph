<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanEmergenciaModel extends Model
{
    protected $table = 'tbl_plan_emergencia';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_visita',
        // Fotos fachada y panorama
        'foto_fachada', 'foto_panorama',
        // Descripcion del inmueble
        'casas_o_apartamentos', 'sismo_resistente', 'anio_construccion',
        'numero_torres', 'numero_unidades_habitacionales', 'casas_pisos',
        'foto_torres_1', 'foto_torres_2',
        // Parqueaderos
        'parqueaderos_carros_residentes', 'parqueaderos_carros_visitantes',
        'parqueaderos_motos_residentes', 'parqueaderos_motos_visitantes',
        'hay_parqueadero_privado', 'foto_parqueaderos_carros', 'foto_parqueaderos_motos',
        // Areas comunes
        'cantidad_salones_comunales', 'cantidad_locales_comerciales',
        'tiene_oficina_admin', 'foto_oficina_admin',
        // Servicios
        'tanque_agua', 'planta_electrica',
        // Circulaciones
        'circulacion_vehicular', 'foto_circulacion_vehicular',
        'circulacion_peatonal', 'foto_circulacion_peatonal_1', 'foto_circulacion_peatonal_2',
        'salidas_emergencia', 'foto_salida_emergencia_1', 'foto_salida_emergencia_2',
        'ingresos_peatonales', 'foto_ingresos_peatonales',
        'accesos_vehiculares', 'foto_acceso_vehicular_1', 'foto_acceso_vehicular_2',
        // Conceptos consultor
        'concepto_entradas_salidas', 'hidrantes',
        // Entidades cercanas
        'cai_cercano', 'bomberos_cercanos',
        // Proveedores
        'proveedor_vigilancia', 'proveedor_aseo', 'otros_proveedores',
        // Control visitantes
        'registro_visitantes_forma', 'registro_visitantes_emergencia',
        // Comunicaciones y seguridad
        'cuenta_megafono', 'ruta_evacuacion', 'mapa_evacuacion',
        'foto_ruta_evacuacion_1', 'foto_ruta_evacuacion_2',
        'puntos_encuentro', 'foto_punto_encuentro_1', 'foto_punto_encuentro_2',
        'sistema_alarma', 'codigos_alerta', 'energia_emergencia',
        'deteccion_fuego', 'vias_transito',
        // Administracion
        'nombre_administrador', 'horarios_administracion',
        'personal_aseo', 'personal_vigilancia',
        // Telefonos emergencia
        'ciudad', 'cuadrante', 'tiene_gabinetes_hidraulico',
        // Servicios generales
        'ruta_residuos_solidos', 'empresa_aseo', 'servicios_sanitarios',
        'frecuencia_basura', 'detalle_mascotas', 'detalle_dependencias',
        // General
        'observaciones', 'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_plan_emergencia.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_plan_emergencia.id_cliente', 'left')
            ->where('tbl_plan_emergencia.id_consultor', $idConsultor)
            ->orderBy('tbl_plan_emergencia.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_plan_emergencia.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_plan_emergencia.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_plan_emergencia.id_cliente', 'left')
            ->where('tbl_plan_emergencia.id_consultor', $idConsultor)
            ->where('tbl_plan_emergencia.estado', 'borrador')
            ->orderBy('tbl_plan_emergencia.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_plan_emergencia.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_plan_emergencia.id_cliente', 'left')
            ->where('tbl_plan_emergencia.estado', 'borrador')
            ->orderBy('tbl_plan_emergencia.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_plan_emergencia.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_plan_emergencia.id_consultor', 'left')
            ->where('tbl_plan_emergencia.id_cliente', $idCliente)
            ->orderBy('tbl_plan_emergencia.fecha_visita', 'DESC')
            ->findAll();
    }
}
