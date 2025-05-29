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
            $views=="grabaciones" ||
            $views=="foro" ||
            $views=="foroslist" ||
            $views=="anunciocurso" ||
			$views=="materialcurso" ||
            $views=="avisoslist"
        ){
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
