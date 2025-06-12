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
        $resultadoFinal = $chkRes->fetch(PDO::FETCH_ASSOC)['Nota'];
    } else {
        $preguntas = $insEvaluacion->list_preguntas_by_evaluacion_controller($evaluacionId);
        $totalPreguntas = count($preguntas);
        $aciertos = 0;

        $pdo->beginTransaction();
        try {
            foreach ($preguntas as $p) {
                $pregId = intval($p['id']);
                $campoName = 'respuesta_' . $pregId;
                $selectedOpcionId = isset($_POST[$campoName]) ? intval($_POST[$campoName]) : null;

                if ($selectedOpcionId) {
                    $insResp = $pdo->prepare("
                        INSERT INTO respuesta_estudiante 
                            (EvaluacionId, EstudianteCodigo, PreguntaId, OpcionElegidaId)
                        VALUES (?, ?, ?, ?)
                    ");
                    $insResp->execute([$evaluacionId, $userCode, $pregId, $selectedOpcionId]);

                    $chkOpt = $pdo->prepare("
                        SELECT EsCorrecta 
                          FROM opcion 
                         WHERE id = ?
                    ");
                    $chkOpt->execute([$selectedOpcionId]);
                    if (intval($chkOpt->fetch(PDO::FETCH_ASSOC)['EsCorrecta']) === 1) {
                        $aciertos++;
                    }
                }
            }

            $nota = $totalPreguntas > 0
                  ? round(($aciertos / $totalPreguntas) * 100, 2)
                  : 0;

            $insRes = $pdo->prepare("
                INSERT INTO resultado (EvaluacionId, EstudianteCodigo, Nota)
                VALUES (?, ?, ?)
            ");
            $insRes->execute([$evaluacionId, $userCode, $nota]);

            $pdo->commit();
            $resultadoFinal = $nota;
        } catch (Exception $e) {
            $pdo->rollBack();
            $resultadoFinal = 'Error al guardar la evaluación.';
        }
    }
}

// 7) Preparar formulario de evaluación si corresponde
$mostrarFormulario = false;
$evaluacionActual  = null;
$preguntasForm     = [];
if (isset($_GET['eval_id'])) {
    $evalId = intval($_GET['eval_id']);
    $evaluacionActual = $insEvaluacion->get_evaluacion_by_id_controller($evalId);
    if ($evaluacionActual) {
        $now = date('Y-m-d H:i:s');
        $fi  = $evaluacionActual['FechaInicio'];
        $fc  = $evaluacionActual['FechaCierre'];
        if ($now >= $fi && $now <= $fc && $resultadoFinal === null) {
            $chkRes2 = $pdo->prepare("
                SELECT Nota 
                  FROM resultado 
                 WHERE EvaluacionId = ? 
                   AND EstudianteCodigo = ?
            ");
            $chkRes2->execute([$evalId, $userCode]);
            if ($chkRes2->rowCount() === 0) {
                $mostrarFormulario = true;
                $pregList = $insEvaluacion->list_preguntas_by_evaluacion_controller($evalId);
                foreach ($pregList as $p) {
                    $opts = $insEvaluacion->list_opciones_by_pregunta_controller(intval($p['id']));
                    $preguntasForm[] = [
                        'id'       => intval($p['id']),
                        'texto'    => $p['TextoPregunta'],
                        'opciones' => $opts
                    ];
                }
            }
        }
    }
}

// 8) Listar evaluaciones de la sesión
$evaluaciones = $insEvaluacion->list_evaluaciones_by_sesion_controller($sesionId);
?>
<style>
  /* ==== Paleta de colores ==== */
  :root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(174,12,12,0.61);
    --text-light:       #FFFFFF;
    --hover-accent:     rgba(209,177,110,0.2);
  }

  /* Reset global */
  html, body {
    margin: 0; padding: 0;
    background: var(--primary-bg);
    color: var(--text-light);
    width: 100%; height: 100%;
    overflow-x: hidden;
    font-family: 'RobotoCondensed', sans-serif;
  }

  /* Banner de logo */
  .dashboard-banner {
    position: fixed;
    top: 0; left: 270px;
    width: calc(100% - 270px); height: 100%;
    background: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
    opacity: 0.05;
    pointer-events: none;
    z-index: 0;
  }

  /* Contenido */
  .dashboard-contentPage {
    position: relative; z-index: 1;
    margin-left: 180px;
    width: calc(100% - 270px);
    padding: 0 30px auto;
    min-height: 100vh;
    box-sizing: border-box;
  }

  /* Ocultar buscador y menú */
  .btn-options,
  .dropdown-toggle,
  .btn-search,
  i.zmdi-zmdi-search,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }
  /* Botón Volver */
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
  /* Cabecera */
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

  /* Lista de evaluaciones */
  .lista-evaluaciones table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2rem;
  }
  .lista-evaluaciones th,
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

  /* Formulario de evaluación */
 :root {
  --primary-bg:       #2B2B2B;
  --primary-accent:   #D1B16E;
  --secondary-bg:     rgba(174,12,12,0.61);
  --text-light:       #FFFFFF;
  --hover-accent:     rgba(209,177,110,0.2);
}

/* …resto de tu CSS… */

/* Panel y contenedor de la evaluación */
.evaluation-form .panel {
  background: var(--secondary-bg);
  border: 1px solid var(--primary-accent);
  border-radius: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.5);
  overflow: hidden;
  margin: 2rem auto;
  max-width: 900px;
}
.evaluation-form .panel-heading {
  background: var(--primary-accent) !important;
  color: #2B2B2B;
  text-align: center;
  padding: 1rem;
  font-weight: bold;
}
.evaluation-form .panel-body {
  padding: 1.5rem;
}

