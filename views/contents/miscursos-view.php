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
    min-height: 100vh;
    background-color: #1e1f28;
    max-width: calc(100vw - 170px);
    box-sizing: border-box;
  }

  .page-header h1 {
    font-size: 28px;
    color: #00e5ff;
    text-shadow: 1px 1px 6px #000;
    margin-bottom: 10px;
    text-align: center;
  }

  .lead {
    font-size: 1.1rem;
    color: #ccc;
    text-align: center;
    max-width: 780px;
    margin: 0 auto 30px auto;
  }

  .courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
    max-width: 1000px;
    margin: 0 auto;
  }

  .course-card {
    background: #2a2c3b;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.6);
    overflow: hidden;
    transition: transform 0.2s ease-in-out;
  }

  .course-card:hover {
    transform: translateY(-5px);
  }

  .course-header {
    height: 160px;
    background-size: cover;
    background-position: center;
  }

  .course-body {
    padding: 1rem;
    color: #fff;
  }

  .course-title {
    font-size: 1.1rem;
    font-weight: bold;
    margin-bottom: 0.25rem;
    color: #29b6f6;
  }

  .course-subtitle {
    font-size: 0.9rem;
    color: #bbb;
    margin-bottom: 0.5rem;
  }

  .course-dropdown {
    background: #333;
    border-radius: 5px;
    overflow: hidden;
    display: none;
    margin-top: 0.5rem;
    box-shadow: inset 0 0 0 1px #444;
  }

  .course-dropdown a {
    display: block;
    padding: 10px 15px;
    color: #fff;
    text-decoration: none;
    font-size: 0.9rem;
    transition: background 0.3s;
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
      <p class="text-center">No hay cursos para mostrar.</p>
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
                <a href="<?php echo SERVERURL."asistencia/{$c['id']}/"; ?>">
                  <i class="zmdi zmdi-comment-text"></i> Asistencias
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
