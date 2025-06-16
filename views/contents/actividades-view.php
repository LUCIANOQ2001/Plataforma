<?php
// views/contents/actividades-view.php

// 1) Control de sesión y roles
if (session_status() === PHP_SESSION_NONE) session_start();
$userType  = $_SESSION['userType'] ?? '';
$estCodigo = $_SESSION['userKey'] ?? '';

// 2) Controladores
require_once __DIR__ . '/../../controllers/sesionController.php';
require_once __DIR__ . '/../../controllers/actividadesController.php';

$insSesion    = new sesionController();
$insActividad = new actividadesController();

// 3) Leer sesiónId de la URL
$parts    = explode('/', trim($_GET['views'], '/'));
$sesionId = intval($parts[1] ?? 0);

// 4) Obtener datos de la sesión
$stmt = $insSesion->get_sesion_by_id_controller($sesionId);
if ($stmt->rowCount() === 0) {
    echo '<div class="alert alert-danger">Sesión no encontrada.</div>';
    return;
}
$sesion = $stmt->fetch(PDO::FETCH_ASSOC);

// 5) Procesar POST
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Docente crea actividad con archivo opcional
    if (in_array($userType, ['Administrador','Docente']) && isset($_POST['crearActividad'])) {
        $alert = $insActividad->add_actividad_controller($sesionId, $_POST, $_FILES);
    }
    // Docente borra actividad
    if (in_array($userType, ['Administrador','Docente']) && isset($_POST['deleteActividad'])) {
        $alert = $insActividad->delete_actividad_controller((int)$_POST['deleteActividad']);
    }
    // Estudiante sube entrega
    if ($userType === 'Estudiante' && isset($_POST['subirEntrega'])) {
        $alert = $insActividad->add_entrega_controller((int)$_POST['actividadId'], $estCodigo);
    }
    // Docente califica entrega
    if (in_array($userType, ['Administrador','Docente']) && isset($_POST['gradeEntrega'])) {
        $alert = $insActividad->grade_entrega_controller((int)$_POST['entregaId'], $_POST);
    }
    // Refrescar para mostrar cambios
    echo "<script>location.replace(location.pathname);</script>";
    exit;
}

// 6) Listado de actividades y fecha actual
$actividades = $insActividad->list_actividades_by_sesion_controller($sesionId);
$hoy = date('Y-m-d');
?>

