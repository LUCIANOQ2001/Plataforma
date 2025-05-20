<?php if($_SESSION['userType'] === "Administrador"): ?>

<!-- Estilos inline para quitar fondos blancos, mantener márgenes y no tapar el sidebar -->
<style>
  /* 1) Desplaza el contenido para no tapar el sidebar (ancho 270px) */
  .dashboard-contentPage {
    margin-left: 170px;
    padding: 20px;
    width: calc(100% - 270px);
    box-sizing: border-box;
    overflow: auto;
  }
  .dashboard-contentPage.full-box {
    width: auto;
  }

  /* 2) Todas las cajas (container, panel, breadcrumb) transparentes */
  .dashboard-contentPage .container-fluid,
  .dashboard-contentPage .breadcrumb,
  .dashboard-contentPage .panel,
  .dashboard-contentPage .panel-heading,
  .dashboard-contentPage .panel-body {
    background-color: transparent !important;
    color:            #fff        !important;
  }

  /* 3) Breadcrumb personalizado para mejor contraste */
  .dashboard-contentPage .breadcrumb-tabs {
    background: transparent !important;
    margin-bottom: 20px;
  }
  .dashboard-contentPage .breadcrumb-tabs li {
    margin-right: 10px;
  }
  .dashboard-contentPage .breadcrumb-tabs a.btn-info {
    background-color: #0288d1 !important;
    border-color:     #0277bd !important;
    color:            #fff    !important;
  }
  .dashboard-contentPage .breadcrumb-tabs a.btn-success {
    background-color: #388e3c !important;
    border-color:     #2e7d32 !important;
    color:            #fff    !important;
  }

  /* 4) Panel-success heading en verde intenso */
  .dashboard-contentPage .panel-success .panel-heading {
    background-color: #4caf50 !important;
    color:            #fff    !important;
  }

  /* 5) Tablas y bordes visibles sobre fondo oscuro */
  .dashboard-contentPage .table-responsive .table {
    background: transparent;
    color:      #fff !important;
  }
  .dashboard-contentPage .table-responsive .table th,
  .dashboard-contentPage .table-responsive .table td {
    border-color: #555 !important;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-tv-list zmdi-hc-fw"></i> Clases <small>(Listado)</small>
      </h1>
    </div>
    <p class="lead">
      En esta sección puede ver el listado de todas las clases registradas en el sistema,
      puede actualizar datos o eliminar una clase cuando lo desee.
    </p>
  </div>

  <div class="container-fluid">
    <ul class="breadcrumb breadcrumb-tabs">
      <li class="active">
        <a href="<?php echo SERVERURL; ?>class/" class="btn btn-info">
          <i class="zmdi zmdi-plus"></i> Nueva
        </a>
      </li>
      <li>
        <a href="<?php echo SERVERURL; ?>classlist/" class="btn btn-success">
          <i class="zmdi zmdi-format-list-bulleted"></i> Lista
        </a>
      </li>
    </ul>
  </div>

  <?php 
    require_once "./controllers/videoController.php";
    $insVideo = new videoController();
  ?>

  <div class="container-fluid">
    <div class="row">
      <div class="col-xs-12">
        <div class="panel panel-success">
          <div class="panel-heading">
            <h3 class="panel-title">
              <i class="zmdi zmdi-format-list-bulleted"></i> Lista de clases
            </h3>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <?php
                $parts = explode("/", $_GET['views']);
                $page  = isset($parts[1]) ? intval($parts[1]) : 1;
                echo $insVideo->pagination_video_controller($page, 10);
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php 
  else:
    $logout2 = new loginController();
    echo $logout2->login_session_force_destroy_controller(); 
  endif;
?>
