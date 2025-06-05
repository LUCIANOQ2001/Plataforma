<?php
// File: views/contents/evaluacion-view.php

// 1) Sólo Docentes (y Administradores) pueden acceder
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

$insSesion     = new sesionController();
$insEvaluacion = new evaluacionController();
$userType      = $_SESSION['userType'];

// 3) ID de la sesión (por URL: /evaluacion/{sesionId}/)
$parts   = explode("/", trim($_GET['views'], "/"));
$sesionId= intval($parts[1]);

// 4) Obtener datos de la sesión y curso (para encabezado)
$stmtSesion = $insSesion->get_sesion_by_id_controller($sesionId);
if ($stmtSesion->rowCount() === 0) {
    echo '<div class="alert alert-danger text-center">Sesión no encontrada.</div>';
    return;
}
$sesion = $stmtSesion->fetch(PDO::FETCH_ASSOC);

// 5) Procesar POST: creación de evaluación
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alert = $insEvaluacion->add_evaluacion_controller($sesionId, $_POST);
    // Evitar reenvío de formulario
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}

// 6) Listar evaluaciones existentes para esta sesión
$evaluaciones = $insEvaluacion->list_evaluaciones_controller($sesionId);
?>

<style>
  /* ===== Tema oscuro unificado ===== */
  html, body {
    margin: 0; padding: 0;
    background-color: #1e1f28; color: #fff;
    width: 100%; height: 100%;
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
    background: #2c2d3f;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
    border: 1px solid #3c3d4f;
  }
  .panel-heading {
    background-color: #43a047 !important;
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
  .form-control, .control-label {
    background: rgba(239, 235, 235, 0.05) !important;
    border: 1px solid #555 !important;
    color: #fff !important;
  }
  fieldset, legend {
    border: none; padding: 0; margin-bottom: 20px; color: #efebeb;
  }
  .evaluaciones-list {
    margin-top: 30px;
  }
  .evaluaciones-list table {
    width: 100%; border-collapse: collapse;
  }
  .evaluaciones-list th, .evaluaciones-list td {
    padding: 10px; border: 1px solid #444; color: #fff;
  }
  .evaluaciones-list th {
    background: #222; color: #ddd;
  }
  .evaluaciones-list tr:nth-child(even) {
    background: #2a2c3b;
  }
  .btn-primary {
    background-color: #0288d1 !important;
    border-color: #0277bd !important;
    color: #fff !important;
  }
  .btn-primary:hover {
    background-color: #039be5 !important;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-assignment"></i>
        Evaluaciones para:
        <small><?php echo htmlspecialchars($sesion['Titulo']); ?></small>
      </h1>
    </div>
    <p class="lead">
      Desde aquí puedes crear nuevas evaluaciones para esta sesión. 
      Completa título, fechas, intentos y duración, y luego agrega preguntas y opciones.
    </p>
    <?php echo $alert; ?>
  </div>

  <!-- Formulario para crear nueva evaluación -->
  <div class="container-fluid">
    <div class="panel panel-info">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="zmdi zmdi-plus-circle"></i> Crear Evaluación</h3>
      </div>
      <div class="panel-body">
        <form method="POST" autocomplete="off">
          <fieldset>
            <legend><i class="zmdi zmdi-label"></i> Metadatos de la Evaluación</legend>
            <div class="row">
              <div class="col-sm-4">
                <div class="form-group label-floating">
                  <label class="control-label">Título *</label>
                  <input type="text" name="titulo" class="form-control" required>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="form-group label-floating">
                  <label class="control-label">Fecha Inicio *</label>
                  <input type="datetime-local" name="fechainicio" class="form-control" required>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="form-group label-floating">
                  <label class="control-label">Fecha Cierre *</label>
                  <input type="datetime-local" name="fechacierre" class="form-control" required>
                </div>
              </div>
              <div class="col-sm-3">
                <div class="form-group label-floating">
                  <label class="control-label">Intentos Permitidos *</label>
                  <input type="number" name="intentos" class="form-control" min="1" value="1" required>
                </div>
              </div>
              <div class="col-sm-3">
                <div class="form-group label-floating">
                  <label class="control-label">Duración (min) *</label>
                  <input type="number" name="duracion" class="form-control" min="1" value="5" required>
                </div>
              </div>
            </div>
          </fieldset>

          <!-- Aquí puedes agregar dinámicamente preguntas y opciones con JavaScript. 
               Por simplicidad, mostramos 3 preguntas estáticas, cada una con 4 opciones. (Puedes adaptarlo a tu necesidad). -->
          <fieldset>
            <legend><i class="zmdi zmdi-help-outline"></i> Preguntas y Opciones</legend>

            <!-- Pregunta 1 -->
            <div class="pregunta-block" style="margin-bottom:1rem; padding:10px; background:#2a2c3b; border-radius:8px;">
              <div class="form-group label-floating">
                <label class="control-label">Pregunta 1 *</label>
                <input type="text" name="questions[0][texto]" class="form-control" required>
              </div>
              <!-- Opciones 1 -->
              <div>
                <label class="control-label">Opciones *</label>
                <div class="form-group">
                  <input type="radio" name="questions[0][correcta]" value="0"> 
                  <input type="text" name="questions[0][opciones][0][texto]" class="form-control" placeholder="Opción A" required>
                </div>
                <div class="form-group">
                  <input type="radio" name="questions[0][correcta]" value="1"> 
                  <input type="text" name="questions[0][opciones][1][texto]" class="form-control" placeholder="Opción B" required>
                </div>
                <div class="form-group">
                  <input type="radio" name="questions[0][correcta]" value="2"> 
                  <input type="text" name="questions[0][opciones][2][texto]" class="form-control" placeholder="Opción C" required>
                </div>
                <div class="form-group">
                  <input type="radio" name="questions[0][correcta]" value="3"> 
                  <input type="text" name="questions[0][opciones][3][texto]" class="form-control" placeholder="Opción D" required>
                </div>
              </div>
            </div>

            <!-- Pregunta 2 (mismo formato) -->
            <div class="pregunta-block" style="margin-bottom:1rem; padding:10px; background:#2a2c3b; border-radius:8px;">
              <div class="form-group label-floating">
                <label class="control-label">Pregunta 2</label>
                <input type="text" name="questions[1][texto]" class="form-control">
              </div>
              <div>
                <label class="control-label">Opciones</label>
                <div class="form-group">
                  <input type="radio" name="questions[1][correcta]" value="0"> 
                  <input type="text" name="questions[1][opciones][0][texto]" class="form-control" placeholder="Opción A">
                </div>
                <div class="form-group">
                  <input type="radio" name="questions[1][correcta]" value="1"> 
                  <input type="text" name="questions[1][opciones][1][texto]" class="form-control" placeholder="Opción B">
                </div>
                <div class="form-group">
                  <input type="radio" name="questions[1][correcta]" value="2"> 
                  <input type="text" name="questions[1][opciones][2][texto]" class="form-control" placeholder="Opción C">
                </div>
                <div class="form-group">
                  <input type="radio" name="questions[1][correcta]" value="3"> 
                  <input type="text" name="questions[1][opciones][3][texto]" class="form-control" placeholder="Opción D">
                </div>
              </div>
            </div>

            <!-- (agrega tantas preguntas como desees, o lo harás dinámico con JS) -->
          </fieldset>

          <p class="text-center">
            <button type="submit" class="btn btn-primary btn-raised btn-sm">
              <i class="zmdi zmdi-floppy"></i> Guardar Evaluación
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>

  <!-- Listado de evaluaciones creadas -->
  <div class="container-fluid evaluaciones-list">
    <?php if (empty($evaluaciones)): ?>
      <p>No hay evaluaciones creadas para esta sesión.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Título</th>
            <th>Fecha Inicio</th>
            <th>Fecha Cierre</th>
            <th>Intentos</th>
            <th>Duración</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($evaluaciones as $i => $ev): ?>
            <tr>
              <td><?php echo $i + 1; ?></td>
              <td><?php echo htmlspecialchars($ev['Titulo']); ?></td>
              <td><?php echo date("d/m/Y H:i", strtotime($ev['FechaInicio'])); ?></td>
              <td><?php echo date("d/m/Y H:i", strtotime($ev['FechaCierre'])); ?></td>
              <td><?php echo intval($ev['IntentosPermitidos']); ?></td>
              <td><?php echo intval($ev['DuracionMinutos']); ?> min</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>
