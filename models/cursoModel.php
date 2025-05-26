<?php
require_once __DIR__ . '/../core/mainModel.php';
class cursoModel extends mainModel {
    public function count_cursos_by_estudiante_model($codigoEstudiante) {
        $query = "SELECT COUNT(*) FROM curso_estudiante WHERE EstudianteCodigo = :codigo";
        $stmt = mainModel::conectar()->prepare($query);
        $stmt->bindParam(":codigo", $codigoEstudiante);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}