<?php
// views/contents/classlist-view.php

// 1) Permisos: solo Administrador y Docente
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])) {
    echo '
    <div class="container-fluid text-center" style="color: #fff; background-color: #1e1f28; padding: 40px;">
      <div class="page-header">
        <h1 class="text-titles"><i class="zmdi zmdi-block-alt"></i> Acceso denegado</h1>
      </div>
      <p class="lead">No tienes permisos para ver esta sección.</p>
    </div>';
    exit;
}

// 2) controlar POST de eliminación
require_once "./controllers/videoController.php";
$insVideo = new videoController();
if (isset($_POST['videoCode'])) {
    echo $insVideo->delete_video_controller($_POST['videoCode']);
}
?>
<style>
  /* === Paleta de colores === */
  :root {
    --primary-bg:     #2B2B2B;
    --primary-accent: #D1B16E;
    --secondary-bg:   rgba(174,12,12,0.61);
    --text-light:     #FFFFFF;
    --hover-accent:   rgba(209,177,110,0.2);
  }
  /* reset y fondo */
  html, body {
    margin: 0; padding: 0;
    width: 100%; height: 100%;
    background: var(--primary-bg);
    color: var(--text-light);
    overflow-x: hidden;
    font-family: 'RobotoCondensed', sans-serif;
  }
  /* logo de fondo */
  .dashboard-banner {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
    opacity: 0.05;
    pointer-events: none;
    z-index: 0;
  }
  /* contenido por encima */
  .dashboard-contentPage {
    position: relative; z-index: 1;
    margin-left: 170px;
    padding: auto;
    min-height: 100vh;
    box-sizing: border-box;
    background: transparent;
    width: 90%;
  }
  /* ocultar buscador y menú */
  .btn-search,
  i.zmdi.zmdi-search,
  .btn-options,
  .dropdown-toggle,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }
  /* encabezado */
  .page-header h1 {
    font-size: 28px;
    color: var(--primary-accent);
    text-shadow: 1px 1px 6px rgba(0,0,0,0.7);
    margin-bottom: 10px;
  }
  .lead {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: 30px;
  }
  /* breadcrumb botones */
  .breadcrumb-tabs .btn {
    font-weight: bold;
    padding: 8px 16px;
    border-radius: 4px;
    color: var(--text-light) !important;
    transition: background .3s;
  }
  .breadcrumb-tabs .btn-info {
    background: var(--primary-accent) !important;
    border: 1px solid var(--primary-accent) !important;
    color: #000 !important;/*color para el primer botón*/
  }
  .breadcrumb-tabs .btn-info:hover {
    background: var(--hover-accent) !important;
  }
  .breadcrumb-tabs .btn-success {
    background: var(--primary-accent) !important;
    border: 1px solid var(--primary-accent) !important;
    color: #000 !important; /* color para el segúndo botón que está a la derecha*/
  }
  .breadcrumb-tabs .btn-success:hover {
    background: var(--hover-accent) !important;
  }
  .breadcrumb-tabs li {
    margin-right: 10px;
  }
  /* Quita fondo blanco de los breadcrumbs y su contenedor */
  .dashboard-contentPage > .container-fluid {
    background: transparent !important;
  }
  .breadcrumb-tabs {
    background: transparent !important;
  }
  /* panel */
  .panel {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.5);
    margin-bottom: 30px;
    width: 90%;
  }
  .panel-heading {
    background: var(--primary-accent) !important;
    color: var(--primary-bg) !important;
    font-weight: bold;
    font-size: 17px;
    text-align: center;
    padding: 12px 15px;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
  }
  .panel-body {
    padding: 20px;
    color: var(--text-light);
  }
  /* tablas */
  .table-responsive {
    border-radius: 8px;
    overflow-x: auto;
  }
  .table {
    width: 100%;
    border-collapse: collapse;
    background: transparent;
    color: var(--text-light);
  }
  .table th, .table td {
    border: 1px solid rgba(255,255,255,0.2);
    padding: 12px;
    text-align: center;
    vertical-align: middle;
  }
  .table-hover tbody tr:hover {
    background: rgba(255,255,255,0.05);
  }
  /* paginación */
  .pagination > li > a,
  .pagination > li > span {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    color: var(--text-light);
    font-weight: bold;
  }
  .pagination > .active > a {
    background: var(--primary-accent) !important;
    color: var(--primary-bg) !important;
    border-color: var(--primary-accent) !important;
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-videocam zmdi-hc-fw"></i>
        Clases <small>(Listado)</small>
      </h1>
    </div>
    <p class="lead">
      En esta sección puedes ver el listado de todas las clases registradas en el sistema;
      puedes actualizar datos o eliminar una clase cuando lo desees.
    </p>
  </div>

  <div class="container-fluid">
    <ul class="breadcrumb breadcrumb-tabs">
      <li>
        <a href="<?php echo SERVERURL; ?>class/" class="btn btn-info">
          <i class="zmdi zmdi-plus"></i> Nueva Clase
        </a>
      </li>
      <li class="active">
        <a href="<?php echo SERVERURL; ?>classlist/" class="btn btn-success">
          <i class="zmdi zmdi-format-list-bulleted"></i> Lista de Clases
        </a>
      </li>
    </ul>
  </div>

  <div class="container-fluid">
    <div class="panel panel-info">
      <div class="panel-heading">
        <h3 class="panel-title">
          <i class="zmdi zmdi-format-list-bulleted"></i> Lista de Clases
        </h3>
      </div>
      <div class="panel-body">
        <div class="table-responsive">
          <?php
            $parts = explode("/", $_GET['views']);
            $page  = isset($parts[1]) && is_numeric($parts[1]) ? intval($parts[1]) : 1;
            echo $insVideo->pagination_video_controller($page, 10);
          ?>
        </div>
      </div>
    </div>
  </div>
</section>
