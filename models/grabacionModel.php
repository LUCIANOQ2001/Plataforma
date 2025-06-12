<?php
require_once __DIR__.'/../core/mainModel.php';
class grabacionModel extends mainModel {
    public function count_grabaciones_by_estudiante_model(string $codigoEstudiante): int {
        $query="SELECT COUNT(r.id) FROM grabacion r
                 INNER JOIN sesion s ON r.sesion_id=s.id
                 INNER JOIN curso_estudiante ce ON s.CursoId=ce.CursoId
                 WHERE ce.EstudianteCodigo=:codigo";
        $stmt=mainModel::conectar()->prepare($query);
        $stmt->bindParam(':codigo',$codigoEstudiante);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
}
