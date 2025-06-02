<?php
// views/contents/evaluacion-view.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/evaluacionController.php';
$insEvaluacion = new evaluacionController();

// Extraer IDs de la URL: “/evaluacion/{sesionId}/”
$parts     = explode('/', trim($_GET['views'], '/'));
$sesionId  = intval($parts[1] ?? 0);
$evalId    = 0;

// 1) Intentar obtener evaluación existente para esta sesión
$stmtEval = $insEvaluacion->get_evaluacion_by_sesion_controller($sesionId);
if ($stmtEval->rowCount() === 1) {
    $evalRow = $stmtEval->fetch(PDO::FETCH_ASSOC);
    $evalId  = (int)$evalRow['id'];
    $tituloActual = $evalRow['Titulo'];
} else {
    $tituloActual = '';
}

$alert = '';

// 2) Procesar POST: crear, editar o eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_eval'])) {
        // Eliminar evaluación
        $alert = $insEvaluacion->delete_evaluacion_controller($evalId);
        // Después redirigir para refrescar
        echo "<script>location.replace(location.pathname);</script>";
        exit;
    }
    elseif (isset($_POST['edit_eval'])) {
        // Actualizar evaluación existente
        $alert = $insEvaluacion->update_evaluacion_controller($evalId, $_POST);
        echo "<script>location.replace(location.pathname);</script>";
        exit;
    }
    else {
        // Crear nueva evaluación
        $alert = $insEvaluacion->add_evaluacion_controller($sesionId, $_POST);
        echo "<script>location.replace(location.pathname);</script>";
        exit;
    }
}

// 3) Si ya existe, cargar preguntas + opciones
$preguntasOpciones = [];
if ($evalId > 0) {
    $preguntasOpciones = $insEvaluacion->list_preguntas_opciones($evalId);
}
?>

