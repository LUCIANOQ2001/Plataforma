<?php
// controllers/evaluacionController.php

require_once __DIR__ . '/../models/evaluacionModel.php';

class evaluacionController {
    /** @var \PDO */
    private $pdo;
    /** @var evaluacionModel */
    private $model;

    public function __construct() {
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $this->model = new evaluacionModel();
    }

    /**
     * Inserta una nueva evaluación y sus preguntas/opciones.
     */
    public function add_evaluacion_controller(int $sesionId, array $post): string {
        // Validar campos requeridos
        $titulo       = trim($post['titulo'] ?? '');
        $fechaInicio  = trim($post['fecha_inicio'] ?? '');
        $fechaCierre  = trim($post['fecha_cierre'] ?? '');
        $intentos     = intval($post['intentos'] ?? $post['intentospermitidos'] ?? 1);
        $duracion     = intval($post['duracion'] ?? $post['duracionminutos'] ?? 0);

        if (!$titulo || !$fechaInicio || !$fechaCierre || $duracion <= 0) {
            return '<div class="alert alert-warning text-center">Complete todos los campos obligatorios.</div>';
        }
        if ($fechaCierre < $fechaInicio) {
            return '<div class="alert alert-warning text-center">La fecha de cierre debe ser posterior al inicio.</div>';
        }

        try {
            // Crear evaluación
            $evalId = $this->model->add_evaluacion_model(
                $sesionId, $titulo, $fechaInicio, $fechaCierre, $intentos, $duracion
            );
            // Procesar preguntas y opciones
            if (!empty($post['pregunta_texto']) && is_array($post['pregunta_texto'])) {
                foreach ($post['pregunta_texto'] as $idx => $texto) {
                    $pregId = $this->model->add_pregunta_model($evalId, trim($texto));
                    $keyOpts = 'opciones_' . $idx;
                    $keyCorr = 'pregunta_correcta_' . $idx;
                    $correct = intval($post[$keyCorr] ?? 0);
                    if (!empty($post[$keyOpts]) && is_array($post[$keyOpts])) {
                        foreach ($post[$keyOpts] as $optIdx => $optTexto) {
                            $isCorrect = ($optIdx === $correct) ? 1 : 0;
                            $this->model->add_opcion_model($pregId, trim($optTexto), $isCorrect);
                        }
                    }
                }
            }
            return '<div class="alert alert-success text-center">Evaluación creada correctamente.</div>';
        } catch (PDOException $e) {
            return '<div class="alert alert-danger text-center">Error al guardar evaluación:<br>'
                 . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    /**
     * Actualiza una evaluación existente y redefine sus preguntas/opciones.
     */
    public function update_evaluacion_controller(int $evaluacionId, array $post): string {
        $titulo      = trim($post['titulo'] ?? '');
        $fechaInicio = trim($post['fecha_inicio'] ?? '');
        $fechaCierre = trim($post['fecha_cierre'] ?? '');
        $intentos    = intval($post['intentos'] ?? $post['intentospermitidos'] ?? 1);
        $duracion    = intval($post['duracion'] ?? $post['duracionminutos'] ?? 0);

        if (!$titulo || !$fechaInicio || !$fechaCierre || $duracion <= 0) {
            return '<div class="alert alert-warning text-center">Faltan datos obligatorios.</div>';
        }
        if ($fechaCierre < $fechaInicio) {
            return '<div class="alert alert-warning text-center">La fecha de cierre debe ser posterior al inicio.</div>';
        }
        try {
            // 1) Actualizar tabla evaluacion
            $stmt = $this->pdo->prepare(
                "UPDATE evaluacion
                 SET Titulo = ?, FechaInicio = ?, FechaCierre = ?, IntentosPermitidos = ?, DuracionMinutos = ?
                 WHERE id = ?"
            );
            $stmt->execute([$titulo, $fechaInicio, $fechaCierre, $intentos, $duracion, $evaluacionId]);
            // 2) Eliminar preguntas y opciones previas
            $this->pdo->prepare("DELETE o FROM opcion o
                                   JOIN pregunta p ON o.PreguntaId = p.id
                                  WHERE p.EvaluacionId = ?")->execute([$evaluacionId]);
            $this->pdo->prepare("DELETE FROM pregunta WHERE EvaluacionId = ?")->execute([$evaluacionId]);
            // 3) Reinsertar preguntas y opciones
            if (!empty($post['pregunta_texto']) && is_array($post['pregunta_texto'])) {
                foreach ($post['pregunta_texto'] as $idx => $texto) {
                    $pregId = $this->model->add_pregunta_model($evaluacionId, trim($texto));
                    $keyOpts = 'opciones_' . $idx;
                    $keyCorr = 'pregunta_correcta_' . $idx;
                    $correct = intval($post[$keyCorr] ?? 0);
                    if (!empty($post[$keyOpts]) && is_array($post[$keyOpts])) {
                        foreach ($post[$keyOpts] as $optIdx => $optTexto) {
                            $isCorrect = ($optIdx === $correct) ? 1 : 0;
                            $this->model->add_opcion_model($pregId, trim($optTexto), $isCorrect);
                        }
                    }
                }
            }
            return '<div class="alert alert-success text-center">Evaluación actualizada correctamente.</div>';
        } catch (PDOException $e) {
            return '<div class="alert alert-danger text-center">Error al actualizar evaluación:<br>'
                 . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    /**
     * Elimina una evaluación completa (cascada manual).
     */
    public function delete_evaluacion_controller(int $evaluacionId): string {
        try {
            // Eliminar opciones, preguntas y evaluación
            $this->pdo->prepare("DELETE o FROM opcion o
                                   JOIN pregunta p ON o.PreguntaId = p.id
                                  WHERE p.EvaluacionId = ?")->execute([$evaluacionId]);
            $this->pdo->prepare("DELETE FROM pregunta WHERE EvaluacionId = ?")->execute([$evaluacionId]);
            $this->pdo->prepare("DELETE FROM evaluacion WHERE id = ?")->execute([$evaluacionId]);
            return '<div class="alert alert-success text-center">Evaluación eliminada correctamente.</div>';
        } catch (PDOException $e) {
            return '<div class="alert alert-danger text-center">Error al eliminar evaluación:<br>'
                 . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    // Los demás métodos (list, get, list_preguntas/opciones, submit_respuestas)
    public function list_evaluaciones_by_sesion_controller(int $sesionId): array {
        $stmt = $this->pdo->prepare(
            "SELECT id, SesionId, Titulo, FechaInicio, FechaCierre, DuracionMinutos
             FROM evaluacion
             WHERE SesionId = ?
             ORDER BY FechaInicio ASC"
        );
        $stmt->execute([$sesionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_evaluacion_by_id_controller(int $evaluacionId): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT id, SesionId, Titulo, FechaInicio, FechaCierre, DuracionMinutos
             FROM evaluacion
             WHERE id = ?"
        );
        $stmt->execute([$evaluacionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function list_preguntas_by_evaluacion_controller(int $evaluacionId): array {
        return $this->model->get_preguntas_by_evaluacion_model($evaluacionId);
    }

    public function list_opciones_by_pregunta_controller(int $preguntaId): array {
        return $this->model->get_opciones_by_pregunta_model($preguntaId);
    }

    public function count_resultados_by_evaluacion_estudiante(int $evaluacionId, string $estudianteCodigo): int {
        return $this->model->count_resultados_by_evaluacion_estudiante_model($evaluacionId, $estudianteCodigo);
    }
    
    public function submit_respuestas_controller(int $evaluacionId, string $estudianteCodigo, array $post): string {
        if ($this->model->exists_resultado_model($evaluacionId, $estudianteCodigo) > 0) {
            return '<div class="alert alert-info text-center">Ya respondiste esta evaluación.</div>';
        }
        $preguntas = $this->model->get_preguntas_by_evaluacion_model($evaluacionId);
        $total     = count($preguntas);
        $aciertos  = 0;
        $this->pdo->beginTransaction();
        try {
            foreach ($preguntas as $p) {
                $pregId = intval($p['id']);
                $key    = 'resp_' . $pregId;
                if (!isset($post[$key])) continue;
                $opcionId = intval($post[$key]);
                $this->model->add_respuesta_estudiante_model($evaluacionId, $estudianteCodigo, $pregId, $opcionId);
                if ($this->model->is_opcion_correcta_model($opcionId) === 1) {
                    $aciertos++;
                }
            }
            $nota = $total > 0 ? round(($aciertos / $total) * 100, 2) : 0;
            $this->model->add_resultado_model($evaluacionId, $estudianteCodigo, $nota);
            $this->pdo->commit();
            return '<div class="alert alert-success text-center">Has obtenido <strong>' . $nota . '%</strong> en esta evaluación.</div>';
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return '<div class="alert alert-danger text-center">Error al procesar tus respuestas.</div>';
        }
    }
}
