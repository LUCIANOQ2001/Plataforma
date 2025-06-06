<?php
// views/contents/evaluacion-view.php

// 1) Sólo usuarios autenticados (Admin o Docente)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

// 2) Controladores necesarios
require_once __DIR__ . '/../../controllers/sesionController.php';
require_once __DIR__ . '/../../controllers/evaluacionController.php';

$insSesion      = new sesionController();
$insEvaluacion  = new evaluacionController();
$userType       = $_SESSION['userType'];

// 3) ID de sesión por URL: /evaluacion/{sesionId}/
$parts     = explode("/", trim($_GET['views'], "/"));
$sesionId  = intval($parts[1]);

// 4) Obtener datos de la sesión
$dataSesion = $insSesion->get_sesion_by_id_controller($sesionId);
if ($dataSesion->rowCount() === 0) {
    echo '<div class="alert alert-danger">Sesión no encontrada.</div>';
    return;
}
$sesion = $dataSesion->fetch(PDO::FETCH_ASSOC);

// 5) Manejo de acciones por GET (delete o edit) y POST (creación o actualización)
$alert = '';
$editMode = false;
$editData = null;      // Contendrá datos generales de la evaluación
$editPreguntas = [];   // Contendrá array de preguntas y opciones para el modo edición

// 5.1) Si llega acción “delete”
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $idDel = intval($_GET['id']);
    $alert = $insEvaluacion->delete_evaluacion_controller($idDel);
    // Después de eliminar, recargar sin parámetros GET para que no intente eliminar nuevamente
    echo "<script>location.replace(location.pathname + '?v=ok');</script>";
    exit;
}

// 5.2) Si llega acción “edit” (cargar datos en formulario)
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $idEdit = intval($_GET['id']);
    $editData = $insEvaluacion->get_evaluacion_by_id_controller($idEdit);
    if ($editData) {
        $editMode = true;
        // 5.2.1) Obtener preguntas y opciones existentes
        $preguntas = $insEvaluacion->list_preguntas_by_evaluacion_controller($idEdit);
        foreach ($preguntas as $p) {
            $opts = $insEvaluacion->list_opciones_by_pregunta_controller(intval($p['id']));
            // Reconstruir arreglo de opciones: sólo el texto y marcaremos cuál es correcta
            $arrayOpts = [];
            $correctIndex = 0;
            foreach ($opts as $idxOpt => $o) {
                $arrayOpts[] = $o['TextoOpcion'];
                if (intval($o['EsCorrecta']) === 1) {
                    $correctIndex = $idxOpt;
                }
            }
            $editPreguntas[] = [
                'texto' => $p['TextoPregunta'],
                'opciones' => $arrayOpts,
                'correcta' => $correctIndex
            ];
        }
    } else {
        $alert = '<div class="alert alert-danger text-center">Evaluación no encontrada para editar.</div>';
    }
}

// 5.3) Procesar POST para creación de evaluación (si no estamos en modo edición)
if (!$editMode && $_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['evaluacion_id'])) {
    $alert = $insEvaluacion->add_evaluacion_controller($sesionId, $_POST);
    // PRG: evitar reenvío
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}

// 5.4) Procesar POST para actualización si estamos en modo edición
if ($editMode && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['evaluacion_id'])) {
    $idToUpdate = intval($_POST['evaluacion_id']);
    $alert = $insEvaluacion->update_evaluacion_controller($idToUpdate, $_POST);
    // Después de actualizar, recargar sin parámetros GET
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}

