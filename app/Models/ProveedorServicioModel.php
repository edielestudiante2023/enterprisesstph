<?php

namespace App\Models;

use CodeIgniter\Model;

class ProveedorServicioModel extends Model
{
    protected $table      = 'tbl_proveedor_servicio';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'tipo_servicio', 'tipo_servicio_otro', 'estado',
        'razon_social', 'nit', 'email_empresa', 'telefono_empresa',
        'nombre_responsable_sst', 'email_responsable_sst',
        'cargo_responsable_sst', 'telefono_responsable_sst',
        'id_consultor',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getTipoLabel(string $tipo, ?string $otro): string
    {
        return $tipo === 'Otro' ? ($otro ?: 'Otro') : $tipo;
    }
}
