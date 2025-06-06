<?php 
// views/contents/foro-view.php

// 1) Sesión y control de acceso
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente','Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

// 2) Controladores necesarios
require_once __DIR__ . '/../../controllers/foroController.php';
require_once __DIR__ . '/../../controllers/sesionController.php';

$fc = new foroController();
$sc = new sesionController();

// 3) IDs de URL: /foro/{sesionId}/ o /foro/{sesionId}/{foroId}/
$parts    = explode('/', trim($_GET['views'], '/'));
$sesionId = intval($parts[1] ?? 0);
$foroId   = intval($parts[2] ?? 0);

// 4) Obtener datos de la sesión
$stmtSes = $sc->get_sesion_by_id_controller($sesionId);
if ($stmtSes->rowCount() === 0) {
    echo '<div class="alert alert-danger">Sesión no encontrada.</div>';
    return;
}
$ses = $stmtSes->fetch(PDO::FETCH_ASSOC);

// 5) Manejo de POST (crear / editar / eliminar foro o añadir comentario)
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eliminar foro
    if (isset($_POST['delete_foro'])) {
        $alert = $fc->delete_foro(intval($_POST['delete_foro']));
        echo "<script>window.location='".SERVERURL."foro/{$sesionId}/';</script>";
        exit;
    }
    // Editar foro
    if (isset($_POST['edit_foro'])) {
        $alert = $fc->update_foro(intval($_POST['edit_foro']), $_POST, $_FILES);
        echo "<script>location.replace(location.pathname);</script>";
        exit;
    }
    // Agregar comentario
    if (isset($_POST['idc'])) {
        $alert = $fc->add_comentario($foroId, $_SESSION['userKey'], $_POST, $_FILES);
        echo "<script>location.replace(location.pathname);</script>";
        exit;
    }
    // Agregar nuevo foro
    if (isset($_POST['add_foro'])) {
        $alert = $fc->add_foro($sesionId, $_POST, $_FILES);
        echo "<script>location.replace(location.pathname);</script>";
        exit;
    }
}

// 6) Cargar lista de foros (a usar en la vista “Listado”)
$foros = $fc->list_foros_by_sesion($sesionId);

// 7) Si estamos en detalle de un foro ($foroId > 0), traemos sus datos
$foro = null;
if ($foroId > 0) {
    $foro = $fc->get_foro($foroId);
    if (!$foro) {
        echo '<div class="alert alert-danger text-center">Foro no encontrado.</div>';
        return;
    }
}
?>

