<?php
// views/contents/reportenotas-view.php

// 1) Control de acceso: Solo Docente o Admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/cursoController.php';
require_once __DIR__ . '/../../controllers/sesionController.php';
require_once __DIR__ . '/../../controllers/notaController.php';

$cc = new cursoController();
$sc = new sesionController();
$nc = new notaController();

// 2) Extraemos el ID de curso de la URL: “/reportenotas/{cursoId}/”
$parts   = explode('/', trim($_GET['views'],'/'));
$cursoId = intval($parts[1] ?? 0);

// 3) Obtenemos datos del curso
$curso = $cc->get_curso_by_id_controller($cursoId);
if ($curso === null) {
    echo '<div class="alert alert-danger text-center">Curso no encontrado.</div>';
    return;
}

// 4) Listamos sesiones de ese curso
$sesiones = $sc->list_sesiones_controller($cursoId);

// 5) Listamos estudiantes inscritos en ese curso
$alumnos = $cc->list_estudiantes_por_curso_controller($cursoId);

// 6) Procesar POST: guardar todas las notas enviadas
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notas'])) {
    foreach ($_POST['notas'] as $estCodigo => $arrayNotas) {
        foreach ($arrayNotas as $sesId => $valorNota) {
            $sesIdInt  = intval($sesId);
            $notaFloat = floatval(number_format(floatval($valorNota), 2, '.', ''));
            $alert     = $nc->save_nota_controller($cursoId, $sesIdInt, $estCodigo, $notaFloat);
        }
    }
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}

// 7) Precargar notas existentes
$notasExistentes = [];
foreach ($alumnos as $al) {
    $estCodigo = $al['Codigo'];
    foreach ($sesiones as $ses) {
        $sesId    = intval($ses['id']);
        $filaNota = $nc->get_nota_by_sesion_estudiante_controller($sesId, $estCodigo);
        $notasExistentes[$estCodigo][$sesId] = $filaNota !== null
            ? floatval($filaNota['Nota'])
            : '';
    }
}
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
  /* Logo de fondo */
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
  /* Ocultar buscador y menús extra */
  .btn-search, i.zmdi.zmdi-search,
  .btn-options, .dropdown-toggle,
  .zmdi-more-vert, .btn-menu-dashboard {
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
    margin-left: 145px;
    padding: auto;
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
    margin-bottom: 1rem;
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
  .notas-table input[type="number"] {
    width: 60px;
    background: rgba(255,255,255,0.05);
    border: 1px solid #555;
    color: var(--text-light);
    text-align: center;
    padding: 6px;
    border-radius: .3rem;
  }
  .notas-table input[type="number"]:focus {
    border-color: var(--primary-accent);
    box-shadow: 0 0 5px rgba(209,177,110,0.5);
    outline: none;
  }
  .promedio-cell {
    font-weight: bold;
    color: #ffeb3b;
    background: var(--primary-bg);
  }
  .btn-guardar {
    background: var(--primary-accent);
    color: var(--primary-bg);
    border: none;
    border-radius: .3rem;
    padding: .6rem 1.2rem;
    font-size: 1rem;
    transition: background .3s;
  }
  .btn-guardar:hover {
    background: var(--hover-accent);
  }
  @media (max-width: 768px) {
    .dashboard-contentPage { margin-left: 0; padding: 1rem; }
    .dashboard-banner { left: 0; width: 100%; }
    .notas-table { min-width: auto; }
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <a href="<?= SERVERURL ?>miscursos/" class="btn-back-home">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Mis Cursos
    </a>
    <div class="page-header">
      <h1><i class="zmdi zmdi-chart"></i> Reporte de Notas – <?= htmlspecialchars($curso['Nombre']) ?></h1>
      <p>Registra aquí la nota (0–20) de cada estudiante por sesión. El promedio se calculará automáticamente.</p>
      <?= $alert; ?>
    </div>
  </div>

  <div class="container-fluid">
    <?php if (empty($sesiones)): ?>
      <div class="panel"><div class="panel-heading">Atención</div>
        <div class="panel-body text-center">
          No hay sesiones para este curso. Crea sesiones primero.
        </div>
      </div>
    <?php elseif (empty($alumnos)): ?>
      <div class="panel"><div class="panel-heading">Atención</div>
        <div class="panel-body text-center">
          No hay estudiantes inscritos en este curso.
        </div>
      </div>
    <?php else: ?>
      <form method="POST" autocomplete="off">
        <div class="panel">
          <div class="panel-heading">
            <i class="zmdi zmdi-edit"></i> Ingresar / Actualizar Notas
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="notas-table">
                <thead>
                  <tr>
                    <th>Estudiante</th>
                    <?php foreach ($sesiones as $ses): ?>
                      <th>
                        <?= htmlspecialchars($ses['Titulo']) ?><br>
                        <small><?= date("d/m/Y", strtotime($ses['Fecha'])) ?></small>
                      </th>
                    <?php endforeach; ?>
                    <th>Promedio</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($alumnos as $al): 
                    $estCod   = $al['Codigo'];
                    $sumNotas = 0; $count = count($sesiones);
                  ?>
                    <tr>
                      <td style="text-align:left;">
                        <?= htmlspecialchars("{$al['Nombres']} {$al['Apellidos']}") ?>
                      </td>
                      <?php foreach ($sesiones as $ses): 
                        $sesId    = intval($ses['id']);
                        $val      = $notasExistentes[$estCod][$sesId] ?? '';
                        $sumNotas += is_numeric($val) ? $val : 0;
                      ?>
                        <td>
                          <input type="number"
                                 name="notas[<?= $estCod ?>][<?= $sesId ?>]"
                                 step="0.01" min="0" max="20"
                                 value="<?= $val ?>" placeholder="—">
                        </td>
                      <?php endforeach;
                        $prom = $count>0 ? round($sumNotas/$count,2) : 0;
                      ?>
                      <td class="promedio-cell"><?= number_format($prom,2,'.','') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <p class="text-center">
              <button type="submit" class="btn-guardar">
                <i class="zmdi zmdi-floppy"></i> Guardar Notas
              </button>
            </p>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div>
</section>
