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
  /* ==== Paleta de colores ==== */
  :root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(174, 12, 12, 0.61);
    --text-light:       #FFFFFF;
    --hover-accent:     rgba(209, 177, 110, 0.2);
  }

  /* Banner de logo en fondo */
  .dashboard-banner {
    position: fixed;
    top: 0; left: 170px;
    width: calc(100% - 170px);
    height: 100%;
    background: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
    opacity: 0.05;
    pointer-events: none;
    z-index: 0;
  }

  /* Ocultar buscador y menú de puntos */
  .btn-search,
  i.zmdi.zmdi-search,
  .btn-options,
  .dropdown-toggle,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }

  html, body {
    margin: 0; padding: 0;
    background: var(--primary-bg);
    color: var(--text-light);
    font-family: 'RobotoCondensed', sans-serif;
    overflow-x: hidden;
  }

  .dashboard-contentPage {
    position: relative;
    z-index: 1;
    margin-left: 150px;
    padding: 10px auto;
    min-height: 100vh;
    box-sizing: border-box;
    width: 90%;
  }

  .btn-back-cursos {
    background: var(--primary-accent) !important;
    color: var(--primary-bg) !important;
    border: none !important;
    margin-bottom: 15px;
    padding: .5rem 1rem;
    border-radius: .3rem;
    text-decoration: none;
    display: inline-block;
    transition: background .3s;
  }
  .btn-back-cursos:hover {
    background: var(--hover-accent) !important;
  }

  .page-header h1 {
    font-size: 2rem;
    color: var(--primary-accent);
    margin-bottom: .5rem;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
  }
  .page-header p {
    color: rgba(255,255,255,0.7);
    margin-top: 0;
    margin-bottom: 1.5rem;
  }

  .panel {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    border-radius: .75rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    overflow: hidden;
    margin-bottom: 1.5rem;
  }
  .panel-heading {
    background: var(--primary-accent) !important;
    color: var(--primary-bg) !important;
    padding: 1rem;
    font-size: 1.1rem;
    font-weight: bold;
    text-align: center;
  }
  .panel-body {
    padding: 1rem 1.5rem;
  }

  .anuncio-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
  }
  .anuncio-table th,
  .anuncio-table td {
    padding: .75rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    color: var(--text-light);
    vertical-align: top;
  }
  .anuncio-table thead th {
    background: var(--primary-bg);
    color: var(--primary-accent);
    text-align: left;
  }

  .btn-xs {
    padding: .3rem .6rem;
    font-size: .75rem;
    border-radius: .3rem;
    transition: background .3s;
  }
  .btn-info.btn-xs {
    background: var(--primary-accent) !important;
    color: var(--primary-bg) !important;
    border: none;
  }
  .btn-info.btn-xs:hover {
    background: var(--hover-accent) !important;
  }
  .btn-danger.btn-xs {
    background: #d32f2f !important;
    color: #fff !important;
    border: none;
  }
  .btn-danger.btn-xs:hover {
    background: #b71c1c !important;
  }

  .edit-form {
    display: none;
    background: var(--primary-bg);
    border: 1px solid var(--primary-accent);
    padding: 1rem;
    border-radius: .5rem;
  }

  .edit-form .form-group label {
    color: rgba(255,255,255,0.8);
  }

  /* Responsivo */
  @media (max-width: 768px) {
    .dashboard-contentPage {
      margin-left: 0;
      padding: 1rem;
    }
    .dashboard-banner {
      left: 0;
      width: 100%;
    }
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <!-- Botón Volver a Mis Cursos (para Docente y Estudiante) -->
    <?php if (in_array($_SESSION['userType'], ['Docente','Estudiante'])): ?>
      <a href="<?= SERVERURL ?>miscursos/" class="btn-back-cursos btn-sm">
        <i class="zmdi zmdi-arrow-left"></i> Volver a Mis Cursos
      </a>
    <?php endif; ?>

    <div class="page-header">
      <h1><i class="zmdi zmdi-notifications"></i> Anuncios – <?= htmlspecialchars($curso['Nombre']) ?></h1>
      <?php if ($_SESSION['userType'] === 'Estudiante'): ?>
        <p>A continuación ves todos los anuncios publicados para este curso.</p>
      <?php endif; ?>
      <?= $alert ?>
    </div>
  </div>

  <div class="container-fluid">
    <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
      <div class="panel">
        <div class="panel-heading"><i class="zmdi zmdi-plus"></i> Nuevo anuncio</div>
        <div class="panel-body">
          <form method="POST" autocomplete="off">
            <div class="form-group">
              <label style="color:rgba(255,255,255,0.8)">Título *</label>
              <input type="text" name="titulo" class="form-control" required maxlength="255"
                     style="background:rgba(255,255,255,0.05);border:1px solid #555;color:#fff;">
            </div>
            <div class="form-group">
              <label style="color:rgba(255,255,255,0.8)">Contenido *</label>
              <textarea name="contenido" class="form-control" rows="3" required
                        style="background:rgba(255,255,255,0.05);border:1px solid #555;color:#fff;"></textarea>
            </div>
            <button type="submit" class="btn btn-success btn-raised btn-sm"
                    style="background:#388e3c;border:none;color:#fff;">
              <i class="zmdi zmdi-floppy"></i> Guardar anuncio
            </button>
          </form>
        </div>
      </div>
    <?php endif; ?>

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
                  <button class="btn-info btn-xs"
                          onclick="document.getElementById('edit-<?= $a['id'] ?>').style.display='block'">
                    <i class="zmdi zmdi-edit"></i>
                  </button>
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="delete_id" value="<?= $a['id'] ?>">
                    <button class="btn-danger btn-xs" onclick="return confirm('¿Eliminar este anuncio?')">
                      <i class="zmdi zmdi-delete"></i>
                    </button>
                  </form>
                </td>
              <?php endif; ?>
            </tr>
            <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
              <tr id="edit-<?= $a['id'] ?>" class="edit-form">
                <td colspan="<?= in_array($_SESSION['userType'], ['Administrador','Docente']) ? 5 : 4 ?>">
                  <form method="POST" autocomplete="off">
                    <input type="hidden" name="edit_id" value="<?= $a['id'] ?>">
                    <div class="form-group">
                      <label style="color:rgba(255,255,255,0.8)">Título *</label>
                      <input type="text" name="titulo" class="form-control" required maxlength="255"
                             value="<?= htmlspecialchars($a['Titulo']) ?>"
                             style="background:rgba(255,255,255,0.05);border:1px solid #555;color:#fff;">
                    </div>
                    <div class="form-group">
                      <label style="color:rgba(255,255,255,0.8)">Contenido *</label>
                      <textarea name="contenido" class="form-control" rows="2" required
                                style="background:rgba(255,255,255,0.05);border:1px solid #555;color:#fff;"><?= htmlspecialchars($a['Contenido']) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-xs btn-raised"
                            style="background:#388e3c;border:none;color:#fff;">
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
              <td colspan="<?= in_array($_SESSION['userType'], ['Administrador','Docente']) ? 5 : 4 ?>" style="text-align:center;color:rgba(255,255,255,0.7);">
                No hay anuncios registrados para este curso.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
