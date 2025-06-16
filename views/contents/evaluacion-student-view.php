<?php
// views/contents/evaluacion-student-view.php

// 1) Sólo usuarios autenticados como Estudiante
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'Estudiante') {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

// 2) Controladores necesarios
require_once __DIR__ . '/../../controllers/sesionController.php';
require_once __DIR__ . '/../../controllers/evaluacionController.php';

$insSesion     = new sesionController();
$insEvaluacion = new evaluacionController();
$userCode      = $_SESSION['userKey'] ?? $_SESSION['userCode'] ?? '';

// 3) ID de sesión por URL: /evaluacion-student/{sesionId}/
$parts    = explode("/", trim($_GET['views'], "/"));
$sesionId = intval($parts[1] ?? 0);

// 4) Obtener datos de la sesión para título
$dataSesion = $insSesion->get_sesion_by_id_controller($sesionId);
if (!$dataSesion || $dataSesion->rowCount() === 0) {
    echo '<div class="alert alert-danger">Sesión no encontrada.</div>';
    return;
}
$sesion = $dataSesion->fetch(PDO::FETCH_ASSOC);

// 5) Conexión PDO para notas
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error al conectar con la base de datos.</div>';
    exit;
}

// 6) Listar evaluaciones de la sesión
$evaluaciones = $insEvaluacion->list_evaluaciones_by_sesion_controller($sesionId);

// Momento actual
$now = date('Y-m-d H:i:s');
?>

<style>
:root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(174,12,12,0.61);
    --text-light:       #FFFFFF;
    --hover-accent:     rgba(209,177,110,0.2);
}

html, body {
    margin: 0; padding: 0;
    background: var(--primary-bg);
    color: var(--text-light);
    width: 100%; height: 100%;
    overflow-x: hidden;
    font-family: 'RobotoCondensed',sans-serif;
}

/* -------- fondo de logo -------- */
.dashboard-banner {
    position: fixed;
    top: 0; left: 270px;
    width: calc(100% - 270px); height: 100%;
    background: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
    opacity: 0.05;
    pointer-events: none;
    z-index: 0;
}

/* contenedor principal */
.dashboard-contentPage {
    position: relative; z-index: 1;
    margin-left: 180px;
    width: calc(100% - 270px);
    padding: 0 30px auto;
    min-height: 100vh;
    box-sizing: border-box;
}

.btn-options,
.dropdown-toggle,
.btn-search,
i.zmdi.zmdi-search,
.zmdi-more-vert,
.btn-menu-dashboard {
    display: none !important;
}

.btn-back-home {
    display: inline-block;
    background: var(--primary-accent) !important;
    color: var(--text-light) !important;
    border: none !important;
    border-radius: .3rem;
    padding: .5rem 1rem;
    margin-bottom: 1.5rem;
    font-size: .9rem;
    text-decoration: none;
    transition: background .3s;
}
.btn-back-home:hover {
    background: var(--hover-accent) !important;
    text-decoration: none;
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
    font-size: 1.1rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: 2rem;
    max-width: 800px;
    margin: 0 auto 2rem;
}

.lista-evaluaciones table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2rem;
}
.lista-evaluaciones th{

    color:rgb(0, 0, 0);
}
.lista-evaluaciones td {
    padding: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    text-align: left;
    color: var(--text-light);
}
.lista-evaluaciones th {
    background: var(--primary-accent);
}
.lista-evaluaciones tbody tr:nth-child(even) {
    background: rgba(255,255,255,0.05);
}
.lista-evaluaciones table tbody tr:hover {
    background: transparent !important;
}

.btn-action {
    background: var(--primary-accent);
    color: var(--text-light);
    border: none;
    border-radius: .3rem;
    padding: .3rem .6rem;
    font-size: .8rem;
    transition: background .3s;
    text-decoration: none;
}
.btn-action:hover {
    background: var(--hover-accent);
}
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <p class="text-center">
      <a href="<?= SERVERURL ?>sesion/<?= htmlspecialchars($sesion['CursoId']) ?>/" class="btn btn-back-home">
        <i class="zmdi zmdi-arrow-left"></i> Volver a Sesiones
      </a>
    </p>
  </div>

  <div class="page-header">
    <h1><i class="zmdi zmdi-assignment"></i> Evaluaciones de la sesión: <?= htmlspecialchars($sesion['Titulo']) ?></h1>
  </div>
  <p class="lead">Fecha: <?= date('d/m/Y', strtotime($sesion['Fecha'])) ?></p>

  <div class="container-fluid lista-evaluaciones">
    <?php if (!empty($evaluaciones)): ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Título</th>
              <th>Inicio</th>
              <th>Cierre</th>
              <th>Estado</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($evaluaciones as $ev):
              $fi = $ev['FechaInicio'];
              $fc = $ev['FechaCierre'];

              // ¿Ya la rindió?
              $chkNota = $pdo->prepare(
                "SELECT Nota FROM resultado 
                 WHERE EvaluacionId = ? 
                   AND EstudianteCodigo = ?"
              );
              $chkNota->execute([$ev['id'], $userCode]);
              $yaTomada = $chkNota->rowCount() > 0;
              $notaAlu  = $yaTomada 
                         ? (float)$chkNota->fetch(PDO::FETCH_ASSOC)['Nota'] 
                         : null;

              // Determinar estado
              if ($yaTomada) {
                $esAprobado = is_numeric($notaAlu) && $notaAlu >= 11;
                $estado = sprintf(
                  'Calificada (%.2f%%) / %s',
                  $notaAlu,
                  $esAprobado ? 'Aprobado' : 'Desaprobado'
                );
              } elseif ($now < $fi) {
                $estado = 'No iniciada';
              } elseif ($now > $fc) {
                $estado = 'Expirada';
              } else {
                $estado = 'Disponible';
              }
            ?>
              <tr>
                <td><?= htmlspecialchars($ev['Titulo']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($fi)) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($fc)) ?></td>
                <td><?= htmlspecialchars($estado) ?></td>
                <td>
                  <?php if ($estado === 'Disponible'): ?>
                    <button class="btn btn-action btn-xs"
                            onclick="window.open(
                              '<?= SERVERURL . "evaluacion-student-resolver/{$sesionId}/{$ev['id']}/" ?>',
                              '_blank','width=800,height=600'
                            );">
                      Iniciar
                    </button>
                  <?php elseif ($yaTomada): ?>
                    <button class="btn btn-action btn-xs"
                            onclick="window.open(
                              '<?= SERVERURL . "evaluacion-student-detalle/{$sesionId}/{$ev['id']}/" ?>',
                              '_blank','width=800,height=600'
                            );">
                      Ver detalles
                    </button>
                  <?php else: ?>
                    &mdash;
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-center" style="color:rgba(255,255,255,0.7);">
        No hay evaluaciones programadas.
      </p>
    <?php endif; ?>
  </div>
</section>
