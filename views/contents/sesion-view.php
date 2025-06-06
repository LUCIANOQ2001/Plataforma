<?php
// views/contents/sesion-view.php

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

$insCurso   = new cursoController();
$insSesion  = new sesionController();
$userType   = $_SESSION['userType'];

// 3) ID de curso por URL: /sesion/{cursoId}/
$parts    = explode("/", trim($_GET['views'], "/"));
$cursoId  = intval($parts[1]);

// 4) Obtener datos del curso
// 4) Obtener datos del curso
$dataCurso = $insCurso->get_curso_by_id_controller($cursoId);

// Si el controlador devolvió un PDOStatement (versión antigua), hacemos lo mismo que antes:
if ($dataCurso instanceof PDOStatement) {
    if ($dataCurso->rowCount() === 0) {
        echo '<div class="alert alert-danger">Curso no encontrado.</div>';
        return;
    }
    $curso = $dataCurso->fetch(PDO::FETCH_ASSOC);

// Si devolvió un array asociativo con los datos de la fila o devolvió null:
} else {
    // Si no devolvió nada, error:
    if (empty($dataCurso)) {
        echo '<div class="alert alert-danger">Curso no encontrado.</div>';
        return;
    }
    // Si $dataCurso ya es un arreglo asociativo con las columnas del curso:
    if (isset($dataCurso['Nombre'])) {
        $curso = $dataCurso;
    // Si por alguna razón devolvió un array de filas (indexado), tomamos la primera:
    } elseif (isset($dataCurso[0])) {
        $curso = $dataCurso[0];
    } else {
        // Cualquier otro caso inesperado:
        echo '<div class="alert alert-danger">Error al obtener datos del curso.</div>';
        return;
    }
}



// 5) Procesar POST para creación de sesión (solo Admin/Docente)
$alert = '';
if (in_array($userType, ['Administrador','Docente'])
    && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $alert = $insSesion->add_sesion_controller($cursoId, $_POST);
    // PRG: evita reenvío
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}

// 6) Listar sesiones (forzamos que quede un array)
// 6) Listar sesiones (forzamos que siempre sea array indexado de filas)
$sesionesRaw = $insSesion->list_sesiones_controller($cursoId);

if ($sesionesRaw instanceof PDOStatement) {
    // Si devolvió un PDOStatement, hacemos fetchAll para obtener array de filas
    $sesiones = $sesionesRaw->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Si devolvió ya un array de filas (o nulo), lo convertimos a array.
    $sesiones = is_array($sesionesRaw) ? $sesionesRaw : [];
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
    margin-left: 120px;
    padding: 0 30px;
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
    font-size: 1.1rem;
    color: #ccc;
    margin-bottom: 30px;
  }

  .btn-info {
    background-color: #03a9f4;
    border-color: #0288d1;
    color: #fff;
  }

  .btn-info:hover {
    background-color: #0288d1;
  }

  .btn-back-home {
    background-color: #607d8b !important;
    border-color:     #455a64 !important;
    color:            #fff !important;
    margin-bottom: 20px;
  }

  .panel {
    background: #2c2d3f;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
    border: 1px solid #3c3d4f;
    color: #fff;
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

  .form-control {
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid #555;
    color: #fff;
  }

  .course-sessions {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: flex-start;
  }

  .session-card {
    background: #2a2c3b;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
    overflow: hidden;
    width: 250px;
    transition: transform 0.3s ease;
  }

  .session-card:hover {
    transform: translateY(-5px);
  }

  .session-card .header {
    background: #b71c1c;
    color: #fff;
    padding: 16px;
    font-weight: bold;
    font-size: 16px;
    text-align: center;
  }

  .session-card .header small {
    display: block;
    font-weight: normal;
    font-size: 13px;
    margin-top: 4px;
  }

  .session-card .body {
    padding: 15px;
  }

  .session-card .body a {
    display: block;
    padding: 6px 10px;
    margin-bottom: 8px;
    background: #333;
    border-radius: 5px;
    color: #fff;
    text-decoration: none;
    transition: background 0.2s;
  }

  .session-card .body a:hover {
    background: #444;
  }

  .session-card .body a i {
    margin-right: 6px;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <!-- Botón Volver a Mis Cursos -->
    <a href="<?php echo SERVERURL; ?>miscursos/"
       class="btn btn-back-home btn-sm">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Mis Cursos
    </a>

    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-play-circle"></i>
        Sesiones de: <?php echo htmlspecialchars($curso['Nombre']); ?>
      </h1>
    </div>
    <p class="lead">
      <?php 
        // Si existe $curso['Descripcion'], lo mostramos; si no, mostramos cadena vacía.
        echo htmlspecialchars( $curso['Descripcion'] ?? '' ); 
      ?>
    </p>

    <?php echo $alert; ?>
  </div>

  <?php if (in_array($userType, ['Administrador','Docente'])): ?>
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
  <?php endif; ?>

  <!-- Listado de sesiones (visible para todos los roles) -->
  <div class="container-fluid">
    <div class="course-sessions">
      <?php if (empty($sesiones)): ?>
        <p>No hay sesiones aún. 
           <?php if(in_array($userType,['Administrador','Docente'])): ?>
             Crea la primera arriba.
           <?php endif; ?>
        </p>
      <?php else: ?>
            <?php foreach($sesiones as $s): ?>
              <div class="session-card">
                <div class="header">
                  <!-- Asegúrate de que “Titulo” y “Fecha” coinciden con los campos que trae tu SELECT: -->
                  <?php echo htmlspecialchars($s['Titulo']); ?><br>
                  <small><?php echo date("d/m/Y", strtotime($s['Fecha'])); ?></small>
                </div>
                <div class="body">
                  <a href="<?php echo SERVERURL."material/{$s['id']}/"; ?>">
                    <i class="zmdi zmdi-collection-text"></i> Material
                  </a>

              <!-- Enlace a Evaluación según tipo de usuario -->
              <?php if ($userType === 'Docente' || $userType === 'Administrador'): ?>
                <a href="<?php echo SERVERURL."evaluacion/{$s['id']}/"; ?>">
                  <i class="zmdi zmdi-assignment"></i> Evaluación
                </a>
              <?php else: /* Estudiante */ ?>
                <a href="<?php echo SERVERURL."evaluacion-student/{$s['id']}/estudiante/"; ?>">
                  <i class="zmdi zmdi-assignment"></i> Evaluación
                </a>
              <?php endif; ?>

              <?php if ($s['Video']): ?>
                <a href="<?php echo htmlspecialchars($s['Video']); ?>" target="_blank">
                  <i class="zmdi zmdi-videocam"></i> Video
                </a>
              <?php endif; ?>
              <a href="<?php echo SERVERURL."grabaciones/{$s['id']}/"; ?>">
                <i class="zmdi zmdi-movie"></i> Grabaciones
              </a>
              <a href="<?php echo SERVERURL."foro/{$s['id']}/"; ?>">
                <i class="zmdi zmdi-comments"></i> Foro
              </a>
              <a href="<?php echo SERVERURL."asistencia/{$s['id']}/"; ?>">
                <i class="zmdi zmdi-comment-text"></i> Asistencias
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>
