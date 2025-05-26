<?php
// views/contents/foro-view.php

// 1) Control de acceso: Admin, Docente y Estudiante
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente','Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/foroController.php';
$fc       = new foroController();

// 2) Extraemos IDs de la URL “/foro/{sesionId}/{foroId}/”
$parts    = explode('/', trim($_GET['views'], '/'));
$sesionId = intval($parts[1] ?? 0);
$foroId   = intval($parts[2] ?? 0);

// 3) Manejo de POST
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['idc'])) {
        // Comentario con posible archivo
        $alert = $fc->add_comentario($foroId, $_SESSION['userKey'], $_POST, $_FILES);
    } else {
        // Nuevo foro
        $alert = $fc->add_foro($sesionId, $_POST, $_FILES);
    }
    // PRG
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}
?>
<style>
.dashboard-contentPage { margin-left:70px; padding:20px; background:#1e1f28; color:#fff; }
.forum-container { max-width:700px; margin:0 auto; }
.forum-item, .comment-item { background:#2a2c3b; padding:15px; border-radius:8px; margin-bottom:1rem; }
.forum-header { font-size:1.3rem; font-weight:bold; margin-bottom:5px; }
.forum-date, .comment-date { font-size:0.85rem; color:#888; }
.comment-form textarea, .comment-form input[type="file"] {
  width:100%; margin-top:8px; background:#1f2235; border:1px solid #444; color:#fff;
}
.btn-back, .btn-create, .btn-send { margin-top:10px; }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid forum-container">
    <?php echo $alert; ?>

    <?php if ($foroId > 0): 
      // 4) Vista de un foro concreto
      $foro = $fc->get_foro($foroId);
      if (!$foro) {
        echo '<p>Foro no encontrado.</p>'; exit;
      }
    ?>
      <!-- Volver -->
      <a href="<?php echo SERVERURL."foro/{$sesionId}/"; ?>" class="btn btn-secondary btn-sm btn-back">
        <i class="zmdi zmdi-arrow-left"></i> Volver
      </a>

      <!-- Detalle Foro -->
      <div class="forum-item">
        <div class="forum-header"><?php echo htmlspecialchars($foro['Titulo']); ?></div>
        <div class="forum-date">
          Creado: <?php echo $foro['FechaSubida']; ?>
          <?php if ($foro['FechaCierre']): ?>
            | Cierre: <?php echo htmlspecialchars($foro['FechaCierre']); ?>
          <?php endif; ?>
        </div>
        <p><?php echo nl2br(htmlspecialchars($foro['Descripcion'])); ?></p>
        <?php if (!empty($foro['Archivo'])): ?>
          <p>
            <strong>Adjunto:</strong>
            <a href="<?php echo SERVERURL."uploads/foros/".urlencode($foro['Archivo']); ?>"
               target="_blank"><?php echo htmlspecialchars($foro['Archivo']); ?></a>
          </p>
        <?php endif; ?>
      </div>

      <!-- Comentarios existentes -->
      <?php foreach ($fc->list_comentarios($foroId) as $c): ?>
        <div class="comment-item">
          <span class="comment-author"><?=htmlspecialchars($c['UsuarioCodigo'])?></span>
          <span class="comment-date"><?=htmlspecialchars($c['Fecha'])?></span>
          <p><?=nl2br(htmlspecialchars($c['Comentario']))?></p>
          <?php if (!empty($c['Adjunto'])): ?>
            <p>
              <strong>Archivo:</strong>
              <a href="<?=SERVERURL."uploads/foros/".urlencode($c['Adjunto'])?>"
                download="<?=htmlspecialchars($c['Adjunto'])?>">
                <?=htmlspecialchars($c['Adjunto'])?>
              </a>
            </p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

      <!-- Formulario para añadir comentario (todos pueden) -->
      <div class="comment-item comment-form">
        <form method="POST" enctype="multipart/form-data">
          <textarea name="comentario" rows="3" required placeholder="Escribe tu comentario..."></textarea>
          <input type="file" name="adjunto">
          <input type="hidden" name="idc" value="1">
          <button type="submit" class="btn btn-info btn-sm btn-send">
            <i class="zmdi zmdi-mail-send"></i> Enviar comentario
          </button>
        </form>
      </div>

    <?php else: 
      // 5) Listado de foros y creación (solo Admin/Docente crean foros)
    ?>
      <!-- Nuevo Foro -->
      <?php if(in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
      <div class="forum-item">
        <form method="POST" enctype="multipart/form-data">
          <input type="text" name="titulo" class="form-control" placeholder="Título del foro" required>
          <textarea name="descripcion" class="form-control" placeholder="Descripción del foro" rows="3" required></textarea>
          <div style="margin-top:5px">
            <label>Adjunto (opcional):</label>
            <input type="file" name="archivo" class="form-control">
          </div>
          <label style="margin-top:5px;">Fecha de cierre (opcional):</label>
          <input type="datetime-local" name="fechacierre" class="form-control">
          <button type="submit" class="btn btn-success btn-sm btn-create" style="margin-top:8px">
            <i class="zmdi zmdi-plus"></i> Crear foro
          </button>
        </form>
      </div>
      <?php endif; ?>

      <!-- Listado de Foros -->
      <?php foreach($fc->list_foros_by_sesion($sesionId) as $f): ?>
        <a href="<?php echo SERVERURL."foro/{$sesionId}/{$f['id']}/"; ?>" class="forum-item">
          <div class="forum-header"><?php echo htmlspecialchars($f['Titulo']); ?></div>
          <div class="forum-date">
            Subido: <?php echo $f['FechaSubida']; ?>
            <?php if($f['FechaCierre']) echo ' | Cierre: '.$f['FechaCierre']; ?>
          </div>
          <p><?php echo nl2br(htmlspecialchars(substr($f['Descripcion'],0,100))); ?>…</p>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
