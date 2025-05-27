<?php if($_SESSION['userType']=="Administrador"): ?>
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

  .container-fluid, .panel, .panel-heading, .panel-body, .form-control {
    background: transparent !important;
    color: #fff !important;
  }

  .dashboard-contentPage {
    margin-left: 170px;
    padding: 30px;
    width: calc(100% - 170px);
    min-height: 100vh;
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

  .panel {
    background: #2c2d3f;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
    border: 1px solid #3c3d4f;
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

  .form-group.label-floating label {
    color: #ccc;
  }

  .form-control {
    background: rgba(255,255,255,0.05);
    border: 1px solid #555;
    color: #fff;
  }

  .btn-success {
    background-color: #388e3c;
    border: 1px solid #2e7d32;
    color: #fff;
  }

  .btn-info {
    background-color: #0288d1;
    border: 1px solid #0277bd;
    color: #fff;
  }

  .btn:hover {
    opacity: 0.9;
  }
</style>

<div class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles"><i class="zmdi zmdi-settings zmdi-hc-fw"></i> Datos del estudiante</h1>
    </div>
    <p class="lead">
      Bienvenido a la sección de actualización de los datos de los estudiantes. Acá podrá actualizar la información personal de los estudiantes registrados en el sistema.
    </p>
  </div>
<?php 
  require_once "./controllers/studentController.php";

  $studentIns = new studentController();

  if(isset($_POST['code'])){
    echo $studentIns->update_student_controller();
  }

  $code=explode("/", $_GET['views']);

  $data=$studentIns->data_student_controller("Only",$code[1]);
  if($data->rowCount()>0):
    $rows=$data->fetch();
?>
<p class="text-center">
  <a href="<?php echo SERVERURL; ?>studentlist/" class="btn btn-info btn-raised btn-sm">
    <i class="zmdi zmdi-long-arrow-return"></i> Volver
  </a>
</p>
<div class="container-fluid">
  <div class="row">
    <div class="col-xs-12">
      <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="zmdi zmdi-refresh"></i> Actualizar datos</h3>
        </div>
        <div class="panel-body">
          <form action="" method="POST" enctype="multipart/form-data" autocomplete="off">
            <fieldset>
              <legend><i class="zmdi zmdi-account-box"></i> Datos personales</legend><br>
              <input type="hidden" name="code" value="<?php echo $rows['Codigo']; ?>">
              <div class="container-fluid">
                <div class="row">
                  <div class="col-xs-12 col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Nombres *</label>
                      <input pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,30}" class="form-control" type="text" name="name" value="<?php echo $rows['Nombres']; ?>" required maxlength="30">
                    </div>
                  </div>
                  <div class="col-xs-12 col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Apellidos *</label>
                      <input pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,30}" class="form-control" type="text" name="lastname" value="<?php echo $rows['Apellidos']; ?>" required maxlength="30">
                    </div>
                  </div>
                  <div class="col-xs-12 col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Email</label>
                      <input class="form-control" type="email" name="email" value="<?php echo $rows['Email']; ?>">
                    </div>
                  </div>
                </div>
              </div>
            </fieldset>
            <p class="text-center">
              <button type="submit" class="btn btn-success btn-raised btn-sm">
                <i class="zmdi zmdi-refresh"></i> Guardar cambios
              </button>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<?php else: ?>
  <p class="lead text-center">Lo sentimos ocurrió un error inesperado</p>
<?php
    endif;
  else:
    $logout2 = new loginController();
    echo $logout2->login_session_force_destroy_controller(); 
  endif;
?>
