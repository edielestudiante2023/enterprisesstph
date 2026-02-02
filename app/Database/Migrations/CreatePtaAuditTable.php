<?php

namespace App\Database\Migrations;

/**
 * Script para crear la tabla de auditoría del Plan de Trabajo Anual (PTA)
 * Ejecutar: php spark migrate o acceder vía URL /audit-pta/setup
 */

use CodeIgniter\Database\Migration;

class CreatePtaAuditTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_audit' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_ptacliente' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'comment'    => 'ID del registro modificado en tbl_pta_cliente',
            ],
            'id_cliente' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'ID del cliente al que pertenece el registro',
            ],
            'accion' => [
                'type'       => 'ENUM',
                'constraint' => ['INSERT', 'UPDATE', 'DELETE', 'BULK_UPDATE'],
                'null'       => false,
                'comment'    => 'Tipo de acción realizada',
            ],
            'campo_modificado' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Nombre del campo que fue modificado',
            ],
            'valor_anterior' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Valor antes del cambio',
            ],
            'valor_nuevo' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Valor después del cambio',
            ],
            'id_usuario' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'comment'    => 'ID del usuario que realizó el cambio',
            ],
            'nombre_usuario' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Nombre del usuario para referencia rápida',
            ],
            'email_usuario' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Email del usuario',
            ],
            'rol_usuario' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Rol del usuario al momento del cambio',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
                'comment'    => 'Dirección IP desde donde se realizó el cambio',
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'Navegador/dispositivo del usuario',
            ],
            'metodo' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Método del controlador que realizó el cambio',
            ],
            'descripcion' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Descripción legible del cambio realizado',
            ],
            'fecha_accion' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => 'Fecha y hora del cambio',
            ],
        ]);

        $this->forge->addKey('id_audit', true);
        $this->forge->addKey('id_ptacliente', false, false, 'idx_ptacliente');
        $this->forge->addKey('id_cliente', false, false, 'idx_cliente');
        $this->forge->addKey('id_usuario', false, false, 'idx_usuario');
        $this->forge->addKey('fecha_accion', false, false, 'idx_fecha');
        $this->forge->addKey('accion', false, false, 'idx_accion');
        $this->forge->addKey('campo_modificado', false, false, 'idx_campo');

        $this->forge->createTable('tbl_pta_cliente_audit', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_pta_cliente_audit', true);
    }
}
