<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ProbabilidadPeligrosModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class ProbabilidadPeligrosController extends BaseController
{
    protected ProbabilidadPeligrosModel $inspeccionModel;

    /**
     * 12 peligros agrupados en 3 origenes.
     */
    public const PELIGROS = [
        'naturales' => [
            'label' => 'Naturales',
            'icon'  => 'fa-mountain',
            'items' => [
                'sismos'       => 'Sismos, Caidas de Estructuras',
                'inundaciones' => 'Inundaciones',
                'vendavales'   => 'Vendavales',
            ],
        ],
        'sociales' => [
            'label' => 'Sociales',
            'icon'  => 'fa-users',
            'items' => [
                'atentados'    => 'Atentados Terroristas',
                'asalto_hurto' => 'Asalto, Hurto',
                'vandalismo'   => 'Vandalismo',
            ],
        ],
        'tecnologicos' => [
            'label' => 'Tecnologicos',
            'icon'  => 'fa-industry',
            'items' => [
                'incendios'              => 'Incendios',
                'explosiones'            => 'Explosiones',
                'inhalacion_gases'       => 'Inhalacion de Gases',
                'falla_estructural'      => 'Falla Estructural',
                'intoxicacion_alimentos' => 'Intoxicacion por Alimentos',
                'densidad_poblacional'   => 'Densidad Poblacional',
            ],
        ],
    ];

    public const FRECUENCIAS = [
        'poco_probable' => 'Poco Probable',
        'probable'      => 'Probable',
        'muy_probable'  => 'Muy Probable',
    ];

    public function __construct()
    {
        $this->inspeccionModel = new ProbabilidadPeligrosModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->inspeccionModel
                ->select('tbl_probabilidad_peligros.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_probabilidad_peligros.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_probabilidad_peligros.id_consultor', 'left')
                ->orderBy('tbl_probabilidad_peligros.fecha_inspeccion', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->inspeccionModel->getByConsultor($userId);
        }

        $data = [
            'title'        => 'Probabilidad de Ocurrencia de Peligros',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/probabilidad-peligros/list', $data),
            'title'   => 'Prob. Peligros',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'       => 'Nueva Probabilidad de Peligros',
            'inspeccion'  => null,
            'idCliente'   => $idCliente,
            'peligros'    => self::PELIGROS,
            'frecuencias' => self::FRECUENCIAS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/probabilidad-peligros/form', $data),
            'title'   => 'Nueva Prob. Peligros',
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

        $this->inspeccionModel->insert($data);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        return redirect()->to('/inspecciones/probabilidad-peligros/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/probabilidad-peligros')->with('error', 'Inspeccion no encontrada');
        }

        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/probabilidad-peligros/view/' . $id);
        }

        $data = [
            'title'       => 'Editar Probabilidad de Peligros',
            'inspeccion'  => $inspeccion,
            'idCliente'   => $inspeccion['id_cliente'],
            'peligros'    => self::PELIGROS,
            'frecuencias' => self::FRECUENCIAS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/probabilidad-peligros/form', $data),
            'title'   => 'Editar Prob. Peligros',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/probabilidad-peligros')->with('error', 'No se puede editar');
        }

        $data = $this->getInspeccionPostData();

        $this->inspeccionModel->update($id, $data);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        return redirect()->to('/inspecciones/probabilidad-peligros/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/probabilidad-peligros')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'       => 'Ver Probabilidad de Peligros',
            'inspeccion'  => $inspeccion,
            'cliente'     => $clientModel->find($inspeccion['id_cliente']),
            'consultor'   => $consultantModel->find($inspeccion['id_consultor']),
            'peligros'    => self::PELIGROS,
            'frecuencias' => self::FRECUENCIAS,
            'porcentajes' => $this->calcularPorcentajes($inspeccion),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/probabilidad-peligros/view', $data),
            'title'   => 'Ver Prob. Peligros',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/probabilidad-peligros')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->inspeccionModel->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to('/inspecciones/probabilidad-peligros/view/' . $id)
            ->with('msg', 'Inspeccion finalizada y PDF generado');
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/probabilidad-peligros')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="probabilidad_peligros_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/probabilidad-peligros')->with('error', 'No encontrada');
        }
        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/probabilidad-peligros')->with('error', 'No se puede eliminar una inspeccion completa');
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/probabilidad-peligros')->with('msg', 'Inspeccion eliminada');
    }

    // ===== MÉTODOS PRIVADOS =====

    private function getInspeccionPostData(): array
    {
        $data = [
            'id_cliente'       => $this->request->getPost('id_cliente'),
            'fecha_inspeccion' => $this->request->getPost('fecha_inspeccion'),
            'observaciones'    => $this->request->getPost('observaciones'),
        ];

        $validKeys = array_keys(self::FRECUENCIAS);
        foreach (self::PELIGROS as $grupo) {
            foreach ($grupo['items'] as $key => $label) {
                $val = $this->request->getPost($key);
                $data[$key] = in_array($val, $validKeys) ? $val : null;
            }
        }

        return $data;
    }

    private function calcularPorcentajes(array $inspeccion): array
    {
        $total = 0;
        $conteo = ['poco_probable' => 0, 'probable' => 0, 'muy_probable' => 0];

        foreach (self::PELIGROS as $grupo) {
            foreach ($grupo['items'] as $key => $label) {
                $val = $inspeccion[$key] ?? null;
                if ($val && isset($conteo[$val])) {
                    $conteo[$val]++;
                    $total++;
                }
            }
        }

        if ($total === 0) {
            return ['poco_probable' => 0, 'probable' => 0, 'muy_probable' => 0];
        }

        return [
            'poco_probable' => round($conteo['poco_probable'] / $total, 4),
            'probable'      => round($conteo['probable'] / $total, 4),
            'muy_probable'  => round($conteo['muy_probable'] / $total, 4),
        ];
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
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

        $porcentajes = $this->calcularPorcentajes($inspeccion);

        $data = [
            'inspeccion'  => $inspeccion,
            'cliente'     => $cliente,
            'consultor'   => $consultor,
            'peligros'    => self::PELIGROS,
            'frecuencias' => self::FRECUENCIAS,
            'porcentajes' => $porcentajes,
            'logoBase64'  => $logoBase64,
        ];

        $html = view('inspecciones/probabilidad-peligros/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/probabilidad-peligros/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'probabilidad_peligros_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 17)
            ->like('observaciones', 'prob_pel_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'probabilidad_peligros_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'PROBABILIDAD PELIGROS - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 17,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. prob_pel_id:' . $inspeccion['id'],
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
