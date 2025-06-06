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
if (!$data || $data->rowCount() === 0) {
  echo '<div class="alert alert-danger text-center">Usuario no encontrado.</div>';
  return;
}
$rows = $data->fetch(PDO::FETCH_ASSOC);
?>

<style>
    /* Si la lupa tiene la clase .btn-search o un <i class="zmdi zmdi-search"> */
  .btn-search,
  i.zmdi.zmdi-search {
    display: none !important;
  }
  html, body {
    margin: 0;
    padding: 0;
    background-color: #1e1f28;
    color: #fff;
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
  }

  .dashboard-contentPage {
    margin-left: 130px;
    padding: 0 30px;
    background: #1e1f28;
    min-height: 100vh;
    max-width: 1350px;
    margin-right: auto;
    box-sizing: border-box;
  }

  .page-header h1 {
    font-size: 28px;
    color: #00e5ff;
    text-shadow: 1px 1px 6px #000;
    margin-bottom: 10px;
  }

  .lead {
    color: #ccc;
    font-size: 1.1rem;
    margin-bottom: 30px;
  }

  .btn-back-home {
    background-color: #607d8b !important;
    border-color: #455a64 !important;
    color: #fff !important;
    margin-bottom: 20px;
    padding: 8px 14px;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-block;
  }
  .btn-back-home i {
    margin-right: 6px;
  }

  .panel {
    background: #2c2d3f;
    border: 1px solid #3c3d4f;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
    margin-bottom: 30px;
  }

  .panel-heading {
    background: #43a047 !important;
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

  .form-control, .control-label {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid #555 !important;
    color: #fff !important;
  }

  .form-group label.control-label {
    color: #ccc;
  }

  .btn-success, .btn-info {
    color: #fff !important;
    font-weight: bold;
  }
  .btn-success {
    background-color: #388e3c !important;
    border: 1px solid #2e7d32 !important;
  }
  .btn-success:hover {
    background-color: #2e7d32 !important;
  }
  .btn-info {
    background-color: #0288d1 !important;
    border: 1px solid #0277bd !important;
  }
  .btn-info:hover {
    background-color: #0277bd !important;
  }

</style>
<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1><i class="zmdi zmdi-settings"></i> Datos Personales</h1>
    </div>
    <p class="lead">Aquí puedes actualizar tus datos personales.</p>

    <p class="text-center">
      <a href="<?= SERVERURL ?>home/" class="btn btn-back-home">
        <i class="zmdi zmdi-long-arrow-return"></i> Volver
      </a>
    </p>

    <div class="panel panel-success">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="zmdi zmdi-refresh"></i> Actualizar Datos</h3>
      </div>
      <div class="panel-body">
        <form method="POST" autocomplete="off">
          <input type="hidden" name="code" value="<?= htmlspecialchars($rows['Codigo']) ?>">
          <fieldset>
            <legend><i class="zmdi zmdi-account-box"></i> Datos Personales</legend>
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
          <p class="text-center" style="margin-top:20px;">
            <button type="submit" class="btn btn-success btn-raised btn-sm">
              <i class="zmdi zmdi-refresh"></i> Guardar Cambios
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>
</section>
