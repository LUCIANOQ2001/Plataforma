<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "./controllers/studentController.php";
require_once "./controllers/adminController.php";
require_once "./controllers/cursoController.php";

// Instancias
$insEstudiante = new studentController();
$insDocente    = new adminController();
$insCurso      = new cursoController();

// Datos comunes
$totalEstudiantes = $insEstudiante->count_estudiantes();
$totalDocentes    = $insDocente->count_docentes();
$totalCursos      = $insCurso->count_cursos();

// Datos específicos de Docente
$isDocente = ($_SESSION['userType'] === 'Docente');
if($isDocente){
    $docCodigo        = $_SESSION['userKey'];
    $misCursos        = $insCurso->list_mis_cursos_controller($docCodigo);
    $totalAlumnos     = $insCurso->count_students_by_docente_controller($docCodigo);
    $sesionesPorCurso = $insCurso->sessions_count_by_docente_controller($docCodigo);
}
?>
<style>
  /* === Paleta oscura con acentos dorados === */
  :root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(88, 0, 0, 0.76);
    --text-light:       #FFF;
    --hover-accent:     rgba(7, 5, 0, 0.2);
  }
  body, html {
    margin:0; padding:0;
    background: var(--primary-bg);
    color: var(--text-light);
    font-family:'RobotoCondensed',sans-serif;
  }
  .dashboard-container {
    margin-left: 270px;
    padding: 20px;
    min-height: 100vh;
    background-color: var(--primary-bg);
    background-image: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png');
    background-repeat: no-repeat;
    background-position: center;
    background-size: 60%;
    background-blend-mode: overlay;
    overflow-x: hidden;
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
  .dashboard-header h1 {
    text-align: center;
    font-size: 2rem;
    color: rgb(233,18,18);
    margin-bottom: 2.5rem;
    text-shadow:2px 2px 6px rgba(0,0,0,0.7);
  }
  .stats-row {
    display: flex; flex-wrap: wrap;
    gap: 1.5rem; justify-content: center;
    margin-bottom: 3rem;
  }
  .stats-card {
    flex:1 1 200px; max-width:280px;
    background: var(--secondary-bg);
    border:1px solid var(--primary-accent);
    border-radius:1rem;
    box-shadow:0 4px 12px rgba(0,0,0,0.4);
    padding:1.5rem; text-align:center;
    transition:transform .3s;
  }
  .stats-card:hover { transform: translateY(-4px); }
  .stats-icon { font-size:2.5rem; color:var(--primary-accent); margin-bottom:.5rem; }
  .stats-title{ font-size:1.1rem; color:var(--primary-accent); font-weight:bold; }
  .stats-number{ font-size:2rem; color:var(--primary-accent); margin:.5rem 0; }
  .stats-desc  { font-size:.9rem; color:rgba(255,255,255,0.7); }

  /* Contenedor para gráfico + detalle */
  .dashboard-content {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
    justify-content: center;
    margin-bottom: 3rem;
  }
  .left-col, .right-col {
    background: var(--secondary-bg);
    border:1px solid var(--primary-accent);
    border-radius:1rem;
    box-shadow:0 4px 12px rgba(0,0,0,0.4);
    padding:1.5rem;
    flex:1 1 350px;
    max-width: 600px;
  }

  /* Ajuste del gráfico */
  .left-col canvas {
    max-width: 100%;
    max-height: 400px;
  }

  /* Tabla de detalle de cursos */
  .detail-table table {
    width:100%; border-collapse:collapse;
  }
  .detail-table th,
  .detail-table td {
    padding:0.75rem; text-align:center;
    border-bottom:1px solid rgba(255,255,255,0.2);
    color: var(--text-light);
  }
  .detail-table th {
    background: rgba(209,177,110,0.2);
    color: var(--primary-accent);
  }
</style>

<div class="dashboard-container">
  <header class="dashboard-header">
    <h1>
      <i class="zmdi zmdi-graduation-cap zmdi-hc-fw"></i>
      Aula Virtual CIP Lambayeque
    </h1>
  </header>

  <?php if(!$isDocente): ?>
    <!-- VISTA ADMINISTRADOR -->
    <div class="stats-row">
      <div class="stats-card">
        <div class="stats-icon"><i class="zmdi zmdi-accounts"></i></div>
        <div class="stats-title">Estudiantes</div>
        <div class="stats-number"><?= $totalEstudiantes ?></div>
        <div class="stats-desc">Registrados en la plataforma</div>
      </div>
      <div class="stats-card">
        <div class="stats-icon"><i class="zmdi zmdi-account-box-mail"></i></div>
        <div class="stats-title">Docentes</div>
        <div class="stats-number"><?= $totalDocentes ?></div>
        <div class="stats-desc">Activos en el sistema</div>
      </div>
      <div class="stats-card">
        <div class="stats-icon"><i class="zmdi zmdi-book"></i></div>
        <div class="stats-title">Cursos</div>
        <div class="stats-number"><?= $totalCursos ?></div>
        <div class="stats-desc">Creados en la plataforma</div>
      </div>
    </div>
  <?php else: ?>
    <!-- VISTA DOCENTE -->
    <div class="stats-row">
      <div class="stats-card">
        <div class="stats-icon"><i class="zmdi zmdi-book"></i></div>
        <div class="stats-title">Mis Cursos</div>
        <div class="stats-number"><?= count($misCursos) ?></div>
        <div class="stats-desc">Cursos a mi cargo</div>
      </div>
      <div class="stats-card">
        <div class="stats-icon"><i class="zmdi zmdi-accounts"></i></div>
        <div class="stats-title">Mis Alumnos</div>
        <div class="stats-number"><?= $totalAlumnos ?></div>
        <div class="stats-desc">Estudiantes matriculados</div>
      </div>
    </div>

    <div class="dashboard-content">
      <!-- Columna izquierda: Gráfico -->
      <div class="left-col">
        <h2 class="stats-title" style="margin-bottom:1rem;">Sesiones por Curso</h2>
        <canvas id="sessionsChart"></canvas>
      </div>

      <!-- Columna derecha: Detalle de cursos -->
      <div class="right-col detail-table">
        <h2 class="stats-title" style="margin-bottom:1rem;">Detalle de Mis Cursos</h2>
        <table>
          <thead>
            <tr>
              <th>Curso</th>
              <th>Estudiantes</th>
              <th>Sesiones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sessMap = [];
            foreach($sesionesPorCurso as $sp){
              $sessMap[$sp['Nombre']] = $sp['Sesiones'];
            }
            foreach($misCursos as $c):
              $estCount  = count($insCurso->list_estudiantes_por_curso_controller($c['id']));
              $sessCount = $sessMap[$c['Nombre']] ?? 0;
            ?>
            <tr>
              <td><?= htmlspecialchars($c['Nombre']) ?></td>
              <td><?= $estCount ?></td>
              <td><?= $sessCount ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Chart.js desde CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      const labels = <?= json_encode(array_column($sesionesPorCurso, 'Nombre')) ?>;
      const datos  = <?= json_encode(array_map('intval', array_column($sesionesPorCurso, 'Sesiones'))) ?>;
      new Chart(
        document.getElementById('sessionsChart').getContext('2d'),
        {
          type: 'bar',
          data: {
            labels,
            datasets: [{
              label: 'Sesiones',
              data: datos,
              backgroundColor: 'rgba(129,96,27,0.6)',
              borderColor: 'rgb(227,161,18)',
              borderWidth: 2
            }]
          },
          options: {
            scales: {
              y: { beginAtZero:true, ticks:{ stepSize:1 } }
            },
            plugins: { legend:{ display:false } }
          }
        }
      );
    </script>
  <?php endif; ?>
</div>
