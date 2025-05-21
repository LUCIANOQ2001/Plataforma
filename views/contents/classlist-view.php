<?php
// views/contents/classlist-view.php

// 1) Permisos: solo Administrador y Docente
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])) {
    // Mostrar mensaje de acceso denegado
    echo '
    <div class="container-fluid text-center">
      <div class="page-header">
        <h1 class="text-titles"><i class="zmdi zmdi-block-alt"></i> Acceso denegado</h1>
      </div>
      <p class="lead">No tienes permisos para ver esta sección.</p>
    </div>';
    exit;
}

// 2) Estilos inline para transparencia y márgenes
?>
<style>
  .dashboard-contentPage {
    margin-left: 170px;            /* ancho real de tu sidebar */
    padding: 20px;
    width: calc(100% - 270px);
    box-sizing: border-box;
    overflow: auto;
  }
  .dashboard-contentPage.full-box { width: auto; }

  .dashboard-contentPage .container-fluid,
  .dashboard-contentPage .panel,
  .dashboard-contentPage .panel-heading,
  .dashboard-contentPage .panel-body,
  .dashboard-contentPage .table-responsive,
  .dashboard-contentPage .table-responsive .table {
    background: transparent !important;
    color: #fff          !important;
  }
  .dashboard-contentPage .panel-success .panel-heading {
    background-color: #5cb85c !important;
    color: #fff             !important;
  }
  .dashboard-contentPage .table-responsive .table th,
  .dashboard-contentPage .table-responsive .table td {
    border-color: #444 !important;
  }
</style>

<section class="dashboard-contentPage">
  <?php
    // 3) Controlador y modelo
    require_once "./controllers/videoController.php";
    $insVideo = new videoController();

    // Si llega POST para borrar una clase
    if (isset($_POST['videoCode'])) {
        echo $insVideo->delete_video_controller($_POST['videoCode']);
    }
  ?>

  <!-- 4) Cabecera y breadcrumb -->
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

  <!-- 5) Tabla paginada de clases -->
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
                // Extraemos la página del URL: /classlist/{p}/
                $parts = explode("/", $_GET['views']);
                $page  = isset($parts[1]) && is_numeric($parts[1]) ? intval($parts[1]) : 1;
                // Mostramos la tabla con paginación
                echo $insVideo->pagination_video_controller($page, 10);
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
