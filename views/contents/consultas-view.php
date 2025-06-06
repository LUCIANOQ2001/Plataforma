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

// Procesar POST con PRG (Post/Redirect/Get)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigoEst = $_SESSION['userKey'] ?? '';
    if (!$codigoEst) {
        $_SESSION['alert'] = '<div class="alert alert-danger text-center">Debes iniciar sesión.</div>';
    } else {
        if (isset($_POST['delete_id'])) {
            $stmt = $pdo->prepare("DELETE FROM consultas WHERE id = :id");
            $_SESSION['alert'] = $stmt->execute([':id' => (int)$_POST['delete_id']])
                ? '<div class="alert alert-success text-center">Consulta eliminada.</div>'
                : '<div class="alert alert-danger text-center">Error al eliminar.</div>';
        } elseif (isset($_POST['id'], $_POST['estado'])) {
            $stmt = $pdo->prepare("UPDATE consultas SET Estado = :estado WHERE id = :id");
            $_SESSION['alert'] = $stmt->execute([
                ':estado' => clean_string($_POST['estado']),
                ':id'     => (int)$_POST['id']
            ])
                ? '<div class="alert alert-success text-center">Estado actualizado.</div>'
                : '<div class="alert alert-danger text-center">Error al actualizar.</div>';
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
                ? '<div class="alert alert-success text-center">Consulta enviada.</div>'
                : '<div class="alert alert-danger text-center">Error al enviar.</div>';
        }
    }
    // Redirigir para evitar reenvío al refrescar (JS)
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

// Consulta de datos con JOIN a estudiante (incluye Mensaje)
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
      /* Si la lupa tiene la clase .btn-search o un <i class="zmdi zmdi-search"> */
  .btn-search,
  i.zmdi.zmdi-search {
    display: none !important;
  }
  html, body {
    margin: 0;
    padding: 0;
    background-color: #1e1f28;
    color: #fff;
    width: 100%;
    height: 100%;
    box-sizing: border-box;
  }

  .dashboard-contentPage {
    margin-left: 150px;
    padding: 0 30px;
    background-color: #1e1f28;
    min-height: 100vh;
    box-sizing: border-box;

    /* Para restringir ancho y centrar, si lo deseas */
    max-width: 1350px;
    margin-right: auto;
 
  }

  .page-header {
    text-align: center;
    margin-bottom: 30px;
  }

  .page-header h1 {
    font-size: 28px;
    color: #00e5ff;
    text-shadow: 1px 1px 6px #000;
    margin-bottom: 10px;
  }

  .page-header p {
    font-size: 1.1rem;
    color: #ccc;
    margin-bottom: 20px;
  }

  .panel {
    background: #2c2d3f;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
    border: 1px solid #3c3d4f;
    color: #fff;
    margin-bottom: 30px;
  }

  .panel-heading {
    background: #43a047 !important;
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
  }

  .form-neon .form-group {
    margin-bottom: 20px;
  }

  .form-neon .control-label {
    color: #ccc;
  }

  .form-neon .form-control {
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid #555;
    color: #fff;
  }

  .form-neon .form-control:focus {
    border-color: #00e5ff;
    box-shadow: 0 0 5px rgba(0, 229, 255, 0.5);
    outline: none;
  }

  .btn-raised {
    box-shadow: 0 4px 6px rgba(0,0,0,0.4);
    border-radius: 4px;
    transition: background 0.2s;
  }

  .btn-raised.btn-success {
    background-color: #43a047;
    border-color: #388e3c;
    color: #fff;
  }

  .btn-raised.btn-success:hover {
    background-color: #388e3c;
  }

  .table-responsive {
    overflow-x: auto;
    margin-bottom: 20px;
    max-height: 400px;
  }

  .table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }

  .table th,
  .table td {
    padding: 12px;
    border-bottom: 1px solid #444;
    text-align: center;
    color: #fff;
    white-space: nowrap;
  }

  .table thead th {
    background: #333;
    color: #ddd;
  }

  .label {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9rem;
  }

  .label-warning {
    background-color: #f0ad4e;
    color: #000;
  }

  .label-success {
    background-color: #5cb85c;
    color: #fff;
  }

  .pagination {
    display: inline-flex;
    padding: 10px 0;
    justify-content: center;
    width: 100%;
  }

  .pagination li {
    margin: 0 4px;
  }

  .pagination li a {
    display: block;
    padding: 6px 12px;
    background-color: #2a2c3b;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
    border: 1px solid #444;
    transition: background 0.2s;
  }

  .pagination li a:hover {
    background-color: #333;
  }

  .pagination li.active a {
    background-color: #00e5ff;
    color: #000;
    border-color: #00aacc;
  }
</style>

<section class="dashboard-contentPage">
  <div class="page-header">
    <h1>
      <i class="zmdi zmdi-email"></i> Centro de Consultas
    </h1>
    <p>
      Envía tus dudas o preguntas y revisa el historial de tus consultas.
    </p>
    <?php echo $alert; ?>
  </div>

  <!-- Formulario de Nueva Consulta -->
  <div class="panel">
    <div class="panel-heading">
      <i class="zmdi zmdi-mail-send"></i> Nueva Consulta
    </div>
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
    <div class="panel-heading">
      <i class="zmdi zmdi-folder"></i> Historial de Consultas
    </div>
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
              <td><?php echo $i++; ?></td>
              <td><?php echo "{$row['Nombres']} {$row['Apellidos']}"; ?></td>
              <td><?php echo htmlspecialchars($row['Asunto'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td style="max-width:200px; white-space: normal; text-align:left;">
                <?php echo nl2br(htmlspecialchars($row['Mensaje'], ENT_QUOTES, 'UTF-8')); ?>
              </td>
              <td><?php echo $row['Fecha']; ?></td>
              <td>
                <span class="label label-<?php echo ($row['Estado'] === 'pendiente') ? 'warning' : 'success'; ?>">
                  <?php echo $row['Estado']; ?>
                </span>
              </td>
              <td>
                <button class="btn btn-danger btn-xs" onclick="deleteConsulta(<?php echo $row['id']; ?>)">
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
            <li class="<?php echo ($p === $page) ? 'active' : ''; ?>">
              <a href="?page=<?php echo $p; ?>"><?php echo $p; ?></a>
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

  function updateStatus(id, estado) {
    const data = new FormData();
    data.append('id', id);
    data.append('estado', estado);
    fetch(location.href, { method: 'POST', body: data })
      .then(() => location.reload());
  }
</script>