<style>
  /* ==== Paleta de colores ==== */
  :root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(174,12,12,0.61);
    --text-light:       #FFFFFF;
    --hover-accent:     rgba(209,177,110,0.2);
  }

  /* Reset global */
  html, body {
    margin: 0; padding: 0;
    background: var(--primary-bg);
    color: var(--text-light);
    width: 100%; height: 100%;
    overflow-x: hidden;
    font-family: 'RobotoCondensed', sans-serif;
  }

  /* Banner con logo de fondo */
  .dashboard-banner {
    position: fixed; top: 0; left: 270px;
    width: calc(100% - 270px); height: 100%;
    background: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
    opacity: 0.05;
    pointer-events: none;
    z-index: 0;
  }

  /* Contenido principal */
  .dashboard-contentPage {
    position: relative; z-index: 1;
    margin-left: 180px;
    width: calc(100% - 270px);
    padding: 0 30px auto;
    min-height: 100vh;
    box-sizing: border-box;
  }

  /* Ocultar íconos indeseados */
  .btn-options, .dropdown-toggle, .btn-search,
  i.zmdi-zmdi-search, .zmdi-more-vert, .btn-menu-dashboard {
    display: none !important;
  }

  /* Encabezado */
  .page-header h1 {
    font-size: 2rem;
    color: var(--primary-accent);
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
    margin-bottom: 0.5rem;
    text-align: center;
  }
  .lead {
    text-align: center;
    font-size: 1.1rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: 2rem;
    max-width: 800px;
    margin: 0 auto;
  }

  /* Botón Volver */
  .btn-back-home {
    background: var(--primary-accent) !important;
    color: var(--text-light) !important;
    border: none !important;
    border-radius: .3rem;
    padding: .5rem 1rem;
    font-size: .9rem;
    display: inline-block;
    margin-bottom: 1.5rem;
    transition: background .3s;
  }
  .btn-back-home:hover {
    background: var(--hover-accent) !important;
    text-decoration: none;
  }

  /* Panel principal */
  .panel {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    margin-bottom: 2rem;
    overflow: hidden;
  }
  .panel-heading {
    background: var(--primary-accent) !important;
    color: #2B2B2B;
    padding: .75rem 1rem;
    font-weight: bold;
    text-align: center;
  }
  .panel-body {
    padding: 1.5rem;
  }

  /* Form-control */
  .form-control, textarea {
    background: rgba(207, 7, 7, 0.05) !important;
    border: 1px solid #555 !important;
    color: var(--text-light) !important;
  }
  label {
    color: rgba(255,255,255,0.7) !important;
  }

  /* Botones */
  .btn-info, .btn-success, .btn-danger {
    font-weight: bold;
    border-radius: .3rem;
  }
  .btn-info {
    background: var(--primary-accent) !important;
    border: none !important;
    color: var(--text-light) !important;
  }
  .btn-success {
    background: var(--hover-accent) !important;
    border: none !important;
    color: var(--text-light) !important;
  }
  .btn-danger {
    background: #b71c1c !important;
    border: none !important;
    color: var(--text-light) !important;
  }

  /* Grid de sesiones/actividades */
  /* Grid de actividades: aquí aumentamos el espacio entre filas (row-gap)
     y entre columnas (column-gap) */
    .course-sessions {
    display: flex;
    flex-wrap: nowrap;           /* no apilar en varias filas */
    gap: 2.5rem;                 /* espacio entre tarjetas */
    overflow-x: auto;            /* permite scroll horizontal */
    padding-bottom: 1rem;        /* opcional: espacio bajo las cards */
    margin-top: 2rem;
    }

    /* opcional: para que el scroll sea más “suave” 
    .course-sessions::-webkit-scrollbar {
    height: 8px;
    }
    .course-sessions::-webkit-scrollbar-thumb {
    background: rgba(209,177,110,0.7);
    border-radius: 4px;
    }*/

  .session-card:hover {
    transform: translateY(-5px);
  }
  .session-card:hover {
    transform: translateY(-5px);
  }
  .session-card .header {
    background: var(--primary-accent);
    color: #000;
    padding: 1rem;
    text-align: center;
    font-weight: bold;
  }
  .session-card .header small {
    display: block;
    font-size: .85rem;
    margin-top: .25rem;
    color: rgba(0,0,0,0.7);
  }
  .session-card .body {
    padding: 1rem;
  }
  .delete-form {
    position: absolute; top: 8px; right: 8px;
  }
  .delete-btn {
    background: transparent;
    border: none;
    color: var(--text-light);
    font-size: 1.2rem;
    cursor: pointer;
  }
  .delete-btn:hover {
    color: var(--hover-accent);
  }

  @media (max-width: 768px) {
    .dashboard-contentPage {
      margin-left: 0; width: 100%; padding: 1rem;
    }
    .dashboard-banner {
      left: 0; width: 100%;
    }
    .course-sessions {
      grid-template-columns: 1fr;
    }
  }
</style>

