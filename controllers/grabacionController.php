<?php
require_once __DIR__ . '/../models/grabacionModel.php';
class grabacionController {
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root','',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    /** Listar grabaciones de una sesión */
    public function list_grabaciones_by_sesion_controller(int $sesionId): array {
        $stmt = $this->pdo->prepare(
            "SELECT id, archivo, fecha FROM grabacion WHERE sesion_id = ? ORDER BY fecha DESC"
        );
        $stmt->execute([$sesionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Añadir grabación */
    public function add_grabacion_controller(int $sesionId): string {
        if (empty($_FILES['grabacion']['name'])) {
            return '<div class="alert alert-warning text-center">Debe elegir un archivo.</div>';
        }
        $uploadDir = __DIR__ . '/../attachments/grabaciones/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $orig = basename($_FILES['grabacion']['name']);
        $clean = preg_replace('/[^a-zA-Z0-9_\.\-]/','_', $orig);
        $filename = time().'_'.$clean;
        $dest = $uploadDir . $filename;
        if (!is_uploaded_file($_FILES['grabacion']['tmp_name'])
            || !move_uploaded_file($_FILES['grabacion']['tmp_name'],$dest)) {
            return '<div class="alert alert-danger text-center">Error subiendo la grabación.</div>';
        }
        $stmt = $this->pdo->prepare(
            "INSERT INTO grabacion (sesion_id, archivo) VALUES (?, ?)"
        );
        $stmt->execute([$sesionId, $filename]);
        return '<div class="alert alert-success text-center">Grabación agregada.</div>';
    }

    /** Borrar grabación */
    public function delete_grabacion_controller(int $grabId): string {
        $stmt = $this->pdo->prepare("SELECT archivo FROM grabacion WHERE id = ?");
        $stmt->execute([$grabId]);
        $file = $stmt->fetchColumn();
        if ($file) {
            @unlink(__DIR__ . '/../../attachments/grabaciones/' . $file);
        }
        $del = $this->pdo->prepare("DELETE FROM grabacion WHERE id = ?");
        $del->execute([$grabId]);
        return '<div class="alert alert-success text-center">Grabación eliminada.</div>';
    }
}