<style>
  
  /* Si la lupa tiene la clase .btn-search o un <i class="zmdi zmdi-search"> */
  .btn-search,
  i.zmdi.zmdi-search {
    display: none !important;
  }
  html, body {
    background-color: #1e1f28;
    color: #fff;
    margin: 0; padding: 0;
    width: 100%; height: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
  }
  .dashboard-contentPage {
    margin-left: 170px;
    padding: 0 30px;
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
    margin-bottom: 1rem;
  }
  .panel-heading {
    background-color: #00bcd4;
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
    color: #fff;
  }
  .panel-footer {
    padding: 12px 15px;
    background: #232334;
    border-top: 1px solid #444;
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
    text-align: right;
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
  .btn-back-home {
    background-color: #607d8b !important;
    border-color:     #455a64 !important;
    color:            #fff !important;
    margin-bottom: 20px;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <!-- Botón para volver a Mis Sesiones -->
    <a href="<?php echo SERVERURL;?>sesion/<?php echo $ses['CursoId']; ?>/"
       class="btn btn-back-home btn-sm">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Sesiones
    </a>

    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-comment-text"></i>
        Foros – <?php echo htmlspecialchars($ses['Titulo']); ?>
      </h1>
      <?php echo $alert; ?>
    </div>
    <p class="lead">
      <?php if ($foroId > 0): ?>
        Aquí puedes ver el contenido del foro y sus comentarios.
      <?php else: ?>
        <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
          Aquí puedes crear nuevos foros para esta sesión.
        <?php else: ?>
          Aquí se muestran los foros disponibles. Haz clic en “Ver Foro” para participar.
        <?php endif; ?>
      <?php endif; ?>
    </p>
  </div>

  <!-- ====== FORMULARIO “Nuevo Foro” (solo Admin/Docente) ====== -->
  <?php if ($foroId === 0 && in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
    <div class="container-fluid">
      <button class="btn btn-info btn-raised"
              onclick="document.getElementById('formAdd').style.display='block'">
        <i class="zmdi zmdi-plus"></i> Nuevo Foro
      </button>
    </div>

    <div class="container-fluid" id="formAdd" style="display:none; margin-top:10px;">
      <div class="panel panel-info">
        <div class="panel-heading">
          <h3 class="panel-title"><i class="zmdi zmdi-plus-box"></i> Agregar Foro</h3>
        </div>
        <div class="panel-body">
          <form method="POST" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="add_foro" value="1">
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Título *</label>
                  <input name="titulo" class="form-control" type="text" required>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group label-floating">
                  <label class="control-label">Archivo (opcional)</label>
                  <input name="archivo" class="form-control" type="file">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-12">
                <div class="form-group label-floating">
                  <label class="control-label">Descripción *</label>
                  <textarea name="descripcion" class="form-control" rows="3" required></textarea>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-4">
                <div class="form-group label-floating">
                  <label class="control-label">Fecha de cierre (opcional)</label>
                  <input type="datetime-local" name="fechacierre" class="form-control">
                </div>
              </div>
              <div class="col-sm-8 text-right">
                <button type="submit" class="btn btn-success btn-raised">
                  <i class="zmdi zmdi-floppy"></i> Crear Foro
                </button>
                <button type="button" class="btn btn-default" 
                        onclick="document.getElementById('formAdd').style.display='none'">
                  Cancelar
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>


  <!-- ====== LISTADO DE FOROS ====== -->
  <?php if ($foroId === 0): ?>
    <div class="container-fluid" style="margin-top:15px;">
      <?php if (empty($foros)): ?>
        <div class="alert alert-info text-center">No hay foros en esta sesión.</div>
      <?php else: ?>
        <?php foreach ($foros as $f): ?>
          <div class="panel panel-success">
            <div class="panel-heading"><?php echo htmlspecialchars($f['Titulo']); ?></div>
            <div class="panel-body">
              <p style="font-size:0.9rem; color:#ccc;">
                Creado: <?php echo htmlspecialchars($f['FechaSubida']); ?>
                <?php if (!empty($f['FechaCierre'])): ?>
                  | Cierre: <?php echo htmlspecialchars($f['FechaCierre']); ?>
                <?php endif; ?>
              </p>
              <p><?php echo nl2br(htmlspecialchars(substr($f['Descripcion'],0,200))); ?>…</p>
              <?php if (!empty($f['Archivo'])): ?>
                <p>
                  <strong>Archivo:</strong>
                  <a href="<?php echo SERVERURL.'attachments/foros/'.urlencode($f['Archivo']); ?>"
                     target="_blank">
                    <?php echo htmlspecialchars($f['Archivo']); ?>
                  </a>
                </p>
              <?php endif; ?>
            </div>
            <div class="panel-footer">
              <a href="<?php echo SERVERURL."foro/{$sesionId}/{$f['id']}/"; ?>"
                 class="btn btn-info btn-sm btn-raised">
                <i class="zmdi zmdi-eye"></i> Ver Foro
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <?php return; // Fin de listado. Si estamos en lista, no cargamos detalle. ?>
  <?php endif; ?>


  <!-- ====== DETALLE DE UN FORO (cuando $foroId > 0) ====== -->
  <div class="container-fluid" style="margin-top:15px;">
    <!-- Botón para volver a Foros de esta sesión -->
    <a href="<?php echo SERVERURL."foro/{$sesionId}/"; ?>"
       class="btn btn-info btn-sm btn-raised btn-back-home">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Foros
    </a>

    <!-- Botón para volver a Mis Sesiones -->
    <a href="<?php echo SERVERURL."sesion/{$ses['CursoId']}/"; ?>"
       class="btn btn-back-home btn-sm" style="margin-left:10px;">
      <i class="zmdi zmdi-arrow-left"></i> Volver a Sesiones
    </a>

    <!-- Si es Admin/Docente, mostramos botones de Eliminar/Editar -->
    <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
      <div class="text-right" style="margin-top:10px; margin-bottom:10px;">
        <form method="POST" style="display:inline;">
          <!-- hidden delete_foro imprimirá el ID del foro a eliminar -->
          <input type="hidden" name="delete_foro" value="<?php echo $foro['id']; ?>">
          <button type="submit" class="btn btn-danger btn-sm btn-raised"
                  onclick="return confirm('¿Eliminar este foro?');">
            <i class="zmdi zmdi-delete"></i> Eliminar
          </button>
        </form>
        <button id="btnEdit" class="btn btn-success btn-sm btn-raised">
          <i class="zmdi zmdi-edit"></i> Editar
        </button>
      </div>

      <!-- Formulario de edición (inicialmente oculto) -->
      <div id="editForm" style="display:none; margin-bottom:15px;">
        <div class="panel panel-info">
          <div class="panel-heading">Editar Foro</div>
          <div class="panel-body">
            <form method="POST" enctype="multipart/form-data" autocomplete="off">
              <input type="hidden" name="edit_foro" value="<?php echo $foro['id']; ?>">

              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group label-floating">
                    <label class="control-label">Título *</label>
                    <input name="titulo" class="form-control" type="text"
                           value="<?php echo htmlspecialchars($foro['Titulo']); ?>" required>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group label-floating">
                    <label class="control-label">Archivo (reemplaza existente)</label>
                    <input name="archivo" class="form-control" type="file">
                  </div>
                  <?php if (!empty($foro['Archivo'])): ?>
                    <p style="margin-top:5px; color:#ccc;">
                      Archivo actual: 
                      <a href="<?php echo SERVERURL.'attachments/foros/'.urlencode($foro['Archivo']); ?>"
                         target="_blank" style="color:#fff; text-decoration:underline;">
                        <?php echo htmlspecialchars($foro['Archivo']); ?>
                      </a>
                    </p>
                  <?php endif; ?>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-12">
                  <div class="form-group label-floating">
                    <label class="control-label">Descripción *</label>
                    <textarea name="descripcion" class="form-control" rows="3" required><?php echo htmlspecialchars($foro['Descripcion']); ?></textarea>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-4">
                  <div class="form-group label-floating">
                    <label class="control-label">Fecha de cierre (opcional)</label>
                    <input type="datetime-local" name="fechacierre" class="form-control"
                      value="<?php echo $foro['FechaCierre'] 
                        ? date('Y-m-d\TH:i', strtotime($foro['FechaCierre'])) 
                        : ''; ?>">
                  </div>
                </div>
                <div class="col-sm-8 text-right">
                  <button type="submit" class="btn btn-success btn-sm btn-raised">
                    <i class="zmdi zmdi-floppy"></i> Guardar cambios
                  </button>
                  <button type="button" class="btn btn-default btn-sm"
                          onclick="document.getElementById('editForm').style.display='none'">
                    Cancelar
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Panel principal del foro -->
    <div class="panel panel-success">
      <div class="panel-heading"><?php echo htmlspecialchars($foro['Titulo']); ?></div>
      <div class="panel-body">
        <p style="font-size:0.9rem; color:#ccc;">
          Creado: <?php echo htmlspecialchars($foro['FechaSubida']); ?>
          <?php if (!empty($foro['FechaCierre'])): ?>
            | Cierre: <?php echo htmlspecialchars($foro['FechaCierre']); ?>
          <?php endif; ?>
        </p>
        <p><?php echo nl2br(htmlspecialchars($foro['Descripcion'])); ?></p>
        <?php if (!empty($foro['Archivo'])): ?>
          <p>
            <strong>Archivo:</strong>
            <a href="<?php echo SERVERURL.'attachments/foros/'.urlencode($foro['Archivo']); ?>"
               target="_blank">
              <?php echo htmlspecialchars($foro['Archivo']); ?>
            </a>
          </p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Comentarios del foro -->
    <?php foreach ($fc->list_comentarios($foroId) as $c): ?>
      <div class="panel">
        <div class="panel-heading" style="background:#444; font-size:16px;">
          <strong><?php echo htmlspecialchars($c['NombreUsuario']); ?></strong>
           &nbsp;<span style="font-size:0.85rem; color:#ccc;">– <?php echo htmlspecialchars($c['Fecha']); ?></span>
        </div>
        <div class="panel-body">
          <p><?php echo nl2br(htmlspecialchars($c['Comentario'])); ?></p>
          <?php if (!empty($c['Adjunto'])): ?>
            <p>
              <strong>Archivo:</strong>
              <a href="<?php echo SERVERURL.'attachments/foros/'.urlencode($c['Adjunto']); ?>"
                 download="<?php echo htmlspecialchars($c['Adjunto']); ?>">
                <?php echo htmlspecialchars($c['Adjunto']); ?>
              </a>
            </p>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Formulario para agregar comentario (estudiante o docente) -->
    <div class="panel panel-success">
      <div class="panel-heading">Escribe tu comentario</div>
      <div class="panel-body">
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
          <div class="row">
            <div class="col-sm-12">
              <textarea name="comentario" class="form-control" rows="3" placeholder="Escribe tu comentario…" required></textarea>
            </div>
          </div>
          <div class="row" style="margin-top:10px;">
            <div class="col-sm-6">
              <div class="form-group label-floating">
                <label class="control-label">Adjunto (opcional)</label>
                <input type="file" name="adjunto" class="form-control">
              </div>
            </div>
            <div class="col-sm-6 text-right">
              <input type="hidden" name="idc" value="1">
              <button type="submit" class="btn btn-info btn-raised">
                <i class="zmdi zmdi-mail-send"></i> Enviar
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <script>
      document.getElementById('btnEdit')?.addEventListener('click', function(){
        document.getElementById('editForm').style.display = 'block';
      });
    </script>
  </div>
</section>
