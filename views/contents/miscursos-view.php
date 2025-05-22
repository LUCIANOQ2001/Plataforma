<?php
// views/contents/miscursos-view.php

// Sólo Docentes (y Administradores, si quieres) pueden ver esta página
if(!in_array($_SESSION['userType'] ?? '', ['Docente','Administrador'])){
  $logout = new loginController();
  echo $logout->login_session_force_destroy_controller();
  exit;
}

// Controlador de cursos
require_once __DIR__ . '/../../controllers/cursoController.php';
$insCurso = new cursoController();

// Lista de cursos que el docente tiene a cargo
$cursos = $insCurso->list_mis_cursos_controller($_SESSION['userKey']);
?>

<style>
  /* Ajustes del layout: alinea con tu sidebar */
  .dashboard-contentPage {
    margin-left: 170px; /* ← ajusta si tu sidebar cambia */
    padding: 20px;
    box-sizing: border-box;
  }

  /* Grid de tarjetas */
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

  /* Cabecera */
  .course-header {
    height: 180px; /* ← cambia altura */
    background-size: cover;
    background-position: center;
  }

  /* Cuerpo */
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

  /* Menú siempre debajo del título */
  .course-dropdown {
    background: #333;
    border-radius: 4px;
    overflow: hidden;
    display: none; /* oculto por defecto */
    margin-bottom: .5rem;
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

  /* Mostrar menú al hover de la tarjeta */
  .course-card:hover .course-dropdown {
    display: block;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles"><i class="zmdi zmdi-book"></i> Mis Cursos</h1>
    </div>
    <p class="lead">
      Aquí tienes los cursos a tu cargo. Pasa el cursor sobre una tarjeta para ver sus opciones.
    </p>
  </div>

  <div class="container-fluid">
    <div class="courses-grid">
      <?php foreach($cursos as $c): ?>
        <div class="course-card" data-course-id="<?php echo $c['id']; ?>">
          <!-- Cabecera con imagen (ajusta la URL aquí) -->
          <div class="course-header"
               style="background-image:url('<?php echo SERVERURL;?>views/assets/img/cursito.jpg')">
          </div>
          <div class="course-body">
            <div class="course-title"><?php echo htmlspecialchars($c['Nombre']); ?></div>
            <div class="course-subtitle"><?php echo htmlspecialchars($c['Descripcion']); ?></div>
            <!-- Menú debajo del título, aparece al hover de la tarjeta -->
            <div class="course-dropdown">
              <a href="<?php echo SERVERURL."sesion/{$c['id']}/"; ?>">Sesiones</a>
              <a href="<?php echo SERVERURL."anunciocurso/{$c['id']}/"; ?>">Anuncio</a>
              <a href="<?php echo SERVERURL."consultascourse/{$c['id']}/"; ?>">Consulta</a>
              <a href="<?php echo SERVERURL."reportecurso/{$c['id']}/"; ?>">Reporte</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
