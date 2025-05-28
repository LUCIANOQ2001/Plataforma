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

// 3) IDs de URL: /foro/{sesionId}/{foroId}/
$parts    = explode('/', trim($_GET['views'], '/'));
$sesionId = intval($parts[1] ?? 0);
$foroId   = intval($parts[2] ?? 0);

// 4) Obtener CursoId desde la sesión
$stmtSes = $sc->get_sesion_by_id_controller($sesionId);
if ($stmtSes->rowCount() === 0) {
    echo '<div class="alert alert-danger">Sesión no encontrada.</div>';
    return;
}
$sesion  = $stmtSes->fetch(PDO::FETCH_ASSOC);
$cursoId = $sesion['CursoId'];

// 5) Manejo de POST (crear, editar, eliminar foro o añadir comentario)
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_foro'])) {
        $alert = $fc->delete_foro($foroId);
        echo "<script>window.location='".SERVERURL."foro/{$sesionId}/';</script>";
        exit;
    }
    if (isset($_POST['edit_foro'])) {
        $alert = $fc->update_foro($foroId, $_POST, $_FILES);
        echo "<script>location.replace(location.pathname);</script>";
        exit;
    }
    if (isset($_POST['idc'])) {
        $alert = $fc->add_comentario($foroId, $_SESSION['userKey'], $_POST, $_FILES);
    } else {
        $alert = $fc->add_foro($sesionId, $_POST, $_FILES);
    }
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}

