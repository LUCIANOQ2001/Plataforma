<?php
// controllers/notaController.php

class notaController {
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
     * Obtiene la nota de un estudiante en una sesión específica.
     *
     * @param int    $sesionId
     * @param string $estudianteCodigo
     * @return array|null  Devuelve ['Nota'=>..., 'Fecha'=>...] o null si no existe.
     */
    public function get_nota_by_sesion_estudiante_controller(int $sesionId, string $estudianteCodigo): ?array {
        $stmt = $this->pdo->prepare("
            SELECT Nota, Fecha
              FROM nota_sesion
             WHERE SesionId = ?
               AND EstudianteCodigo = ?
        ");
        $stmt->execute([$sesionId, $estudianteCodigo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Inserta o actualiza la nota de un estudiante para una sesión.
     * Validación de rango: 0.00 a 20.00.
     *
     * @param int    $cursoId
     * @param int    $sesionId
     * @param string $estudianteCodigo
     * @param float  $nota
     * @return string  Mensaje HTML con resultado (éxito o error).
     */
    public function save_nota_controller(int $cursoId, int $sesionId, string $estudianteCodigo, float $nota): string {
        // Validar rango de nota (0.00 a 20.00)
        if ($nota < 0 || $nota > 20) {
            return '<div class="alert alert-warning text-center">'
                 . 'La nota debe estar entre 0 y 20.</div>';
        }

        // Verificar si ya existe esa nota
        $chk = $this->pdo->prepare("
            SELECT id 
              FROM nota_sesion 
             WHERE SesionId = ? 
               AND EstudianteCodigo = ?
        ");
        $chk->execute([$sesionId, $estudianteCodigo]);

        try {
            if ($chk->rowCount() > 0) {
                // Existe → UPDATE
                $row = $chk->fetch(PDO::FETCH_ASSOC);
                $idNota = intval($row['id']);
                $upd = $this->pdo->prepare("
                    UPDATE nota_sesion
                       SET Nota = ?, Fecha = current_timestamp()
                     WHERE id = ?
                ");
                $upd->execute([$nota, $idNota]);
                return '<div class="alert alert-success text-center">'
                     . 'Nota actualizada correctamente.</div>';
            } else {
                // No existe → INSERT
                $ins = $this->pdo->prepare("
                    INSERT INTO nota_sesion 
                        (CursoId, SesionId, EstudianteCodigo, Nota)
                    VALUES (?, ?, ?, ?)
                ");
                $ins->execute([$cursoId, $sesionId, $estudianteCodigo, $nota]);
                return '<div class="alert alert-success text-center">'
                     . 'Nota guardada correctamente.</div>';
            }
        } catch (PDOException $e) {
            return '<div class="alert alert-danger text-center">'
                 . 'Error al guardar la nota:<br>' 
                 . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    /**
     * Lista todas las notas de un curso.
     *
     * @param int $cursoId
     * @return array
     */
    public function list_notas_by_curso_controller(int $cursoId): array {
        $stmt = $this->pdo->prepare("
            SELECT 
              ns.SesionId,
              ns.EstudianteCodigo,
              ns.Nota,
              ns.Fecha
            FROM nota_sesion ns
            WHERE ns.CursoId = ?
            ORDER BY ns.EstudianteCodigo ASC, ns.SesionId ASC
        ");
        $stmt->execute([$cursoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
