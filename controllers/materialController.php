<?php
require_once __DIR__ . '/../models/materialModel.php';
class materialController {
    private $pdo;

    public function __construct(){
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root','',
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
        );
    }

    /** Listar todos los materiales de una sesión */
    public function list_materials_controller(int $sesionId): array {
        $stmt = $this->pdo->prepare("
            SELECT id, Titulo, Archivo, Fecha
              FROM material
             WHERE sesion_id = ?
             ORDER BY Fecha DESC
        ");
        $stmt->execute([$sesionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
/** Añadir un material a una sesión */
    public function add_material_controller(int $sesionId): string {
        if (empty($_FILES['archivo']['name']) || empty($_POST['titulo'])) {
            return '<div class="alert alert-warning text-center">Título y archivo son obligatorios.</div>';
        }

        // Ruta de destino
        $uploadDir = __DIR__ . '/../attachments/material/';

        // Asegura que el directorio exista
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Limpieza del nombre del archivo original
        $originalName = basename($_FILES['archivo']['name']);
        $cleanName    = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
        $filename     = time() . '_' . $cleanName;

        // Ruta destino completa
        $destPath = $uploadDir . $filename;

        // Verifica que haya tmp_name y mueve el archivo
        if (!is_uploaded_file($_FILES['archivo']['tmp_name']) || 
            !move_uploaded_file($_FILES['archivo']['tmp_name'], $destPath)) {
            return '<div class="alert alert-danger text-center">Error subiendo el archivo.</div>';
        }

        // Guardar en base de datos
        $stmt = $this->pdo->prepare("
            INSERT INTO material (sesion_id, Titulo, Archivo)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $sesionId,
            trim($_POST['titulo']),
            $filename
        ]);

        return '<div class="alert alert-success text-center">Material agregado correctamente.</div>';
    }


    /** Borrar un material por su ID */
    public function delete_material_controller(int $materialId): string {
        // primero obtenemos el nombre de archivo para borrarlo del servidor
        $stmt = $this->pdo->prepare("SELECT Archivo FROM material WHERE id = ?");
        $stmt->execute([$materialId]);
        $file = $stmt->fetchColumn();
        if($file){
            @unlink(__DIR__ . '/../../views/assets/material/' . $file);
        }

        $del = $this->pdo->prepare("DELETE FROM material WHERE id = ?");
        $del->execute([$materialId]);

        return '<div class="alert alert-success text-center">Material eliminado.</div>';
    }

    public function count_material_by_estudiante($codigoEstudiante) {
        $materialModel = new materialModel();
        return $materialModel->count_material_by_estudiante_model($codigoEstudiante);
    }
}    
