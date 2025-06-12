<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente','Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller(); exit;
}
require_once __DIR__ . '/../../controllers/sesionController.php';
require_once __DIR__ . '/../../controllers/grabacionController.php';
$insSesion   = new sesionController();
$insGrab     = new grabacionController();
$parts     = explode('/', trim($_GET['views'],'/'));
$sesionId  = intval($parts[1]);
$dataSes = $insSesion->get_sesion_by_id_controller($sesionId);
if ($dataSes->rowCount() === 0) { echo '<div class="alert alert-danger">Sesión no encontrada.</div>'; return; }
$ses = $dataSes->fetch(PDO::FETCH_ASSOC);
$alert = '';
if (in_array($_SESSION['userType'],['Administrador','Docente'])
    && $_SERVER['REQUEST_METHOD']==='POST') {
    if (isset($_POST['delete_id'])) {
        $alert = $insGrab->delete_grabacion_controller((int)$_POST['delete_id']);
    } else {
        $alert = $insGrab->add_grabacion_controller($sesionId);
    }
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}
$grabs = $insGrab->list_grabaciones_by_sesion_controller($sesionId);
?>
<style>
  :root {
    --primary-bg: #2B2B2B;
    --primary-accent: #D1B16E;
    --secondary-bg: rgba(174,12,12,0.61);
    --text-light: #FFFFFF;
    --hover-accent: rgba(209,177,110,0.2);
  }
  html, body { margin:0; padding:0; background:var(--primary-bg); color:var(--text-light);
      width:100%; height:100%; overflow-x:hidden; font-family:'RobotoCondensed',sans-serif; }
  .dashboard-banner { position:fixed; top:0; left:270px;
      width:calc(100% - 270px); height:100%; background:url('<?=SERVERURL?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
      opacity:0.05; pointer-events:none; z-index:0; }
  .dashboard-contentPage { position:relative; z-index:1; margin-left:180px;
      width:calc(100% - 270px); padding:0 30px auto; min-height:100vh; box-sizing:border-box; }
  .btn-options, .dropdown-toggle, .btn-search, i.zmdi-zmdi-search, .zmdi-more-vert, .btn-menu-dashboard { display:none!important; }
  .page-header h1 { font-size:2rem; color:var(--primary-accent);
      text-shadow:2px 2px 8px rgba(0,0,0,0.7); margin-bottom:0.5rem; text-align:center; }
  .lead { text-align:center; font-size:1.1rem; color:rgba(255,255,255,0.7);
      margin:0 auto 2rem; }
  .btn-back-home { background:var(--primary-accent)!important; color:var(--text-light)!important;
      border:none!important; border-radius:.3rem; padding:.5rem 1rem; font-size:.9rem;
      display:inline-block; margin-bottom:1.5rem; transition:background .3s; }
  .btn-back-home:hover { background:var(--hover-accent)!important; text-decoration:none; }
  form.grab-form { display:flex; align-items:center; gap:1rem; margin-bottom:1rem; }
  form.grab-form input[type="file"], form.grab-form label, form.grab-form button {
      background:var(--secondary-bg); color:var(--text-light)!important;
      border:1px solid var(--primary-accent)!important; border-radius:.3rem;
      padding:.5rem .75rem; }
  table.grab-table { width:100%; border-collapse:collapse; margin-top:1rem; }
  .grab-table th, .grab-table td { padding:.75rem; border-bottom:1px solid rgba(255,255,255,0.2); }
  .grab-table th { background:var(--primary-accent); color:var(--text-light); text-align:left; }
  .grab-table td a { color:var(--text-light); text-decoration:none; }
  .grab-table td .delete-btn { color:#b71c1c; cursor:pointer; }
  @media(max-width:768px) { .dashboard-contentPage { margin-left:0; width:100%; padding:1rem; } }
</style>
<div class="dashboard-banner"></div>
<section class="dashboard-contentPage">
  <div class="container-fluid">
    <a href="<?=SERVERURL?>sesion/<?=$ses['CursoId']?>/" class="btn btn-back-home btn-sm">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Sesiones
    </a>
    <div class="page-header">
      <h1><i class="zmdi zmdi-videocam"></i> Grabaciones – <?=htmlspecialchars($ses['Titulo'])?></h1>
    </div>
    <?=$alert?>
  </div>
  <?php if(in_array($_SESSION['userType'],['Administrador','Docente'])):?>
  <div class="container-fluid">
    <form method="POST" enctype="multipart/form-data" class="grab-form">
      <label for="grab-file">Nueva grabación</label>
      <input id="grab-file" type="file" name="grabacion" required>
      <button type="submit" class="btn btn-info btn-sm">
        <i class="zmdi zmdi-cloud-upload"></i> Subir
      </button>
    </form>
  </div>
  <?php endif;?>
  <div class="container-fluid">
    <div class="table-responsive">
      <table class="grab-table">
        <thead><tr><th>Archivo</th><th>Fecha</th><?php if(in_array($_SESSION['userType'],['Administrador','Docente'])): ?><th>Acciones</th><?php endif;?></tr></thead>
        <tbody>
          <?php if($grabs): foreach($grabs as $g): ?>
          <tr>
            <td><a href="<?=SERVERURL?>attachments/grabaciones/<?=urlencode($g['archivo'])?>" download><?=$g['archivo']?></a></td>
            <td><?=$g['fecha']?></td>
            <?php if(in_array($_SESSION['userType'],['Administrador','Docente'])):?>
            <td><span class="delete-btn zmdi zmdi-delete" title="Eliminar" onclick="if(confirm('¿Eliminar grabación?')) this.closest('form').submit();"></span>
            <form method="POST" style="display:none"><input type="hidden" name="delete_id" value="<?=$g['id']?>"></form></td>
            <?php endif;?>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="<?=(in_array($_SESSION['userType'],['Administrador','Docente'])?3:2)?>" class="text-center">No hay grabaciones disponibles.</td></tr>
          <?php endif;?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<script>
  document.getElementById('grab-file').addEventListener('change',function(){
    /* no mostrar nombre */
  });
</script>
