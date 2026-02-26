<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use CodeIgniter\Controller;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('consultant/admindashboard');
    }

    public function addClient()
    {
        $consultantModel = new ConsultantModel();
        $consultants = $consultantModel->findAll(); // Recupera todos los consultores

        // Verifica que los consultores se están cargando
        if (empty($consultants)) {
            log_message('error', 'No se encontraron consultores en la base de datos.');
        }

        // Pasa los consultores a la vista
        $data = [
            'consultants' => $consultants
        ];
        return view('consultant/add_client', $data);
    }

   



    public function addClientPost()
{
    $clientModel = new ClientModel();

    // Aquí añadimos el código para obtener el id_consultor desde el formulario
    $id_consultor = $this->request->getPost('id_consultor');
    if (empty($id_consultor)) {
        return redirect()->back()->with('error', 'Debe seleccionar un consultor.');
    }

    $logo = $this->request->getFile('logo');
    $firma = $this->request->getFile('firma_representante_legal');

    $logoName = null;
    $firmaName = null;

    if ($logo && $logo->isValid() && !$logo->hasMoved()) {
        $logoName = $logo->getRandomName();
        $logo->move(ROOTPATH . 'public/uploads', $logoName); // Cambiado WRITEPATH por ROOTPATH
    }

    if ($firma && $firma->isValid() && !$firma->hasMoved()) {
        $firmaName = $firma->getRandomName();
        $firma->move(ROOTPATH . 'public/uploads', $firmaName); // Cambiado WRITEPATH por ROOTPATH
    }

    $passwordPlano = $this->request->getVar('password');

    $data = [
        'datetime' => date('Y-m-d H:i:s'),
        'fecha_ingreso' => $this->request->getVar('fecha_ingreso'),
        'nit_cliente' => $this->request->getVar('nit_cliente'),
        'nombre_cliente' => $this->request->getVar('nombre_cliente'),
        'usuario' => $this->request->getVar('usuario'),
        'password' => password_hash($passwordPlano, PASSWORD_BCRYPT),
        'correo_cliente' => $this->request->getVar('correo_cliente'),
        'telefono_1_cliente' => $this->request->getVar('telefono_1_cliente'),
        'telefono_2_cliente' => $this->request->getVar('telefono_2_cliente'),
        'direccion_cliente' => $this->request->getVar('direccion_cliente'),
        'persona_contacto_compras' => $this->request->getVar('persona_contacto_compras'),
        'codigo_actividad_economica' => $this->request->getVar('codigo_actividad_economica'),
        'nombre_rep_legal' => $this->request->getVar('nombre_rep_legal'),
        'cedula_rep_legal' => $this->request->getVar('cedula_rep_legal'),
        'fecha_fin_contrato' => $this->request->getVar('fecha_fin_contrato'),
        'ciudad_cliente' => $this->request->getVar('ciudad_cliente'),
        'estado' => 'activo',
        'id_consultor' => $id_consultor,  // Modificado para usar el valor del formulario
        'logo' => $logoName,
        'firma_representante_legal' => $firmaName,
        'estandares' => $this->request->getVar('estandares'),
    ];

    if ($clientModel->save($data)) {
        // Recuperar el NIT del cliente recién guardado
        $nitCliente = $this->request->getVar('nit_cliente');

        // Crear la carpeta para el cliente en public/uploads/{nit_cliente}
        $uploadPath = ROOTPATH . 'public/uploads/' . $nitCliente;

        if (!is_dir($uploadPath)) { // Verificar si la carpeta ya existe
            mkdir($uploadPath, 0777, true); // Crear la carpeta con permisos 0777
        }

        // Enviar email de bienvenida con credenciales de acceso
        $emailMsg = '';
        try {
            $correoCliente = $this->request->getVar('correo_cliente');
            if (!empty($correoCliente) && filter_var($correoCliente, FILTER_VALIDATE_EMAIL)) {
                $consultantModel = new ConsultantModel();
                $consultor = $consultantModel->find($id_consultor);
                $nombreConsultor = $consultor ? ($consultor['nombre_consultor'] ?? 'Consultor SST') : 'Consultor SST';

                $emailSent = $this->sendWelcomeCredentialsEmail(
                    $this->request->getVar('nombre_cliente'),
                    $this->request->getVar('usuario'),
                    $passwordPlano,
                    $correoCliente,
                    $nombreConsultor
                );

                if ($emailSent) {
                    $emailMsg = ' Se enviaron las credenciales de acceso al correo del cliente.';
                } else {
                    $emailMsg = ' No se pudo enviar el email de credenciales. Revise los logs.';
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error al enviar email de bienvenida: ' . $e->getMessage());
            $emailMsg = ' Error al enviar email de credenciales.';
        }

        session()->setFlashdata('msg', 'Cliente agregado exitosamente y carpeta creada.' . $emailMsg);
        return redirect()->to('/addClient');
    } else {
        session()->setFlashdata('msg', 'Error al agregar cliente');
        return redirect()->to('/addClient');
    }
}






    public function addConsultant()
    {
        return view('consultant/add_consultant');
    }

  





    public function addConsultantPost()
    {
        $consultantModel = new ConsultantModel();

        $data = [
            'nombre_consultor' => $this->request->getVar('nombre_consultor'),
            'cedula_consultor' => $this->request->getVar('cedula_consultor'),
            'usuario' => $this->request->getVar('usuario'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_BCRYPT),
            'correo_consultor' => $this->request->getVar('correo_consultor'),
            'telefono_consultor' => $this->request->getVar('telefono_consultor'),
            'numero_licencia' => $this->request->getVar('numero_licencia'),
            'id_cliente' => $this->request->getVar('id_cliente'),
        ];

        // Manejar la subida de la foto
        $photo = $this->request->getFile('foto_consultor');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $photoName = $photo->getRandomName();
            $photo->move(ROOTPATH . 'public/uploads', $photoName);
            $data['foto_consultor'] = $photoName;
        }

        // Manejar la subida de la firma
        $signature = $this->request->getFile('firma_consultor');
        if ($signature && $signature->isValid() && !$signature->hasMoved()) {
            $signatureName = $signature->getRandomName();
            $signature->move(ROOTPATH . 'public/uploads', $signatureName);
            $data['firma_consultor'] = $signatureName;
        }

        if ($consultantModel->save($data)) {
            return redirect()->to('/addConsultant')->with('msg', 'Consultor agregado exitosamente');
        } else {
            return redirect()->to('/addConsultant')->with('msg', 'Error al agregar consultor');
        }
    }

    public function listConsultants()
    {
        $consultantModel = new ConsultantModel();
        $consultants = $consultantModel->findAll();

        $data = [
            'consultants' => $consultants
        ];

        return view('consultant/list_consultants', $data);
    }

    public function editConsultant($id)
    {
        $consultantModel = new ConsultantModel();
        $consultant = $consultantModel->find($id);

        if ($this->request->getMethod() === 'post') {
            $data = [
                'nombre_consultor' => $this->request->getVar('nombre_consultor'),
                'cedula_consultor' => $this->request->getVar('cedula_consultor'),
                'usuario' => $this->request->getVar('usuario'),
                'correo_consultor' => $this->request->getVar('correo_consultor'),
                'telefono_consultor' => $this->request->getVar('telefono_consultor'),
                'numero_licencia' => $this->request->getVar('numero_licencia'),
                'rol' => $this->request->getVar('rol')
            ];

            $photo = $this->request->getFile('foto_consultor');
            if ($photo && $photo->isValid() && !$photo->hasMoved()) {
                $photoName = $photo->getRandomName();
                $photo->move(ROOTPATH . 'public/uploads', $photoName); // Guarda en la carpeta correcta
                $data['foto_consultor'] = $photoName;
            }


            if ($consultantModel->update($id, $data)) {
                session()->setFlashdata('msg', 'Consultor actualizado exitosamente');
                return redirect()->to('/listConsultants');
            } else {
                session()->setFlashdata('msg', 'Error al actualizar consultor');
                return redirect()->to('/addConsultant');
            }
        }

        $data = ['consultant' => $consultant];
        return view('consultant/edit_consultant', $data);
    }

    public function deleteConsultant($id)
    {
        $consultantModel = new ConsultantModel();
        if ($consultantModel->delete($id)) {
            session()->setFlashdata('msg', 'Consultor eliminado exitosamente');
        } else {
            session()->setFlashdata('msg', 'Error al eliminar consultor');
        }

        return redirect()->to('/listConsultants');
    }

    public function showPhoto($id)
    {
        $consultantModel = new ConsultantModel();
        $consultant = $consultantModel->find($id);

        if (!$consultant || empty($consultant['foto_consultor'])) {
            return redirect()->to('/listConsultants')->with('msg', 'Foto no encontrada o consultor no tiene foto.');
        }

        $data = [
            'foto' => $consultant['foto_consultor']
        ];

        return view('consultant/show_photo', $data);
    }


    public function editConsultantPost($id)
    {
        $consultantModel = new ConsultantModel();
        $consultant = $consultantModel->find($id);

        if (!$consultant) {
            return redirect()->to('/listConsultants')->with('msg', 'Consultor no encontrado');
        }

        // Datos que siempre se actualizarán
        $data = [
            'nombre_consultor' => $this->request->getVar('nombre_consultor'),
            'cedula_consultor' => $this->request->getVar('cedula_consultor'),
            'usuario' => $this->request->getVar('usuario'),
            'correo_consultor' => $this->request->getVar('correo_consultor'),
            'telefono_consultor' => $this->request->getVar('telefono_consultor'),
            'numero_licencia' => $this->request->getVar('numero_licencia'),
            'rol' => $this->request->getVar('rol'),
            'id_cliente' => $this->request->getVar('id_cliente')
        ];

        // Manejar la subida de una nueva imagen
        $newPhoto = $this->request->getFile('foto_consultor');
        if ($newPhoto && $newPhoto->isValid() && !$newPhoto->hasMoved()) {
            $newPhotoName = $newPhoto->getRandomName();
            $newPhoto->move(ROOTPATH . 'public/uploads', $newPhotoName);

            // Eliminar la imagen anterior si existe
            if (!empty($consultant['foto_consultor']) && file_exists(ROOTPATH . 'public/uploads/' . $consultant['foto_consultor'])) {
                unlink(ROOTPATH . 'public/uploads/' . $consultant['foto_consultor']);
            }

            // Actualizar el campo en la base de datos
            $data['foto_consultor'] = $newPhotoName;
        }

       

        // Manejar la subida de una nueva firma
        $newSignature = $this->request->getFile('firma_consultor');
        if ($newSignature && $newSignature->isValid() && !$newSignature->hasMoved()) {
            $newSignatureName = $newSignature->getRandomName();
            $newSignature->move(ROOTPATH . 'public/uploads', $newSignatureName);

            // Eliminar la firma anterior si existe
            if (!empty($consultant['firma_consultor']) && file_exists(ROOTPATH . 'public/uploads/' . $consultant['firma_consultor'])) {
                unlink(ROOTPATH . 'public/uploads/' . $consultant['firma_consultor']);
            }

            // Actualizar el campo en la base de datos
            $data['firma_consultor'] = $newSignatureName;
        }


        // Guardar los datos actualizados
        if ($consultantModel->update($id, $data)) {
            return redirect()->to('/listConsultants')->with('msg', 'Consultor actualizado exitosamente');
        } else {
            return redirect()->to('/editConsultant/' . $id)->with('msg', 'Error al actualizar consultor');
        }
    }

    public function listClients()
    {
        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();

        return view('consultant/list_clients', ['clients' => $clients]);
    }



    public function editClient($id)
    {
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $client = $clientModel->find($id);
        $consultants = $consultantModel->findAll();

        if (!$client) {
            return redirect()->to('/listClients')->with('error', 'Cliente no encontrado.');
        }

        $data = [
            'client' => $client,
            'consultants' => $consultants
        ];

        return view('consultant/edit_client', $data);
    }



    public function updateClient($id)
    {
        $clientModel = new ClientModel();
        $client = $clientModel->find($id);

        if (!$client) {
            return redirect()->to('/listClients')->with('msg', 'Cliente no encontrado');
        }

        // Datos que siempre se actualizarán
        $data = [
            'nombre_cliente' => $this->request->getVar('nombre_cliente'),
            'nit_cliente' => $this->request->getVar('nit_cliente'),
            'usuario' => $this->request->getVar('usuario'),
            'correo_cliente' => $this->request->getVar('correo_cliente'),
            'telefono_1_cliente' => $this->request->getVar('telefono_1_cliente'),
            'telefono_2_cliente' => $this->request->getVar('telefono_2_cliente'),
            'direccion_cliente' => $this->request->getVar('direccion_cliente'),
            'persona_contacto_compras' => $this->request->getVar('persona_contacto_compras'),
            'codigo_actividad_economica' => $this->request->getVar('codigo_actividad_economica'),
            'nombre_rep_legal' => $this->request->getVar('nombre_rep_legal'),
            'cedula_rep_legal' => $this->request->getVar('cedula_rep_legal'),
            'fecha_fin_contrato' => $this->request->getVar('fecha_fin_contrato'),
            'ciudad_cliente' => $this->request->getVar('ciudad_cliente'),
            'estado' => $this->request->getVar('estado'),
            'id_consultor' => $this->request->getVar('id_consultor'),
            'estandares' => $this->request->getVar('estandares')
        ];

        // Manejar la subida de un nuevo logo
        $newLogo = $this->request->getFile('logo');
        if ($newLogo && $newLogo->isValid() && !$newLogo->hasMoved()) {
            $newLogoName = $newLogo->getRandomName();
            $newLogo->move(ROOTPATH . 'public/uploads', $newLogoName);

            // Eliminar el logo anterior si existe
            if (!empty($client['logo']) && file_exists(ROOTPATH . 'public/uploads/' . $client['logo'])) {
                unlink(ROOTPATH . 'public/uploads/' . $client['logo']);
            }

            // Actualizar el campo en la base de datos
            $data['logo'] = $newLogoName;
        }

        // Manejar la subida de una nueva firma
        $newSignature = $this->request->getFile('firma_representante_legal');
        if ($newSignature && $newSignature->isValid() && !$newSignature->hasMoved()) {
            $newSignatureName = $newSignature->getRandomName();
            $newSignature->move(ROOTPATH . 'public/uploads', $newSignatureName);

            // Eliminar la firma anterior si existe
            if (!empty($client['firma_representante_legal']) && file_exists(ROOTPATH . 'public/uploads/' . $client['firma_representante_legal'])) {
                unlink(ROOTPATH . 'public/uploads/' . $client['firma_representante_legal']);
            }

            // Actualizar el campo en la base de datos
            $data['firma_representante_legal'] = $newSignatureName;
        }

        // Guardar los datos actualizados
        if ($clientModel->update($id, $data)) {
            return redirect()->to('/listClients')->with('msg', 'Cliente actualizado exitosamente');
        } else {
            return redirect()->to('/editClient/' . $id)->with('msg', 'Error al actualizar cliente');
        }
    }

    public function deleteClient($id)
    {
        $clientModel = new ClientModel();

        try {
            // Intentar eliminar el cliente
            $client = $clientModel->find($id);
            if ($client) {
                // Eliminar las imágenes relacionadas si existen
                if (!empty($client['logo']) && file_exists(ROOTPATH . 'public/uploads/' . $client['logo'])) {
                    unlink(ROOTPATH . 'public/uploads/' . $client['logo']);
                }
                if (!empty($client['firma_representante_legal']) && file_exists(ROOTPATH . 'public/uploads/' . $client['firma_representante_legal'])) {
                    unlink(ROOTPATH . 'public/uploads/' . $client['firma_representante_legal']);
                }
                // Intentar eliminar el cliente
                $clientModel->delete($id);

                return redirect()->to('/listClients')->with('msg', 'Cliente eliminado exitosamente');
            } else {
                return redirect()->to('/listClients')->with('msg', 'Cliente no encontrado');
            }
        } catch (\Exception $e) {
            // Capturar la excepción y mostrar un mensaje de advertencia
            return redirect()->to('/listClients')->with('error', 'No puedes eliminar clientes que ya tienen registros grabados en la base de datos. Póngase en contacto con su administrador.');
        }
    }

    public function deletePtaAbiertas()
    {
        $clientModel = new ClientModel();
        $clients = $clientModel->where('estado', 'activo')->findAll();

        return view('consultant/delete_pta_abiertas', ['clients' => $clients]);
    }

    public function countPtaAbiertas()
    {
        $idCliente = $this->request->getPost('id_cliente');

        if (empty($idCliente)) {
            return $this->response->setJSON(['error' => 'Debe seleccionar un cliente']);
        }

        $db = \Config\Database::connect();
        $count = $db->table('tbl_pta_cliente')
                    ->where('id_cliente', $idCliente)
                    ->where('estado_actividad', 'ABIERTA')
                    ->countAllResults();

        return $this->response->setJSON(['count' => $count]);
    }

    public function deletePtaAbiertasPost()
    {
        $idCliente = $this->request->getPost('id_cliente');

        if (empty($idCliente)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Debe seleccionar un cliente']);
        }

        $db = \Config\Database::connect();
        $db->table('tbl_pta_cliente')
           ->where('id_cliente', $idCliente)
           ->where('estado_actividad', 'ABIERTA')
           ->delete();

        $affected = $db->affectedRows();

        return $this->response->setJSON([
            'success' => true,
            'deleted' => $affected,
            'message' => "Se eliminaron {$affected} actividades ABIERTAS exitosamente."
        ]);
    }

    // ─── Email de bienvenida (mismo template que ConsultantController) ─────────

    private function sendWelcomeCredentialsEmail($nombreCliente, $usuario, $password, $correo, $consultorNombre, $isResend = false)
    {
        $apiKey = env('SENDGRID_API_KEY');
        if (empty($apiKey)) {
            log_message('error', 'SENDGRID_API_KEY no configurada — email de credenciales no enviado.');
            return false;
        }

        $subject = $isResend
            ? 'Nuevas Credenciales de Acceso — Enterprise SST'
            : 'Bienvenido a Enterprise SST — Credenciales de Acceso';

        $loginUrl = base_url('/login');
        $anio = date('Y');

        $titulo = $isResend ? 'Nuevas Credenciales de Acceso' : 'Bienvenido a Enterprise SST';
        $mensajeIntro = $isResend
            ? 'Se han generado nuevas credenciales de acceso para su cuenta en nuestra plataforma.'
            : 'En nombre de todo el equipo de <strong>Cycloid Talent SAS</strong>, queremos agradecerle profundamente por confiar en nosotros como su empresa de asesoría en el <strong>Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST)</strong>.';

        $mensajeExtra = $isResend
            ? ''
            : '<p style="font-size: 15px; color: #4a4a4a; line-height: 1.7;">Es un placer acompañarle en este proceso tan importante para el bienestar de su organización y sus colaboradores. Estamos comprometidos con brindarle el mejor servicio y acompañamiento durante todo el proceso.</p>';

        $htmlContent = '
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
        <body style="font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 30px 0;">
                <tr><td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                        <tr><td style="background: linear-gradient(135deg, #bd9751, #d4a94d, #c9a04e); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: bold; text-shadow: 0 1px 3px rgba(0,0,0,0.2);">' . htmlspecialchars($titulo) . '</h1>
                            <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0; font-size: 14px;">Plataforma de Gestión SG-SST</p>
                        </td></tr>
                        <tr><td style="padding: 35px 30px 20px;">
                            <p style="font-size: 16px; color: #2c3e50; margin: 0 0 5px;">Estimado(a),</p>
                            <h2 style="font-size: 20px; color: #1c2437; margin: 0 0 20px; font-weight: bold;">' . htmlspecialchars($nombreCliente) . '</h2>
                            <p style="font-size: 15px; color: #4a4a4a; line-height: 1.7;">' . $mensajeIntro . '</p>' . $mensajeExtra . '
                            <p style="font-size: 15px; color: #4a4a4a; line-height: 1.7;">A continuación encontrará sus credenciales de acceso a nuestra plataforma:</p>
                        </td></tr>
                        <tr><td style="padding: 0 30px 25px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background: linear-gradient(135deg, #faf8f3, #f5f0e6); border: 2px solid #d4a94d; border-radius: 10px; overflow: hidden;">
                                <tr><td style="background: #1c2437; padding: 12px 20px;"><p style="color: #d4a94d; margin: 0; font-size: 15px; font-weight: bold;">Sus Credenciales de Acceso</p></td></tr>
                                <tr><td style="padding: 20px;">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr><td style="padding: 8px 0; border-bottom: 1px solid #e8dcc8;"><span style="color: #7a6b4f; font-size: 13px; font-weight: bold;">USUARIO</span><br><span style="color: #1c2437; font-size: 16px; font-weight: bold;">' . htmlspecialchars($usuario) . '</span></td></tr>
                                        <tr><td style="padding: 8px 0; border-bottom: 1px solid #e8dcc8;"><span style="color: #7a6b4f; font-size: 13px; font-weight: bold;">CONTRASE&Ntilde;A</span><br><span style="color: #bd9751; font-size: 18px; font-weight: bold; letter-spacing: 1px;">' . htmlspecialchars($password) . '</span></td></tr>
                                        <tr><td style="padding: 8px 0;"><span style="color: #7a6b4f; font-size: 13px; font-weight: bold;">CORREO REGISTRADO</span><br><span style="color: #1c2437; font-size: 15px;">' . htmlspecialchars($correo) . '</span></td></tr>
                                    </table>
                                </td></tr>
                            </table>
                        </td></tr>
                        <tr><td style="padding: 0 30px 20px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f0f4f8; border-radius: 8px; border-left: 4px solid #1c2437;">
                                <tr><td style="padding: 15px 20px;"><p style="margin: 0; font-size: 14px; color: #4a4a4a;">Su consultor asignado es <strong style="color: #1c2437;">' . htmlspecialchars($consultorNombre) . '</strong>, quien le acompañará durante todo el proceso de implementación del SG-SST.</p></td></tr>
                            </table>
                        </td></tr>
                        <tr><td style="padding: 10px 30px 25px; text-align: center;">
                            <a href="' . $loginUrl . '" style="display: inline-block; background: linear-gradient(135deg, #1c2437, #2c3e50); color: #ffffff; padding: 16px 40px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; box-shadow: 0 3px 10px rgba(28,36,55,0.3);">Ingresar a la Plataforma</a>
                        </td></tr>
                        <tr><td style="padding: 0 30px 25px;">
                            <p style="font-size: 13px; color: #888; background-color: #fff8ed; padding: 12px 15px; border-radius: 6px; border: 1px solid #f0e0c0; margin: 0;"><strong>Recomendación de seguridad:</strong> Le sugerimos cambiar su contraseña después del primer inicio de sesión.</p>
                        </td></tr>
                        <tr><td style="padding: 0 30px 15px;">
                            <p style="font-size: 15px; color: #4a4a4a; line-height: 1.7;">Si tiene alguna duda o necesita asistencia, no dude en contactarnos. Estamos para servirle.</p>
                            <p style="font-size: 15px; color: #4a4a4a; margin-bottom: 5px;">Con gratitud,</p>
                            <p style="font-size: 16px; color: #1c2437; font-weight: bold; margin: 0;">Equipo Cycloid Talent SAS</p>
                        </td></tr>
                        <tr><td style="background-color: #1c2437; padding: 25px 30px; text-align: center;">
                            <p style="margin: 0 0 5px; color: #d4a94d; font-size: 14px; font-weight: bold;">Cycloid Talent SAS</p>
                            <p style="margin: 0 0 5px; color: rgba(255,255,255,0.7); font-size: 12px;">NIT: 901.653.912</p>
                            <p style="margin: 0 0 10px; color: rgba(255,255,255,0.7); font-size: 12px;">Asesores especializados en SG-SST</p>
                            <p style="margin: 0; color: rgba(255,255,255,0.5); font-size: 11px;">&copy; ' . $anio . ' Cycloid Talent SAS - Todos los derechos reservados</p>
                        </td></tr>
                    </table>
                </td></tr>
            </table>
        </body></html>';

        $payload = [
            'personalizations' => [[
                'to' => [['email' => $correo, 'name' => $nombreCliente]],
                'subject' => $subject,
            ]],
            'from' => [
                'email' => env('SENDGRID_FROM_EMAIL', 'notificacion.cycloidtalent@cycloidtalent.com'),
                'name'  => env('SENDGRID_FROM_NAME', 'Enterprise SST'),
            ],
            'content' => [['type' => 'text/html', 'value' => $htmlContent]],
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }
}
