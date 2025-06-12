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
  /* ==== Paleta y reset ==== */
  :root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(174,12,12,0.61);
    --text-light:       #FFFFFF;
    --hover-accent:     rgba(209,177,110,0.2);
  }
  html, body {
    margin: 0; padding: 0;
    background: var(--primary-bg);
    color: var(--text-light);
    width: 100%; height: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
    font-family: 'RobotoCondensed', sans-serif;
  }
  /* Banner logo de fondo */
  .dashboard-banner {
    position: fixed; top: 0; left: 270px;
    width: calc(100% - 270px); height: 100%;
    background: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
    opacity: 0.05; pointer-events: none; z-index: 0;
  }
  /* Contenedor principal */
  .dashboard-contentPage {
    position: relative; z-index: 1;
    margin-left: 180px;
    width: calc(100% - 270px);
    padding: 0 20px auto;
    min-height: 100vh;
    box-sizing: border-box;
  }
  /* Ocultar buscador y menús innecesarios */
  .btn-options,
  .dropdown-toggle,
  .btn-search,
  i.zmdi-zmdi-search,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }
  /* Encabezados */
  .page-header h1 {
    font-size: 2rem;
    color: var(--primary-accent);
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
    margin-bottom: .5rem;
    text-align: center;
  }
  .lead {
    text-align: center;
    font-size: 1.1rem;
    color: rgba(255,255,255,0.7);
    margin: 0 auto 2rem;
    max-width: 800px;
  }
  /* Botones de regreso */
  .btn-back-home {
    background: var(--primary-accent) !important;
    color:  #2B2B2B;
    border: none !important;
    border-radius: .3rem;
    padding: .5rem 1rem;
    margin-right: .5rem;
    font-size: .9rem;
  }
  .btn-back-home:hover {
    background: var(--hover-accent) !important;
    color:  #2B2B2B !important;
    text-decoration: none;
  }
  /* Paneles */
  .panel {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    margin-bottom: 1rem;
    overflow: hidden;
  }
  .panel-heading {
    background: var(--primary-accent) !important;
    color:  #2B2B2B;
    font-weight: bold;
    text-align: center;
    padding: 1rem;
    font-size: 1.1rem;
  }
  .panel-body {
    padding: 1.5rem;
  }
  .panel-footer {
    background: rgba(0,0,0,0.2);
    padding: .75rem 1rem;
    text-align: right;
  }
  /* Form-control y labels */
  .form-control, .control-label, textarea {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid #555 !important;
    color: var(--text-light) !important;
  }
  /* Estilo para input file */
  input[type="file"].form-control {
    background: var(--secondary-bg) !important;
    border: 1px solid var(--primary-accent) !important;
    color: var(--text-light) !important;
    padding: .5rem;
    border-radius: .3rem;
    cursor: pointer;
  }
  input[type="file"].form-control::-webkit-file-upload-button {
    background: var(--primary-accent);
    color: var(--primary-bg);
    border: none;
    padding: .3rem .6rem;
    border-radius: .3rem;
    cursor: pointer;
    transition: background .3s;
  }
  input[type="file"].form-control::-webkit-file-upload-button:hover {
    background: var(--hover-accent);
  }
  /* Botones primarios */
  .btn-info, .btn-success, .btn-danger {
    color: #2B2B2B !important;
    font-weight: bold;
    border-radius: .3rem;
  }
  .btn-info {
    background: var(--primary-accent) !important;
    border: 1px solid var(--primary-accent) !important;
  }
  .btn-success {
    background: var(--hover-accent) !important;
    border: 1px solid var(--primary-accent) !important;
  }
  .btn-danger {
    background: #d32f2f !important;
    border: 1px solid #b71c1c !important;
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <!-- Botones de regreso -->
    <a href="<?php echo SERVERURL;?>sesion/<?php echo $ses['CursoId']; ?>/"
       class="btn-back-home"><i class="zmdi zmdi-arrow-left"></i> Mis Sesiones</a>
    <a href="<?php echo SERVERURL;?>foro/<?php echo $sesionId; ?>/"
       class="btn-back-home"><i class="zmdi zmdi-arrow-left"></i> Foros</a>

    <div class="page-header">
      <h1><i class="zmdi zmdi-comment-text"></i> Foros – <?= htmlspecialchars($ses['Titulo']) ?></h1>
      <?= $alert ?>
    </div>
    <p class="lead">
      <?php if ($foroId > 0): ?>
        Aquí puedes ver el contenido del foro y sus comentarios.
      <?php else: ?>
        <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
          Crea nuevos foros para esta sesión.
        <?php else: ?>
          Selecciona “Ver Foro” para participar.
        <?php endif; ?>
      <?php endif; ?>
    </p>
  </div>

  <?php if ($foroId === 0 && in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
    <div class="container-fluid">
      <button class="btn btn-info btn-raised"
              onclick="document.getElementById('formAdd').style.display='block'">
        <i class="zmdi zmdi-plus"></i> Nuevo Foro
      </button>
    </div>

    <div class="container-fluid" id="formAdd" style="display:none; margin-top:10px;">
      <div class="panel">
        <div class="panel-heading">Agregar Foro</div>
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
            <div class="form-group label-floating">
              <label class="control-label">Descripción *</label>
              <textarea name="descripcion" class="form-control" rows="3" required></textarea>
            </div>
            <div class="form-group label-floating">
              <label class="control-label">Fecha de cierre (opcional)</label>
              <input type="datetime-local" name="fechacierre" class="form-control">
            </div>
            <div class="text-right">
              <button type="submit" class="btn btn-success"><i class="zmdi zmdi-floppy"></i> Crear</button>
              <button type="button" class="btn btn-danger"
                      onclick="document.getElementById('formAdd').style.display='none'">
                Cancelar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- LISTADO DE FOROS -->
  <?php if ($foroId === 0): ?>
    <div class="container-fluid" style="margin-top:15px;">
      <?php if (empty($foros)): ?>
        <div class="panel"><div class="panel-body text-center">No hay foros en esta sesión.</div></div>
      <?php else: ?>
        <?php foreach ($foros as $f): ?>
          <div class="panel">
            <div class="panel-heading"><?= htmlspecialchars($f['Titulo']) ?></div>
            <div class="panel-body">
              <p style="font-size:.9rem; color:rgba(255,255,255,0.7);">
                Creado: <?= htmlspecialchars($f['FechaSubida']) ?>
                <?php if (!empty($f['FechaCierre'])): ?>
                  | Cierre: <?= htmlspecialchars($f['FechaCierre']) ?>
                <?php endif; ?>
              </p>
              <p><?= nl2br(htmlspecialchars(substr($f['Descripcion'],0,200))) ?>…</p>
              <?php if (!empty($f['Archivo'])): ?>
                <p><strong>Archivo:</strong>
                  <a href="<?= SERVERURL.'attachments/foros/'.urlencode($f['Archivo']) ?>"
                     target="_blank"><?= htmlspecialchars($f['Archivo']) ?></a>
                </p>
              <?php endif; ?>
            </div>
            <div class="panel-footer">
              <a href="<?= SERVERURL."foro/{$sesionId}/{$f['id']}/" ?>"
                 class="btn btn-info btn-sm"><i class="zmdi zmdi-eye"></i> Ver Foro</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <?php return; ?>
  <?php endif; ?>

  <!-- DETALLE DE UN FORO -->
  <div class="container-fluid" style="margin-top:15px;">


    <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
      <div class="text-right" style="margin:1rem 0;">
        <form method="POST" style="display:inline;">
          <input type="hidden" name="delete_foro" value="<?= $foro['id'] ?>">
          <button type="submit" class="btn btn-danger btn-sm"><i class="zmdi zmdi-delete"></i> Eliminar</button>
        </form>
        <button id="btnEdit" class="btn btn-success btn-sm"><i class="zmdi zmdi-edit"></i> Editar</button>
      </div>

      <div id="editForm" style="display:none; margin-bottom:1rem;">
        <div class="panel">
          <div class="panel-heading">Editar Foro</div>
          <div class="panel-body">
            <form method="POST" enctype="multipart/form-data" autocomplete="off">
              <input type="hidden" name="edit_foro" value="<?= $foro['id'] ?>">
              <div class="form-group label-floating">
                <label class="control-label">Título *</label>
                <input name="titulo" class="form-control" type="text"
                       value="<?= htmlspecialchars($foro['Titulo']) ?>" required>
              </div>
              <div class="form-group label-floating">
                <label class="control-label">Archivo (reemplaza existente)</label>
                <input name="archivo" class="form-control" type="file">
              </div>
              <?php if (!empty($foro['Archivo'])): ?>
                <p style="color:rgba(255,255,255,0.7);">
                  Actual: <a href="<?= SERVERURL.'attachments/foros/'.urlencode($foro['Archivo']) ?>"
                             target="_blank"><?= htmlspecialchars($foro['Archivo']) ?></a>
                </p>
              <?php endif; ?>
              <div class="form-group label-floating">
                <label class="control-label">Descripción *</label>
                <textarea name="descripcion" class="form-control" rows="3" required><?= htmlspecialchars($foro['Descripcion']) ?></textarea>
              </div>
              <div class="form-group label-floating">
                <label class="control-label">Fecha de cierre (opcional)</label>
                <input type="datetime-local" name="fechacierre" class="form-control"
                  value="<?= $foro['FechaCierre'] ? date('Y-m-d\TH:i',strtotime($foro['FechaCierre'])) : '' ?>">
              </div>
              <div class="text-right">
                <button type="submit" class="btn btn-success"><i class="zmdi zmdi-floppy"></i> Guardar</button>
                <button type="button" class="btn btn-danger" onclick="document.getElementById('editForm').style.display='none'">Cancelar</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <script>
        document.getElementById('btnEdit').addEventListener('click', () => {
          document.getElementById('editForm').style.display = 'block';
        });
      </script>
    <?php endif; ?>

    <!-- Contenido del foro -->
    <div class="panel">
      <div class="panel-heading"><?= htmlspecialchars($foro['Titulo']) ?></div>
      <div class="panel-body">
        <p style="font-size:.9rem;color:rgba(255,255,255,0.7);">
          Creado: <?= htmlspecialchars($foro['FechaSubida']) ?>
          <?php if (!empty($foro['FechaCierre'])): ?>
            | Cierre: <?= htmlspecialchars($foro['FechaCierre']) ?>
          <?php endif; ?>
        </p>
        <p><?= nl2br(htmlspecialchars($foro['Descripcion'])) ?></p>
        <?php if (!empty($foro['Archivo'])): ?>
          <p><strong>Archivo:</strong>
            <a href="<?= SERVERURL.'attachments/foros/'.urlencode($foro['Archivo']) ?>"
               target="_blank"><?= htmlspecialchars($foro['Archivo']) ?></a>
          </p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Comentarios -->
    <?php foreach ($fc->list_comentarios($foroId) as $c): ?>
      <div class="panel">
        <div class="panel-heading" style="background:#444;font-size:1rem;">
          <strong><?= htmlspecialchars($c['NombreUsuario']) ?></strong>
          <span style="font-size:.85rem;color: #2B2B2B;">– <?= htmlspecialchars($c['Fecha']) ?></span>
        </div>
        <div class="panel-body">
          <p><?= nl2br(htmlspecialchars($c['Comentario'])) ?></p>
          <?php if (!empty($c['Adjunto'])): ?>
            <p><strong>Archivo:</strong>
              <a href="<?= SERVERURL.'attachments/foros/'.urlencode($c['Adjunto']) ?>"
                 download><?= htmlspecialchars($c['Adjunto']) ?></a>
            </p>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Formulario de comentario -->
    <div class="panel">
      <div class="panel-heading">Escribe tu comentario</div>
      <div class="panel-body">
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
          <div class="form-group">
            <textarea name="comentario" class="form-control" rows="3" placeholder="Escribe tu comentario…" required></textarea>
          </div>
          <div class="form-group">
            <label>Adjunto (opcional)</label>
            <input type="file" name="adjunto" class="form-control">
          </div>
          <input type="hidden" name="idc" value="1">
          <div class="text-right">
            <button type="submit" class="btn btn-info"><i class="zmdi zmdi-mail-send"></i> Enviar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