<section class="dashboard-contentPage">
  <div class="dashboard-banner"></div>

  <a href="<?= SERVERURL ?>sesion/<?= $sesion['CursoId'] ?>/" class="btn-back-home btn-sm">
    <i class="zmdi zmdi-arrow-left"></i> Volver a Sesiones
  </a>

  <div class="page-header">
    <h1><i class="zmdi zmdi-collection-item"></i> Actividades de: <?= htmlspecialchars($sesion['Titulo']) ?></h1>
    <?= $alert ?>
  </div>

  <!-- Nueva Actividad (Docente/Administrador) -->
  <?php if (in_array($userType, ['Administrador','Docente'])): ?>
    <button class="btn btn-info btn-sm" onclick="document.getElementById('newActForm').style.display='block'">
      <i class="zmdi zmdi-plus"></i> Nueva Actividad
    </button>
    <div id="newActForm" style="display:none; margin-top:1rem;">
      <div class="panel">
        <div class="panel-heading">Crear Actividad</div>
        <div class="panel-body">
          <form method="POST" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="crearActividad" value="1">
            <div class="row">
              <div class="col-sm-5">
                <div class="form-group label-floating">
                  <label class="control-label">Título *</label>
                  <input type="text" name="titulo" class="form-control" required>
                </div>
              </div>
              <div class="col-sm-7">
                <div class="form-group label-floating">
                  <label class="control-label">Archivo (opcional)</label>
                  <input type="file" name="archivo" class="form-control">
                </div>
              </div>
            </div>
            <div class="row" style="margin-top:.5rem;">
              <div class="col-sm-4">
                <div class="form-group">
                  <label>Inicio *</label>
                  <input type="date" name="fecha_inicio" class="form-control" required>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="form-group">
                  <label>Cierre *</label>
                  <input type="date" name="fecha_cierre" class="form-control" required>
                </div>
              </div>
            </div>
            <p class="text-center" style="margin-top:1rem;">
              <button type="submit" class="btn btn-success">Guardar</button>
              <button type="button" class="btn-back-home" onclick="document.getElementById('newActForm').style.display='none'">
                Cancelar
              </button>
            </p>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Listado de Actividades -->
  <div class="course-sessions">
    <?php if (empty($actividades)): ?>
      <p>No hay actividades aún.</p>
    <?php else: ?>
      <?php foreach ($actividades as $act): ?>
        <div class="session-card">
          <?php if (in_array($userType, ['Administrador','Docente'])): ?>
            <form method="POST" class="delete-form" onsubmit="return confirm('¿Eliminar esta actividad?');">
              <input type="hidden" name="deleteActividad" value="<?= $act['id'] ?>">
              <button type="submit" class="delete-btn" title="Eliminar">
                <i class="zmdi zmdi-delete"></i>
              </button>
            </form>
          <?php endif; ?>

          <div class="header">
            <?= htmlspecialchars($act['Titulo']) ?>
            <?php if (!empty($act['Archivo'])): ?>
              <br><small>
                <a href="<?= SERVERURL ?>attachments/actividades/<?= htmlspecialchars($act['Archivo']) ?>" download>
                  <?= htmlspecialchars($act['Archivo']) ?>
                </a>
              </small>
            <?php endif; ?>
            <br><small>
              <?= date("d/m/Y", strtotime($act['FechaInicio'])) ?> – <?= date("d/m/Y", strtotime($act['FechaCierre'])) ?>
            </small>
          </div>

          <div class="body">
            <?php if ($act['Descripcion']): ?>
              <p><?= nl2br(htmlspecialchars($act['Descripcion'])) ?></p>
            <?php endif; ?>

            <?php if (in_array($userType, ['Administrador','Docente'])): ?>
              <!-- Listado de Entregas -->
              <?php $entregas = $insActividad->list_entregas_by_actividad_controller($act['id']); ?>
              <h5>Entregas:</h5>
              <?php if (empty($entregas)): ?>
                <p>No hay entregas aún.</p>
              <?php else: ?>
                <table class="table">
                  <thead>
                    <tr>
                      <th>Alumno</th>
                      <th>Archivo</th>
                      <th>Nota</th>
                      <th>Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($entregas as $e): ?>
                      <tr>
                        <td><?= htmlspecialchars($e['Alumno']) ?></td>
                        <td>
                          <a href="<?= SERVERURL ?>attachments/trabajos/<?= htmlspecialchars($e['Archivo']) ?>" download>
                            <?= htmlspecialchars($e['Archivo']) ?>
                          </a>
                        </td>
                        <td><?= $e['Nota'] ?? '—' ?></td>
                        <td>
                          <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="gradeEntrega" value="1">
                            <input type="hidden" name="entregaId" value="<?= $e['id'] ?>">
                            <input
                              type="number"
                              name="nota"
                              class="form-control"
                              step="0.1"
                              min="0" max="20"
                              value="<?= $e['Nota'] ?? '' ?>"
                              required
                              style="width:4rem; display:inline-block;"
                            >
                            <button type="submit" class="btn btn-info btn-sm">OK</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>

            <?php else: ?>
              <!-- Vista Estudiante: subir o ver entrega -->
              <?php if ($hoy >= $act['FechaInicio'] && $hoy <= $act['FechaCierre']): 
                $myEntrega = null;
                foreach ($insActividad->list_entregas_by_actividad_controller($act['id']) as $e) {
                  if ($e['EstudianteCodigo'] === $estCodigo) {
                    $myEntrega = $e;
                    break;
                  }
                }
              ?>
                <?php if (!$myEntrega): ?>
                  <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="subirEntrega" value="1">
                    <input type="hidden" name="actividadId" value="<?= $act['id'] ?>">
                    <div class="form-group">
                      <label>Tu entrega (.pdf, .docx, .zip):</label>
                      <input type="file" name="entrega" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">
                      <i class="zmdi zmdi-upload"></i> Subir entrega
                    </button>
                  </form>
                <?php else: ?>
                  <p>Tu entrega:
                    <a href="<?= SERVERURL ?>attachments/trabajos/<?= htmlspecialchars($myEntrega['Archivo']) ?>" download>
                      <?= htmlspecialchars($myEntrega['Archivo']) ?>
                    </a>
                  </p>
                  <p>Nota: <?= $myEntrega['Nota'] ?? 'En espera' ?></p>
                <?php endif; ?>
              <?php else: ?>
                <p>Periodo de entrega cerrado.</p>
              <?php endif; ?>
            <?php endif; ?>

          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
