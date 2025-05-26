<?php
class foroController {
    private $pdo;

    public function __construct(){
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root','',
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
        );
    }

    /**
     * Crea un foro y guarda el archivo con su nombre original.
     */
    public function add_foro(int $sesionId, array $post, array $files): string {
        $titulo      = trim($post['titulo']      ?? '');
        $descripcion = trim($post['descripcion'] ?? '');
        $fechaCierre = trim($post['fechacierre'] ?? null);

        if (!$titulo || !$descripcion) {
            return '<div class="alert alert-warning text-center">Complete título y descripción.</div>';
        }

        // Procesar archivo adjunto
        $archivoNombre = null;
        if (!empty($files['archivo']['name'])) {
            $origName = basename($files['archivo']['name']);
            $folder   = __DIR__ . "/../../uploads/foros/";
            if (!is_dir($folder)) mkdir($folder, 0755, true);
            $dest = $folder . $origName;
            if (!move_uploaded_file($files['archivo']['tmp_name'], $dest)) {
                return '<div class="alert alert-danger text-center">Error al subir archivo.</div>';
            }
            $archivoNombre = $origName;
        }

        // Insertar en la base de datos
        $sql = "
            INSERT INTO foro 
                (sesion_id, Titulo, Descripcion, FechaCierre" 
               . ($archivoNombre ? ", Archivo" : "") . ")
            VALUES 
                (?, ?, ?, ?" 
               . ($archivoNombre ? ", ?" : "") . ")
        ";
        $params = [$sesionId, $titulo, $descripcion, $fechaCierre ?: null];
        if ($archivoNombre) {
            $params[] = $archivoNombre;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return '<div class="alert alert-success text-center">Foro creado correctamente.</div>';
    }

    /**
     * Obtiene los datos de un foro por su ID.
     */
    public function get_foro(int $foroId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT id, sesion_id, Titulo, Descripcion, FechaSubida, FechaCierre, Archivo
              FROM foro
             WHERE id = ?
        ");
        $stmt->execute([$foroId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Lista todos los foros de una sesión dada.
     */
    public function list_foros_by_sesion(int $sesionId): array {
        $stmt = $this->pdo->prepare("
            SELECT id, Titulo, Descripcion, FechaSubida, FechaCierre, Archivo
              FROM foro
             WHERE sesion_id = ?
             ORDER BY FechaSubida DESC
        ");
        $stmt->execute([$sesionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
  public function add_comentario(int $foroId, string $userCodigo, array $post, array $files): string {
    $texto = trim($post['comentario'] ?? '');
    if (!$texto) {
      return '<div class="alert alert-warning">Escribe un comentario.</div>';
    }
    $adjuntoNombre = null;
    if (!empty($files['adjunto']['name'])) {
      $ext = strtolower(pathinfo($files['adjunto']['name'], PATHINFO_EXTENSION));
      $permitidos = ['pdf','doc','docx','jpg','png','zip'];
      if (!in_array($ext, $permitidos)) {
        return '<div class="alert alert-danger">Tipo de archivo no permitido.</div>';
      }
      $folder = __DIR__ . '/../../uploads/foros/';
      if (!is_dir($folder)) mkdir($folder,0755,true);
      $adjuntoNombre = uniqid('foro_').'.'.$ext;
      if (!move_uploaded_file($files['adjunto']['tmp_name'], $folder.$adjuntoNombre)) {
        return '<div class="alert alert-danger">Error al subir adjunto.</div>';
      }
    }
    $stmt = $this->pdo->prepare("
      INSERT INTO foro_comentario (ForoId, UsuarioCodigo, Comentario, Adjunto)
      VALUES (?,?,?,?)
    ");
    $stmt->execute([$foroId, $userCodigo, $texto, $adjuntoNombre]);
    return '<div class="alert alert-success">Comentario enviado.</div>';
  }

  public function list_comentarios(int $foroId): array {
    $stmt = $this->pdo->prepare("
      SELECT id, UsuarioCodigo, Comentario, Fecha, Adjunto
        FROM foro_comentario
       WHERE ForoId = ?
       ORDER BY Fecha ASC
    ");
    $stmt->execute([$foroId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

}
