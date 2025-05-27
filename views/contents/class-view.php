<?php 
// Solo Administradores y Docentes pueden ver esta página
if (in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])): 

  require_once __DIR__ . '/../../controllers/cursoController.php';
  $insCurso     = new cursoController();
  $todosCursos  = $insCurso->list_cursos_controller();
  $dateNow = date("Y-m-d");
?>

<style>
  html, body {
    margin: 0;
    padding: 0;
    background-color: #1e1f28;
    color: #fff;
    width: 100%; height: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
  }
  .dashboard-contentPage {
    margin-left: 170px;
    padding: 30px;
    width: calc(100% - 170px);
    box-sizing: border-box;
    background-color: #1e1f28;
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
  .breadcrumb-tabs .btn {
    font-weight: bold;
    color: #fff !important;
    padding: 8px 16px;
    border-radius: 4px;
    transition: background .3s;
  }
  .breadcrumb-tabs .btn-info {
    background-color: #0288d1 !important;
    border: 1px solid #0277bd !important;
  }
  .breadcrumb-tabs .btn-success {
    background-color: #388e3c !important;
    border: 1px solid #2e7d32 !important;
  }
  .breadcrumb-tabs li { margin-right: 10px; }
  .panel {
    background: #2c2d3f;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
    border: 1px solid #3c3d4f;
  }
  .panel-heading {
    background-color: #00bcd4 !important;
    color: #fff;
    font-weight: bold;
    font-size: 17px;
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
    fieldset, legend {
    border: none;
    padding: 0;
    margin-bottom: 20px;
    color: #efebeb;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-tv-alt-play zmdi-hc-fw"></i> Clases <small>(Registro)</small>
      </h1>
    </div>
    <p class="lead">
      Bienvenido a la sección de clases, aquí podrás registrar nuevas clases 
      (Los campos marcados con * son obligatorios para registrar una nueva clase o transmisión en vivo).
    </p>
  </div>

  <div class="container-fluid">
    <ul class="breadcrumb breadcrumb-tabs">
      <li class="active">
        <a href="<?php echo SERVERURL; ?>class/" class="btn btn-info">
          <i class="zmdi zmdi-plus"></i> Nueva
        </a>
      </li>
      <li>
        <a href="<?php echo SERVERURL; ?>classlist/" class="btn btn-success">
          <i class="zmdi zmdi-format-list-bulleted"></i> Lista
        </a>
      </li>
    </ul>
  </div>

  <div class="container-fluid">
    <div class="row">
      <div class="col-xs-12">
        <div class="panel panel-info">
          <div class="panel-heading">
            <h3 class="panel-title"><i class="zmdi zmdi-plus"></i> Nueva clase</h3>
          </div>
          <div class="panel-body">
            <form action="<?php echo SERVERURL; ?>ajax/ajaxVideo.php"
                  method="POST"
                  enctype="multipart/form-data"
                  autocomplete="off"
                  data-form="AddVideo"
                  class="ajaxDataForm">

              <fieldset>
                <legend><i class="zmdi zmdi-videocam"></i> Datos de la clase</legend>
                <div class="row">

                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <span class="control-label">Curso *</span>
                      <select name="curso_id" class="form-control" required>
                        <option value="">Seleccione curso...</option>
                        <?php foreach($todosCursos as $c): ?>
                          <option value="<?php echo $c['id']; ?>">
                            <?php echo htmlspecialchars($c['Nombre']); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <span class="control-label">Título *</span>
                      <input class="form-control" type="text" name="title" required>
                    </div>
                  </div>

                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <span class="control-label">Tutor o Docente *</span>
                      <input class="form-control" type="text" name="teacher" required>
                    </div>
                  </div>

                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <span class="control-label">Fecha *</span>
                      <input class="form-control" type="date" name="date"
                             value="<?php echo $dateNow; ?>" required>
                    </div>
                  </div>

                  <div class="col-xs-12">
                    <div class="form-group label-floating">
                      <label class="control-label">Código del vídeo *</label>
                      <textarea name="code" class="form-control" rows="3" required></textarea>
                    </div>
                  </div>

                </div>
              </fieldset>

              <fieldset>
                <legend><i class="zmdi zmdi-comment-video"></i> Descripción e información adicional</legend>
                <div class="row">
                  <div class="col-xs-12">
                    <textarea name="description" id="spv-editor" class="form-control"></textarea>
                  </div>
                </div>
              </fieldset>

              <fieldset>
                <legend><i class="zmdi zmdi-attachment"></i> Archivos adjuntos</legend>
                <div class="form-group">
                  <input type="file" name="attachments[]" multiple
                         accept=".jpg,.png,.jpeg,.pdf,.ppt,.pptx,.doc,.docx">
                  <div class="input-group">
                    <input type="text" readonly class="form-control"
                           placeholder="Elija los archivos adjuntos...">
                    <span class="input-group-btn input-group-sm">
                      <button type="button" class="btn btn-fab btn-fab-mini">
                        <i class="zmdi zmdi-attachment-alt"></i>
                      </button>
                    </span>
                  </div>
                  <small>
                    Tamaño máximo 5MB. Permitidos PNG, JPG, PDF, WORD y PPT.
                  </small>
                </div>
              </fieldset>

              <p class="text-center">
                <button type="submit" class="btn btn-info btn-raised btn-sm">
                  <i class="zmdi zmdi-floppy"></i> Guardar
                </button>
              </p>

              <div class="form-process full-box"></div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php 
else:
    $logout2 = new loginController();
    echo $logout2->login_session_force_destroy_controller(); 
endif;
?>
