<?php
// views/contents/asistencia-view.php

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Control de acceso
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente','Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/asistenciaController.php';
require_once __DIR__ . '/../../controllers/sesionController.php';

$insAsist  = new asistenciaController();
$insSesion = new sesionController();

// Extraer ID de sesión de la URL (?views=asistencia/8)
$parts    = explode("/", trim($_GET['views'], "/"));
$sesionId = isset($parts[1]) ? intval($parts[1]) : 0;

// Validar existencia de la sesión
$stmtSes = $insSesion->get_sesion_by_id_controller($sesionId);
if ($stmtSes->rowCount() === 0) {
    echo '<div class="alert alert-danger text-center">Sesión no encontrada.</div>';
    return;
}
$sesion = $stmtSes->fetch(PDO::FETCH_ASSOC);

// Procesar POST (solo Admin/Docente)
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && in_array($_SESSION['userType'], ['Administrador','Docente'])) {

    $alert = $insAsist->save_attendance_by_session_controller(
        $sesionId,
        $_POST['asistencia'] ?? []
    );
}

// Cargar lista de estudiantes (solo Admin/Docente)
$students = [];
if (in_array($_SESSION['userType'], ['Administrador','Docente'])) {
    $students = $insAsist->get_students_by_session_controller($sesionId);
}
?>
<style>
  /* fondo completo */
  html, body {
    margin: 0; padding: 0;
    width: 100%; height: 100%;
    background-color: #1e1f28;
    overflow-x: hidden;
  }
  body { box-sizing: border-box; }

  .content-wrapper {
    margin-left: 245px;
    padding: 30px;
    background-color: #1e1f28;
    color: #fff;
    min-height: 100vh;
    width: calc(100% - 245px);
    overflow-x: hidden;
  }

  .page-header h1 {
    color: #00e5ff;
    text-shadow: 1px 1px 5px #000;
    font-size: 28px;
    margin-bottom: 20px;
  }
  .lead {
    color: #ccc;
    margin-bottom: 30px;
    font-size: 1.1rem;
  }
 .btn-back-home {
    background-color: #607d8b !important;
    border-color:     #455a64 !important;
    color:            #fff !important;
    margin-bottom: 20px;
  }

  .panel {  /* aquí es la tabla*/
    background-color: #2b2c3d !important;
    border: 1px solid #444;
    box-shadow: 0 4px 15px rgba(0,0,0,0.5);
    border-radius: 8px;
    margin-left: 180px; /* ajusta este valor a tu gusto */
  }
  .panel-heading {
    background-color: #43a047 !important;
    color: #fff;
    border-bottom: 1px solid #2e7d32;
    text-align: center;
  }
  .panel-title {
    font-size: 18px;
    font-weight: bold;
  }

  .table > thead > tr > th,
  .table > tbody > tr > td {
    background-color: transparent !important;
    color: #fff !important;
    border-color: #444;
    vertical-align: middle;
    
  }
  .table-hover > tbody > tr:hover {
    background-color: rgba(255,255,255,0.05);
  }

  .btn-info.btn-xs {
    padding: 2px 8px;
    font-size: 12px;
  }

 
</style>

<div class="content-wrapper">
  <!-- Encabezado -->
  <div class="container-fluid">
        <!-- Botón Volver a Sesiones -->
    <a href="<?php echo SERVERURL; ?>sesion/<?php echo $sesion['CursoId']; ?>/"
       class="btn btn-back-home btn-sm">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Sesiones
    </a>
    <div class="page-header text-center">
      <h1 class="text-titles">
        <i class="zmdi zmdi-check-circle zmdi-hc-fw"></i>
        Asistencia <small>Sesión: <?php echo htmlspecialchars($sesion['Titulo']); ?></small>
      </h1>
    </div>
    <p class="lead text-center">
      <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
        Aquí puede registrar la asistencia de cada estudiante. 
        Haga clic en <button class="btn btn-info btn-raised btn-xs">
          <i class="zmdi zmdi-floppy"></i>
        </button> para guardar.
      <?php else: ?>
        Aquí puede visualizar su estado de asistencia registrado.
      <?php endif; ?>
    </p>
  </div>

  <?php if ($alert): ?>
    <div class="container-fluid">
      <?php echo $alert; ?>
    </div>
  <?php endif; ?>

  <!-- Vista centrada -->
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-xs-12 col-sm-10 col-md-8">
        <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
          <form method="POST">
            <div class="panel panel-success">
              <div class="panel-heading">
                <h3 class="panel-title">
                  <i class="zmdi zmdi-format-list-bulleted"></i>
                  Registro de Asistencia
                </h3>
              </div>
              <div class="panel-body">
                <?php if (empty($students)): ?>
                  <p class="text-danger text-center">No hay estudiantes inscritos.</p>
                <?php else: ?>
                  <div class="table-responsive custom-table-wrapper">
                    <table class="table table-hover text-center">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Código</th>
                          <th>Estudiante</th>
                          <th>Estado</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $i=1; foreach($students as $row): ?>
                          <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['Codigo']); ?></td>
                            <td><?php echo htmlspecialchars("{$row['Nombres']} {$row['Apellidos']}"); ?></td>
                            <td>
                              <?php $est = $row['estado']; ?>
                              <select name="asistencia[<?php echo $row['Codigo']; ?>]" class="form-control">
                                <option value="presente"   <?php if($est==='presente')   echo 'selected'; ?>>Presente</option>
                                <option value="ausente"     <?php if($est==='ausente')     echo 'selected'; ?>>Ausente</option>
                                <option value="justificado" <?php if($est==='justificado') echo 'selected'; ?>>Justificado</option>
                              </select>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                  <div class="text-center">
                    <button type="submit" class="btn btn-info btn-raised">
                      <i class="zmdi zmdi-floppy"></i> Guardar Asistencia
                    </button>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </form>
          <?php else: ?>
            <div class="panel panel-info">
              <div class="panel-heading">
                <h3 class="panel-title">
                  <i class="zmdi zmdi-assignment-account"></i>
                  Tu Asistencia
                </h3>
              </div>
              <div class="panel-body text-center">
                <?php
                  // Antes: $codigo = $_SESSION['userCode'] ?? '';
                  // Ahora sí usamos la variable que efectivamente guardó loginController:
                  $codigo = $_SESSION['userKey'] ?? '';
                  $status = $insAsist
                    ->get_attendance_status_student_controller($sesionId, $codigo)
                    ?? 'ausente';
                ?>
                <p class="lead">
                  Tu estado de asistencia es: 
                  <strong><?php echo ucfirst($status); ?></strong>
                </p>
              </div>
            </div>
          <?php endif; ?>

      </div>
    </div>
  </div>
</div>
