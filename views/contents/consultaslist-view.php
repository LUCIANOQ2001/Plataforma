<?php
// views/contents/consultaslist-view.php

// 1) Inicia sesión SIEMPRE al principio
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2) Sólo Administradores y Docentes pueden ver esta página
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])) {
    header("Location: " . SERVERURL . "login/");
    exit;
}

// 3) Conexión a la base de datos
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
        'root','',
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    die('Error de conexión: '.$e->getMessage());
}

// 4) Función para sanitizar entradas
function clean_string($str){
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES,'UTF-8');
}

// 5) Procesar POST (PRG)
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare("DELETE FROM consultas WHERE id = :id");
        $_SESSION['alert'] = $stmt->execute([':id'=>(int)$_POST['delete_id']])
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
    }
    header("Location: ".$_SERVER['REQUEST_URI']);
    exit;
}

// 6) Mostrar alerta si existe
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']);
}

// 7) Paginación
$perPage    = 10;
$page       = max(1, intval($_GET['page'] ?? 1));
$start      = ($page - 1) * $perPage;
$total      = (int)$pdo->query("SELECT COUNT(*) FROM consultas")->fetchColumn();
$totalPages = ceil($total / $perPage);

// 8) Consulta de datos
$stmt = $pdo->prepare("
    SELECT c.id, e.Nombres, e.Apellidos, c.Asunto, c.Mensaje, c.Fecha, c.Estado
    FROM consultas c
    JOIN estudiante e ON c.CodigoEstudiante = e.Codigo
    ORDER BY c.Fecha DESC
    LIMIT :start, :limit
");
$stmt->bindValue(':start',$start,PDO::PARAM_INT);
$stmt->bindValue(':limit',$perPage,PDO::PARAM_INT);
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
    margin:0; padding:0;
    background:var(--primary-bg);
    color:var(--text-light);
    width:100%; height:100%;
    overflow-x:hidden;
    box-sizing:border-box;
    font-family:'RobotoCondensed',sans-serif;
  }
  .dashboard-banner {
    position:fixed; top:0; left:270px;
    width:calc(100% - 270px); height:100%;
    background:url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png') center/60% no-repeat;
    opacity:0.05; pointer-events:none; z-index:0;
  }
  .dashboard-contentPage {
    position:relative; z-index:1;
    margin-left:200px;
    width:calc(100% - 270px);
    padding:20px;
    min-height:100vh;
  }
  /* Ocultar iconos de buscador y menú */
  .btn-options, .dropdown-toggle,
  .btn-search, i.zmdi-zmdi-search,
  .zmdi-more-vert, .btn-menu-dashboard {
    display:none !important;
  }
  .panel {
    background:var(--secondary-bg) !important;
    border:1px solid var(--primary-accent) !important;
    border-radius:1rem;
    box-shadow:0 4px 12px rgba(0,0,0,0.5);
    margin-bottom:2rem;
  }
  .panel-heading {
    background:var(--primary-accent) !important;
    color:var(--primary-bg) !important;
    padding:1rem; font-weight:bold; text-align:center;
    border-top-left-radius:1rem; border-top-right-radius:1rem;
  }
  .panel-body {
    padding:1.5rem;
  }
  .consultas-panel {
    max-width:950px; margin:0 auto;
  }
  .consultas-heading h3 {
    margin:0; font-size:1.25rem;
  }
  .consultas-heading {
    background:var(--primary-accent);
    color:var(--primary-bg);
    padding:.75rem 1.5rem;
    border-top-left-radius:1rem; border-top-right-radius:1rem;
  }
  .table-responsive {
    overflow-x:auto; max-height:400px;
  }
  .table {
    width:100%; border-collapse:collapse;
  }
  .table th, .table td {
    padding:.75rem; color:var(--text-light);
    border-bottom:1px solid rgba(255,255,255,0.2);
    text-align:center; vertical-align:middle;
  }
  .table thead th {
    background:var(--primary-accent);
    color:#2B2B2B;
  }
  .table-hover tbody tr:hover {
    background:rgba(255,255,255,0.05);
  }
  select.form-control {
    background:rgba(255,255,255,0.1) !important;
    color:var(--text-light) !important;
    border:1px solid #555 !important;
  }
  .pagination {
    display:inline-flex; justify-content:center;
    padding:1rem 0; width:100%;
  }
  .pagination li {
    margin:0 .25rem;
  }
  .pagination li a {
    display:block; padding:.5rem .75rem;
    background:var(--secondary-bg);
    color:var(--text-light);
    text-decoration:none;
    border:1px solid rgba(255,255,255,0.2);
    border-radius:.3rem;
    transition:background .2s;
  }
  .pagination li a:hover {
    background:var(--hover-accent);
  }
  .pagination li.active a {
    background:var(--primary-accent);
    border-color:var(--primary-accent);
    color:var(--primary-bg);
  }
  /* Quita el fondo blanco de las opciones nativas */
select.form-control {
  background: transparent !important;
  color: var(--text-light) !important;
}

/* Aplica tu paleta a las opciones desplegadas */
select.form-control option {
  background-color: var(--secondary-bg) !important;
  color: var(--text-light) !important;
}

/* Opcional: al pasar el ratón por encima */
select.form-control option:hover {
  background-color: var(--hover-accent) !important;
}
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <?= $alert ?>

  <div class="consultas-panel">
    <div class="consultas-heading">
      <h3>Consultas de Estudiantes</h3>
    </div>
    <div class="panel-body table-responsive table-hover">
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Estudiante</th>
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
            <td><?= htmlspecialchars($row['Asunto']) ?></td>
            <td style="text-align:left; white-space:normal;">
              <?= nl2br(htmlspecialchars($row['Mensaje'])) ?>
            </td>
            <td><?= $row['Fecha'] ?></td>
            <td>
              <select class="form-control"
                      onchange="updateStatus(<?= $row['id'] ?>, this.value)">
                <option value="pendiente"   <?= $row['Estado']==='pendiente'   ? 'selected':'' ?>>pendiente</option>
                <option value="respondido"  <?= $row['Estado']==='respondido'  ? 'selected':'' ?>>respondido</option>
              </select>
            </td>
            <td>
              <button class="btn btn-danger btn-xs btn-raised"
                      onclick="deleteConsulta(<?= $row['id'] ?>)">
                <i class="zmdi zmdi-delete"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="7">No hay consultas registradas.</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Page navigation">
      <ul class="pagination">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <li class="<?= $p === $page ? 'active' : '' ?>">
            <a href="?page=<?= $p ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
    <?php endif; ?>

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
function updateStatus(id, estado) {
  const data = new FormData();
  data.append('id', id);
  data.append('estado', estado);
  fetch(location.href, { method: 'POST', body: data })
    .then(() => location.reload());
}
</script>
