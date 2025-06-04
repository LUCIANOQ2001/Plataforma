<?php
// controllers/asistenciaController.php

class asistenciaController {
    /** @var \PDO */
    private $pdo;

    public function __construct(){
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root','',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    /**
     * 1) Trae los estudiantes matriculados en el curso de esa sesión
     *    y su estado de asistencia (por defecto 'ausente').
     */
    public function get_students_by_session_controller(int $sessionId): array {
        $sql = "
            SELECT 
                e.Codigo,
                e.Nombres,
                e.Apellidos,
                COALESCE(a.estado, 'ausente') AS estado
            FROM sesion s
            JOIN curso_estudiante ce 
              ON ce.CursoId = s.CursoId
            JOIN estudiante e 
              ON e.Codigo = ce.EstudianteCodigo
            LEFT JOIN asistencia a
              ON a.estudiante = e.Codigo
             AND a.sesion_id = s.id
            WHERE s.id = :sesion
            ORDER BY e.Apellidos, e.Nombres
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':sesion' => $sessionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 2) Guarda o reemplaza todos los estados de asistencia para esa sesión.
     *    Ahora usamos sesion_id, no clase_id.
     */
    public function save_attendance_by_session_controller(int $sessionId, array $attendance): string {
        if (empty($attendance)) {
            return '<div class="alert alert-warning text-center">'
                 . 'No se enviaron datos de asistencia.</div>';
        }

        try {
            $this->pdo->beginTransaction();

            // 2.1) Borrar registros previos de esta sesión
            $del = $this->pdo->prepare("DELETE FROM asistencia WHERE sesion_id = ?");
            $del->execute([$sessionId]);

            // 2.2) Insertar cada nueva asistencia
            $ins = $this->pdo->prepare("
                INSERT INTO asistencia (sesion_id, estudiante, estado)
                VALUES (?, ?, ?)
            ");
            foreach ($attendance as $codigo => $estado) {
                $ins->execute([$sessionId, $codigo, $estado]);
            }

            $this->pdo->commit();
            return '<div class="alert alert-success text-center">'
                 . 'Asistencia guardada correctamente.</div>';
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return '<div class="alert alert-danger text-center">'
                 . 'Error al guardar asistencia: '
                 . htmlspecialchars($e->getMessage())
                 . '</div>';
        }
    }

    /**
     * 3) Recupera el estado de un único estudiante en una sesión.
     */
    public function get_attendance_status_student_controller(int $sessionId, string $codigo): ?string {
        $stmt = $this->pdo->prepare("
            SELECT estado
              FROM asistencia
             WHERE sesion_id = ?
               AND estudiante = ?
        ");
        $stmt->execute([$sessionId, $codigo]);
        $r = $stmt->fetchColumn();
        return ($r !== false) ? $r : null;
    }
    public function get_history_by_student_controller(string $codigo): array {
            $sql = "
                SELECT 
                  cu.Nombre     AS Curso,
                  s.Titulo      AS Sesion,
                  a.fecha       AS Fecha,
                  a.estado      AS Estado
                FROM asistencia a
                JOIN sesion s 
                  ON s.id = a.sesion_id
                JOIN curso cu 
                  ON cu.id = s.CursoId
                WHERE a.estudiante = ?
                ORDER BY a.fecha DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$codigo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }   
    public function get_history_by_student_course_controller(string $codigo, int $cursoId): array {
    $sql = "
        SELECT 
          cu.Nombre   AS Curso,
          s.Titulo    AS Sesion,
          a.fecha     AS Fecha,
          a.estado    AS Estado
        FROM asistencia a
        JOIN sesion s 
          ON s.id = a.sesion_id
        JOIN curso cu 
          ON cu.id = s.CursoId
        WHERE a.estudiante = ?
          AND cu.id = ?
        ORDER BY a.fecha DESC
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$codigo, $cursoId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
