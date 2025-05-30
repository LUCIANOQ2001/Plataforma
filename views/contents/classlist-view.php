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

// Estilos modernos oscuros
?>
<style>
  html, body {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    background-color: #1e1f28;
    color: #fff;
    overflow-x: hidden;
    box-sizing: border-box;
  }
  .dashboard-contentPage {
    margin-left: 170px;
    padding: 30px;
    width: calc(100% - 170px);
    box-sizing: border-box;
    min-height: 100vh;
  }
  .page-header h1 {
    font-size: 28px;
    color: #00e5ff;
    text-shadow: 1px 1px 5px #000;
  }
  .lead {
    font-size: 1.1rem;
    color: #ccc;
    margin-bottom: 30px;
  }
  .breadcrumb-tabs .btn {
    font-weight: bold;
    color: #fff !important;
    padding: 8px 16px;
    border-radius: 4px;
    transition: background .3s;
  }
  .breadcrumb-tabs .btn-info {
    background-color: #0288d1 !important;
    border: 1px solid #0277bd !important;
  }
  .breadcrumb-tabs .btn-info:hover {
    background-color: #039be5 !important;
  }
  .breadcrumb-tabs .btn-success {
    background-color: #388e3c !important;
    border: 1px solid #2e7d32 !important;
  }
  .breadcrumb-tabs .btn-success:hover {
    background-color: #4caf50 !important;
  }
  .breadcrumb-tabs li {
    margin-right: 10px;
  }
  .panel {
    background: #2c2d3f;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
    border: 1px solid #3c3d4f;
  }
  .panel-heading {
    background-color: #43a047 !important;
    color: #fff;
    font-weight: bold;
    font-size: 17px;
    text-align: center;
    padding: 12px 15px;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
  }
  .panel-body {
    padding: 20px;
  }
  .table-responsive {
    border-radius: 8px;
    overflow-x: auto;
  }
  .table {
    width: 100%;
    margin-bottom: 0;
  }
  .table th, .table td {
    text-align: center;
    vertical-align: middle;
    background-color: transparent !important;
    color: #fff;
    border: 1px solid #444;
  }
  .table-hover tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.05);
  }
  .pagination > li > a, 
  .pagination > li > span {
    background-color: #2e2f3f;
    border: 1px solid #555;
    color: #fff;
    font-weight: bold;
  }
  .pagination > .active > a {
    background-color: #03a9f4 !important;
    border-color: #0288d1 !important;
    color: #fff;
  }
</style>

<section class="dashboard-contentPage">
  <?php
    require_once "./controllers/videoController.php";
    $insVideo = new videoController();

    if (isset($_POST['videoCode'])) {
        echo $insVideo->delete_video_controller($_POST['videoCode']);
    }
  ?>

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
    <div class="row">
      <div class="col-xs-12">
        <div class="panel panel-success">
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
    </div>
  </div>
</section>