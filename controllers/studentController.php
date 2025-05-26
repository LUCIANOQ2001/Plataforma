<?php
class studentController {
    private $pdo;

    public function __construct(){
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root','',
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
        );
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
}
