<?php
// views/contents/sesion-view.php

// 1) Sólo usuarios autenticados (Admin, Docente o Estudiante)
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

$insCurso   = new cursoController();
$insSesion  = new sesionController();
$userType   = $_SESSION['userType'];

// 3) ID de curso por URL: /sesion/{cursoId}/
$parts    = explode("/", trim($_GET['views'], "/"));
$cursoId  = intval($parts[1]);

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

// 5) Procesar POST para creación de sesión (solo Admin/Docente)
$alert = '';
if (in_array($userType, ['Administrador','Docente']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $alert = $insSesion->add_sesion_controller($cursoId, $_POST);
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}

// 6) Listar sesiones
$sesionesRaw = $insSesion->list_sesiones_controller($cursoId);
$sesiones = $sesionesRaw instanceof PDOStatement
    ? $sesionesRaw->fetchAll(PDO::FETCH_ASSOC)
    : (is_array($sesionesRaw) ? $sesionesRaw : []);
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
  .dashboard-banner {
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
    margin-left: 140px;
    width: calc(100% - 270px);
    padding: 20 30px auto;
    min-height: 100vh;
    box-sizing: border-box;
  }

  /* Ocultar buscador y tres puntitos */
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
  }

  /* Botones */
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
  .btn-info {
    background: var(--primary-accent) !important;
    border: none !important;
    color: var(--text-light) !important;
    margin-bottom: 1.5rem;
  }
  .btn-info:hover {
    background: var(--hover-accent) !important;
  }

  /* Panel formulario */
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

  .form-control {
    background: rgba(255,255,255,0.1) !important;
    border: 1px solid #555 !important;
    color: var(--text-light) !important;
  }

  /* Grid de sesiones */
  .course-sessions {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px,1fr));
    gap: 1.5rem;
    margin-top: 2rem;
  }
  .session-card {
    background: var(--secondary-bg);
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    overflow: hidden;
    transition: transform .2s ease-in-out;
  }
  .session-card:hover {
    transform: translateY(-5px);
  }

  .session-card .header {
    background: var(--primary-accent);
    color:rgb(6, 1, 1);
    padding: 1rem;
    font-weight: bold;
    text-align: center;
  }
  .session-card .header small {
    display: block;
    font-size: .85rem;
    font-weight: normal;
    margin-top: .25rem;
    color: rgba(16, 1, 1, 0.8); /*esto cambia el color de las fechas de cada sesión*/
  }
  .session-card .body {
    padding: 1rem;
  }
  .session-card .body a {
    display: block;
    padding: .75rem 1rem;
    margin-bottom: .5rem;
    background: #333;
    border-radius: .5rem;
    color: var(--text-light);
    text-decoration: none;
    transition: background .2s;
  }
  .session-card .body a:hover {
    background: #444;
  }
  .session-card .body a i {
    margin-right: .5rem;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .dashboard-contentPage {
      margin-left: 0; width: 100%; padding: 1rem;
    }
    .dashboard-banner {
      left: 0; width: 100%;
    }
    .course-sessions {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <a href="<?= SERVERURL ?>miscursos/" class="btn btn-back-home btn-sm">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Mis Cursos
    </a>

    <div class="page-header">
      <h1><i class="zmdi zmdi-play-circle"></i> Sesiones de: <?= htmlspecialchars($curso['Nombre']) ?></h1>
    </div>
    <p class="lead"><?= htmlspecialchars($curso['Descripcion'] ?? '') ?></p>

    <?= $alert ?>
  </div>

  <?php if (in_array($userType, ['Administrador','Docente'])): ?>
    <div class="container-fluid">
      <button class="btn btn-info btn-raised btn-sm"
              onclick="document.getElementById('newSessionForm').style.display='block'">
        <i class="zmdi zmdi-plus"></i> Nueva Sesión
      </button>
    </div>
    <div class="container-fluid" id="newSessionForm" style="display:none; margin-top:1rem;">
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
                  <label class="control-label">Fecha *</label>
                  <input type="date" name="fecha" class="form-control" required>
                </div>
              </div>
              <div class="col-sm-4">
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
              <button type="button" class="btn btn-back-home"
                      onclick="this.closest('#newSessionForm').style.display='none'">
                Cancelar
              </button>
            </p>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="container-fluid">
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
            <div class="header">
              <?= htmlspecialchars($s['Titulo']) ?><br>
              <small><?= date("d/m/Y", strtotime($s['Fecha'])) ?></small>
            </div>
            <div class="body">
              <a href="<?= SERVERURL ?>material/<?= $s['id'] ?>/">
                <i class="zmdi zmdi-collection-text"></i> Material
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
  </div>
</section>
