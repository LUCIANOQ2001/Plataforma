<?php
// File: models/evaluacionModel.php

class evaluacionModel {

    /**
     * Conecta a la base de datos y devuelve el objeto PDO.
     */
    protected function connect(){
        return new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    /**
     * Inserta una nueva evaluación y devuelve su ID generado.
     * Agrega también los campos: FechaInicio, FechaCierre, IntentosPermitidos y DuracionMinutos.
     */
    public function add_evaluacion(int $sesionId, string $titulo, string $fechaInicio, string $fechaCierre, int $intentos, int $duracion): int {
        $pdo = $this->connect();
        $sql = "INSERT INTO evaluacion 
                    (SesionId, Titulo, FechaCreacion, FechaInicio, FechaCierre, IntentosPermitidos, DuracionMinutos)
                VALUES 
                    (?, ?, NOW(), ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $sesionId,
            $titulo,
            $fechaInicio,
            $fechaCierre,
            $intentos,
            $duracion
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Inserta una pregunta asociada a una evaluación y devuelve el ID de la pregunta.
     */
    public function add_pregunta(int $evaluacionId, string $textoPregunta): int {
        $pdo = $this->connect();
        $sql = "INSERT INTO pregunta (EvaluacionId, TextoPregunta) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$evaluacionId, $textoPregunta]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Inserta una opción para una pregunta dada. $esCorrecta = 0 o 1.
     */
    public function add_opcion(int $preguntaId, string $textoOpcion, int $esCorrecta): void {
        $pdo = $this->connect();
        $sql = "INSERT INTO opcion (PreguntaId, TextoOpcion, EsCorrecta) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$preguntaId, $textoOpcion, $esCorrecta]);
    }

    /**
     * Devuelve un array con todas las evaluaciones de una sesión dada,
     * ordenadas por FechaCreacion descendente.
     */
    public function list_evaluaciones_by_sesion(int $sesionId): array {
        $pdo = $this->connect();
        $sql = "SELECT * 
                  FROM evaluacion 
                 WHERE SesionId = ?
                 ORDER BY FechaCreacion DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sesionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los datos de una evaluación por su ID.
     * Devuelve fila asociativa o false si no existe.
     */
    public function get_evaluacion(int $evaluacionId) {
        $pdo = $this->connect();
        $sql = "SELECT * FROM evaluacion WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$evaluacionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve un array con todas las preguntas asociadas a una evaluación.
     */
    public function get_preguntas_by_evaluacion(int $evaluacionId): array {
        $pdo = $this->connect();
        $sql = "SELECT * FROM pregunta WHERE EvaluacionId = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$evaluacionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve un array con todas las opciones de una pregunta dada.
     */
    public function get_opciones_by_pregunta(int $preguntaId): array {
        $pdo = $this->connect();
        $sql = "SELECT * FROM opcion WHERE PreguntaId = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$preguntaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve 1 o 0 si la opción indicada es correcta.
     */
    public function is_opcion_correcta(int $opcionId): int {
        $pdo = $this->connect();
        $sql = "SELECT EsCorrecta FROM opcion WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$opcionId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Inserta una respuesta de estudiante para una pregunta.
     */
    public function add_respuesta_estudiante(int $evaluacionId, string $estudianteCodigo, int $preguntaId, int $opcionElegidaId): void {
        $pdo = $this->connect();
        $sql = "INSERT INTO respuesta_estudiante 
                    (EvaluacionId, EstudianteCodigo, PreguntaId, OpcionElegidaId, Fecha)
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$evaluacionId, $estudianteCodigo, $preguntaId, $opcionElegidaId]);
    }

    /**
     * Inserta el resultado final (nota) de un estudiante en una evaluación.
     */
    public function add_resultado(int $evaluacionId, string $estudianteCodigo, float $nota): void {
        $pdo = $this->connect();
        $sql = "INSERT INTO resultado 
                    (EvaluacionId, EstudianteCodigo, Nota, Fecha)
                VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$evaluacionId, $estudianteCodigo, $nota]);
    }

    /**
     * Verifica si ya existe un resultado para esta evaluación y estudiante.
     * Devuelve COUNT(*).
     */
    public function exists_resultado(int $evaluacionId, string $estudianteCodigo): int {
        $pdo = $this->connect();
        $sql = "SELECT COUNT(*) FROM resultado 
                 WHERE EvaluacionId = ? AND EstudianteCodigo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$evaluacionId, $estudianteCodigo]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Cuenta cuántos resultados (intentosenviados) tiene un estudiante en una evaluación.
     */
    public function count_resultados_by_evaluacion_estudiante(int $evaluacionId, string $estudianteCodigo): int {
        $pdo = $this->connect();
        $sql = "SELECT COUNT(*) FROM resultado 
                 WHERE EvaluacionId = ? AND EstudianteCodigo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$evaluacionId, $estudianteCodigo]);
        return (int)$stmt->fetchColumn();
    }

    // ------------------------------------------------------------------------
    // Alias “_model” para que el controller use el sufijo _model según convención
    // ------------------------------------------------------------------------

    public function add_evaluacion_model(int $sesionId, string $titulo, string $fechaInicio, string $fechaCierre, int $intentos, int $duracion): int {
        return $this->add_evaluacion($sesionId, $titulo, $fechaInicio, $fechaCierre, $intentos, $duracion);
    }

    public function add_pregunta_model(int $evaluacionId, string $textoPregunta): int {
        return $this->add_pregunta($evaluacionId, $textoPregunta);
    }

    public function add_opcion_model(int $preguntaId, string $textoOpcion, int $esCorrecta): void {
        $this->add_opcion($preguntaId, $textoOpcion, $esCorrecta);
    }

    public function list_evaluaciones_by_sesion_model(int $sesionId): array {
        return $this->list_evaluaciones_by_sesion($sesionId);
    }

    public function get_evaluacion_by_id_model(int $evaluacionId) {
        return $this->connect()->prepare("SELECT * FROM evaluacion WHERE id = ?")->execute([$evaluacionId])
            ? $this->connect()->prepare("SELECT * FROM evaluacion WHERE id = ?")->execute([$evaluacionId])
            : null;
    }

    public function get_preguntas_by_evaluacion_model(int $evaluacionId): array {
        return $this->get_preguntas_by_evaluacion($evaluacionId);
    }

    public function get_opciones_by_pregunta_model(int $preguntaId): array {
        return $this->get_opciones_by_pregunta($preguntaId);
    }

    public function is_opcion_correcta_model(int $opcionId): int {
        return $this->is_opcion_correcta($opcionId);
    }

    public function add_respuesta_estudiante_model(int $evaluacionId, string $estudianteCodigo, int $preguntaId, int $opcionElegidaId): void {
        $this->add_respuesta_estudiante($evaluacionId, $estudianteCodigo, $preguntaId, $opcionElegidaId);
    }

    public function add_resultado_model(int $evaluacionId, string $estudianteCodigo, float $nota): void {
        $this->add_resultado($evaluacionId, $estudianteCodigo, $nota);
    }

    public function exists_resultado_model(int $evaluacionId, string $estudianteCodigo): int {
        return $this->exists_resultado($evaluacionId, $estudianteCodigo);
    }

    public function count_resultados_by_evaluacion_estudiante_model(int $evaluacionId, string $estudianteCodigo): int {
        return $this->count_resultados_by_evaluacion_estudiante($evaluacionId, $estudianteCodigo);
    }
}
