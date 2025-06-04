<?php 
class viewsModel {
    public function get_views_model($views){
        // Dividimos la cadena por "/" para extraer el "root" (primera parte)
        $parts = explode("/", $views);
        $root  = $parts[0];

        if(
            // Si la ruta comienza por "evaluacion", o bien coincide exactamente con alguna de las vistas fijas:
            $root=="evaluacion"             || 
            $views=="home"                  ||
            $views=="dashboard"             ||
            $views=="admin"                 ||
            $views=="adminlist"             ||
            $views=="admininfo"             ||
            $views=="anuncio"               || // <-- incluimos aquí		
            $views=="account"               ||
            $views=="student"               ||
            $views=="studentlist"           ||
            $views=="studentinfo"           ||
            $views=="class"                 ||
            $views=="classlist"             ||
            $views=="classinfo"             ||
            $views=="classview"             ||
            $views=="videonow"              ||
            $views=="videolist"             ||
            $views=="search"                ||
            $views=="consultas"             ||
            $views=="consultaslist"         ||
            $views=="avisos"                ||
            $views=="asistencia"            ||
            $views=="asistencialist"        ||
            $views=="curso"                 ||
            $views=="miscursos"             ||
            $views=="sesion"                ||
            $views=="material"              ||
            $views=="materialcurso"         ||   // ← nueva vista: listará sesiones de un curso
            $views=="teacherinfo"           ||  
            $views=="grabaciones"           ||
            $views=="evaluacion"            || 
            $views=="evaluacion-student"    || 
            $views=="evaluacion-student-resolver"    || 
            $views=="foro"                  ||
            $views=="foroslist"             ||
            $views=="anunciocurso"          ||
            $views=="avisoslist"
        ){
            // Volvemos a generar $parts/$root dentro por si no se hubiese calculado antes
            $parts = explode("/", $views);
            $root  = $parts[0];

            if ($root === "evaluacion") {
                // 1) /evaluacion/{sesionId}/
                if (isset($parts[1]) && !isset($parts[2])) {
                    return "./views/contents/evaluacion-view.php";
                }
                // 2) /evaluacion/{sesionId}/estudiante/
                if (isset($parts[2]) && $parts[2] === "estudiante" && !isset($parts[3])) {
                    return "./views/contents/evaluacion-student-view.php";
                }
                // 3) /evaluacion/{sesionId}/estudiante/{evaluacionId}/
                if (isset($parts[3]) && $parts[2] === "estudiante") {
                    return "./views/contents/evaluacion-student-resolver-view.php";
                }
            }

            // caso especial para "anuncio"
            if($views === "anuncio"){
                $contents = "./views/contents/anuncios-view.php";
            }
            // todas las demás vistas: "{vista}-view.php"
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
