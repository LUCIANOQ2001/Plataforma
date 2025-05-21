<?php if($_SESSION['userType'] === "Administrador"): ?>

<!-- Estilos inline para respetar el sidebar y quitar TODOS los fondos blancos -->
<style>
  /* 1) Ajusta aquí tu sidebar */
  .dashboard-contentPage {
    margin-left: 170px;             /* ← ancho de tu sidebar */
    padding: 20px;
    width: calc(100% - 270px);
    box-sizing: border-box;
    overflow: auto;
  }
  .dashboard-contentPage.full-box { width: auto; }

  /* 2) Anula cualquier fondo blanco de paneles, contenedores y tablas */
  .dashboard-contentPage .container-fluid,
  .dashboard-contentPage .panel,
  .dashboard-contentPage .panel-heading,
  .dashboard-contentPage .panel-body,
  .dashboard-contentPage .table-responsive,
  .dashboard-contentPage .table-responsive .table {
    background: transparent !important;
    color: #fff         !important;
  }

  /* 3) Bordes de tabla en gris para verse sobre fondo oscuro */
  .dashboard-contentPage .table-responsive .table th,
  .dashboard-contentPage .table-responsive .table td {
    border-color: #444 !important;
  }

  /* 4) Mantén color original del encabezado del panel-info */
  .dashboard-contentPage .panel-info .panel-heading {
    background-color: #5bc0de !important;
    color:             #fff    !important;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-account zmdi-hc-fw"></i>
        Usuarios <small>(Administradores / Docentes)</small>
      </h1>
    </div>
    <p class="lead">
      Bienvenido a la sección de usuarios; aquí podrá registrar nuevos administradores o docentes.
      (Los campos marcados con * son obligatorios).
    </p>
  </div>

  <div class="container-fluid">
    <ul class="breadcrumb breadcrumb-tabs">
      <li class="active">
        <a href="<?php echo SERVERURL; ?>admin/" class="btn btn-info">
          <i class="zmdi zmdi-plus"></i> Nuevo
        </a>
      </li>
      <li>
        <a href="<?php echo SERVERURL; ?>adminlist/" class="btn btn-success">
          <i class="zmdi zmdi-format-list-bulleted"></i> Lista
        </a>
      </li>
    </ul>
  </div>

  <?php 
    require_once "./controllers/adminController.php";
    $insAdmin = new adminController();
    if (isset($_POST['name'], $_POST['username'], $_POST['type'])) {
      echo $insAdmin->add_admin_controller();
    }
  ?>

  <div class="container-fluid">
    <div class="row">
      <div class="col-xs-12">
        <div class="panel panel-info">
          <div class="panel-heading">
            <h3 class="panel-title">
              <i class="zmdi zmdi-plus"></i> Nuevo Usuario
            </h3>
          </div>
          <div class="panel-body">
            <form action="" method="POST" enctype="multipart/form-data" autocomplete="off">
              <!-- Datos personales -->
              <fieldset>
                <legend><i class="zmdi zmdi-account-box"></i> Datos personales</legend>
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Nombres *</label>
                      <input
                        pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,30}"
                        class="form-control"
                        type="text"
                        name="name"
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                        required maxlength="30"
                      >
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Apellidos *</label>
                      <input
                        pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,30}"
                        class="form-control"
                        type="text"
                        name="lastname"
                        value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>"
                        required maxlength="30"
                      >
                    </div>
                  </div>
                </div>
              </fieldset>

              <!-- Datos de la cuenta -->
              <fieldset>
                <legend><i class="zmdi zmdi-key"></i> Datos de la cuenta</legend>
                <div class="row">
                  <!-- Username -->
                  <div class="col-sm-4">
                    <div class="form-group label-floating">
                      <label class="control-label">Nombre de usuario *</label>
                      <input
                        pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ]{1,15}"
                        class="form-control"
                        type="text"
                        name="username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required maxlength="15"
                      >
                    </div>
                  </div>
                  <!-- Tipo -->
                  <div class="col-sm-4">
                    <!-- Dentro de tu <form> de Nuevo Administrador -->
                    <div class="form-group label-floating">
                      <label class="control-label">Tipo de usuario *</label>
                      <select name="type" class="form-control" required>
                        <option value="Administrador">Administrador</option>
                        <option value="Docente">Docente</option>
                      </select>
                  </div>
                  </div>
                  <!-- Género -->
                  <div class="col-sm-4">
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
                </div>

                <div class="row">
                  <!-- Contraseña -->
                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Contraseña *</label>
                      <input class="form-control" type="password" name="password1" required maxlength="70">
                    </div>
                  </div>
                  <!-- Repetir contraseña -->
                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Repita la contraseña *</label>
                      <input class="form-control" type="password" name="password2" required maxlength="70">
                    </div>
                  </div>
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
    $logout2 = new loginController();
    echo $logout2->login_session_force_destroy_controller();
  endif; 
?>
