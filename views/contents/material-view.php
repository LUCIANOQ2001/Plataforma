<?php
// views/contents/material-view.php

// Control de acceso
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])) {
    $logout = new loginController();
    echo $logout->login_session_force_destroy_controller();
    exit;
}

// Controladores
require_once __DIR__ . '/../../controllers/sesionController.php';
require_once __DIR__ . '/../../controllers/materialController.php';

$insSesion   = new sesionController();
$insMaterial = new materialController();

// Obtenemos ID de sesión de la URL: /material/{sesionId}/
$parts     = explode("/", $_GET['views']);
$sesionId  = intval($parts[1]);

// Datos de la sesión (para mostrar el título)
$dataSes = $insSesion->get_sesion_by_id_controller($sesionId);
if($dataSes->rowCount()===0){
    echo '<div class="alert alert-danger">Sesión no encontrada.</div>';
    return;
}
$ses = $dataSes->fetch(PDO::FETCH_ASSOC);

// Procesar POST (añadir o borrar)
$alert = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['add_material'])){
        $alert = $insMaterial->add_material_controller($sesionId);
    }
    if(isset($_POST['delete_id'])){
        $alert = $insMaterial->delete_material_controller(intval($_POST['delete_id']));
    }
    // PRG: evitar reenvío
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}

// Listar materiales
$materials = $insMaterial->list_materials_controller($sesionId);
?>

<style>
  .dashboard-contentPage { margin-left:170px; padding:20px; }
  .material-list {
    max-height: 400px;
    overflow-y: auto;
    margin-top:1rem;
  }
  .material-list table { width:100%; }
  .material-list th, .material-list td {
    padding: 0.8rem; border-bottom:1px solid #444;
    color:#fff;
  }
  .material-list td a { color:#0af; text-decoration:none; }
  .material-list td .actions i {
    cursor:pointer; margin-left:0.5rem; color:#0af;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-collection-text"></i>
        Material – <?php echo htmlspecialchars($ses['Titulo']); ?>
      </h1>
    </div>
    <?php echo $alert; ?>
    <p class="lead">Aquí ves todos los archivos subidos para esta sesión.</p>
  </div>

  <div class="container-fluid">
    <!-- Botón Nuevo Material -->
    <button class="btn btn-info btn-raised"
            onclick="document.getElementById('formAdd').style.display='block'">
      <i class="zmdi zmdi-plus"></i> Nuevo Material
    </button>
  </div>

  <!-- Formulario oculto -->
  <div class="container-fluid" id="formAdd" style="display:none; margin-top:1rem;">
    <div class="panel panel-info">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="zmdi zmdi-plus-box"></i> Agregar Material</h3>
      </div>
      <div class="panel-body">
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
          <input type="hidden" name="add_material" value="1">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group label-floating">
                <label class="control-label">Título *</label>
                <input name="titulo" class="form-control" type="text" required>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Archivo *</label>
                <input name="archivo" type="file" required>
              </div>
            </div>
          </div>
          <p class="text-center">
            <button type="submit" class="btn btn-success btn-raised">
              <i class="zmdi zmdi-floppy"></i> Subir
            </button>
            <button type="button" class="btn btn-default"
                    onclick="this.closest('#formAdd').style.display='none'">
              Cancelar
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>

  <!-- Listado -->
  <div class="container-fluid material-list">
    <table>
      <thead>
        <tr>
          <th>Archivo</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($materials)): ?>
          <tr><td colspan="3" class="text-center">No hay material aún.</td></tr>
        <?php else: foreach($materials as $m): ?>
          <tr>
            <td>
              <i class="zmdi zmdi-folder"></i>
              <a href="<?php echo SERVERURL.'views/assets/material/'.$m['Archivo']; ?>"
                 target="_blank">
                <?php echo htmlspecialchars($m['Titulo']); ?>
              </a>
            </td>
            <td><?php echo date("d/m/Y H:i", strtotime($m['Fecha'])); ?></td>
            <td class="actions">
              <!-- Editar: podrías abrir un modal similar al Add -->
              <i class="zmdi zmdi-edit" title="Editar"></i>
              <!-- Borrar -->
              <form method="POST" style="display:inline">
                <input type="hidden" name="delete_id" value="<?php echo $m['id']; ?>">
                <i class="zmdi zmdi-delete" title="Eliminar"
                   onclick="if(confirm('¿Eliminar este material?')) this.parentElement.submit();">
                </i>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</section>
