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
  :root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(174,12,12,0.61);
    --text-light:       #FFFFFF;
    --hover-accent:     rgba(209,177,110,0.2);
  }
  html, body {
    margin: 0; padding: 0;
    width: 100%; height: 100%;
    background: var(--primary-bg);
    color: var(--text-light);
    overflow-x: hidden;
    box-sizing: border-box;
    font-family: 'RobotoCondensed', sans-serif;
  }
  /* Banner logo de fondo */
  .dashboard-banner {
    position: fixed; top: 0; left: 270px;
    width: calc(100% - 270px); height: 100%;
    background: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
    opacity: 0.05; pointer-events: none; z-index: 0;
  }
  /* Contenedor principal */
  .dashboard-contentPage {
    position: relative; z-index: 1;
    margin-left: 180px;
    width: calc(100% - 270px);
    padding: 0 30px auto;
    box-sizing: border-box;
  }
  /* Ocultar buscador y menús innecesarios */
  .btn-options,
  .dropdown-toggle,
  .btn-search,
  i.zmdi-zmdi-search,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }
  .page-header h1 {
    font-size: 2rem;
    color: var(--primary-accent);
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
    margin-bottom: 1rem;
    text-align: center;
  }
  .lead {
    text-align: center;
    font-size: 1.1rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: 2rem;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
  }
  .btn-back-home {
    background: var(--primary-accent) !important;
    color: var(--primary-bg) !important;
    border: none !important;
    border-radius: .3rem;
    padding: .5rem 1rem;
    font-size: .9rem;
    text-decoration: none;
    display: inline-block;
    margin-bottom: 1rem;
  }
  .btn-back-home:hover {
    background: var(--hover-accent) !important;
    color: var(--primary-bg) !important;
  }
  .panel {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    margin-bottom: 2rem;
    overflow: hidden;
  }
  .panel-heading {
    background: var(--primary-accent) !important;
    color: var(--primary-bg);
    padding: 1rem;
    font-weight: bold;
    text-align: center;
    font-size: 1.1rem;
  }
  .panel-body {
    padding: 1.5rem;
  }
  .table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1rem;
  }
  .table th, .table td {
    padding: .75rem;
    text-align: center;
    border: 1px solid rgba(255,255,255,0.1);
    color: var(--text-light);
  }
  .table-hover tbody tr:hover {
    background: rgba(255,255,255,0.05);
  }
select.form-control {
  background: var(--primary-bg);
  border: 1px solid var(--primary-accent) !important;
  color: var(--text-light) !important;
  border-radius: .3rem;
  padding: .4rem;
  /* opcional: eliminar el 'glow' nativo en focus */
  outline: none;
  box-shadow: none;
}
select.form-control option {
  background: var(--primary-bg) !important;
  color: var(--text-light) !important;
} 
  button[type="submit"].btn {
    background: var(--primary-accent) !important;
    color: var(--primary-bg) !important;
    border: none;
    border-radius: .3rem;
    padding: .6rem 1.2rem;
    font-weight: bold;
  }
  button[type="submit"].btn:hover {
    background: var(--hover-accent) !important;
  }
</style>

<div class="dashboard-banner"></div>
<section class="dashboard-contentPage">
  <div class="container-fluid">
    <a href="<?= SERVERURL ?>sesion/<?= $sesion['CursoId']; ?>/" class="btn-back-home">
      <i class="zmdi zmdi-arrow-left"></i> Mis Sesiones
    </a>
    <div class="page-header">
      <h1><i class="zmdi zmdi-check-circle"></i> Asistencia</h1>
      <small>Sesión: <?= htmlspecialchars($sesion['Titulo']); ?></small>
    </div>
    <p class="lead">
      <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
        Registro de asistencia de estudiantes. Seleccione el estado y guarde.
      <?php else: ?>
        Visualiza tu estado de asistencia aquí.
      <?php endif; ?>
    </p>
  </div>

  <?php if ($alert): ?>
    <div class="container-fluid">
      <?= $alert ?>
    </div>
  <?php endif; ?>

  <div class="container-fluid">
    <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
      <form method="POST">
        <div class="panel">
          <div class="panel-heading">
            Registro de Asistencia
          </div>
          <div class="panel-body">
            <?php if (empty($students)): ?>
              <p class="text-center">No hay estudiantes inscritos.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Código</th>
                      <th>Estudiante</th>
                      <th>Estado</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $i = 1; foreach($students as $row): ?>
                      <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['Codigo']) ?></td>
                        <td><?= htmlspecialchars("{$row['Nombres']} {$row['Apellidos']}") ?></td>
                        <td>
                          <?php $est = $row['estado']; ?>
                          <select name="asistencia[<?= $row['Codigo'] ?>]" class="form-control">
                            <option value="presente"   <?= $est==='presente'   ? 'selected':'' ?>>Presente</option>
                            <option value="ausente"     <?= $est==='ausente'     ? 'selected':'' ?>>Ausente</option>
                            <option value="justificado" <?= $est==='justificado' ? 'selected':'' ?>>Justificado</option>
                          </select>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <div class="text-center">
                <button type="submit" class="btn"><i class="zmdi zmdi-floppy"></i> Guardar Asistencia</button>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </form>
    <?php else: ?>
      <div class="panel">
        <div class="panel-heading">Tu Asistencia</div>
        <div class="panel-body text-center">
          <?php
            $codigo = $_SESSION['userKey'] ?? '';
            $status = $insAsist
              ->get_attendance_status_student_controller($sesionId, $codigo)
              ?? 'ausente';
          ?>
          <p class="lead">
            Tu estado es: <strong><?= ucfirst($status) ?></strong>
          </p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
