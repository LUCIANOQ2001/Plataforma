<?php
// File: views/contents/evaluacion-view.php

// 1) Sólo Admin/Docente pueden ver y crear esta vista
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/evaluacionController.php';
require_once __DIR__ . '/../../controllers/sesionController.php'; // para obtener nombre de la sesión (opcional)
$insEval   = new evaluacionController();
$insSesion = new sesionController();

$userType  = $_SESSION['userType'];
$parts     = explode("/", trim($_GET['views'], "/"));
$sesionId  = intval($parts[1]);

// 2) Obtener datos de la sesión (sólo para mostrar el nombre de la sesión arriba)
$stmtSesion = $insSesion->get_sesion_by_id_controller($sesionId);
if ($stmtSesion->rowCount() === 0) {
    echo '<div class="alert alert-danger text-center">Sesión no encontrada.</div>';
    return;
}
$sesion = $stmtSesion->fetch(PDO::FETCH_ASSOC);

// 3) Procesar POST si viene por POST
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alert = $insEval->add_evaluacion_controller($sesionId, $_POST);
    // PRG (Post-Redirect-Get) para evitar reenvío
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}

// 4) Listar evaluaciones ya existentes para esta sesión (opcional, para mostrar abajo)
$evaluaciones = $insEval->list_evaluaciones_controller($sesionId);
?>

