<?php 
  require_once "./controllers/videoController.php";
  $insVideo = new videoController();
  $dateNow  = date("Y-m-d");
  $page     = explode("/", $_GET['views']);
?>
<div class="content-wrapper">
  <!-- Encabezado -->
  <div class="container-fluid">
    <div class="page-header text-center">
      <h1 class="text-titles">
        <i class="zmdi zmdi-tv-list zmdi-hc-fw"></i>
        Clases <small>(Listado)</small>
      </h1>
    </div>
    <p class="lead text-center">
      En esta sección puede ver el listado de todas las clases impartidas en la plataforma de 
      <strong><?php echo COMPANY; ?></strong>.  
      Haga clic en el botón 
      <button class="btn btn-info btn-raised btn-xs">
        <i class="zmdi zmdi-tv"></i>
      </button>
      para acceder a la clase.
    </p>
  </div>

  <!-- Tabla centrada -->
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
        <div class="panel panel-success">
          <div class="panel-heading text-center">
            <h3 class="panel-title">
              <i class="zmdi zmdi-format-list-bulleted"></i>
              Lista de clases
            </h3>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <?php
                // Controlador que genera la tabla paginada
                echo $insVideo->pagination_video_list_controller($page[1], 10);
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
