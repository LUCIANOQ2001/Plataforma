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
if (isset($_POST['username'], $_POST['code'])) {
    echo $insMain->update_account_controller();
}

// Obtener código de la URL
$parts = explode("/", trim($_GET['views'], "/"));
$code  = $parts[1] ?? '';

// Recuperar datos actuales
$data = $insMain->data_account_controller($code);
if ($data->rowCount() === 0) {
    echo '<p class="lead text-center">Usuario no encontrado.</p>';
    return;
}
$rows = $data->fetch(PDO::FETCH_ASSOC);
?>

<!-- Estilos modernos oscuros unificados -->
<style>
  html, body {
    margin: 0; padding: 0;
    width: 100%; height: 100%;
    background-color: #1e1f28;
    color: #fff;
    overflow-x: hidden;
    box-sizing: border-box;
  }
  .dashboard-contentPage {
    margin-left: 170px;
    padding: 30px;
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
  .panel {
    background: #2c2d3f;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.5);
    border: 1px solid #3c3d4f;
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
  fieldset, legend {
    border: none;
    padding: 0;
    margin-bottom: 20px;
    color: #efebeb;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-settings zmdi-hc-fw"></i>
        Mi Cuenta
      </h1>
    </div>
    <p class="lead">
      Aquí puedes actualizar tus datos. Para cambiar la contraseña ingrésala dos veces;
      si no deseas cambiarla deja esos campos en blanco.
    </p>
  </div>

  <div class="container-fluid">
    <div class="panel">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="zmdi zmdi-refresh"></i> Actualizar Cuenta</h3>
      </div>
      <div class="panel-body">
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
          <fieldset>
            <legend><i class="zmdi zmdi-key"></i> Datos de la cuenta</legend>
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
