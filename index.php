<?php 
	error_reporting(E_ALL);
	ini_set('display_errors','1');
	require_once "./core/configGeneral.php";
	require_once "./controllers/viewsController.php";

	session_start();
	
	$ViewTemplate=new viewsController();
	$ViewTemplate->get_template();
	
	?>