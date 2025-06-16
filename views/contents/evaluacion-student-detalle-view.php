<?php
// views/contents/evaluacion-student-detalle-view.php

// 1) Sólo Estudiantes pueden ver esta página
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['userType']) || $_SESSION['userType'] !== 'Estudiante') {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

// 2) Obtener el código del estudiante
$userCode = $_SESSION['userKey'] ?? $_SESSION['userCode'] ?? '';

// 3) IDs de URL: /evaluacion-student-detalle/{sesionId}/{evaluacionId}/
$parts        = explode('/', trim($_GET['views'], '/'));
$sesionId     = intval($parts[1] ?? 0);
$evaluacionId = intval($parts[2] ?? 0);

// 4) Conectar PDO para consultas directas
$pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// 5) Controlador para datos de evaluación y preguntas
require_once __DIR__ . '/../../controllers/evaluacionController.php';
$insEvaluacion = new evaluacionController();

// 6) Obtener datos de la evaluación
$evStmt = $insEvaluacion->get_evaluacion_by_id_controller($evaluacionId);
if (!$evStmt || ($evStmt instanceof PDOStatement && $evStmt->rowCount() === 0)) {
    echo '<div style="padding:20px; color:#fff; background:#c00;">Evaluación no encontrada.</div>';
    exit;
}
$evaluacion = ($evStmt instanceof PDOStatement)
    ? $evStmt->fetch(PDO::FETCH_ASSOC)
    : $evStmt;

// 7) Listar preguntas
$preguntas = $insEvaluacion->list_preguntas_by_evaluacion_controller($evaluacionId);
if (empty($preguntas)) {
    echo '<div style="padding:20px; color:#fff; background:#555;">No hay preguntas en esta evaluación.</div>';
    exit;
}

// 8) Cargar respuestas del estudiante
$resStmt = $pdo->prepare("
    SELECT PreguntaId, OpcionElegidaId
      FROM respuesta_estudiante
     WHERE EvaluacionId = ?
       AND EstudianteCodigo = ?
");
$resStmt->execute([$evaluacionId, $userCode]);
$respuestas = $resStmt->fetchAll(PDO::FETCH_ASSOC);
// Mapear PreguntaId => OpcionElegidaId
$mapResp = [];
foreach ($respuestas as $r) {
    $mapResp[(int)$r['PreguntaId']] = (int)$r['OpcionElegidaId'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Detalle Evaluación: <?= htmlspecialchars($evaluacion['Titulo']) ?></title>
  <style>
    /* ——————————————————————————
       1) Oculta absolutamente todo el layout
       excepto el contenedor .dashboard-contentPage
       —————————————————————————— */
    body > *:not(.dashboard-contentPage) {
      display: none !important;
    }
    .dashboard-contentPage {
      margin: 0 !important;
      width: 100% !important;
      padding: 20px !important;
      box-sizing: border-box;
    }

    /* ——————————————————————————
       2) Estilos de la ventana emergente
       —————————————————————————— */
    body {
      margin:0; padding:0;
      font-family:'RobotoCondensed',sans-serif;
      background:#2B2B2B;
      color:#FFF;
    }
    .panel {
      background:rgba(174,12,12,0.61);
      border:1px solid #D1B16E;
      border-radius:8px;
      padding:1rem;
      margin-bottom:1.5rem;
    }
    .panel h2 {
      margin:0 0 1rem;
      color:#D1B16E;
      text-align:center;
    }
    .panel p {
      text-align:center;
      margin-bottom:0;
    }
    .question {
      margin-bottom:1rem;
      padding:1rem;
      border:1px solid rgba(255,255,255,0.1);
      border-radius:6px;
    }
    .question h3 {
      margin:0 0 .5rem;
      color:#D1B16E;
    }
    .option {
      margin:0.5rem 0;
      padding-left:1rem;
      position: relative;
    }
    .option:before {
      content: "•";
      position: absolute;
      left:0; color:#D1B16E;
    }
    .result {
      font-weight:bold;
      margin-top:.5rem;
    }
    .correct {
      color:#4CAF50;
    }
    .incorrect {
      color:#FF5722;
    }
    .correct-answer {
      margin-top:.3rem;
      padding:.4rem;
      background:rgba(76,175,80,0.1);
      border-left:4px solid #4CAF50;
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
    .close-btn {
      display:block;
      margin:2rem auto 0;
      padding:.6rem 1.2rem;
      background:#D1B16E;
      color:#2B2B2B;
      border:none;
      border-radius:.3rem;
      cursor:pointer;
    }
    .close-btn:hover {
      background:rgba(209,177,110,0.8);
    }
  </style>
</head>
<body>
  <div class="dashboard-contentPage">
    <div class="panel">
      <h2><?= htmlspecialchars($evaluacion['Titulo']) ?></h2>
      <p>
        Inicio: <?= date('d/m/Y H:i', strtotime($evaluacion['FechaInicio'])) ?> |
        Cierre: <?= date('d/m/Y H:i',   strtotime($evaluacion['FechaCierre'])) ?>
      </p>
    </div>

    <?php foreach ($preguntas as $i => $preg):
      $qid = (int)$preg['id'];
    ?>
      <div class="question">
        <h3>Pregunta <?= $i+1 ?>:</h3>
        <p><?= nl2br(htmlspecialchars($preg['TextoPregunta'])) ?></p>

        <?php
          // ¿Qué eligió el alumno?
          $chosenId = $mapResp[$qid] ?? null;
          if ($chosenId === null) {
            echo '<p class="result incorrect">No respondiste esta pregunta.</p>';
          } else {
            // Traer el texto y si era correcta
            $optStmt = $pdo->prepare("SELECT TextoOpcion, EsCorrecta FROM opcion WHERE id = ?");
            $optStmt->execute([$chosenId]);
            $opt = $optStmt->fetch(PDO::FETCH_ASSOC);
            $esCorr = intval($opt['EsCorrecta']) === 1;

            // Mostrar la opción elegida
            echo '<div class="option">'.htmlspecialchars($opt['TextoOpcion']).'</div>';
            echo '<p class="result '.($esCorr ? 'correct' : 'incorrect').'">'
               . ($esCorr ? '✓ Correcta' : '✗ Incorrecta')
               . '</p>';

            // Si estuvo mal, mostramos la correcta
            if (!$esCorr) {
              $corrStmt = $pdo->prepare("
                SELECT TextoOpcion
                  FROM opcion
                 WHERE PreguntaId = ?
                   AND EsCorrecta = 1
                 LIMIT 1
              ");
              $corrStmt->execute([$qid]);
              $corrText = $corrStmt->fetchColumn();
              echo '<div class="correct-answer">'
                 . 'Respuesta correcta: '.htmlspecialchars($corrText)
                 . '</div>';
            }
          }
        ?>
      </div>
    <?php endforeach; ?>

    <button class="close-btn" onclick="window.close();">Cerrar detalle</button>
  </div>
</body>
</html>
