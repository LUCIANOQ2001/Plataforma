<?php 
// Sólo Administradores y Docentes pueden ver esta página
if (in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])): 
?>

<!-- Estilos inline actualizados -->
<style>
  /* 1) Desplaza todo el contenido para no tapar el sidebar (ancho = 270px) */
  .dashboard-contentPage {
    margin-left: 170px;            
    padding: 20px;
    width: calc(100% - 270px);
    box-sizing: border-box;
    overflow: auto;
  }
  .dashboard-contentPage.full-box { width: auto; }

  /* 2) Haz transparentes los fondos de los contenedores y paneles */
  .dashboard-contentPage .container-fluid,
  .dashboard-contentPage .panel,
  .dashboard-contentPage .panel-heading,
  .dashboard-contentPage .panel-body,
  .dashboard-contentPage fieldset {
    background-color: transparent !important;
    color: #fff !important;
  }

  /* 3) Inputs y textarea: bordes claros y texto blanco */
  .dashboard-contentPage .form-control,
  .dashboard-contentPage textarea {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid #555      !important;
    color:  #fff                 !important;
  }

  /* 4) Encabezado del panel-info conserva su color */
  .dashboard-contentPage .panel-info .panel-heading {
    background-color: #5bc0de !important;
    color:             #fff   !important;
  }

  /* 5) Breadcrumb (Nueva / Lista): quita fondo blanco y mejora contraste */
  .dashboard-contentPage .breadcrumb-tabs {
    background: transparent !important;
    padding:    0          !important;
    margin:     0 0 20px 0  !important;
  }
  .dashboard-contentPage .breadcrumb-tabs li a.btn-info {
    background-color: #0288d1 !important;  /* azul más intenso */
    border-color:     #0277bd !important;
    color:            #fff    !important;
  }
  .dashboard-contentPage .breadcrumb-tabs li a.btn-success {
    background-color: #388e3c !important;  /* verde más intenso */
    border-color:     #2e7d32 !important;
    color:            #fff    !important;
  }
  .dashboard-contentPage .breadcrumb-tabs li {
    margin-right: 10px;
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

  <?php $dateNow = date("Y-m-d"); ?>

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
