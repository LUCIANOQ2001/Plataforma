<?php if($_SESSION['userType'] === "Administrador"): ?>

<!-- Estilos modernos oscuros unificados -->
<style>
  html, body {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    background-color: #1e1f28;
    color: #fff;
    overflow-x: hidden;
    box-sizing: border-box;
  }

  .dashboard-contentPage {
    margin-left: 170px;
    padding: 30px;
    min-height: 100vh;
    width: calc(100% - 170px);
    background-color: #1e1f28;
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
    background-color: #43a047 !important;
    border: 1px solid #388e3c !important;
  }

  .breadcrumb-tabs .btn-info:hover {
    background-color: #039be5 !important;
  }

  .breadcrumb-tabs .btn-success:hover {
    background-color: #4caf50 !important;
  }

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
    text-align: center;
    padding: 12px 15px;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
  }

  .panel-body {
    padding: 20px;
  }

  .form-control, .control-label {
    background: rgba(239, 235, 235, 0.05) !important;
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
        <i class="zmdi zmdi-face zmdi-hc-fw"></i>
        Usuarios <small>(Estudiantes)</small>
      </h1>
    </div>
    <p class="lead">
      Bienvenido a la sección de estudiantes, aquí podrás registrar nuevos estudiantes
      (Los campos marcados con * son obligatorios).
    </p>
  </div>

  <div class="container-fluid">
    <ul class="breadcrumb breadcrumb-tabs">
      <li class="active">
        <a href="<?php echo SERVERURL; ?>student/" class="btn btn-info">
          <i class="zmdi zmdi-plus"></i> Nuevo
        </a>
      </li>
      <li>
        <a href="<?php echo SERVERURL; ?>studentlist/" class="btn btn-success">
          <i class="zmdi zmdi-format-list-bulleted"></i> Lista
        </a>
      </li>
    </ul>
  </div>

  <?php 
    require_once "./controllers/studentController.php";
    $insStudent = new studentController();
    if($_SERVER['REQUEST_METHOD']==='POST'){
      echo $insStudent->add_student_controller($_POST);
    }
    $cursosList = $insStudent->list_cursos_controller();
  ?>

  <div class="container-fluid">
    <div class="row">
      <div class="col-xs-12">
        <div class="panel panel-info">
          <div class="panel-heading">
            <h3 class="panel-title">
              <i class="zmdi zmdi-plus"></i> Nuevo Estudiante
            </h3>
          </div>
          <div class="panel-body">
            <form action="" method="POST" autocomplete="off">
              <fieldset>
                <legend><i class="zmdi zmdi-account-box"></i> Datos personales</legend><br>
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Nombres *</label>
                      <input pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,30}" class="form-control" type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required maxlength="30">
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Apellidos *</label>
                      <input pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,30}" class="form-control" type="text" name="lastname" value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>" required maxlength="30">
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Email</label>
                      <input class="form-control" type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" maxlength="70">
                    </div>
                  </div>
                </div>
              </fieldset>
              <br>
              <fieldset>
                <legend><i class="zmdi zmdi-key"></i> Datos de la cuenta</legend><br>
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Nombre de usuario *</label>
                      <input pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ]{1,15}" class="form-control" type="text" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required maxlength="15">
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Género</label>
                      <select name="gender" class="form-control">
                        <?php if(isset($_POST['gender'])): ?>
                          <option value="<?php echo $_POST['gender']; ?>">
                            <?php echo $_POST['gender']; ?> (Actual)
                          </option>
                        <?php endif; ?>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Contraseña *</label>
                      <input class="form-control" type="password" name="password1" required maxlength="70">
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Repita la contraseña *</label>
                      <input class="form-control" type="password" name="password2" required maxlength="70">
                    </div>
                  </div>
                </div>
              </fieldset>

              <fieldset>
                <legend><i class="zmdi zmdi-book"></i> Cursos asignados *</legend>
                <div class="form-group">
                  <select name="cursos[]" class="form-control" multiple required>
                    <?php foreach($cursosList as $cu): ?>
                      <option value="<?php echo $cu['id']; ?>">
                        <?php echo htmlspecialchars($cu['Nombre']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <small class="help-block">Mantén pulsada Ctrl (o Cmd) para seleccionar varios.</small>
                </div>
              </fieldset>

              <p class="text-center">
                <button type="submit" class="btn btn-info btn-raised btn-sm">
                  <i class="zmdi zmdi-floppy"></i> Guardar
                </button>
              </p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php 
  else:
    (new loginController())->login_session_force_destroy_controller();
  endif;
?>
