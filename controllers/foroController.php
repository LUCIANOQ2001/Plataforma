<?php
// controllers/foroController.php

class foroController {
    /** @var \PDO */
    private $pdo;

    public function __construct(){
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root','',
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
        );
    }

    /** Lista todos los foros de una sesión */
    public function list_foros_by_sesion(int $sesionId): array {
        $stmt = $this->pdo->prepare("
            SELECT id, Titulo, Descripcion,
                   FechaSubida, FechaCierre, Archivo
              FROM foro
             WHERE sesion_id = ?
             ORDER BY FechaSubida DESC
        ");
        $stmt->execute([$sesionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Recupera un foro por su ID */
    public function get_foro(int $foroId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT id, sesion_id, Titulo, Descripcion,
                   FechaSubida, FechaCierre, Archivo
              FROM foro
             WHERE id = ?
        ");
        $stmt->execute([$foroId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Crea un foro y guarda el archivo (opcional) en attachments/foros/
     */
    public function add_foro(int $sesionId, array $post, array $files): string {
        $titulo      = trim($post['titulo']      ?? '');
        $descripcion = trim($post['descripcion'] ?? '');
        $fechaCierre = trim($post['fechacierre'] ?? null);

        if (!$titulo || !$descripcion) {
            return '<div class="alert alert-warning text-center">'
                 . 'Título y descripción son obligatorios.</div>';
        }

        $uploadDir = __DIR__ . '/../attachments/foros/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = null;
        if (!empty($files['archivo']['name'])) {
            $origName  = basename($files['archivo']['name']);
            $cleanName = preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', $origName);
            $filename  = time() . '_' . $cleanName;

            if (!is_uploaded_file($files['archivo']['tmp_name'])
                || !move_uploaded_file($files['archivo']['tmp_name'], $uploadDir . $filename)
            ) {
                return '<div class="alert alert-danger text-center">'
                     . 'Error al subir el archivo.</div>';
            }
        }

        $sql = "
            INSERT INTO foro
              (sesion_id, Titulo, Descripcion, FechaCierre"
              . ($filename ? ", Archivo" : "") . ")
            VALUES (?, ?, ?, ?"
              . ($filename ? ", ?" : "") . ")
        ";
        $params = [$sesionId, $titulo, $descripcion, $fechaCierre ?: null];
        if ($filename) $params[] = $filename;

        $this->pdo->prepare($sql)->execute($params);

        return '<div class="alert alert-success text-center">'
             . 'Foro creado correctamente.</div>';
    }

    /**
     * Actualiza un foro y opcionalmente su archivo adjunto.
     */
    public function update_foro(int $foroId, array $post, array $files): string {
        $titulo      = trim($post['titulo']      ?? '');
        $descripcion = trim($post['descripcion'] ?? '');
        $fechaCierre = trim($post['fechacierre'] ?? null);

        if (!$titulo || !$descripcion) {
            return '<div class="alert alert-warning text-center">'
                 . 'Título y descripción son obligatorios.</div>';
        }

        // obtener nombre de archivo viejo
        $stmt0 = $this->pdo->prepare("SELECT Archivo FROM foro WHERE id = ?");
        $stmt0->execute([$foroId]);
        $oldFile = $stmt0->fetchColumn();

        $uploadDir = __DIR__ . '/../attachments/foros/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        // procesar nuevo archivo si viene
        $filename = $oldFile;
        if (!empty($files['archivo']['name'])) {
            // borrar antiguo
            if ($oldFile && file_exists($uploadDir . $oldFile)) {
                @unlink($uploadDir . $oldFile);
            }
            $origName  = basename($files['archivo']['name']);
            $cleanName = preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', $origName);
            $filename  = time() . '_' . $cleanName;

            if (!is_uploaded_file($files['archivo']['tmp_name'])
                || !move_uploaded_file($files['archivo']['tmp_name'], $uploadDir . $filename)
            ) {
                return '<div class="alert alert-danger text-center">'
                     . 'Error al subir el nuevo archivo.</div>';
            }
        }

        // actualizar BD
        $stmt = $this->pdo->prepare("
            UPDATE foro SET
              Titulo      = ?,
              Descripcion = ?,
              FechaCierre = ?,
              Archivo     = ?
             WHERE id = ?
        ");
        $stmt->execute([$titulo, $descripcion, $fechaCierre ?: null, $filename, $foroId]);

        return '<div class="alert alert-success text-center">'
             . 'Foro actualizado correctamente.</div>';
    }

    /**
     * Elimina un foro y su archivo adjunto.
     */
    public function delete_foro(int $foroId): string {
        // obtener archivo
        $stmt0 = $this->pdo->prepare("SELECT Archivo FROM foro WHERE id = ?");
        $stmt0->execute([$foroId]);
        $oldFile = $stmt0->fetchColumn();

        $uploadDir = __DIR__ . '/../attachments/foros/';
        if ($oldFile && file_exists($uploadDir . $oldFile)) {
            @unlink($uploadDir . $oldFile);
        }

        // borrar registro
        $stmt = $this->pdo->prepare("DELETE FROM foro WHERE id = ?");
        $stmt->execute([$foroId]);

        return '<div class="alert alert-success text-center">'
             . 'Foro eliminado correctamente.</div>';
    }

    /** Añade un comentario con adjunto opcional */
    public function add_comentario(int $foroId, string $userCodigo, array $post, array $files): string {
        $texto = trim($post['comentario'] ?? '');
        if (!$texto) {
            return '<div class="alert alert-warning text-center">'
                 . 'Escribe un comentario.</div>';
        }

        $uploadDir = __DIR__ . '/../attachments/foros/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $adjName = null;
        if (!empty($files['adjunto']['name'])) {
            $ext       = pathinfo($files['adjunto']['name'], PATHINFO_EXTENSION);
            $cleanBase = preg_replace('/[^a-zA-Z0-9_\-]/','_',
                                     pathinfo($files['adjunto']['name'], PATHINFO_FILENAME));
            $adjName   = time() . "_{$cleanBase}.{$ext}";

            if (!is_uploaded_file($files['adjunto']['tmp_name'])
                || !move_uploaded_file($files['adjunto']['tmp_name'], $uploadDir . $adjName)
            ) {
                return '<div class="alert alert-danger text-center">'
                     . 'Error al subir adjunto.</div>';
            }
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO foro_comentario
              (ForoId, UsuarioCodigo, Comentario, Adjunto)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$foroId, $userCodigo, $texto, $adjName]);

        return '<div class="alert alert-success text-center">'
             . 'Comentario enviado.</div>';
    }
/**
 * Lista los comentarios de un foro, incluyendo el nombre completo del usuario
 */
    public function list_comentarios(int $foroId): array {
        $sql = "
            SELECT 
                fc.id,
                fc.UsuarioCodigo,
                fc.Comentario,
                fc.Fecha,
                fc.Adjunto,
                -- Recuperamos el tipo de cuenta y luego, según sea, sacamos nombre y apellidos
                cu.Tipo AS TipoCuenta,
                -- Intentamos obtener el nombre desde estudiante, docente o admin
                COALESCE(
                    CONCAT(e.Nombres, ' ', e.Apellidos),
                    CONCAT(d.Nombres, ' ', d.Apellidos),
                    CONCAT(a.Nombres, ' ', a.Apellidos),
                    cu.Usuario           -- como fallback: si no existe en ninguna tabla, muestro el UserName
                ) AS NombreUsuario
            FROM foro_comentario fc
            INNER JOIN cuenta cu
                ON fc.UsuarioCodigo = cu.Codigo
            LEFT JOIN estudiante e
                ON cu.Codigo = e.Codigo
            LEFT JOIN docente d
                ON cu.Codigo = d.Codigo
            LEFT JOIN admin a
                ON cu.Codigo = a.Codigo
            WHERE fc.ForoId = ?
            ORDER BY fc.Fecha ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$foroId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
