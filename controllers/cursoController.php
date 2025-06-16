<?php
require_once __DIR__ . '/../models/cursoModel.php';

class cursoController {
    private $pdo;

    public function __construct(){
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root','',
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
        );
    }

    /**
     * Lista todos los docentes (tabla cuenta) para el <select>
     */
    public function list_docentes_controller(): array {
        $stmt = $this->pdo->prepare("
            SELECT cu.Codigo,
                   COALESCE(d.Nombres, cu.Usuario)   AS Nombres,
                   COALESCE(d.Apellidos, cu.Usuario) AS Apellidos
              FROM cuenta cu
         LEFT JOIN docente d
                ON d.Codigo = cu.Codigo
             WHERE cu.Tipo = 'Docente'
          ORDER BY Apellidos, Nombres
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Inserta un nuevo curso, asegurando antes la existencia del docente
     */
    public function add_curso_controller(): string {
        $nombre      = trim($_POST['nombre']      ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $docente     = trim($_POST['docente_codigo'] ?? '');
        $fechaIni    = trim($_POST['fecha_inicio']  ?? '');
        $fechaFin    = trim($_POST['fecha_fin']     ?? '');

        // Validaciones básicas
        if(!$nombre || !$descripcion || !$docente || !$fechaIni || !$fechaFin){
            return '<div class="alert alert-warning text-center">
                    Complete todos los campos.
                    </div>';
        }
        if($fechaFin < $fechaIni){
            return '<div class="alert alert-warning text-center">
                    La fecha de fin debe ser igual o posterior a la fecha de inicio.
                    </div>';
        }

        try {
            // 1) Asegurar docente en tabla docente (igual que antes)…
            $chk = $this->pdo->prepare("SELECT 1 FROM docente WHERE Codigo = ?");
            $chk->execute([$docente]);
            if(!$chk->fetch()){
                // … insertar en docente…
            }

            // 2) Insertar curso con fechas
            $ins = $this->pdo->prepare("
                INSERT INTO curso 
                (Nombre, Descripcion, DocenteCodigo, FechaInicio, FechaFin)
                VALUES (?, ?, ?, ?, ?)
            ");
            $ins->execute([
                $nombre, 
                $descripcion, 
                $docente, 
                $fechaIni, 
                $fechaFin
            ]);

            return '<div class="alert alert-success text-center">
                    Curso agregado correctamente.
                    </div>';
        } catch(PDOException $e) {
            return '<div class="alert alert-danger text-center">
                    Error al guardar el curso:<br>'
                    . htmlspecialchars($e->getMessage()) .
                '</div>';
        }
    }

 /**
 * Lista los cursos de un docente concreto
 */
public function list_mis_cursos_controller(string $docenteCodigo): array {
    $stmt = $this->pdo->prepare("
        SELECT id, Nombre, Descripcion, FechaInicio, FechaFin
          FROM curso
         WHERE DocenteCodigo = ?
         ORDER BY Nombre
    ");
    $stmt->execute([$docenteCodigo]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lista cursos en los que está inscrito un estudiante
 */
public function list_cursos_estudiante_controller(string $estudianteCodigo): array {
    $stmt = $this->pdo->prepare("
        SELECT c.id, c.Nombre, c.Descripcion, c.FechaInicio, c.FechaFin
          FROM curso c
    INNER JOIN curso_estudiante ce ON ce.CursoId = c.id
         WHERE ce.EstudianteCodigo = ?
      ORDER BY c.Nombre
    ");
    $stmt->execute([$estudianteCodigo]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
        /**
     * Lista **todos** los cursos de la plataforma
     */
    public function list_cursos_controller(): array {
        $stmt = $this->pdo->query("
            SELECT id, Nombre, Descripcion
              FROM curso
             ORDER BY Nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
public function get_curso_by_id_controller(int $cursoId): ?array {
    $stmt = $this->pdo->prepare("SELECT Nombre FROM curso WHERE id = ?");
    $stmt->execute([$cursoId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

    public function count_cursos() {
    $stmt = mainModel::ejecutar_consulta_simple("SELECT COUNT(*) AS total FROM curso");
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    public function count_cursos_by_estudiante($codigoEstudiante) {
        $cursoModel = new cursoModel();
        return $cursoModel->count_cursos_by_estudiante_model($codigoEstudiante);
    }
    
    public function is_estudiante_inscrito_en_curso_controller(string $estCodigo, int $cursoId): bool {
    $sql = "
      SELECT 1
        FROM curso_estudiante
       WHERE EstudianteCodigo = ?
         AND CursoId = ?
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$estCodigo, $cursoId]);
    return $stmt->fetchColumn() !== false;
}

public function list_estudiantes_por_curso_controller(int $cursoId): array {
    $stmt = $this->pdo->prepare("
        SELECT e.Codigo, e.Nombres, e.Apellidos, e.Email
          FROM estudiante e
    INNER JOIN curso_estudiante ce ON ce.EstudianteCodigo = e.Codigo
         WHERE ce.CursoId = ?
      ORDER BY e.Apellidos, e.Nombres
    ");
    $stmt->execute([$cursoId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    /**
     * Cuenta alumnos distintos para un docente.
     */
    public function count_students_by_docente_controller(string $docenteCodigo): int {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT ce.EstudianteCodigo) AS total
              FROM curso_estudiante ce
              INNER JOIN curso c ON ce.CursoId = c.id
             WHERE c.DocenteCodigo = ?
        ");
        $stmt->execute([$docenteCodigo]);
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Obtiene array ['Nombre' => curso, 'Sesiones' => count] para graficar.
     */
    public function sessions_count_by_docente_controller(string $docenteCodigo): array {
        $stmt = $this->pdo->prepare("
            SELECT c.Nombre,
                   COUNT(s.id) AS Sesiones
              FROM curso c
         LEFT JOIN sesion s ON s.CursoId = c.id
             WHERE c.DocenteCodigo = ?
          GROUP BY c.id, c.Nombre
          ORDER BY c.Nombre
        ");
        $stmt->execute([$docenteCodigo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
