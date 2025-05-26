<?php
// views/contents/miscursos-view.php

// Sólo Docentes, Administradores y Estudiantes pueden ver esta página
if(!in_array($_SESSION['userType'] ?? '', ['Docente','Administrador','Estudiante'])){
  echo (new loginController())->login_session_force_destroy_controller();
  exit;
}

// Controlador de cursos
require_once __DIR__ . '/../../controllers/cursoController.php';
$insCurso = new cursoController();

$userType = $_SESSION['userType'];
$userKey  = $_SESSION['userKey'];

// Según el rol, obtenemos el listado apropiado
if($userType === 'Estudiante'){
  $cursos = $insCurso->list_cursos_estudiante_controller($userKey);
  $subtitle = "Aquí tienes los cursos en los que estás inscrito. Pasa el cursor sobre una tarjeta para ver sus opciones.";
} else {
  $cursos = $insCurso->list_mis_cursos_controller($userKey);
  $subtitle = "Aquí tienes los cursos a tu cargo. Pasa el cursor sobre una tarjeta para ver sus opciones.";
}
?>

<style>
  .dashboard-contentPage {
    margin-left: 170px;
    padding: 20px;
    box-sizing: border-box;
  }
  .courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px,1fr));
    gap: 1rem;
    margin-top: 1rem;
  }
  .course-card {
    position: relative;
    background: #2a2c3b;
    border-radius: 6px;
    overflow: hidden;
    transition: transform .2s;
  }
  .course-card:hover {
    transform: translateY(-4px);
  }
  .course-header {
    height: 180px;
    background-size: cover;
    background-position: center;
  }
  .course-body {
    padding: .8rem;
    color: #fff;
  }
  .course-title {
    font-size: 1rem;
    font-weight: bold;
    margin-bottom: .25rem;
  }
  .course-subtitle {
    font-size: .85rem;
    color: #aaa;
    margin-bottom: .5rem;
  }
  .course-dropdown {
    background: #333;
    border-radius: 4px;
    overflow: hidden;
    display: none;
    margin-top: .5rem;
  }
  .course-dropdown a {
    display: block;
    padding: .5rem 1rem;
    color: #fff;
    text-decoration: none;
    font-size: .9rem;
  }
  .course-dropdown a:hover {
    background: #444;
  }
  .course-card:hover .course-dropdown {
    display: block;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-book zmdi-hc-fw"></i> Mis Cursos
      </h1>
      <hr>
      <p class="lead"><?php echo $subtitle; ?></p>
    </div>
  </div>

  <div class="container-fluid">
    <?php if(empty($cursos)): ?>
      <p>No hay cursos para mostrar.</p>
    <?php else: ?>
      <div class="courses-grid">
        <?php foreach($cursos as $c): ?>
          <div class="course-card">
            <div class="course-header"
                 style="background-image:url('<?php echo SERVERURL;?>views/assets/img/cursito.jpg')">
            </div>
            <div class="course-body">
              <div class="course-title">
                <?php echo htmlspecialchars($c['Nombre']); ?>
              </div>
              <div class="course-subtitle">
                <?php echo htmlspecialchars($c['Descripcion']); ?>
              </div>
              <div class="course-dropdown">
                <a href="<?php echo SERVERURL."sesion/{$c['id']}/"; ?>">
                  <i class="zmdi zmdi-time-restore"></i> Sesiones
                </a>
                <a href="<?php echo SERVERURL."anunciocurso/{$c['id']}/"; ?>">
                  <i class="zmdi zmdi-notifications"></i> Anuncios
                </a>
                <a href="<?php echo SERVERURL."consultascourse/{$c['id']}/"; ?>">
                  <i class="zmdi zmdi-comment-text"></i> Consultas
                </a>
                <a href="<?php echo SERVERURL."reportecurso/{$c['id']}/"; ?>">
                  <i class="zmdi zmdi-chart"></i> Reporte de notas
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