// 6) Listar evaluaciones existentes de esta sesión
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
    margin-left: 120px;
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
    margin-bottom: 30px;
  }

  .btn-info {
    background-color:rgba(3, 168, 244, 0.2);
    border-color:rgba(2, 137, 209, 0.28);
    color: #fff;
  }

  .btn-info:hover {
    background-color: #0288d1;
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

  .form-control {
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid #555;
    color: #fff;
  }

  .form-control:focus {
    border-color: #00e5ff;
    box-shadow: 0 0 5px rgba(0, 229, 255, 0.5);
  }

  .question-card {
    background: #2a2c3b;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
    padding: 15px;
    margin-bottom: 20px;
  }

  .question-card h4 {
    margin-top: 0;
    color: #ffeb3b;
  }

  .option-group {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
  }

  .option-group input[type="text"] {
    flex: 1;
    margin-left: 8px;
  }

  .btn-add-question {
    background-color: #ff5722;
    border-color: #e64a19;
    color: #fff;
    margin-bottom: 20px;
  }

  .btn-add-question:hover {
    background-color: #e64a19;
  }

  /* ===========================================
     Ajustes de tamaño para el formulario de Evaluación
     =========================================== */
  #evaluacionForm {
    width: 90%;
    max-width: 1000px;
    margin: 0 auto;
    box-sizing: border-box;
  }

  #evaluacionForm .panel {
    padding: 20px;
    background: #2c2d3f;
    border-radius: 8px;
  }

  @media (max-width: 768px) {
    #evaluacionForm {
      width: 100%;
      padding: 0 10px;
    }
  }

  #evaluacionForm .row > [class*="col-"] {
    padding-left: 8px;
    padding-right: 8px;
  }

  .dashboard-contentPage .container-fluid {
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    box-sizing: border-box;
  }

  /* Estilos para la lista de evaluaciones existentes */
  .lista-evaluaciones {
    margin-top: 30px;
  }
  .lista-evaluaciones table {
    width: 100%;
    border-collapse: collapse;
  }
  .lista-evaluaciones th,
  .lista-evaluaciones td {
    padding: 10px;
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
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <!-- Botón Volver a Sesiones -->
    <a href="<?php echo SERVERURL; ?>sesion/<?php echo $sesion['CursoId']; ?>/"
       class="btn btn-back-home btn-sm">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Sesiones
    </a>

    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-assignment"></i>
        <?php echo $editMode ? 'Editar Evaluación' : 'Crear Evaluación'; ?>: 
        <?php echo htmlspecialchars($sesion['Titulo']); ?>
      </h1>
    </div>
    <p class="lead">
      Fecha de la sesión: <?php echo date("d/m/Y", strtotime($sesion['Fecha'])); ?>
    </p>

    <?php echo $alert; ?>
  </div>

  <?php if (in_array($userType, ['Administrador','Docente'])): ?>
    <!-- Formulario para crear o editar evaluación -->
    <div class="container-fluid" id="evaluacionForm">
      <div class="panel panel-info">
        <div class="panel-heading">
          <h3 class="panel-title">
            <i class="zmdi zmdi-plus-circle"></i>
            <?php echo $editMode ? 'Editar datos de la Evaluación' : 'Detalles de la Evaluación'; ?>
          </h3>
        </div>
        <div class="panel-body">
          <form method="POST" autocomplete="off">
            <!-- Si estamos editando, incluir campo oculto con ID -->
            <?php if ($editMode): ?>
              <input type="hidden" name="evaluacion_id" value="<?php echo intval($editData['id']); ?>">
            <?php endif; ?>

            <div class="row">
              <div class="col-sm-4">
                <div class="form-group label-floating">
                  <label class="control-label">Título *</label>
                  <input 
                    type="text" 
                    name="titulo" 
                    class="form-control" 
                    required
                    value="<?php echo $editMode ? htmlspecialchars($editData['Titulo']) : ''; ?>"
                  >
                </div>
              </div>
              <div class="col-sm-4">
                <div class="form-group label-floating">
                  <label class="control-label">Fecha Inicio *</label>
                  <input 
                    type="datetime-local" 
                    name="fecha_inicio" 
                    class="form-control" 
                    required
                    <?php if ($editMode): 
                      // Convertir "YYYY-MM-DD HH:MM:SS" a "YYYY-MM-DDTHH:MM"
                      $fi = date_create($editData['FechaInicio']);
                      $fiVal = date_format($fi, 'Y-m-d\TH:i');
                    ?>
                      value="<?php echo $fiVal; ?>"
                    <?php endif; ?>
                  >
                </div>
              </div>
              <div class="col-sm-4">
                <div class="form-group label-floating">
                  <label class="control-label">Fecha Cierre *</label>
                  <input 
                    type="datetime-local" 
                    name="fecha_cierre" 
                    class="form-control" 
                    required
                    <?php if ($editMode): 
                      $fc = date_create($editData['FechaCierre']);
                      $fcVal = date_format($fc, 'Y-m-d\TH:i');
                    ?>
                      value="<?php echo $fcVal; ?>"
                    <?php endif; ?>
                  >
                </div>
              </div>
            </div>

            <div class="row" style="margin-top: 10px;">
              <div class="col-sm-4">
                <div class="form-group label-floating">
                  <label class="control-label">Tiempo Límite (minutos) *</label>
                  <input 
                    type="number" 
                    name="duracion" 
                    class="form-control" 
                    min="1" 
                    required
                    value="<?php echo $editMode ? intval($editData['DuracionMinutos']) : ''; ?>"
                  >
                </div>
              </div>
            </div>

            <hr style="border-color: #3c3d4f; margin: 25px 0;">

            <!-- Sección para agregar preguntas dinámicamente -->
            <button 
              type="button" 
              class="btn btn-add-question btn-raised" 
              id="btnAddQuestion"
            >
              <i class="zmdi zmdi-plus"></i> Agregar Pregunta
            </button>

            <div id="questionsContainer">
              <!-- Aquí se clonan las tarjetas de pregunta -->
            </div>

            <p class="text-center" style="margin-top: 20px;">
              <button type="submit" class="btn btn-success btn-raised">
                <i class="zmdi zmdi-floppy"></i> 
                <?php echo $editMode ? 'Actualizar Evaluación' : 'Guardar Evaluación'; ?>
              </button>
            </p>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Listado de evaluaciones existentes -->
  <div class="container-fluid lista-evaluaciones">
    <?php if (!empty($evaluaciones)): ?>
      <div class="panel panel-info">
        <div class="panel-heading">
          <h3 class="panel-title"><i class="zmdi zmdi-view-list"></i> Evaluaciones Existentes</h3>
        </div>
        <div class="panel-body">
          <table>
            <thead>
              <tr>
                <th>Título</th>
                <th>Inicio</th>
                <th>Cierre</th>
                <th>Límite (min)</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($evaluaciones as $ev): ?>
                <tr>
                  <td><?php echo htmlspecialchars($ev['Titulo']); ?></td>
                  <td><?php echo date("d/m/Y H:i", strtotime($ev['FechaInicio'])); ?></td>
                  <td><?php echo date("d/m/Y H:i", strtotime($ev['FechaCierre'])); ?></td>
                  <td><?php echo intval($ev['DuracionMinutos']); ?></td>
                  <td>
                    <!-- Botón Editar -->
                    <a 
                      href="<?php echo SERVERURL . "evaluacion/{$sesionId}/?action=edit&id=" 
                                   . intval($ev['id']); ?>"
                      class="btn btn-info btn-xs btn-action"
                      title="Editar evaluación"
                    >
                      <i class="zmdi zmdi-edit"></i>
                    </a>
                    <!-- Botón Eliminar (con confirmación JS) -->
                    <a 
                      href="javascript:void(0);" 
                      onclick="if(confirm('¿Está seguro de eliminar esta evaluación?')) {
                          window.location.href = '<?php echo SERVERURL . "evaluacion/{$sesionId}/?action=delete&id=" 
                                                    . intval($ev['id']); ?>';
                        }"
                      class="btn btn-danger btn-xs btn-action"
                      title="Eliminar evaluación"
                    >
                      <i class="zmdi zmdi-delete"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php else: ?>
      <p class="text-center" style="color: #ccc;">No hay evaluaciones aún para esta sesión.</p>
    <?php endif; ?>
  </div>
