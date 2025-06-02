<?php
// views/contents/evaluacion-student-view.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/evaluacionController.php';
$insEvaluacion = new evaluacionController();

$userCodigo = $_SESSION['userKey'] ?? '';

// Extraer IDs de la URL: “/evaluacion/{sesionId}/estudiante/”
$parts    = explode('/', trim($_GET['views'], '/'));
$sesionId = intval($parts[1] ?? 0);

$alert = '';
$nota  = null;

// 1) Obtener evaluación existente para esta sesión
$stmtEval = $insEvaluacion->get_evaluacion_by_sesion_controller($sesionId);
if ($stmtEval->rowCount() === 0) {
    echo '<div class="dashboard-contentPage">
            <p class="lead text-center">Aún no hay evaluación para esta sesión.</p>
          </div>';
    return;
}
$evalRow = $stmtEval->fetch(PDO::FETCH_ASSOC);
$evalId  = (int)$evalRow['id'];
$tituloEvaluacion = $evalRow['Titulo'];

// 2) Si el estudiante ya había respondido, mostrarle su nota y no permitir reenvío
$resultado = $insEvaluacion->get_resultado_controller($evalId, $userCodigo);
if ($resultado) {
    $nota = $resultado['Nota'];
}

// 3) Si se envía POST con respuestas, procesarlas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $nota === null) {
    $alert = $insEvaluacion->submit_respuestas_controller($evalId, $_POST);
    // Volver a obtener la nota tras envío
    $resultado = $insEvaluacion->get_resultado_controller($evalId, $userCodigo);
    if ($resultado) {
        $nota = $resultado['Nota'];
    }
}

// 4) Obtener preguntas + opciones
$preguntasOpciones = $insEvaluacion->list_preguntas_opciones($evalId);
?>

<style>
  /* ========================
     Estilos estudiante (oscuro)
     ======================== */
  html, body {
    margin:0; padding:0;
    background:#1e1f28; color:#fff;
    width:100%; height:100%; overflow-x:hidden;
    box-sizing: border-box;
  }
  .dashboard-contentPage {
    margin-left:170px; padding:30px;
    width: calc(100% - 170px);
    background:#1e1f28; min-height:100vh;
    box-sizing: border-box;
  }
  .page-header h1 {
    font-size:28px; color:#00e5ff;
    text-shadow:1px 1px 6px #000;
    margin-bottom:10px;
  }
  .lead {
    font-size:1.1rem; color:#ccc;
    margin-bottom:30px;
  }
  .panel {
    background:#2c2d3f; border-radius:12px;
    box-shadow:0 4px 18px rgba(0,0,0,0.5);
    border:1px solid #3c3d4f; margin-bottom:1rem;
  }
  .panel-heading {
    background:#00bcd4 !important; color:#fff;
    font-weight:bold; font-size:17px;
    text-align:center; padding:12px 15px;
    border-top-left-radius:12px; border-top-right-radius:12px;
  }
  .panel-body { padding:20px; }
  .pregunta-block {
    padding:15px; margin-bottom:1rem;
    background: rgba(255,255,255,0.05); border-radius:8px;
  }
  .pregunta-block h5 {
    font-size:1rem; color:#29b6f6; margin-bottom:10px;
  }
  .opcion-item {
    display: block; padding:8px 12px; margin-bottom:6px;
    background:#333; border-radius:4px; color:#fff;
    cursor:pointer; transition:background 0.2s;
  }
  .opcion-item:hover { background:#444; }
  .form-check {
    margin-bottom:8px;
  }
  .form-check input[type="radio"] {
    margin-right:8px;
  }
  .btn-success {
    background-color:#43a047 !important; border:1px solid #388e3c !important;
    color:#fff; font-weight:bold;
  }
  .btn-success:hover {
    background-color:#4caf50 !important;
  }
  .resultado-box {
    background: #333; padding: 20px; border-radius: 8px; text-align: center;
    color: #fff; font-size: 1.2rem; margin-bottom: 1rem;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-assignment"></i>
        <?= htmlspecialchars($tituloEvaluacion) ?>
      </h1>
    </div>
    <p class="lead">Responde las siguientes preguntas. Tu nota será calculada sobre 20.</p>
    <?= $alert ?>

    <?php if ($nota !== null): ?>
      <div class="resultado-box">
        Ya respondiste esta evaluación.<br>
        <strong>Tu nota: <?= htmlspecialchars($nota) ?> / 20</strong>
      </div>
      <?php // NO mostramos el formulario nuevamente ?>
    <?php else: ?>
      <form method="POST">
        <?php foreach ($preguntasOpciones as $preguntaId => $datos): ?>
          <div class="pregunta-block">
            <h5><?= htmlspecialchars($datos['TextoPregunta']) ?></h5>
            <?php foreach ($datos['Opciones'] as $op): ?>
              <div class="form-check">
                <label class="opcion-item">
                  <input type="radio"
                         name="respuesta[<?= $preguntaId ?>]"
                         value="<?= $op['opcionId'] ?>"
                         required>
                  <?= htmlspecialchars($op['TextoOpcion']) ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>

        <p class="text-center">
          <button type="submit" class="btn btn-success">
            <i class="zmdi zmdi-mail-send"></i> Enviar respuestas
          </button>
        </p>
      </form>
    <?php endif; ?>
  </div>
</section>
