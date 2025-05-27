<?php 
  require_once "./controllers/videoController.php";
  $insVideo = new videoController();
  $dateNow  = date("Y-m-d");
  $page     = explode("/", $_GET['views']);
?>
<style>

  /* ← Requiere que el fondo sea completo en toda la ventana */
html, body {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100%;
  background-color: #1e1f28; /* ← mismo color que el content-wrapper */
  overflow-x: hidden;
}

/* ← Elimina separación superior entre el borde del navegador y el contenido */
body {
  box-sizing: border-box;
}

/* === Contenedor general === */
.content-wrapper {
  margin-left: 180px;               /* no tapar el sidebar */
  padding: 30px;
  background-color: #1e1f28;
  color: #fff;
  min-height: 100vh;
  box-sizing: border-box;
  width: calc(100% - 170px);        /* ← fuerza a ocupar el resto de la pantalla */
  overflow-x: hidden;
}


/* === Encabezado === */
.page-header h1 {
  color: #00e5ff;
  text-shadow: 1px 1px 5px #000;
  font-size: 28px;
  margin-bottom: 20px;
}

.lead {
  color: #ccc;
  margin-bottom: 30px;
  font-size: 1.1rem;
}

/* === Panel y tabla === */
.panel {
  background-color: #2b2c3d !important;
  border: 1px solid #444;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
  border-radius: 8px;
}

.panel-heading {
  background-color: #43a047 !important;
  color: #fff;
  border-bottom: 1px solid #2e7d32;
  text-align: center;
}

.panel-title {
  font-size: 18px;
  font-weight: bold;
}

/* Tabla */
.table > thead > tr > th,
.table > tbody > tr > td {
  background-color: transparent !important;
  color: #fff !important;
  border-color: #444;
  vertical-align: middle;
}

.table-hover > tbody > tr:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

.pagination > li > a,
.pagination > li > span {
  background-color: #2d2d3f;
  color: #fff;
  border: 1px solid #555;
}

.pagination > .active > a {
  background-color: #03a9f4 !important;
  border-color: #0288d1 !important;
  color: #fff !important;
}

/* Botón de ícono */
.btn-info.btn-xs {
  padding: 2px 8px;
  font-size: 12px;
}
</style>

<div class="content-wrapper">
  <!-- Encabezado -->
  <div class="container-fluid">
    <div class="page-header text-center">
      <h1 class="text-titles">
        <i class="zmdi zmdi-tv-play zmdi-hc-fw"></i>
        Clases <small>(Ahora)</small>
      </h1>
    </div>
    <p class="lead text-center">
      En esta sección puede ver el listado de todas las clases para el día de hoy. 
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
              Lista de clases para hoy
            </h3>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <?php
                echo $insVideo->pagination_video_now_controller(
                  $page[1],
                  10,
                  $dateNow
                );
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