</section>

<!-- Plantilla oculta para clonar cada pregunta -->
<template id="questionTemplate">
  <div class="question-card">
    <h4>Pregunta <span class="question-number"></span></h4>
    <div class="form-group">
      <textarea 
        name="pregunta_texto[]" 
        class="form-control" 
        rows="2" 
        placeholder="Texto de la pregunta" 
        required
      ></textarea>
    </div>
    <div class="option-group">
      <input type="radio" name="pregunta_correcta_INDEX" value="0" required>
      <input 
        type="text" 
        name="opciones_INDEX[]" 
        class="form-control" 
        placeholder="Opción 1" 
        required
      >
    </div>
    <div class="option-group">
      <input type="radio" name="pregunta_correcta_INDEX" value="1">
      <input 
        type="text" 
        name="opciones_INDEX[]" 
        class="form-control" 
        placeholder="Opción 2" 
        required
      >
    </div>
    <div class="option-group">
      <input type="radio" name="pregunta_correcta_INDEX" value="2">
      <input 
        type="text" 
        name="opciones_INDEX[]" 
        class="form-control" 
        placeholder="Opción 3" 
        required
      >
    </div>
    <div class="option-group">
      <input type="radio" name="pregunta_correcta_INDEX" value="3">
      <input 
        type="text" 
        name="opciones_INDEX[]" 
        class="form-control" 
        placeholder="Opción 4" 
        required
      >
    </div>
    <button 
      type="button" 
      class="btn btn-danger btn-sm btnRemoveQuestion" 
      style="margin-top: 10px;"
    >
      <i class="zmdi zmdi-delete"></i> Eliminar Pregunta
    </button>
  </div>
