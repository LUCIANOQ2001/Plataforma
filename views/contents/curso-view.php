<?php 
// Sólo Administradores y Docentes pueden ver esta página
if(!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])) {
    $logout2 = new loginController();
    echo $logout2->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/cursoController.php';
$insCurso = new cursoController();

// 1) Procesar POST
$alert = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alert = $insCurso->add_curso_controller();
}

// 2) Listar docentes (de cuenta)
$docentes = $insCurso->list_docentes_controller();
?>
<style>
  .dashboard-contentPage {
    margin-left: 170px;
    padding: 20px;
    width: calc(100% - 270px);
    box-sizing: border-box;
    overflow: auto;
  }
  .dashboard-contentPage.full-box { width: auto; }

  .dashboard-contentPage .container-fluid,
  .dashboard-contentPage .panel,
  .dashboard-contentPage .panel-heading,
  .dashboard-contentPage .panel-body,
  .dashboard-contentPage .breadcrumb-tabs {
    background: transparent !important;
    color: #fff !important;
  }
  .dashboard-contentPage .panel-info .panel-heading {
    background-color: #5bc0de !important;
    color: #fff !important;
  }
  .dashboard-contentPage .breadcrumb-tabs li a.btn-info {
    background-color: #0288d1 !important;
    border-color:     #0277bd !important;
    color:            #fff    !important;
  }
  .dashboard-contentPage .breadcrumb-tabs li a.btn-success {
    background-color: #388e3c !important;
    border-color:     #2e7d32 !important;
    color:            #fff    !important;
  }
  .dashboard-contentPage .breadcrumb-tabs li { margin-right: 10px; }

  .dashboard-contentPage .form-control,
  .dashboard-contentPage textarea {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid #555      !important;
    color:  #fff                 !important;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-book zmdi-hc-fw"></i> Cursos <small>(Registro)</small>
      </h1>
    </div>
    <p class="lead">
      Aquí puedes crear un nuevo curso. (Los campos marcados con * son obligatorios).
    </p>
  </div>

  <div class="container-fluid">
    <?php echo $alert; ?>
    <ul class="breadcrumb breadcrumb-tabs">
      <li class="active">
        <a href="<?php echo SERVERURL; ?>curso/" class="btn btn-info">
          <i class="zmdi zmdi-plus"></i> Nuevo
        </a>
      </li>
      <li>
        <a href="<?php echo SERVERURL; ?>cursolist/" class="btn btn-success">
          <i class="zmdi zmdi-format-list-bulleted"></i> Lista
        </a>
      </li>
    </ul>
  </div>

  <div class="container-fluid">
    <div class="panel panel-info">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="zmdi zmdi-plus"></i> Nuevo Curso</h3>
      </div>
      <div class="panel-body">
        <form action="" method="POST" autocomplete="off">
          <fieldset>
            <legend><i class="zmdi zmdi-label"></i> Datos del Curso</legend>
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Nombre del Curso *</label>
                  <input name="nombre" class="form-control" type="text" required maxlength="255">
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Descripción *</label>
                  <textarea name="descripcion" class="form-control" rows="2" required></textarea>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Docente *</label>
                  <?php if($_SESSION['userType'] === "Administrador"): ?>
                    <select name="docente_codigo" class="form-control" required>
                      <option value="">Seleccione...</option>
                      <?php foreach($docentes as $d): ?>
                        <option value="<?php echo $d['Codigo']; ?>">
                          <?php echo htmlspecialchars("{$d['Apellidos']}, {$d['Nombres']}"); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  <?php else: /* Docente logueado */ ?>
                    <input type="text" class="form-control"
                           value="<?php echo $_SESSION['userName']; ?>" disabled>
                    <input type="hidden" name="docente_codigo"
                           value="<?php echo $_SESSION['userKey']; ?>">
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </fieldset>
          <p class="text-center">
            <button type="submit" class="btn btn-info btn-raised btn-sm">
              <i class="zmdi zmdi-floppy"></i> Guardar Curso
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>
</section>
