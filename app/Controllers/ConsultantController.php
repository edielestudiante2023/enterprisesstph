<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Models\PlanModel;
use App\Models\CronogcapacitacionModel;
use App\Models\PtaclienteModel;
use App\Models\PendientesModel;
use App\Models\VencimientosMantenimientoModel;
use App\Models\SimpleEvaluationModel;
use App\Models\DashboardItemModel;
use App\Libraries\WorkPlanLibrary;
use App\Libraries\TrainingLibrary;
use App\Libraries\StandardsLibrary;
use App\Libraries\ClientDocumentInitializerLibrary;
use App\Libraries\MatricesGeneratorLibrary;
use App\Models\CicloVisitaModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class ConsultantController extends Controller
{
    public function index()
    {
        $model = new DashboardItemModel();
        $data['items'] = $model->where('orden >=', 1)
            ->where('orden <=', 5)
            ->findAll();

        return view('consultant/dashboard', $data);
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
        $rutFile = $this->request->getFile('rut');
        $camaraFile = $this->request->getFile('camara_comercio');
        $cedulaDocFile = $this->request->getFile('cedula_rep_legal_doc');
        $ofertaFile = $this->request->getFile('oferta_comercial');

        $logoName = null;
        $firmaName = null;
        $rutName = null;
        $camaraName = null;
        $cedulaDocName = null;
        $ofertaName = null;

        $uploadPath = ROOTPATH . 'public/uploads';

        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $logoName = $logo->getRandomName();
            $logo->move($uploadPath, $logoName);
            $this->generateLogoThumbnail($uploadPath, $logoName);
        }

        if ($firma && $firma->isValid() && !$firma->hasMoved()) {
            $firmaName = $firma->getRandomName();
            $firma->move($uploadPath, $firmaName);
        }

        if ($rutFile && $rutFile->isValid() && !$rutFile->hasMoved()) {
            $rutName = $rutFile->getRandomName();
            $rutFile->move($uploadPath, $rutName);
        }

        if ($camaraFile && $camaraFile->isValid() && !$camaraFile->hasMoved()) {
            $camaraName = $camaraFile->getRandomName();
            $camaraFile->move($uploadPath, $camaraName);
        }

        if ($cedulaDocFile && $cedulaDocFile->isValid() && !$cedulaDocFile->hasMoved()) {
            $cedulaDocName = $cedulaDocFile->getRandomName();
            $cedulaDocFile->move($uploadPath, $cedulaDocName);
        }

        if ($ofertaFile && $ofertaFile->isValid() && !$ofertaFile->hasMoved()) {
            $ofertaName = $ofertaFile->getRandomName();
            $ofertaFile->move($uploadPath, $ofertaName);
        }

        $passwordPlano = $this->request->getVar('password');

        $fechaCierre = $this->request->getVar('fecha_cierre_facturacion');
        $fechaAsignacion = $this->request->getVar('fecha_asignacion_cronograma');

        $data = [
            'datetime' => date('Y-m-d H:i:s'),
            'fecha_ingreso' => $this->request->getVar('fecha_ingreso'),
            'nit_cliente' => $this->request->getVar('nit_cliente'),
            'nombre_cliente' => $this->request->getVar('nombre_cliente'),
            'usuario' => $this->request->getVar('usuario'),
            'password' => password_hash($passwordPlano, PASSWORD_BCRYPT),
            'correo_cliente' => $this->request->getVar('correo_cliente'),
            'correo_consejo_admon' => $this->request->getVar('correo_consejo_admon'),
            'telefono_1_cliente' => $this->request->getVar('telefono_1_cliente'),
            'telefono_2_cliente' => $this->request->getVar('telefono_2_cliente'),
            'direccion_cliente' => $this->request->getVar('direccion_cliente'),
            'persona_contacto_compras' => $this->request->getVar('persona_contacto_compras'),
            'persona_contacto_operaciones' => $this->request->getVar('persona_contacto_operaciones'),
            'persona_contacto_pagos' => $this->request->getVar('persona_contacto_pagos'),
            'horarios_y_dias' => $this->request->getVar('horarios_y_dias'),
            'codigo_actividad_economica' => $this->request->getVar('codigo_actividad_economica'),
            'nombre_rep_legal' => $this->request->getVar('nombre_rep_legal'),
            'cedula_rep_legal' => $this->request->getVar('cedula_rep_legal'),
            'fecha_fin_contrato' => $this->request->getVar('fecha_fin_contrato'),
            'ciudad_cliente' => $this->request->getVar('ciudad_cliente'),
            'estado' => 'activo',
            'id_consultor' => $id_consultor,
            'vendedor' => $this->request->getVar('vendedor'),
            'plazo_cartera' => $this->request->getVar('plazo_cartera'),
            'fecha_cierre_facturacion' => !empty($fechaCierre) ? (int) $fechaCierre : null,
            'fecha_asignacion_cronograma' => !empty($fechaAsignacion) ? $fechaAsignacion : null,
            'logo' => $logoName,
            'rut' => $rutName,
            'camara_comercio' => $camaraName,
            'cedula_rep_legal_doc' => $cedulaDocName,
            'oferta_comercial' => $ofertaName,
            'firma_representante_legal' => $firmaName,
            'estandares' => $this->request->getVar('estandares'),
        ];

        if ($clientModel->save($data)) {
            // Obtener el ID del cliente recién creado
            $clientId = $clientModel->getInsertID();

            // Recuperar el NIT del cliente recién guardado
            $nitCliente = $this->request->getVar('nit_cliente');

            // Crear la carpeta para el cliente en public/uploads/{nit_cliente}
            $uploadPath = ROOTPATH . 'public/uploads/' . $nitCliente;

            if (!is_dir($uploadPath)) { // Verificar si la carpeta ya existe
                mkdir($uploadPath, 0777, true); // Crear la carpeta con permisos 0777
            }

            // Inicializar client_policies y document_versions para el nuevo cliente
            ClientDocumentInitializerLibrary::initialize($clientId);

            // Generar primer ciclo de visita
            $estandarCliente = $this->request->getVar('estandares') ?? '';
            if ($estandarCliente) {
                (new CicloVisitaModel())->generarPrimerCiclo(
                    (int)$clientId,
                    (int)$id_consultor,
                    $estandarCliente
                );
            }

            // Generar automáticamente el Plan de Trabajo Año 1
            try {
                $tipoServicio = strtolower($this->request->getVar('estandares'));
                $workPlanLibrary = new WorkPlanLibrary();

                // Obtener las actividades del Año 1 según el tipo de servicio
                $activities = $workPlanLibrary->getActivities($clientId, 1, $tipoServicio);

                // Insertar las actividades
                if (!empty($activities)) {
                    $planModel = new PlanModel();
                    $insertedCount = 0;

                    foreach ($activities as $activity) {
                        if ($planModel->insert($activity)) {
                            $insertedCount++;
                        }
                    }

                    log_message('info', "Plan de Trabajo generado automáticamente para cliente ID {$clientId}: {$insertedCount} actividades insertadas");
                }
            } catch (\Exception $e) {
                // Log del error pero no interrumpir el flujo
                log_message('error', 'Error al generar Plan de Trabajo automático: ' . $e->getMessage());
            }

            // Generar automáticamente el Cronograma de Capacitaciones
            try {
                $tipoServicio = strtolower($this->request->getVar('estandares'));
                $trainingLibrary = new TrainingLibrary();

                // Obtener las capacitaciones según el tipo de servicio
                $trainings = $trainingLibrary->getTrainings($clientId, $tipoServicio);

                // Insertar las capacitaciones
                if (!empty($trainings)) {
                    $cronogModel = new CronogcapacitacionModel();
                    $insertedCount = 0;

                    foreach ($trainings as $training) {
                        if ($cronogModel->insert($training)) {
                            $insertedCount++;
                        }
                    }

                    log_message('info', "Cronograma de Capacitaciones generado automáticamente para cliente ID {$clientId}: {$insertedCount} capacitaciones insertadas");
                }
            } catch (\Exception $e) {
                // Log del error pero no interrumpir el flujo
                log_message('error', 'Error al generar Cronograma de Capacitaciones automático: ' . $e->getMessage());
            }

            // Generar automáticamente los Estándares Mínimos
            try {
                $standardsLibrary = new StandardsLibrary();

                // Obtener los estándares mínimos desde el CSV maestro
                $standards = $standardsLibrary->getStandards($clientId);

                // Insertar los estándares
                if (!empty($standards)) {
                    $evaluationModel = new SimpleEvaluationModel();
                    $insertedCount = 0;

                    foreach ($standards as $standard) {
                        if ($evaluationModel->insert($standard)) {
                            $insertedCount++;
                        }
                    }

                    log_message('info', "Estándares Mínimos generados automáticamente para cliente ID {$clientId}: {$insertedCount} estándares insertados");
                }
            } catch (\Exception $e) {
                // Log del error pero no interrumpir el flujo
                log_message('error', 'Error al generar Estándares Mínimos automáticos: ' . $e->getMessage());
            }

            // Generar automáticamente las Matrices SST personalizadas (EPP + Peligros)
            try {
                $matricesLib = new MatricesGeneratorLibrary();
                $matricesResult = $matricesLib->generarYRegistrar((int)$clientId);

                if (!empty($matricesResult['errors'])) {
                    log_message('error', 'Matrices SST - Errores para cliente ID ' . $clientId . ': ' . implode(', ', $matricesResult['errors']));
                } else {
                    log_message('info', "Matrices SST generadas automáticamente para cliente ID {$clientId}");
                }
            } catch (\Exception $e) {
                log_message('error', 'Error al generar Matrices SST automáticas: ' . $e->getMessage());
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
                        log_message('info', "Email de bienvenida enviado a {$correoCliente} para cliente ID {$clientId}");
                    } else {
                        $emailMsg = ' No se pudo enviar el email de credenciales. Revise los logs.';
                    }
                } else {
                    $emailMsg = ' No se envió email: correo del cliente no válido o vacío.';
                }
            } catch (\Exception $e) {
                log_message('error', 'Error al enviar email de bienvenida: ' . $e->getMessage());
                $emailMsg = ' Error al enviar email de credenciales.';
            }

            // Enviar email de felicitación al equipo interno
            try {
                $this->enviarEmailFelicitacionClienteNuevo(
                    $this->request->getVar('nombre_cliente'),
                    $this->request->getVar('nit_cliente'),
                    $this->request->getVar('ciudad_cliente'),
                    $this->request->getVar('vendedor'),
                    $this->request->getVar('estandares')
                );
            } catch (\Exception $e) {
                log_message('error', 'Error al enviar email de felicitación: ' . $e->getMessage());
            }

            // Crear usuario en tbl_usuarios para que el cliente pueda hacer login
            try {
                $userModel = new UserModel();
                $correoCliente = $this->request->getVar('correo_cliente');
                if (!empty($correoCliente) && filter_var($correoCliente, FILTER_VALIDATE_EMAIL)) {
                    // Solo crear si no existe ya un usuario con ese email
                    if (!$userModel->findByEmail($correoCliente)) {
                        $userModel->skipValidation(false)->insert([
                            'email'          => $correoCliente,
                            'password'       => password_hash($passwordPlano, PASSWORD_BCRYPT),
                            'nombre_completo' => $this->request->getVar('nombre_cliente'),
                            'tipo_usuario'   => 'client',
                            'id_entidad'     => $clientId,
                            'estado'         => 'activo',
                        ]);
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error al crear usuario en tbl_usuarios: ' . $e->getMessage());
            }

            session()->setFlashdata('msg', 'Cliente agregado exitosamente.' . $emailMsg);

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
        $consultantModel = new ConsultantModel();

        // Obtener todos los clientes
        $clients = $clientModel->findAll();

        // Recorrer los clientes y agregar el nombre del consultor correspondiente
        foreach ($clients as &$client) {
            $consultant = $consultantModel->find($client['id_consultor']);
            $client['nombre_consultor'] = $consultant ? $consultant['nombre_consultor'] : 'No asignado';
        }

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
        $fechaCierre = $this->request->getVar('fecha_cierre_facturacion');
        $fechaAsignacion = $this->request->getVar('fecha_asignacion_cronograma');

        $data = [
            'fecha_ingreso' => $this->request->getVar('fecha_ingreso'),
            'nombre_cliente' => $this->request->getVar('nombre_cliente'),
            'nit_cliente' => $this->request->getVar('nit_cliente'),
            'usuario' => $this->request->getVar('usuario'),
            'correo_cliente' => $this->request->getVar('correo_cliente'),
            'correo_consejo_admon' => $this->request->getVar('correo_consejo_admon'),
            'telefono_1_cliente' => $this->request->getVar('telefono_1_cliente'),
            'telefono_2_cliente' => $this->request->getVar('telefono_2_cliente'),
            'direccion_cliente' => $this->request->getVar('direccion_cliente'),
            'persona_contacto_compras' => $this->request->getVar('persona_contacto_compras'),
            'persona_contacto_operaciones' => $this->request->getVar('persona_contacto_operaciones'),
            'persona_contacto_pagos' => $this->request->getVar('persona_contacto_pagos'),
            'horarios_y_dias' => $this->request->getVar('horarios_y_dias'),
            'codigo_actividad_economica' => $this->request->getVar('codigo_actividad_economica'),
            'nombre_rep_legal' => $this->request->getVar('nombre_rep_legal'),
            'cedula_rep_legal' => $this->request->getVar('cedula_rep_legal'),
            'fecha_fin_contrato' => $this->request->getVar('fecha_fin_contrato'),
            'ciudad_cliente' => $this->request->getVar('ciudad_cliente'),
            'estado' => $this->request->getVar('estado'),
            'id_consultor' => $this->request->getVar('id_consultor'),
            'vendedor' => $this->request->getVar('vendedor'),
            'plazo_cartera' => $this->request->getVar('plazo_cartera'),
            'fecha_cierre_facturacion' => !empty($fechaCierre) ? (int) $fechaCierre : null,
            'fecha_asignacion_cronograma' => !empty($fechaAsignacion) ? $fechaAsignacion : null,
            'estandares' => $this->request->getVar('estandares')
        ];

        $uploadPath = ROOTPATH . 'public/uploads';

        // Manejar la subida de un nuevo logo
        $newLogo = $this->request->getFile('logo');
        if ($newLogo && $newLogo->isValid() && !$newLogo->hasMoved()) {
            $newLogoName = $newLogo->getRandomName();
            $newLogo->move($uploadPath, $newLogoName);
            $this->generateLogoThumbnail($uploadPath, $newLogoName);

            if (!empty($client['logo'])) {
                if (file_exists($uploadPath . '/' . $client['logo'])) {
                    unlink($uploadPath . '/' . $client['logo']);
                }
                $oldThumb = $uploadPath . '/thumb_' . $client['logo'];
                if (file_exists($oldThumb)) {
                    unlink($oldThumb);
                }
            }

            $data['logo'] = $newLogoName;
        }

        // Manejar la subida de una nueva firma
        $newSignature = $this->request->getFile('firma_representante_legal');
        if ($newSignature && $newSignature->isValid() && !$newSignature->hasMoved()) {
            $newSignatureName = $newSignature->getRandomName();
            $newSignature->move($uploadPath, $newSignatureName);

            if (!empty($client['firma_representante_legal']) && file_exists($uploadPath . '/' . $client['firma_representante_legal'])) {
                unlink($uploadPath . '/' . $client['firma_representante_legal']);
            }

            $data['firma_representante_legal'] = $newSignatureName;
        }

        // Manejar archivos de documentos (RUT, Cámara, Cédula doc, Oferta)
        $fileFields = ['rut', 'camara_comercio', 'cedula_rep_legal_doc', 'oferta_comercial'];
        foreach ($fileFields as $field) {
            $newFile = $this->request->getFile($field);
            if ($newFile && $newFile->isValid() && !$newFile->hasMoved()) {
                $newFileName = $newFile->getRandomName();
                $newFile->move($uploadPath, $newFileName);

                if (!empty($client[$field]) && file_exists($uploadPath . '/' . $client[$field])) {
                    unlink($uploadPath . '/' . $client[$field]);
                }

                $data[$field] = $newFileName;
            }
        }

        // Guardar los datos actualizados
        if ($clientModel->update($id, $data)) {
            return redirect()->to('/listClients')->with('msg', 'Cliente actualizado exitosamente');
        } else {
            return redirect()->to('/editClient/' . $id)->with('msg', 'Error al actualizar cliente');
        }
    }

    // ─── Acciones de estado del cliente ────────────────────────────────────────

    /**
     * Reactivar cliente: pone estado=activo y limpia campos de tbl_clientes
     * conservando nombre_cliente, nit_cliente, fecha_ingreso y logo.
     * No elimina registros de tablas relacionadas (historial intacto).
     */
    public function reactivarCliente($id)
    {
        $clientModel = new ClientModel();
        $client = $clientModel->find($id);

        if (!$client) {
            return redirect()->to('/listClients')->with('error', 'Cliente no encontrado.');
        }

        $db = \Config\Database::connect();

        $db->query("UPDATE tbl_clientes SET
            usuario = NULL,
            password = NULL,
            correo_cliente = NULL,
            telefono_1_cliente = NULL,
            telefono_2_cliente = NULL,
            direccion_cliente = NULL,
            persona_contacto_compras = NULL,
            codigo_actividad_economica = NULL,
            nombre_rep_legal = NULL,
            cedula_rep_legal = NULL,
            fecha_fin_contrato = NULL,
            ciudad_cliente = NULL,
            estado = 'activo',
            firma_representante_legal = NULL,
            estandares = NULL
            WHERE id_cliente = ?", [$id]);

        return redirect()->to('/editClient/' . $id)->with('msg', 'Cliente reactivado exitosamente.');
    }

    /**
     * Retirar cliente: pone estado=inactivo y cierra todas sus actividades
     * en las 4 tablas con el estado CERRADA POR FIN CONTRATO.
     */
    public function retirarCliente($id)
    {
        $clientModel = new ClientModel();
        $client      = $clientModel->find($id);

        if (!$client) {
            return redirect()->to('/listClients')->with('error', 'Cliente no encontrado.');
        }

        $db = \Config\Database::connect();

        // Verificar si tbl_vencimientos_mantenimientos existe en este entorno ANTES de abrir la transacción.
        // (En CI4, si una query falla dentro de transStart(), la transacción queda marcada como fallida
        //  incluso si capturas la excepción con try/catch.)
        $vencimientosExiste = $db->query("SHOW TABLES LIKE 'tbl_vencimientos_mantenimientos'")->getNumRows() > 0;

        $db->transStart();

        $db->query("UPDATE tbl_pta_cliente SET estado_actividad = 'CERRADA POR FIN CONTRATO' WHERE id_cliente = ?", [$id]);
        $db->query("UPDATE tbl_cronog_capacitacion SET estado = 'CERRADA POR FIN CONTRATO' WHERE id_cliente = ?", [$id]);
        $db->query("UPDATE tbl_pendientes SET estado = 'CERRADA POR FIN CONTRATO' WHERE id_cliente = ?", [$id]);

        if ($vencimientosExiste) {
            $db->query("UPDATE tbl_vencimientos_mantenimientos SET estado_actividad = 'CERRADA POR FIN CONTRATO' WHERE id_cliente = ?", [$id]);
        }

        $db->query("UPDATE tbl_clientes SET estado = 'inactivo' WHERE id_cliente = ?", [$id]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/editClient/' . $id)->with('error', 'Error al retirar el cliente. Intente de nuevo.');
        }

        // ── Comunicado oficial: terminación de respaldo SST ──────────────────
        // Se envía automáticamente al cliente notificando que el consultor
        // ya no está autorizado para actuar en SST en su nombre.
        $this->_enviarComunicadoFinAutorizacionSST($client);

        return redirect()->to('/editClient/' . $id)->with('msg', 'Cliente retirado. Todas sus actividades fueron marcadas como CERRADA POR FIN CONTRATO. Comunicado oficial enviado al cliente.');
    }

    /**
     * Envía el comunicado oficial de terminación de respaldo SST al cliente.
     * Se invoca automáticamente desde retirarCliente().
     */
    private function _enviarComunicadoFinAutorizacionSST(array $client): void
    {
        $idCliente = $client['id_cliente'] ?? 0;

        $apiKey = env('SENDGRID_API_KEY');
        if (empty($apiKey)) {
            log_message('error', "SENDGRID_API_KEY no configurada — Comunicado Fin Autorización SST no enviado para cliente {$idCliente}.");
            return;
        }

        // ── Datos del cliente ────────────────────────────────────────────────
        $nombreCliente = $client['nombre_cliente'] ?? 'Sin nombre';
        $nitCliente    = $client['nit_cliente']    ?? 'Sin NIT';
        $ciudadCliente = $client['ciudad_cliente'] ?? '';
        $correoCliente = $client['correo_cliente'] ?? '';

        // ── Datos del consultor asignado ─────────────────────────────────────
        $consultor        = null;
        $nombreConsultor  = 'Consultor Cycloid';
        $cedulaConsultor  = 'No registrada';
        $licenciaConsultor = 'No registrada';
        $correoConsultor  = '';

        if (!empty($client['id_consultor'])) {
            $consultantModel = new ConsultantModel();
            $consultor = $consultantModel->find($client['id_consultor']);
        }

        if ($consultor) {
            $nombreConsultor   = $consultor['nombre_consultor']  ?? 'Consultor Cycloid';
            $cedulaConsultor   = $consultor['cedula_consultor']  ?? 'No registrada';
            $licenciaConsultor = $consultor['numero_licencia']   ?? 'No registrada';
            $correoConsultor   = $consultor['correo_consultor']  ?? '';
        }

        // ── Construir HTML del email ─────────────────────────────────────────
        $fechaEmision = date('d \d\e F \d\e Y');

        $htmlEmail = view('emails/fin_autorizacion_sst', [
            'nombreCliente'    => $nombreCliente,
            'nitCliente'       => $nitCliente,
            'ciudadCliente'    => $ciudadCliente,
            'nombreConsultor'  => $nombreConsultor,
            'cedulaConsultor'  => $cedulaConsultor,
            'licenciaConsultor' => $licenciaConsultor,
            'fechaEmision'     => $fechaEmision,
        ]);

        // ── Destinatarios ────────────────────────────────────────────────────
        // TO: el cliente (quien debe dejar de usar las credenciales)
        // CC: el consultor notificado + equipo interno Cycloid

        if (!empty($correoCliente)) {
            $toList = [['email' => $correoCliente, 'name' => $nombreCliente]];
        } else {
            // Sin correo de cliente: enviamos solo a internos
            $toList = [['email' => 'head.consultant.cycloidtalent@gmail.com', 'name' => 'Head Consultant Cycloid']];
            log_message('warning', "Comunicado Fin Autorización SST cliente {$idCliente}: sin correo registrado. Enviado solo a internos.");
        }

        // CC: el consultor (ve que fue notificado formalmente)
        $ccList = [];
        if (!empty($correoConsultor)) {
            $ccList[] = ['email' => $correoConsultor, 'name' => $nombreConsultor];
        }

        // BCC: equipo interno Cycloid (copia oculta)
        $bccList = [
            ['email' => 'diana.cuestas@cycloidtalent.com',       'name' => 'Diana Cuestas'],
            ['email' => 'natalia.jimenez@cycloidtalent.com',     'name' => 'Natalia Jiménez'],
            ['email' => 'edison.cuervo@cycloidtalent.com',       'name' => 'Edison Cuervo'],
            ['email' => 'head.consultant.cycloidtalent@gmail.com', 'name' => 'Head Consultant Cycloid'],
        ];

        $personalization = [
            'to'      => $toList,
            'subject' => 'Comunicado Oficial — Terminación de Respaldo SST | ' . $nombreCliente . ' | ' . date('d/m/Y'),
            'bcc'     => $bccList,
        ];
        if (!empty($ccList)) {
            $personalization['cc'] = $ccList;
        }

        $payload = [
            'personalizations' => [$personalization],
            'from' => [
                'email' => env('SENDGRID_FROM_EMAIL', 'notificacion.cycloidtalent@cycloidtalent.com'),
                'name'  => env('SENDGRID_FROM_NAME', 'Enterprise SST'),
            ],
            'content' => [
                ['type' => 'text/html', 'value' => $htmlEmail],
            ],
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT,        60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            log_message('info', "Comunicado Fin Autorización SST enviado para cliente {$idCliente} ({$nombreCliente}). HTTP {$httpCode}");
        } else {
            log_message('error', "SendGrid Error (Fin Autorización SST cliente {$idCliente}) — HTTP {$httpCode}: {$response} | cURL: {$curlError}");
        }
    }

    /**
     * Marcar cliente como pendiente: solo cambia estado en tbl_clientes.
     */
    public function marcarPendienteCliente($id)
    {
        $clientModel = new ClientModel();
        $client = $clientModel->find($id);

        if (!$client) {
            return redirect()->to('/listClients')->with('error', 'Cliente no encontrado.');
        }

        $clientModel->update($id, ['estado' => 'pendiente']);

        return redirect()->to('/editClient/' . $id)->with('msg', 'Cliente marcado como Pendiente.');
    }

    /**
     * Emitir Paz y Salvo por Todo Concepto.
     * Valida que no haya actividades abiertas en las 4 tablas antes de enviar el email.
     */
    public function emitirPazYSalvo($id)
    {
        $clientModel    = new ClientModel();
        $consultantModel = new ConsultantModel();

        $client = $clientModel->find($id);
        if (!$client) {
            return redirect()->to('/listClients')->with('error', 'Cliente no encontrado.');
        }

        $db = \Config\Database::connect();

        // ── Validación estricta: no debe haber actividades abiertas ──────────

        $actividadesAbiertas = [];

        $ptaAbiertas = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $id)
            ->whereIn('estado_actividad', ['ABIERTA', 'GESTIONANDO'])
            ->countAllResults();
        if ($ptaAbiertas > 0) {
            $actividadesAbiertas[] = "Plan de Trabajo Anual ({$ptaAbiertas} actividad(es) abierta(s))";
        }

        $cronogAbiertas = $db->table('tbl_cronog_capacitacion')
            ->where('id_cliente', $id)
            ->whereIn('estado', ['PROGRAMADA', 'REPROGRAMADA'])
            ->countAllResults();
        if ($cronogAbiertas > 0) {
            $actividadesAbiertas[] = "Cronograma de Capacitación ({$cronogAbiertas} sesión(es) pendiente(s))";
        }

        $pendientesAbiertos = $db->table('tbl_pendientes')
            ->where('id_cliente', $id)
            ->whereIn('estado', ['ABIERTA', 'SIN RESPUESTA DEL CLIENTE'])
            ->countAllResults();
        if ($pendientesAbiertos > 0) {
            $actividadesAbiertas[] = "Pendientes ({$pendientesAbiertos} ítem(s) sin cerrar)";
        }

        try {
            $vencimientosAbiertos = $db->table('tbl_vencimientos_mantenimientos')
                ->where('id_cliente', $id)
                ->where('estado_actividad', 'sin ejecutar')
                ->countAllResults();
            if ($vencimientosAbiertos > 0) {
                $actividadesAbiertas[] = "Vencimientos y Mantenimientos ({$vencimientosAbiertos} ítem(s) sin ejecutar)";
            }
        } catch (\Exception $e) {
            // Tabla no existe en este entorno
        }

        if (!empty($actividadesAbiertas)) {
            $detalle = implode('; ', $actividadesAbiertas);
            return redirect()->to('/editClient/' . $id)
                ->with('error', 'No se puede emitir el Paz y Salvo. Existen actividades abiertas: ' . $detalle);
        }

        // ── Obtener datos del consultor asignado ─────────────────────────────

        $consultor = null;
        if (!empty($client['id_consultor'])) {
            $consultor = $consultantModel->find($client['id_consultor']);
        }

        // ── Construir y enviar email vía SendGrid ────────────────────────────

        $apiKey = env('SENDGRID_API_KEY');
        if (empty($apiKey)) {
            log_message('error', 'SENDGRID_API_KEY no configurada — Paz y Salvo no enviado.');
            return redirect()->to('/editClient/' . $id)
                ->with('error', 'Error de configuración: no se pudo enviar el email. Contacte al administrador.');
        }

        $fechaEmision    = date('d \d\e F \d\e Y', strtotime('now'));
        $nombreCliente   = $client['nombre_cliente']   ?? 'Sin nombre';
        $nitCliente      = $client['nit_cliente']      ?? 'Sin NIT';
        $ciudadCliente   = $client['ciudad_cliente']   ?? '';
        $correoCliente   = $client['correo_cliente']   ?? '';
        $nombreConsultor = $consultor ? ($consultor['nombre_consultor'] ?? 'Consultor Cycloid') : 'Consultor Cycloid';
        $correoConsultor = $consultor ? ($consultor['correo_consultor'] ?? '') : '';

        $htmlEmail = view('emails/paz_y_salvo', [
            'nombreCliente'   => $nombreCliente,
            'nitCliente'      => $nitCliente,
            'ciudadCliente'   => $ciudadCliente,
            'nombreConsultor' => $nombreConsultor,
            'fechaEmision'    => $fechaEmision,
        ]);

        $ccList = [];
        if (!empty($correoConsultor)) {
            $ccList[] = ['email' => $correoConsultor, 'name' => $nombreConsultor];
        }
        $ccList[] = ['email' => 'head.consultant.cycloidtalent@gmail.com',  'name' => 'Head Consultant Cycloid'];
        $ccList[] = ['email' => 'diana.cuestas@cycloidtalent.com',          'name' => 'Diana Cuestas'];

        $personalization = [
            'subject' => 'Paz y Salvo por Todo Concepto — ' . $nombreCliente . ' | ' . date('d/m/Y'),
        ];

        if (!empty($correoCliente)) {
            $personalization['to'] = [['email' => $correoCliente, 'name' => $nombreCliente]];
        } else {
            // Sin correo de cliente: enviamos solo a los CC como destinatarios principales
            $personalization['to'] = [['email' => 'head.consultant.cycloidtalent@gmail.com', 'name' => 'Head Consultant Cycloid']];
            log_message('warning', "Paz y Salvo cliente {$id}: sin correo registrado. Enviado solo a internos.");
        }

        if (!empty($ccList)) {
            $personalization['cc'] = $ccList;
        }

        $payload = [
            'personalizations' => [$personalization],
            'from' => [
                'email' => env('SENDGRID_FROM_EMAIL', 'notificacion.cycloidtalent@cycloidtalent.com'),
                'name'  => env('SENDGRID_FROM_NAME', 'Enterprise SST'),
            ],
            'content' => [
                ['type' => 'text/html', 'value' => $htmlEmail],
            ],
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT,        60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            log_message('info', "Paz y Salvo enviado exitosamente para cliente {$id} ({$nombreCliente}). HTTP {$httpCode}");
            return redirect()->to('/editClient/' . $id)
                ->with('msg', 'Paz y Salvo enviado exitosamente al cliente' . (!empty($correoCliente) ? " ({$correoCliente})" : '') . ' y al equipo interno.');
        } else {
            log_message('error', "SendGrid Error (Paz y Salvo cliente {$id}) — HTTP {$httpCode}: {$response} | cURL: {$curlError}");
            return redirect()->to('/editClient/' . $id)
                ->with('error', 'Error al enviar el email. Código HTTP: ' . $httpCode . '. Revise los logs del sistema.');
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

    // ─── Email de bienvenida y reenvío de credenciales ─────────────────────────

    /**
     * Reenviar credenciales: genera contraseña temporal, actualiza BD y envía email.
     * Retorna JSON para llamada AJAX desde list_clients.php.
     */
    public function resendCredentials($id)
    {
        $clientModel = new ClientModel();
        $client = $clientModel->find($id);

        if (!$client) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado.']);
        }

        $correo = $client['correo_cliente'] ?? '';
        if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['success' => false, 'message' => 'El cliente no tiene un correo válido registrado.']);
        }

        // Generar contraseña temporal
        $tempPassword = 'Temp' . rand(1000, 9999) . '!';

        // Actualizar en BD
        $clientModel->update($id, ['password' => password_hash($tempPassword, PASSWORD_BCRYPT)]);

        // Obtener nombre del consultor
        $consultantModel = new ConsultantModel();
        $consultor = !empty($client['id_consultor']) ? $consultantModel->find($client['id_consultor']) : null;
        $nombreConsultor = $consultor ? ($consultor['nombre_consultor'] ?? 'Consultor SST') : 'Consultor SST';

        $emailSent = $this->sendWelcomeCredentialsEmail(
            $client['nombre_cliente'],
            $client['usuario'],
            $tempPassword,
            $correo,
            $nombreConsultor,
            true // isResend
        );

        if ($emailSent) {
            log_message('info', "Credenciales reenviadas a {$correo} para cliente ID {$id}");
            return $this->response->setJSON([
                'success' => true,
                'message' => "Credenciales enviadas exitosamente a {$correo}. Se generó una nueva contraseña temporal."
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al enviar el email. Revise los logs del sistema.'
            ]);
        }
    }

    /**
     * Enviar email de felicitación al equipo interno por cliente nuevo ganado.
     */
    private function enviarEmailFelicitacionClienteNuevo($nombreCliente, $nit, $ciudad, $vendedor, $tipoServicio)
    {
        $apiKey = env('SENDGRID_API_KEY');
        if (empty($apiKey)) {
            log_message('error', 'SENDGRID_API_KEY no configurada — email de felicitación no enviado.');
            return false;
        }

        $destinatarios = [
            ['email' => 'natalia.jimenez@cycloidtalent.com', 'name' => 'Natalia Jimenez'],
            ['email' => 'diana.cuestas@cycloidtalent.com', 'name' => 'Diana Cuestas'],
            ['email' => 'edison.cuervo@cycloidtalent.com', 'name' => 'Edison Cuervo'],
            ['email' => 'solangel.cuervo@cycloidtalent.com', 'name' => 'Solangel Cuervo'],
            ['email' => 'eleyson.segura@cycloidtalent.com', 'name' => 'Eleyson Segura'],
        ];

        $fecha = date('d/m/Y');
        $anio = date('Y');
        $nombreEsc = htmlspecialchars($nombreCliente);
        $nitEsc = htmlspecialchars($nit);
        $ciudadEsc = htmlspecialchars($ciudad);
        $vendedorEsc = htmlspecialchars($vendedor);
        $tipoEsc = htmlspecialchars($tipoServicio);

        $htmlContent = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 30px 0;">
                <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">

                            <!-- Header celebración -->
                            <tr>
                                <td style="background: linear-gradient(135deg, #bd9751, #d4a94d, #e6c066); padding: 40px 30px; text-align: center;">
                                    <p style="font-size: 48px; margin: 0 0 10px;">&#127881;&#127942;&#127881;</p>
                                    <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">NUEVO CLIENTE GANADO</h1>
                                    <p style="color: rgba(255,255,255,0.95); margin: 10px 0 0; font-size: 16px; font-weight: 500;">Felicitaciones a todo el equipo</p>
                                </td>
                            </tr>

                            <!-- Mensaje principal -->
                            <tr>
                                <td style="padding: 35px 30px 15px;">
                                    <p style="font-size: 16px; color: #2c3e50; line-height: 1.7; margin: 0 0 15px;">
                                        Nos complace informar que hemos cerrado exitosamente un <strong>nuevo cliente</strong>. Este logro es resultado del esfuerzo y dedicaci&oacute;n de todo nuestro equipo.
                                    </p>
                                </td>
                            </tr>

                            <!-- Datos del cliente -->
                            <tr>
                                <td style="padding: 0 30px 25px;">
                                    <table width="100%" cellpadding="0" cellspacing="0" style="background: linear-gradient(135deg, #faf8f3, #f5f0e6); border: 2px solid #d4a94d; border-radius: 10px; overflow: hidden;">
                                        <tr>
                                            <td style="background: #1c2437; padding: 12px 20px;">
                                                <p style="color: #d4a94d; margin: 0; font-size: 15px; font-weight: bold;">Datos del Nuevo Cliente</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 20px;">
                                                <table width="100%" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td style="padding: 10px 0; border-bottom: 1px solid #e8dcc8;">
                                                            <span style="color: #7a6b4f; font-size: 12px; font-weight: bold; text-transform: uppercase;">Cliente</span><br>
                                                            <span style="color: #1c2437; font-size: 17px; font-weight: bold;">' . $nombreEsc . '</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 10px 0; border-bottom: 1px solid #e8dcc8;">
                                                            <span style="color: #7a6b4f; font-size: 12px; font-weight: bold; text-transform: uppercase;">NIT</span><br>
                                                            <span style="color: #1c2437; font-size: 15px;">' . $nitEsc . '</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 10px 0; border-bottom: 1px solid #e8dcc8;">
                                                            <span style="color: #7a6b4f; font-size: 12px; font-weight: bold; text-transform: uppercase;">Ciudad</span><br>
                                                            <span style="color: #1c2437; font-size: 15px;">' . $ciudadEsc . '</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 10px 0; border-bottom: 1px solid #e8dcc8;">
                                                            <span style="color: #7a6b4f; font-size: 12px; font-weight: bold; text-transform: uppercase;">Tipo de Servicio</span><br>
                                                            <span style="color: #bd9751; font-size: 15px; font-weight: bold;">' . $tipoEsc . '</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 10px 0; border-bottom: 1px solid #e8dcc8;">
                                                            <span style="color: #7a6b4f; font-size: 12px; font-weight: bold; text-transform: uppercase;">Vendedor</span><br>
                                                            <span style="color: #1c2437; font-size: 15px; font-weight: bold;">' . $vendedorEsc . '</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 10px 0;">
                                                            <span style="color: #7a6b4f; font-size: 12px; font-weight: bold; text-transform: uppercase;">Fecha de Registro</span><br>
                                                            <span style="color: #1c2437; font-size: 15px;">' . $fecha . '</span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Mensaje motivacional -->
                            <tr>
                                <td style="padding: 0 30px 25px;">
                                    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f0f8e8; border-radius: 8px; border-left: 4px solid #28a745;">
                                        <tr>
                                            <td style="padding: 18px 20px;">
                                                <p style="margin: 0; font-size: 15px; color: #2c5f2d; line-height: 1.6;">
                                                    <strong>&#128170; Seguimos creciendo juntos.</strong> Cada nuevo cliente es una muestra de la confianza que genera nuestro trabajo. &iexcl;Felicitaciones al equipo comercial y a todos los que hacen posible este resultado!
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Despedida -->
                            <tr>
                                <td style="padding: 0 30px 20px;">
                                    <p style="font-size: 15px; color: #4a4a4a; margin-bottom: 5px;">Con orgullo,</p>
                                    <p style="font-size: 16px; color: #1c2437; font-weight: bold; margin: 0;">Equipo Cycloid Talent SAS</p>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style="background-color: #1c2437; padding: 25px 30px; text-align: center;">
                                    <p style="margin: 0 0 5px; color: #d4a94d; font-size: 14px; font-weight: bold;">Cycloid Talent SAS</p>
                                    <p style="margin: 0 0 5px; color: rgba(255,255,255,0.7); font-size: 12px;">NIT: 901.653.912</p>
                                    <p style="margin: 0 0 10px; color: rgba(255,255,255,0.7); font-size: 12px;">Asesores especializados en SG-SST</p>
                                    <p style="margin: 0; color: rgba(255,255,255,0.5); font-size: 11px;">&copy; ' . $anio . ' Cycloid Talent SAS - Todos los derechos reservados</p>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';

        $payload = [
            'personalizations' => [[
                'to' => $destinatarios,
                'subject' => 'NUEVO CLIENTE GANADO — ' . $nombreCliente,
            ]],
            'from' => [
                'email' => env('SENDGRID_FROM_EMAIL', 'notificacion.cycloidtalent@cycloidtalent.com'),
                'name'  => env('SENDGRID_FROM_NAME', 'Enterprise SST'),
            ],
            'content' => [
                ['type' => 'text/html', 'value' => $htmlContent],
            ],
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            log_message('info', "Email de felicitación enviado para cliente: {$nombreCliente}");
            return true;
        }

        log_message('error', "SendGrid Error (felicitación) — HTTP {$httpCode}: {$response} | cURL: {$curlError}");
        return false;
    }

    /**
     * Enviar email de bienvenida/credenciales vía SendGrid API v3 (cURL).
     */
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

        $htmlContent = $this->getWelcomeEmailTemplate($nombreCliente, $usuario, $password, $correo, $consultorNombre, $isResend);

        $payload = [
            'personalizations' => [[
                'to' => [['email' => $correo, 'name' => $nombreCliente]],
                'subject' => $subject,
            ]],
            'from' => [
                'email' => env('SENDGRID_FROM_EMAIL', 'notificacion.cycloidtalent@cycloidtalent.com'),
                'name'  => env('SENDGRID_FROM_NAME', 'Enterprise SST'),
            ],
            'content' => [
                ['type' => 'text/html', 'value' => $htmlContent],
            ],
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        log_message('error', "SendGrid Error (credenciales cliente) — HTTP {$httpCode}: {$response} | cURL: {$curlError}");
        return false;
    }

    /**
     * Template HTML del email de bienvenida / credenciales.
     */
    private function getWelcomeEmailTemplate($nombreCliente, $usuario, $password, $correo, $consultorNombre, $isResend = false)
    {
        $loginUrl = base_url('/login');
        $anio     = date('Y');

        if ($isResend) {
            $encabezado  = 'Tus nuevas credenciales est&aacute;n listas';
            $subtitulo   = 'Enterprisesst &mdash; Propiedad Horizontal';
            $intro       = 'Hola <strong>' . htmlspecialchars($nombreCliente) . '</strong>, hemos actualizado las credenciales de tu cuenta. A partir de ahora puedes ingresar con los datos que te compartimos a continuaci&oacute;n.';
            $cierreTexto = 'Si no solicitaste este cambio o tienes alguna duda, escr&iacute;benos de inmediato y con gusto te ayudamos.';
        } else {
            $encabezado  = '&iexcl;Bienvenido(a) a Enterprisesst!';
            $subtitulo   = 'Gesti&oacute;n de Propiedad Horizontal &mdash; SG-SST';
            $intro       = 'Hola <strong>' . htmlspecialchars($nombreCliente) . '</strong>, nos alegra mucho tenerte con nosotros.<br><br>
                            A partir de hoy cuentas con una plataforma dedicada para el seguimiento de tu Sistema de Gesti&oacute;n de Seguridad y Salud en el Trabajo.
                            <strong>' . htmlspecialchars($consultorNombre) . '</strong> ser&aacute; tu consultor de cabecera y estar&aacute; disponible para acompa&ntilde;arte en cada etapa del proceso.';
            $cierreTexto = 'Si tienes preguntas o simplemente quieres conocer m&aacute;s sobre tu plataforma, no dudes en escribirnos. &iexcl;Estamos aqu&iacute; para ti!';
        }

        return '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>' . htmlspecialchars($encabezado) . '</title>
</head>
<body style="margin:0;padding:0;background:#f0f2f5;font-family:\'Segoe UI\',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f2f5;padding:40px 16px;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.10);">

      <!-- Header oscuro -->
      <tr>
        <td style="background:#1c2437;padding:36px 40px 28px;text-align:center;">
          <p style="margin:0 0 8px;color:#d4a94d;font-size:11px;letter-spacing:2.5px;text-transform:uppercase;font-weight:700;">' . $subtitulo . '</p>
          <h1 style="margin:0;color:#fff;font-size:26px;font-weight:700;line-height:1.3;">' . $encabezado . '</h1>
        </td>
      </tr>

      <!-- Línea dorada -->
      <tr><td style="background:linear-gradient(90deg,#bd9751,#d4a94d,#c9a04e);height:4px;font-size:0;">&nbsp;</td></tr>

      <!-- Cuerpo -->
      <tr>
        <td style="padding:36px 40px 24px;">
          <p style="margin:0;font-size:15px;color:#3a3a3a;line-height:1.85;">' . $intro . '</p>
        </td>
      </tr>

      <!-- Intro credenciales -->
      <tr>
        <td style="padding:0 40px 12px;">
          <p style="margin:0;font-size:14px;color:#666;">Aquí tienes tus datos para ingresar:</p>
        </td>
      </tr>

      <!-- Caja de credenciales -->
      <tr>
        <td style="padding:0 40px 28px;">
          <table width="100%" cellpadding="0" cellspacing="0" style="border-radius:12px;overflow:hidden;border:1.5px solid #e8dcc8;">
            <tr>
              <td style="background:#f9f5ed;padding:12px 22px;border-bottom:1.5px solid #e8dcc8;">
                <p style="margin:0;font-size:11px;color:#7a6b4f;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;">Credenciales de Acceso</p>
              </td>
            </tr>
            <tr>
              <td style="padding:20px 22px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="padding:10px 0;border-bottom:1px solid #f0e8d8;">
                      <p style="margin:0 0 3px;font-size:10px;color:#bbb;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Correo (usuario de ingreso)</p>
                      <p style="margin:0;font-size:16px;color:#1c2437;font-weight:600;">' . htmlspecialchars($correo) . '</p>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:10px 0;">
                      <p style="margin:0 0 3px;font-size:10px;color:#bbb;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Contrase&ntilde;a</p>
                      <p style="margin:0;font-size:22px;color:#bd9751;font-weight:700;letter-spacing:2px;">' . htmlspecialchars($password) . '</p>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- Nota seguridad -->
      <tr>
        <td style="padding:0 40px 28px;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#fffbf2;border-radius:8px;border-left:4px solid #d4a94d;">
            <tr>
              <td style="padding:13px 18px;">
                <p style="margin:0;font-size:13px;color:#6b5c3e;line-height:1.6;">
                  Recomendamos cambiar tu contrase&ntilde;a la primera vez que ingreses, desde la secci&oacute;n de perfil de tu cuenta.
                </p>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- Botón -->
      <tr>
        <td style="padding:0 40px 36px;text-align:center;">
          <a href="' . $loginUrl . '" style="display:inline-block;background:#d4a94d;color:#1c2437;padding:16px 48px;text-decoration:none;border-radius:8px;font-weight:700;font-size:15px;letter-spacing:0.5px;">Ingresar a mi plataforma &rarr;</a>
        </td>
      </tr>

      <!-- Cierre -->
      <tr>
        <td style="padding:24px 40px 32px;border-top:1px solid #f0f0f0;">
          <p style="margin:0 0 14px;font-size:14px;color:#555;line-height:1.75;">' . $cierreTexto . '</p>
          <p style="margin:0;font-size:14px;color:#555;">Un abrazo,<br>
          <strong style="color:#1c2437;font-size:15px;">Equipo Enterprisesst &mdash; Propiedad Horizontal</strong></p>
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td style="background:#1c2437;padding:20px 40px;text-align:center;">
          <p style="margin:0 0 4px;color:#d4a94d;font-size:13px;font-weight:700;">Cycloid Talent SAS &mdash; NIT 901.653.912</p>
          <p style="margin:0;color:rgba(255,255,255,0.40);font-size:11px;">&copy; ' . $anio . ' Enterprisesst. Todos los derechos reservados.</p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>';
    }

    /**
     * Genera un thumbnail de 150x150 del logo subido.
     * El archivo se guarda como thumb_{filename} en el mismo directorio.
     */
    private function generateLogoThumbnail(string $uploadPath, string $logoName): void
    {
        if (!function_exists('imagecreatefromstring')) {
            return;
        }
        $srcPath = $uploadPath . '/' . $logoName;
        if (!file_exists($srcPath)) {
            return;
        }
        $data = @file_get_contents($srcPath);
        if ($data === false) {
            return;
        }
        $src = @imagecreatefromstring($data);
        if (!$src) {
            return;
        }
        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $size = 150;
        $ratio = min($size / $srcW, $size / $srcH);
        $newW = max(1, (int)($srcW * $ratio));
        $newH = max(1, (int)($srcH * $ratio));
        $thumb = imagecreatetruecolor($newW, $newH);
        // Preserve transparency for PNG
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        imagecopyresampled($thumb, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);
        $destPath = $uploadPath . '/thumb_' . $logoName;
        // Save as JPEG unless original is PNG
        $ext = strtolower(pathinfo($logoName, PATHINFO_EXTENSION));
        if ($ext === 'png') {
            imagepng($thumb, $destPath, 8);
        } else {
            imagejpeg($thumb, $destPath, 82);
        }
        imagedestroy($src);
        imagedestroy($thumb);
    }
}
