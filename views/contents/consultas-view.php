<?php
// views/contents/consultas-view.php

// Conexión a la base de datos (ajusta credenciales)
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// Inicia sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función para sanitizar entradas
function clean_string($str) {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

$alert = '';

// Procesar POST con PRG
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigoEst = $_SESSION['userKey'] ?? '';
    if (!$codigoEst) {
        $_SESSION['alert'] = '<div class="panel"><div class="panel-body text-center">Debes iniciar sesión.</div></div>';
    } else {
        if (isset($_POST['delete_id'])) {
            $stmt = $pdo->prepare("DELETE FROM consultas WHERE id = :id");
            $_SESSION['alert'] = $stmt->execute([':id' => (int)$_POST['delete_id']])
                ? '<div class="panel"><div class="panel-body text-center">Consulta eliminada.</div></div>'
                : '<div class="panel"><div class="panel-body text-center">Error al eliminar.</div></div>';
        } elseif (isset($_POST['id'], $_POST['estado'])) {
            $stmt = $pdo->prepare("UPDATE consultas SET Estado = :estado WHERE id = :id");
            $_SESSION['alert'] = $stmt->execute([
                ':estado' => clean_string($_POST['estado']),
                ':id'     => (int)$_POST['id']
            ])
                ? '<div class="panel"><div class="panel-body text-center">Estado actualizado.</div></div>'
                : '<div class="panel"><div class="panel-body text-center">Error al actualizar.</div></div>';
        } elseif (!empty($_POST['asunto']) && !empty($_POST['descripcion'])) {
            // Insertar nueva consulta
            $stmt = $pdo->prepare(
                "INSERT INTO consultas (CodigoEstudiante, Asunto, Mensaje, Estado) 
                 VALUES (:codigo, :asunto, :mensaje, 'pendiente')"
            );
            $_SESSION['alert'] = $stmt->execute([
                ':codigo'  => $codigoEst,
                ':asunto'  => clean_string($_POST['asunto']),
                ':mensaje' => clean_string($_POST['descripcion'])
            ])
                ? '<div class="panel"><div class="panel-body text-center">Consulta enviada.</div></div>'
                : '<div class="panel"><div class="panel-body text-center">Error al enviar.</div></div>';
        }
    }
    // Redirigir para evitar reenvío al refrescar
    echo "<script>window.location.href='" . $_SERVER['REQUEST_URI'] . "';</script>";
    exit;
}

// Mostrar alerta almacenada si existe
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']);
}

// Configuración de paginación
$perPage    = 10;
$page       = max(1, intval($_GET['page'] ?? 1));
$start      = ($page - 1) * $perPage;

// Conteo total de registros
$total      = (int)$pdo->query("SELECT COUNT(*) FROM consultas")->fetchColumn();
$totalPages = ceil($total / $perPage);

