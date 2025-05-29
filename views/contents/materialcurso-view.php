<?php
// views/contents/materialcurso-view.php

if(session_status()===PHP_SESSION_NONE) session_start();
if(!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente','Estudiante'])){
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/cursoController.php';
require_once __DIR__ . '/../../controllers/sesionController.php';

$insCurso  = new cursoController();
$insSesion = new sesionController();

// extraemos el cursoId de la URL
$parts    = explode('/', trim($_GET['views'],'/'));
$cursoId  = intval($parts[1] ?? 0);

if($cursoId < 1){
    echo '<div class="alert alert-danger text-center">ID de curso inválido.</div>';
    return;
}

// obtenemos datos del curso
$stmtC = $insCurso->get_curso_by_id_controller($cursoId);
if($stmtC->rowCount()===0){
  echo '<div class="alert alert-danger text-center">Curso no encontrado.</div>';
  return;
}
$curso = $stmtC->fetch(PDO::FETCH_ASSOC);

// listamos sus sesiones
$sesiones = $insSesion->list_sesiones_controller($cursoId);
?>
<style>
  /* copia aquí los estilos de tu sesion-view.php adaptando .dashboard-contentPage */
  /* … */
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header text-center">
      <h1 class="text-titles">
        <i class="zmdi zmdi-collection-text"></i>
        Material de curso: <?php echo htmlspecialchars($curso['Nombre']); ?>
      </h1>
    </div>
    <p class="lead text-center">
      Aquí ves todas las sesiones de este curso. Selecciona una para ver su material.
    </p>
  </div>

  <div class="container-fluid">
    <?php if(empty($sesiones)): ?>
      <div class="alert alert-warning text-center">
        No hay sesiones definidas para este curso.
      </div>
    <?php else: ?>
      <div class="course-sessions">
        <?php foreach($sesiones as $s): ?>
          <div class="session-card">
            <div class="header">
              <?php echo htmlspecialchars($s['Titulo']); ?><br>
              <small><?php echo date("d/m/Y",strtotime($s['Fecha'])); ?></small>
            </div>
            <div class="body">
              <a href="<?php echo SERVERURL."material/{$s['id']}/"; ?>">
                <i class="zmdi zmdi-folder"></i> Ver material
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
