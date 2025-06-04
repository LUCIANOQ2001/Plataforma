<?php
// views/contents/evaluacion-student-resolver-view.php

// 1) Sólo Estudiantes pueden ver esta página
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'Estudiante') {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/evaluacionController.php';
$ec = new evaluacionController();

// 2) Extraer IDs de URL: /evaluacion/{sesionId}/estudiante/{evaluacionId}/
$parts = explode('/', trim($_GET['views'], '/'));
$sesionId     = intval($parts[1] ?? 0);
$evaluacionId = intval($parts[3] ?? 0);

// 3) Obtener datos de la evaluación
$evaluacion = $ec->get_evaluacion($evaluacionId);
if (!$evaluacion) {
    echo '<div class="alert alert-danger text-center">Evaluación no encontrada.</div>';
    return;
}

// 4) Verificar fechas: inicio y cierre
$ahora        = new DateTime();
$fechaInicio  = DateTime::createFromFormat('Y-m-d H:i:s', $evaluacion['FechaInicio']);
$fechaCierre  = DateTime::createFromFormat('Y-m-d H:i:s', $evaluacion['FechaCierre']);

if ($ahora < $fechaInicio) {
    echo '<div class="alert alert-info text-center">Esta evaluación comenzará el '
         . $fechaInicio->format('d/m/Y H:i') . '.</div>';
    return;
}
if ($ahora > $fechaCierre) {
    echo '<div class="alert alert-warning text-center">El tiempo para esta evaluación ha finalizado.</div>';
    return;
}

// 5) Contar intentos realizados por el estudiante
$estudianteCodigo = $_SESSION['userCode'] ?? $_SESSION['userKey'];
$intentosHechos   = $ec->count_resultados_by_evaluacion_estudiante($evaluacionId, $estudianteCodigo);
$maxIntentos      = intval($evaluacion['IntentosPermitidos'] ?? 1);

if ($intentosHechos >= $maxIntentos) {
    echo '<div class="alert alert-warning text-center">Has agotado tus ' 
         . $maxIntentos . ' intentos para esta evaluación.</div>';
    return;
}

// 6) Procesar envío de respuestas si es POST
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alert = $ec->submit_respuestas_controller($evaluacionId, $estudianteCodigo, $_POST);
    // Después de enviar, ya no mostrar el formulario
}

// 7) Si ya envió en este intento, mostrar alerta y no el formulario
?>
<style>
  html, body {
    margin: 0; padding: 0;
    background-color: #1e1f28;
    color: #fff;
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
  }
  .dashboard-contentPage {
    margin-left: 170px;
    padding: 30px;
    background-color: #1e1f28;
    min-height: 100vh;
    box-sizing: border-box;
  }
  .page-header h1 {
    font-size: 28px;
    color: #00e5ff;
    text-shadow: 1px 1px 6px #000;
    margin-bottom: 10px;
  }
  .lead {
    font-size: 1.1rem;
    color: #ccc;
    margin-bottom: 20px;
  }
  .panel {
    background: #2c2d3f;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
    border: 1px solid #3c3d4f;
    color: #fff;
    margin-bottom: 20px;
  }
  .panel-heading {
    background: #00bcd4 !important;
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
  .form-control {
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid #555;
    color: #fff;
  }
  .btn-submit {
    background-color: #43a047;
    border-color: #388e3c;
    color: #fff;
  }
  .btn-submit:hover {
    background-color: #4caf50;
  }
  .alert {
    width: 80%;
    margin: 20px auto;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-assignment"></i>
        <?php echo htmlspecialchars($evaluacion['Titulo']); ?>
        <small>(Intento <?php echo ($intentosHechos + 1) . ' de ' . $maxIntentos; ?>)</small>
      </h1>
    </div>
    <p class="lead">
      Inicia: <?php echo $fechaInicio->format('d/m/Y H:i'); ?> |
      Cierra: <?php echo $fechaCierre->format('d/m/Y H:i'); ?> |
      Duración: <?php echo intval($evaluacion['DuracionMinutos']); ?> minutos
    </p>

    <?php echo $alert; ?>

    <?php if ($_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
      <?php
        // 8) Listar preguntas y opciones
        $preguntas = $ec->get_preguntas_by_evaluacion($evaluacionId);
      ?>
      <?php if (empty($preguntas)): ?>
        <div class="alert alert-info text-center">No hay preguntas para esta evaluación.</div>
      <?php else: ?>
        <form method="POST" autocomplete="off">
          <?php foreach ($preguntas as $idx => $preg): 
              $pregId = intval($preg['id']);
              $opciones = $ec->get_opciones_by_pregunta($pregId);
          ?>
            <div class="panel">
              <div class="panel-heading">
                Pregunta <?php echo ($idx + 1); ?>:
              </div>
              <div class="panel-body">
                <p><?php echo nl2br(htmlspecialchars($preg['TextoPregunta'])); ?></p>
                <?php if (empty($opciones)): ?>
                  <p class="text-warning">No hay opciones para esta pregunta.</p>
                <?php else: ?>
                  <?php foreach ($opciones as $j => $op): ?>
                    <div class="form-group">
                      <label>
                        <input 
                          type="radio" 
                          name="resp_<?php echo $pregId; ?>" 
                          value="<?php echo intval($op['id']); ?>" 
                          required>
                        <?php echo htmlspecialchars($op['TextoOpcion']); ?>
                      </label>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>

          <p class="text-center">
            <button type="submit" class="btn btn-submit btn-raised btn-lg">
              <i class="zmdi zmdi-check"></i> Enviar respuestas
            </button>
          </p>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
