<?php if($_SESSION['userType'] === "Administrador"): ?>

<!-- Inline CSS para quitar fondos blancos, respetar sidebar y márgenes -->
<style>
  /* 1) Ajusta aquí el ancho de tu sidebar */
  .dashboard-contentPage {
    margin-left: 170px;               /* ← ancho real del sidebar */
    padding: 20px;                    /* Espacio interior */
    width: calc(100% - 270px);        /* Resto del ancho sin overflow */
    box-sizing: border-box;
    overflow: auto;                   /* Scroll interno si es necesario */
    background: transparent;          /* Nada de fondo blanco */
    color: #fff;                      /* Texto blanco */
  }
  .dashboard-contentPage.full-box {
    width: auto;                      /* Anula cualquier full-width */
  }

  /* 2) Anula todos los fondos blancos de los containers, paneles, etc. */
  .dashboard-contentPage .container-fluid,
  .dashboard-contentPage .breadcrumb-tabs,
  .dashboard-contentPage .panel,
  .dashboard-contentPage .panel-heading,
  .dashboard-contentPage .panel-body,
  .dashboard-contentPage fieldset,
  .dashboard-contentPage legend {
    background-color: transparent !important;
    color:            #fff        !important;
  }

  /* 3) Inputs y labels con fondo tenue y bordes visibles */
  .dashboard-contentPage .form-control,
  .dashboard-contentPage .control-label {
    background: rgba(255,255,255,0.05) !important;
    border:     1px solid #555      !important;
    color:      #fff                !important;
  }

  /* 4) Breadcrumb “Nuevo / Lista” contraste */
  .dashboard-contentPage .breadcrumb-tabs li {
    margin-right: 8px;
  }
  .dashboard-contentPage .breadcrumb-tabs a.btn-info {
    background-color: #0288d1 !important;
    border-color:     #0277bd !important;
    color:            #fff    !important;
  }
  .dashboard-contentPage .breadcrumb-tabs a.btn-success {
    background-color: #388e3c !important;
    border-color:     #2e7d32 !important;
    color:            #fff    !important;
  }

  /* 5) Mantén encabezado del panel-info en azul claro */
  .dashboard-contentPage .panel-info .panel-heading {
    background-color: #00bcd4 !important;
    color:            #fff    !important;
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
      (Los campos marcados con * son obligatorios para registrar un estudiante).
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
    if (isset($_POST['name'], $_POST['username'])) {
      echo $insStudent->add_student_controller();
    }
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
            <form action="" method="POST" enctype="multipart/form-data" autocomplete="off">
              <fieldset>
                <legend><i class="zmdi zmdi-account-box"></i> Datos personales</legend><br>
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
                        required
                        maxlength="30"
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
                        required
                        maxlength="30"
                      >
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Email</label>
                      <input
                        class="form-control"
                        type="email"
                        name="email"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        maxlength="70"
                      >
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
                      <input
                        pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ]{1,15}"
                        class="form-control"
                        type="text"
                        name="username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                        maxlength="15"
                      >
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
                      <input
                        class="form-control"
                        type="password"
                        name="password1"
                        required
                        maxlength="70"
                      >
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group label-floating">
                      <label class="control-label">Repita la contraseña *</label>
                      <input
                        class="form-control"
                        type="password"
                        name="password2"
                        required
                        maxlength="70"
                      >
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
