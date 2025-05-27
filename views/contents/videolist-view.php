<?php 
  require_once "./controllers/videoController.php";
  $insVideo = new videoController();
  $dateNow  = date("Y-m-d");
  $page     = explode("/", $_GET['views']);
?>
<style>
  /* Mover encabezado y texto más a la derecha sin romper centrado */
.page-header,
.lead {
  margin-left: 100px;  /* ← Puedes aumentar este valor si lo deseas */
  margin-right: 20px;
}

/* === Fondo global y estructura === */
html, body {
  margin: 0;
  padding: 0;
  background-color: #1e1f28;
  color: #fff;
  width: 100%;
  height: 100%;
  overflow-x: hidden;
  box-sizing: border-box;
}

.content-wrapper {
  margin-left: 170px;
  padding: 30px;
  min-height: 100vh;
  background-color: #1e1f28;
}

/* === Encabezado === */
.page-header h1 {
  font-size: 28px;
  color: #00e5ff;
  text-shadow: 1px 1px 6px #000;
  margin-bottom: 10px;
}
.lead {
  font-size: 1.1rem;
  color: #ccc;
  margin-bottom: 30px;
}
.lead button {
  margin-left: 5px;
  transform: translateY(-2px);
}

/* === Panel personalizado === */
.panel {
  background: #2c2d3f;
  border-radius: 12px;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
  border: 1px solid #3c3d4f;
}
.panel-heading {
  background: #43a047 !important;
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

/* === Tabla moderna === */
.table-responsive {
  border-radius: 8px;
  overflow: hidden;
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

/* === Paginación === */
.pagination > li > a, 
.pagination > li > span {
  background-color: #2e2f3f;
  border: 1px solid #555;
  color: #fff;
}
.pagination > .active > a {
  background-color: #03a9f4 !important;
  border-color: #0288d1 !important;
  color: #fff;
}

/* === Botón ícono mini === */
.btn-info.btn-xs {
  padding: 2px 8px;
  font-size: 12px;
  border-radius: 5px;
}
</style>

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
          <div class="panel-heading">
            <i class="zmdi zmdi-format-list-bulleted"></i> Lista de clases
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <?php
                echo $insVideo->pagination_video_list_controller($page[1], 10);
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
