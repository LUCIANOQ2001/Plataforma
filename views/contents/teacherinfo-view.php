<?php if($_SESSION['userType'] === "Docente"): ?>
<!-- teacherinfo-view.php -->

<style>
  /* === Paleta de colores compartida === */
  :root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(174,12,12,0.61);
    --text-light:       #FFFFFF;
    --hover-accent:     rgba(209,177,110,0.2);
  }

  /* Ocultar íconos de búsqueda y menú */
  .btn-options,
  .dropdown-toggle,
  .btn-search,
  i.zmdi.zmdi-search,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }

  /* Banner de logo sutil de fondo */
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
    position: relative;
    z-index: 1;
    margin-left: 180px;
    width: calc(100% - 270px);
    padding: 30px auto;
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
  legend {
    font-size: 1.2rem;
    color:rgb(0, 0, 0);
    margin-bottom: 1rem;
  }
  .btn-back-home {
    background: var(--primary-accent) !important;
    color: var(--text-light) !important;
    border: none !important;
    border-radius: .3rem;
    padding: .5rem 1rem;
    font-size: .9rem;
    text-decoration: none;
    display: inline-block;
    transition: background .3s;
  }
  .btn-back-home:hover {
    background: var(--hover-accent) !important;
    color: var(--text-light) !important;
  }

  .panel {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    margin-bottom: 2rem;
    overflow: hidden;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
  }
  .panel-heading {
    background: var(--primary-accent) !important;
    color: #2B2B2B;
    font-size: 1.2rem;
    padding: .75rem 1rem;
    text-align: center;
  }
  .panel-body {
    padding: 1.5rem;
    color:#2B2B2B;
  }

  .form-group label.control-label {
    color: rgb(0, 0, 0) !important;
  }
  .form-control {
    background: rgba(255,255,255,0.1) !important;
    border: 1px solid #555 !important;
    color: var(--text-light) !important;
  }

  .btn-success,
  .btn-info {
    font-weight: bold;
    border-radius: .3rem;
    transition: background .3s;
  }
  .btn-success {
    background: var(--hover-accent) !important;
    border: 1px solid var(--primary-accent) !important;
    color: var(--text-light) !important;
  }
  .btn-success:hover {
    background: var(--primary-accent) !important;
  }
  .btn-info {
    background: var(--primary-accent) !important;
    border: 1px solid var(--primary-accent) !important;
    color: var(--text-light) !important;
  }
  .btn-info:hover {
    background: var(--hover-accent) !important;
  }
</style>

<div class="dashboard-banner"></div>

<div class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-settings zmdi-hc-fw"></i> Datos del Docente
      </h1>
    </div>
    <p class="lead">
      Bienvenido a la sección de actualización de datos. Aquí podrás actualizar tu información personal.
    </p>
  </div>

<?php 
  require_once "./controllers/docenteController.php";
  $insDocente = new docenteController();

  if(isset($_POST['code'])){
    echo $insDocente->update_docente_controller();
  }

  $parts = explode("/", trim($_GET['views'], "/"));
  $code  = $parts[1] ?? '';

  $data = $insDocente->data_docente_controller("Only", $code);
  if($data->rowCount() > 0):
    $rows = $data->fetch();
?>
  <p class="text-center">
    <a href="<?= SERVERURL ?>dashboard/" class="btn-back-home">
      <i class="zmdi zmdi-long-arrow-return"></i> Volver
    </a>
  </p>

  <div class="container-fluid">
    <div class="panel">
      <div class="panel-heading">
        <h3 class="panel-title">
          <i class="zmdi zmdi-refresh"></i> Actualizar Datos
        </h3>
      </div>
      <div class="panel-body">
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
          <input type="hidden" name="code" value="<?= htmlspecialchars($rows['Codigo']) ?>">
          <fieldset>
            <legend><i class="zmdi zmdi-account-box"></i> Datos Personales</legend>
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Nombres *</label>
                  <input pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,30}"
                         class="form-control"
                         type="text"
                         name="name"
                         value="<?= htmlspecialchars($rows['Nombres']) ?>"
                         required maxlength="30">
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Apellidos *</label>
                  <input pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,30}"
                         class="form-control"
                         type="text"
                         name="lastname"
                         value="<?= htmlspecialchars($rows['Apellidos']) ?>"
                         required maxlength="30">
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Email</label>
                  <input class="form-control"
                         type="email"
                         name="email"
                         value="<?= htmlspecialchars($rows['Email']) ?>">
                </div>
              </div>
            </div>
          </fieldset>
          <p class="text-center" style="margin-top:1rem;">
            <button type="submit" class="btn-success btn-raised btn-sm">
              <i class="zmdi zmdi-refresh"></i> Guardar Cambios
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>

<?php else: ?>
  <p class="lead text-center">Lo sentimos, ocurrió un error inesperado.</p>
<?php
  endif;
else:
  echo (new loginController())->login_session_force_destroy_controller();
endif;
?>
