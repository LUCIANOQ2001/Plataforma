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
  /* === Paleta de colores actualizada === */
  :root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(174,12,12,0.61);
    --text-light:       #FFFFFF;
    --hover-accent:     rgba(209,177,110,0.2);
  }

  /* Reseteo y fondo global */
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
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-image: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png');
    background-repeat: no-repeat;
    background-position: center;
    background-size: 60%;
    opacity: 0.05;
    z-index: 0;
    /* 1) Que no reciba eventos de ratón, para que los clicks pasen al sidebar */
    pointer-events: none;
    
    /* 2) Opcional: ajustarlo sólo al ancho del contenido (dejando libre el sidebar) */
    left: 270px;               /* mismo ancho de tu sidebar */
    width: calc(100% - 270px);
  }

  /* Contenido principal */
  .dashboard-contentPage {
    position: relative;
    z-index: 1;
    margin-left: 180px;   /* espacio para sidebar */
    width: calc(100% - 270px);
    padding: 0 3px auto;
    min-height: 100vh;
    box-sizing: border-box;
  }

  /* Cabecera de página */
  .page-header h1 {
    font-size: 2rem;
    color: var(--primary-accent);
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
    margin-bottom: 1rem;
  }
  .lead {
    color: rgba(255,255,255,0.7);
    font-size: 1.1rem;
    margin-bottom: 2rem;
  }
.zmdi-more-vert,
.zmdi-search,
.btn-menu-dashboard {
  display: none !important;
}
  /* Botón Volver */
  .btn-back-home {
    background: var(--primary-accent) !important;
    color: var(--text-light) !important;
    border: none !important;
    border-radius: .3rem;
    padding: .5rem 1rem;
    font-size: .9rem;
    transition: background .3s;
  }
  .btn-back-home:hover {
    background: var(--hover-accent) !important;
    text-decoration: none;
    color: var(--text-light) !important;
  }

  /* Panel de formulario */
  .panel {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    margin-bottom: 2rem;
    overflow: hidden;
    width: 100%;
    max-width: 900px;             /* o el valor que mejor te acomode */
    margin: 0 auto 2rem;          /* centrado horizontal y separación inferior */
    box-sizing: border-box;       /* para que padding no aumente el ancho */

  }
  .panel-heading {
    background: var(--primary-accent) !important;
    color: var(--text-light) !important;
    font-size: 1.2rem;
    text-align: center;
    padding: .75rem 1rem;
  }
  /* Asegura que los campos del formulario también entren */
  .panel-body .form-group,
  .panel-body input,
  .panel-body select,
  .panel-body textarea {
    width: 100% !important;
  }

  /* Estilos de formulario */
  .form-control {
    background: rgba(0, 0, 0, 0.1) !important;
    border: 1px solid #555 !important;
    color: var(--text-light) !important;
  }
  .control-label {
    color: rgba(255,255,255,0.7) !important;
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

    <div class="panel">
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
          <p class="text-center" style="margin-top:2rem;">
            <button type="submit" class="btn btn-success btn-raised btn-sm">
              <i class="zmdi zmdi-refresh"></i> Guardar Cambios
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>
</section>
