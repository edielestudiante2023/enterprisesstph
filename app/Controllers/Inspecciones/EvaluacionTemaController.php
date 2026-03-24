<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\EvaluacionTemaModel;
use App\Models\EvaluacionPreguntaModel;

class EvaluacionTemaController extends BaseController
{
    protected EvaluacionTemaModel $temaModel;
    protected EvaluacionPreguntaModel $preguntaModel;

    public function __construct()
    {
        $this->temaModel     = new EvaluacionTemaModel();
        $this->preguntaModel = new EvaluacionPreguntaModel();
    }

    public function list()
    {
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/evaluacion-tema/list', [
                'temas' => $this->temaModel->getTodosConConteo(),
            ]),
            'title' => 'Temas de Evaluación',
        ]);
    }

    public function create()
    {
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/evaluacion-tema/form', [
                'tema'      => null,
                'preguntas' => [],
            ]),
            'title' => 'Nuevo Tema',
        ]);
    }

    public function store()
    {
        $idTema = $this->temaModel->insert([
            'nombre'      => trim($this->request->getPost('nombre')),
            'descripcion' => trim($this->request->getPost('descripcion') ?? ''),
            'estado'      => 'activo',
        ]);
        $idTema = $this->temaModel->getInsertID();

        $this->guardarPreguntas($idTema);

        return redirect()->to('/inspecciones/evaluacion-tema/edit/' . $idTema)
            ->with('msg', 'Tema creado con las preguntas.');
    }

    public function edit(int $id)
    {
        $tema = $this->temaModel->find($id);
        if (!$tema) {
            return redirect()->to('/inspecciones/evaluacion-tema')->with('error', 'Tema no encontrado.');
        }

        $preguntas = $this->preguntaModel->getConOpcionesByTema($id);

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/evaluacion-tema/form', [
                'tema'      => $tema,
                'preguntas' => $preguntas,
            ]),
            'title' => 'Editar Tema',
        ]);
    }

    public function update(int $id)
    {
        $tema = $this->temaModel->find($id);
        if (!$tema) {
            return redirect()->to('/inspecciones/evaluacion-tema')->with('error', 'Tema no encontrado.');
        }

        $this->temaModel->update($id, [
            'nombre'      => trim($this->request->getPost('nombre')),
            'descripcion' => trim($this->request->getPost('descripcion') ?? ''),
            'estado'      => $this->request->getPost('estado') ?: 'activo',
        ]);

        // Borrar preguntas y opciones existentes y re-insertar
        $db = \Config\Database::connect();
        $idsPreguntas = $db->table('tbl_evaluacion_pregunta')
            ->where('id_tema', $id)
            ->get()->getResultArray();
        $ids = array_column($idsPreguntas, 'id');
        if (!empty($ids)) {
            $db->table('tbl_evaluacion_opcion')->whereIn('id_pregunta', $ids)->delete();
        }
        $db->table('tbl_evaluacion_pregunta')->where('id_tema', $id)->delete();

        $this->guardarPreguntas($id);

        return redirect()->to('/inspecciones/evaluacion-tema/edit/' . $id)
            ->with('msg', 'Tema y preguntas actualizados.');
    }

    public function delete(int $id)
    {
        $db  = \Config\Database::connect();
        $ids = array_column(
            $db->table('tbl_evaluacion_pregunta')->where('id_tema', $id)->get()->getResultArray(),
            'id'
        );
        if (!empty($ids)) {
            $db->table('tbl_evaluacion_opcion')->whereIn('id_pregunta', $ids)->delete();
        }
        $db->table('tbl_evaluacion_pregunta')->where('id_tema', $id)->delete();
        $this->temaModel->delete($id);

        return redirect()->to('/inspecciones/evaluacion-tema')->with('msg', 'Tema eliminado.');
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    private function guardarPreguntas(int $idTema): void
    {
        $textos   = $this->request->getPost('p_texto')    ?? [];
        $correctas = $this->request->getPost('p_correcta') ?? [];
        $opcA     = $this->request->getPost('p_opc_a')    ?? [];
        $opcB     = $this->request->getPost('p_opc_b')    ?? [];
        $opcC     = $this->request->getPost('p_opc_c')    ?? [];
        $opcD     = $this->request->getPost('p_opc_d')    ?? [];

        $db       = \Config\Database::connect();
        $stmtP    = $db->prepare(fn($db) => $db->table('tbl_evaluacion_pregunta')->set([
            'id_tema' => 0, 'orden' => 0, 'texto' => '', 'correcta' => '',
            'created_at' => '', 'updated_at' => '',
        ])->getCompiledInsert());

        foreach ($textos as $i => $texto) {
            $texto = trim($texto);
            if (!$texto) continue;

            $db->table('tbl_evaluacion_pregunta')->insert([
                'id_tema'    => $idTema,
                'orden'      => $i + 1,
                'texto'      => $texto,
                'correcta'   => $correctas[$i] ?? 'a',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $idPregunta = $db->insertID();

            foreach (['a' => $opcA, 'b' => $opcB, 'c' => $opcC, 'd' => $opcD] as $letra => $arr) {
                $textoOpc = trim($arr[$i] ?? '');
                if (!$textoOpc) continue;
                $db->table('tbl_evaluacion_opcion')->insert([
                    'id_pregunta' => $idPregunta,
                    'letra'       => $letra,
                    'texto'       => $textoOpc,
                ]);
            }
        }
    }
}
