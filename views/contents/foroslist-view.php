<?php
// views/contents/foroslist-view.php

if(!in_array($_SESSION['userType'] ?? '', ['Docente','Administrador'])){
  $logout = new loginController();
  echo $logout->login_session_force_destroy_controller();
  exit;
}

require_once __DIR__.'/../../controllers/sesionController.php';
$insS = new sesionController();

// extraer session ID de la URL: /foro/{sesionId}/
$parts    = explode('/', $_GET['views']);
$sesionId = intval($parts[1] ?? 0);

// manejar POST de creación de hilo
$alert = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $alert = $insS->add_foro_controller($sesionId,$_POST);
  echo "<script>location.href=location.href;</script>";
  exit;
}

// obtener lista de hilos
$foros = $insS->list_foros_by_sesion_controller($sesionId);
?>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-comment-text"></i>
        Foros – Sesión <?php echo $parts[1]; ?>
      </h1>
      <?php echo $alert; ?>
    </div>
  </div>
  <div class="container-fluid">
    <!-- formulario para nuevo hilo -->
    <form method="POST" class="form-inline" style="margin-bottom:1rem;">
      <input type="text" name="titulo" class="form-control" placeholder="Título del foro" required>
      <input type="text" name="fecha_cierre" class="form-control" placeholder="Fecha cierre (YYYY-MM-DD)" >
      <button class="btn btn-info"><i class="zmdi zmdi-plus"></i> Crear Foro</button>
    </form>

    <div class="list-group">
      <?php if($foros): foreach($foros as $f): ?>
      <a href="<?php echo SERVERURL."foro/{$f['id']}/"; ?>"
         class="list-group-item list-group-item-action">
        <h5 class="mb-1"><?php echo htmlspecialchars($f['Titulo']); ?></h5>
        <small>Creado: <?php echo $f['FechaSubida']; ?>
          <?php if($f['FechaCierre']): ?>
            – Cierre: <?php echo $f['FechaCierre']; ?>
          <?php endif; ?>
        </small>
        <p class="mb-1"><?php echo htmlspecialchars($f['Descripcion']); ?></p>
      </a>
      <?php endforeach; else: ?>
        <div class="alert alert-info">No hay foros en esta sesión.</div>
      <?php endif; ?>
    </div>
  </div>
</section>
