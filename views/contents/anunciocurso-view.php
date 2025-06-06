<?php
// views/contents/anunciocurso-view.php

// 1) Control de acceso: Admin, Docente y Estudiante
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente','Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/anuncioController.php';
require_once __DIR__ . '/../../controllers/cursoController.php';

$ac      = new anuncioController();
$cc      = new cursoController();

// 2) Extraemos el ID de curso de la URL “/anunciocurso/{cursoId}/”
$parts   = explode('/', trim($_GET['views'],'/'));
$cursoId = intval($parts[1] ?? 0);

// 3) Obtenemos el nombre del curso
$curso = $cc->get_curso_by_id_controller($cursoId);
// Ahora get_curso_by_id_controller devuelve un array asociativo o null
if ($curso === null) {
    echo '<div class="alert alert-danger text-center">Curso no encontrado.</div>';
    return;
}

// 4) Procesar POST (solo Admin/Docente)
$alert = '';
if (in_array($_SESSION['userType'], ['Administrador','Docente'])
    && $_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['delete_id'])) {
        $alert = $ac->delete_anuncio_controller(intval($_POST['delete_id']));
    }
    elseif (isset($_POST['edit_id'])) {
        $alert = $ac->update_anuncio_controller(intval($_POST['edit_id']), $_POST);
    }
    else {
        $alert = $ac->add_anuncio_controller($cursoId, $_POST);
    }
    // PRG
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}

// 5) Listamos todos los anuncios para este curso
$anuncios = $ac->list_anuncios_by_curso_controller($cursoId);
?>
<style>
  .dashboard-contentPage { margin-left:170px; padding:10px; background:#1e1f28; min-height:100vh; color:#fff; }
  .page-header h1 { font-size:28px; color:#00e5ff; margin-bottom:10px; }
  .btn-back-cursos {
    background-color: #607d8b !important;
    border-color:     #455a64 !important;
    color:            #fff !important;
    margin-bottom: 15px;
  }
  .panel { background:#2c2d3f; border-radius:8px; border:1px solid #444; margin-bottom:1rem; }
  .panel-heading { background:#00bcd4; color:#fff; padding:12px 15px; font-weight:bold; }
  .panel-body { padding:20px; }
  .anuncio-table { width:100%; border-collapse: collapse; margin-top:1rem; }
  .anuncio-table th, .anuncio-table td { padding:.75rem; border-bottom:1px solid #444; color:#fff; }
  .anuncio-table thead th { background:#333; color:#ddd; text-align:left; }
  .btn-xs { padding:4px 8px; font-size:.85rem; }
  .edit-form { display:none; background:#333; padding:10px; border-radius:4px; }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <!-- Botón Volver a Mis Cursos (para Docente y Estudiante) -->
    <?php if (in_array($_SESSION['userType'], ['Docente','Estudiante'])): ?>
      <a href="<?php echo SERVERURL; ?>miscursos/" 
         class="btn btn-back-cursos btn-sm">
        <i class="zmdi zmdi-arrow-left"></i> Volver a Mis Cursos
      </a>
    <?php endif; ?>

    <div class="page-header">
      <h1>
        <i class="zmdi zmdi-notifications"></i>
        Anuncios – <?= htmlspecialchars($curso['Nombre']) ?>
      </h1>
      <?php if ($_SESSION['userType'] === 'Estudiante'): ?>
        <p>A continuación ves todos los anuncios publicados para este curso.</p>
      <?php endif; ?>
    </div>
    <?= $alert ?>
  </div>

  <div class="container-fluid">
    <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
    <!-- formulario para crear nuevo anuncio -->
    <div class="panel panel-info">
      <div class="panel-heading"><i class="zmdi zmdi-plus"></i> Nuevo anuncio</div>
      <div class="panel-body">
        <form method="POST" autocomplete="off">
          <div class="form-group">
            <label>Título *</label>
            <input type="text" name="titulo" class="form-control" required maxlength="255">
          </div>
          <div class="form-group">
            <label>Contenido *</label>
            <textarea name="contenido" class="form-control" rows="3" required></textarea>
          </div>
          <button type="submit" class="btn btn-success btn-raised btn-sm">
            <i class="zmdi zmdi-floppy"></i> Guardar anuncio
          </button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <!-- tabla de anuncios -->
    <div class="table-responsive">
      <table class="anuncio-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Título</th>
            <th>Contenido</th>
            <th>Fecha</th>
            <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
            <th>Acciones</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php if ($anuncios): foreach ($anuncios as $i => $a): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($a['Titulo']) ?></td>
            <td><?= nl2br(htmlspecialchars($a['Contenido'])) ?></td>
            <td><?= $a['Fecha'] ?></td>
            <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
            <td>
              <button class="btn btn-info btn-xs"
                      onclick="document.getElementById('edit-<?= $a['id'] ?>').style.display='block'">
                <i class="zmdi zmdi-edit"></i>
              </button>
              <form method="POST" style="display:inline">
                <input type="hidden" name="delete_id" value="<?= $a['id'] ?>">
                <button class="btn btn-danger btn-xs"
                        onclick="return confirm('¿Eliminar este anuncio?')">
                  <i class="zmdi zmdi-delete"></i>
                </button>
              </form>
            </td>
            <?php endif; ?>
          </tr>
          <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
          <tr id="edit-<?= $a['id'] ?>" class="edit-form">
            <td colspan="5">
              <form method="POST" autocomplete="off">
                <input type="hidden" name="edit_id" value="<?= $a['id'] ?>">
                <div class="form-group">
                  <label>Título *</label>
                  <input type="text" name="titulo" class="form-control"
                         value="<?= htmlspecialchars($a['Titulo']) ?>" required>
                </div>
                <div class="form-group">
                  <label>Contenido *</label>
                  <textarea name="contenido" class="form-control" rows="2" required><?= htmlspecialchars($a['Contenido']) ?></textarea>
                </div>
                <button type="submit" class="btn btn-success btn-raised btn-sm">
                  <i class="zmdi zmdi-refresh"></i> Actualizar
                </button>
                <button type="button" class="btn btn-default btn-xs"
                        onclick="this.closest('tr').style.display='none'">
                  Cancelar
                </button>
              </form>
            </td>
          </tr>
          <?php endif; ?>
        <?php endforeach; else: ?>
          <tr>
            <td colspan="<?= in_array($_SESSION['userType'], ['Administrador','Docente']) ? 5 : 4 ?>" class="text-center">
              No hay anuncios registrados para este curso.
            </td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
