<?php
// controllers/evaluacionController.php

class evaluacionController {
    /** @var \PDO */
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    /**
     * Inserta una nueva evaluación con todas sus preguntas y opciones (igual que antes).
     *
     * @param int   $sesionId  ID de la sesión a la que pertenece la evaluación.
     * @param array $post      Datos provenientes del formulario ($_POST).
     * @return string          HTML con mensaje de éxito o error.
     */
    public function add_evaluacion_controller(int $sesionId, array $post): string {
        // 1) Validar campos generales
        $titulo       = trim($post['titulo'] ?? '');
        $fechaInicio  = trim($post['fecha_inicio'] ?? '');  // formato "YYYY-MM-DDTHH:MM"
        $fechaCierre  = trim($post['fecha_cierre'] ?? '');
        $duracion     = intval($post['duracion'] ?? 0);

        if (!$titulo || !$fechaInicio || !$fechaCierre || $duracion <= 0) {
            return '<div class="alert alert-warning text-center">'
                 . 'Complete todos los campos generales de la evaluación.</div>';
        }

        // 2) Convertir formatos "YYYY-MM-DDTHH:MM" a "YYYY-MM-DD HH:MM:00"
        $fechaInicioDb = str_replace('T', ' ', $fechaInicio) . ':00';
        $fechaCierreDb = str_replace('T', ' ', $fechaCierre) . ':00';

        // 3) Obtener arreglo de preguntas
        $preguntasTexto = $post['pregunta_texto'] ?? [];
        if (!is_array($preguntasTexto) || count($preguntasTexto) === 0) {
            return '<div class="alert alert-warning text-center">'
                 . 'Debe agregar al menos una pregunta.</div>';
        }

        try {
            // Iniciar transacción
            $this->pdo->beginTransaction();

            // 4) Insertar fila en tabla `evaluacion`
            $stmtEv = $this->pdo->prepare("
                INSERT INTO evaluacion 
                    (SesionId, Titulo, FechaInicio, FechaCierre, DuracionMinutos)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtEv->execute([
                $sesionId,
                $titulo,
                $fechaInicioDb,
                $fechaCierreDb,
                $duracion
            ]);
            $evaluacionId = intval($this->pdo->lastInsertId());

            // 5) Recorrer cada pregunta
            foreach ($preguntasTexto as $index => $textoPregunta) {
                $textoPregunta = trim($textoPregunta);
                if ($textoPregunta === '') {
                    // Si alguna pregunta está vacía, cancelar
                    $this->pdo->rollBack();
                    return '<div class="alert alert-warning text-center">'
                         . 'La pregunta ' . ($index + 1) . ' está vacía.</div>';
                }

                // 5.1) Insertar en tabla `pregunta`
                $stmtPreg = $this->pdo->prepare("
                    INSERT INTO pregunta (EvaluacionId, TextoPregunta)
                    VALUES (?, ?)
                ");
                $stmtPreg->execute([
                    $evaluacionId,
                    $textoPregunta
                ]);
                $preguntaId = intval($this->pdo->lastInsertId());

                // 5.2) Obtener índice de la opción correcta
                $correctKey = 'pregunta_correcta_' . $index;
                if (!isset($post[$correctKey])) {
                    $this->pdo->rollBack();
                    return '<div class="alert alert-warning text-center">'
                         . 'No se especificó la opción correcta para la pregunta ' . ($index + 1) . '.</div>';
                }
                $correctIndex = intval($post[$correctKey]);

                // 5.3) Recorrer las 4 opciones de texto
                $opcionesKey = 'opciones_' . $index;
                $opcionesArr = $post[$opcionesKey] ?? [];
                if (!is_array($opcionesArr) || count($opcionesArr) < 4) {
                    $this->pdo->rollBack();
                    return '<div class="alert alert-warning text-center">'
                         . 'La pregunta ' . ($index + 1) . ' debe tener cuatro opciones.</div>';
                }

                foreach ($opcionesArr as $optIndex => $textoOpcion) {
                    $textoOpcion = trim($textoOpcion);
                    if ($textoOpcion === '') {
                        $this->pdo->rollBack();
                        return '<div class="alert alert-warning text-center">'
                             . 'La opción ' . ($optIndex + 1) . ' de la pregunta ' . ($index + 1) . ' está vacía.</div>';
                    }
                    $esCorrecta = ($optIndex === $correctIndex) ? 1 : 0;

                    // Insertar en tabla `opcion`
                    $stmtOpt = $this->pdo->prepare("
                        INSERT INTO opcion (PreguntaId, TextoOpcion, EsCorrecta)
                        VALUES (?, ?, ?)
                    ");
                    $stmtOpt->execute([
                        $preguntaId,
                        $textoOpcion,
                        $esCorrecta
                    ]);
                }
            }

            // 6) Confirmar transacción
            $this->pdo->commit();
            return '<div class="alert alert-success text-center">'
                 . 'Evaluación creada exitosamente.</div>';

        } catch (PDOException $e) {
            // En caso de error, revertir transacción
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return '<div class="alert alert-danger text-center">'
                 . 'Error al crear la evaluación:<br>' 
                 . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    /**
     * Actualiza una evaluación existente (edita todos sus campos);
     * para simplificar, borra las preguntas/opciones previas y vuelve a insertar las nuevas.
     *
     * @param int   $evaluacionId  ID de la evaluación a modificar.
     * @param array $post          Datos provenientes del formulario ($_POST).
     * @return string              HTML con mensaje de éxito o error.
     */
    public function update_evaluacion_controller(int $evaluacionId, array $post): string {
        // 1) Validar campos generales
        $titulo       = trim($post['titulo'] ?? '');
        $fechaInicio  = trim($post['fecha_inicio'] ?? '');  // formato "YYYY-MM-DDTHH:MM"
        $fechaCierre  = trim($post['fecha_cierre'] ?? '');
        $duracion     = intval($post['duracion'] ?? 0);

        if (!$titulo || !$fechaInicio || !$fechaCierre || $duracion <= 0) {
            return '<div class="alert alert-warning text-center">'
                 . 'Complete todos los campos generales de la evaluación.</div>';
        }

        // 2) Convertir formatos "YYYY-MM-DDTHH:MM" a "YYYY-MM-DD HH:MM:00"
        $fechaInicioDb = str_replace('T', ' ', $fechaInicio) . ':00';
        $fechaCierreDb = str_replace('T', ' ', $fechaCierre) . ':00';

        // 3) Obtener arreglo de preguntas
        $preguntasTexto = $post['pregunta_texto'] ?? [];
        if (!is_array($preguntasTexto) || count($preguntasTexto) === 0) {
            return '<div class="alert alert-warning text-center">'
                 . 'Debe agregar al menos una pregunta.</div>';
        }

        try {
            // Iniciar transacción
            $this->pdo->beginTransaction();

            // 4) Actualizar fila en tabla `evaluacion`
            $stmtUpd = $this->pdo->prepare("
                UPDATE evaluacion
                   SET Titulo = ?, FechaInicio = ?, FechaCierre = ?, DuracionMinutos = ?
                 WHERE id = ?
            ");
            $stmtUpd->execute([
                $titulo,
                $fechaInicioDb,
                $fechaCierreDb,
                $duracion,
                $evaluacionId
            ]);

            // 5) Borrar preguntas u opciones previas (cascade con FK)
            //    Dado que existe FK con ON DELETE CASCADE, basta con borrar de 'pregunta':
            $delPreg = $this->pdo->prepare("
                DELETE FROM pregunta WHERE EvaluacionId = ?
            ");
            $delPreg->execute([$evaluacionId]);
            // → al borrar preguntas, la tabla 'opcion' también borra en cascada las filas relacionadas.

            // 6) Insertar nuevamente las preguntas y opciones (similar a add_evaluacion_controller)
            foreach ($preguntasTexto as $index => $textoPregunta) {
                $textoPregunta = trim($textoPregunta);
                if ($textoPregunta === '') {
                    $this->pdo->rollBack();
                    return '<div class="alert alert-warning text-center">'
                         . 'La pregunta ' . ($index + 1) . ' está vacía.</div>';
                }

                // 6.1) Insertar en tabla `pregunta`
                $stmtPreg = $this->pdo->prepare("
                    INSERT INTO pregunta (EvaluacionId, TextoPregunta)
                    VALUES (?, ?)
                ");
                $stmtPreg->execute([
                    $evaluacionId,
                    $textoPregunta
                ]);
                $preguntaId = intval($this->pdo->lastInsertId());

                // 6.2) Obtener índice de la opción correcta
                $correctKey = 'pregunta_correcta_' . $index;
                if (!isset($post[$correctKey])) {
                    $this->pdo->rollBack();
                    return '<div class="alert alert-warning text-center">'
                         . 'No se especificó la opción correcta para la pregunta ' . ($index + 1) . '.</div>';
                }
                $correctIndex = intval($post[$correctKey]);

                // 6.3) Recorrer las 4 opciones de texto
                $opcionesKey = 'opciones_' . $index;
                $opcionesArr = $post[$opcionesKey] ?? [];
                if (!is_array($opcionesArr) || count($opcionesArr) < 4) {
                    $this->pdo->rollBack();
                    return '<div class="alert alert-warning text-center">'
                         . 'La pregunta ' . ($index + 1) . ' debe tener cuatro opciones.</div>';
                }

                foreach ($opcionesArr as $optIndex => $textoOpcion) {
                    $textoOpcion = trim($textoOpcion);
                    if ($textoOpcion === '') {
                        $this->pdo->rollBack();
                        return '<div class="alert alert-warning text-center">'
                             . 'La opción ' . ($optIndex + 1) . ' de la pregunta ' . ($index + 1) . ' está vacía.</div>';
                    }
                    $esCorrecta = ($optIndex === $correctIndex) ? 1 : 0;

                    // Insertar en tabla `opcion`
                    $stmtOpt = $this->pdo->prepare("
                        INSERT INTO opcion (PreguntaId, TextoOpcion, EsCorrecta)
                        VALUES (?, ?, ?)
                    ");
                    $stmtOpt->execute([
                        $preguntaId,
                        $textoOpcion,
                        $esCorrecta
                    ]);
                }
            }

            // 7) Confirmar transacción
            $this->pdo->commit();
            return '<div class="alert alert-success text-center">'
                 . 'Evaluación actualizada exitosamente.</div>';

        } catch (PDOException $e) {
            // En caso de error, revertir transacción
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return '<div class="alert alert-danger text-center">'
                 . 'Error al actualizar la evaluación:<br>' 
                 . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    /**
     * Elimina una evaluación (y, en cascada, sus preguntas y opciones).
     *
     * @param int $evaluacionId  ID de la evaluación a borrar.
     * @return string            HTML con mensaje de éxito o error.
     */
    public function delete_evaluacion_controller(int $evaluacionId): string {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM evaluacion WHERE id = ?");
            $stmt->execute([$evaluacionId]);
            return '<div class="alert alert-success text-center">'
                 . 'Evaluación eliminada exitosamente.</div>';
        } catch (PDOException $e) {
            return '<div class="alert alert-danger text-center">'
                 . 'Error al eliminar la evaluación:<br>' 
                 . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    /**
     * Devuelve un arreglo con todas las evaluaciones de una sesión dada.
     *
     * @param int $sesionId  ID de la sesión.
     * @return array         Array de evaluaciones (cada una con keys: id, SesionId, Titulo, FechaInicio, FechaCierre, DuracionMinutos).
     */
    public function list_evaluaciones_by_sesion_controller(int $sesionId): array {
        $stmt = $this->pdo->prepare("
            SELECT 
                id,
                SesionId,
                Titulo,
                FechaInicio,
                FechaCierre,
                DuracionMinutos
            FROM evaluacion
            WHERE SesionId = ?
            ORDER BY FechaInicio ASC
        ");
        $stmt->execute([$sesionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los datos de una evaluación por su ID (sin preguntas/opciones).
     *
     * @param int $evaluacionId  ID de la evaluación.
     * @return array|null        Arreglo asociativo con la fila (o null si no existe).
     */
    public function get_evaluacion_by_id_controller(int $evaluacionId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT 
                id,
                SesionId,
                Titulo,
                FechaInicio,
                FechaCierre,
                DuracionMinutos
            FROM evaluacion
            WHERE id = ?
        ");
        $stmt->execute([$evaluacionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Lista todas las preguntas correspondientes a una evaluación.
     *
     * @param int $evaluacionId  ID de la evaluación.
     * @return array             Array de preguntas (cada una con keys: id, EvaluacionId, TextoPregunta).
     */
    public function list_preguntas_by_evaluacion_controller(int $evaluacionId): array {
        $stmt = $this->pdo->prepare("
            SELECT 
                id,
                EvaluacionId,
                TextoPregunta
            FROM pregunta
            WHERE EvaluacionId = ?
            ORDER BY id ASC
        ");
        $stmt->execute([$evaluacionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista todas las opciones de una pregunta dada.
     *
     * @param int $preguntaId  ID de la pregunta.
     * @return array           Array de opciones (cada una con keys: id, PreguntaId, TextoOpcion, EsCorrecta).
     */
    public function list_opciones_by_pregunta_controller(int $preguntaId): array {
        $stmt = $this->pdo->prepare("
            SELECT 
                id,
                PreguntaId,
                TextoOpcion,
                EsCorrecta
            FROM opcion
            WHERE PreguntaId = ?
            ORDER BY id ASC
        ");
        $stmt->execute([$preguntaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
