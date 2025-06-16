<?php
	if($actionsRequired){
		require_once "../core/mainModel.php";
	}else{ 
		require_once "./core/mainModel.php";
	}

	class studentModel extends mainModel{

		/*----------  Add Student Model  ----------*/
		public function add_student_model($data){
			$query=self::connect()->prepare("INSERT INTO estudiante(Codigo,Nombres,Apellidos,Email) VALUES(:Codigo,:Nombres,:Apellidos,:Email)");
			$query->bindParam(":Codigo",$data['Codigo']);
			$query->bindParam(":Nombres",$data['Nombres']);
			$query->bindParam(":Apellidos",$data['Apellidos']);
			$query->bindParam(":Email",$data['Email']);
			$query->execute();
			return $query;
		}


		/*----------  Data Student Model  ----------*/
		public function data_student_model($data){
			if($data['Tipo']=="Count"){
				$query=self::connect()->prepare("SELECT Codigo FROM estudiante");
			}elseif($data['Tipo']=="Only"){
				$query=self::connect()->prepare("SELECT * FROM estudiante WHERE Codigo=:Codigo");
				$query->bindParam(":Codigo",$data['Codigo']);
			}
			$query->execute();
			return $query;
		}


		/*----------  Delete Student Model  ----------*/
		public function delete_student_model($code){
			$query=self::connect()->prepare("DELETE FROM estudiante WHERE Codigo=:Codigo");
			$query->bindParam(":Codigo",$code);
			$query->execute();
			return $query;
		}


		/*----------  Update Student Model  ----------*/
		public function update_student_model($data){
			$query=self::connect()->prepare("UPDATE estudiante SET Nombres=:Nombres,Apellidos=:Apellidos,Email=:Email WHERE Codigo=:Codigo");
			$query->bindParam(":Nombres",$data['Nombres']);
			$query->bindParam(":Apellidos",$data['Apellidos']);
			$query->bindParam(":Email",$data['Email']);
			$query->bindParam(":Codigo",$data['Codigo']);
			$query->execute();
			return $query;
		}


    /*----------  Lista de estudiantes por docente (con promedio)  ----------*/
    public function list_students_by_docente_model(string $docenteCodigo, int $offset = null, int $limit = null){
        $sql = "
          SELECT 
            e.Codigo,
            CONCAT(e.Apellidos, ', ', e.Nombres) AS NombreCompleto,
            e.Email,
            GROUP_CONCAT(DISTINCT c2.Nombre ORDER BY c2.Nombre SEPARATOR ', ') AS Cursos,
            COALESCE((
              SELECT ROUND(AVG(ns.Nota),2)
              FROM nota_sesion ns
              WHERE ns.EstudianteCodigo = e.Codigo
                AND ns.CursoId IN (
                  SELECT id FROM curso WHERE DocenteCodigo = :docenteCodigo
                )
            ),0) AS Promedio
          FROM curso_estudiante ce
          INNER JOIN curso c 
            ON ce.CursoId = c.id
            AND c.DocenteCodigo = :docenteCodigo
          INNER JOIN estudiante e 
            ON e.Codigo = ce.EstudianteCodigo
          LEFT JOIN curso c2 
            ON c2.id = ce.CursoId
          GROUP BY e.Codigo, e.Apellidos, e.Nombres, e.Email
          ORDER BY e.Apellidos, e.Nombres
        ";
        if($offset !== null && $limit !== null){
            $sql .= " LIMIT :offset, :limit";
        }
        $stmt = self::connect()->prepare($sql);
        $stmt->bindParam(':docenteCodigo', $docenteCodigo);
        if($offset !== null && $limit !== null){
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit',  $limit,  PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

	}