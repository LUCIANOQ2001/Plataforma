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
    // $_POST['notas'][ <EstudianteCodigo> ][ <SesionId> ] = <nota>
    foreach ($_POST['notas'] as $estCodigo => $arrayNotas) {
        foreach ($arrayNotas as $sesId => $valorNota) {
            $sesIdInt   = intval($sesId);
            $notaFloat  = floatval(number_format(floatval($valorNota), 2, '.', ''));
            // Guardar (insertar/actualizar) en tabla nota_sesion
            $alert = $nc->save_nota_controller($cursoId, $sesIdInt, $estCodigo, $notaFloat);
        }
    }
    // Redirect after POST
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}

// 7) Precargar valores existentes en $notasExistentes[<EstCodigo>][<SesId>] = <nota>
$notasExistentes = [];
foreach ($alumnos as $al) {
    $estCodigo = $al['Codigo'];
    foreach ($sesiones as $ses) {
        $sesId = intval($ses['id']);
        $filaNota = $nc->get_nota_by_sesion_estudiante_controller($sesId, $estCodigo);
        if ($filaNota !== null) {
            $notasExistentes[$estCodigo][$sesId] = floatval($filaNota['Nota']);
        } else {
            $notasExistentes[$estCodigo][$sesId] = '';
        }
    }
}
?>
<style>
  /* ------------------------------------ */
  /* Ocultar íconos de “tres puntos” y “lupa” */
  /* ------------------------------------ */

  /* Si los tres puntos usan la clase .btn-options o .dropdown-toggle: */
  .btn-options,
  .dropdown-toggle {
    display: none !important;
  }

  /* Si la lupa tiene la clase .btn-search o un <i class="zmdi zmdi-search"> */
  .btn-search,
  i.zmdi.zmdi-search {
    display: none !important;
  }

  /* ------------------------------------ */
  /* El resto de tu CSS habitual queda abajo */
  /* ------------------------------------ */

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
    background: #43a047 !important;
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

  .notas-table input[type="number"] {
    width: 60px;
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid #555;
    color: #fff;
    text-align: center;
    padding: 6px;
    border-radius: 4px;
  }

  .notas-table input[type="number"]:focus {
    border-color: #00e5ff;
    box-shadow: 0 0 5px rgba(0, 229, 255, 0.5);
    outline: none;
  }

  .promedio-cell {
    font-weight: bold;
    color: #ffeb3b;
    background: #2a2c3b;
  }

  .btn-guardar {
    background-color: #03a9f4;
    border-color: #0288d1;
    color: #fff;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 1rem;
    text-shadow: 1px 1px 6px #000;
    margin-top: 15px;
  }
  .btn-guardar:hover {
    background-color: #0288d1;
  }

  @media (max-width: 768px) {
    .notas-table th,
    .notas-table td {
      padding: 8px;
      font-size: 0.9rem;
    }
    .notas-table input[type="number"] {
      width: 50px;
    }
    .dashboard-contentPage {
      max-width: 100%;
      margin-left: 170px;
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
      <h1><i class="zmdi zmdi-chart"></i> Reporte de Notas – <?= htmlspecialchars($curso['Nombre']) ?></h1>
      <p>
        Registra aquí la nota (0–20) de cada estudiante por cada sesión del curso.
        El promedio (sobre 20) se calculará automáticamente.
      </p>
      <?= $alert; ?>
    </div>
  </div>

  <div class="container-fluid">
    <?php if (empty($sesiones)): ?>
      <div class="panel panel-info">
        <div class="panel-heading">Atención</div>
        <div class="panel-body text-center">
          No hay sesiones registradas para este curso.<br>
          Primero crea sesiones desde la sección “Sesiones”.
        </div>
      </div>
    <?php elseif (empty($alumnos)): ?>
      <div class="panel panel-info">
        <div class="panel-heading">Atención</div>
        <div class="panel-body text-center">
          No hay estudiantes inscritos en este curso.<br>
          Asegúrate de que haya alumnos antes de registrar notas.
        </div>
      </div>
    <?php else: ?>
      <form method="POST" autocomplete="off">
        <div class="panel panel-info">
          <div class="panel-heading">
            <i class="zmdi zmdi-edit"></i> Ingresar / Actualizar Notas (0–20)
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="notas-table">
                <thead>
                  <tr>
                    <th>Estudiante</th>
                    <?php foreach ($sesiones as $ses): ?>
                      <th>
                        <?= htmlspecialchars($ses['Titulo']); ?><br>
                        <small><?= date("d/m/Y", strtotime($ses['Fecha'])); ?></small>
                      </th>
                    <?php endforeach; ?>
                    <th>Promedio<br>(0–20)</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($alumnos as $al): ?>
                    <?php
                      $estCod    = $al['Codigo'];
                      $sumaNotas = 0.0;
                      $countSes  = count($sesiones);
                    ?>
                    <tr>
                      <td style="text-align: left;">
                        <?= htmlspecialchars($al['Nombres'] . ' ' . $al['Apellidos']); ?>
                      </td>
                      <?php foreach ($sesiones as $ses): ?>
                        <?php
                          $sesId        = intval($ses['id']);
                          $valorPrefill = $notasExistentes[$estCod][$sesId] ?? '';
                          $sumaNotas   += is_numeric($valorPrefill) ? floatval($valorPrefill) : 0;
                        ?>
                        <td>
                          <input
                            type="number"
                            name="notas[<?= $estCod ?>][<?= $sesId ?>]"
                            step="0.01"
                            min="0"
                            max="20"
                            value="<?= $valorPrefill ?>"
                            placeholder="---"
                          >
                        </td>
                      <?php endforeach; ?>
                      <?php
                        $promedio = ($countSes > 0)
                                    ? round($sumaNotas / $countSes, 2)
                                    : 0.00;
                      ?>
                      <td class="promedio-cell">
                        <?= number_format($promedio, 2, '.', ''); ?>
                      </td>
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
