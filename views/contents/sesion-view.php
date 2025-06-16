<?php
// views/contents/sesion-view.php

// 1) Sólo usuarios autorizados
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente','Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

// 2) Controladores necesarios
require_once __DIR__ . '/../../controllers/cursoController.php';
require_once __DIR__ . '/../../controllers/sesionController.php';

$insCurso  = new cursoController();
$insSesion = new sesionController();
$userType  = $_SESSION['userType'];

// 3) Leer cursoId de la URL
$parts   = explode("/", trim($_GET['views'], "/"));
$cursoId = intval($parts[1]);

// 4) Obtener datos del curso
$dataCurso = $insCurso->get_curso_by_id_controller($cursoId);
if ($dataCurso instanceof PDOStatement) {
    if ($dataCurso->rowCount() === 0) {
        echo '<div class="alert alert-danger">Curso no encontrado.</div>';
        return;
    }
    $curso = $dataCurso->fetch(PDO::FETCH_ASSOC);
} else {
    if (empty($dataCurso)) {
        echo '<div class="alert alert-danger">Curso no encontrado.</div>';
        return;
    }
    $curso = isset($dataCurso['Nombre']) ? $dataCurso : $dataCurso[0];
}

// 5) Procesar POST: creación o eliminación
$alert = '';
if (in_array($userType, ['Administrador','Docente']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['deleteSession'])) {
        // Eliminar sesión
        $alert = $insSesion->delete_sesion_controller((int)$_POST['deleteSession']);
        // recarga para reflejar el cambio
        echo "<script>location.replace(location.pathname);</script>";
        exit;
    }
    if (isset($_POST['titulo'])) {
        // Nueva sesión
        $alert = $insSesion->add_sesion_controller($cursoId, $_POST);
        echo "<script>location.replace(location.pathname);</script>";
        exit;
    }
}