// 6) Listar foros de la sesión
$foros = $fc->list_foros_by_sesion($sesionId);
?>
<style>
  html, body { margin:0; padding:0; background:#1e1f28; color:#fff;
               width:100%; height:100%; overflow-x:hidden; box-sizing:border-box; }
  .dashboard-contentPage { margin-left:170px; padding:30px;
                           width:calc(100% - 170px); background:#1e1f28;
                           min-height:100vh; }
  .forum-container { max-width:800px; margin:0 auto; }
  .panel { background:#2c2d3f; border:1px solid #3c3d4f;
           border-radius:12px; box-shadow:0 4px 18px rgba(0,0,0,0.5);
           margin-bottom:1rem; }
  .panel-heading { background:#00bcd4!important; color:#fff;
                   padding:12px; font-weight:bold; text-align:center;
                   border-radius:12px 12px 0 0; }
  .forum-item, .comment-item { padding:15px; border-radius:0 0 8px 8px; }
  .forum-date, .comment-date { font-size:.85rem; color:#888;
                                margin-bottom:10px; }
  .form-control, textarea.form-control {
    background:#1f2235; border:1px solid #444; color:#fff;
  }
  .btn-back, .btn-create, .btn-send, .btn-edit, .btn-delete {
    margin-top:10px;
    color:#ccc;
  }
    /* aquí agregas tus colores nuevos */
  .btn-back {
    background-color: #e91e63 !important;
    border-color:     #c2185b !important;
    color:            #fff !important;
  }
  .btn-info {
    background-color: #9c27b0 !important;
    border-color:     #7b1fa2 !important;
    color:            #fff !important;
  }
  .btn-create {
    background-color: #4caf50 !important;
    border-color:     #388e3c !important;
  }
  .no-foros { text-align:center; color:#ccc; margin:30px 0; }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid forum-container">
    <?php echo $alert; ?>

    <?php if ($foroId > 0): ?>
      <!-- Detalle de un foro concreto -->
      <a href="<?php echo SERVERURL."foro/{$sesionId}/"; ?>"
         class="btn btn-info btn-sm btn-back">
        <i class="zmdi zmdi-arrow-left"></i> Volver a Foros
      </a>

      <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
        <!-- Eliminar / Editar -->
        <div class="text-right" style="margin-bottom:10px">
          <form method="POST" style="display:inline">
            <input type="hidden" name="delete_foro" value="1">
            <button type="submit" class="btn btn-danger btn-sm btn-delete"
                    onclick="return confirm('¿Eliminar este foro?');">
              <i class="zmdi zmdi-delete"></i> Eliminar
            </button>
          </form>
          <button id="btnEdit" class="btn btn-success btn-sm btn-edit">
            <i class="zmdi zmdi-edit"></i> Editar
          </button>
        </div>
        <!-- Formulario edición -->
        <div id="editForm" style="display:none" class="panel">
          <div class="panel-heading">Editar Foro</div>
          <div class="forum-item">
            <form method="POST" enctype="multipart/form-data">
              <input type="hidden" name="edit_foro" value="1">
              <input type="text" name="titulo" class="form-control"
                     value="<?php echo htmlspecialchars($foro['Titulo']); ?>" required>
              <textarea name="descripcion" class="form-control" rows="4"
                        required><?php echo htmlspecialchars($foro['Descripcion']); ?></textarea>
              <label>Fecha de cierre (opcional):</label>
              <input type="datetime-local" name="fechacierre" class="form-control"
                value="<?php
                  echo $foro['FechaCierre']
                       ? date('Y-m-d\TH:i', strtotime($foro['FechaCierre']))
                       : '';
                ?>">
              <label>Adjunto (reemplaza existente):</label>
              <input type="file" name="archivo" class="form-control">
              <button type="submit" class="btn btn-success btn-sm">
                <i class="zmdi zmdi-floppy"></i> Guardar cambios
              </button>
              <button type="button" class="btn btn-default btn-sm"
                      onclick="document.getElementById('editForm').style.display='none'">
                Cancelar
              </button>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <div class="panel">
        <div class="panel-heading"><?php echo htmlspecialchars($foro['Titulo']); ?></div>
        <div class="forum-item">
          <div class="forum-date">
            Creado: <?php echo htmlspecialchars($foro['FechaSubida']); ?>
            <?php if ($foro['FechaCierre']): ?>
              | Cierre: <?php echo htmlspecialchars($foro['FechaCierre']); ?>
            <?php endif; ?>
          </div>
          <p><?php echo nl2br(htmlspecialchars($foro['Descripcion'])); ?></p>
          <?php if (!empty($foro['Archivo'])): ?>
            <p>
              <strong>Adjunto:</strong>
              <a href="<?php echo SERVERURL.'attachments/foros/'.urlencode($foro['Archivo']); ?>"
                 target="_blank">
                <?php echo htmlspecialchars($foro['Archivo']); ?>
              </a>
            </p>
          <?php endif; ?>
        </div>
      </div>

      <?php foreach ($fc->list_comentarios($foroId) as $c): ?>
        <div class="panel comment-item">
          <div class="comment-date">
            <strong><?php echo htmlspecialchars($c['UsuarioCodigo']); ?></strong>
            &ndash; <?php echo htmlspecialchars($c['Fecha']); ?>
          </div>
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
      <?php endforeach; ?>

      <div class="panel comment-item comment-form">
        <form method="POST" enctype="multipart/form-data">
          <textarea name="comentario" class="form-control" rows="3"
                    placeholder="Escribe tu comentario…" required></textarea>
          <input type="file" name="adjunto" class="form-control">
          <input type="hidden" name="idc" value="1">
          <button type="submit" class="btn btn-info btn-sm btn-send">
            <i class="zmdi zmdi-mail-send"></i> Enviar
          </button>
        </form>
      </div>

      <script>
        document.getElementById('btnEdit')?.addEventListener('click', function(){
          document.getElementById('editForm').style.display = 'block';
        });
      </script>

    <?php else: ?>
      <!-- Listado de foros -->
      <a href="<?php echo SERVERURL."sesion/{$cursoId}/"; ?>"
         class="btn btn-secondary btn-sm btn-back">
        <i class="zmdi zmdi-arrow-left"></i> Volver a Sesiones
      </a>

      <?php if ($_SESSION['userType']==='Estudiante' && empty($foros)): ?>
        <p class="no-foros">No existe ningún foro por el momento.</p>
      <?php endif; ?>

      <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
        <div class="panel">
          <div class="panel-heading">Nuevo Foro</div>
          <div class="forum-item">
            <form method="POST" enctype="multipart/form-data">
              <input type="text"  name="titulo" class="form-control"
                     placeholder="Título del foro" required>
              <textarea name="descripcion" class="form-control" rows="3"
                        placeholder="Descripción" required></textarea>
              <label>Adjunto (opcional):</label>
              <input type="file" name="archivo" class="form-control">
              <label>Fecha de cierre (opcional):</label>
              <input type="datetime-local" name="fechacierre" class="form-control">
              <button type="submit" class="btn btn-success btn-sm btn-create">
                <i class="zmdi zmdi-plus"></i> Crear Foro
              </button>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <?php foreach ($foros as $f): ?>
        <a href="<?php echo SERVERURL."foro/{$sesionId}/{$f['id']}/"; ?>"
           class="panel forum-item">
          <div class="panel-heading"><?php echo htmlspecialchars($f['Titulo']); ?></div>
          <div class="forum-item">
            <div class="forum-date">
              Subido: <?php echo htmlspecialchars($f['FechaSubida']); ?>
              <?php if ($f['FechaCierre']) echo ' | Cierre: '.$f['FechaCierre']; ?>
            </div>
            <p><?php echo nl2br(htmlspecialchars(substr($f['Descripcion'],0,100))); ?>…</p>
          </div>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</section>
