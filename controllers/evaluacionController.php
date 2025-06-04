<?php
// File: controllers/evaluacionController.php

require_once __DIR__ . '/../models/evaluacionModel.php';

class evaluacionController extends evaluacionModel {

    /**
     * Procesa el formulario completo (evaluación + preguntas + opciones).
     * Devuelve un HTML con alertas (éxito o error).
     */
    public function add_evaluacion_controller(int $sesionId, array $post): string {
        // 1) Sanitizar y validar metadatos
        $titulo       = trim($post['titulo'] ?? '');
        $fechaInicio  = trim($post['fechainicio'] ?? '');
        $fechaCierre  = trim($post['fechacierre'] ?? '');
        $intentos     = intval($post['intentos'] ?? 1);
        $duracion     = intval($post['duracion'] ?? 0);

        if (!$titulo || !$fechaInicio || !$fechaCierre) {
            return '<div class="alert alert-warning text-center">Por favor complete el título, fecha de inicio y fecha de cierre.</div>';
        }
        // (Opcional) validar que $fechaInicio < $fechaCierre

        try {
            // 2) Insertar la evaluación y obtener su ID
            $evaluacionId = $this->add_evaluacion(
                $sesionId,
                $titulo,
                $fechaInicio,
                $fechaCierre,
                $intentos,
                $duracion
            );

            // 3) Recorrer las preguntas enviadas
            if (isset($post['questions']) && is_array($post['questions'])) {
                foreach ($post['questions'] as $index => $q) {
                    $textoPregunta = trim($q['texto'] ?? '');
                    if (!$textoPregunta) {
                        // Saltar si no hay texto
                        continue;
                    }
                    // Insertar pregunta y obtener su ID
                    $preguntaId = $this->add_pregunta($evaluacionId, $textoPregunta);

                    // Índice de la opción correcta (si vino como radio)
                    $correctIdx = isset($q['correcta']) ? intval($q['correcta']) : -1;

                    // Recorrer las opciones para esta pregunta
                    if (isset($q['opciones']) && is_array($q['opciones'])) {
                        foreach ($q['opciones'] as $j => $op) {
                            $textoOpcion = trim($op['texto'] ?? '');
                            if ($textoOpcion === '') {
                                continue;
                            }
                            // Marcar EsCorrecta = 1 sólo si coincide con el índice
                            $esCorrecta = ($j === $correctIdx) ? 1 : 0;
                            $this->add_opcion($preguntaId, $textoOpcion, $esCorrecta);
                        }
                    }
                }
            }

            return '<div class="alert alert-success text-center">Evaluación creada correctamente.</div>';
        } catch (PDOException $e) {
            return '<div class="alert alert-danger text-center">
                        Error al guardar la evaluación:<br>' . htmlspecialchars($e->getMessage()) . '
                    </div>';
        }
    }

    /**
     * Devuelve un array con todas las evaluaciones creadas para una sesión dada.
     */
    public function list_evaluaciones_controller(int $sesionId): array {
        return $this->list_evaluaciones_by_sesion($sesionId);
    }

    /**
     * Verifica si ya existe un resultado para esta evaluación y estudiante.
     * Firma compatible con el modelo: devuelve int (0 o 1).
     */
    public function exists_resultado(int $evaluacionId, string $estudianteCodigo): int {
        return $this->exists_resultado_model($evaluacionId, $estudianteCodigo);
    }

    /**
     * Cuenta cuántos resultados (intentos) tiene un estudiante en una evaluación.
     */
    public function count_resultados_by_evaluacion_estudiante(int $evaluacionId, string $estudianteCodigo): int {
        return $this->count_resultados_by_evaluacion_estudiante_model($evaluacionId, $estudianteCodigo);
    }

    /**
     * Controlador para que el estudiante envíe sus respuestas.
     * Recibe: evaluacionId, estudianteCodigo (de sesión) y $_POST con respuestas.
     * Calcula la nota (cada correcta vale igual para sumar 20 puntos).
     */
    public function submit_respuestas_controller(int $evaluacionId, string $estudianteCodigo, array $post): string {
        // Verificar que no exista ya un resultado (o respetar límite de intentos)
        $intentosHechos = $this->count_resultados_by_evaluacion_estudiante($evaluacionId, $estudianteCodigo);
        $evalData       = $this->get_evaluacion($evaluacionId);
        $maxIntentos    = intval($evalData['IntentosPermitidos'] ?? 1);

        if ($intentosHechos >= $maxIntentos) {
            return '<div class="alert alert-warning text-center">Has alcanzado el máximo de intentos permitidos.</div>';
        }

        // Obtener todas las preguntas de esta evaluación
        $preguntas = $this->get_preguntas_by_evaluacion($evaluacionId);
        $totalPreguntas = count($preguntas);
        if ($totalPreguntas === 0) {
            return '<div class="alert alert-warning text-center">No hay preguntas para esta evaluación.</div>';
        }

        // Contar aciertos
        $aciertos = 0;
        foreach ($preguntas as $preg) {
            $pid = intval($preg['id']);
            // En el formulario, cada respuesta vino con name="resp_{preguntaId}"
            if (isset($post["resp_{$pid}"])) {
                $opcionElegidaId = intval($post["resp_{$pid}"]);
                // Guardar la respuesta
                $this->add_respuesta_estudiante($evaluacionId, $estudianteCodigo, $pid, $opcionElegidaId);
                // Verificar si es correcta
                if ($this->is_opcion_correcta($opcionElegidaId) === 1) {
                    $aciertos++;
                }
            }
        }

        // Calcular nota sobre 20
        $nota = ($aciertos / $totalPreguntas) * 20;
        $nota = round($nota, 2);

        // Guardar resultado
        $this->add_resultado($evaluacionId, $estudianteCodigo, $nota);

        return '<div class="alert alert-success text-center">Respuestas enviadas. Tu nota: ' . $nota . '</div>';
    }
}