// 6) Listar sesiones
$sesiones = $insSesion->list_sesiones_controller($cursoId);
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
  html, body {
    margin:0; padding:0;
    background: var(--primary-bg);
    color: var(--text-light);
    width:100%; height:100%;
    overflow-x:hidden;
    font-family:'RobotoCondensed',sans-serif;
  }
  .dashboard-banner {
    position:fixed; top:0; left:270px;
    width:calc(100% - 270px); height:100%;
    background-image:url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png');
    background-repeat:no-repeat;
    background-position:center;
    background-size:60%;
    opacity:0.05;
    pointer-events:none;
    z-index:0;
  }
  .dashboard-contentPage {
    position:relative; z-index:1;
    margin-left:180px;
    width:calc(100% - 270px);
    padding:auto;
    min-height:100vh;
    box-sizing:border-box;
  }

 /* ocultar buscador y menú */
  .btn-search,
  i.zmdi.zmdi-search,
  .btn-options,
  .dropdown-toggle,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }

  /*botón volver*/
  .btn-back-home {
    background:var(--primary-accent)!important;
    color:var(--text-light)!important;
    border:none!important;
    border-radius:.3rem;
    padding:.5rem 1rem;
    font-size:.9rem;
    margin-bottom:1.5rem;
    display:inline-block;
    transition:background .3s;
  }
  .btn-back-home:hover {
    background:var(--hover-accent)!important;
    text-decoration:none;
  }
  .page-header h1 {
    font-size:2rem;
    color:var(--primary-accent);
    text-shadow:2px 2px 8px rgba(0,0,0,0.7);
    margin-bottom:.5rem;
    text-align:center;
  }
  .lead {
    text-align:center;
    font-size:1.1rem;
    color:rgba(255,255,255,0.7);
    margin-bottom:2rem;
  }
  .btn-info {
    background:var(--primary-accent)!important;
    border:none!important;
    color:var(--text-light)!important;
    margin-bottom:1rem;
  }
  .btn-info:hover {
    background:var(--hover-accent)!important;
  }
  .panel {
    background:var(--secondary-bg);
    border:1px solid var(--primary-accent);
    border-radius:1rem;
    box-shadow:0 4px 12px rgba(0,0,0,0.5);
    margin-bottom:2rem;
  }
  .panel-heading {
    background:var(--primary-accent)!important;
    color:var(--text-light)!important;
    padding:.75rem 1rem;
    font-weight:bold;
    text-align:center;
  }
  .panel-body { padding:1.5rem; }
  .form-control {
    background:rgba(255,255,255,0.1)!important;
    border:1px solid #555!important;
    color:var(--text-light)!important;
  }
  .course-sessions {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(250px,1fr));
    gap:1.5rem;
    margin-top:2rem;
  }
  .session-card {
    background:var(--secondary-bg);
    border-radius:1rem;
    box-shadow:0 4px 12px rgba(0,0,0,0.5);
    transition:transform .2s;
    position:relative;
  }
  .session-card:hover { transform:translateY(-5px); }
  .session-card .header {
    background:var(--primary-accent);
    color:#000;
    padding:1rem;
    text-align:center;
    font-weight:bold;
  }
  .session-card .header small {
    display:block;
    font-size:.85rem;
    margin-top:.25rem;
    color:rgba(0,0,0,0.7);
  }
  .session-card .body {
    padding:1rem;
  }
  .session-card .body a {
    display:block;
    padding:.75rem 1rem;
    margin-bottom:.5rem;
    background:#333;
    border-radius:.5rem;
    color:var(--text-light);
    text-decoration:none;
    transition:background .2s;
  }
  .session-card .body a:hover { background:#444; }

  /* Botón eliminar */
  .session-card form.delete-form {
    position:absolute;
    top:8px; right:8px;
  }
  .session-card button.delete-btn {
    background:transparent;
    border:none;
    color:var(--text-light);
    font-size:1.2rem;
    cursor:pointer;
  }
  .session-card button.delete-btn:hover {
    color:var(--hover-accent);
  }

  @media(max-width:768px){
    .dashboard-contentPage{margin-left:0;width:100%;padding:1rem;}
    .dashboard-banner{left:0;width:100%;}
    .course-sessions{grid-template-columns:1fr;}
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <a href="<?= SERVERURL ?>miscursos/" class="btn-back-home btn-sm">
    <i class="zmdi zmdi-arrow-left"></i> Volver a Mis Cursos
  </a>

  <div class="page-header">
    <h1><i class="zmdi zmdi-play-circle"></i>
      Sesiones de: <?= htmlspecialchars($curso['Nombre']) ?>
    </h1>
    <p class="lead"><?= htmlspecialchars($curso['Descripcion'] ?? '') ?></p>
    <?= $alert ?>
  </div>

  <?php if (in_array($userType, ['Administrador','Docente'])): ?>
    <button class="btn btn-info btn-raised btn-sm"
            onclick="document.getElementById('newSessionForm').style.display='block'">
      <i class="zmdi zmdi-plus"></i> Nueva Sesión
    </button>
    <div id="newSessionForm" style="display:none; margin-top:1rem;">
      <div class="panel">
        <div class="panel-heading">
          <i class="zmdi zmdi-plus-circle"></i> Crear Sesión
        </div>
        <div class="panel-body">
          <form method="POST" autocomplete="off">
            <div class="row">
              <div class="col-sm-4">
                <div class="form-group label-floating">
                  <label class="control-label">Título *</label>
                  <input type="text" name="titulo" class="form-control" required>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="form-group label-floating">
                  <label class="control-label">Fecha de Inicio *</label>
                  <input type="date" name="fecha" class="form-control" required>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="form-group label-floating">
                  <label class="control-label">Fecha de Fin *</label>
                  <input type="date" name="fecha_fin" class="form-control" required>
                </div>
              </div>
            </div>
            <div class="row" style="margin-top:1rem;">
              <div class="col-sm-12">
                <div class="form-group label-floating">
                  <label class="control-label">Enlace/Video</label>
                  <input type="text" name="video" class="form-control">
                </div>
              </div>
            </div>
            <p class="text-center" style="margin-top:1rem;">
              <button type="submit" class="btn btn-success btn-raised">
                <i class="zmdi zmdi-floppy"></i> Guardar Sesión
              </button>
              <button type="button" class="btn-back-home"
                      onclick="document.getElementById('newSessionForm').style.display='none'">
                Cancelar
              </button>
            </p>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="course-sessions">
    <?php if (empty($sesiones)): ?>
      <p>No hay sesiones aún.
        <?php if(in_array($userType,['Administrador','Docente'])): ?>
          Crea la primera arriba.
        <?php endif; ?>
      </p>
    <?php else: ?>
      <?php foreach($sesiones as $s): ?>
        <div class="session-card">
          <?php if(in_array($userType,['Administrador','Docente'])): ?>
            <form method="POST" class="delete-form" onsubmit="return confirm('¿Eliminar esta sesión?');">
              <input type="hidden" name="deleteSession" value="<?= $s['id']; ?>">
              <button type="submit" class="delete-btn" title="Eliminar sesión">
                <i class="zmdi zmdi-delete"></i>
              </button>
            </form>
          <?php endif; ?>

                <div class="header">
            <?= htmlspecialchars($s['Titulo']) ?><br>
            <small>
              Inicio: <?= date("d/m/Y", strtotime($s['Fecha'])) ?><br>
              Fin:    
              <?php
                // Si FechaFin viene vacía o nula mostramos un guion
                if (!empty($s['FechaFin'])) {
                    echo date("d/m/Y", strtotime($s['FechaFin']));
                } else {
                    echo '—';
                }
              ?>
            </small>
          </div>

          <div class="body">
            <a href="<?= SERVERURL ?>material/<?= $s['id'] ?>/">
              <i class="zmdi zmdi-collection-text"></i> Material
            </a>
            <a href="<?= SERVERURL ?>actividades/<?= $s['id'] ?>/">
              <i class="zmdi zmdi-collection-item"></i> Actividades Pendientes
            </a>

            <?php if ($userType === 'Docente' || $userType === 'Administrador'): ?>
              <a href="<?= SERVERURL ?>evaluacion/<?= $s['id'] ?>/">
                <i class="zmdi zmdi-assignment"></i> Evaluación
              </a>
            <?php else: ?>
              <a href="<?= SERVERURL ?>evaluacion-student/<?= $s['id'] ?>/estudiante/">
                <i class="zmdi zmdi-assignment"></i> Evaluación
              </a>
            <?php endif; ?>
            <?php if ($s['Video']): ?>
              <a href="<?= htmlspecialchars($s['Video']) ?>" target="_blank">
                <i class="zmdi zmdi-videocam"></i> Video
              </a>
            <?php endif; ?>
            <a href="<?= SERVERURL ?>grabaciones/<?= $s['id'] ?>/">
              <i class="zmdi zmdi-movie"></i> Grabaciones
            </a>
            <a href="<?= SERVERURL ?>foro/<?= $s['id'] ?>/">
              <i class="zmdi zmdi-comments"></i> Foro
            </a>
            <a href="<?= SERVERURL ?>asistencia/<?= $s['id'] ?>/">
              <i class="zmdi zmdi-comment-text"></i> Asistencias
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
