<!-- views/teacher-studentlist-view.php -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SESSION['userType'] !== 'Docente') {
    header("Location: " . SERVERURL . "login/");
    exit;
}

require_once "./controllers/studentController.php";
$insStudent = new studentController();

// Determinar página actual
$parts = explode("/", $_GET['views']);
$page  = isset($parts[1]) && intval($parts[1]) > 0
         ? intval($parts[1])
         : 1;
?>
<style>
  /* === Paleta de Colores === */
  :root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(174,12,12,0.61);
    --text-light:       #FFFFFF;
    --hover-accent:     rgba(209,177,110,0.2);
  }

  /* Ocultar buscador y menú */
  .btn-options,
  .dropdown-toggle,
  .btn-search,
  i.zmdi.zmdi-search,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }

  /* Banner de logo sutil */
  .dashboard-banner {
    position: fixed;
    top: 0; left: 270px;
    width: calc(100% - 270px);
    height: 100%;
    background: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
    opacity: 0.05;
    pointer-events: none;
    z-index: 0;
  }

  html, body {
    margin: 0; padding: 0;
    width: 100%; height: 100%;
    background: var(--primary-bg);
    color: var(--text-light);
    overflow-x: hidden;
    font-family: 'RobotoCondensed', sans-serif;
    box-sizing: border-box;
  }

  .dashboard-contentPage {
    position: relative; z-index: 1;
    margin-left: 180px;
    width: calc(100% - 270px);
    padding: auto;
    min-height: 100vh;
    box-sizing: border-box;
  }

  .page-header h1 {
    font-size: 2rem;
    color: var(--primary-accent);
    margin-bottom: 1rem;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
  }
  .lead {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: 2rem;
  }

  /* Breadcrumb transparente */
  .breadcrumb-tabs,
  .breadcrumb {
    background: transparent !important;
    padding: 0 !important;
    margin-bottom: 2rem !important;
    border: none !important;
  }
  .breadcrumb-tabs li {
    display: inline-block;
    margin-right: .5rem;
  }
  .breadcrumb-tabs .btn {
    background: var(--primary-accent) !important;
    color: #000 !important;
    border: none !important;
    border-radius: .3rem;
    padding: .5rem 1rem;
    font-size: .9rem;
    text-decoration: none;
    transition: background .3s;
  }
  .breadcrumb-tabs .btn:hover {
    background: var(--hover-accent) !important;
  }

  .panel {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    overflow: hidden;
    margin-bottom: 2rem;
  }
  .panel-heading {
    background: var(--primary-accent) !important;
    color: #2B2B2B;
    padding: .75rem 1rem;
    text-align: center;
    font-size: 1.2rem;
  }
  .panel-body {
    padding: 1.5rem;
  }

  .table-responsive {
    border-radius: .75rem;
    overflow-x: auto;
  }
  .table {
    width: 100%;
    border-collapse: collapse;
    min-width: 90px;
  }
  .table th{
    padding: .75rem;
    border: 1px solid rgba(255,255,255,0.2);
    text-align: center;
    color:rgb(0, 0, 0);
    background: transparent;
  }
  
  .table td {
    padding: .75rem;
    border: 1px solid rgba(255,255,255,0.2);
    text-align: center;
    color: var(--text-light);
    background: transparent;
  }
  .table-hover tbody tr:hover {
    background: var(--hover-accent);
  }

  .pagination > li > a,
  .pagination > li > span {
    background: var(--secondary-bg);
    border: 1px solid var(--hover-accent);
    color: var(--text-light);
    font-weight: bold;
    margin: 0 .25rem;
    border-radius: .3rem;
    padding: .5rem .75rem;
  }
  .pagination > .active > a {
    background: var(--primary-accent) !important;
    border-color: var(--primary-accent) !important;
    color: var(--text-light) !important;
  }

  .btn-warning.btn-xs {
    background: var(--primary-accent) !important;
    border: none;
    color: var(--text-light) !important;
    font-weight: bold;
    padding: .25rem .5rem;
    border-radius: .3rem;
    transition: background .3s;
    margin-left: .5rem;
  }
  .btn-warning.btn-xs:hover {
    background: var(--hover-accent) !important;
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-face zmdi-hc-fw"></i>
        Mis Estudiantes
      </h1>
    </div>
    <p class="lead">
      Listado de estudiantes matriculados en tus cursos.
    </p>
  </div>

  <div class="container-fluid">
    <ul class="breadcrumb-tabs">
      <li class="active">
        <a href="<?= SERVERURL ?>teacher-students/" class="btn">
          <i class="zmdi zmdi-format-list-bulleted"></i> Lista
        </a>
      </li>
    </ul>
  </div>

  <div class="container-fluid">
    <div class="panel">
      <div class="panel-heading">Mis Estudiantes</div>
      <div class="panel-body">
        <div class="table-responsive">
          <?= $insStudent->student_list_for_role_controller($page, 10); ?>
        </div>
      </div>
    </div>
  </div>
</section>
