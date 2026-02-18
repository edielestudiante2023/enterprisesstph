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
use App\Libraries\WorkPlanLibrary;
use App\Libraries\TrainingLibrary;
use App\Libraries\StandardsLibrary;
use CodeIgniter\Controller;

class ConsultantController extends Controller
{
    public function index()
    {
        return view('consultant/dashboard');
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

        $data = [
            'datetime' => date('Y-m-d H:i:s'),
            'fecha_ingreso' => $this->request->getVar('fecha_ingreso'),
            'nit_cliente' => $this->request->getVar('nit_cliente'),
            'nombre_cliente' => $this->request->getVar('nombre_cliente'),
            'usuario' => $this->request->getVar('usuario'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_BCRYPT),
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
            // Obtener el ID del cliente recién creado
            $clientId = $clientModel->getInsertID();

            // Recuperar el NIT del cliente recién guardado
            $nitCliente = $this->request->getVar('nit_cliente');

            // Crear la carpeta para el cliente en public/uploads/{nit_cliente}
            $uploadPath = ROOTPATH . 'public/uploads/' . $nitCliente;

            if (!is_dir($uploadPath)) { // Verificar si la carpeta ya existe
                mkdir($uploadPath, 0777, true); // Crear la carpeta con permisos 0777
            }

            // Los documentos SST se consumen directamente desde DocumentLibrary (app/Libraries/DocumentLibrary.php)
            // No se insertan registros en BD, todos los clientes leen de la misma librería estática

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

            session()->setFlashdata('msg', 'Cliente agregado exitosamente.');

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
        $data = [
            'fecha_ingreso' => $this->request->getVar('fecha_ingreso'),
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

    // ─── Acciones de estado del cliente ────────────────────────────────────────

    /**
     * Reactivar cliente: pone estado=activo y borra todos sus datos relacionados
     * conservando solo nombre_cliente, nit_cliente y fecha_ingreso en tbl_clientes.
     */
    public function reactivarCliente($id)
    {
        $clientModel = new ClientModel();
        $client = $clientModel->find($id);

        if (!$client) {
            return redirect()->to('/listClients')->with('error', 'Cliente no encontrado.');
        }

        $db = \Config\Database::connect();

        $vencimientosExisteReactivar = $db->query("SHOW TABLES LIKE 'tbl_vencimientos_mantenimientos'")->getNumRows() > 0;

        $db->transStart();

        // Borrar registros relacionados — SQL directo para evitar acumulación de estado en builder
        $db->query("DELETE FROM tbl_pta_cliente WHERE id_cliente = ?", [$id]);
        $db->query("DELETE FROM tbl_cronog_capacitacion WHERE id_cliente = ?", [$id]);
        $db->query("DELETE FROM tbl_pendientes WHERE id_cliente = ?", [$id]);

        if ($vencimientosExisteReactivar) {
            $db->query("DELETE FROM tbl_vencimientos_mantenimientos WHERE id_cliente = ?", [$id]);
        }

        // Limpiar todos los campos de tbl_clientes EXCEPTO los 3 históricos
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
            logo = NULL,
            firma_representante_legal = NULL,
            estandares = NULL
            WHERE id_cliente = ?", [$id]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/editClient/' . $id)->with('error', 'Error al reactivar el cliente. Intente de nuevo.');
        }

        return redirect()->to('/editClient/' . $id)->with('msg', 'Cliente reactivado exitosamente. Datos relacionados eliminados.');
    }

    /**
     * Retirar cliente: pone estado=inactivo y cierra todas sus actividades
     * en las 4 tablas con el estado CERRADA POR FIN CONTRATO.
     */
    public function retirarCliente($id)
    {
        $clientModel = new ClientModel();
        $client = $clientModel->find($id);

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

        return redirect()->to('/editClient/' . $id)->with('msg', 'Cliente retirado. Todas sus actividades fueron marcadas como CERRADA POR FIN CONTRATO.');
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
}
