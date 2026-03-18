<?php

namespace App\Controllers;

use App\Models\SeguimientoAgendaModel;
use App\Models\SeguimientoHistorialModel;
use App\Models\ClientModel;
use App\Services\IADocumentacionService;

class SeguimientoAgendaController extends BaseController
{
    private SeguimientoAgendaModel   $model;
    private SeguimientoHistorialModel $historial;
    private ClientModel              $clientModel;

    public function __construct()
    {
        $this->model       = new SeguimientoAgendaModel();
        $this->historial   = new SeguimientoHistorialModel();
        $this->clientModel = new ClientModel();
    }

    public function index(): string
    {
        $seguimientos = $this->model->getAllConCliente();
        $clientes     = $this->clientModel->where('estado', 'activo')->orderBy('nombre_cliente')->findAll();

        return view('seguimiento/index', [
            'seguimientos' => $seguimientos,
            'clientes'     => $clientes,
        ]);
    }

    public function store()
    {
        $data = [
            'id_cliente'      => $this->request->getPost('id_cliente'),
            'asunto'          => $this->request->getPost('asunto'),
            'mensaje'         => $this->request->getPost('mensaje'),
            'opciones_fechas' => $this->request->getPost('opciones_fechas'),
            'consultor'       => $this->request->getPost('consultor') ?: 'Edison Cuervo',
            'cargo_consultor' => $this->request->getPost('cargo_consultor') ?: 'Consultor SST',
            'activo'          => 1,
            'detenido'        => 0,
        ];

        // Evitar duplicado activo para el mismo cliente
        $existe = $this->model->where('id_cliente', $data['id_cliente'])
            ->where('activo', 1)->where('detenido', 0)->first();

        if ($existe) {
            return $this->response->setJSON(['success' => false, 'message' => 'Este cliente ya tiene un seguimiento activo.']);
        }

        $this->model->save($data);
        return $this->response->setJSON(['success' => true, 'message' => 'Seguimiento activado correctamente.']);
    }

    public function detener($id)
    {
        $seg = $this->model->find($id);
        if (!$seg) {
            return $this->response->setJSON(['success' => false, 'message' => 'No encontrado.']);
        }

        $motivo = $this->request->getPost('motivo') ?: 'Detenido manualmente';
        $this->model->update($id, [
            'activo'           => 0,
            'detenido'         => 1,
            'motivo_detencion' => $motivo,
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Seguimiento detenido.']);
    }

    public function reactivar($id)
    {
        $this->model->update($id, [
            'activo'           => 1,
            'detenido'         => 0,
            'motivo_detencion' => null,
        ]);
        return $this->response->setJSON(['success' => true, 'message' => 'Seguimiento reactivado.']);
    }

    public function destroy($id)
    {
        $this->model->delete($id);
        return $this->response->setJSON(['success' => true]);
    }

    public function historialCliente($id)
    {
        $registros = $this->historial
            ->where('id_seguimiento', $id)
            ->orderBy('fecha_envio', 'DESC')
            ->findAll(30);

        return $this->response->setJSON(['success' => true, 'data' => $registros]);
    }

    public function generarTexto()
    {
        $nombre_cliente = trim($this->request->getPost('nombre_cliente') ?? '');
        if (empty($nombre_cliente)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Falta el nombre del cliente.']);
        }

        $hoy      = date('d/m/Y');
        $diasSem  = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
        $meses    = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        $diaSem   = $diasSem[(int)date('w')];
        $mes      = $meses[(int)date('n') - 1];
        $año      = date('Y');

        // Calcular próximas 3 opciones de fecha (próximos 3 días hábiles L-V, a las 5 PM)
        $opciones = [];
        $d = new \DateTime();
        $d->modify('+1 day');
        while (count($opciones) < 3) {
            $dow = (int)$d->format('w');
            if ($dow >= 1 && $dow <= 5) {
                $opciones[] = ucfirst($diasSem[$dow]) . ' ' . $d->format('d') . ' de ' . $meses[(int)$d->format('n') - 1] . ' – 5:00 p. m.';
            }
            $d->modify('+1 day');
        }

        $prompt = <<<PROMPT
Eres Edison Cuervo, consultor SST (Seguridad y Salud en el Trabajo) de la empresa Cycloid Talent.
Necesitas redactar un correo electrónico de seguimiento para programar una visita de asesoría SST con el cliente: {$nombre_cliente}.

El correo debe:
- Ser cordial y profesional, tono cálido pero directo
- Recordar al cliente que tiene pendiente programar la visita mensual de asesoría SST
- Mencionar que llevas varios intentos de contacto sin respuesta
- Proponer 3 opciones de fecha/hora para que el cliente elija la que más le convenga
- Terminar con un llamado a la acción claro: responder el correo o llamar directamente
- No superar los 4 párrafos
- No incluir saludo de cierre ni firma (eso se añade automáticamente)

Responde ÚNICAMENTE con un JSON válido con esta estructura exacta (sin markdown, sin explicaciones):
{
  "asunto": "...",
  "mensaje": "...",
  "opciones_fechas": ["opción 1", "opción 2", "opción 3"]
}

Las opciones de fecha a proponer son:
- {$opciones[0]}
- {$opciones[1]}
- {$opciones[2]}
PROMPT;

        try {
            $ia       = new IADocumentacionService();
            $raw      = $ia->generarContenido($prompt, 600);
            $raw      = trim($raw);
            // Limpiar posibles bloques ```json
            $raw = preg_replace('/^```json\s*/i', '', $raw);
            $raw = preg_replace('/```\s*$/', '', $raw);
            $data = json_decode(trim($raw), true);

            if (!$data || !isset($data['asunto'], $data['mensaje'])) {
                return $this->response->setJSON(['success' => false, 'message' => 'Respuesta inválida de IA.', 'raw' => $raw]);
            }

            return $this->response->setJSON(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
