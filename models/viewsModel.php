<?php 
class viewsModel {
    public function get_views_model($views){

        if(
            // Vistas estándar
            $views == "home"                  ||
            $views == "dashboard"             ||
            $views == "admin"                 ||
            $views == "adminlist"             ||
            $views == "admininfo"             ||
            $views == "anuncio"               ||
            $views == "account"               ||
            $views== "actividades"            ||
            $views == "student"               ||
            $views == "studentlist"           ||
            $views == "studentinfo"           ||
            $views == "class"                 ||
            $views == "classlist"             ||
            $views == "classinfo"             ||
            $views == "classview"             ||
            $views == "videonow"              ||
            $views == "videolist"             ||
            $views == "search"                ||
            $views == "consultas"             ||
            $views == "consultaslist"         ||
            $views == "avisos"                ||
            $views == "asistencia"            ||
            $views == "asistencialist"        ||
            $views == "curso"                 ||
            $views == "miscursos"             ||
            $views == "sesion"                ||
            $views == "material"              ||
            $views == "materialcurso"         ||
            $views == "teacherinfo"           ||
            $views == "grabaciones"           ||
            $views == "evaluacion"            ||
            $views == "evaluacion-student"    ||
            $views== "evaluacion-student-detalle"  ||
            $views == "evaluacion-student-resolver" ||
            $views == "foro"                  ||
            $views == "foroslist"             ||
            $views == "anunciocurso"          ||
            $views == "reportenotas"          ||
            $views == "reportenotas-student"  ||
            $views == "avisoslist"            ||
            // Rutas para listado de estudiantes por docente
            $views == "teacher-students"      ||
            $views == "teacher-studentlist"
        ){

            // caso especial para "anuncio"
            if($views === "anuncio"){
                $contents = "./views/contents/anuncios-view.php";
            }
            // caso especial para listado de estudiantes de docente
            elseif($views === "teacher-students" || $views === "teacher-studentlist"){
                $contents = "./views/contents/teacher-studentlist-view.php";
            }
            // todas las demás vistas: "{vista}-view.php"
            elseif(is_file("./views/contents/" . $views . "-view.php")){
                $contents = "./views/contents/" . $views . "-view.php";
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
