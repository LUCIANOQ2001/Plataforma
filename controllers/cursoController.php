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

        if(!$nombre || !$descripcion || !$docente) {
            return '<div class="alert alert-warning text-center">
                      Complete todos los campos.
                    </div>';
        }

        try {
            // 1) Si el docente no está en `docente`, lo damos de alta con datos de `cuenta`
            $chk = $this->pdo->prepare("SELECT 1 FROM docente WHERE Codigo = ?");
            $chk->execute([$docente]);
            if(!$chk->fetch()) {
                // obtenemos Usuario de la cuenta
                $u = $this->pdo->prepare("SELECT Usuario FROM cuenta WHERE Codigo = ?");
                $u->execute([$docente]);
                $usuario = $u->fetchColumn() ?: '';
                $insDoc = $this->pdo->prepare("
                    INSERT INTO docente (Codigo, Nombres, Apellidos, Email)
                    VALUES (?, ?, ?, '')
                ");
                $insDoc->execute([$docente, $usuario, $usuario]);
            }

            // 2) Ahora insertamos el curso
            $ins = $this->pdo->prepare("
                INSERT INTO curso (Nombre, Descripcion, DocenteCodigo)
                VALUES (?, ?, ?)
            ");
            $ins->execute([$nombre, $descripcion, $docente]);

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
            SELECT id, Nombre, Descripcion
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
            SELECT c.id, c.Nombre, c.Descripcion
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
    public function get_curso_by_id_controller(int $cursoId): PDOStatement {
        $stmt = $this->pdo->prepare("
            SELECT id, Nombre, Descripcion, DocenteCodigo
            FROM curso
            WHERE id = ?
        ");
        $stmt->execute([$cursoId]);
        return $stmt;
    }


    public function count_cursos() {
    $stmt = mainModel::ejecutar_consulta_simple("SELECT COUNT(*) AS total FROM curso");
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    public function count_cursos_by_estudiante($codigoEstudiante) {
        $cursoModel = new cursoModel();
        return $cursoModel->count_cursos_by_estudiante_model($codigoEstudiante);
    }
    
}
