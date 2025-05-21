<?php
class asistenciaController {
  private $pdo;

  public function __construct(){
    // Ajusta aquí tu conexión
    $this->pdo = new PDO(
      'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
      'root','',
      [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
    );
  }

  /**
   * Obtiene el título de la clase y la lista de estudiantes
   * con su asistencia previa (si existe) para esta clase.
   */
  public function get_students_by_class_controller(int $classId): array {
    // 1) título de la clase
    $stmt = $this->pdo->prepare("SELECT Titulo FROM clase WHERE id = ?");
    $stmt->execute([$classId]);
    $title = $stmt->fetchColumn() ?: 'Sin título';

    // 2) lista de estudiantes + LEFT JOIN a asistencia
    $stmt2 = $this->pdo->prepare("
      SELECT e.Codigo, e.Nombres, e.Apellidos,
             COALESCE(a.estado,'presente') AS estado
        FROM estudiante e
        LEFT JOIN asistencia a
          ON a.estudiante = e.Codigo
         AND a.clase_id = ?
      ORDER BY e.Apellidos, e.Nombres
    ");
    $stmt2->execute([$classId]);
    $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    return ['classTitle'=>$title, 'rows'=>$rows];
  }

  /**
   * Guarda (o reemplaza) la asistencia de una clase.
   * $post['asistencia'] es un array ['EC012...'=>'ausente', ...]
   */

   public function get_history_by_student_controller(string $codigo): array {
  $stmt = $this->pdo->prepare("
    SELECT a.fecha, c.Titulo, a.estado
      FROM asistencia a
      JOIN clase c ON a.clase_id = c.id
     WHERE a.estudiante = ?
     ORDER BY a.fecha DESC
  ");
  $stmt->execute([$codigo]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

  public function save_attendance_controller(int $classId, array $post): string {
    if(empty($post['asistencia'])) {
      return '<div class="alert alert-warning text-center">No hay datos de asistencia.</div>';
    }
    try {
      $this->pdo->beginTransaction();
      // Borra registros existentes de esta clase (opcional)
      $del = $this->pdo->prepare("DELETE FROM asistencia WHERE clase_id = ?");
      $del->execute([$classId]);
      // Inserta nuevos
      $ins = $this->pdo->prepare("
        INSERT INTO asistencia (clase_id, estudiante, estado)
        VALUES (?, ?, ?)
      ");
      foreach($post['asistencia'] as $codigo => $estado){
        $ins->execute([$classId, $codigo, $estado]);
      }
      $this->pdo->commit();
      return '<div class="alert alert-success text-center">Asistencia guardada.</div>';
    } catch(Exception $e) {
      $this->pdo->rollBack();
      return '<div class="alert alert-danger text-center">Error al guardar: '.$e->getMessage().'</div>';
    }
  }
}