/* Bloques de pregunta */
.evaluation-form .question-block {
  background: var(--secondary-bg);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: .5rem;
  padding: 1rem;
  margin-bottom: 1.5rem;
}
.evaluation-form .question-block h4 {
  margin: 0 0 .5rem;
  color: var(--primary-accent);
}

/* Opciones */
.evaluation-form .option-group {
  display: flex;
  align-items: center;
  margin-bottom: .75rem;
}
.evaluation-form .option-group input {
  margin-right: .75rem;
}

/* Botón de envío */
.evaluation-form button[type="submit"] {
  background: var(--primary-accent);
  color: var(--text-light);
  border: none;
  border-radius: .3rem;
  padding: .6rem 1.2rem;
  font-size: 1rem;
  transition: background .3s;
}
.evaluation-form button[type="submit"]:hover {
  background: var(--hover-accent);
}

/* Responsivo */
@media (max-width: 768px) {
  .evaluation-form .panel {
    width: 100%;
    margin: 1rem 0;
    box-sizing: border-box;
  }
}

</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
    <!-- NUEVO: botón para regresar a sesiones -->
  <div class="container-fluid">
    <p class="text-center">
      <a href="<?= SERVERURL ?>sesion/<?= htmlspecialchars($sesion['CursoId']) ?>/" class="btn btn-back-home">
        <i class="zmdi zmdi-arrow-left"></i> Volver a Sesiones
      </a>
    </p>
  </div>
  <div class="container-fluid">

    <div class="page-header">
      <h1><i class="zmdi zmdi-assignment"></i> Evaluaciones de Sesión: <?= htmlspecialchars($sesion['Titulo']) ?></h1>
    </div>
    <p class="lead">Fecha de la sesión: <?= date("d/m/Y", strtotime($sesion['Fecha'])) ?></p>
  </div>

  <div class="container-fluid lista-evaluaciones">
    <?php if (!empty($evaluaciones)): ?>
    <div class="table-responsive">
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
            $fi = $ev['FechaInicio']; $fc = $ev['FechaCierre'];
            $chkNota = $pdo->prepare("SELECT Nota FROM resultado WHERE EvaluacionId=? AND EstudianteCodigo=?");
            $chkNota->execute([$ev['id'],$userCode]);
            $yaTomada = $chkNota->rowCount()>0;
            $notaAlu  = $yaTomada ? $chkNota->fetch(PDO::FETCH_ASSOC)['Nota'] : null;
            if ($yaTomada) {
              $estado='Calificada ('.$notaAlu.'%)';
            } elseif ($now<$fi) {
              $estado='No iniciada';
            } elseif ($now>$fc) {
              $estado='Expirada';
            } else {
              $estado='Disponible';
            }
          ?>
          <tr>
            <td><?= htmlspecialchars($ev['Titulo']) ?></td>
            <td><?= date('d/m/Y H:i',strtotime($fi)) ?></td>
            <td><?= date('d/m/Y H:i',strtotime($fc)) ?></td>
            <td><?= $estado ?></td>
            <td>
              <?php if ($estado==='Disponible'): ?>
                <a href="<?= SERVERURL."evaluacion-student/{$sesionId}/estudiante/?eval_id=".intval($ev['id']) ?>"
                   class="btn-action">
                  Iniciar
                </a>
              <?php elseif ($yaTomada): ?>
                <span style="color:#4caf50;font-weight:bold;">✔ Rendida</span>
              <?php else: ?>
                <span>—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <p class="text-center" style="color:rgba(255,255,255,0.7);">No hay evaluaciones programadas.</p>
    <?php endif; ?>
  </div>

  <?php if ($mostrarFormulario && $evaluacionActual): ?>
  <div class="container-fluid evaluation-form">
    <div class="panel panel-info">
      <div class="panel-heading"><?= htmlspecialchars($evaluacionActual['Titulo']) ?></div>
      <div class="panel-body">
        <form method="POST" autocomplete="off">
          <input type="hidden" name="evaluacion_id" value="<?= intval($evaluacionActual['id']) ?>">
          <?php foreach ($preguntasForm as $i => $preg): ?>
          <div class="question-block">
            <h4>Pregunta <?= $i+1 ?></h4>
            <p><?= htmlspecialchars($preg['texto']) ?></p>
            <?php foreach ($preg['opciones'] as $opt): ?>
            <div class="option-group">
              <input type="radio"
                     name="respuesta_<?= intval($preg['id']) ?>"
                     value="<?= intval($opt['id']) ?>"
                     required>
              <label><?= htmlspecialchars($opt['TextoOpcion']) ?></label>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endforeach; ?>
          <p class="text-center">
            <button type="submit"><i class="zmdi zmdi-check"></i> Terminar Evaluación</button>
          </p>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($resultadoFinal !== null): ?>
  <div class="container-fluid evaluation-form">
    <div class="panel panel-success">
      <div class="panel-heading">
        Tu nota: <?= is_numeric($resultadoFinal) ? $resultadoFinal.'%' : htmlspecialchars($resultadoFinal) ?>
      </div>
      <div class="panel-body">
        <p style="text-align:center;font-size:1.1rem;">
          <?= is_numeric($resultadoFinal)
              ? 'Obtuviste '.$resultadoFinal.'% en esta evaluación.'
              : $resultadoFinal ?>
        </p>
        <p class="text-center">
          <a href="<?= SERVERURL."evaluacion-student/{$sesionId}/estudiante/" ?>"
             class="btn btn-action"><i class="zmdi zmdi-arrow-left"></i> Volver</a>
        </p>
      </div>
    </div>
  </div>
  <?php endif; ?>

</section>
