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
//    Asumimos que en login guardas el "Código" del estudiante en $_SESSION['userKey']
$estudianteCodigo = $_SESSION['userKey'] ?? '';

// Si por alguna razón 'userKey' no existiera o no fuese válido:
if (empty($estudianteCodigo)) {
    echo '<div class="alert alert-danger text-center">Acceso inválido.</div>';
    return;
}

// 6) Para cada sesión, obtenemos la nota de este estudiante
$notasEstudiante = [];
foreach ($sesiones as $ses) {
    $sesId      = intval($ses['id']);
    $filaNota   = $nc->get_nota_by_sesion_estudiante_controller($sesId, $estudianteCodigo);
    if ($filaNota !== null) {
        $notasEstudiante[$sesId] = floatval($filaNota['Nota']);
    } else {
        $notasEstudiante[$sesId] = null;
    }
}

// 7) Calculamos promedio sobre las sesiones existentes
$totalSesiones = count($sesiones);
$sumaNotas     = 0.0;
$contadorNotas = 0;
foreach ($sesiones as $ses) {
    $sesId = intval($ses['id']);
    $n = $notasEstudiante[$sesId];
    if (is_numeric($n)) {
        $sumaNotas += $n;
        $contadorNotas++;
    }
}
$promedio = ($totalSesiones > 0 && $contadorNotas > 0)
    ? round($sumaNotas / $totalSesiones, 2)
    : 0.00;
?>
<style>
    
  /* Si la lupa tiene la clase .btn-search o un <i class="zmdi zmdi-search"> */
  .btn-search,
  i.zmdi.zmdi-search {
    display: none !important;
  }
  html, body {
    margin: 0;
    padding: 0;
    background-color: #1e1f28;
    color: #fff;
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
  }

  .dashboard-contentPage {
    margin-left: 150px;
    padding: 30px;
    background-color: #1e1f28;
    min-height: 100vh;
    box-sizing: border-box;

    /* Para restringir ancho y centrar, si lo deseas */
    max-width: 1350px;
    margin-right: auto;
 
  }
  .page-header h1 {
    font-size: 28px;
    color: #00e5ff;
    text-shadow: 1px 1px 6px #000;
    margin-bottom: 10px;
  }

  .page-header p {
    font-size: 1.1rem;
    color: #ccc;
    margin-bottom: 20px;
  }

  .btn-back-home {
    background-color: #607d8b !important;
    border-color:     #455a64 !important;
    color:            #fff !important;
    margin-bottom: 20px;
    padding: 10px 15px;
    text-decoration: none;
    display: inline-block;
  }
  .btn-back-home i {
    margin-right: 6px;
  }

  .panel {
    background: #2c2d3f;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
    border: 1px solid #3c3d4f;
    color: #fff;
    margin-bottom: 30px;
  }

  .panel-heading {
    background: #007bff !important;
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

  /* Contenedor que habilita scroll horizontal cuando la tabla es demasiado ancha */
  .table-responsive {
    overflow-x: auto;
    margin-bottom: 15px;
  }

  .notas-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
  }

  .notas-table th,
  .notas-table td {
    padding: 12px;
    border-bottom: 1px solid #444;
    text-align: center;
    color: #fff;
    white-space: nowrap;
  }

  .notas-table thead th {
    background: #333;
  }

  .notas-table td {
    font-size: 1rem;
  }

  .promedio-cell {
    font-weight: bold;
    color: #ffeb3b;
    background: #2a2c3b;
  }

  @media (max-width: 768px) {
    .notas-table th,
    .notas-table td {
      padding: 8px;
      font-size: 0.9rem;
    }
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <!-- Botón “Volver a Mis Cursos” -->
    <a href="<?php echo SERVERURL; ?>miscursos/" class="btn-back-home">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Mis Cursos
    </a>

    <div class="page-header">
      <h1>
        <i class="zmdi zmdi-chart"></i>
        Mis Notas – <?= htmlspecialchars($curso['Nombre']) ?>
      </h1>
      <p>
        Aquí ves tus calificaciones (0–20) en cada sesión de este curso,
        junto con el promedio final (sobre 20).
      </p>
    </div>
  </div>

  <div class="container-fluid">
    <?php if (empty($sesiones)): ?>
      <div class="panel panel-info">
        <div class="panel-heading">Atención</div>
        <div class="panel-body text-center">
          Aún no se han creado sesiones para este curso.<br>
          Cuando el docente agregue sesiones, podrás ver tus notas aquí.
        </div>
      </div>
    <?php else: ?>
      <div class="panel panel-info">
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
                  <th>Nota<br>(0–20)</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($sesiones as $ses): ?>
                  <?php
                    $sesId       = intval($ses['id']);
                    $tituloSes   = htmlspecialchars($ses['Titulo']);
                    $fechaSes    = date("d/m/Y", strtotime($ses['Fecha']));
                    $notaSesion  = $notasEstudiante[$sesId];
                    $textoNota   = is_numeric($notaSesion) ? number_format($notaSesion, 2, '.', '') : '—';
                  ?>
                  <tr>
                    <td><?= $tituloSes ?></td>
                    <td><?= $fechaSes ?></td>
                    <td><?= $textoNota ?></td>
                  </tr>
                <?php endforeach; ?>

                <!-- Fila final: PROMEDIO -->
                <tr>
                  <td colspan="2" style="text-align: right; font-weight: bold;">
                    Promedio (0–20):
                  </td>
                  <td class="promedio-cell">
                    <?= number_format($promedio, 2, '.', '') ?>
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
