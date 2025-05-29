<?php
class anuncioController {
    private $pdo;

    public function __construct(){
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root','',
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
        );
    }

    /** Lista todos los anuncios de un curso */
    public function list_anuncios_by_curso_controller(int $cursoId): array {
        $stmt = $this->pdo->prepare("
            SELECT id, Titulo, Contenido, Fecha
              FROM anuncio
             WHERE CursoId = ?
             ORDER BY Fecha DESC
        ");
        $stmt->execute([$cursoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Inserta un nuevo anuncio */
    public function add_anuncio_controller(int $cursoId, array $post): string {
        $titulo    = trim($post['titulo']   ?? '');
        $contenido = trim($post['contenido'] ?? '');
        if (!$titulo || !$contenido) {
            return '<div class="alert alert-warning text-center">Complete título y contenido.</div>';
        }
        $ins = $this->pdo->prepare("
            INSERT INTO anuncio (CursoId, Titulo, Contenido)
            VALUES (?, ?, ?)
        ");
        $ins->execute([$cursoId, $titulo, $contenido]);
        return '<div class="alert alert-success text-center">Anuncio creado correctamente.</div>';
    }

    /** Actualiza título y contenido de un anuncio */
    public function update_anuncio_controller(int $id, array $post): string {
        $titulo    = trim($post['titulo']   ?? '');
        $contenido = trim($post['contenido'] ?? '');
        if (!$titulo || !$contenido) {
            return '<div class="alert alert-warning text-center">Complete título y contenido.</div>';
        }
        $upd = $this->pdo->prepare("
            UPDATE anuncio
               SET Titulo   = ?,
                   Contenido = ?
             WHERE id       = ?
        ");
        $upd->execute([$titulo, $contenido, $id]);
        return '<div class="alert alert-success text-center">Anuncio actualizado.</div>';
    }

    /** Borra un anuncio */
    public function delete_anuncio_controller(int $id): string {
        $del = $this->pdo->prepare("DELETE FROM anuncio WHERE id = ?");
        $del->execute([$id]);
        return '<div class="alert alert-success text-center">Anuncio eliminado.</div>';
    }

    /**
     * Lista los N anuncios más recientes a los que el usuario tiene acceso:
     * - Si es Estudiante: de sus cursos.
     * - Si es Docente o Admin: de los cursos que imparte/gestiona.
     *
     * IMPORTANTE: el LIMIT se inyecta directamente (sin parámetro) para evitar
     * errores de sintaxis en MariaDB.
     */
    public function list_recent_by_user_controller(string $userKey, string $userType, int $limit = 10): array {
        // Asegurarnos que $limit es un entero positivo
        $limit = max(1, intval($limit));

        if ($userType === 'Estudiante') {
            $sql = "
              SELECT a.Titulo,
                     a.Contenido,
                     a.Fecha,
                     c.Nombre AS Curso
                FROM anuncio a
                JOIN curso c
                  ON a.CursoId = c.id
                JOIN curso_estudiante ce
                  ON ce.CursoId = c.id
                 AND ce.EstudianteCodigo = ?
               ORDER BY a.Fecha DESC
               LIMIT $limit
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userKey]);

        } else {
            // Docente o Administrador: mostramos anuncios de cursos donde es docente
            $sql = "
              SELECT a.Titulo,
                     a.Contenido,
                     a.Fecha,
                     c.Nombre AS Curso
                FROM anuncio a
                JOIN curso c
                  ON a.CursoId = c.id
               WHERE c.DocenteCodigo = ?
               ORDER BY a.Fecha DESC
               LIMIT $limit
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userKey]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
