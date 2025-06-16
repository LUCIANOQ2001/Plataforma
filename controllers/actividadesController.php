<?php
// controllers/actividadesController.php

require_once __DIR__ . '/../models/actividadModel.php';

class actividadesController {
    /** @var \PDO */
    private $pdo;
    /** @var actividadModel */
    private $model;

    public function __construct(){
        $this->pdo   = new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root','',
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
        );
        $this->model = new actividadModel();
    }

    public function list_actividades_by_sesion_controller(int $sesionId): array {
        $stmt = $this->pdo->prepare("
            SELECT id, Titulo, Descripcion, FechaInicio, FechaCierre, Archivo
              FROM actividad
             WHERE sesion_id = ?
             ORDER BY FechaInicio ASC
        ");
        $stmt->execute([$sesionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

public function add_actividad_controller(int $sesionId, array $post, array $files): string {
    $titulo      = trim($post['titulo'] ?? '');
    $fechaIni    = trim($post['fecha_inicio'] ?? '');
    $fechaFin    = trim($post['fecha_cierre'] ?? '');
    $descripcion = trim($post['descripcion'] ?? '');

    // validaciones
    if (!$titulo || !$fechaIni || !$fechaFin) {
        return '<div class="alert alert-warning text-center">'
             . 'Complete título, fecha de inicio y fecha de cierre.'
             . '</div>';
    }
    if ($fechaFin < $fechaIni) {
        return '<div class="alert alert-warning text-center">'
             . 'La fecha de cierre debe ser igual o posterior a la fecha de inicio.'
             . '</div>';
    }

    $archivoName = null;
    // si enviaron un archivo, lo procesamos
    if (!empty($files['archivo']['name'])) {
        // 1) directorio igual que materialController
        $uploadDir = __DIR__ . '/../attachments/actividades/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 2) nombre original sanitizado
        $original  = $files['archivo']['name'];
        $cleanName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $original);
        $ext       = pathinfo($cleanName, PATHINFO_EXTENSION);
        $allowed   = ['pdf','doc','docx','zip'];
        if (!in_array(strtolower($ext), $allowed)) {
            return '<div class="alert alert-danger text-center">'
                 . 'Formato no permitido. Solo .pdf, .docx, .zip'
                 . '</div>';
        }

        // 3) mover el tmp al directorio binario intacto
        $dest = $uploadDir . $cleanName;
        if (!move_uploaded_file($files['archivo']['tmp_name'], $dest)) {
            return '<div class="alert alert-danger text-center">'
                 . 'Error al subir el archivo. Comprueba permisos de carpeta.'
                 . '</div>';
        }

        $archivoName = $cleanName;
    }

    // 4) insertar la actividad con el nombre de archivo (o null)
    $ins = $this->pdo->prepare("
        INSERT INTO actividad
        (sesion_id, Titulo, Descripcion, FechaInicio, FechaCierre, Archivo)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $ins->execute([
        $sesionId,
        $titulo,
        $descripcion,
        $fechaIni,
        $fechaFin,
        $archivoName
    ]);

    return '<div class="alert alert-success text-center">'
         . 'Actividad creada correctamente.'
         . '</div>';
}
    public function delete_actividad_controller(int $id): string {
        // Borrar archivo físico si existe
        $stmt = $this->pdo->prepare("SELECT Archivo FROM actividad WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetchColumn();
        if ($file) {
            $path = rtrim($_SERVER['DOCUMENT_ROOT'], '/')
                  . '/attachments/actividades/' . $file;
            if (is_file($path)) @unlink($path);
        }
        // Borrar registro
        $del = $this->pdo->prepare("DELETE FROM actividad WHERE id = ?");
        $del->execute([$id]);
        return '<div class="alert alert-success text-center">'
             . 'Actividad eliminada.'
             . '</div>';
    }



    /*— Entregas de los estudiantes —*/

    // Lista las entregas de una actividad
    public function list_entregas_by_actividad_controller(int $actividadId): array {
        $stmt = $this->pdo->prepare("
            SELECT ea.id,
                   ea.EstudianteCodigo,
                   CONCAT(e.Nombres,' ',e.Apellidos) AS Alumno,
                   ea.Archivo,
                   ea.FechaSubida,
                   ea.Nota,
                   ea.FechaCalificacion
              FROM entrega_actividad ea
         LEFT JOIN estudiante e ON e.Codigo = ea.EstudianteCodigo
             WHERE ea.actividad_id = ?
          ORDER BY ea.FechaSubida ASC
        ");
        $stmt->execute([$actividadId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

// controllers/actividadesController.php

public function add_entrega_controller(int $actividadId, string $estCodigo): string {
    // 1) Validar que venga archivo
    if (empty($_FILES['entrega']['name'])) {
        return '<div class="alert alert-warning text-center">
                    Seleccione un archivo.
                </div>';
    }

    // 2) Sanitizar nombre original
    $original  = $_FILES['entrega']['name'];
    $cleanName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $original);
    $ext       = pathinfo($cleanName, PATHINFO_EXTENSION);

    // 3) Validar extensión
    $allowed = ['pdf','doc','docx','zip'];
    if (!in_array(strtolower($ext), $allowed)) {
        return '<div class="alert alert-danger text-center">
                    Formato no permitido. Sólo .pdf, .docx, .zip
                </div>';
    }

    // 4) Preparar directorio público igual que materialController
    $uploadDir = __DIR__ . '/../attachments/trabajos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // 5) Mover tmp al destino (binario intacto)
    $destPath = $uploadDir . $cleanName;
    if (!move_uploaded_file($_FILES['entrega']['tmp_name'], $destPath)) {
        return '<div class="alert alert-danger text-center">
                    Error al subir el archivo. Verifica permisos de carpeta.
                </div>';
    }

    // 6) Insertar o actualizar en BD
    $stmt = $this->pdo->prepare("
        INSERT INTO entrega_actividad
          (actividad_id, EstudianteCodigo, Archivo, FechaSubida)
        VALUES (?, ?, ?, CURRENT_TIMESTAMP())
        ON DUPLICATE KEY UPDATE
          Archivo      = VALUES(Archivo),
          FechaSubida  = CURRENT_TIMESTAMP()
    ");
    $stmt->execute([$actividadId, $estCodigo, $cleanName]);

    return '<div class="alert alert-success text-center">
                Entrega realizada correctamente.
            </div>';
}


    // El docente asigna nota a una entrega
    public function grade_entrega_controller(int $entregaId, array $post): string {
        $nota = trim($post['nota'] ?? '');
        if ($nota === '' || !is_numeric($nota)) {
            return '<div class="alert alert-warning text-center">
                        Introduzca una nota válida.
                    </div>';
        }
        $stmt = $this->pdo->prepare("
            UPDATE entrega_actividad
               SET Nota = ?, FechaCalificacion = CURRENT_TIMESTAMP()
             WHERE id = ?
        ");
        $stmt->execute([$nota, $entregaId]);

        return '<div class="alert alert-success text-center">
                    Nota actualizada.
                </div>';
    }
}
