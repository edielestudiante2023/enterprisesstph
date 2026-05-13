<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ClientModel;
use App\Models\EncuestaCaracterizacionModel;
use App\Models\EncuestaCaracterizacionRespuestaModel;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class EncuestaCaracterizacionController extends BaseController
{
    protected EncuestaCaracterizacionModel $encuestaModel;
    protected EncuestaCaracterizacionRespuestaModel $respuestaModel;

    private const QUESTIONS = [
        'nombre_administrador' => 'Nombre del administrador',
        'horarios_administracion' => 'Horarios de administracion',
        'anio_construccion' => 'Ano de construccion',
        'estructura_sismo_resistente' => 'La estructura es sismo resistente (Si / No / Detalle)',
        'total_unidades_habitacionales' => 'Numero total de unidades habitacionales',
        'numero_torres_casas' => 'Numero de torres o casas, segun corresponda a su copropiedad',
        'cantidad_locales_comerciales' => 'Cantidad de locales comerciales',
        'tiene_oficina_administracion' => 'Tiene oficina de administracion',
        'cantidad_salones_comunales' => 'Cantidad de salones comunales',
        'parqueaderos_carros_residentes' => 'Parqueaderos para carros de residentes',
        'parqueaderos_carros_visitantes' => 'Parqueaderos para carros de visitantes',
        'parqueaderos_motos_residentes' => 'Parqueaderos para motos de residentes',
        'parqueaderos_motos_visitantes' => 'Parqueaderos para motos de visitantes',
        'propietarios_parqueadero_privado' => 'Hay propietarios con parqueadero privado',
        'proveedor_vigilancia' => 'Proveedor de vigilancia con NIT',
        'cantidad_personal_vigilancia' => 'Cantidad de personal de vigilancia',
        'proveedor_aseo' => 'Proveedor de aseo con NIT',
        'cantidad_personal_aseo' => 'Cantidad de personal de aseo',
        'otros_proveedores' => 'Otros proveedores de relevancia',
        'empresa_control_roedores' => 'Empresa con la cual realiza el control de roedores actualmente (Empresa + NIT)',
        'registro_visitantes_descripcion' => 'Descripcion de la forma en que se registran los visitantes',
        'registro_visitantes_emergencia' => 'El registro de visitantes permite saber cuantas personas hay en el conjunto en caso de emergencia',
        'cuenta_planta_electrica' => 'Cuenta con planta electrica (Detalle)',
        'cantidad_tanques' => 'Cantidad de tanques',
        'capacidad_individual_tanque' => 'Capacidad individual de cada tanque',
        'capacidad_total_almacenamiento' => 'Capacidad total de almacenamiento',
        'cuarto_basuras_abierto' => 'El cuarto de basuras esta abierto todos los dias? Si la respuesta es no, que dias esta cerrado y cuales son los horarios',
        'cuenta_megafono' => 'Cuenta el conjunto con megafono',
        'equipos_telefono_fijo' => 'Cantidad de equipos de comunicacion: Telefono fijo',
        'equipos_telefonia_celular' => 'Cantidad de equipos de comunicacion: Telefonia celular',
        'equipos_radio_onda_corta' => 'Cantidad de equipos de comunicacion: Radio de onda corta',
        'equipos_software_citofonia' => 'Cantidad de equipos de comunicacion: Software de citofonia',
        'equipos_sistemas_megafonia' => 'Cantidad de equipos de comunicacion: Sistemas de megafonia',
        'equipos_cctv_audio' => 'Cantidad de equipos de comunicacion: CCTV con audio',
        'equipos_alarma_comunicacion' => 'Cantidad de equipos de comunicacion: Alarma con comunicacion',
        'equipos_voip' => 'Cantidad de equipos de comunicacion: Voz sobre IP - VOIP',
    ];

    public function __construct()
    {
        $this->encuestaModel  = new EncuestaCaracterizacionModel();
        $this->respuestaModel = new EncuestaCaracterizacionRespuestaModel();
    }

    public static function preguntas(): array
    {
        return self::QUESTIONS;
    }

    public function list()
    {
        $encuestas = $this->encuestaModel
            ->select('tbl_encuesta_caracterizacion.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_encuesta_caracterizacion.id_cliente', 'left')
            ->orderBy('tbl_encuesta_caracterizacion.created_at', 'DESC')
            ->findAll();

        foreach ($encuestas as &$encuesta) {
            $encuesta['total_respuestas'] = $this->respuestaModel
                ->where('id_encuesta', $encuesta['id'])
                ->countAllResults();
        }
        unset($encuesta);

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/encuesta-caracterizacion/list', [
                'encuestas' => $encuestas,
            ]),
            'title' => 'Items Nucleares SG-SST',
        ]);
    }

    public function create()
    {
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/encuesta-caracterizacion/form', [
                'encuesta' => null,
            ]),
            'title' => 'Nuevo Items Nucleares SG-SST',
        ]);
    }

    public function store()
    {
        $idCliente = (int) ($this->request->getPost('id_cliente') ?? 0);
        if (!$idCliente) {
            return redirect()->back()->withInput()->with('error', 'Debe seleccionar un cliente.');
        }

        $titulo = trim((string) $this->request->getPost('titulo'));
        $this->encuestaModel->insert([
            'id_cliente' => $idCliente,
            'titulo'     => $titulo !== '' ? $titulo : 'Items Nucleares SG-SST',
            'token'      => bin2hex(random_bytes(24)),
            'estado'     => 'activa',
        ]);

        return redirect()->to('/inspecciones/encuesta-caracterizacion/view/' . $this->encuestaModel->getInsertID())
            ->with('msg', 'Encuesta creada. Comparte el enlace o QR para diligenciarla.');
    }

    public function view(int $id)
    {
        $encuesta = $this->encuestaModel->find($id);
        if (!$encuesta) {
            return redirect()->to('/inspecciones/encuesta-caracterizacion')->with('error', 'Encuesta no encontrada.');
        }

        $cliente    = (new ClientModel())->find($encuesta['id_cliente']);
        $respuestas = $this->respuestaModel->getByEncuesta($id);
        $url        = base_url('encuesta-caracterizacion/' . $encuesta['token']);

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/encuesta-caracterizacion/view', [
                'encuesta'   => $encuesta,
                'cliente'    => $cliente,
                'respuestas' => $respuestas,
                'preguntas'  => self::QUESTIONS,
                'url'        => $url,
                'qrBase64'   => $this->generarQrBase64($url),
            ]),
            'title' => 'Ver Items Nucleares SG-SST',
        ]);
    }

    public function delete(int $id)
    {
        $encuesta = $this->encuestaModel->find($id);
        if (!$encuesta) {
            return redirect()->to('/inspecciones/encuesta-caracterizacion')->with('error', 'Encuesta no encontrada.');
        }

        $this->respuestaModel->where('id_encuesta', $id)->delete();
        $this->encuestaModel->delete($id);

        return redirect()->to('/inspecciones/encuesta-caracterizacion')->with('msg', 'Encuesta eliminada.');
    }

    public function formPublico(string $token)
    {
        $encuesta = $this->encuestaModel->findByToken($token);
        if (!$encuesta || ($encuesta['estado'] ?? '') !== 'activa') {
            return view('inspecciones/encuesta-caracterizacion/cerrado');
        }

        return view('inspecciones/encuesta-caracterizacion/form-publico', [
            'encuesta'  => $encuesta,
            'cliente'   => (new ClientModel())->find($encuesta['id_cliente']),
            'preguntas' => self::QUESTIONS,
        ]);
    }

    public function submitPublico(string $token)
    {
        $encuesta = $this->encuestaModel->findByToken($token);
        if (!$encuesta || ($encuesta['estado'] ?? '') !== 'activa') {
            return redirect()->to('/encuesta-caracterizacion/' . $token);
        }

        $payload = ['id_encuesta' => (int) $encuesta['id']];
        foreach (array_keys(self::QUESTIONS) as $field) {
            $value = trim((string) $this->request->getPost($field));
            if ($value === '') {
                return redirect()->to('/encuesta-caracterizacion/' . $token)
                    ->withInput()
                    ->with('error', 'Todos los campos son obligatorios.');
            }
            $payload[$field] = mb_substr($value, 0, 255);
        }
        $userAgent = $this->request->getUserAgent();
        $payload['ip_registro'] = mb_substr((string) $this->request->getIPAddress(), 0, 45);
        $payload['user_agent']  = mb_substr(method_exists($userAgent, 'getAgentString') ? $userAgent->getAgentString() : (string) $userAgent, 0, 255);

        $this->respuestaModel->insert($payload);

        return redirect()->to('/encuesta-caracterizacion/' . $token . '/gracias');
    }

    public function gracias(string $token)
    {
        $encuesta = $this->encuestaModel->findByToken($token);

        return view('inspecciones/encuesta-caracterizacion/gracias', [
            'encuesta' => $encuesta,
        ]);
    }

    private function generarQrBase64(string $url): string
    {
        try {
            $options = new QROptions;
            $options->outputType    = QROutputInterface::GDIMAGE_PNG;
            $options->eccLevel      = EccLevel::H;
            $options->scale         = 10;
            $options->imageBase64   = true;
            $options->quietzoneSize = 2;

            return (new QRCode($options))->render($url);
        } catch (\Throwable $e) {
            log_message('error', 'QR encuesta caracterizacion failed: ' . $e->getMessage());
            return '';
        }
    }
}
