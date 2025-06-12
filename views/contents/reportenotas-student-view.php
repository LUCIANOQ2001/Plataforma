<?php
// views/contents/reportenotas-student-view.php

// 1) Control de acceso: Solo Estudiante
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/cursoController.php';
require_once __DIR__ . '/../../controllers/sesionController.php';
require_once __DIR__ . '/../../controllers/notaController.php';

$cc = new cursoController();
$sc = new sesionController();
$nc = new notaController();

// 2) Extraemos el ID de curso de la URL: “/reportenotas-student/{cursoId}/”
$parts   = explode('/', trim($_GET['views'],'/'));
$cursoId = intval($parts[1] ?? 0);

// 3) Obtenemos datos del curso
$curso = $cc->get_curso_by_id_controller($cursoId);
if ($curso === null) {
    echo '<div class="alert alert-danger text-center">Curso no encontrado.</div>';
    return;
}

// 4) Obtenemos todas las sesiones de este curso
$sesiones = $sc->list_sesiones_controller($cursoId);

// 5) Identificamos el código del estudiante logueado
$estudianteCodigo = $_SESSION['userKey'] ?? '';
if (empty($estudianteCodigo)) {
    echo '<div class="alert alert-danger text-center">Acceso inválido.</div>';
    return;
}

// 6) Para cada sesión, obtenemos la nota de este estudiante
$notasEstudiante = [];
foreach ($sesiones as $ses) {
    $sesId    = intval($ses['id']);
    $filaNota = $nc->get_nota_by_sesion_estudiante_controller($sesId, $estudianteCodigo);
    $notasEstudiante[$sesId] = $filaNota !== null
        ? floatval($filaNota['Nota'])
        : null;
}

// 7) Calculamos promedio
$totalSesiones = count($sesiones);
$sumaNotas     = 0.0;
foreach ($sesiones as $ses) {
    $n = $notasEstudiante[intval($ses['id'])];
    if (is_numeric($n)) {
        $sumaNotas += $n;
    }
}
$promedio = $totalSesiones > 0
    ? round($sumaNotas / $totalSesiones, 2)
    : 0.00;
?>
<style>
  /* ==== Paleta de colores ==== */
  :root {
    --primary-bg:     #2B2B2B;
    --primary-accent: #D1B16E;
    --secondary-bg:   rgba(174,12,12,0.61);
    --text-light:     #FFFFFF;
    --hover-accent:   rgba(209,177,110,0.2);
  }
  /* Banner con logo de fondo */
  .dashboard-banner {
    position: fixed;
    top: 0; left: 150px;
    width: calc(100% - 150px);
    height: 100%;
    background: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
    opacity: 0.05;
    pointer-events: none;
    z-index: 0;
  }
  /* Ocultar buscador y menú de tres puntos */
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
    width: 100%; height: 100%;
    overflow-x: hidden;
    font-family: 'RobotoCondensed', sans-serif;
    box-sizing: border-box;
  }
  .dashboard-contentPage {
    position: relative; z-index: 1;
    margin-left: 150px;
    padding: auto ;
    min-height: 100vh;
    box-sizing: border-box;
    width: 90%;
  }
  .btn-back-home {
    background: var(--primary-accent) !important;
    color: var(--primary-bg) !important;
    border: none !important;
    border-radius: .3rem;
    padding: .5rem 1rem;
    text-decoration: none;
    display: inline-block;
    margin-bottom: 20px;
    transition: background .3s;
  }
  .btn-back-home:hover {
    background: var(--hover-accent) !important;
  }
  .page-header h1 {
    font-size: 2rem;
    color: var(--primary-accent);
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
    margin-bottom: .5rem;
    text-align: center;
  }
  .page-header p {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.7);
    text-align: center;
    margin-bottom: 2rem;
  }
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
    color: var(--primary-bg) !important;
    padding: 1rem;
    font-size: 1.2rem;
    font-weight: bold;
    text-align: center;
  }
  .panel-body {
    padding: 1.5rem;
  }
  .table-responsive {
    overflow-x: auto;
    margin-bottom: 1rem;
    border-radius: .75rem;
  }
  .notas-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
  }
  .notas-table th,
  .notas-table td {
    padding: .75rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    text-align: center;
    color: var(--text-light);
    white-space: nowrap;
  }
  .notas-table thead th {
    background: var(--primary-bg);
    color: var(--primary-accent);
  }
  .promedio-cell {
    font-weight: bold;
    color: #ffeb3b;
    background: var(--primary-bg);
  }
  @media (max-width: 768px) {
    .dashboard-contentPage {
      margin-left: 0;
      padding: 1rem;
    }
    .dashboard-banner {
      left: 0;
      width: 100%;
    }
    .notas-table {
      min-width: auto;
    }
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <!-- Botón Volver a Mis Cursos -->
    <a href="<?= SERVERURL ?>miscursos/" class="btn-back-home">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Mis Cursos
    </a>

    <div class="page-header">
      <h1>
        <i class="zmdi zmdi-chart"></i>
        Mis Notas – <?= htmlspecialchars($curso['Nombre']) ?>
      </h1>
      <p>
        Aquí ves tus calificaciones en cada sesión de este curso,<br>
        junto con el promedio final.
      </p>
    </div>
  </div>

  <div class="container-fluid">
    <?php if (empty($sesiones)): ?>
      <div class="panel">
        <div class="panel-heading">Atención</div>
        <div class="panel-body text-center">
          Aún no se han creado sesiones para este curso.<br>
          Cuando el docente agregue sesiones, podrás ver tus notas aquí.
        </div>
      </div>
    <?php else: ?>
      <div class="panel">
        <div class="panel-heading">
          <i class="zmdi zmdi-view-list"></i> Tus Notas por Sesión
        </div>
        <div class="panel-body">
          <div class="table-responsive">
            <table class="notas-table">
              <thead>
                <tr>
                  <th>Sesión</th>
                  <th>Fecha</th>
                  <th>Nota (0–20)</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($sesiones as $ses): 
                  $sesId     = intval($ses['id']);
                  $titulo    = htmlspecialchars($ses['Titulo']);
                  $fecha     = date("d/m/Y", strtotime($ses['Fecha']));
                  $nota      = $notasEstudiante[$sesId];
                  $textoNota = is_numeric($nota) ? number_format($nota,2,'.','') : '—';
                ?>
                  <tr>
                    <td><?= $titulo ?></td>
                    <td><?= $fecha ?></td>
                    <td><?= $textoNota ?></td>
                  </tr>
                <?php endforeach; ?>
                <tr>
                  <td colspan="2" style="text-align:right;font-weight:bold;">
                    Promedio (0–20):
                  </td>
                  <td class="promedio-cell">
                    <?= number_format($promedio,2,'.','') ?>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
