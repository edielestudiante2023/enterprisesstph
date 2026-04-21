<?php

namespace App\Traits;

use CodeIgniter\HTTP\ResponseInterface;
use Closure;

/**
 * Trait con utilidades transversales para los controladores de Inspecciones:
 *
 * 1. validateAutosaveMinimum(): garantiza que un autosave no cree registros
 *    con id_cliente / fecha_inspeccion vacíos. Retorna 422 si falla.
 *
 * 2. runTransactional(): envuelve un closure en transStart/transComplete.
 *    Si el closure lanza excepción o la transacción marca rollback, retorna
 *    una Response 500 con mensaje autosave-friendly. Compatible con submit
 *    manual (el redirect se construye en el caller a partir de este retorno).
 *
 * Requiere que el controlador también use AutosaveJsonTrait.
 */
trait InspeccionesTransactionalTrait
{
    protected function validateAutosaveMinimum(): ?ResponseInterface
    {
        $idCliente = $this->request->getPost('id_cliente');
        $fecha     = $this->request->getPost('fecha_inspeccion');

        if (empty($idCliente) || !is_numeric($idCliente) || empty($fecha)) {
            return $this->autosaveJsonError('Falta cliente o fecha de inspección', 422);
        }
        return null;
    }

    /**
     * Ejecuta $fn dentro de una transacción. Retorna:
     *   - ResponseInterface (error 500) si la tx falló o el closure lanzó excepción
     *   - lo que retorne $fn (true, ids, etc.) si todo ok
     *   - si $fn devolvió una ResponseInterface (p.ej. un error validado) se propaga
     *
     * Nota: las operaciones sobre disco (uploadFoto) NO son transaccionales; si
     * la tx hace rollback después de mover archivos, quedan huérfanos en FS.
     * Documentado como riesgo aceptado de fase 1.
     */
    protected function runTransactional(Closure $fn)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        try {
            $result = $fn($db);
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'runTransactional exception: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            return $this->autosaveJsonError('Error interno: ' . $e->getMessage(), 500);
        }
        $db->transComplete();

        if ($db->transStatus() === false) {
            log_message('error', 'runTransactional: transaction status FALSE (rollback ejecutado)');
            return $this->autosaveJsonError('Error al guardar — cambios revertidos', 500);
        }

        return $result;
    }
}
