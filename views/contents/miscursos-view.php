<?php
// views/contents/miscursos-view.php

// Sólo Docentes, Administradores y Estudiantes pueden ver esta página
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Docente','Administrador','Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

// Controlador de cursos
require_once __DIR__ . '/../../controllers/cursoController.php';
$insCurso = new cursoController();

$userType = $_SESSION['userType'];
$userKey  = $_SESSION['userKey'];

// Según el rol, obtenemos el listado apropiado
if ($userType === 'Estudiante') {
    $cursos   = $insCurso->list_cursos_estudiante_controller($userKey);
    $subtitle = "Aquí tienes los cursos en los que estás inscrito. Pasa el cursor sobre una tarjeta para ver sus opciones.";
} else {
    $cursos   = $insCurso->list_mis_cursos_controller($userKey);
    $subtitle = "Aquí tienes los cursos a tu cargo. Pasa el cursor sobre una tarjeta para ver sus opciones.";
}
?>
<style>
  /* Paleta de colores */
  :root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(174,12,12,0.61);
    --text-light:       #FFFFFF;
    --hover-accent:     rgba(209,177,110,0.2);
  }

  /* Reset global */
  html, body {
    margin: 0; padding: 0;
    background: var(--primary-bg);
    color: var(--text-light);
    width: 100%; height: 100%;
    overflow-x: hidden;
    font-family: 'RobotoCondensed', sans-serif;
  }

  /* Banner de logo de fondo */
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
    position: relative;
    z-index: 1;
    margin-left: 170px;
    width: calc(100% - 200px);
    padding: 0 30px auto;
    min-height: 100vh;
    box-sizing: border-box;
  }

  /* Ocultar iconos indeseados */
  .btn-options,
  .dropdown-toggle,
  .btn-search,
  i.zmdi.zmdi-search,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }

  /* Encabezado */
  .page-header h1 {
    text-align: center;
    font-size: 2rem;
    color: var(--primary-accent);
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
    margin-bottom: .5rem;
  }
  .page-header hr {
    width: 220px;
    border: none;
    border-top: 2px solid rgba(255,255,255,0.3);
    margin: .5rem auto 1.5rem;
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

  /* Grid de cursos */
  .courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1.5rem;
    max-width: 1000px;
    margin: 10px auto;
  }

  /* Tarjeta de curso */
  .course-card {
    position: relative;
    background: var(--secondary-bg);
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.6);
    overflow: visible;
    transition: transform .2s ease-in-out;
  }
  .course-card:hover {
    transform: translateY(-5px);
  }

  .course-header {
    height: 140px;
    background-size: cover;
    background-position: center;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
  }

  .course-body {
    padding: 1rem;
  }
  .course-title {
    font-size: 1.1rem;
    font-weight: bold;
    color: var(--primary-accent);
    margin-bottom: .5rem;
  }
  .course-subtitle {
    font-size: .9rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: .75rem;
  }

  /* Dropdown de opciones */
  .course-dropdown {
    position: absolute;
    top: 100%; left: 0; right: 0;
    background: #333;
    border-radius: 0 0 .5rem .5rem;
    display: none;
    box-shadow: 0 4px 8px rgba(0,0,0,0.5);
    z-index: 10;
  }
  .course-dates {
  font-size: 0.95rem;
  color: rgb(0, 0, 0);
  margin-top: 0.5rem;
}

  .course-dropdown a {
    display: block;
    padding: .75rem 1rem;
    color: var(--text-light);
    text-decoration: none;
    font-size: .9rem;
    transition: background .3s;
  }
  .course-dropdown a:hover {
    background: rgba(255,255,255,0.1);
  }
  .course-card:hover .course-dropdown {
    display: block;
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

  /* Responsivo */
  @media (max-width: 768px) {
    .dashboard-contentPage { margin-left: 0; width: 100%; padding: 1rem; }
    .dashboard-banner { left: 0; width: 100%; }
    .courses-grid { gap: 1rem; }
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1><i class="zmdi zmdi-book zmdi-hc-fw"></i> Mis Cursos</h1>
      <hr>
      <p class="lead"><?= $subtitle ?></p>
    </div>

    <p class="text-center">
      <a href="<?= SERVERURL ?>home/" class="btn btn-back-home">
        <i class="zmdi zmdi-long-arrow-return"></i> Volver
      </a>
    </p>
  </div>

  <div class="container-fluid">
    <?php if (empty($cursos)): ?>
      <p class="text-center">No hay cursos para mostrar.</p>
    <?php else: ?>
      <div class="courses-grid">
        <?php foreach ($cursos as $c): ?>
        <div class="course-card">
          <div class="course-header"
              style="background-image:url('<?= SERVERURL ?>views/assets/img/cursito.jpg')">
          </div>
          <div class="course-body">
            <div class="course-title"><?= htmlspecialchars($c['Nombre']) ?></div>
            <div class="course-subtitle"><?= htmlspecialchars($c['Descripcion']) ?></div>
            <div class="course-dates">
              <small>
                <strong>Inicio:</strong>
                <?= date('d/m/Y', strtotime($c['FechaInicio'])) ?>
                &nbsp;|&nbsp;
                <strong>Fin:</strong>
                <?= date('d/m/Y', strtotime($c['FechaFin'])) ?>
              </small>
            </div>
          </div>
          <div class="course-dropdown">
              <a href="<?= SERVERURL ?>sesion/<?= $c['id'] ?>/">
                <i class="zmdi zmdi-time-restore"></i> Sesiones
              </a>
              <a href="<?= SERVERURL ?>anunciocurso/<?= $c['id'] ?>/">
                <i class="zmdi zmdi-notifications"></i> Anuncios
              </a>
              <?php if ($userType === 'Estudiante'): ?>
                <a href="<?= SERVERURL ?>asistencialist/<?= $c['id'] ?>/">
                  <i class="zmdi zmdi-comment-text"></i> Mis Asistencias
                </a>
                <a href="<?= SERVERURL ?>reportenotas-student/<?= $c['id'] ?>/">
                  <i class="zmdi zmdi-chart"></i> Reporte de Notas
                </a>
              <?php else: ?>
                <a href="<?= SERVERURL ?>reportenotas/<?= $c['id'] ?>/">
                  <i class="zmdi zmdi-chart"></i> Reporte de Notas
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
