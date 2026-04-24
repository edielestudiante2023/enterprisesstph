<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionGimnasioModel extends Model
{
    protected $table = 'tbl_inspeccion_gimnasio';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'fecha_inspeccion',
        'aforo_maximo', 'horario_operacion', 'area_aproximada_m2',
        'aforo_senalizado', 'reglamento_visible', 'piso_antideslizante',
        'ventilacion_adecuada', 'iluminacion_adecuada',
        'extintor_vigente_senalizado', 'botiquin_visible_dotado',
        'plano_evacuacion_visible', 'espejos_seguros', 'punto_hidratacion',
        'vestier_ordenado', 'salida_emergencia_libre', 'pulsador_emergencia_funcional',
        'introduccion', 'alcance', 'justificacion',
        'observaciones_generales', 'recomendaciones_generales',
        'marco_normativo', 'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_gimnasio.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_gimnasio.id_cliente', 'left')
            ->where('tbl_inspeccion_gimnasio.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_gimnasio.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_gimnasio.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->getByConsultor($idConsultor, 'borrador');
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_gimnasio.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_gimnasio.id_consultor', 'left')
            ->where('tbl_inspeccion_gimnasio.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_gimnasio.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
