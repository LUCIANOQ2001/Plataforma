<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])) {
    header("Location: " . SERVERURL . "login/");
    exit;
}

require_once "./controllers/studentController.php";
$insStudent = new studentController();

// Si vienen POST de eliminación
if (isset($_POST['studentCode'])) {
    echo $insStudent->delete_student_controller($_POST['studentCode']);
}

// Determinar página actual, clamp a 1 mínimo
$parts = explode("/", $_GET['views']);
$page  = isset($parts[1]) && intval($parts[1]) > 0
         ? intval($parts[1])
         : 1;
?>
<style>
/* Fondo completo y sin scroll lateral */
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
  background-color: #1e1f28;
}

/* Encabezado */
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

/* Botones resaltados */
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
  background-color: #43a047 !important;
  border: 1px solid #388e3c !important;
}

.breadcrumb-tabs .btn-success:hover {
  background-color: #4caf50 !important;
}

.breadcrumb-tabs li {
  margin-right: 10px;
}

/* Panel contenedor de tabla */
.panel {
  background: #2c2d3f;
  border-radius: 12px;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
  border: 1px solid #3c3d4f;
  overflow-x: auto;
}

.panel-heading {
  background-color: #43a047 !important;
  color: #fff;
  font-weight: bold;
  text-align: center;
  font-size: 17px;
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
  padding: 12px 15px;
}

.panel-body {
  padding: 20px;
}

/* Tabla adaptativa */
.table-responsive {
  border-radius: 8px;
  overflow-x: auto;
  width: 100%;
}

.table {
  width: 100%;
  min-width: 900px; /* obliga espacio horizontal mínimo */
}

.table th, .table td {
  text-align: center;
  vertical-align: middle;
  background-color: transparent !important;
  color: #fff;
  border: 1px solid #444;
}

/* Hover */
.table-hover tbody tr:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

/* Paginación */
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
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-face zmdi-hc-fw"></i>
        Usuarios <small>(Estudiantes)</small>
      </h1>
    </div>
    <p class="lead">
      Aquí está el listado de todos los estudiantes; puedes eliminar alguno.
    </p>
  </div>

  <div class="container-fluid">
    <ul class="breadcrumb breadcrumb-tabs">
      <?php if($_SESSION['userType'] === "Administrador"): ?>
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
      <?php else: ?>
        <li class="active">
          <a href="<?php echo SERVERURL; ?>studentlist/" class="btn btn-success">
            <i class="zmdi zmdi-format-list-bulleted"></i> Lista de Estudiantes
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </div>

  <div class="container-fluid">
    <div class="panel panel-success">
      <div class="panel-heading">
        <h3 class="panel-title">
          <i class="zmdi zmdi-format-list-bulleted"></i> Lista de Estudiantes
        </h3>
      </div>
      <div class="panel-body">
        <div class="table-responsive">
          <?php
            // La función imprime la <table> y la paginación
            echo $insStudent->pagination_student_controller($page, 10);
          ?>
        </div>
      </div>
    </div>
  </div>
</section>
