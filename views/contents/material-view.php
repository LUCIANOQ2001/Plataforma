<?php 
// views/contents/material-view.php

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
  /* ------------------------------------ */
  /* Ocultar íconos de “tres puntos” y “lupa” */
  /* ------------------------------------ */

  .btn-options,
  .dropdown-toggle {
    display: none !important;
  }

  .btn-search,
  i.zmdi.zmdi-search {
    display: none !important;
  }

  html, body {
    background-color: #1e1f28;
    color: #fff;
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
  }
  .dashboard-contentPage {
    margin-left: 130px;
    padding: 0 30px;
    width: calc(100% - 170px);
    background-color: #1e1f28;
    box-sizing: border-box;
  }
  .page-header h1 {
    font-size: 28px;
    color: #00e5ff;
    text-shadow: 1px 1px 6px #000;
  }
  .lead {
    color: #ccc;
    font-size: 1.1rem;
    margin-bottom: 30px;
  }
  .panel {
    background: #2c2d3f;
    border: 1px solid #3c3d4f;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
  }
  .panel-heading {
    background-color: #00bcd4;
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
  .form-control, .control-label, textarea {
    background: rgba(255,255,255,0.08) !important;
    border: 1px solid #555 !important;
    color: #fff !important;
  }
  .btn-info, .btn-success, .btn-danger {
    color: #fff !important;
    font-weight: bold;
  }
  .btn-info {
    background-color: #0288d1 !important;
    border: 1px solid #0277bd !important;
  }
  .btn-success {
    background-color: #388e3c !important;
    border: 1px solid #2e7d32 !important;
  }
  .btn-danger {
    background-color: #d32f2f !important;
    border: 1px solid #b71c1c !important;
  }
  .table {
    color: #fff;
    border-color: #444;
    margin-top: 1rem;
  }
  .table th, .table td {
    border: 1px solid #444;
    text-align: center;
  }
  .btn-back-home {
    background-color: #607d8b !important;
    border-color: #455a64 !important;
    color: #fff !important;
    margin-bottom: 20px;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <!-- Botón Volver a Mis Sesiones -->
    <a href="<?php echo SERVERURL;?>sesion/<?php echo $ses['CursoId'];?>/"
       class="btn btn-back-home btn-sm">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Sesiones
    </a>

    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-collection-text"></i>
        Material – <?php echo htmlspecialchars($ses['Titulo']); ?>
      </h1>
    </div>
    <?php echo $alert; ?>
    <p class="lead">Aquí ves todos los archivos subidos para esta sesión.</p>
  </div>

  <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
  <div class="container-fluid">
    <button class="btn btn-info btn-raised"
            onclick="document.getElementById('formAdd').style.display='block'">
      <i class="zmdi zmdi-plus"></i> Nuevo Material
    </button>
  </div>

  <div class="container-fluid" id="formAdd" style="display:none; margin-top:1rem;">
    <div class="panel panel-info">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="zmdi zmdi-plus-box"></i> Agregar Material</h3>
      </div>
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
          <p class="text-center">
            <button type="submit" class="btn btn-success btn-raised">
              <i class="zmdi zmdi-floppy"></i> Subir
            </button>
            <button type="button" class="btn btn-default"
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
    <div class="panel panel-success">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="zmdi zmdi-folder"></i> Lista de Material</h3>
      </div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-hover">
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
                  <td colspan="<?php echo in_array($_SESSION['userType'], ['Administrador','Docente']) ? 3 : 2; ?>">
                    No hay material aún.
                  </td>
                </tr>
              <?php else: foreach ($materials as $m): ?>
                <tr>
                  <td>
                    <i class="zmdi zmdi-folder"></i>
                    <a href="<?php echo SERVERURL . 'attachments/material/' . $m['Archivo']; ?>"
                       target="_blank">
                      <?php echo htmlspecialchars($m['Titulo']); ?>
                    </a>
                  </td>
                  <td><?php echo date("d/m/Y H:i", strtotime($m['Fecha'])); ?></td>
                  <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
                  <td>
                    <form method="POST" style="display:inline">
                      <input type="hidden" name="delete_id" value="<?php echo $m['id']; ?>">
                      <button type="submit" class="btn btn-danger btn-sm btn-raised"
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
