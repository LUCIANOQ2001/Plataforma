<?php
// File: views/contents/evaluacion-student-view.php

// 1) Sólo usuarios autenticados (Admin, Docente o Estudiante)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente','Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

// 2) Controladores necesarios
require_once __DIR__ . '/../../controllers/cursoController.php';
require_once __DIR__ . '/../../controllers/sesionController.php';
require_once __DIR__ . '/../../controllers/evaluacionController.php'; // <--- IMPORTANTE: controlador de evaluaciones

$insCurso      = new cursoController();
$insSesion     = new sesionController();
$insEvaluacion = new evaluacionController();
$userType      = $_SESSION['userType'];

// 3) ID de curso/ID de sesión por URL: /evaluacion/{sesionId}/estudiante/
$parts    = explode("/", trim($_GET['views'], "/"));
$sesionId = intval($parts[1]);

// 4) Obtener datos del curso y la sesión (para mostrar encabezados)
$stmtSesion = $insSesion->get_sesion_by_id_controller($sesionId);
if ($stmtSesion->rowCount() === 0) {
    echo '<div class="alert alert-danger text-center">Sesión no encontrada.</div>';
    return;
}
$sesion = $stmtSesion->fetch(PDO::FETCH_ASSOC);

// 5) Listar evaluaciones de esta sesión para que el estudiante elija
//    FIRMA CORRECTA: list_evaluaciones_controller
$evaluaciones = $insEvaluacion->list_evaluaciones_controller($sesionId);
?>

<style>
  /* ===== Estilos generales para tema oscuro ===== */
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
  .evaluaciones-list a {
    color: #03a9f4; text-decoration: none;
  }
  .evaluaciones-list a:hover {
    text-decoration: underline;
  }
  .btn-entrar {
    background-color: rgba(126, 175, 129, 0.32);
    border: 1px solid #388e3c;
    color: #fff;
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
  }
  .btn-entrar:hover {
    opacity: 0.9;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-assignment"></i>
        Evaluaciones disponibles para:
        <small><?php echo htmlspecialchars($sesion['Titulo']); ?></small>
      </h1>
    </div>
    <p class="lead">
      A continuación verás todas las evaluaciones que el docente ha creado para esta sesión.
      Si la fecha de inicio y cierre está dentro del intervalo, podrás “Entrar a la evaluación”.
    </p>
  </div>

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
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($evaluaciones as $i => $ev): 
              // Determinar si “está activa” revisando fechas
              $ahora      = new DateTime();
              $fInicio    = new DateTime($ev['FechaInicio']);
              $fCierre    = new DateTime($ev['FechaCierre']);
              $estaActiva = ($ahora >= $fInicio && $ahora <= $fCierre);
          ?>
            <tr>
              <td><?php echo $i + 1; ?></td>
              <td><?php echo htmlspecialchars($ev['Titulo']); ?></td>
              <td><?php echo date("d/m/Y H:i", strtotime($ev['FechaInicio'])); ?></td>
              <td><?php echo date("d/m/Y H:i", strtotime($ev['FechaCierre'])); ?></td>
              <td><?php echo intval($ev['IntentosPermitidos']); ?></td>
              <td><?php echo intval($ev['DuracionMinutos']); ?> min</td>
              <td>
                <?php if ($estaActiva): ?>
                  <!--
                    IMPORTANTE: aquí el href debe terminar en “/” para que
                    viewsModel.php reconozca la ruta “evaluacion/{$sesionId}/estudiante/{$ev['id']}/”
                  -->
                  <a 
                    href="<?php echo SERVERURL . "evaluacion/{$sesionId}/estudiante/{$ev['id']}/"; ?>" 
                    class="btn-entrar"
                  >
                    Entrar
                  </a>
                <?php else: ?>
                  <span style="color:#aaa;">Cerrada</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>