// Consulta de datos
$stmt = $pdo->prepare(
    "SELECT c.id, e.Nombres, e.Apellidos, c.Asunto, c.Mensaje, c.Fecha, c.Estado
     FROM consultas c
     JOIN estudiante e ON c.CodigoEstudiante = e.Codigo
     ORDER BY c.Fecha DESC
     LIMIT :start, :limit"
);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->execute();
$consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
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
  .dashboard-banner {
    position: fixed;
    top: 0; left: 270px;
    width: calc(100% - 270px);
    height: 100%;
    background: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
    opacity: 0.05;
    pointer-events: none;
    z-index: 0;
  }
  .dashboard-contentPage {
    position: relative; z-index: 1;
    margin-left: 200px;
    width: calc(100% - 270px);
    padding: auto;
    min-height: 100vh;
    box-sizing: border-box;
  }
  .btn-options,
  .dropdown-toggle,
  .btn-search,
  i.zmdi-zmdi-search,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }
  .page-header h1 {
    font-size: 2rem;
    color: var(--primary-accent);
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
    margin-bottom: .5rem;
    text-align: center;
  }
  .page-header p {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.7);
    text-align: center;
    margin-bottom: 2rem;
  }
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
    color: var(--primary-bg) !important;
    font-weight: bold;
    font-size: 1.2rem;
    text-align: center;
    padding: 1rem;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
  }
  .panel-body {
    padding: 1.5rem;
  }
  .form-neon .form-group {
    margin-bottom: 1.5rem;
  }
  .form-neon .control-label {
    color: rgba(255,255,255,0.7);
  }
  .form-neon .form-control {
    background: rgba(255,255,255,0.1) !important;
    border: 1px solid #555 !important;
    color: var(--text-light) !important;
  }
  .form-neon .form-control:focus {
    border-color: var(--primary-accent) !important;
    box-shadow: 0 0 5px rgba(209,177,110,0.5) !important;
  }
  .btn-raised {
    box-shadow: 0 4px 6px rgba(0,0,0,0.4);
    border-radius: .3rem;
    transition: background .3s;
  }
  .btn-raised.btn-success {
    background: var(--primary-accent);
    border: 1px solid var(--primary-accent);
    color: var(--primary-bg);
  }
  .btn-raised.btn-success:hover {
    background: var(--hover-accent);
  }
  .table-responsive {
    overflow-x: auto;
    margin-bottom: 2rem;
    max-height: 400px;
  }
  .table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
  }
  .table th, .table td {
    padding: 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    text-align: center;
    color: var(--text-light);
    white-space: nowrap;
  }
  .table thead th {
    background: var(--primary-accent);
    color: var(--text-light);
  }
  .label {
    padding: .4rem .8rem;
    border-radius: .3rem;
    font-size: .9rem;
  }
  .label-warning {
    background: var(--hover-accent);
    color: var(--primary-bg);
  }
  .label-success {
    background: var(--primary-accent);
    color: var(--primary-bg);
  }
  .pagination {
    display: inline-flex;
    padding: 1rem 0;
    justify-content: center;
    width: 100%;
  }
  .pagination li {
    margin: 0 .25rem;
  }
  .pagination li a {
    display: block;
    padding: .5rem 1rem;
    background: var(--secondary-bg);
    color: var(--text-light);
    text-decoration: none;
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: .3rem;
    transition: background .2s;
  }
  .pagination li a:hover {
    background: var(--hover-accent);
  }
  .pagination li.active a {
    background: var(--primary-accent);
    border-color: var(--primary-accent);
    color: var(--primary-bg);
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="page-header">
    <h1><i class="zmdi zmdi-email"></i> Centro de Consultas</h1>
    <p>Envía tus dudas o preguntas y revisa el historial de tus consultas.</p>
    <?= $alert ?>
  </div>

  <!-- Nueva Consulta -->
  <div class="panel">
    <div class="panel-heading"><i class="zmdi zmdi-mail-send"></i> Nueva Consulta</div>
    <div class="panel-body">
      <form action="" method="POST" class="form-neon">
        <div class="form-group label-floating">
          <label class="control-label">Asunto *</label>
          <input name="asunto" type="text" class="form-control" required>
        </div>
        <div class="form-group label-floating">
          <label class="control-label">Descripción *</label>
          <textarea name="descripcion" class="form-control" rows="4" required></textarea>
        </div>
        <p class="text-center">
          <button type="submit" class="btn btn-raised btn-success">
            <i class="zmdi zmdi-mail-send"></i> Enviar Consulta
          </button>
        </p>
      </form>
    </div>
  </div>

  <!-- Historial de Consultas -->
  <div class="panel">
    <div class="panel-heading"><i class="zmdi zmdi-folder"></i> Historial de Consultas</div>
    <div class="panel-body">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Alumno</th>
              <th>Asunto</th>
              <th>Descripción</th>
              <th>Fecha</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($consultas): $i = $start + 1; foreach ($consultas as $row): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= "{$row['Nombres']} {$row['Apellidos']}" ?></td>
              <td><?= htmlspecialchars($row['Asunto'], ENT_QUOTES, 'UTF-8') ?></td>
              <td style="max-width:200px; white-space: normal; text-align:left;">
                <?= nl2br(htmlspecialchars($row['Mensaje'], ENT_QUOTES, 'UTF-8')) ?>
              </td>
              <td><?= $row['Fecha'] ?></td>
              <td>
                <span class="label <?= $row['Estado']==='pendiente'?'label-warning':'label-success' ?>">
                  <?= ucfirst($row['Estado']) ?>
                </span>
              </td>
              <td>
                <button class="btn btn-danger btn-raised btn-xs"
                        onclick="deleteConsulta(<?= $row['id'] ?>)">
                  <i class="zmdi zmdi-delete"></i>
                </button>
              </td>
            </tr>
            <?php endforeach; else: ?>
            <tr>
              <td colspan="7">No hay consultas.</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <?php if ($totalPages > 1): ?>
      <nav aria-label="Page navigation">
        <ul class="pagination">
          <?php for($p = 1; $p <= $totalPages; $p++): ?>
            <li class="<?= $p === $page ? 'active' : '' ?>">
              <a href="?page=<?= $p ?>"><?= $p ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
  function deleteConsulta(id) {
    if (confirm('¿Eliminar esta consulta?')) {
      const data = new FormData();
      data.append('delete_id', id);
      fetch(location.href, { method: 'POST', body: data })
        .then(() => location.reload());
    }
  }
</script>
