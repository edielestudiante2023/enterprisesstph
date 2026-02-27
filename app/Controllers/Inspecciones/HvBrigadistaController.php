<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\HvBrigadistaModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class HvBrigadistaController extends BaseController
{
    protected HvBrigadistaModel $hvModel;

    public function __construct()
    {
        $this->hvModel = new HvBrigadistaModel();
    }

    /**
     * Lista de HV brigadista (admin ve todas, consultor ve sus clientes)
     */
    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $registros = $this->hvModel
                ->select('tbl_hv_brigadista.*, tbl_clientes.nombre_cliente')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_hv_brigadista.id_cliente', 'left')
                ->orderBy('tbl_hv_brigadista.created_at', 'DESC')
                ->findAll();
        } else {
            $registros = $this->hvModel->getByConsultor($userId);
        }

        $data = [
            'title'     => 'Hoja de Vida Brigadistas',
            'registros' => $registros,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/hv-brigadista/list', $data),
            'title'   => 'HV Brigadista',
        ]);
    }

    /**
     * Vista read-only de una HV
     */
    public function view($id)
    {
        $hv = $this->hvModel->find($id);
        if (!$hv) {
            return redirect()->to('/inspecciones/hv-brigadista')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();

        $data = [
            'title'   => 'Ver HV Brigadista',
            'hv'      => $hv,
            'cliente' => $clientModel->find($hv['id_cliente']),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/hv-brigadista/view', $data),
            'title'   => 'Ver HV Brigadista',
        ]);
    }

    /**
     * Genera y muestra el PDF inline
     */
    public function generatePdf($id)
    {
        $hv = $this->hvModel->find($id);
        if (!$hv) {
            return redirect()->to('/inspecciones/hv-brigadista')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->hvModel->update($id, ['ruta_pdf' => $pdfPath]);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="hv_brigadista_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    /**
     * Finalizar: genera PDF + registra en tbl_reporte
     */
    public function finalizar($id)
    {
        $hv = $this->hvModel->find($id);
        if (!$hv) {
            return redirect()->to('/inspecciones/hv-brigadista')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->hvModel->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $hv = $this->hvModel->find($id);
        $this->uploadToReportes($hv, $pdfPath);

        return redirect()->to('/inspecciones/hv-brigadista/view/' . $id)
            ->with('msg', 'HV finalizada y PDF generado');
    }

    /**
     * Eliminar (solo borradores)
     */
    public function delete($id)
    {
        $hv = $this->hvModel->find($id);
        if (!$hv) {
            return redirect()->to('/inspecciones/hv-brigadista')->with('error', 'No encontrada');
        }
        // Borrar foto
        if (!empty($hv['foto_brigadista']) && file_exists(FCPATH . $hv['foto_brigadista'])) {
            unlink(FCPATH . $hv['foto_brigadista']);
        }

        // Borrar firma
        if (!empty($hv['firma']) && file_exists(FCPATH . $hv['firma'])) {
            unlink(FCPATH . $hv['firma']);
        }

        // Borrar PDF
        if (!empty($hv['ruta_pdf']) && file_exists(FCPATH . $hv['ruta_pdf'])) {
            unlink(FCPATH . $hv['ruta_pdf']);
        }

        $this->hvModel->delete($id);

        return redirect()->to('/inspecciones/hv-brigadista')->with('msg', 'HV eliminada');
    }

    /**
     * Formulario de edicion
     */
    public function edit($id)
    {
        $hv = $this->hvModel->find($id);
        if (!$hv) {
            return redirect()->to('/inspecciones/hv-brigadista')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();

        $data = [
            'title'   => 'Editar HV Brigadista',
            'hv'      => $hv,
            'cliente' => $clientModel->find($hv['id_cliente']),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/hv-brigadista/form', $data),
            'title'   => 'Editar HV Brigadista',
        ]);
    }

    /**
     * Actualizar HV
     */
    public function update($id)
    {
        $hv = $this->hvModel->find($id);
        if (!$hv) {
            return redirect()->to('/inspecciones/hv-brigadista')->with('error', 'No encontrada');
        }

        $data = $this->getHvPostData();

        // Foto replacement
        $nuevaFoto = $this->uploadFoto('foto_brigadista', 'uploads/inspecciones/hv-brigadista/fotos/');
        if ($nuevaFoto) {
            if (!empty($hv['foto_brigadista']) && file_exists(FCPATH . $hv['foto_brigadista'])) {
                unlink(FCPATH . $hv['foto_brigadista']);
            }
            $data['foto_brigadista'] = $nuevaFoto;
        }

        // Firma replacement (base64 canvas)
        $nuevaFirma = $this->guardarFirma();
        if ($nuevaFirma) {
            if (!empty($hv['firma']) && file_exists(FCPATH . $hv['firma'])) {
                unlink(FCPATH . $hv['firma']);
            }
            $data['firma'] = $nuevaFirma;
        }

        $this->hvModel->update($id, $data);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        return redirect()->to('/inspecciones/hv-brigadista/edit/' . $id)
            ->with('msg', 'HV actualizada');
    }

    // ===== METODOS PRIVADOS =====

    private function getHvPostData(): array
    {
        return [
            'fecha_inscripcion'       => $this->request->getPost('fecha_inscripcion') ?: null,
            'nombre_completo'         => trim($this->request->getPost('nombre_completo') ?? ''),
            'documento_identidad'     => trim($this->request->getPost('documento_identidad') ?? ''),
            'f_nacimiento'            => $this->request->getPost('f_nacimiento') ?: null,
            'email'                   => trim($this->request->getPost('email') ?? ''),
            'telefono'                => trim($this->request->getPost('telefono') ?? ''),
            'direccion_residencia'    => trim($this->request->getPost('direccion_residencia') ?? ''),
            'edad'                    => $this->request->getPost('edad') !== '' ? (int) $this->request->getPost('edad') : null,
            'eps'                     => trim($this->request->getPost('eps') ?? ''),
            'peso'                    => $this->request->getPost('peso') ?: null,
            'estatura'                => $this->request->getPost('estatura') ?: null,
            'rh'                      => $this->request->getPost('rh') ?: null,
            'estudios_1'              => trim($this->request->getPost('estudios_1') ?? '') ?: null,
            'lugar_estudio_1'         => trim($this->request->getPost('lugar_estudio_1') ?? '') ?: null,
            'anio_estudio_1'          => $this->request->getPost('anio_estudio_1') ? (int) $this->request->getPost('anio_estudio_1') : null,
            'estudios_2'              => trim($this->request->getPost('estudios_2') ?? '') ?: null,
            'lugar_estudio_2'         => trim($this->request->getPost('lugar_estudio_2') ?? '') ?: null,
            'anio_estudio_2'          => $this->request->getPost('anio_estudio_2') ? (int) $this->request->getPost('anio_estudio_2') : null,
            'estudios_3'              => trim($this->request->getPost('estudios_3') ?? '') ?: null,
            'lugar_estudio_3'         => trim($this->request->getPost('lugar_estudio_3') ?? '') ?: null,
            'anio_estudio_3'          => $this->request->getPost('anio_estudio_3') ? (int) $this->request->getPost('anio_estudio_3') : null,
            'enfermedades_importantes' => trim($this->request->getPost('enfermedades_importantes') ?? ''),
            'medicamentos'            => trim($this->request->getPost('medicamentos') ?? ''),
            'cardiaca'                => $this->request->getPost('cardiaca') ?: null,
            'pechoactividad'          => $this->request->getPost('pechoactividad') ?: null,
            'dolorpecho'              => $this->request->getPost('dolorpecho') ?: null,
            'conciencia'              => $this->request->getPost('conciencia') ?: null,
            'huesos'                  => $this->request->getPost('huesos') ?: null,
            'medicamentos_bool'       => $this->request->getPost('medicamentos_bool') ?: null,
            'actividadfisica'         => $this->request->getPost('actividadfisica') ?: null,
            'convulsiones'            => $this->request->getPost('convulsiones') ?: null,
            'vertigo'                 => $this->request->getPost('vertigo') ?: null,
            'oidos'                   => $this->request->getPost('oidos') ?: null,
            'lugarescerrados'         => $this->request->getPost('lugarescerrados') ?: null,
            'miedoalturas'            => $this->request->getPost('miedoalturas') ?: null,
            'haceejercicio'           => $this->request->getPost('haceejercicio') ?: null,
            'miedo_ver_sangre'        => $this->request->getPost('miedo_ver_sangre') ?: null,
            'restricciones_medicas'   => trim($this->request->getPost('restricciones_medicas') ?? ''),
            'deporte_semana'          => trim($this->request->getPost('deporte_semana') ?? ''),
        ];
    }

    private function uploadFoto(string $campo, string $dir): ?string
    {
        $file = $this->request->getFile($campo);
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }
        if (!is_dir(FCPATH . $dir)) {
            mkdir(FCPATH . $dir, 0755, true);
        }
        $fileName = $file->getRandomName();
        $file->move(FCPATH . $dir, $fileName);
        return $dir . $fileName;
    }

    private function guardarFirma(): ?string
    {
        $firmaB64 = $this->request->getPost('firma_imagen');
        if (empty($firmaB64) || $firmaB64 === 'data:,') {
            return null;
        }

        $parts = explode(',', $firmaB64);
        $data = base64_decode(end($parts));
        if ($data === false) {
            return null;
        }

        $dir = 'uploads/inspecciones/hv-brigadista/firmas/';
        if (!is_dir(FCPATH . $dir)) {
            mkdir(FCPATH . $dir, 0755, true);
        }

        $fileName = 'firma_' . uniqid() . '_' . date('Ymd_His') . '.png';
        $path = $dir . $fileName;
        file_put_contents(FCPATH . $path, $data);

        return $path;
    }

    /**
     * Genera el PDF con DOMPDF
     */
        public function regenerarPdf($id)
    {
        $inspeccion = $this->hvModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/hv-brigadista')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->hvModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->hvModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/hv-brigadista/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    private function generarPdfInterno($id): ?string
    {
        $hv = $this->hvModel->find($id);
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($hv['id_cliente']);

        // Logo en base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        // Foto brigadista a base64
        $fotoBase64 = '';
        if (!empty($hv['foto_brigadista'])) {
            $fotoPath = FCPATH . $hv['foto_brigadista'];
            if (file_exists($fotoPath)) {
                $mime = mime_content_type($fotoPath);
                $fotoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fotoPath));
            }
        }

        // Firma a base64
        $firmaBase64 = '';
        if (!empty($hv['firma'])) {
            $firmaPath = FCPATH . $hv['firma'];
            if (file_exists($firmaPath)) {
                $mime = mime_content_type($firmaPath);
                $firmaBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($firmaPath));
            }
        }

        // Consultor del cliente
        $consultorNombre = '';
        if (!empty($cliente['id_consultor'])) {
            $consultantModel = new ConsultantModel();
            $consultor = $consultantModel->find($cliente['id_consultor']);
            $consultorNombre = $consultor['nombre_consultor'] ?? '';
        }

        $data = [
            'hv'              => $hv,
            'cliente'         => $cliente,
            'consultorNombre' => $consultorNombre,
            'logoBase64'      => $logoBase64,
            'fotoBase64'      => $fotoBase64,
            'firmaBase64'     => $firmaBase64,
        ];

        $html = view('inspecciones/hv-brigadista/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/hv-brigadista/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'hv_brigadista_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        // Borrar PDF anterior
        if (!empty($hv['ruta_pdf']) && file_exists(FCPATH . $hv['ruta_pdf'])) {
            unlink(FCPATH . $hv['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    /**
     * Registra/actualiza el PDF en tbl_reporte
     */
    private function uploadToReportes(array $hv, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($hv['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'];

        $existente = $reporteModel
            ->where('id_cliente', $hv['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 22)
            ->like('observaciones', 'hv_brig_id:' . $hv['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'hv_brigadista_' . $hv['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'HV BRIGADISTA - ' . ($hv['nombre_completo'] ?? '') . ' - ' . ($cliente['nombre_cliente'] ?? ''),
            'id_detailreport' => 22,
            'id_report_type'  => 6,
            'id_cliente'      => $hv['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. hv_brig_id:' . $hv['id'],
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
