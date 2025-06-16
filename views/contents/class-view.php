<?php 
// Solo Administradores y Docentes pueden ver esta página
if (in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])): 

  require_once __DIR__ . '/../../controllers/cursoController.php';
  $insCurso     = new cursoController();
  $todosCursos  = $insCurso->list_cursos_controller();
  $dateNow = date("Y-m-d");
?>

<style>
  /* ==== Paleta de colores ==== */
  :root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(174,12,12,0.61);
    --text-light:       #FFFFFF;
    --hover-accent:     rgba(209,177,110,0.2);
  }

  /* Ocultar buscador y menú */
  .btn-options,
  .dropdown-toggle,
  .btn-search,
  i.zmdi.zmdi-search,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }

  /* Banner de logo como fondo suave */
  .dashboard-banner {
    position: fixed;
    top: 0; left: 270px;
    width: calc(100% - 270px);
    height: 100%;
    background: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
    opacity: 0.05;
    pointer-events: none;
    z-index: 0;
  }

  html, body {
    margin: 0; padding: 0;
    width: 100%; height: 100%;
    background: var(--primary-bg);
    color: var(--text-light);
    overflow-x: hidden;
    font-family: 'RobotoCondensed', sans-serif;
    box-sizing: border-box;
  }

  .dashboard-contentPage {
    position: relative; z-index: 1;
    margin-left: 180px;
    width: calc(100% - 270px);
    padding: 30px auto;
    min-height: 100vh;
    box-sizing: border-box;
  }

  .page-header h1 {
    font-size: 2rem;
    color: var(--primary-accent);
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
    margin-bottom: 1rem;
  }
  .lead {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: 2rem;
  }

  /* Breadcrumb transparente */
  .breadcrumb-tabs {
    background: transparent !important;
    padding: 0; margin-bottom: 2rem; border: none;
    
  }
  .breadcrumb-tabs li {
    display: inline-block; margin-right: .5rem;
  }
  .breadcrumb-tabs .btn {
    background: var(--primary-accent) !important;
    color:rgb(0, 0, 0);
    border: none !important;
    border-radius: .3rem;
    padding: .5rem 1rem;
    font-size: .9rem;
    text-decoration: none;
    transition: background .3s;
  }
  .breadcrumb-tabs .btn:hover {
    background: var(--hover-accent) !important;
  }

  .panel {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    overflow: hidden;
    margin-bottom: 2rem;
  }
  .panel-heading {
    background: var(--primary-accent) !important;
    color:rgb(0, 0, 0);
    padding: .75rem 1rem;
    text-align: center;
    font-size: 1.2rem;
  }
  .panel-body {
    padding: 1.5rem;
  }

  .form-control, .control-label, textarea {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid #555 !important;
    color: var(--text-light) !important;
  }
  /* Y aplica el mismo fondo y texto a cada option */
.form-control option {
  background: var(--secondary-bg) !important;
  color: var(--text-light) !important;
}

/* Opción enfocada */
.form-control:focus {
  background: rgba(255,255,255,0.1) !important;
  color: var(--text-light) !important;
}
  fieldset, legend {
    border: none;
    margin-bottom: 1rem;
    color:rgb(10, 1, 1);
  }

  .btn-info, .btn-success {
    font-weight: bold;
    border-radius: .3rem;
    transition: background .3s;

  }
  .btn-info {
    background: var(--primary-accent) !important;
    border: none !important;
    color: #000 !important;
  }
  .btn-info:hover {
    background: var(--hover-accent) !important;
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-tv-alt-play zmdi-hc-fw"></i>
        Clases <small>(Registro)</small>
      </h1>
    </div>
    <p class="lead">
      Bienvenido a la sección de clases, aquí podrás registrar nuevas clases 
      (Los campos marcados con * son obligatorios para registrar una nueva clase o transmisión en vivo).
    </p>
  </div>

  <div class="container-fluid">
    <ul class="breadcrumb-tabs">
      <li class="active">
        <a href="<?= SERVERURL ?>class/" class="btn btn-info">
          <i class="zmdi zmdi-plus"></i> Nueva
        </a>
      </li>
      <li>
        <a href="<?= SERVERURL ?>classlist/" class="btn btn-info">
          <i class="zmdi zmdi-format-list-bulleted"></i> Lista
        </a>
      </li>
    </ul>
  </div>

  <div class="container-fluid">
    <div class="panel">
      <div class="panel-heading">
        <i class="zmdi zmdi-plus"></i> Nueva clase
      </div>
      <div class="panel-body">
        <form action="<?= SERVERURL ?>ajax/ajaxVideo.php"
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
                      <option value="<?= $c['id']; ?>">
                        <?= htmlspecialchars($c['Nombre']); ?>
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
                         value="<?= $dateNow; ?>" required>
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
              <!-- input oculto -->
              <input 
                type="file" 
                id="attachments" 
                name="attachments[]" 
                multiple 
                accept=".jpg,.png,.jpeg,.pdf,.ppt,.pptx,.doc,.docx"
                style="display:none;"
              >
              <!-- botón visible -->
              <button 
                type="button" 
                class="btn btn-info btn-raised btn-sm"
                onclick="document.getElementById('attachments').click()"
              >
                <i class="zmdi zmdi-attachment-alt"></i> Añadir archivo
              </button>
              <!-- lista de nombres -->
              <div id="attachment-names" style="margin-top:.75rem; color:rgb(12, 0, 0);"></div>
              <small class="form-text text-muted">
                Tamaño máximo 5MB. Permitidos PNG, JPG, PDF, WORD y PPT.
              </small>
            </div>
          </fieldset>

          <script>
          // Cuando el usuario seleccione archivos, mostramos sus nombres
          document.getElementById('attachments').addEventListener('change', function(){
            const list = Array.from(this.files).map(f => f.name).join(', ');
            document.getElementById('attachment-names').textContent = list;
          });
          </script>

          <p class="text-center">
            <button type="submit" class="btn btn-info btn-raised">
              <i class="zmdi zmdi-floppy"></i> Guardar
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
    $logout2 = new loginController();
    echo $logout2->login_session_force_destroy_controller(); 
endif;
?>
