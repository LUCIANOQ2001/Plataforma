<?php
	require_once "./models/viewsModel.php"; 
	class viewsController extends viewsModel{
		/*----------  Get Template  ----------*/
		public function get_template(){
			require_once "./views/template.php";
		}

		/*----------  Get Views Controller  ----------*/
		public function get_views_controller(){
        // 1) Determinamos la “vista” solicitada, o usamos 'login' si no hay ninguna
        $view = isset($_GET['views'])
              ? explode("/", $_GET['views'])[0]
              : 'login';

        // 2) Delegamos al modelo para que nos devuelva la ruta del fichero
        return self::get_views_model($view);
    }
	}