<style>
  /* ============================
     Estilos modernos oscuros
     ============================ */
  html, body {
    margin: 0; padding: 0;
    background-color: #1e1f28; color: #fff;
    width:100%; height:100%; overflow-x:hidden;
    box-sizing: border-box;
  }
  .dashboard-contentPage {
    margin-left: 170px; padding: 30px;
    width: calc(100% - 170px);
    background-color: #1e1f28; min-height:100vh;
  }
  .page-header h1 {
    font-size: 28px; color: #00e5ff;
    text-shadow: 1px 1px 6px #000;
    margin-bottom: 10px;
  }
  .lead {
    font-size: 1.1rem; color: #ccc;
    margin-bottom: 30px;
  }
  .panel {
    background: #2c2d3f; border-radius:12px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.5);
    border: 1px solid #3c3d4f; margin-bottom:1rem;
  }
  .panel-heading {
    background: #00bcd4 !important; color:#fff;
    font-weight:bold; font-size:17px;
    text-align:center; padding:12px 15px;
    border-top-left-radius:12px; border-top-right-radius:12px;
  }
  .panel-body {
    padding:20px;
  }
  .form-control, .control-label {
    background: rgba(239,235,235,0.05) !important;
    border: 1px solid #555 !important;
    color: #fff !important;
  }
  .form-group.label-floating label {
    color: #ccc;
  }
  fieldset, legend {
    border: none; padding: 0; margin-bottom:20px;
    color: #efebeb;
  }
  .btn-info, .btn-success, .btn-danger {
    font-weight:bold; color:#fff !important;
  }
  .btn-info {
    background-color:#0288d1 !important; border:1px solid #0277bd !important;
  }
  .btn-success {
    background-color:#43a047 !important; border:1px solid #388e3c !important;
  }
  .btn-danger {
    background-color:#d32f2f !important; border:1px solid #b71c1c !important;
  }
  .btn-info:hover { background-color:#039be5 !important; }
  .btn-success:hover { background-color:#4caf50 !important; }
  .btn-danger:hover  { background-color:#e53935 !important; }
  .pregunta-block {
    margin-bottom: 1.5rem; padding: 1rem; background: rgba(255,255,255,0.05); border-radius: 8px;
  }
  .pregunta-block h5 {
    color: #29b6f6; margin-bottom: 0.75rem;
  }
  .opcion-inline {
    display: block; padding: 8px 12px; margin-bottom: 6px;
    background: #333; border-radius: 4px; color: #fff;
    cursor: pointer;
    transition: background 0.2s;
  }
  .opcion-inline:hover {
    background: #444;
  }
  .form-check {
    margin-bottom: 8px;
  }
  .form-check input[type="radio"] {
    margin-right: 8px;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-assignment"></i>
        Evaluación <small>Sesión <?= htmlspecialchars($sesionId) ?></small>
      </h1>
    </div>
    <p class="lead">
      Aquí puedes <?= $evalId>0 ? 'editar' : 'crear' ?> la evaluación para esta sesión.
    </p>
    <?= $alert ?>
  </div>

  <div class="container-fluid">
    <div class="panel panel-info">
      <div class="panel-heading">
        <h3 class="panel-title">
          <i class="zmdi zmdi-plus-circle"></i>
          <?= $evalId>0 ? 'Editar evaluación' : 'Nueva evaluación' ?>
        </h3>
      </div>
      <div class="panel-body">
        <form method="POST" autocomplete="off">
          <!-- Campo oculto para indicar edición -->
          <?php if ($evalId > 0): ?>
            <input type="hidden" name="edit_eval" value="1">
          <?php endif; ?>

          <fieldset>
            <legend><i class="zmdi zmdi-edit"></i> Título de la evaluación</legend>
            <div class="form-group label-floating">
              <label class="control-label">Título *</label>
              <input type="text" name="titulo" class="form-control" required
                     value="<?= htmlspecialchars($tituloActual) ?>">
            </div>
          </fieldset>

          <fieldset id="preguntas-container">
            <legend><i class="zmdi zmdi-help"></i> Preguntas y opciones *</legend>
            <?php
            // Si ya hay preguntas cargadas, renderizar bloques con datos
            if ($evalId > 0 && !empty($preguntasOpciones)):
              foreach ($preguntasOpciones as $preguntaId => $datos):
                $textoPregunta = htmlspecialchars($datos['TextoPregunta']);
                $opciones = $datos['Opciones'];
                // Encontrar índice de la opción correcta en este arreglo
                $correctaIndex = 0;
                foreach ($opciones as $idx => $op) {
                  if ($op['EsCorrecta'] == 1) {
                    $correctaIndex = $idx;
                    break;
                  }
                }
            ?>
              <div class="pregunta-block">
                <div class="form-group label-floating">
                  <label class="control-label">Pregunta *</label>
                  <input type="text" name="pregunta[]" class="form-control pregunta-text"
                         required value="<?= $textoPregunta ?>">
                </div>
                <div class="row opciones-wrapper">
                  <?php foreach ($opciones as $j => $op): ?>
                    <div class="col-sm-6">
                      <div class="form-group label-floating">
                        <label class="control-label">Opción <?= $j+1 ?> *</label>
                        <input type="text" name="opcion_texto[<?= $preguntaId ?>][]" class="form-control opcion-text"
                               required value="<?= htmlspecialchars($op['TextoOpcion']) ?>">
                      </div>
                    </div>
                  <?php endforeach; ?>

                  <!-- Para indicar cuál es la correcta -->
                  <div class="col-xs-12">
                    <div class="form-group">
                      <label>Índice de opción correcta (0-basado):</label>
                      <input type="number"
                             name="correcta[<?= $preguntaId ?>]"
                             class="form-control"
                             min="0"
                             max="<?= count($opciones)-1 ?>"
                             value="<?= $correctaIndex ?>">
                      <small class="help-block">
                        Ingresa el número 0 para la primera opción, 1 para la segunda, etc.
                      </small>
                    </div>
                  </div>
                </div>
                <!-- Botón para eliminar bloque de pregunta (front-end) -->
                <button type="button" class="btn btn-danger btn-xs borra-pregunta">
                  <i class="zmdi zmdi-delete"></i> Borrar esta pregunta
                </button>
              </div>
            <?php
              endforeach;
            endif;
            ?>
          </fieldset>

          <!-- Botón para agregar nueva pregunta (se duplicará un bloque en blanco) -->
          <p class="text-center" style="margin-bottom:1rem;">
            <button type="button" id="btnAgregarPregunta" class="btn btn-info btn-sm">
              <i class="zmdi zmdi-plus"></i> Agregar otra pregunta
            </button>
          </p>

          <p class="text-center">
            <?php if ($evalId > 0): ?>
              <button type="submit" class="btn btn-success btn-sm">
                <i class="zmdi zmdi-refresh"></i> Actualizar evaluación
              </button>
              <button type="submit" name="delete_eval" class="btn btn-danger btn-sm"
                      onclick="return confirm('¿Eliminar esta evaluación? Esto borrará también todas sus preguntas y opciones.');">
                <i class="zmdi zmdi-delete"></i> Eliminar evaluación
              </button>
            <?php else: ?>
              <button type="submit" class="btn btn-success btn-sm">
                <i class="zmdi zmdi-floppy"></i> Guardar evaluación
              </button>
            <?php endif; ?>
          </p>
        </form>
      </div>
    </div>
  </div>
</section>

<script>
// ==============================
// Scripts JS mínimos para manejar
// adición/borrado de preguntas
// ==============================
document.addEventListener("DOMContentLoaded", function(){
  const contenedor = document.getElementById('preguntas-container');

  // Función auxiliar para crear un bloque de pregunta en blanco
  function crearBloquePregunta() {
    const div = document.createElement('div');
    div.classList.add('pregunta-block');
    div.innerHTML = `
      <div class="form-group label-floating">
        <label class="control-label">Pregunta *</label>
        <input type="text" name="pregunta[]" class="form-control pregunta-text" required>
      </div>
      <div class="row opciones-wrapper">
        ${[0,1].map((j) => `
          <div class="col-sm-6">
            <div class="form-group label-floating">
              <label class="control-label">Opción ${j+1} *</label>
              <input type="text"
                     name="opcion_texto[][${j}]"
                     class="form-control opcion-text" required>
            </div>
          </div>
        `).join('')}
        <div class="col-xs-12">
          <div class="form-group">
            <label>Índice de opción correcta (0-basado):</label>
            <input type="number" name="correcta[]" class="form-control" min="0" max="1" value="0">
            <small class="help-block">
              Ingresa 0 para la primera opción, 1 para la segunda.
            </small>
          </div>
        </div>
      </div>
      <button type="button" class="btn btn-danger btn-xs borra-pregunta">
        <i class="zmdi zmdi-delete"></i> Borrar esta pregunta
      </button>
    `;
    return div;
  }

  // Agregar una nueva pregunta vacía
  document.getElementById('btnAgregarPregunta').addEventListener('click', function(){
    contenedor.appendChild(crearBloquePregunta());
  });

  // Delegación: si se hace click en “borrar pregunta”, remover el bloque
  contenedor.addEventListener('click', function(e){
    if (e.target.closest('.borra-pregunta')) {
      const bloque = e.target.closest('.pregunta-block');
      bloque.remove();
    }
  });
});
</script>
