<?php
// views/contents/evaluacion-student-view.php

// 1) Sólo usuarios autenticados como Estudiante
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

// 2) Controladores necesarios
require_once __DIR__ . '/../../controllers/sesionController.php';
require_once __DIR__ . '/../../controllers/evaluacionController.php';

$insSesion     = new sesionController();
$insEvaluacion = new evaluacionController();
$userCode      = $_SESSION['userCode'] ?? '';  // Código del estudiante desde sesión
$userType      = $_SESSION['userType'];

// 3) ID de sesión por URL: /evaluacion-student/{sesionId}/estudiante/
$parts    = explode("/", trim($_GET['views'], "/"));
$sesionId = intval($parts[1]);

// 4) Obtener datos de la sesión (principalmente por redirección o mostrar título)
$dataSesion = $insSesion->get_sesion_by_id_controller($sesionId);
if ($dataSesion->rowCount() === 0) {
    echo '<div class="alert alert-danger">Sesión no encontrada.</div>';
    return;
}
$sesion = $dataSesion->fetch(PDO::FETCH_ASSOC);

// 5) Conexión PDO para guardar respuestas y resultados
$pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// 6) Si llega POST, procesar la entrega de la evaluación
$resultadoFinal = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['evaluacion_id'])) {
    $evaluacionId = intval($_POST['evaluacion_id']);
    $now = date('Y-m-d H:i:s');

    // 6.1) Verificar si estudiante ya presentó esta evaluación
    $chkRes = $pdo->prepare("
        SELECT Nota 
          FROM resultado 
         WHERE EvaluacionId = ? 
           AND EstudianteCodigo = ?
    ");
    $chkRes->execute([$evaluacionId, $userCode]);
    if ($chkRes->rowCount() > 0) {
        // Ya tiene nota: no permitir reenviar
        $resultadoFinal = $chkRes->fetch(PDO::FETCH_ASSOC)['Nota'];
    } else {
        // 6.2) Obtener las preguntas de la evaluación
        $preguntas = $insEvaluacion->list_preguntas_by_evaluacion_controller($evaluacionId);
        $totalPreguntas = count($preguntas);
        $aciertos = 0;

        // Iniciar transacción
        $pdo->beginTransaction();
        try {
            // 6.3) Recorrer cada pregunta, revisar respuesta en $_POST y guardar en `respuesta_estudiante`
            foreach ($preguntas as $p) {
                $pregId = intval($p['id']);
                $campoName = 'respuesta_' . $pregId;
                if (!isset($_POST[$campoName])) {
                    // Si no respondió, guardamos con opción 0 (o saltamos). Aquí lo marcamos como incorrecto.
                    $selectedOpcionId = null;
                } else {
                    $selectedOpcionId = intval($_POST[$campoName]);
                }

                if ($selectedOpcionId) {
                    // 6.3.1) Insertar en `respuesta_estudiante`
                    $insResp = $pdo->prepare("
                        INSERT INTO respuesta_estudiante 
                            (EvaluacionId, EstudianteCodigo, PreguntaId, OpcionElegidaId)
                        VALUES (?, ?, ?, ?)
                    ");
                    $insResp->execute([
                        $evaluacionId,
                        $userCode,
                        $pregId,
                        $selectedOpcionId
                    ]);

                    // 6.3.2) Verificar si la opción es correcta
                    $chkOpt = $pdo->prepare("
                        SELECT EsCorrecta 
                          FROM opcion 
                         WHERE id = ?
                    ");
                    $chkOpt->execute([$selectedOpcionId]);
                    $esCorrecta = intval($chkOpt->fetch(PDO::FETCH_ASSOC)['EsCorrecta']);
                    if ($esCorrecta === 1) {
                        $aciertos++;
                    }
                }
                // Si no seleccionó ninguna opción, consideramos incorrecta (no incrementamos $aciertos)
            }

            // 6.4) Calcular nota: porcentaje de aciertos * 20 (ó la escala que prefieras).
            // Aquí se usa: nota = (aciertos / total) * 100, redondeado a dos decimales.
            if ($totalPreguntas > 0) {
                $nota = round(($aciertos / $totalPreguntas) * 100, 2);
            } else {
                $nota = 0;
            }

            // 6.5) Insertar en `resultado` (si aún no existe)
            $insRes = $pdo->prepare("
                INSERT INTO resultado (EvaluacionId, EstudianteCodigo, Nota)
                VALUES (?, ?, ?)
            ");
            $insRes->execute([
                $evaluacionId,
                $userCode,
                $nota
            ]);

            $pdo->commit();
            $resultadoFinal = $nota;
        } catch (Exception $e) {
            $pdo->rollBack();
            $resultadoFinal = 'Error al guardar la evaluación.';
        }
    }
}

// 7) Si llega GET con parámetro `eval_id`, preparar para mostrar preguntas
$mostrarFormulario = false;
$evaluacionActual = null;
$preguntasForm    = [];
if (isset($_GET['eval_id'])) {
    $evalId = intval($_GET['eval_id']);

    // 7.1) Obtener datos de la evaluación
    $evaluacionActual = $insEvaluacion->get_evaluacion_by_id_controller($evalId);
    if ($evaluacionActual) {
        // 7.2) Verificar fechas de inicio y cierre
        $now = date('Y-m-d H:i:s');
        $fi  = $evaluacionActual['FechaInicio'];
        $fc  = $evaluacionActual['FechaCierre'];
        if ($now >= $fi && $now <= $fc) {
            // 7.3) Verificar si el estudiante ya respondió (para no permitir reenviar)
            $chkRes2 = $pdo->prepare("
                SELECT Nota 
                  FROM resultado 
                 WHERE EvaluacionId = ? 
                   AND EstudianteCodigo = ?
            ");
            $chkRes2->execute([$evalId, $userCode]);
            if ($chkRes2->rowCount() === 0 && $resultadoFinal === null) {
                $mostrarFormulario = true;
                // 7.4) Cargar preguntas y opciones para el formulario
                $pregList = $insEvaluacion->list_preguntas_by_evaluacion_controller($evalId);
                foreach ($pregList as $p) {
                    $opts = $insEvaluacion->list_opciones_by_pregunta_controller(intval($p['id']));
                    $preguntasForm[] = [
                        'id'       => intval($p['id']),
                        'texto'    => $p['TextoPregunta'],
                        'opciones' => $opts  // cada 'opt' contiene id, TextoOpcion, EsCorrecta
                    ];
                }
            }
        }
    }
}

// 8) Listar todas las evaluaciones programadas para esta sesión
$evaluaciones = $insEvaluacion->list_evaluaciones_by_sesion_controller($sesionId);
?>

<style>
  html, body {
    margin: 0;
    padding: 0;
    background-color: #1e1f28;
    color: #fff;
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
    font-family: 'Arial', sans-serif;
  }

  .dashboard-contentPage {
    margin-left: 130px;
    padding:0 30px;
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
    margin-bottom: 30px;
  }

  .btn-back-home {
    background-color: #607d8b !important;
    border-color:     #455a64 !important;
    color:            #fff !important;
    margin-bottom: 20px;
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

  .btn-info {
    background-color: #03a9f4;
    border-color: #0288d1;
    color: #fff;
  }

  .btn-info:hover {
    background-color: #0288d1;
  }

  .btn-success {
    background-color: #4caf50;
    border-color: #388e3c;
    color: #fff;
  }

  .btn-success:hover {
    background-color: #388e3c;
  }

  .btn-primary {
    background-color: #2196f3;
    border-color: #1976d2;
    color: #fff;
  }

  .btn-primary:hover {
    background-color: #1976d2;
  }

  .form-control {
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid #555;
    color: #fff;
  }

  .form-control:focus {
    border-color: #00e5ff;
    box-shadow: 0 0 5px rgba(0, 229, 255, 0.5);
  }

  /* Estilos para la lista de evaluaciones */
  .lista-evaluaciones table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
  }
  .lista-evaluaciones th,
  .lista-evaluaciones td {
    padding: 12px;
    border-bottom: 1px solid #444;
    text-align: left;
    color: #fff;
  }
  .lista-evaluaciones th {
    background: #333;
  }
  .lista-evaluaciones td .btn-action {
    margin-right: 5px;
  }

  /* Estilos para el formulario de la evaluación */
  .evaluation-form {
    margin-top: 30px;
  }

  .question-block {
    background: #2a2c3b;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
  }

  .question-block h4 {
    margin-top: 0;
    color: #ffeb3b;
  }

  .option-group {
    margin: 8px 0;
    display: flex;
    align-items: center;
  }

  .option-group input[type="radio"] {
    margin-right: 10px;
  }

  .evaluation-form button {
    margin-top: 20px;
  }

  /* Contenedor de ancho fijo y centrado */
  .lista-evaluaciones,
  .evaluation-form {
    width: 90%;
    max-width: 1000px;
    margin: 0 auto;
    box-sizing: border-box;
  }

  @media (max-width: 768px) {
    .lista-evaluaciones,
    .evaluation-form {
      width: 100%;
      padding: 0 10px;
    }
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <!-- Botón Volver a Mis Cursos o Sesiones -->
    <a href="<?php echo SERVERURL; ?>miscursos/" class="btn btn-back-home btn-sm">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Mis Cursos
    </a>
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-assignment"></i>
        Evaluaciones para Sesión: <?php echo htmlspecialchars($sesion['Titulo']); ?>
      </h1>
    </div>
    <p class="lead">
      Fecha de la sesión: <?php echo date("d/m/Y", strtotime($sesion['Fecha'])); ?>
    </p>
  </div>

  <!-- 8) Listado de evaluaciones programadas para esta sesión -->
  <div class="container-fluid lista-evaluaciones">
    <?php if (!empty($evaluaciones)): ?>
      <div class="panel panel-info">
        <div class="panel-heading">
          <h3 class="panel-title"><i class="zmdi zmdi-view-list"></i> Evaluaciones Disponibles</h3>
        </div>
        <div class="panel-body">
          <table>
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
              <?php
                $now = date('Y-m-d H:i:s');
                foreach ($evaluaciones as $ev):
                  $fi = $ev['FechaInicio'];
                  $fc = $ev['FechaCierre'];
                  // Verificar si ya hay nota
                  $chkNota = $pdo->prepare("
                    SELECT Nota 
                      FROM resultado 
                     WHERE EvaluacionId = ? 
                       AND EstudianteCodigo = ?
                  ");
                  $chkNota->execute([$ev['id'], $userCode]);
                  $yaTomada = ($chkNota->rowCount() > 0);
                  $notaAlumno = $yaTomada ? $chkNota->fetch(PDO::FETCH_ASSOC)['Nota'] : null;

                  // Determinar estado: 
                  //  - Si ya está calificada → "Calificada"
                  //  - Si ahora < FechaInicio → "No iniciada"
                  //  - Si ahora > FechaCierre → "Expirada"
                  //  - Si dentro de ventana → "Disponible"
                  if ($yaTomada) {
                    $estado = 'Calificada (' . $notaAlumno . '%)';
                  } elseif ($now < $fi) {
                    $estado = 'No iniciada';
                  } elseif ($now > $fc) {
                    $estado = 'Expirada';
                  } else {
                    $estado = 'Disponible';
                  }
              ?>
                <tr>
                  <td><?php echo htmlspecialchars($ev['Titulo']); ?></td>
                  <td><?php echo date("d/m/Y H:i", strtotime($fi)); ?></td>
                  <td><?php echo date("d/m/Y H:i", strtotime($fc)); ?></td>
                  <td><?php echo $estado; ?></td>
                  <td>
                    <?php if ($estado === 'Disponible'): ?>
                      <a 
                        href="<?php 
                          echo SERVERURL . "evaluacion-student/{$sesionId}/estudiante/?eval_id=" 
                               . intval($ev['id']); 
                        ?>"
                        class="btn btn-primary btn-xs btn-action"
                        title="Iniciar Evaluación"
                      >
                        <i class="zmdi zmdi-play-circle"></i> Iniciar
                      </a>
                    <?php elseif ($yaTomada): ?>
                      <span style="color: #4caf50; font-weight: bold;">✔ Ya rendida</span>
                    <?php else: ?>
                      <button class="btn btn-info btn-xs" disabled>—</button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php else: ?>
      <p class="text-center" style="color: #ccc;">No hay evaluaciones programadas para esta sesión.</p>
    <?php endif; ?>
  </div>

  <!-- 7) Formulario de la evaluación activa (si corresponde) -->
  <?php if ($mostrarFormulario && $evaluacionActual): ?>
    <div class="container-fluid evaluation-form">
      <div class="panel panel-info">
        <div class="panel-heading">
          <h3 class="panel-title">
            <i class="zmdi zmdi-edit"></i>
            <?php echo htmlspecialchars($evaluacionActual['Titulo']); ?>
            <small style="font-size: 14px; display: block;">
              (Duración: <?php echo intval($evaluacionActual['DuracionMinutos']); ?> min)
            </small>
          </h3>
        </div>
        <div class="panel-body">
          <form method="POST" autocomplete="off">
            <input type="hidden" name="evaluacion_id" 
                   value="<?php echo intval($evaluacionActual['id']); ?>">

            <?php foreach ($preguntasForm as $idx => $preg): ?>
              <div class="question-block">
                <h4>Pregunta <?php echo $idx + 1; ?>:</h4>
                <p><?php echo htmlspecialchars($preg['texto']); ?></p>
                <?php foreach ($preg['opciones'] as $opt): ?>
                  <div class="option-group">
                    <input 
                      type="radio" 
                      name="respuesta_<?php echo intval($preg['id']); ?>" 
                      value="<?php echo intval($opt['id']); ?>" 
                      required
                    >
                    <label><?php echo htmlspecialchars($opt['TextoOpcion']); ?></label>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>

            <p class="text-center">
              <button type="submit" class="btn btn-success">
                <i class="zmdi zmdi-check"></i> Terminar Evaluación
              </button>
            </p>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- 6) Mostrar nota final si se acaba de enviar o ya estaba presente -->
  <?php if ($resultadoFinal !== null): ?>
    <div class="container-fluid evaluation-form">
      <div class="panel panel-success">
        <div class="panel-heading">
          <h3 class="panel-title">
            <i class="zmdi zmdi-chart"></i>
            Tu nota: <?php echo is_numeric($resultadoFinal) ? $resultadoFinal . '%' : htmlspecialchars($resultadoFinal); ?>
          </h3>
        </div>
        <div class="panel-body">
          <p style="font-size: 1.1rem; color: #fff; text-align: center;">
            <?php 
              if (is_numeric($resultadoFinal)) {
                echo 'Obtuviste ' . $resultadoFinal . '% en esta evaluación.';
              } else {
                echo $resultadoFinal;
              }
            ?>
          </p>
          <p class="text-center">
            <a href="<?php echo SERVERURL . "evaluacion-student/{$sesionId}/estudiante/"; ?>" 
               class="btn btn-primary">
              <i class="zmdi zmdi-arrow-left"></i> Volver a Evaluaciones
            </a>
          </p>
        </div>
      </div>
    </div>
  <?php endif; ?>
</section>
