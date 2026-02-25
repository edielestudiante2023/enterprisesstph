<?php

namespace App\Models;

use CodeIgniter\Model;

class HvBrigadistaModel extends Model
{
    protected $table = 'tbl_hv_brigadista';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente',
        'fecha_registro', 'fecha_inscripcion',
        'foto_brigadista', 'nombre_completo', 'documento_identidad',
        'f_nacimiento', 'email', 'telefono', 'direccion_residencia',
        'edad', 'eps', 'peso', 'estatura', 'rh',
        'estudios_1', 'lugar_estudio_1', 'anio_estudio_1',
        'estudios_2', 'lugar_estudio_2', 'anio_estudio_2',
        'estudios_3', 'lugar_estudio_3', 'anio_estudio_3',
        'enfermedades_importantes', 'medicamentos',
        'cardiaca', 'pechoactividad', 'dolorpecho', 'conciencia',
        'huesos', 'medicamentos_bool', 'actividadfisica', 'convulsiones',
        'vertigo', 'oidos', 'lugarescerrados', 'miedoalturas',
        'haceejercicio', 'miedo_ver_sangre',
        'restricciones_medicas', 'deporte_semana',
        'firma', 'estado', 'ruta_pdf',
    ];
    protected $useTimestamps = true;

    /**
     * HV por consultor (derivado via tbl_clientes.id_consultor)
     */
    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_hv_brigadista.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_hv_brigadista.id_cliente', 'left')
            ->where('tbl_clientes.id_consultor', $idConsultor)
            ->orderBy('tbl_hv_brigadista.created_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_hv_brigadista.estado', $estado);
        }

        return $builder->findAll();
    }

    /**
     * Borradores por consultor (para dashboard pendientes)
     */
    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_hv_brigadista.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_hv_brigadista.id_cliente', 'left')
            ->where('tbl_clientes.id_consultor', $idConsultor)
            ->where('tbl_hv_brigadista.estado', 'borrador')
            ->orderBy('tbl_hv_brigadista.updated_at', 'DESC')
            ->findAll();
    }

    /**
     * HV por cliente (para portal cliente)
     */
    public function getByCliente(int $idCliente)
    {
        return $this->where('id_cliente', $idCliente)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * HV completas por cliente
     */
    public function getCompletosByCliente(int $idCliente)
    {
        return $this->where('id_cliente', $idCliente)
            ->where('estado', 'completo')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
