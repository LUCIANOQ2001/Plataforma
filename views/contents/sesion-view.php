<?php
// views/contents/sesion-view.php

// Solo Administrador y Docente pueden entrar
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])) {
    $logout = new loginController();
    echo $logout->login_session_force_destroy_controller();
    exit;
}

// Controladores necesarios
require_once __DIR__ . '/../../controllers/cursoController.php';
require_once __DIR__ . '/../../controllers/sesionController.php';

$insCurso  = new cursoController();
$insSesion = new sesionController();

// ID de curso por URL: /sesion/{cursoId}/
$parts    = explode("/", $_GET['views']);
$cursoId  = intval($parts[1]);

// 1) Obtener datos del curso
$stmtCurso = $insCurso->get_curso_by_id_controller($cursoId);
if ($stmtCurso->rowCount() === 0) {
    echo '<div class="alert alert-danger">Curso no encontrado.</div>';
    return;
}
$curso = $stmtCurso->fetch(PDO::FETCH_ASSOC);

// 2) Procesar POST para creación de sesión (PRG)
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alert = $insSesion->add_sesion_controller($cursoId, $_POST);
    // redirigir para evitar reenvío
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}

// 3) Listar sesiones
$sesiones = $insSesion->list_sesiones_controller($cursoId);
?>

<style>
.dashboard-contentPage { margin-left:170px; padding:20px; }
.course-sessions { display:flex; gap:1rem; flex-wrap:wrap; }
.session-card {
  background:#fff; border-radius:6px; overflow:hidden;
  width:200px; box-shadow:0 2px 6px rgba(0,0,0,0.2);
}
.session-card .header {
  background:#b71c1c; color:#fff; padding:1rem;
  text-align:center;
}
.session-card .body {
  padding:0.8rem; font-size:0.9rem;
}
.session-card .body a {
  display:flex; align-items:center; margin:0.4rem 0;
  color:#333; text-decoration:none;
}
.session-card .body a i { margin-right:0.5rem; }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-play-circle"></i>
        Sesiones de: <?php echo htmlspecialchars($curso['Nombre']); ?>
      </h1>
    </div>
    <p class="lead">
      <?php echo htmlspecialchars($curso['Descripcion']); ?>
    </p>
    <?php echo $alert; ?>
  </div>

  <!-- Botón para crear nueva sesión -->
  <div class="container-fluid">
    <button class="btn btn-info btn-raised" 
            onclick="document.getElementById('newSessionForm').style.display='block'">
      <i class="zmdi zmdi-plus"></i> Nueva Sesión
    </button>
  </div>

  <!-- Formulario oculto inicialmente -->
  <div class="container-fluid" id="newSessionForm" style="display:none; margin-top:1rem;">
    <div class="panel panel-info">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="zmdi zmdi-plus-circle"></i> Crear Sesión</h3>
      </div>
      <div class="panel-body">
        <form method="POST" autocomplete="off">
          <div class="row">
            <div class="col-sm-4">
              <div class="form-group label-floating">
                <label class="control-label">Título *</label>
                <input type="text" name="titulo" class="form-control" required>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group label-floating">
                <label class="control-label">Fecha *</label>
                <input type="date" name="fecha" class="form-control" required>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group label-floating">
                <label class="control-label">Enlace/Video</label>
                <input type="text" name="video" class="form-control">
              </div>
            </div>
          </div>
          <p class="text-center">
            <button type="submit" class="btn btn-success btn-raised">
              <i class="zmdi zmdi-floppy"></i> Guardar Sesión
            </button>
            <button type="button" class="btn btn-default"
                    onclick="this.closest('#newSessionForm').style.display='none'">
              Cancelar
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>

  <!-- Listado de sesiones -->
  <div class="container-fluid">
    <div class="course-sessions">
      <?php if(empty($sesiones)): ?>
        <p>No hay sesiones aún. Crea la primera arriba.</p>
      <?php else: foreach($sesiones as $s): ?>
        <div class="session-card">
          <div class="header">
            <?php echo htmlspecialchars($s['Titulo']); ?><br>
            <small><?php echo date("d/m/Y",strtotime($s['Fecha'])); ?></small>
          </div>
          <div class="body">
            <a href="<?php echo SERVERURL."material/{$s['id']}/"; ?>">
              <i class="zmdi zmdi-collection-text"></i> Material
            </a>
            <a href="<?php echo SERVERURL."evaluacion/{$s['id']}/"; ?>">
              <i class="zmdi zmdi-assignment"></i> Evaluación
            </a>
            <a href="<?php echo $s['Video']; ?>" target="_blank">
              <i class="zmdi zmdi-videocam"></i> Video Conferencia
            </a>
            <a href="<?php echo SERVERURL."grabaciones/{$s['id']}/"; ?>">
              <i class="zmdi zmdi-movie"></i> Grabaciones
            </a>
            <a href="<?php echo SERVERURL."foro/{$s['id']}/"; ?>">
              <i class="zmdi zmdi-comments"></i> Foro
            </a>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</section>
