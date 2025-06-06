<?php
// views/contents/account-view.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sólo Administrador, Docente y Estudiante pueden acceder
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente','Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

// Controlador
require_once "./controllers/mainController.php";
$insMain = new mainController();

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['code'])) {
    echo $insMain->update_account_controller();
}

// Obtener código de la URL
$parts = explode("/", trim($_GET['views'], "/"));
$code  = $parts[1] ?? '';

// Recuperar datos actuales
$data = $insMain->data_account_controller($code);
if (!$data || $data->rowCount() === 0) {
    echo '<p class="lead text-center">Usuario no encontrado.</p>';
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
    margin: 0; padding: 0;
    width: 100%; height: 100%;
    background-color: #1e1f28;
    color: #fff;
    overflow-x: hidden;
    box-sizing: border-box;
  }
  .dashboard-contentPage {
    margin-left: 130px;
    padding: 0 30px;
    min-height: 100vh;
    width: calc(100% - 170px);
    background-color: #1e1f28;
    box-sizing: border-box;
  }
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
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
    border: 1px solid #3c3d4f;
    margin-bottom: 30px;
  }
  .panel-heading {
    background-color: #00bcd4 !important;
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
    background: rgba(239,235,235,0.05) !important;
    border: 1px solid #555 !important;
    color: #fff !important;
  }
  legend {
    font-size: 1.1rem;
    color: #efebeb;
    margin-bottom: 20px;
    padding: 0;
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

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1>
        <i class="zmdi zmdi-settings zmdi-hc-fw"></i>
        Mi Cuenta
      </h1>
    </div>
    <p class="lead">
      Aquí puedes actualizar tus datos. Para cambiar la contraseña ingrésala dos veces;
      si no deseas cambiarla deja esos campos en blanco.
    </p>

    <p class="text-center">
      <a href="<?= SERVERURL ?>home/" class="btn btn-back-home">
        <i class="zmdi zmdi-long-arrow-return"></i> Volver
      </a>
    </p>

    <div class="panel">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="zmdi zmdi-refresh"></i> Actualizar Cuenta</h3>
      </div>
      <div class="panel-body">
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
          <fieldset>
            <legend><i class="zmdi zmdi-key"></i> Datos de la Cuenta</legend>
            <input type="hidden" name="code" value="<?= htmlspecialchars($rows['Codigo']) ?>">
            <input type="hidden" name="oldusername" value="<?= htmlspecialchars($rows['Usuario']) ?>">

            <div class="row">
              <div class="col-xs-12 col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Nombre de usuario *</label>
                  <input pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ]{1,15}"
                         class="form-control"
                         type="text"
                         name="username"
                         value="<?= htmlspecialchars($rows['Usuario']) ?>"
                         required maxlength="15">
                </div>
              </div>
              <div class="col-xs-12 col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Género</label>
                  <select name="gender" class="form-control">
                    <option value="<?= htmlspecialchars($rows['Genero']) ?>">
                      <?= htmlspecialchars($rows['Genero']) ?> (Actual)
                    </option>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                  </select>
                </div>
              </div>
              <div class="col-xs-12 col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Nueva Contraseña</label>
                  <input class="form-control"
                         type="password"
                         name="password1"
                         maxlength="70">
                </div>
              </div>
              <div class="col-xs-12 col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Repita la contraseña</label>
                  <input class="form-control"
                         type="password"
                         name="password2"
                         maxlength="70">
                </div>
              </div>
            </div>
          </fieldset>

          <p class="text-center">
            <button type="submit" class="btn btn-success btn-raised btn-sm">
              <i class="zmdi zmdi-floppy"></i> Guardar Cambios
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>
</section>
