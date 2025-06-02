<?php 
class viewsModel {
    public function get_views_model($views){
        if(
            $views=="home" ||
            $views=="dashboard" ||
            $views=="admin" ||
            $views=="adminlist" ||
            $views=="admininfo" ||
            $views=="anuncio"        || // <-- incluimos aquí		
            $views=="account" ||
            $views=="student" ||
            $views=="studentlist" ||
            $views=="studentinfo" ||
            $views=="class" ||
            $views=="classlist" ||
            $views=="classinfo" ||
            $views=="classview" ||
            $views=="videonow" ||
            $views=="videolist" ||
            $views=="search" ||
            $views=="consultas" ||
            $views=="consultaslist" ||
            $views=="avisos" ||
            $views== "asistencia" ||
            $views=="asistencialist" ||
            $views=="curso" ||
            $views=="miscursos" ||
            $views=="sesion" ||
	    	$views=="material"         ||
	    	$views=="materialcurso"    ||   // ← nueva vista: listará sesiones de un curso
            $views=="teacherinfo"    ||  
            $views=="grabaciones" ||
            $views=="evaluacion" ||
            $views=="evaluacion-student"  || 
            $views=="foro" ||
            $views=="foroslist" ||
            $views=="anunciocurso" ||
			$views=="materialcurso" ||
            $views=="avisoslist"
        ){
            $parts = explode("/", $views);
            $root  = $parts[0];
            if ($root === "evaluacion") {
                // Si la segunda parte es el ID de la sesión, y no hay un “estudiante” tras,
                // cargamos la vista de docente/edición:
                if (isset($parts[1]) && !isset($parts[2])) {
                    return "./views/contents/evaluacion-view.php";
                }
                // Si hay “estudiante” como tercera parte: /evaluacion/{sesionId}/estudiante/
                if (isset($parts[2]) && $parts[2] === "estudiante") {
                    return "./views/contents/evaluacion-student-view.php";
                }
            }
            // caso especial para "anuncio"
            if($views === "anuncio"){
                $contents = "./views/contents/anuncios-view.php";
            }
            // para todas las demás vistas seguimos con {vista}-view.php
            elseif(is_file("./views/contents/".$views."-view.php")){
                $contents = "./views/contents/".$views."-view.php";
            } else {
                // si no existe el fichero, redirigimos a login
                $contents = "login";
            }
        }
        elseif($views=="index" || $views=="login"){
            $contents = "login";
        }
        else{
            $contents = "login";
        }
        return $contents;
    }
}
