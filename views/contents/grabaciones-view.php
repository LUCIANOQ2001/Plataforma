<?php
// views/contents/grabaciones-view.php

// 1) Control de acceso: permitimos Admin, Docente y Estudiante
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente','Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/sesionController.php';
$insSesion = new sesionController();

// 2) Extraemos el ID de sesión de la URL “grabaciones/{id}/”
$parts    = explode('/', trim($_GET['views'], '/'));
$sesionId = isset($parts[1]) ? intval($parts[1]) : 0;

// 3) Obtener datos de la sesión
$dataSes = $insSesion->get_sesion_by_id_controller($sesionId);
if ($dataSes->rowCount() === 0) {
    echo '
    <section class="dashboard-contentPage">
      <div class="container-fluid">
        <div class="page-header">
          <h1 class="text-titles"><i class="zmdi zmdi-alert-circle"></i> Sesión no encontrada</h1>
        </div>
        <p class="lead">No existe la sesión indicada, o fue eliminada.</p>
      </div>
    </section>
    ';
    exit;
}
$ses = $dataSes->fetch(PDO::FETCH_ASSOC);

// 4) Procesar POST: subir o borrar grabación (sólo Admin/Docente)
$alert = '';
if (in_array($_SESSION['userType'], ['Administrador','Docente'])
    && $_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['delete_id'])) {
    $alert = $insSesion->delete_grabacion_controller((int)$_POST['delete_id']);
  } else {
    $alert = $insSesion->add_grabacion_controller($sesionId);
  }
  // PRG para evitar reenvío
  echo "<script>location.replace(location.pathname);</script>";
  exit;
}

// 5) Listar grabaciones
$grabs = $insSesion->list_grabaciones_by_sesion_controller($sesionId);
?>

<style>
    /* Si la lupa tiene la clase .btn-search o un <i class="zmdi zmdi-search"> */
  .btn-search,
  i.zmdi.zmdi-search {
    display: none !important;
  }
  html, body {
    background-color: #1e1f28;
    color: #fff;
    margin: 0; padding: 0;
    width: 100%; height: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
  }
  .dashboard-contentPage {
    margin-left: 130px;
    padding: 20px;
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
    margin-bottom: 1rem;
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
    color: #fff;
  }
  .grab-form {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
  }
  .grab-form input[type="file"] {
    display: inline-block;
  }
  .grab-form label {
    margin: 0;
    color: #fff;
  }
  .grab-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
  }
  .grab-table th,
  .grab-table td {
    padding: .75rem;
    border-bottom: 1px solid #444;
    color: #fff;
  }
  .grab-table th {
    text-align: left;
  }
  .grab-table td a {
    color: #0af;
    text-decoration: none;
  }
  .grab-table td .actions i {
    cursor: pointer;
    margin-left: 0.5rem;
    color: #f55;
  }
  .btn-info,
  .btn-raised {
    color: #fff;
    font-weight: bold;
  }
  .btn-info {
    background-color: #0288d1 !important;
    border: 1px solid #0277bd !important;
  }
  .btn-danger {
    background-color: #d32f2f !important;
    border: 1px solid #b71c1c !important;
    color: #fff !important;
  }
  /* Botón “Volver a Sesiones” */
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
    <a href="<?php echo SERVERURL; ?>sesion/<?php echo $ses['CursoId']; ?>/"
       class="btn btn-back-home btn-sm">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Sesiones
    </a>

    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-videocam"></i>
        Grabaciones &ndash; <?php echo htmlspecialchars($ses['Titulo']); ?>
      </h1>
    </div>
    <?php echo $alert; ?>
  </div>

  <div class="container-fluid">
    <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
      <!-- Formulario para nueva grabación -->
      <form action="" method="POST" enctype="multipart/form-data" class="grab-form">
        <label for="grab-file">Nueva grabación</label>
        <input id="grab-file" type="file" name="grabacion" required>
        <span id="grab-file-name"></span>
        <button type="submit" class="btn btn-info btn-raised btn-sm">
          <i class="zmdi zmdi-cloud-upload"></i> Subir
        </button>
      </form>
    <?php endif; ?>

    <!-- Tabla de grabaciones -->
    <div class="table-responsive">
      <table class="grab-table">
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
          <?php if (!empty($grabs)): foreach ($grabs as $g): ?>
          <tr>
            <td>
              <a href="<?php echo SERVERURL; ?>uploads/grabaciones/<?php echo urlencode($g['archivo']); ?>"
                 download="<?php echo htmlspecialchars($g['archivo']); ?>">
                <?php echo htmlspecialchars($g['archivo']); ?>
              </a>
            </td>
            <td><?php echo htmlspecialchars($g['fecha']); ?></td>
            <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
            <td class="actions">
              <form method="POST" style="display:inline">
                <input type="hidden" name="delete_id" value="<?php echo (int)$g['id']; ?>">
                <i class="zmdi zmdi-delete" title="Eliminar"
                   onclick="if(confirm('¿Eliminar grabación?')) this.parentElement.submit();">
                </i>
              </form>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="<?php echo in_array($_SESSION['userType'], ['Administrador','Docente']) ? 3 : 2; ?>"
                class="text-center">
              No hay grabaciones disponibles.
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
  // Mostrar nombre de archivo antes de subir
  document.getElementById('grab-file')?.addEventListener('change', function(){
    document.getElementById('grab-file-name').textContent =
      this.files[0]?.name || '';
  });
</script>
