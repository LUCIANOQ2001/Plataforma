<?php
// sólo Docente/Admin
if (!in_array($_SESSION['userType'] ?? '', ['Docente','Administrador'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}
require_once __DIR__ . '/../../controllers/foroController.php';
$fc       = new foroController();
$views    = $_GET['views'] ?? '';
$parts    = explode('/', trim($views, '/'));
$sesionId = intval($parts[1] ?? 0);
$foroId   = intval($parts[2] ?? 0);
$alert    = '';

// Manejo de POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['idc'])) {
        $alert = $fc->add_comentario($foroId, $_SESSION['userKey'], $_POST);
    } else {
        // Ahora pasamos también $_FILES para el adjunto
        $alert = $fc->add_foro($sesionId, $_POST, $_FILES);
    }
    echo "<script>location.href = location.href;</script>";
    exit;
}
?>
<style>
  .dashboard-contentPage { 
    margin-left: 70px; padding: 20px; background: #1e1f28; min-height: 100vh;
  }
  .forum-container  { max-width:700px; margin:0 auto; color:#fff; }
  .forum-item, .comment-item {
    background:#2a2c3b; padding:15px; border-radius:8px; margin-bottom:1rem;
    box-shadow:0 2px 5px rgba(0,0,0,0.4);
  }
  .forum-header    { font-size:1.3rem; font-weight:bold; margin-bottom:5px; }
  .forum-date, .comment-date { font-size:0.85rem; color:#888; }
  .input-file-group { margin-top:5px; display:flex; }
  .input-file-group input[type="text"] { flex:1; }
  .input-file-group .btn-select      { margin-left:5px; }
  .btn-back, .btn-create, .btn-send { margin-top:10px; }
  .comment-form textarea { width:100%; margin-top:8px; background:#1f2235; border:1px solid #444; color:#fff; }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid forum-container">
    <?php echo $alert; ?>

    <?php if ($foroId > 0): 
      // vista del foro
      $foro = $fc->get_foro($foroId);
      if (!$foro) { echo '<p>Foro no encontrado.</p>'; exit; }
    ?>
      <a href="<?php echo SERVERURL."foro/{$sesionId}/"; ?>"
         class="btn btn-secondary btn-sm btn-back">
        <i class="zmdi zmdi-arrow-left"></i> Volver
      </a>

      <div class="forum-item">
        <div class="forum-header"><?php echo htmlspecialchars($foro['Titulo']); ?></div>
        <div class="forum-date">
          Creado: <?php echo $foro['FechaSubida'];
          if ($foro['FechaCierre']) echo ' | Cierre: ' . htmlspecialchars($foro['FechaCierre']);
          ?>
        </div>
        <p><?php echo nl2br(htmlspecialchars($foro['Descripcion'])); ?></p>
      
        <?php if (!empty($foro['Archivo'])): ?>
        <p>
          <strong>Archivo adjunto:</strong>
          <a href="<?php echo SERVERURL;?>uploads/foros/<?php echo urlencode($foro['Archivo']);?>"
            download="<?php echo htmlspecialchars($foro['Archivo']);?>">
            <?php echo htmlspecialchars($foro['Archivo']); ?>
          </a>
        </p>
      <?php endif; ?>


      </div>

      <?php foreach ($fc->list_comentarios($foroId) as $c): ?>
        <div class="comment-item">
          <span class="comment-author"><?php echo htmlspecialchars($c['UsuarioCodigo']); ?></span>
          <span class="comment-date">— <?php echo htmlspecialchars($c['Fecha']); ?></span>
          <p><?php echo nl2br(htmlspecialchars($c['Comentario'])); ?></p>
        </div>
      <?php endforeach; ?>

      <div class="comment-item comment-form">
        <form method="POST">
          <textarea name="comentario" rows="3" required placeholder="Escribe tu comentario..."></textarea>
          <input type="hidden" name="idc" value="1">
          <button type="submit" class="btn btn-info btn-sm btn-send">
            <i class="zmdi zmdi-mail-send"></i> Enviar comentario
          </button>
        </form>
      </div>

    <?php else: ?>
      <!-- formulario crear foro -->
      <div class="forum-item">
        <form method="POST" enctype="multipart/form-data">
          <input type="text" name="titulo" class="form-control"
                 placeholder="Título del foro" required>
          <textarea name="descripcion" class="form-control"
                    placeholder="Descripción del foro" rows="3" required></textarea>

          <!-- Selección de archivo -->
          <div class="input-file-group">
            <input type="text" id="selected-file-name" class="form-control"
                   placeholder="Ningún archivo seleccionado" readonly>
            <button type="button" class="btn btn-light btn-select"
                    onclick="document.getElementById('forum-file-input').click()">
              <i class="zmdi zmdi-attachment-alt"></i>
            </button>
          </div>
          <input type="file" name="archivo" id="forum-file-input" style="display:none"
                 onchange="document.getElementById('selected-file-name').value = this.files[0]?.name || ''">

          <label style="margin-top:10px;">Fecha de cierre (opcional):</label>
          <input type="datetime-local" name="fechacierre" class="form-control">

          <button type="submit" class="btn btn-success btn-sm btn-create">
            <i class="zmdi zmdi-plus"></i> Crear foro
          </button>
        </form>
      </div>

      <!-- listado de foros -->
      <?php foreach ($fc->list_foros_by_sesion($sesionId) as $f): ?>
        <a href="<?php echo SERVERURL."foro/{$sesionId}/{$f['id']}/"; ?>"
           class="forum-item">
          <div class="forum-header"><?php echo htmlspecialchars($f['Titulo']); ?></div>
          <div class="forum-date">
            Subido: <?php echo $f['FechaSubida'];
            if ($f['FechaCierre']) echo ' | Cierre: '.$f['FechaCierre'];
            ?>
          </div>
          <p><?php echo nl2br(htmlspecialchars(substr($f['Descripcion'],0,100))); ?>…</p>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
