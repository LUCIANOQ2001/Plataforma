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
        if(empty($_FILES['archivo']['name']) || empty($_POST['titulo'])) {
            return '<div class="alert alert-warning text-center">Título y archivo son obligatorios.</div>';
        }

        // carpeta destino
        $uploadDir = __DIR__ . '/../../views/assets/material/';
        if(!is_dir($uploadDir)) mkdir($uploadDir,0755,true);

        $tmp  = $_FILES['archivo']['tmp_name'];
        $name = time() . '_' . basename($_FILES['archivo']['name']);
        $dest = $uploadDir . $name;

        if(!move_uploaded_file($tmp, $dest)) {
            return '<div class="alert alert-danger text-center">Error subiendo el archivo.</div>';
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO material (sesion_id, Titulo, Archivo)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $sesionId,
            trim($_POST['titulo']),
            $name
        ]);

        return '<div class="alert alert-success text-center">Material agregado.</div>';
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

