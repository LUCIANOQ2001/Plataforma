<?php

require_once __DIR__ . '/../core/mainModel.php';

class materialModel extends mainModel {
   public function count_material_by_estudiante_model($codigoEstudiante) {
    $query = "SELECT COUNT(DISTINCT m.id) 
              FROM material m 
              INNER JOIN sesion s ON m.sesion_id = s.id 
              INNER JOIN curso c ON s.CursoID = c.id 
              INNER JOIN curso_estudiante ce ON c.id = ce.CursoID
              WHERE ce.EstudianteCodigo = :codigo";
    $stmt = mainModel::conectar()->prepare($query);
    $stmt->bindParam(":codigo", $codigoEstudiante);
    $stmt->execute();
    return $stmt->fetchColumn();
    }

}
