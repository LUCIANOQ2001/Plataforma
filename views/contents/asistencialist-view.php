<?php 
// views/contents/asistencialist-view.php

// Solo Estudiantes pueden acceder a este historial
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SESSION['userType'] !== "Estudiante") {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

// 1) Extraer el courseId de la URL: /asistencialist/{cursoId}/
$parts    = explode('/', trim($_GET['views'], '/'));
$cursoId  = intval($parts[1] ?? 0);

if ($cursoId <= 0) {
    echo '<div class="alert alert-danger text-center">Curso no válido.</div>';
    return;
}

require_once __DIR__ . '/../../controllers/asistenciaController.php';
require_once __DIR__ . '/../../controllers/cursoController.php';

// 2) Verificar que el estudiante esté inscrito en ese curso (opcional pero recomendado)
$insCurso = new cursoController();
$userKey  = $_SESSION['userKey'] ?? '';
$estaInscrito = $insCurso->is_estudiante_inscrito_en_curso_controller($userKey, $cursoId);

if (!$estaInscrito) {
    echo '<div class="alert alert-danger text-center">'
       . 'No estás inscrito en este curso.</div>';
    return;
}

// 3) Obtener listado de asistencias para este estudiante y este curso
$insAsist  = new asistenciaController();
$records   = $insAsist->get_history_by_student_course_controller($userKey, $cursoId);

// 4) Obtener nombre del curso para el encabezado
$cursoInfo = $insCurso->get_curso_by_id_controller($cursoId);
$cursoNombre = $cursoInfo['Nombre'] ?? '';

// (Si no tienes estos métodos en cursoController, crea uno que haga:
//    SELECT Nombre FROM curso WHERE id = ? )
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
  }

  .dashboard-contentPage {
    margin-left: 170px;
    padding: 30px 40px;
    background-color: #1e1f28;
    min-height: 100vh;
    box-sizing: border-box;
    max-width: calc(100vw - 170px);
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
    text-align: center;
    margin: 0 auto 30px auto;
    max-width: 760px;
  }

  .panel {
    background: #2c2d3f;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
    border: 1px solid #3c3d4f;
    max-width: 960px;
    margin: 0 auto 20px auto;
  }
  .btn-back-cursos {
    background-color: #607d8b !important;
    border-color:     #455a64 !important;
    color:            #fff !important;
    margin-bottom: 15px;
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

  .table-responsive {
    border-radius: 8px;
    overflow: hidden;
  }

  .table {
    width: 100%;
    margin-bottom: 0;
  }

  .table th, .table td {
    text-align: center;
    vertical-align: middle;
    background-color: transparent !important;
    color: #fff;
    border: 1px solid #444;
    padding: 10px;
  }

  .table-hover tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.05);
  }

  .label-success {
    background-color: #4caf50;
  }

  .label-danger {
    background-color: #f44336;
  }

  .label-warning {
    background-color: #ff9800;
  }
  
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid text-center">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-time"></i> Mis Asistencias  
        <small><?php echo htmlspecialchars($cursoNombre); ?></small>
      </h1>
    </div>
    <p class="lead">
      Aquí puedes revisar todas tus asistencias registradas para este curso.
    </p>
  </div>

    <!-- Botón Volver a Mis Cursos (para Docente y Estudiante) -->
    <?php if (in_array($_SESSION['userType'], ['Docente','Estudiante'])): ?>
      <a href="<?php echo SERVERURL; ?>miscursos/" 
         class="btn btn-back-cursos btn-sm">
        <i class="zmdi zmdi-arrow-left"></i> Volver a Mis Cursos
      </a>
    <?php endif; ?>

  <div class="container-fluid">
    <div class="panel panel-success">
      <div class="panel-heading">
        <i class="zmdi zmdi-format-list-bulleted"></i> Historial de Asistencias
      </div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-hover text-center">
            <thead>
              <tr>
                <th>#</th>
                <th>Sesión</th>
                <th>Fecha</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($records)): ?>
                <?php $i = 1; foreach ($records as $row): ?>
                  <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($row['Sesion']); ?></td>
                    <td><?php echo htmlspecialchars($row['Fecha']); ?></td>
                    <td>
                      <span class="label label-<?php 
                        echo $row['Estado']=='presente'   ? 'success' 
                             : ($row['Estado']=='ausente'     ? 'danger' : 'warning');
                      ?>">
                        <?php echo ucfirst($row['Estado']); ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4">No se encontraron registros de asistencia para este curso.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
