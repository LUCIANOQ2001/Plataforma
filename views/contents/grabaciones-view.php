<?php
// Sólo Docentes/Admin pueden ver esta página
if (!in_array($_SESSION['userType'] ?? '', ['Docente','Administrador'])) {
  $logout = new loginController();
  echo $logout->login_session_force_destroy_controller();
  exit;
}

require_once __DIR__ . '/../../controllers/sesionController.php';
$insSesion = new sesionController();

// 1) Extraemos el ID de sesión de la URL “sesion/{id}/”
$views    = $_GET['views'] ?? '';
$parts    = explode('/', $views);
$sesionId = isset($parts[1]) ? intval($parts[1]) : 0;

// 2) Procesar POST: subir o borrar grabación
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['delete_id'])) {
    $alert = $insSesion->delete_grabacion_controller((int)$_POST['delete_id']);
  } else {
    $alert = $insSesion->add_grabacion_controller($sesionId);
  }
  // PRG para evitar reenvío
  echo "<script>location.href = location.href;</script>";
  exit;
}

// 3) Obtener datos de la sesión
$dataSes = $insSesion->get_sesion_by_id_controller($sesionId);
$ses     = ($dataSes instanceof PDOStatement)
             ? $dataSes->fetch(PDO::FETCH_ASSOC)
             : false;

// Si no hay sesión, mensaje
if (!$ses) {
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

// 4) Listar grabaciones
$grabs = $insSesion->list_grabaciones_by_sesion_controller($sesionId);
?>

<style>
  .dashboard-contentPage { margin-left:170px; padding:20px; }
  .grab-form { display:flex; align-items:center; gap:1rem; margin-bottom:1rem; }
  .grab-form input[type="file"] { display:inline-block; }
  .grab-form label { margin:0; }

  .grab-table {
    width:100%; border-collapse: collapse; margin-top:1rem;
  }
  .grab-table th,
  .grab-table td {
    padding:.75rem; border-bottom:1px solid #444; color:#fff;
  }
  .grab-table th { text-align:left; }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-videocam"></i>
        Grabaciones &ndash;  <?php echo htmlspecialchars($ses['Titulo']); ?>
      </h1>
    </div>
    <?php echo $alert; ?>
  </div>

  <div class="container-fluid">
    <!-- Formulario para nueva grabación -->
    <form action="" method="POST" enctype="multipart/form-data" class="grab-form">
      <label class="control-label" for="grab-file">Nueva grabación</label>
      <input id="grab-file" type="file" name="grabacion" required>
      <span id="grab-file-name" style="color:#fff;"></span>
      <button type="submit" class="btn btn-info btn-raised btn-sm">
        <i class="zmdi zmdi-cloud-upload"></i> Subir
      </button>
    </form>

    <!-- Tabla de grabaciones -->
    <div class="table-responsive">
      <table class="grab-table">
        <thead>
          <tr>
            <th>Archivo</th>
            <th>Fecha</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($grabs)): foreach ($grabs as $g): ?>
          <tr>
            <td>
              <a href="<?php echo SERVERURL; ?>uploads/grabaciones/<?php echo urlencode($g['archivo']); ?>"
                 target="_blank">
                <?php echo htmlspecialchars($g['archivo']); ?>
              </a>
            </td>
            <td><?php echo htmlspecialchars($g['fecha']); ?></td>
            <td>
              <form style="display:inline" method="POST">
                <input type="hidden" name="delete_id" value="<?php echo (int)$g['id']; ?>">
                <button type="submit"
                        class="btn btn-danger btn-xs"
                        onclick="return confirm('¿Eliminar grabación?')">
                  <i class="zmdi zmdi-delete"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="3">No hay grabaciones disponibles.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
// Mostrar nombre de archivo antes de subir
document.getElementById('grab-file').addEventListener('change', function(){
  const name = this.files[0]?.name || '';
  document.getElementById('grab-file-name').textContent = name;
});
</script>
