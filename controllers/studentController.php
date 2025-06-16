<?php
require_once __DIR__ . '/../models/studentModel.php';


class studentController {
    private $pdo;
    private $model;

    public function __construct(){
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root','',
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
        );
        $this->model = new studentModel();
    }

    public function count_estudiantes() {
        $stmt = mainModel::ejecutar_consulta_simple("SELECT COUNT(*) AS total FROM estudiante");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    /**
     * Trae todos los cursos disponibles para asignar
     */
    public function list_cursos_controller(): array {
        $stmt = $this->pdo->query("SELECT id, Nombre FROM curso ORDER BY Nombre");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Inserta un nuevo estudiante (+ cuenta) y asigna los cursos marcados.
     * Recibe $_POST completo.
     */
    public function add_student_controller(array $post): string {
        if (empty($post['name']) || empty($post['lastname']) || empty($post['username'])
            || empty($post['password1']) || empty($post['password2'])
            || empty($post['cursos'] ?? [])) {
            return '<div class="alert alert-warning text-center">
                      Complete todos los campos.
                    </div>';
        }
        if ($post['password1'] !== $post['password2']) {
            return '<div class="alert alert-warning text-center">
                      Las contraseñas no coinciden.
                    </div>';
        }

        try {
            $this->pdo->beginTransaction();

            // 1) Crear cuenta
            $usuario   = trim($post['username']);
            $clave     = password_hash($post['password1'], PASSWORD_BCRYPT);
            $tipo      = 'Estudiante';
            $genero    = $post['gender'] ?? '';
            $codigoEst = uniqid();

            $ins1 = $this->pdo->prepare("
                INSERT INTO cuenta (Privilegio, Usuario, Clave, Tipo, Genero, Codigo)
                VALUES (4,?,?,?,?,?)
            ");
            $ins1->execute([$usuario, $clave, $tipo, $genero, $codigoEst]);

            // 2) Crear estudiante
            $nombres   = trim($post['name']);
            $apellidos = trim($post['lastname']);
            $email     = trim($post['email'] ?? '');

            $ins2 = $this->pdo->prepare("
                INSERT INTO estudiante (Codigo, Nombres, Apellidos, Email)
                VALUES (?,?,?,?)
            ");
            $ins2->execute([$codigoEst, $nombres, $apellidos, $email]);

            // 3) Asignar cursos
            $asigna = $this->pdo->prepare("
                INSERT IGNORE INTO curso_estudiante (CursoId, EstudianteCodigo)
                VALUES (?,?)
            ");
            foreach ($post['cursos'] as $cursoId) {
                $asigna->execute([(int)$cursoId, $codigoEst]);
            }

            $this->pdo->commit();
            return '<div class="alert alert-success text-center">
                      Estudiante creado correctamente.
                    </div>';
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return '<div class="alert alert-danger text-center">
                      Error al guardar estudiante:<br>'.htmlspecialchars($e->getMessage()).'
                    </div>';
        }
    }

    /**
     * Elimina un estudiante (y sus asignaciones de curso).
     */
    public function delete_student_controller(string $codigo): string {
        try {
            $del = $this->pdo->prepare("DELETE FROM estudiante WHERE Codigo = ?");
            $del->execute([$codigo]);
            return '<div class="alert alert-success text-center">
                      Estudiante eliminado.
                    </div>';
        } catch (PDOException $e) {
            return '<div class="alert alert-danger text-center">
                      Error al eliminar estudiante:<br>'.htmlspecialchars($e->getMessage()).'
                    </div>';
        }
    }

    /**
     * Pagina el listado de estudiantes y devuelve la tabla HTML.
     * Corrige offset negativo si $page < 1.
     */
    public function pagination_student_controller(int $page = 1, int $limit = 10): string {
        // Asegurar que la página mínima sea 1
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $limit;

        // total de estudiantes
        $total = $this->pdo->query("SELECT COUNT(*) FROM estudiante")->fetchColumn();
        $pages = max(1, ceil($total / $limit));

        // datos con GROUP_CONCAT de cursos
        $stmt = $this->pdo->prepare("
            SELECT 
              e.Codigo,
              CONCAT(e.Apellidos, ', ', e.Nombres) AS NombreCompleto,
              e.Email,
              COALESCE(
                GROUP_CONCAT(c.Nombre ORDER BY c.Nombre SEPARATOR ', '),
                '—'
              ) AS Cursos
            FROM estudiante e
            LEFT JOIN curso_estudiante ce 
              ON ce.EstudianteCodigo = e.Codigo
            LEFT JOIN curso c 
              ON c.id = ce.CursoId
            GROUP BY e.Codigo
            ORDER BY e.Apellidos, e.Nombres
            LIMIT :offset, :limit
        ");
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // arma la tabla
        $html = '<table class="table"><thead><tr>'
              .'<th>Código</th><th>Nombre completo</th><th>Email</th><th>Cursos</th><th>Acciones</th>'
              .'</tr></thead><tbody>';
        if ($rows) {
            foreach ($rows as $r) {
                $html .= '<tr>'
                       .'<td>'.htmlspecialchars($r['Codigo']).'</td>'
                       .'<td>'.htmlspecialchars($r['NombreCompleto']).'</td>'
                       .'<td>'.htmlspecialchars($r['Email']).'</td>'
                       .'<td>'.htmlspecialchars($r['Cursos']).'</td>'
                       .'<td>
                           <form method="POST" style="display:inline;">
                             <input type="hidden" name="studentCode" value="'.htmlspecialchars($r['Codigo']).'">
                             <button onclick="return confirm(\'¿Eliminar este estudiante?\')" 
                                     class="btn btn-danger btn-xs">
                               <i class="zmdi zmdi-delete"></i>
                             </button>
                           </form>
                           <a href="'.SERVERURL.'studentinfo/'.htmlspecialchars($r['Codigo']).'/" 
                                class="btn btn-warning btn-xs" title="Editar">
                                <i class="zmdi zmdi-edit"></i>
                            </a>
                         </td>'
                       .'</tr>';
            }
        } else {
            $html .= '<tr><td colspan="5">No hay estudiantes registrados.</td></tr>';
        }
        $html .= '</tbody></table>';

        // paginación
        $html .= '<nav><ul class="pagination">';
        for ($i = 1; $i <= $pages; $i++) {
            $act = $i === $page ? ' active' : '';
            $html .= '<li class="page-item'.$act.'">'
                   .'<a class="page-link" href="'.SERVERURL.'studentlist/'.$i.'/">'.$i.'</a>'
                   .'</li>';
        }
        $html .= '</ul></nav>';

        return $html;
    }
    public function data_student_controller(string $tipo, string $codigo) {
        if ($tipo !== "Only") {
            return false;
        }

        $stmt = $this->pdo->prepare("SELECT * FROM estudiante WHERE Codigo = ?");
        $stmt->execute([$codigo]);
        return $stmt;
    }
    public function update_student_controller(): string {
        $codigo   = $_POST['code'] ?? '';
        $nombres  = trim($_POST['name'] ?? '');
        $apellidos= trim($_POST['lastname'] ?? '');
        $email    = trim($_POST['email'] ?? '');

        if (!$codigo || !$nombres || !$apellidos) {
            return '<div class="alert alert-warning text-center">Faltan datos obligatorios.</div>';
        }

        try {
            $stmt = $this->pdo->prepare("
                UPDATE estudiante 
                SET Nombres = ?, Apellidos = ?, Email = ?
                WHERE Codigo = ?
            ");
            $stmt->execute([$nombres, $apellidos, $email, $codigo]);

            return '<div class="alert alert-success text-center">Datos actualizados correctamente.</div>';
        } catch (PDOException $e) {
            return '<div class="alert alert-danger text-center">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
    /**
 * Lista paginada de estudiantes para el docente logueado.
 */
  /**
     * Lista paginada de estudiantes para el docente logueado (con promedio).
     */
    public function pagination_students_by_docente_controller(int $page = 1, int $limit = 10): string {
        $page   = max(1, $page);
        $offset = ($page - 1) * $limit;
        $docenteCodigo = $_SESSION['userKey'] ?? '';

        // Total de alumnos para este docente
        $stmtTotal = $this->pdo->prepare("
        SELECT COUNT(DISTINCT ce.EstudianteCodigo) AS total
        FROM curso_estudiante ce
        INNER JOIN curso c 
            ON ce.CursoId = c.id
        WHERE c.DocenteCodigo = ?
        ");
        $stmtTotal->execute([$docenteCodigo]);
        $total = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

        $pages = max(1, ceil($total / $limit));

        // Traer los datos con promedio
        $students = $this->model
                         ->list_students_by_docente_model($docenteCodigo, $offset, $limit);

        // Construir la tabla<th>Acciones</th>
        $html = '<table class="table"><thead><tr>'
              .'<th>Código</th><th>Nombre completo</th><th>Email</th>'
              .'<th>Cursos</th><th>Promedio</th> '
              .'</tr></thead><tbody>';
        if($students){
            foreach($students as $r){
                $html .= '<tr>'
                       .'<td>'.htmlspecialchars($r['Codigo']).'</td>'
                       .'<td>'.htmlspecialchars($r['NombreCompleto']).'</td>'
                       .'<td>'.htmlspecialchars($r['Email']).'</td>'
                       .'<td>'.htmlspecialchars($r['Cursos']).'</td>'
                       .'<td>'.htmlspecialchars($r['Promedio']).'</td>'
                      /* .'<td>
                          <a href="'.SERVERURL.'studentinfo/'.htmlspecialchars($r['Codigo']).'/" 
                             class="btn btn-warning btn-xs" title="Editar">
                            <i class="zmdi zmdi-edit"></i>
                          </a>
                         </td>'*/
                       .'</tr>';
            }
        } else {
            $html .= '<tr><td colspan="6">No hay estudiantes en tus cursos.</td></tr>';
        }
        $html .= '</tbody></table>';

        // Paginación
        $html .= '<nav><ul class="pagination">';
        for($i = 1; $i <= $pages; $i++){
            $act = $i === $page ? ' active' : '';
            $html .= '<li class="page-item'.$act.'">'
                   .'<a class="page-link" href="'.SERVERURL.'teacher-students/'.$i.'/">'.$i.'</a>'
                   .'</li>';
        }
        $html .= '</ul></nav>';

        return $html;
    }

    /**
     * Decide la lista según rol: docente vs. administrador.
     */
    public function student_list_for_role_controller(int $page = 1, int $limit = 10): string {
        if($_SESSION['userType'] === 'Docente'){
            return $this->pagination_students_by_docente_controller($page, $limit);
        }
        return $this->pagination_student_controller($page, $limit);
    }

}
