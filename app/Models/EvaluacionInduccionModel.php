<?php

namespace App\Models;

use CodeIgniter\Model;

class EvaluacionInduccionModel extends Model
{
    protected $table      = 'tbl_evaluacion_induccion';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_asistencia_induccion', 'id_cliente', 'titulo', 'token', 'estado',
    ];
    protected $useTimestamps = true;

    /**
     * Preguntas y respuestas correctas de la Evaluación de Inducción SST - PH.
     * key 'correcta' usa la letra de la opción correcta ('a','b','c','d').
     */
    public const PREGUNTAS = [
        [
            'texto'   => '¿Cuál es el principal objetivo del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST)?',
            'opciones' => [
                'a' => 'Maximizar los beneficios económicos.',
                'b' => 'Prevenir enfermedades en los residentes.',
                'c' => 'Minimizar los riesgos legales y de la propiedad horizontal en caso de un eventual accidente.',
                'd' => 'Fomentar el consumo de alcohol y tabaco en el trabajo.',
            ],
            'correcta' => 'c',
        ],
        [
            'texto'   => '¿Quiénes deben implementar el SG-SST en una propiedad horizontal?',
            'opciones' => [
                'a' => 'Solo los residentes.',
                'b' => 'Solo los empleados.',
                'c' => 'Solo los contratistas.',
                'd' => 'Los contratantes de personal bajo modalidad de contrato civil, comercial o administrativo.',
            ],
            'correcta' => 'd',
        ],
        [
            'texto'   => '¿Qué es un "peligro" en el contexto de seguridad y salud en el trabajo?',
            'opciones' => [
                'a' => 'Un evento inesperado.',
                'b' => 'Un acto inseguro.',
                'c' => 'Una fuente, situación o acto con potencial de daño.',
                'd' => 'Un accidente laboral.',
            ],
            'correcta' => 'c',
        ],
        [
            'texto'   => '¿Cuál es la diferencia entre un "peligro" y un "riesgo"?',
            'opciones' => [
                'a' => 'No hay diferencia.',
                'b' => 'El riesgo es un acto inseguro.',
                'c' => 'El peligro es un evento inesperado.',
                'd' => 'El riesgo es la combinación de la probabilidad de que ocurra un peligro y la severidad de la lesión que puede causar.',
            ],
            'correcta' => 'd',
        ],
        [
            'texto'   => '¿Qué función desempeña la "Brigada de Emergencia" en la propiedad horizontal?',
            'opciones' => [
                'a' => 'Mantener orden y limpieza en las áreas comunes.',
                'b' => 'Promover la cultura de la prevención y reaccionar en caso de emergencias como sismos o incendios.',
                'c' => 'Organizar fiestas y eventos.',
                'd' => 'Gestionar la seguridad en las zonas comunes.',
            ],
            'correcta' => 'b',
        ],
        [
            'texto'   => '¿Cuál es el propósito de un "FURAT" en el contexto de seguridad y salud en el trabajo?',
            'opciones' => [
                'a' => 'Registrar la asistencia de los residentes a cursos de seguridad.',
                'b' => 'Informar a la ARL sobre la ocurrencia de un accidente de trabajo.',
                'c' => 'Realizar pruebas de alcoholemia a los trabajadores.',
                'd' => 'Organizar simulacros de evacuación.',
            ],
            'correcta' => 'b',
        ],
        [
            'texto'   => '¿Qué debe exigir la copropiedad en cuanto a las dotaciones de proveedores y contratistas?',
            'opciones' => [
                'a' => 'Equipos de oficina.',
                'b' => 'Programas de entretenimiento para residentes.',
                'c' => 'Programas de capacitación para empleados.',
                'd' => 'Equipos de protección personal (EPP) adecuados.',
            ],
            'correcta' => 'd',
        ],
        [
            'texto'   => '¿Cuál es la política sobre el consumo de alcohol, tabaco y drogas?',
            'opciones' => [
                'a' => 'Prohibir el consumo solo para los residentes.',
                'b' => 'Permitir el consumo en áreas designadas.',
                'c' => 'Prohibir el consumo durante la prestación del servicio para proveedores y contratistas.',
                'd' => 'Promover el consumo de drogas en eventos sociales.',
            ],
            'correcta' => 'c',
        ],
        [
            'texto'   => '¿Cuál es el objetivo de la política de prevención, preparación y respuesta ante emergencias?',
            'opciones' => [
                'a' => 'Fomentar el uso de dispositivos móviles.',
                'b' => 'Proporcionar entretenimiento a los residentes.',
                'c' => 'Salvaguardar la salud y la seguridad de las personas en la propiedad.',
                'd' => 'Controlar el consumo de alimentos en la copropiedad.',
            ],
            'correcta' => 'c',
        ],
        [
            'texto'   => '¿Qué tipo de emergencia se menciona como ejemplo en la información proporcionada?',
            'opciones' => [
                'a' => 'Emergencia tecnológica.',
                'b' => 'Emergencia natural.',
                'c' => 'Emergencia social.',
                'd' => 'Todas las mencionadas.',
            ],
            'correcta' => 'd',
        ],
    ];

    /**
     * Calcula el puntaje (0-100) dadas las respuestas del usuario.
     * $respuestas = ['0' => 'c', '1' => 'd', ...]
     */
    public static function calcularCalificacion(array $respuestas): float
    {
        $correctas = 0;
        $total     = count(self::PREGUNTAS);
        foreach (self::PREGUNTAS as $i => $pregunta) {
            if (isset($respuestas[$i]) && $respuestas[$i] === $pregunta['correcta']) {
                $correctas++;
            }
        }
        return $total > 0 ? round(($correctas / $total) * 100, 2) : 0;
    }

    public function getByAsistencia(int $idAsistencia): ?array
    {
        return $this->where('id_asistencia_induccion', $idAsistencia)->first();
    }
}
