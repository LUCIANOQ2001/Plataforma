<?php
// views/contents/studentinfo-view.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente','Estudiante'])) {
  echo (new loginController())->login_session_force_destroy_controller();
  exit;
}

require_once __DIR__ . '/../../controllers/studentController.php';
$studentIns = new studentController();

// 1) Si viene POST, actualizamos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
  echo $studentIns->update_student_controller();
}

// 2) Obtenemos el código del usuario en URL
$parts  = explode('/', trim($_GET['views'],'/'));
$code   = $parts[1] ?? '';
$data   = $studentIns->data_student_controller("Only", $code);
if (!$data || $data->rowCount()===0) {
  echo '<div class="alert alert-danger text-center">Usuario no encontrado.</div>';
  return;
}
$rows = $data->fetch(PDO::FETCH_ASSOC);
?>

<style>
  /* Tus estilos oscuros aquí (puedes copiarlos de tu versión Admin) */
  .dashboard-contentPage { margin-left:170px; padding:30px; min-height:100vh; background:#1e1f28; color:#fff; }
  .page-header h1 { color:#00e5ff; }
  .lead { color:#ccc; }
  .panel { background:#2c2d3f; border:1px solid #3c3d4f; border-radius:12px; }
  .panel-heading { background:#43a047; color:#fff; }
  .panel-body { padding:20px; }
  .form-control { background:rgba(255,255,255,0.05); border:1px solid #555; color:#fff; }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1><i class="zmdi zmdi-settings"></i> Datos personales</h1>
    </div>
    <p class="lead">Aquí puedes actualizar tus datos personales.</p>
  </div>

  <div class="container-fluid">
    <p class="text-center">
      <a href="<?= SERVERURL ?>home/" class="btn btn-info btn-sm">
        <i class="zmdi zmdi-long-arrow-return"></i> Volver
      </a>
    </p>

    <div class="panel panel-success">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="zmdi zmdi-refresh"></i> Actualizar datos</h3>
      </div>
      <div class="panel-body">
        <form method="POST" autocomplete="off">
          <input type="hidden" name="code" value="<?= htmlspecialchars($rows['Codigo']) ?>">
          <fieldset>
            <legend><i class="zmdi zmdi-account-box"></i> Datos personales</legend>
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Nombres *</label>
                  <input type="text" name="name" class="form-control"
                         value="<?= htmlspecialchars($rows['Nombres']) ?>" required>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Apellidos *</label>
                  <input type="text" name="lastname" class="form-control"
                         value="<?= htmlspecialchars($rows['Apellidos']) ?>" required>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Email</label>
                  <input type="email" name="email" class="form-control"
                         value="<?= htmlspecialchars($rows['Email']) ?>">
                </div>
              </div>
            </div>
          </fieldset>
          <p class="text-center">
            <button type="submit" class="btn btn-success btn-raised btn-sm">
              <i class="zmdi zmdi-refresh"></i> Guardar cambios
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>
</section>