<style>
  /* ===== Estilos generales para “tema oscuro” ===== */
  html, body {
    margin: 0; padding: 0; width: 100%; height: 100%;
    background-color: #1e1f28; color: #fff;
    overflow-x: hidden; box-sizing: border-box;
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
    font-size: 1.1rem; color: #ccc; margin-bottom: 30px;
  }
  .panel {
    background: #2c2d3f; border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.5);
    border: 1px solid #3c3d4f; color: #fff;
  }
  .panel-heading {
    background: #00bcd4 !important; color: #fff;
    font-weight: bold; font-size: 17px; text-align: center;
    padding: 12px 15px;
    border-top-left-radius: 12px; border-top-right-radius: 12px;
  }
  .panel-body {
    padding: 20px;
  }
  .form-control, .control-label {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid #555 !important; color: #fff !important;
  }
  fieldset, legend {
    border: none; padding: 0; margin-bottom: 20px;
    color: #efebeb;
  }
  .btn {
    border-radius: 4px; transition: opacity .3s;
  }
  .btn:hover { opacity: 0.9; }
  .btn-info { background-color: #0288d1; border: 1px solid #0277bd; color: #fff; }
  .btn-success { background-color: #43a047; border: 1px solid #388e3c; color: #fff; }
  .btn-warning { background-color: #f57c00; border: 1px solid #ef6c00; color: #fff; }

  /* ===== Estilo de cada bloque de pregunta ===== */
  .question-block {
    background: #33344c;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    position: relative;
  }
  .question-block .remove-question {
    position: absolute;
    top: 10px; right: 10px;
    color: #ff5252; cursor: pointer;
  }
  .question-block legend {
    font-size: 16px; color: #ffd740; margin-bottom: 10px;
  }
  .options-group {
    margin-left: 20px;
    margin-top: 10px;
  }
  .options-group .option-item {
    margin-bottom: 8px;
  }
  .options-group .option-item input[type="text"] {
    width: 70%;
    display: inline-block;
    margin-right: 8px;
  }
  .options-group .option-item label {
    margin-right: 4px; color: #ccc;
  }
  .add-question-btn {
    margin-bottom: 20px;
  }

  /* ===== Listado de evaluaciones creadas ===== */
  .evaluaciones-list {
    margin-top: 40px;
  }
  .evaluaciones-list table {
    width: 100%; border-collapse: collapse;
  }
  .evaluaciones-list table th,
  .evaluaciones-list table td {
    padding: 10px; border: 1px solid #444; color: #fff;
  }
  .evaluaciones-list table th {
    background: #222; color: #ddd;
  }
  .evaluaciones-list table tr:nth-child(even) {
    background: #2a2c3b;
  }
  .evaluaciones-list a {
    color: #03a9f4; text-decoration: none;
  }
  .evaluaciones-list a:hover {
    text-decoration: underline;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-assignment"></i>
        Crear Evaluación para Sesión:
        <small><?php echo htmlspecialchars($sesion['Titulo']); ?></small>
      </h1>
    </div>
    <p class="lead">
      Aquí puedes programar la evaluación (fecha de inicio, fecha de cierre, intentos, duración) 
      y escribir todas las preguntas con sus opciones de respuesta. 
      Marca en cada grupo de opciones cuál es la correcta.
    </p>
    <?php echo $alert; ?>
  </div>

  <div class="container-fluid">
    <div class="panel panel-info">
      <div class="panel-heading">
        <h3 class="panel-title">
          <i class="zmdi zmdi-plus-circle"></i> Nueva Evaluación
        </h3>
      </div>
      <div class="panel-body">
        <form method="POST" autocomplete="off" id="evaluationForm">
          <fieldset>
            <legend><i class="zmdi zmdi-edit"></i> Datos de la Evaluación</legend>
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Título *</label>
                  <input type="text" name="titulo" class="form-control" required maxlength="255">
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Intentos Permitidos</label>
                  <input type="number" name="intentos" class="form-control" value="1" min="1">
                  <small class="help-block">Cuántas veces puede intentar el estudiante</small>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Fecha de Inicio *</label>
                  <input type="datetime-local" name="fechainicio" class="form-control" required>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Fecha de Cierre *</label>
                  <input type="datetime-local" name="fechacierre" class="form-control" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Duración (minutos)</label>
                  <input type="number" name="duracion" class="form-control" value="0" min="0">
                  <small class="help-block">Tiempo máximo para resolver (en minutos)</small>
                </div>
              </div>
            </div>
          </fieldset>

          <hr style="border-color:#444; margin:20px 0;">

          <fieldset>
            <legend><i class="zmdi zmdi-help-outline"></i> Preguntas y Opciones</legend>
            <p class="text-muted">
              Haz clic en “Agregar pregunta” para cada bloque de pregunta. 
              Luego escribe el texto de la pregunta y las cuatro opciones. 
              Marca la casilla (*) de la opción correcta.
            </p>
            <button type="button" class="btn btn-warning add-question-btn">
              <i class="zmdi zmdi-plus"></i> Agregar Pregunta
            </button>

            <!-- Contenedor donde se irán añadiendo los bloques de preguntas -->
            <div id="questions-container">
              <!-- Al cargar por primera vez, agregamos un bloque de “Pregunta #1” por defecto -->
              <div class="question-block" data-index="0">
                <span class="remove-question" title="Eliminar esta pregunta">&times;</span>
                <legend>Pregunta 1</legend>
                <div class="form-group">
                  <label class="control-label">Texto de la pregunta *</label>
                  <textarea name="questions[0][texto]" class="form-control" rows="2" required></textarea>
                </div>
                <div class="options-group">
                  <!-- Cuatro opciones (A, B, C, D) -->
                  <?php for ($opt = 0; $opt < 4; $opt++): 
                          $letra = chr(ord('A') + $opt);
                        ?>
                  <div class="option-item">
                    <label>
                      <input type="radio" 
                             name="questions[0][correcta]" 
                             value="<?php echo $opt; ?>"
                             <?php echo ($opt === 0) ? 'checked' : ''; ?>
                      >
                      Opción <?php echo $letra; ?>
                    </label>
                    <input 
                      type="text" 
                      name="questions[0][opciones][<?php echo $opt; ?>][texto]" 
                      class="form-control" 
                      placeholder="Texto de la opción <?php echo $letra; ?>" 
                      required
                    >
                  </div>
                  <?php endfor; ?>
                </div>
              </div>
            </div>
          </fieldset>

          <p class="text-center" style="margin-top: 20px;">
            <button type="submit" class="btn btn-success btn-raised btn-sm">
              <i class="zmdi zmdi-floppy"></i> Guardar Evaluación
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>

  <!-- 5) Listado de Evaluaciones existentes para esta sesión -->
  <div class="container-fluid evaluaciones-list">
    <h3><i class="zmdi zmdi-format-list-bulleted"></i> Evaluaciones creadas</h3>
    <?php if (empty($evaluaciones)): ?>
      <p>No hay evaluaciones registradas para esta sesión.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Título</th>
            <th>Fecha Creación</th>
            <th>Fecha Inicio</th>
            <th>Fecha Cierre</th>
            <th>Intentos</th>
            <th>Duración (min)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($evaluaciones as $i => $ev): ?>
            <tr>
              <td><?php echo $i + 1; ?></td>
              <td><?php echo htmlspecialchars($ev['Titulo']); ?></td>
              <td><?php echo date("d/m/Y H:i", strtotime($ev['FechaCreacion'])); ?></td>
              <td><?php echo date("d/m/Y H:i", strtotime($ev['FechaInicio'])); ?></td>
              <td><?php echo date("d/m/Y H:i", strtotime($ev['FechaCierre'])); ?></td>
              <td><?php echo intval($ev['IntentosPermitidos']); ?></td>
              <td><?php echo intval($ev['DuracionMinutos']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>

<script>
  /*
   * JavaScript para agregar/eliminar dinámicamente bloques de pregunta
   */
  document.addEventListener("DOMContentLoaded", function() {
    let questionCount = 1; // Ya hay 1 bloque inicial con data-index="0"

    // Función para crear un nuevo bloque de pregunta
    function createQuestionBlock(index) {
      const letraOpciones = ['A','B','C','D'];
      const bloque = document.createElement("div");
      bloque.classList.add("question-block");
      bloque.setAttribute("data-index", index);

      // Título y botón de eliminar
      bloque.innerHTML = `
        <span class="remove-question" title="Eliminar esta pregunta">&times;</span>
        <legend>Pregunta ${index + 1}</legend>
        <div class="form-group">
          <label class="control-label">Texto de la pregunta *</label>
          <textarea name="questions[${index}][texto]" class="form-control" rows="2" required></textarea>
        </div>
        <div class="options-group">
          ${letraOpciones.map((letra, optIdx) => `
            <div class="option-item">
              <label>
                <input type="radio" name="questions[${index}][correcta]" value="${optIdx}" ${optIdx === 0 ? 'checked' : ''}>
                Opción ${letra}
              </label>
              <input 
                type="text" 
                name="questions[${index}][opciones][${optIdx}][texto]" 
                class="form-control" 
                placeholder="Texto de la opción ${letra}" 
                required
              >
            </div>
          `).join("")}
        </div>
      `;
      return bloque;
    }

    // Agregar evento al botón “Agregar Pregunta”
    document.querySelector(".add-question-btn").addEventListener("click", function() {
      const contenedor = document.getElementById("questions-container");
      const nuevoBloque = createQuestionBlock(questionCount);
      contenedor.appendChild(nuevoBloque);
      questionCount++;
    });

    // Delegación para “click” en el icono de eliminar bloque
    document.getElementById("questions-container").addEventListener("click", function(e) {
      if (e.target.classList.contains("remove-question")) {
        const bloque = e.target.closest(".question-block");
        bloque.parentNode.removeChild(bloque);
        // Para mantener los índices en orden (opcional), podríamos reajustar data-index y nombres,
        // pero para simplificar, asumimos que no es imprescindible reordenar. 
      }
    });
  });
</script>
