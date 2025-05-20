<?php if($_SESSION['userType'] === "Administrador"): ?>

<!-- Ajustes inline: margen para el sidebar y corrección de fondo blanco -->
<style>
  /* Desplaza el contenido a la derecha para no tapar el sidebar */
  .dashboard-contentPage {
    margin-left: 170px;            /* <-- Cambia 270px al ancho real de tu sidebar */
    padding: 0px;                 /* espacio interior */
    width: calc(100% - 270px);
    box-sizing: border-box;
    overflow: auto;
  }
  .dashboard-contentPage.full-box {
    width: auto;
  }

  /* Elimina el fondo blanco de la panel-body y ajusta el color del texto */
  .panel-success .panel-body {
    background-color: #2d2d3f;     /* mismo color oscuro de fondo global */
    color: #fff;                   /* texto blanco para que se lea */
  }

  /* La tabla también hereda fondo transparente y texto blanco */
  .table-responsive .table {
    background-color: transparent;
    color: #fff;
  }
  .table-responsive .table th,
  .table-responsive .table td {
    background-color: transparent;
    color: #fff;
    border-color: #444;            /* bordes grises para distinguir celdas */
  }

  /* Encabezado verde intacto */
  .panel-success .panel-heading {
    background-color: #5cb85c;
    color: #fff;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-account zmdi-hc-fw"></i>
        Usuarios <small>(Administradores)</small>
      </h1>
    </div>
    <p class="lead">
      En esta sección verá el listado de todos los usuarios administradores registrados en el sistema. Puede actualizar datos o eliminar un usuario cuando lo desee.
    </p>
  </div>

  <div class="container-fluid">
    <ul class="breadcrumb breadcrumb-tabs">
      <li>
        <a href="<?php echo SERVERURL; ?>admin/" class="btn btn-info">
          <i class="zmdi zmdi-plus"></i> Nuevo
        </a>
      </li>
      <li class="active">
        <a href="<?php echo SERVERURL; ?>adminlist/" class="btn btn-success">
          <i class="zmdi zmdi-format-list-bulleted"></i> Lista
        </a>
      </li>
    </ul>
  </div>

  <?php  
    require_once "./controllers/adminController.php";
    $insAdmin = new adminController();
    if (isset($_POST['adminCode'])) {
      echo $insAdmin->delete_admin_controller($_POST['adminCode']);
    }
  ?>

  <div class="container-fluid">
    <div class="row">
      <div class="col-xs-12">
        <div class="panel panel-success">
          <div class="panel-heading">
            <h3 class="panel-title">
              <i class="zmdi zmdi-format-list-bulleted"></i> Lista de Administradores
            </h3>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <?php
                $parts = explode("/", $_GET['views']);
                $page  = isset($parts[1]) ? intval($parts[1]) : 1;
                echo $insAdmin->pagination_admin_controller($page, 10);
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
