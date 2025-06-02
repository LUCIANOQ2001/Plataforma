<?php
// controllers/docenteController.php

class docenteController {
    private $pdo;

    public function __construct(){
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    /**
     * Obtiene los datos de un docente por su Código.
     * @param string $tipo Debe ser "Only" para retornar un único registro.
     * @param string $codigo Código único del docente.
     * @return PDOStatement|null
     */
    public function data_docente_controller(string $tipo, string $codigo): ?PDOStatement {
        if ($tipo !== "Only") {
            return null;
        }
        $stmt = $this->pdo->prepare("
            SELECT Codigo, Nombres, Apellidos, Email
              FROM docente
             WHERE Codigo = ?
        ");
        $stmt->execute([$codigo]);
        return $stmt;
    }

    /**
     * Actualiza los datos personales (Nombres, Apellidos, Email) de un docente.
     * Se espera $_POST conteniendo 'code', 'name', 'lastname' y opcionalmente 'email'.
     * @return string HTML con alert de éxito o error.
     */
    public function update_docente_controller(): string {
        $codigo    = $_POST['code']     ?? '';
        $nombres   = trim($_POST['name']     ?? '');
        $apellidos = trim($_POST['lastname'] ?? '');
        $email     = trim($_POST['email']    ?? '');

        if (!$codigo || !$nombres || !$apellidos) {
            return '<div class="alert alert-warning text-center">Faltan datos obligatorios.</div>';
        }

        try {
            $stmt = $this->pdo->prepare("
                UPDATE docente
                   SET Nombres  = ?,
                       Apellidos= ?,
                       Email    = ?
                 WHERE Codigo   = ?
            ");
            $stmt->execute([$nombres, $apellidos, $email, $codigo]);
            return '<div class="alert alert-success text-center">Datos actualizados correctamente.</div>';
        } catch (PDOException $e) {
            return '<div class="alert alert-danger text-center">Error al actualizar: ' 
                   . htmlspecialchars($e->getMessage()) 
                   . '</div>';
        }
    }
}
