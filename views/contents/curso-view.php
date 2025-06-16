<?php 
// Sólo Administradores y Docentes pueden ver esta página
if(!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])) {
    $logout2 = new loginController();
    echo $logout2->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/cursoController.php';
$insCurso = new cursoController();

// 1) Procesar POST
$alert = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alert = $insCurso->add_curso_controller();
}

// 2) Listar docentes (de cuenta)
$docentes = $insCurso->list_docentes_controller();
?>

<style>
  /* === Paleta de colores === */
  :root {
    --primary-bg:     #2B2B2B;
    --primary-accent: #D1B16E;
    --secondary-bg:   rgba(174,12,12,0.61);
    --text-light:rgb(245, 245, 245);
    --hover-accent:   rgba(209,177,110,0.2);
  }
  /* reset y fondo */
  html, body {
    margin: 0; padding: 0;
    width: 100%; height: 100%;
    background: var(--primary-bg);
    color: var(--text-light);
    overflow-x: hidden;
    font-family: 'RobotoCondensed', sans-serif;
  }
  /* logo de fondo */
  .dashboard-banner {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
    opacity: 0.05;
    pointer-events: none;
    z-index: 0;
  }
  /* contenido por encima */
  .dashboard-contentPage {
    position: relative; z-index: 1;
    margin-left: 170px;
    padding: auto;
    min-height: 100vh;
    box-sizing: border-box;
    background: transparent;
    width: 90%;
  }
  /* ocultar buscador y menú */
  .btn-search,
  i.zmdi.zmdi-search,
  .btn-options,
  .dropdown-toggle,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }
  /* encabezado */
  .page-header h1 {
    font-size: 28px;
    color: var(--primary-accent);
    text-shadow: 1px 1px 6px rgba(0,0,0,0.7);
    margin-bottom: 10px;
  }
  .lead {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: 30px;
  }
    legend {
    font-size: 1.2rem;
    color:rgb(0, 0, 0);
    margin-bottom: 1rem;
  }
  /* breadcrumb botones */
  .breadcrumb-tabs .btn {
    font-weight: bold;
    padding: 8px 16px;
    border-radius: 4px;
    color: var(--text-light) !important;
    transition: background .3s;
  }
  .breadcrumb-tabs .btn-info {
    background: var(--primary-accent) !important;
    border: 1px solid var(--primary-accent) !important;
    color: #000 !important;
  }
  .breadcrumb-tabs .btn-info:hover {
    background: var(--hover-accent) !important;
  }
  .breadcrumb-tabs .btn-success {
    background: var(--primary-accent) !important;
    border: 1px solid var(--primary-accent) !important;
    color: #000 !important;
  }
  .breadcrumb-tabs .btn-success:hover {
    background: var(--primary-accent) !important;
  }
  
  .breadcrumb-tabs li {
    margin-right: 10px;
  }
  /* Quita fondo blanco de los breadcrumbs y su contenedor */
  .dashboard-contentPage > .container-fluid {
    background: transparent !important;
  }
  .breadcrumb-tabs {
    background: transparent !important;
  }
  /* panel */
  .panel {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.5);
    margin-bottom: 30px;
    width: 90%;
  }
  .panel-heading {
    background: var(--primary-accent) !important;
    color: var(--primary-bg) !important;
    font-weight: bold;
    font-size: 17px;
    text-align: center;
    padding: 12px 15px;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
  }
  .panel-body {
    padding: 20px;
    color: var(--text-light);
  }
  /* tablas */
  .table-responsive {
    border-radius: 8px;
    overflow-x: auto;
  }
  .table {
    width: 100%;
    border-collapse: collapse;
    background: transparent;
    color: var(--text-light);
  }
  .table th, .table td {
    border: 1px solid rgba(0, 0, 0, 0.2);
    padding: 12px;
    text-align: center;
    vertical-align: middle;
  }
  .table-hover tbody tr:hover {
    background: rgba(255,255,255,0.05);
  }
  /* paginación */
  .pagination > li > a,
  .pagination > li > span {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    color: var(--text-light);
    font-weight: bold;
  }
  .pagination > .active > a {
    background: var(--primary-accent) !important;
    color: var(--primary-bg) !important;
    border-color: var(--primary-accent) !important;
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-book zmdi-hc-fw"></i> Cursos <small>(Registro)</small>
      </h1>
    </div>
    <p class="lead">
      Crea un nuevo curso con fecha de inicio y fin. Los campos marcados con * son obligatorios.
    </p>
  </div>

  <div class="container-fluid">
    <?= $alert; ?>
    <ul class="breadcrumb breadcrumb-tabs">
      <li class="active">
        <a href="<?= SERVERURL ?>curso/" class="btn btn-info">
          <i class="zmdi zmdi-plus"></i> Nuevo
        </a>
      </li>
      <li>
        <a href="<?= SERVERURL ?>cursolist/" class="btn btn-success">
          <i class="zmdi zmdi-format-list-bulleted"></i> Lista
        </a>
      </li>
    </ul>
  </div>

  <div class="container-fluid">
    <div class="panel panel-info">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="zmdi zmdi-plus"></i> Nuevo Curso</h3>
      </div>
      <div class="panel-body">
        <form action="" method="POST" autocomplete="off">
          <fieldset>
            <legend><i class="zmdi zmdi-label"></i> Datos del Curso</legend>

            <div class="row">
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Nombre del Curso *</label>
                  <input name="nombre" class="form-control" type="text" required maxlength="255">
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Fecha de Inicio *</label>
                  <input name="fecha_inicio" class="form-control" type="date" required>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Descripción *</label>
                  <textarea name="descripcion" class="form-control" rows="2" required></textarea>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Fecha de Fin *</label>
                  <input name="fecha_fin" class="form-control" type="date" required>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Docente *</label>
                  <?php if($_SESSION['userType'] === "Administrador"): ?>
                    <select name="docente_codigo" class="form-control" required>
                      <option value="">Seleccione...</option>
                      <?php foreach($docentes as $d): ?>
                        <option value="<?= $d['Codigo']; ?>">
                          <?= htmlspecialchars("{$d['Apellidos']}, {$d['Nombres']}"); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  <?php else: /* Docente logueado */ ?>
                    <input type="text" class="form-control"
                           value="<?= htmlspecialchars($_SESSION['displayName'] ?? $_SESSION['userName']); ?>" disabled>
                    <input type="hidden" name="docente_codigo"
                           value="<?= $_SESSION['userKey']; ?>">
                  <?php endif; ?>
                </div>
              </div>
            </div>

          </fieldset>

          <p class="text-center">
            <button type="submit" class="btn btn-info btn-raised btn-sm">
              <i class="zmdi zmdi-floppy"></i> Guardar Curso
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>
</section>
