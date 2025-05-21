<?php
// views/contents/studentlist-view.php

// 1) Inicia sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2) Sólo Administradores y Docentes pueden ver esta página
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])) {
    header("Location: " . SERVERURL . "login/");
    exit;
}

// 3) Carga el controlador y procesa eliminación si viene POST
require_once "./controllers/studentController.php";
$insStudent = new studentController();
if (isset($_POST['studentCode'])) {
    echo $insStudent->delete_student_controller($_POST['studentCode']);
}
?>

<!-- 4) Estilos inline para mantener transparencia y márgenes -->
<style>
.dashboard-contentPage {
    margin-left: 170px;            /* Ajusta al ancho real de tu sidebar */
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
    color: #fff           !important;
}
.dashboard-contentPage .panel-success .panel-heading {
    background-color: #5cb85c !important;
    color:             #fff    !important;
}
.dashboard-contentPage .table-responsive .table th,
.dashboard-contentPage .table-responsive .table td {
    border-color: #444 !important;
}
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-face zmdi-hc-fw"></i>
        Usuarios <small>(Estudiantes)</small>
      </h1>
    </div>
    <p class="lead">
      En esta sección puede ver el listado de todos los estudiantes registrados en el sistema;
      puede actualizar datos o eliminar un estudiante cuando lo desee.
    </p>
  </div>

  <div class="container-fluid">
    <ul class="breadcrumb breadcrumb-tabs">
      <?php if($_SESSION['userType']==="Administrador"): ?>
      <li class="active">
        <a href="<?php echo SERVERURL; ?>student/" class="btn btn-info">
          <i class="zmdi zmdi-plus"></i> Nuevo
        </a>
      </li>
      <li>
        <a href="<?php echo SERVERURL; ?>studentlist/" class="btn btn-success">
          <i class="zmdi zmdi-format-list-bulleted"></i> Lista
        </a>
      </li>
      <?php else: /* Docente sólo ve listado */ ?>
      <li class="active">
        <a href="<?php echo SERVERURL; ?>studentlist/" class="btn btn-success">
          <i class="zmdi zmdi-format-list-bulleted"></i> Lista de Estudiantes
        </a>
      </li>
      <?php endif; ?>
    </ul>
  </div>

  <div class="container-fluid">
    <div class="row">
      <div class="col-xs-12">
        <div class="panel panel-success">
          <div class="panel-heading">
            <h3 class="panel-title">
              <i class="zmdi zmdi-format-list-bulleted"></i> Lista de Estudiantes
            </h3>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <?php
                // Paginación delegada al controlador
                $parts = explode("/", $_GET['views']);
                $page  = isset($parts[1]) ? intval($parts[1]) : 1;
                echo $insStudent->pagination_student_controller($page, 10);
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
