<?php
// models/actividadModel.php

require_once __DIR__ . '/../core/mainModel.php';

class actividadModel extends mainModel {
    /**
     * Cuenta actividades disponibles para un estudiante
     */
    public function count_actividades_by_estudiante_model(string $codigoEstudiante): int {
        $sql = "
          SELECT COUNT(a.id)
            FROM actividad a
      INNER JOIN sesion s ON a.sesion_id = s.id
      INNER JOIN curso_estudiante ce ON s.CursoId = ce.CursoId
           WHERE ce.EstudianteCodigo = :codigo
        ";
        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':codigo', $codigoEstudiante);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
