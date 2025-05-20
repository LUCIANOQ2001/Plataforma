<?php if($_SESSION['userType']==="Administrador"): ?>

<!-- Estilos inline para respetar el sidebar y quitar fondo blanco -->
<style>
  /* Desplaza contenido para no tapar el sidebar */
  .dashboard-contentPage {
    margin-left: 170px;            /* ← Ajusta al ancho de tu sidebar */
    padding: 20px;
    width: calc(100% - 270px);
    box-sizing: border-box;
    overflow: auto;
  }
  .dashboard-contentPage.full-box { width: auto; }

  /* Quitar fondo de los contenedores y paneles dentro de dashboard-contentPage */
  .dashboard-contentPage .container-fluid,
  .dashboard-contentPage .panel,
  .dashboard-contentPage .panel-body,
  .dashboard-contentPage .table-responsive,
  .dashboard-contentPage .table-responsive .table {
    background: transparent !important;
    color: #fff !important;
  }

  /* Mantener color del encabezado */
  .dashboard-contentPage .panel-success .panel-heading {
    background-color: #5cb85c !important;
    color: #fff !important;
  }

  /* Ajustar bordes de tabla para verse en fondo oscuro */
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
      En esta sección puede ver el listado de todos los estudiantes registrados en el sistema; puede actualizar datos o eliminar un estudiante cuando lo desee.
    </p>
  </div>

  <div class="container-fluid">
    <ul class="breadcrumb breadcrumb-tabs">
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
    </ul>
  </div>

  <?php  
    require_once "./controllers/studentController.php";
    $insStudent = new studentController();
    if(isset($_POST['studentCode'])){
      echo $insStudent->delete_student_controller($_POST['studentCode']);
    }
  ?>

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

<?php 
  else:
    $logout2 = new loginController();
    echo $logout2->login_session_force_destroy_controller(); 
  endif;
?>
