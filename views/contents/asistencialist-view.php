<?php 
// views/contents/asistencialist-view.php

// Sólo Estudiantes pueden acceder a este historial
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SESSION['userType'] !== "Estudiante") {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

// 1) Extraer el courseId de la URL: /asistencialist/{cursoId}/
$parts    = explode('/', trim($_GET['views'], '/'));
$cursoId  = intval($parts[1] ?? 0);

if ($cursoId <= 0) {
    echo '<div class="alert alert-danger text-center">Curso no válido.</div>';
    return;
}

require_once __DIR__ . '/../../controllers/asistenciaController.php';
require_once __DIR__ . '/../../controllers/cursoController.php';

// 2) Verificar inscripción
$insCurso     = new cursoController();
$userKey      = $_SESSION['userKey'] ?? '';
$estaInscrito = $insCurso->is_estudiante_inscrito_en_curso_controller($userKey, $cursoId);
if (!$estaInscrito) {
    echo '<div class="alert alert-danger text-center">No estás inscrito en este curso.</div>';
    return;
}

// 3) Historial
$insAsist = new asistenciaController();
$records  = $insAsist->get_history_by_student_course_controller($userKey, $cursoId);

// 4) Nombre del curso
$cursoInfo   = $insCurso->get_curso_by_id_controller($cursoId);
$cursoNombre = $cursoInfo['Nombre'] ?? '';
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
  .dashboard-banner {
    position: fixed;
    top: 0; left: 170px;
    width: calc(100% - 170px);
    height: 100%;
    background: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
    opacity: 0.05;
    pointer-events: none;
    z-index: 0;
  }
  .btn-search,
  i.zmdi.zmdi-search,
  .btn-options,
  .dropdown-toggle,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }
  html, body {
    margin: 0; padding: 0;
    background: var(--primary-bg);
    color: var(--text-light);
    font-family: 'RobotoCondensed', sans-serif;
    overflow-x: hidden;
  }
  .dashboard-contentPage {
    position: relative; z-index: 1;
    margin-left: 140px;
    padding: 0 20px auto;
    min-height: 100vh;
    box-sizing: border-box;
  }
  .btn-back-cursos {
    background: var(--primary-accent) !important;
    color: var(--primary-bg) !important;
    border: none !important;
    margin-bottom: 15px;
    padding: .5rem 1rem;
    border-radius: .3rem;
    text-decoration: none;
    display: inline-block;
    transition: background .3s;
  }
  .btn-back-cursos:hover {
    background: var(--hover-accent) !important;
  }
  .page-header h1 {
    font-size: 2rem;
    color: var(--primary-accent);
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
    margin-bottom: .5rem;
    text-align: center;
  }
  .lead {
    text-align: center;
    color: rgba(255,255,255,0.7);
    margin-bottom: 2rem;
    font-size: 1.1rem;
  }
  .panel {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    overflow: hidden;
    max-width: 900px;
    margin: 0 auto 2rem;
  }
  .panel-heading {
    background: var(--primary-accent) !important;
    color: var(--primary-bg) !important;
    padding: 1rem;
    font-size: 1.1rem;
    font-weight: bold;
    text-align: center;
  }
  .panel-body {
    padding: 1.5rem;
  }
  .table-responsive {
    border-radius: .75rem;
    overflow: hidden;
  }
  .table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
  }
  .table th, .table td {
    padding: .75rem 1rem;
    border: 1px solid rgba(255,255,255,0.2);
    text-align: center;
    color: var(--text-light);
    vertical-align: middle;
    background: transparent;
  }
  .table thead th {
    background: var(--primary-bg);
    color: var(--primary-accent);
  }
  .table-hover tbody tr:hover {
    background: rgba(255,255,255,0.05);
  }
  .label-success {
    background: #4caf50;
    padding: .3rem .6rem;
    border-radius: .3rem;
  }
  .label-danger {
    background: #f44336;
    padding: .3rem .6rem;
    border-radius: .3rem;
  }
  .label-warning {
    background: #ff9800;
    padding: .3rem .6rem;
    border-radius: .3rem;
  }
  @media (max-width: 768px) {
    .dashboard-contentPage { margin-left: 0; padding: 1rem; }
    .dashboard-banner { left: 0; width: 100%; }
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1>
        <i class="zmdi zmdi-time"></i>
        Mis Asistencias de “<?= htmlspecialchars($cursoNombre) ?>”
      </h1>
      <p class="lead">
        Aquí puedes revisar todas tus asistencias registradas para este curso.
      </p>
    </div>

    <a href="<?= SERVERURL ?>miscursos/" class="btn-back-cursos btn-sm">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Mis Cursos
    </a>

    <div class="panel">
      <div class="panel-heading">
        <i class="zmdi zmdi-format-list-bulleted"></i> Historial de Asistencias
      </div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Sesión</th>
                <th>Fecha</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($records)): $i=1; foreach ($records as $row): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= htmlspecialchars($row['Sesion']) ?></td>
                  <td><?= htmlspecialchars($row['Fecha']) ?></td>
                  <td>
                    <?php
                      $st = $row['Estado'];
                      $cls = $st==='presente' ? 'success'
                           : ($st==='ausente' ? 'danger' : 'warning');
                    ?>
                    <span class="label label-<?= $cls ?>">
                      <?= ucfirst($st) ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; else: ?>
                <tr>
                  <td colspan="4">No se encontraron registros de asistencia para este curso.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
