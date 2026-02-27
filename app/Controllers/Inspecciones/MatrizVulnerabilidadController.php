<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\MatrizVulnerabilidadModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class MatrizVulnerabilidadController extends BaseController
{
    protected MatrizVulnerabilidadModel $matrizModel;

    /**
     * 25 criterios de evaluacion con opciones A/B/C.
     * Terminologia adaptada a copropiedad/propiedad horizontal.
     * Puntaje: A=1.0, B=0.5, C=0.0
     */
    public const CRITERIOS = [
        'c1_plan_evacuacion' => [
            'numero' => 1,
            'titulo' => 'El plan de evacuacion',
            'opciones' => [
                'a' => 'Se ha determinado previamente por parte del personal de la copropiedad los aspectos basicos a poner en practica en caso de una evacuacion.',
                'b' => 'Solo algunos residentes o personal conocen sobre normas de evacuacion o han tenido en cuenta aspectos al respecto.',
                'c' => 'Ningun residente o personal de la copropiedad conoce sobre medidas de evacuacion y no se han desarrollado hasta el momento estrategias o planes al respecto.',
            ],
        ],
        'c2_alarma_evacuacion' => [
            'numero' => 2,
            'titulo' => 'Alarma para evacuacion',
            'opciones' => [
                'a' => 'Esta instalada y es funcional.',
                'b' => 'Es funcional solo en un sector. Bajo ciertas condiciones.',
                'c' => 'Es solo un proyecto que se menciona en algunas ocasiones.',
            ],
        ],
        'c3_ruta_evacuacion' => [
            'numero' => 3,
            'titulo' => 'Ruta de evacuacion',
            'opciones' => [
                'a' => 'Existe una ruta exclusiva de evacuacion, iluminada, senalizada, con pasamanos.',
                'b' => 'Presenta deficiencia en alguno de los aspectos anteriores.',
                'c' => 'No hay ruta exclusiva de evacuacion.',
            ],
        ],
        'c4_visitantes_rutas' => [
            'numero' => 4,
            'titulo' => 'Los visitantes del conjunto conocen las rutas de evacuacion',
            'opciones' => [
                'a' => 'Facil y rapidamente gracias a la senalizacion visible desde todos los angulos.',
                'b' => 'Dificilmente por la poca senalizacion u orientacion al respecto.',
                'c' => 'No las reconocerian facilmente.',
            ],
        ],
        'c5_puntos_reunion' => [
            'numero' => 5,
            'titulo' => 'Los puntos de reunion en una evacuacion',
            'opciones' => [
                'a' => 'Se han establecido claramente y los conocen todos los ocupantes de la copropiedad.',
                'b' => 'Existen varios sitios posibles pero ninguno se ha delimitado con claridad y nadie sabria hacia donde evacuar exactamente.',
                'c' => 'No existen puntos optimos donde evacuar.',
            ],
        ],
        'c6_puntos_reunion_2' => [
            'numero' => 6,
            'titulo' => 'Los puntos de reunion en una evacuacion (Parte 2)',
            'opciones' => [
                'a' => 'Son amplios y seguros.',
                'b' => 'Son amplios, pero con algunos riesgos.',
                'c' => 'Son realmente pequenos para el numero de personas a evacuar y realmente peligrosos.',
            ],
        ],
        'c7_senalizacion_evacuacion' => [
            'numero' => 7,
            'titulo' => 'La senalizacion para evacuacion',
            'opciones' => [
                'a' => 'Se visualiza e identifica plenamente en todas las areas de la copropiedad.',
                'b' => 'Esta muy oculta y apenas se observa en algunos sitios.',
                'c' => 'No existen flechas o croquis de evacuacion en ninguna parte visible.',
            ],
        ],
        'c8_rutas_evacuacion' => [
            'numero' => 8,
            'titulo' => 'Las rutas de evacuacion son',
            'opciones' => [
                'a' => 'Antideslizantes y seguras en todo recorrido.',
                'b' => 'Con obstaculos y tramos resbalosos.',
                'c' => 'Altamente resbalosos, utilizados como bodegas o intransitables en algunos tramos.',
            ],
        ],
        'c9_ruta_principal' => [
            'numero' => 9,
            'titulo' => 'La ruta principal de evacuacion',
            'opciones' => [
                'a' => 'Tiene ruta alterna optima y conocida.',
                'b' => 'Tiene una ruta alterna pero deficiente.',
                'c' => 'No posee ninguna ruta alterna o no se conoce.',
            ],
        ],
        'c10_senal_alarma' => [
            'numero' => 10,
            'titulo' => 'La senal de alarma',
            'opciones' => [
                'a' => 'Se encuentra o se ve claramente en todos los sitios.',
                'b' => 'Algunas veces no se escucha ni se ve claramente. Los ocupantes no la conocen.',
                'c' => 'Usualmente no se escucha, ni se ve.',
            ],
        ],
        'c11_sistema_deteccion' => [
            'numero' => 11,
            'titulo' => 'Sistema de deteccion',
            'opciones' => [
                'a' => 'La copropiedad posee sistema de deteccion de incendio revisado en el ultimo trimestre en todas las areas.',
                'b' => 'Solo existen algunos detectores sin revision y no en todas las areas.',
                'c' => 'No existe ningun tipo de detector.',
            ],
        ],
        'c12_iluminacion' => [
            'numero' => 12,
            'titulo' => 'El sistema de iluminacion',
            'opciones' => [
                'a' => 'Es optimo de dia y noche (siempre se ve claramente, aun de noche).',
                'b' => 'Es optimo solo en el dia (en la noche no se ve con claridad).',
                'c' => 'Deficiente dia y noche.',
            ],
        ],
        'c13_iluminacion_emergencia' => [
            'numero' => 13,
            'titulo' => 'El sistema de iluminacion de emergencia',
            'opciones' => [
                'a' => 'Es de encendido automatico en caso de corte de energia.',
                'b' => 'Es de encendido manual en caso de corte de energia.',
                'c' => 'No existe.',
            ],
        ],
        'c14_sistema_contra_incendio' => [
            'numero' => 14,
            'titulo' => 'El sistema contra incendio',
            'opciones' => [
                'a' => 'Es funcional.',
                'b' => 'Funciona parcialmente.',
                'c' => 'No existe o no funciona.',
            ],
        ],
        'c15_extintores' => [
            'numero' => 15,
            'titulo' => 'Los extintores para incendio',
            'opciones' => [
                'a' => 'Estan ubicados en las areas criticas y son funcionales.',
                'b' => 'Existen, pero no en numero suficiente.',
                'c' => 'No existen o no funcionan.',
            ],
        ],
        'c16_divulgacion_plan' => [
            'numero' => 16,
            'titulo' => 'Divulgacion del plan de emergencia a los residentes y personal',
            'opciones' => [
                'a' => 'Se ha desarrollado minimo una por semestre.',
                'b' => 'Esporadicamente se ha divulgado para algunas areas.',
                'c' => 'No se ha divulgado.',
            ],
        ],
        'c17_coordinador_plan' => [
            'numero' => 17,
            'titulo' => 'Coordinador del plan de emergencia',
            'opciones' => [
                'a' => 'Existe y esta capacitado.',
                'b' => 'Existe pero no esta capacitado.',
                'c' => 'No existe.',
            ],
        ],
        'c18_brigada_emergencia' => [
            'numero' => 18,
            'titulo' => 'La brigada de emergencia',
            'opciones' => [
                'a' => 'Existe y esta capacitada.',
                'b' => 'Existe y no esta capacitada.',
                'c' => 'No existe.',
            ],
        ],
        'c19_simulacros' => [
            'numero' => 19,
            'titulo' => 'Se han realizado simulacros',
            'opciones' => [
                'a' => 'Un simulacro en el ultimo ano.',
                'b' => 'Un simulacro en los ultimos dos anos.',
                'c' => 'Ningun simulacro.',
            ],
        ],
        'c20_entidades_socorro' => [
            'numero' => 20,
            'titulo' => 'Entidades de socorro externas',
            'opciones' => [
                'a' => 'Conocen y participan activamente en el plan de emergencia de la copropiedad.',
                'b' => 'Estan identificadas las entidades de socorro pero no conocen el plan de emergencia de la copropiedad.',
                'c' => 'No se tienen en cuenta.',
            ],
        ],
        'c21_ocupantes' => [
            'numero' => 21,
            'titulo' => 'Los ocupantes de la copropiedad son',
            'opciones' => [
                'a' => 'Siempre los mismos con muy pocos visitantes.',
                'b' => 'Con un 10 a 20% de visitantes nuevos cada dia.',
                'c' => 'El 90% de los ocupantes son visitantes.',
            ],
        ],
        'c22_plano_evacuacion' => [
            'numero' => 22,
            'titulo' => 'En la entrada de la copropiedad o en cada piso',
            'opciones' => [
                'a' => 'Existe y es visible un plano de evacuacion en cada piso.',
                'b' => 'No existe un plano de evacuacion en cada piso pero alguien daria informacion.',
                'c' => 'No existe un plano de evacuacion y nadie esta responsabilizado de dar informacion al respecto.',
            ],
        ],
        'c23_rutas_circulacion' => [
            'numero' => 23,
            'titulo' => 'Las rutas de circulacion',
            'opciones' => [
                'a' => 'En general las rutas de acceso y circulacion de los residentes y visitantes son amplias y seguras.',
                'b' => 'En algun punto de las rutas no se circula con facilidad por falta de espacio u obstaculos al paso.',
                'c' => 'En general las rutas y areas de circulacion son congestionadas y de dificil uso.',
            ],
        ],
        'c24_puertas_salida' => [
            'numero' => 24,
            'titulo' => 'Las puertas de salida de la copropiedad',
            'opciones' => [
                'a' => 'Las puertas cumplen con las medidas minimas reglamentarias y de uso de cerraduras de seguridad.',
                'b' => 'Solo algunas puertas permiten una salida rapida y poseen cerraduras de seguridad.',
                'c' => 'Ninguna puerta es lo suficientemente amplia o brinda garantias para salida segura.',
            ],
        ],
        'c25_estructura_construccion' => [
            'numero' => 25,
            'titulo' => 'Estructura y tipo de construccion',
            'opciones' => [
                'a' => 'La estructura de la copropiedad se soporta en estructuras de concreto y no presenta ningun deterioro en paredes, columnas, techos o aditamentos internos.',
                'b' => 'Presenta deterioro observable en paredes y techos que hagan pensar en danos estructurales.',
                'c' => 'La estructura no posee cimentacion ni soportes de concreto y presenta deterioros estructurales observables en progreso durante los ultimos 6 meses.',
            ],
        ],
    ];

    /**
     * Puntaje por opcion.
     */
    public const PUNTAJES = [
        'a' => 1.0,
        'b' => 0.5,
        'c' => 0.0,
    ];

    /**
     * Clasificacion por rangos de puntaje.
     */
    public const CLASIFICACION = [
        ['min' => 91, 'max' => 100, 'label' => 'Vulnerabilidad minima', 'color' => '#d4edda', 'text_color' => '#155724',
         'desc' => 'La vulnerabilidad es minima y el plan presenta un estado optimo de aplicacion.'],
        ['min' => 71, 'max' => 90,  'label' => 'Baja vulnerabilidad',   'color' => '#cce5ff', 'text_color' => '#004085',
         'desc' => 'La copropiedad presenta una baja vulnerabilidad y un plan para emergencia apenas funcional que debe optimizarse.'],
        ['min' => 51, 'max' => 70,  'label' => 'Vulnerabilidad media-alta', 'color' => '#fff3cd', 'text_color' => '#856404',
         'desc' => 'La copropiedad presenta una vulnerabilidad media-alta y un plan para emergencia incompleto, que solo podria ser activado parcialmente en caso de emergencia.'],
        ['min' => 0,  'max' => 50,  'label' => 'Alta vulnerabilidad',   'color' => '#f8d7da', 'text_color' => '#721c24',
         'desc' => 'La copropiedad presenta una alta vulnerabilidad funcional. Se deben revisar todos los aspectos que puedan estar representando riesgo para las personas que permanecen en la copropiedad en un momento de emergencia.'],
    ];

    public function __construct()
    {
        $this->matrizModel = new MatrizVulnerabilidadModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->matrizModel
                ->select('tbl_matriz_vulnerabilidad.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_matriz_vulnerabilidad.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_matriz_vulnerabilidad.id_consultor', 'left')
                ->orderBy('tbl_matriz_vulnerabilidad.fecha_inspeccion', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->matrizModel->getByConsultor($userId);
        }

        $data = [
            'title'        => 'Matriz de Vulnerabilidad',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/matriz-vulnerabilidad/list', $data),
            'title'   => 'Matriz Vuln.',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Matriz de Vulnerabilidad',
            'inspeccion' => null,
            'idCliente'  => $idCliente,
            'criterios'  => self::CRITERIOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/matriz-vulnerabilidad/form', $data),
            'title'   => 'Nueva Matriz Vuln.',
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');

        if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_inspeccion' => 'required|valid_date'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->getInspeccionPostData();
        $data['id_consultor'] = $userId;
        $data['estado'] = 'borrador';

        $this->matrizModel->insert($data);
        $idInspeccion = $this->matrizModel->getInsertID();

        return redirect()->to('/inspecciones/matriz-vulnerabilidad/edit/' . $idInspeccion)
            ->with('msg', 'Matriz guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->matrizModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/matriz-vulnerabilidad')->with('error', 'Matriz no encontrada');
        }

        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/matriz-vulnerabilidad/view/' . $id);
        }

        $data = [
            'title'      => 'Editar Matriz de Vulnerabilidad',
            'inspeccion' => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
            'criterios'  => self::CRITERIOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/matriz-vulnerabilidad/form', $data),
            'title'   => 'Editar Matriz Vuln.',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->matrizModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/matriz-vulnerabilidad')->with('error', 'No se puede editar');
        }

        $data = $this->getInspeccionPostData();

        $this->matrizModel->update($id, $data);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        return redirect()->to('/inspecciones/matriz-vulnerabilidad/edit/' . $id)
            ->with('msg', 'Matriz actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->matrizModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/matriz-vulnerabilidad')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $puntaje = $this->calcularPuntaje($inspeccion);
        $clasificacion = $this->getClasificacion($puntaje);

        $data = [
            'title'         => 'Ver Matriz de Vulnerabilidad',
            'inspeccion'    => $inspeccion,
            'cliente'       => $clientModel->find($inspeccion['id_cliente']),
            'consultor'     => $consultantModel->find($inspeccion['id_consultor']),
            'criterios'     => self::CRITERIOS,
            'puntaje'       => $puntaje,
            'clasificacion' => $clasificacion,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/matriz-vulnerabilidad/view', $data),
            'title'   => 'Ver Matriz Vuln.',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->matrizModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/matriz-vulnerabilidad')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->matrizModel->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->matrizModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to('/inspecciones/matriz-vulnerabilidad/view/' . $id)
            ->with('msg', 'Matriz finalizada y PDF generado');
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->matrizModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/matriz-vulnerabilidad')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="matriz_vulnerabilidad_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->matrizModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/matriz-vulnerabilidad')->with('error', 'No encontrada');
        }
        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/matriz-vulnerabilidad')->with('error', 'No se puede eliminar una matriz completa');
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->matrizModel->delete($id);

        return redirect()->to('/inspecciones/matriz-vulnerabilidad')->with('msg', 'Matriz eliminada');
    }

    // ===== METODOS PRIVADOS =====

        public function regenerarPdf($id)
    {
        $inspeccion = $this->matrizModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/matriz-vulnerabilidad')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->matrizModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->matrizModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/matriz-vulnerabilidad/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    private function getInspeccionPostData(): array
    {
        $data = [
            'id_cliente'       => $this->request->getPost('id_cliente'),
            'fecha_inspeccion' => $this->request->getPost('fecha_inspeccion'),
            'observaciones'    => $this->request->getPost('observaciones'),
        ];

        $validKeys = ['a', 'b', 'c'];
        foreach (self::CRITERIOS as $key => $criterio) {
            $val = $this->request->getPost($key);
            $data[$key] = in_array($val, $validKeys) ? $val : null;
        }

        return $data;
    }

    /**
     * Calcula el puntaje total (0-100).
     * Cada criterio: a=1.0, b=0.5, c=0.0
     * Total = suma * 4 (25 criterios * 4 = 100 max)
     */
    public function calcularPuntaje(array $inspeccion): float
    {
        $suma = 0;
        $evaluados = 0;

        foreach (self::CRITERIOS as $key => $criterio) {
            $val = $inspeccion[$key] ?? null;
            if ($val && isset(self::PUNTAJES[$val])) {
                $suma += self::PUNTAJES[$val];
                $evaluados++;
            }
        }

        return $suma * 4;
    }

    /**
     * Retorna la clasificacion segun el puntaje.
     */
    public function getClasificacion(float $puntaje): array
    {
        foreach (self::CLASIFICACION as $rango) {
            if ($puntaje >= $rango['min'] && $puntaje <= $rango['max']) {
                return $rango;
            }
        }
        return self::CLASIFICACION[3]; // Alta vulnerabilidad por defecto
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->matrizModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        // Logo en base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $puntaje = $this->calcularPuntaje($inspeccion);
        $clasificacion = $this->getClasificacion($puntaje);

        $data = [
            'inspeccion'    => $inspeccion,
            'cliente'       => $cliente,
            'consultor'     => $consultor,
            'criterios'     => self::CRITERIOS,
            'puntaje'       => $puntaje,
            'clasificacion' => $clasificacion,
            'logoBase64'    => $logoBase64,
        ];

        $html = view('inspecciones/matriz-vulnerabilidad/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/matriz-vulnerabilidad/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'matriz_vulnerabilidad_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    private function uploadToReportes(array $inspeccion, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'];

        $existente = $reporteModel
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 18)
            ->like('observaciones', 'mat_vul_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'matriz_vulnerabilidad_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'MATRIZ VULNERABILIDAD - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 18,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. mat_vul_id:' . $inspeccion['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
