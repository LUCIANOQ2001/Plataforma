<?php
// controllers/evaluacionController.php

class evaluacionController {
    private $pdo;

    public function __construct(){
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root','',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    /* --------------------------------------------------------------------
     * 1) Obtener la evaluación que ya existe para una sesión (si la hay).
     *    Si no existe, rowCount() será 0.
     * -------------------------------------------------------------------- */
    public function get_evaluacion_by_sesion_controller(int $sesionId): PDOStatement {
        $stmt = $this->pdo->prepare("
            SELECT id, SesionId, Titulo, FechaCreacion
              FROM evaluacion
             WHERE SesionId = ?
             LIMIT 1
        ");
        $stmt->execute([$sesionId]);
        return $stmt;
    }

    /* --------------------------------------------------------------------
     * 2) Crear nueva evaluación (docente). Recibe:
     *    - $sesionId: ID de la sesión a la que pertenece.
     *    - $post: $_POST completo con:
     *        'titulo'            => Título de la evaluación
     *        'pregunta'[]        => Array de textos de preguntas
     *        'opcion_texto'[i][] => Para la i-ésima pregunta, array de textos de las opciones
     *        'correcta'[i]       => Para la i-ésima pregunta, índice (0-based) de la opción correcta
     * -------------------------------------------------------------------- */
    public function add_evaluacion_controller(int $sesionId, array $post): string {
        $titulo = trim($post['titulo'] ?? '');
        $preguntas = $post['pregunta'] ?? [];
        $opciones_raw = $post['opcion_texto'] ?? [];
        $correctas_raw = $post['correcta'] ?? [];

        if (!$titulo || empty($preguntas) || empty($opciones_raw)) {
            return '<div class="alert alert-warning text-center">Debe indicar título, al menos una pregunta y sus opciones.</div>';
        }

        try {
            $this->pdo->beginTransaction();

            // 1) Insertar en tabla evaluacion
            $insEval = $this->pdo->prepare("
                INSERT INTO evaluacion (SesionId, Titulo)
                VALUES (?, ?)
            ");
            $insEval->execute([$sesionId, $titulo]);
            $evaluacionId = (int)$this->pdo->lastInsertId();

            // 2) Para cada pregunta, insertar en tabla pregunta
            $insPregunta = $this->pdo->prepare("
                INSERT INTO pregunta (EvaluacionId, TextoPregunta)
                VALUES (?, ?)
            ");
            $insOpcion = $this->pdo->prepare("
                INSERT INTO opcion (PreguntaId, TextoOpcion, EsCorrecta)
                VALUES (?, ?, ?)
            ");

            foreach ($preguntas as $i => $textoPregunta) {
                $textoPregunta = trim($textoPregunta);
                if ($textoPregunta === '') {
                    throw new Exception("La pregunta {$i} está vacía.");
                }
                // Insertar pregunta
                $insPregunta->execute([$evaluacionId, $textoPregunta]);
                $preguntaId = (int)$this->pdo->lastInsertId();

                // Insertar sus opciones
                if (!isset($opciones_raw[$i]) || !is_array($opciones_raw[$i])) {
                    throw new Exception("Faltan opciones para la pregunta {$i}.");
                }
                $opcionesPorPregunta = $opciones_raw[$i];

                // Índice de la opción correcta (0-based)
                $correctaIndex = isset($correctas_raw[$i]) ? intval($correctas_raw[$i]) : -1;

                foreach ($opcionesPorPregunta as $j => $textoOpcion) {
                    $textoOpcion = trim($textoOpcion);
                    if ($textoOpcion === '') {
                        throw new Exception("Opción vacía para la pregunta {$i}, opción {$j}.");
                    }
                    $esCorrecta = ($j === $correctaIndex) ? 1 : 0;
                    $insOpcion->execute([$preguntaId, $textoOpcion, $esCorrecta]);
                }
            }

            $this->pdo->commit();
            return '<div class="alert alert-success text-center">Evaluación creada correctamente.</div>';
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return '<div class="alert alert-danger text-center">Error al crear evaluación: '.htmlspecialchars($e->getMessage()).'</div>';
        }
    }

    /* --------------------------------------------------------------------
     * 3) Actualizar evaluación existente (docente). Recibe:
     *    - $evalId: ID de la evaluación a actualizar.
     *    - $post: misma estructura que en add_evaluacion_controller.
     *    Estrategia: eliminar todas las preguntas/opciones anteriores para
     *    esta evaluación y volver a insertarlas.
     * -------------------------------------------------------------------- */
    public function update_evaluacion_controller(int $evalId, array $post): string {
        $titulo = trim($post['titulo'] ?? '');
        $preguntas = $post['pregunta'] ?? [];
        $opciones_raw = $post['opcion_texto'] ?? [];
        $correctas_raw = $post['correcta'] ?? [];

        if (!$titulo || empty($preguntas) || empty($opciones_raw)) {
            return '<div class="alert alert-warning text-center">Debe indicar título, al menos una pregunta y sus opciones.</div>';
        }

        try {
            $this->pdo->beginTransaction();

            // 1) Actualizar el título de la evaluación
            $updEval = $this->pdo->prepare("
                UPDATE evaluacion
                   SET Titulo = ?
                 WHERE id = ?
            ");
            $updEval->execute([$titulo, $evalId]);

            // 2) Eliminar preguntas y opciones anteriores
            //    Primero obtenemos todas las preguntas para borrar sus opciones
            $stmtPregs = $this->pdo->prepare("SELECT id FROM pregunta WHERE EvaluacionId = ?");
            $stmtPregs->execute([$evalId]);
            $pregsAntiguas = $stmtPregs->fetchAll(PDO::FETCH_COLUMN, 0);

            if (!empty($pregsAntiguas)) {
                // Borrar opciones de esas preguntas
                $inLista = implode(",", array_map('intval', $pregsAntiguas));
                $this->pdo->exec("DELETE FROM opcion WHERE PreguntaId IN ({$inLista})");
                // Borrar preguntas
                $this->pdo->exec("DELETE FROM pregunta WHERE EvaluacionId = ".(int)$evalId);
            }

            // 3) Insertar nuevamente preguntas y opciones (idéntico a add)
            $insPregunta = $this->pdo->prepare("
                INSERT INTO pregunta (EvaluacionId, TextoPregunta)
                VALUES (?, ?)
            ");
            $insOpcion = $this->pdo->prepare("
                INSERT INTO opcion (PreguntaId, TextoOpcion, EsCorrecta)
                VALUES (?, ?, ?)
            ");

            foreach ($preguntas as $i => $textoPregunta) {
                $textoPregunta = trim($textoPregunta);
                if ($textoPregunta === '') {
                    throw new Exception("La pregunta {$i} está vacía.");
                }
                // Insertar pregunta
                $insPregunta->execute([$evalId, $textoPregunta]);
                $preguntaId = (int)$this->pdo->lastInsertId();

                // Insertar sus opciones
                if (!isset($opciones_raw[$i]) || !is_array($opciones_raw[$i])) {
                    throw new Exception("Faltan opciones para la pregunta {$i}.");
                }
                $opcionesPorPregunta = $opciones_raw[$i];
                $correctaIndex = isset($correctas_raw[$i]) ? intval($correctas_raw[$i]) : -1;

                foreach ($opcionesPorPregunta as $j => $textoOpcion) {
                    $textoOpcion = trim($textoOpcion);
                    if ($textoOpcion === '') {
                        throw new Exception("Opción vacía para la pregunta {$i}, opción {$j}.");
                    }
                    $esCorrecta = ($j === $correctaIndex) ? 1 : 0;
                    $insOpcion->execute([$preguntaId, $textoOpcion, $esCorrecta]);
                }
            }

            $this->pdo->commit();
            return '<div class="alert alert-success text-center">Evaluación actualizada correctamente.</div>';
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return '<div class="alert alert-danger text-center">Error al actualizar evaluación: '.htmlspecialchars($e->getMessage()).'</div>';
        }
    }

    /* --------------------------------------------------------------------
     * 4) Eliminar evaluación (docente). Se borran en cascada sus preguntas y opciones.
     * -------------------------------------------------------------------- */
    public function delete_evaluacion_controller(int $evalId): string {
        try {
            $del = $this->pdo->prepare("DELETE FROM evaluacion WHERE id = ?");
            $del->execute([$evalId]);
            return '<div class="alert alert-success text-center">Evaluación eliminada.</div>';
        } catch (PDOException $e) {
            return '<div class="alert alert-danger text-center">Error al eliminar evaluación: '.htmlspecialchars($e->getMessage()).'</div>';
        }
    }

    /* --------------------------------------------------------------------
     * 5) Listar preguntas + opciones para una evaluación dada
     *    Devolverá un arreglo asociativo:
     *    [
     *      preguntaId => [
     *         'TextoPregunta' => '¿...?',
     *         'Opciones' => [
     *             ['opcionId'=>1, 'TextoOpcion'=>'...', 'EsCorrecta'=>0],
     *             ...
     *         ]
     *      ],
     *      ...
     *    ]
     * -------------------------------------------------------------------- */
    public function list_preguntas_opciones(int $evalId): array {
        $resultado = [];

        // 1) Buscar todas las preguntas de esa evaluación
        $stmtP = $this->pdo->prepare("
            SELECT id AS preguntaId, TextoPregunta
              FROM pregunta
             WHERE EvaluacionId = ?
             ORDER BY id ASC
        ");
        $stmtP->execute([$evalId]);
        $preguntas = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        // 2) Por cada pregunta, traer sus opciones
        $stmtO = $this->pdo->prepare("
            SELECT id AS opcionId, TextoOpcion, EsCorrecta
              FROM opcion
             WHERE PreguntaId = ?
             ORDER BY id ASC
        ");

        foreach ($preguntas as $filaP) {
            $preguntaId    = (int)$filaP['preguntaId'];
            $textoPregunta = $filaP['TextoPregunta'];

            $stmtO->execute([$preguntaId]);
            $opcionesRaw = $stmtO->fetchAll(PDO::FETCH_ASSOC);

            $listaOpciones = [];
            foreach ($opcionesRaw as $filaO) {
                $listaOpciones[] = [
                    'opcionId'    => (int)$filaO['opcionId'],
                    'TextoOpcion' => $filaO['TextoOpcion'],
                    'EsCorrecta'  => (int)$filaO['EsCorrecta']
                ];
            }

            $resultado[$preguntaId] = [
                'TextoPregunta' => $textoPregunta,
                'Opciones'      => $listaOpciones
            ];
        }

        return $resultado;
    }

    /* --------------------------------------------------------------------
     * 6) El estudiante envía sus respuestas. Este método:
     *    - Recibe $evaluacionId y $_POST con:
     *         'respuesta'[preguntaId] = opcionElegidaId
     *    - Guarda cada respuesta en `respuesta_estudiante`.
     *    - Calcula la nota: (número de correctas / total preguntas) * 20.
     *    - Inserta o actualiza el registro en `resultado`.
     *    - Devuelve mensaje de éxito o error.
     * -------------------------------------------------------------------- */
    public function submit_respuestas_controller(int $evaluacionId, array $post): string {
        $respuestas = $post['respuesta'] ?? [];
        $userCodigo = $_SESSION['userKey'] ?? '';

        if (!$evaluacionId || empty($respuestas)) {
            return '<div class="alert alert-warning text-center">No se han enviado respuestas.</div>';
        }

        try {
            $this->pdo->beginTransaction();

            // 1) Primero, borro cualquier respuesta previa de este mismo estudiante para esta evaluación
            $delStmt = $this->pdo->prepare("
                DELETE FROM respuesta_estudiante
                 WHERE EvaluacionId = ? AND EstudianteCodigo = ?
            ");
            $delStmt->execute([$evaluacionId, $userCodigo]);

            // 2) Guardar respuestas una a una
            $insResp = $this->pdo->prepare("
                INSERT INTO respuesta_estudiante 
                    (EvaluacionId, EstudianteCodigo, PreguntaId, OpcionElegidaId)
                VALUES (?, ?, ?, ?)
            ");

            $totalPreguntas = count($respuestas);
            $correctasCount = 0;

            // Para verificar si la opción elegida es correcta,
            // prepararemos un SELECT que chequea `EsCorrecta` en la tabla `opcion`.
            $stmtCheck = $this->pdo->prepare("
                SELECT EsCorrecta
                  FROM opcion
                 WHERE id = ?
            ");

            foreach ($respuestas as $preguntaId => $opcionElegidaId) {
                $preguntaId     = intval($preguntaId);
                $opcionElegidaId = intval($opcionElegidaId);

                // 2a) Insertar en respuesta_estudiante
                $insResp->execute([$evaluacionId, $userCodigo, $preguntaId, $opcionElegidaId]);

                // 2b) Verificar si fue correcta
                $stmtCheck->execute([$opcionElegidaId]);
                $rowO = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                if ($rowO && (int)$rowO['EsCorrecta'] === 1) {
                    $correctasCount++;
                }
            }

            // 3) Calcular nota: (correctas / totalPreguntas) * 20
            $nota = 0;
            if ($totalPreguntas > 0) {
                $nota = round(($correctasCount / $totalPreguntas) * 20, 2);
                if ($nota < 0) $nota = 0;
                if ($nota > 20) $nota = 20;
            }

            // 4) Insertar o actualizar en tabla resultado
            //    Primero, verifico si ya había un resultado previo
            $stmtRes = $this->pdo->prepare("
                SELECT id 
                  FROM resultado
                 WHERE EvaluacionId = ? AND EstudianteCodigo = ?
                 LIMIT 1
            ");
            $stmtRes->execute([$evaluacionId, $userCodigo]);

            if ($stmtRes->rowCount() === 1) {
                // Ya existe: actualizar
                $rowR = $stmtRes->fetch(PDO::FETCH_ASSOC);
                $resultadoId = (int)$rowR['id'];
                $updRes = $this->pdo->prepare("
                    UPDATE resultado
                       SET Nota = ?, Fecha = CURRENT_TIMESTAMP()
                     WHERE id = ?
                ");
                $updRes->execute([$nota, $resultadoId]);
            } else {
                // No existía: insertar
                $insRes = $this->pdo->prepare("
                    INSERT INTO resultado (EvaluacionId, EstudianteCodigo, Nota)
                    VALUES (?, ?, ?)
                ");
                $insRes->execute([$evaluacionId, $userCodigo, $nota]);
            }

            $this->pdo->commit();
            return "<div class=\"alert alert-success text-center\">
                      <strong>Evaluación enviada. </strong>Tu nota: {$nota} / 20
                    </div>";
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return '<div class="alert alert-danger text-center">Error al guardar respuestas: '
                   .htmlspecialchars($e->getMessage()).'</div>';
        }
    }

    /* --------------------------------------------------------------------
     * 7) Obtener la nota de un estudiante en una evaluación (para mostrarla).
     * -------------------------------------------------------------------- */
    public function get_resultado_controller(int $evaluacionId, string $estudianteCodigo) {
        $stmt = $this->pdo->prepare("
            SELECT Nota
              FROM resultado
             WHERE EvaluacionId = ? AND EstudianteCodigo = ?
             LIMIT 1
        ");
        $stmt->execute([$evaluacionId, $estudianteCodigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
