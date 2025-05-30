<?php 
// views/contents/classinfo-view.php

if (in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])):

require_once "./controllers/videoController.php";
$insVideo = new videoController();
$urls = SERVERURL.$_GET['views'];
if(isset($_POST['idAtt']) && isset($_POST['nameAtt'])){
  echo $insVideo->delete_video_attachment_controller($_POST['idAtt'],$_POST['nameAtt'],$urls);
}

$code = explode("/", $_GET['views']);
$data = $insVideo->data_video_controller("Only", $code[1]);
if($data->rowCount() > 0):
  $rows = $data->fetch();
?>

<style>
  html, body {
    background-color: #1e1f28;
    color: #fff;
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
  }
  .dashboard-contentPage {
    margin-left: 170px;
    padding: 30px;
    width: calc(100% - 170px);
    background-color: #1e1f28;
    box-sizing: border-box;
  }
  .page-header h1 {
    font-size: 28px;
    color: #00e5ff;
    text-shadow: 1px 1px 6px #000;
  }
  .lead {
    color: #ccc;
    font-size: 1.1rem;
    margin-bottom: 30px;
  }
  .panel {
    background: #2c2d3f;
    border: 1px solid #3c3d4f;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
  }
  .panel-heading {
    background-color: #43a047;
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
  .form-control, .control-label, textarea {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid #555 !important;
    color: #fff !important;
  }
  .btn-info, .btn-success, .btn-danger {
    color: #fff !important;
    font-weight: bold;
  }
  .btn-info {
    background-color: #0288d1 !important;
    border: 1px solid #0277bd !important;
  }
  .btn-success {
    background-color: #388e3c !important;
    border: 1px solid #2e7d32 !important;
  }
  .btn-danger {
    background-color: #d32f2f !important;
    border: 1px solid #b71c1c !important;
  }
  .table {
    color: #fff;
    border-color: #444;
  }
  .table th, .table td {
    border: 1px solid #444;
    text-align: center;
  }
  .table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(255, 255, 255, 0.05);
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles"><i class="zmdi zmdi-videocam zmdi-hc-fw"></i> Clase</h1>
    </div>
    <p class="lead">
      Bienvenido a la sección de actualización de los datos de las clases. Acá podrá actualizar la información de la clase.
    </p>
  </div>

  <p class="text-center">
    <a href="<?php echo SERVERURL; ?>classlist/" class="btn btn-info btn-raised btn-sm">
      <i class="zmdi zmdi-long-arrow-return"></i> Volver
    </a>
  </p>

  <?php if($rows['Adjuntos'] != ""): ?>
  <div class="container-fluid">
    <div class="panel panel-info">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="zmdi zmdi-attachment"></i> Archivos adjuntos asociados</h3>
      </div>
      <div class="panel-body">
        <table class="table table-striped table-hover">
          <thead>
            <tr><th>Adjunto</th><th>Eliminar</th></tr>
          </thead>
          <tbody>
            <?php $catt=1; foreach(explode(",", $rows['Adjuntos']) as $att): ?>
            <tr>
              <td><?php echo $att; ?></td>
              <td>
                <button type="button" class="btn btn-danger btn-raised btn-xs btnFormsAjax" data-action="delatt" data-id="delete-att-<?php echo $catt; ?>">
                  <i class="zmdi zmdi-delete"></i>
                </button>
                <form action="" method="POST" enctype="multipart/form-data" id="delete-att-<?php echo $catt; ?>">
                  <input type="hidden" name="idAtt" value="<?php echo $rows['id']; ?>">
                  <input type="hidden" name="nameAtt" value="<?php echo $att; ?>">
                </form>
              </td>
            </tr>
            <?php $catt++; endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="container-fluid">
    <div class="panel panel-success">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="zmdi zmdi-refresh"></i> Actualizar datos</h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo SERVERURL; ?>ajax/ajaxVideo.php" method="POST" enctype="multipart/form-data" autocomplete="off" data-form="UpdateVideo" class="ajaxDataForm">
          <input type="hidden" name="upid" value="<?php echo $rows['id']; ?>">

          <fieldset>
            <legend><i class="zmdi zmdi-videocam"></i> Datos de la clase</legend>
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <span class="control-label">Título *</span>
                  <input class="form-control" type="text" name="title" value="<?php echo $rows['Titulo']; ?>" required>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <span class="control-label">Tutor o Docente *</span>
                  <input class="form-control" type="text" name="teacher" value="<?php echo $rows['Tutor']; ?>" required>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <span class="control-label">Fecha *</span>
                  <input class="form-control" type="date" name="date" value="<?php echo $rows['Fecha']; ?>" required>
                </div>
              </div>
              <div class="col-xs-12">
                <div class="form-group label-floating">
                  <label class="control-label">Código del vídeo *</label>
                  <textarea name="upcode" class="form-control" rows="3" required><?php echo $rows['Video']; ?></textarea>
                </div>
              </div>
            </div>
          </fieldset>

          <fieldset>
            <legend><i class="zmdi zmdi-comment-video"></i> Descripción e información adicional</legend>
            <textarea name="description" class="form-control" id="spv-editor"><?php echo $rows['Descripcion']; ?></textarea>
          </fieldset>

          <fieldset>
            <legend><i class="zmdi zmdi-attachment"></i> Agregar más archivos adjuntos</legend>
            <div class="form-group">
              <input type="file" name="attachments[]" multiple accept=".jpg, .png, .jpeg, .pdf, .ppt, .pptx, .doc, .docx">
              <div class="input-group">
                <input type="text" readonly class="form-control" placeholder="Elija los archivos adjuntos...">
                <span class="input-group-btn input-group-sm">
                  <button type="button" class="btn btn-fab btn-fab-mini">
                    <i class="zmdi zmdi-attachment-alt"></i>
                  </button>
                </span>
              </div>
              <small>Tamaño máximo 5MB. Tipos permitidos: PNG, JPG, PDF, DOC, PPT.</small>
            </div>
          </fieldset>

          <p class="text-center">
            <button type="submit" class="btn btn-success btn-raised btn-sm">
              <i class="zmdi zmdi-refresh"></i> Guardar cambios
            </button>
          </p>

          <div class="form-process full-box"></div>
        </form>
      </div>
    </div>
  </div>
</section>
<?php
  else:
    echo '<p class="lead text-center">Lo sentimos ocurrió un error inesperado</p>';
  endif;
else:
  $logout2 = new loginController();
  echo $logout2->login_session_force_destroy_controller();
endif;
?>