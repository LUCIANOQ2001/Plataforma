<?php if($_SESSION['userType'] === "Administrador"): ?>

<!-- Inline CSS: respeta sidebar, quita fondos blancos y centra el contenido -->
<style>
  /* 1) Desplaza el contenido para no tapar el sidebar (ancho típico 270px) */
  .dashboard-contentPage {
    margin-left: 170px;
    padding: 20px;
    width: calc(100% - 270px);
    box-sizing: border-box;
    overflow: auto;
  }
  .dashboard-contentPage.full-box {
    width: auto;
  }

  /* 2) Formularios y paneles transparentes con texto blanco */
  .dashboard-contentPage .container-fluid,
  .dashboard-contentPage .well,
  .dashboard-contentPage .panel,
  .dashboard-contentPage .panel-heading,
  .dashboard-contentPage .panel-body {
    background: transparent !important;
    color:      #fff        !important;
  }

  /* 3) El “well” de búsqueda centrado y sin fondo blanco */
  .dashboard-contentPage .well {
    padding: 20px;
    margin: 20px auto;
    max-width: 800px;
    border: 1px solid #555;
    box-shadow: none;
  }

  /* 4) Inputs y botones centrados */
  .dashboard-contentPage .form-control {
    background: transparent;
    border: 1px solid #555;
    color: #fff;
  }
  .dashboard-contentPage .btn-primary,
  .dashboard-contentPage .btn-danger {
    color: #fff !important;
  }

  /* 5) Titulares y párrafos centrados */
  .dashboard-contentPage .page-header,
  .dashboard-contentPage .lead {
    text-align: center;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-search zmdi-hc-fw"></i> Búsqueda
      </h1>
    </div>
    <p class="lead">
      Bienvenido: aquí puede buscar una clase por Docente o Título.
    </p>
  </div>

  <?php 
    require_once "./controllers/videoController.php";
    $insVideo = new videoController();

    // Guardar o destruir búsqueda
    if(isset($_POST['search_init']))   $_SESSION['search'] = trim($_POST['search_init']);
    if(isset($_POST['search_destroy'])) unset($_SESSION['search']);
  ?>

  <?php if(empty($_SESSION['search'])): ?>
    <div class="container-fluid">
      <form method="POST" class="well">
        <div class="form-group">
          <label class="control-label">¿Qué estás buscando?</label>
          <input name="search_init" class="form-control" type="text" required>
        </div>
        <p class="text-center">
          <button type="submit" class="btn btn-primary btn-raised btn-sm">
            <i class="zmdi zmdi-search"></i> Buscar
          </button>
        </p>
      </form>
    </div>
  <?php else: ?>
    <div class="container-fluid">
      <form method="POST" class="well">
        <p class="lead text-center">
          Su última búsqueda fue <strong>“<?php echo htmlspecialchars($_SESSION['search']); ?>”</strong>
        </p>
        <input type="hidden" name="search_destroy" value="1">
        <p class="text-center">
          <button type="submit" class="btn btn-danger btn-raised btn-sm">
            <i class="zmdi zmdi-delete"></i> Eliminar búsqueda
          </button>
        </p>
      </form>
    </div>

    <div class="container-fluid">
      <div class="panel panel-success">
        <div class="panel-heading">
          <h3 class="panel-title">
            <i class="zmdi zmdi-format-list-bulleted"></i>
            Lista de clases para “<?php echo htmlspecialchars($_SESSION['search']); ?>”
          </h3>
        </div>
        <div class="panel-body">
          <div class="table-responsive">
            <?php
              $parts = explode("/", $_GET['views']);
              $page  = isset($parts[1]) ? intval($parts[1]) : 1;
              echo $insVideo->pagination_video_search_controller(
                $page, 10, $_SESSION['search']
              );
            ?>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php else: 
    $logout2 = new loginController();
    echo $logout2->login_session_force_destroy_controller();
endif; ?>