</template>

<script>
  (function() {
    let questionCount = 0;
    const btnAdd = document.getElementById('btnAddQuestion');
    const container = document.getElementById('questionsContainer');
    const template = document.getElementById('questionTemplate').content;

    /**
     * Crea una nueva tarjeta de pregunta (el template ingresa “_INDEX” en los name,
     * luego lo reemplazamos por un número entero (0, 1, 2, …) para que PHP reciba
     * arrays diferenciados).
     * 
     * Si hay datos en modo edición, deberemos pre-poblar los campos a través de
     * la función initEditMode() (al final).
     */
    function addQuestion(prefill = null) {
      // prefill = { texto: "Texto pregunta", opciones: ["opc1", "opc2", "opc3", "opc4"], correcta: 2 }
      const currentIndex = questionCount;
      questionCount++;

      // 1) Clonar plantilla
      let cloneHtml = template.firstElementChild.outerHTML.replace(/_INDEX/g, '_' + currentIndex);
      const wrapper = document.createElement('div');
      wrapper.innerHTML = cloneHtml;
      const card = wrapper.firstElementChild;

      // 2) Actualizar número de pregunta en el encabezado
      card.querySelector('.question-number').textContent = questionCount;

      // 3) Si venimos en modo edición y recibimos 'prefill', rellenamos campos:
      if (prefill) {
        // 3.1) Texto de la pregunta
        const textarea = card.querySelector('textarea[name="pregunta_texto[]"]');
        textarea.value = prefill.texto;

        // 3.2) Opciones: recordar que en el template hay 4 inputs cuyo name es "opciones_INDEX[]"
        const inputsText = card.querySelectorAll(`input[name="opciones_${currentIndex}\\[\\]"]`);
        inputsText.forEach((inputEl, idx) => {
          inputEl.value = prefill.opciones[idx] || '';
        });

        // 3.3) Radio de opción correcta: "pregunta_correcta_INDEX"
        const radios = card.querySelectorAll(`input[name="pregunta_correcta_${currentIndex}"]`);
        if (radios[prefill.correcta]) {
          radios[prefill.correcta].checked = true;
        }
      }

      // 4) Funcionalidad botón “Eliminar Pregunta”
      card.querySelector('.btnRemoveQuestion').addEventListener('click', function() {
        card.remove();
        // (Opcional) podríamos reajustar numeración, pero no es estrictamente necesario
      });

      container.appendChild(card);
    }

    // Cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
      // 5) Si estamos en modo edición, cargar las preguntas preexistentes
      <?php if ($editMode && !empty($editPreguntas)): ?>
        const preguntasData = <?php echo json_encode($editPreguntas, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        preguntasData.forEach(function(preg) {
          // Cada 'preg' tiene: { texto: "...", opciones: ["...","...","...","..."], correcta:  indexCorrecto }
          addQuestion(preg);
        });
      <?php else: ?>
        // Si no es edición, agregamos una pregunta en blanco inicialmente
        addQuestion();
      <?php endif; ?>
      
      // 6) Listener para “Agregar Pregunta”
      btnAdd.addEventListener('click', function() {
        addQuestion();
      });
    });
  })();
</script>
