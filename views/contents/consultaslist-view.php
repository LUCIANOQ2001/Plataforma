<?php
// views/contents/consultaslist-view.php

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

// Procesar POST con PRG: eliminar o actualizar estado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare("DELETE FROM consultas WHERE id = :id");
        $_SESSION['alert'] = $stmt->execute([':id'=>(int)$_POST['delete_id']])
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
    }
    // Redirigir para evitar reenvío al refrescar
    echo "<script>window.location.href='" . $_SERVER['REQUEST_URI'] . "';</script>";
    exit;
}

// Mostrar alerta si existe
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']);
}

// Paginación
$perPage    = 10;
$page       = max(1, intval($_GET['page'] ?? 1));
$start      = ($page - 1) * $perPage;

// Conteo total de consultas
$total      = (int)$pdo->query("SELECT COUNT(*) FROM consultas")->fetchColumn();
$totalPages = ceil($total / $perPage);

// Consulta de datos: todas las consultas con nombre de estudiante
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

<!-- Estilos embebidos: ajusta ancho, alto y posición horizontal -->
<style>
    /* CONTENEDOR PRINCIPAL: ajusta `margin-left` para mover todo el contenido
       hacia la derecha o izquierda según el ancho de tu sidebar */
    .dashboard-contentPage {
        margin-left: 170px;             /* <-- Cambia 270px al ancho de tu sidebar */
        padding: 20px;                  /* Espacio interno */
        width: calc(100% - 270px);      /* Mantiene el ancho sin pisar el sidebar */
        box-sizing: border-box;
        overflow: auto;                 /* Scroll interno si es necesario */
    }

    /* PANEL DE CONSULTAS */
    .consultas-panel {
        max-width: 950px; /* Cambia este valor para ajustar ancho del panel */
        width: 100%;
        margin: 30px auto;
        padding: 0;
    }
    .consultas-heading {
        background: rgb(16, 196, 121); /* Color de encabezado */
        color: #fff;
        padding: 5px 15px;
    }
    .table-responsive {
        max-height: 400px; /* Ajusta altura de scroll */
        overflow-y: auto;
    }
    .pagination {
        display: inline-flex;
        padding: 10px 0;
        justify-content: center;
        width: 100%;
    }
</style>

<section class="full-box dashboard-contentPage">
    <?php echo $alert; ?>
    <div class="dashboard-container">
        <!-- Listado de Consultas para docente -->
        <div class="consultas-panel">
            <div class="consultas-heading">
                <h3 class="panel-title">Consultas de Estudiantes</h3>
            </div>
            <div class="table-responsive">
                <table class="table text-center">
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
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars("{$row['Nombres']} {$row['Apellidos']}"); ?></td>
                            <td><?php echo htmlspecialchars($row['Asunto']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($row['Mensaje'], ENT_QUOTES, 'UTF-8')); ?></td>
                            <td><?php echo $row['Fecha']; ?></td>
                            <td>
                                <select onchange="updateStatus(<?php echo $row['id']; ?>, this.value)">
                                    <option value="pendiente"<?php if($row['Estado']=='pendiente') echo ' selected'; ?>>pendiente</option>
                                    <option value="respondido"<?php if($row['Estado']=='respondido') echo ' selected'; ?>>respondido</option>
                                </select>
                            </td>
                            <td>
                                <button class="btn btn-danger btn-xs" onclick="deleteConsulta(<?php echo $row['id']; ?>)">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr class="no-data"><td colspan="7">No hay consultas registradas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Paginación -->
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php for($p=1; $p<=$totalPages; $p++): ?>
                    <li<?php if($p==$page) echo ' class="active"'; ?>>
                        <a href="?page=<?php echo $p; ?>"><?php echo $p; ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</section>

<script>
function deleteConsulta(id) {
    if (confirm('¿Eliminar esta consulta?')) {
        const f = new FormData();
        f.append('delete_id', id);
        fetch(location.href, { method: 'POST', body: f })
            .then(() => location.reload());
    }
}
function updateStatus(id, estado) {
    const f = new FormData();
    f.append('id', id);
    f.append('estado', estado);
    fetch(location.href, { method: 'POST', body: f })
        .then(() => location.reload());
}
</script>
