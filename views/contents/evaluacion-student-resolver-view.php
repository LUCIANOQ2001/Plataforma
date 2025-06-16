<?php
// views/contents/evaluacion-student-resolver-view.php

// 1) Sólo Estudiantes pueden ver esta página
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['userType']) || $_SESSION['userType'] !== 'Estudiante') {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

// 2) Controladores necesarios
require_once __DIR__ . '/../../controllers/sesionController.php';
require_once __DIR__ . '/../../controllers/evaluacionController.php';

$insSesion     = new sesionController();
$insEvaluacion = new evaluacionController();
$userCode      = $_SESSION['userKey'] ?? $_SESSION['userCode'] ?? '';

// 3) IDs de URL: /evaluacion-student-resolver/{sesionId}/{evaluacionId}/
$parts        = explode('/', trim($_GET['views'], '/'));
$sesionId     = intval($parts[1] ?? 0);
$evaluacionId = intval($parts[2] ?? 0);

// 4) Obtener datos de la sesión
$sesRes = $insSesion->get_sesion_by_id_controller($sesionId);
if (!$sesRes || ($sesRes instanceof PDOStatement && $sesRes->rowCount() === 0)) {
    echo '<div style="padding:20px; color:#fff; background:#c00;">Sesión no encontrada.</div>';
    exit;
}
$sesion = ($sesRes instanceof PDOStatement) ? $sesRes->fetch(PDO::FETCH_ASSOC) : $sesRes;

// 5) Obtener datos de la evaluación
$evRes = $insEvaluacion->get_evaluacion_by_id_controller($evaluacionId);
if (!$evRes || ($evRes instanceof PDOStatement && $evRes->rowCount() === 0)) {
    echo '<div style="padding:20px; color:#fff; background:#c00;">Evaluación no encontrada.</div>';
    exit;
}
$evaluacion = ($evRes instanceof PDOStatement) ? $evRes->fetch(PDO::FETCH_ASSOC) : $evRes;

// 6) Conexión para guardar respuestas y resultados
$pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// 7) Si llega POST, procesar envío (único intento)
$notaFinal = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pregs   = $insEvaluacion->list_preguntas_by_evaluacion_controller($evaluacionId);
    $total   = count($pregs);
    $aciertos = 0;
    $pdo->beginTransaction();
    try {
        foreach ($pregs as $p) {
            $pid   = intval($p['id']);
            $campo = 'respuesta_' . $pid;
            if (!empty($_POST[$campo])) {
                $oid = intval($_POST[$campo]);
                // Guardar respuesta
                $insStmt = $pdo->prepare(
                    "INSERT INTO respuesta_estudiante
                     (EvaluacionId, EstudianteCodigo, PreguntaId, OpcionElegidaId, Fecha)
                     VALUES (?, ?, ?, ?, NOW())"
                );
                $insStmt->execute([$evaluacionId, $userCode, $pid, $oid]);
                // Verificar si correcta
                $chk = $pdo->prepare("SELECT EsCorrecta FROM opcion WHERE id = ?");
                $chk->execute([$oid]);
                if (intval($chk->fetchColumn()) === 1) {
                    $aciertos++;
                }
            }
        }
        // Calcular nota en porcentaje
        $notaFinal = ($total > 0) ? round(($aciertos / $total) * 100, 2) : 0;
        // Insertar resultado
        $insRes = $pdo->prepare(
            "INSERT INTO resultado (EvaluacionId, EstudianteCodigo, Nota, Fecha)
             VALUES (?, ?, ?, NOW())"
        );
        $insRes->execute([$evaluacionId, $userCode, $notaFinal]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $notaFinal = 'Error al guardar.';
    }
}

// 8) Listar preguntas para formulario o mostrar resultado
$preguntas = $insEvaluacion->list_preguntas_by_evaluacion_controller($evaluacionId);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($evaluacion['Titulo']) ?></title>
  <style>
    /* 1) Oculta todo el layout de fondo (sidebar, header, etc.) */
    body > *:not(.dashboard-contentPage) {
      display: none !important;
    }
    .dashboard-contentPage {
      margin: 0; padding: 20px;
      width: 100%; box-sizing: border-box;
      background: #2B2B2B; color: #FFF;
      font-family: 'RobotoCondensed',sans-serif;
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
    /* 2) Estilos de tu formulario/resultado */
    .panel {
      background: rgba(174,12,12,0.61);
      border: 1px solid #D1B16E;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1.5rem;
    }
    .panel h2 {
      margin: 0 0 1rem;
      color: #D1B16E;
      text-align: center;
    }
    .panel p {
      text-align: center;
      margin: 0.5rem 0;
    }
    .question {
      margin-bottom: 1rem;
      padding: 1rem;
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 6px;
    }
    .question h3 {
      margin: 0 0 .5rem;
      color: #D1B16E;
    }
    .option {
      margin: 0.5rem 0;
      padding-left: 1rem;
      position: relative;
    }
    .option:before {
      content: "•";
      position: absolute;
      left: 0; color: #D1B16E;
    }
    .resultado {
      font-size: 1.2rem;
      margin: 1rem 0;
      text-align: center;
    }
    .aprobado {
      color: #4CAF50;
    }
    .desaprobado {
      color: #FF5722;
    }
    .btn-submit {
      display: block;
      margin: 1.5rem auto 0;
      padding: .6rem 1.2rem;
      background: #D1B16E;
      border: none;
      border-radius: .3rem;
      color: #2B2B2B;
      cursor: pointer;
    }
    .btn-submit:hover {
      background: rgba(209,177,110,0.8);
    }
  </style>
</head>
<body>
  <div class="dashboard-contentPage">
    <div class="panel">
      <h2><?= htmlspecialchars($evaluacion['Titulo']) ?></h2>
      <p>Tiempo límite: <?= intval($evaluacion['DuracionMinutos']) ?> minutos</p>
      <p>
        Abre: <?= date('d/m/Y H:i', strtotime($evaluacion['FechaInicio'])) ?> |
        Cierra: <?= date('d/m/Y H:i', strtotime($evaluacion['FechaCierre'])) ?>
      </p>
    </div>

    <?php if ($notaFinal !== null): ?>
      <?php $ap = is_numeric($notaFinal) && $notaFinal >= 11; ?>
      <p class="resultado <?= $ap ? 'aprobado' : 'desaprobado' ?>">
        Tu nota: <?= is_numeric($notaFinal) ? $notaFinal.'%' : htmlspecialchars($notaFinal) ?>
        / <?= $ap ? 'Aprobado' : 'Desaprobado' ?>
      </p>
      <button class="btn-submit"
              onclick="window.opener.location.reload(); window.close();">
        Cerrar
      </button>
    <?php else: ?>
      <form method="POST">
        <?php foreach ($preguntas as $i => $p): ?>
          <div class="panel question">
            <h3>Pregunta <?= $i+1 ?>:</h3>
            <p><?= nl2br(htmlspecialchars($p['TextoPregunta'])) ?></p>
            <?php $opts = $insEvaluacion->list_opciones_by_pregunta_controller((int)$p['id']); ?>
            <?php foreach ($opts as $opt): ?>
              <div class="option">
                <label>
                  <input type="radio"
                         name="respuesta_<?= intval($p['id']) ?>"
                         value="<?= intval($opt['id']) ?>"
                         required>
                  <?= htmlspecialchars($opt['TextoOpcion']) ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
        <button type="submit" class="btn-submit">Enviar Evaluación</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
