<?php 
// views/contents/material-view.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente','Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/sesionController.php';
require_once __DIR__ . '/../../controllers/materialController.php';

$insSesion   = new sesionController();
$insMaterial = new materialController();

$parts     = explode("/", trim($_GET['views'], "/"));
$sesionId  = intval($parts[1]);

$dataSes = $insSesion->get_sesion_by_id_controller($sesionId);
if ($dataSes->rowCount() === 0) {
    echo '<div class="alert alert-danger">Sesión no encontrada.</div>';
    return;
}
$ses = $dataSes->fetch(PDO::FETCH_ASSOC);

$alert = '';
if (in_array($_SESSION['userType'], ['Administrador','Docente']) 
   && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_material'])) {
        $alert = $insMaterial->add_material_controller($sesionId);
    }
    if (isset($_POST['delete_id'])) {
        $alert = $insMaterial->delete_material_controller(intval($_POST['delete_id']));
    }
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}

$materials = $insMaterial->list_materials_controller($sesionId);
?>
<style>
  /* ==== Paleta de colores ==== */
  :root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(174,12,12,0.61);
    --text-light:       #FFFFFF;
    --hover-accent:     rgba(209,177,110,0.2);
  }

  /* Reset global */
  html, body {
    margin: 0; padding: 0;
    background: var(--primary-bg);
    color: var(--text-light);
    width: 100%; height: 100%;
    overflow-x: hidden;
    font-family: 'RobotoCondensed', sans-serif;
  }

  /* Banner con logo de fondo */
  .dashboard-banner {
    position: fixed;
    top: 0; left: 270px;
    width: calc(100% - 270px); height: 100%;
    background: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
    opacity: 0.05;
    pointer-events: none;
    z-index: 0;
  }

  /* Contenido principal */
  .dashboard-contentPage {
    position: relative; z-index: 1;
    margin-left: 180px;
    width: calc(100% - 270px);
    padding: 0 30px auto;
    min-height: 100vh;
    box-sizing: border-box;
  }

  /* Ocultar íconos indeseados */
  .btn-options,
  .dropdown-toggle,
  .btn-search,
  i.zmdi-zmdi-search,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }

  /* Encabezado */
  .page-header h1 {
    font-size: 2rem;
    color: var(--primary-accent);
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
    margin-bottom: 0.5rem;
    text-align: center;
  }
  .lead {
    text-align: center;
    font-size: 1.1rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: 2rem;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
  }

  /* Botón Volver */
  .btn-back-home {
    background: var(--primary-accent) !important;
    color: var(--text-light) !important;
    border: none !important;
    border-radius: .3rem;
    padding: .5rem 1rem;
    font-size: .9rem;
    display: inline-block;
    margin-bottom: 1.5rem;
    transition: background .3s;
  }
  .btn-back-home:hover {
    background: var(--hover-accent) !important;
    text-decoration: none;
  }

  /* Panel principal */
  .panel {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    margin-bottom: 2rem;
    overflow: hidden;
  }
  .panel-heading {
    background: var(--primary-accent) !important;
    color: #2B2B2B;
    padding: .75rem 1rem;
    font-weight: bold;
    text-align: center;
  }
  .panel-body {
    padding: 1.5rem;
  }

  /* Formulario añadiendo material */
  .form-control, textarea {
    background: rgba(255,255,255,0.1) !important;
    border: 1px solid #555 !important;
    color: var(--text-light) !important;
  }
  .control-label {
    color: rgba(255,255,255,0.7) !important;
  }
  .btn-info, .btn-success, .btn-danger {
    font-weight: bold;
    border-radius: .3rem;
  }
  .btn-info {
    background: var(--primary-accent) !important;
    border: none !important;
    color: var(--text-light) !important;
  }
  .btn-success {
    background: var(--hover-accent) !important;
    border: none !important;
    color: var(--text-light) !important;
  }
  .btn-danger {
    background: #b71c1c !important;
    border: none !important;
    color: var(--text-light) !important;
  }

  /* Tabla de materiales */
  .table {
    margin-top: 1rem;
    color: var(--text-light);
    border-collapse: collapse;
    width: 100%;
  }
  .table th, .table td {
    border: 1px solid rgba(255,255,255,0.2);
    padding: .75rem;
    text-align: center;
  }
  .table th {
    background: var(--primary-accent);
    color: var(--text-light);
  }
  .table tbody tr:nth-child(even) {
    background: rgba(255,255,255,0.05);
  }

  /* Responsive */
  @media (max-width: 768px) {
    .dashboard-contentPage {
      margin-left: 0; width: 100%; padding: 1rem;
    }
    .dashboard-banner {
      left: 0; width: 100%;
    }
    .table, .table th, .table td {
      font-size: .9rem;
    }
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <a href="<?= SERVERURL;?>sesion/<?= $ses['CursoId']; ?>/"
       class="btn btn-back-home btn-sm">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Sesiones
    </a>

    <div class="page-header">
      <h1><i class="zmdi zmdi-collection-text"></i> Material – <?= htmlspecialchars($ses['Titulo']); ?></h1>
    </div>
    <?php if ($alert): ?>
      <div class="text-center" style="margin-bottom:1rem; color: var(--primary-accent);">
        <?= $alert ?>
      </div>
    <?php endif; ?>
    <p class="lead">Aquí ves todos los archivos subidos para esta sesión.</p>
  </div>

  <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
  <div class="container-fluid">
    <button class="btn btn-info btn-sm"
            onclick="document.getElementById('formAdd').style.display='block'">
      <i class="zmdi zmdi-plus"></i> Nuevo Material
    </button>
  </div>

  <div class="container-fluid" id="formAdd" style="display:none; margin-top:1rem;">
    <div class="panel">
      <div class="panel-heading"><i class="zmdi zmdi-plus-box"></i> Agregar Material</div>
      <div class="panel-body">
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
          <input type="hidden" name="add_material" value="1">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group label-floating">
                <label class="control-label">Título *</label>
                <input name="titulo" class="form-control" type="text" required>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group label-floating">
                <label class="control-label">Archivo *</label>
                <input name="archivo" class="form-control" type="file" required>
              </div>
            </div>
          </div>
          <p class="text-center" style="margin-top:1rem;">
            <button type="submit" class="btn btn-success btn-sm">
              <i class="zmdi zmdi-floppy"></i> Subir
            </button>
            <button type="button" class="btn btn-back-home btn-sm"
                    onclick="document.getElementById('formAdd').style.display='none'">
              Cancelar
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="container-fluid">
    <div class="panel">
      <div class="panel-heading"><i class="zmdi zmdi-folder"></i> Lista de Material</div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Archivo</th>
                <th>Fecha</th>
                <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
                  <th>Acciones</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($materials)): ?>
                <tr>
                  <td colspan="<?= in_array($_SESSION['userType'], ['Administrador','Docente']) ? 3 : 2 ?>">
                    No hay material aún.
                  </td>
                </tr>
              <?php else: foreach ($materials as $m): ?>
                <tr>
                  <td>
                    <i class="zmdi zmdi-folder"></i>
                    <a href="<?= SERVERURL . 'attachments/material/' . $m['Archivo'] ?>"
                       target="_blank"
                       style="color: var(--text-light); text-decoration: none;">
                      <?= htmlspecialchars($m['Titulo']) ?>
                    </a>
                  </td>
                  <td><?= date("d/m/Y H:i", strtotime($m['Fecha'])) ?></td>
                  <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
                  <td>
                    <form method="POST" style="display:inline">
                      <input type="hidden" name="delete_id" value="<?= $m['id'] ?>">
                      <button type="submit"
                              class="btn btn-danger btn-sm"
                              onclick="return confirm('¿Eliminar este material?');">
                        <i class="zmdi zmdi-delete"></i>
                      </button>
                    </form>
                  </td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
