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
  /* ==== Paleta de colores ==== */
  :root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(174,12,12,0.61);
    --text-light:       #FFFFFF;
    --hover-accent:     rgba(209,177,110,0.2);
  }

  /* Reset y fondo global */
  html, body {
    margin: 0; padding: 0;
    background: var(--primary-bg);
    color: var(--text-light);
    width: 100%; height: 100%;
    overflow-x: hidden;
    font-family: 'RobotoCondensed', sans-serif;
  }

  /* Banner con logo de fondo */
  .dashboard-banner {/* aquí puedo cambiar la posición de la imagen de fondo*/
    position: fixed;
    top: 0; left: 270px;
    width: calc(100% - 270px); height: 100%;
    background-image: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png');
    background-repeat: no-repeat;
    background-position: center;
    background-size: 60%;
    opacity: 0.05;
    pointer-events: none;
    z-index: 0;
  }

  /* Contenido principal */
  .dashboard-contentPage {
    position: relative; z-index: 1;
    margin-left: 170px; /*esta vaina es para mover a la izquierda el contenido*/
    width: calc(100% - 270px);
    padding: 0 30px auto; /*lo de acá es para mover arriba o abajo todo el contenido xd*/
    min-height: 100vh;
    box-sizing: border-box;
  }

  /* Ocultar iconos indeseados */
  .btn-options,
  .dropdown-toggle,
  .btn-search,
  i.zmdi-zmdi-search,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }

  /* Cabecera */
  .page-header h1 {
    font-size: 2rem;
    color: var(--primary-accent);
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
    margin-bottom: .5rem;
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

  /* Panel de cuenta */
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
    color: var(--text-light) !important;
    padding: .75rem 1rem;
    font-weight: bold;
    text-align: center;
  }
  .panel-body {
    padding: 1.5rem;
  }

  /* Formulario */
  fieldset {
    border: none;
    margin: 0;
    padding: 0;
  }
  legend {
    font-size: 1.2rem;
    color: var(--text-light);
    margin-bottom: 1rem;
  }
  .form-control {
    background: rgba(255,255,255,0.1) !important;
    border: 1px solid #555 !important;
    color: var(--text-light) !important;
  }
  .control-label {
    color: rgb(5, 0, 0) !important;
  }
/* Estilo personalizado para el select y sus opciones */
select.form-control {
  background: rgba(255,255,255,0.1) !important; /* mismo fondo de otros inputs */
  color: var(--text-light) !important;          /* texto blanco */
  border: 1px solid #555 !important;
}

/* Opciones del desplegable */
select.form-control option {
  background: var(--secondary-bg) !important;    /* fondo oscuro */
  color: var(--text-light) !important;           /* texto claro */
}

/* Opción seleccionada */
select.form-control option:checked {
  background: var(--primary-accent) !important;  /* color acento */
  color: var(--text-light) !important;
}

/* Hover sobre opciones (sólo en navegadores que lo soporten) */
select.form-control option:hover {
  background: var(--hover-accent) !important;
  color: var(--text-light) !important;
}

  /* Botones de acción */
  .btn-success,
  .btn-info {
    font-weight: bold;
    border-radius: .3rem;
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

  /* Responsive */
  @media (max-width: 768px) {
    .dashboard-contentPage {
      margin-left: 0; width: 100%; padding: 1rem;
    }
    .dashboard-banner {
      left: 0; width: 100%;
    }
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1><i class="zmdi zmdi-settings"></i> Mi Cuenta</h1>
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
        <i class="zmdi zmdi-refresh"></i> Actualizar Cuenta
      </div>
      <div class="panel-body">
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
          <fieldset>
            <legend><i class="zmdi zmdi-key"></i> Datos de la Cuenta</legend>
            <input type="hidden" name="code" value="<?= htmlspecialchars($rows['Codigo']) ?>">
            <input type="hidden" name="oldusername" value="<?= htmlspecialchars($rows['Usuario']) ?>">

            <div class="row">
              <div class="col-sm-6">
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
              <div class="col-sm-6">
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
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Nueva Contraseña</label>
                  <input class="form-control"
                         type="password"
                         name="password1"
                         maxlength="70">
                </div>
              </div>
              <div class="col-sm-6">
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

          <p class="text-center" style="margin-top:2rem;">
            <button type="submit" class="btn btn-success btn-raised btn-sm">
              <i class="zmdi zmdi-floppy"></i> Guardar Cambios
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>
</section>
