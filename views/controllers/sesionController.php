<?php
// controllers/sesionController.php

class sesionController {
    /** @var \PDO */
    private $pdo;

    public function __construct(){
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root','',
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
        );
    }

    /**
     * Devuelve un PDOStatement con los datos de la sesión,
     * incluyendo CursoId para poder navegar de vuelta al curso.
     */
    public function get_sesion_by_id_controller(int $id): PDOStatement {
        $stmt = $this->pdo->prepare("
            SELECT 
                id,
                CursoId,
                Titulo,
                Fecha,
                Video
              FROM sesion
             WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt;
    }

    /**
     * Lista las sesiones de un curso dado.
     */
    public function list_sesiones_controller(int $cursoId): array {
        $stmt = $this->pdo->prepare("
            SELECT 
                id,
                CursoId,
                Titulo,
                Fecha,
                Video
              FROM sesion
             WHERE CursoId = ?
             ORDER BY Fecha ASC
        ");
        $stmt->execute([$cursoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Inserta una nueva sesión para un curso.
     */
    public function add_sesion_controller(int $cursoId, array $post): string {
        $titulo = trim($post['titulo'] ?? '');
        $fecha  = trim($post['fecha']  ?? '');
        $video  = trim($post['video']  ?? '');

        if (!$titulo || !$fecha) {
            return '<div class="alert alert-warning text-center">'
                 . 'Complete título y fecha.</div>';
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO sesion (CursoId, Titulo, Fecha, Video)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$cursoId, $titulo, $fecha, $video]);

        return '<div class="alert alert-success text-center">'
             . 'Sesión creada correctamente.</div>';
    }

    // ----- Grabaciones -----

    public function list_grabaciones_by_sesion_controller(int $sesionId): array {
        $stmt = $this->pdo->prepare("
            SELECT id, archivo, fecha
              FROM grabacion
             WHERE sesion_id = ?
             ORDER BY fecha DESC
        ");
        $stmt->execute([$sesionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add_grabacion_controller(int $sesionId): string {
        if(empty($_FILES['grabacion']['name'])){
            return '<div class="alert alert-warning text-center">'
                 . 'Seleccione un archivo.</div>';
        }
        $file    = $_FILES['grabacion'];
        $ext     = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['mp4','mkv','mp3','wav'];
        if(!in_array(strtolower($ext), $allowed)){
            return '<div class="alert alert-danger text-center">'
                 . 'Formato no permitido.</div>';
        }
        $folder  = __DIR__ . "/../../uploads/grabaciones/";
        if(!is_dir($folder)) mkdir($folder,0755,true);
        $newName = uniqid('grab_').'.'.$ext;
        if(move_uploaded_file($file['tmp_name'], $folder.$newName)){
            $stmt = $this->pdo->prepare("
                INSERT INTO grabacion (sesion_id, archivo)
                VALUES (?, ?)
            ");
            $stmt->execute([$sesionId, $newName]);
            return '<div class="alert alert-success text-center">'
                 . 'Grabación guardada.</div>';
        } else {
            return '<div class="alert alert-danger text-center">'
                 . 'Error al subir archivo.</div>';
        }
    }

    public function delete_grabacion_controller(int $id): string {
        $get = $this->pdo->prepare("SELECT archivo FROM grabacion WHERE id = ?");
        $get->execute([$id]);
        if($row = $get->fetch(PDO::FETCH_ASSOC)){
            $path = __DIR__ . "/../../uploads/grabaciones/" . $row['archivo'];
            if(is_file($path)) unlink($path);
            $del = $this->pdo->prepare("DELETE FROM grabacion WHERE id = ?");
            $del->execute([$id]);
            return '<div class="alert alert-success text-center">'
                 . 'Grabación eliminada.</div>';
        }
        return '<div class="alert alert-danger text-center">'
             . 'No encontrado.</div>';
    }

    public function update_grabacion_controller(int $id, string $newLabel): string {
        $upd = $this->pdo->prepare("UPDATE grabacion SET archivo = ? WHERE id = ?");
        $upd->execute([$newLabel, $id]);
        return '<div class="alert alert-success text-center">'
             . 'Grabación actualizada.</div>';
    }

    // ----- Foros -----

    public function list_foros_by_sesion_controller(int $sesionId): array {
        $stmt = $this->pdo->prepare("
            SELECT id, Titulo, Descripcion, FechaSubida, FechaCierre
              FROM foro
             WHERE sesion_id = ?
             ORDER BY FechaSubida DESC
        ");
        $stmt->execute([$sesionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add_foro_controller(int $sesionId, array $post): string {
        $titulo      = trim($post['titulo'] ?? '');
        $descripcion = trim($post['descripcion'] ?? '');
        $cierre      = trim($post['fecha_cierre'] ?? null);

        if (!$titulo || !$descripcion) {
            return '<div class="alert alert-warning text-center">'
                 . 'Complete título y descripción.</div>';
        }
        $stmt = $this->pdo->prepare("
            INSERT INTO foro (sesion_id, Titulo, Descripcion, FechaCierre)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$sesionId, $titulo, $descripcion, $cierre ?: null]);
        return '<div class="alert alert-success text-center">'
             . 'Foro creado.</div>';
    }

    public function get_foro_by_id_controller(int $foroId): PDOStatement {
        $stmt = $this->pdo->prepare("
            SELECT id, sesion_id, Titulo, Descripcion,
                   FechaSubida, FechaCierre
              FROM foro
             WHERE id = ?
        ");
        $stmt->execute([$foroId]);
        return $stmt;
    }

    // ----- Respuestas -----

    public function list_respuestas_by_foro_controller(int $foroId): array {
        $stmt = $this->pdo->prepare("
            SELECT r.id, r.Respuesta, r.Fecha, r.estudiante_codigo,
                   CONCAT(e.Nombres,' ',e.Apellidos) AS Alumno
              FROM foro_respuesta r
              JOIN estudiante e ON e.Codigo = r.estudiante_codigo
             WHERE r.foro_id = ?
             ORDER BY r.Fecha ASC
        ");
        $stmt->execute([$foroId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add_respuesta_controller(int $foroId, array $post, string $estCodigo): string {
        $texto = trim($post['respuesta'] ?? '');
        if (!$texto) {
            return '<div class="alert alert-warning text-center">'
                 . 'Escriba su respuesta.</div>';
        }
        $stmt = $this->pdo->prepare("
            INSERT INTO foro_respuesta (foro_id, estudiante_codigo, Respuesta)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$foroId, $estCodigo, $texto]);
        return '<div class="alert alert-success text-center">'
             . 'Respuesta enviada.</div>';
    }

    public function delete_respuesta_controller(int $id): string {
        $stmt = $this->pdo->prepare("DELETE FROM foro_respuesta WHERE id = ?");
        $stmt->execute([$id]);
        return '<div class="alert alert-success text-center">'
             . 'Respuesta eliminada.</div>';
    }
}
