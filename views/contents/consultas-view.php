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
    "SELECT c.id, e.Nombres, e.Apellidos, c.Asunto, c.Mensaje, c.Mensaje, c.Fecha, c.Estado
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

<!-- Estilos embebidos: ajusta ancho y alto aquí -->
<style>
.consultas-panel {
    max-width: 900px; /* cambia este valor para ajustar ancho */
    width: 90%;       /* porcentaje relativo */
    margin: 20px auto;
    padding: 0;
}
.consultas-heading {
    background-color: #2c3e50;
    color: #fff;
    padding: 15px 20px;
}
.consultas-body {
    background-color: #fff;
    padding: 20px;
    text-align: center;
}
.table-responsive {
    max-height: 300px; /* ajusta altura de scroll */
    overflow-y: auto;
    margin: 0;
}
.pagination {
    display: inline-flex;
    padding: 10px 0;
    justify-content: center;
    width: 100%;
}
</style>

<section class="full-box dashboard-contentPage content-wrapper">
    <?php echo $alert; ?>
    <div class="dashboard-container">
        <!-- Nueva Consulta -->
        <div class="consultas-panel">
            <div class="consultas-heading">
                <h3 class="panel-title">Nueva Consulta</h3>
            </div>
            <div class="consultas-body">
                <form action="" method="POST" class="form-neon">
                    <div class="form-group label-floating">
                        <label class="control-label">Asunto</label>
                        <input name="asunto" type="text" class="form-control text-center" required>
                    </div>
                    <div class="form-group label-floating">
                        <label class="control-label">Descripción</label>
                        <textarea name="descripcion" class="form-control text-center" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-raised btn-success">
                        <i class="zmdi zmdi-mail-send"></i> Enviar
                    </button>
                </form>
            </div>
        </div>
        <!-- Historial de Consultas -->
        <div class="consultas-panel">
            <div class="consultas-heading">
                <h3 class="panel-title">Historial de Consultas</h3>
            </div>
            <div class="table-responsive">
                <table class="table text-center">
                    <thead>
                        <tr><th>#</th><th>Alumno</th><th>Asunto</th><th>Descripción</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($consultas): $i = $start + 1; foreach ($consultas as $row): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo "{$row['Nombres']} {$row['Apellidos']}"; ?></td>
                            <td><?php echo $row['Asunto']; ?></td>
                            <td><?php echo nl2br(htmlspecialchars($row['Mensaje'], ENT_QUOTES, 'UTF-8')); ?></td>
                            <td><?php echo $row['Fecha']; ?></td>
                            <td><span class="label label-<?php echo $row['Estado']=='pendiente'?'warning':'success'; ?>"><?php echo $row['Estado']; ?></span></td>
                            <td><button class="btn btn-danger btn-xs" onclick="deleteConsulta(<?php echo $row['id']; ?>)">Eliminar</button></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr class="no-data"><td colspan="7">No hay consultas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Paginación -->
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php for($p=1; $p<=$totalPages; $p++): ?>
                    <li<?php if($p==$page) echo ' class="active"'; ?>><a href="?page=<?php echo $p; ?>"><?php echo $p; ?></a></li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</section>

<script>
function deleteConsulta(id) {
    if (confirm('¿Eliminar esta consulta?')) {
        const data = new FormData(); data.append('delete_id', id);
        fetch(location.href, {method:'POST', body:data}).then(() => location.reload());
    }
}
function updateStatus(id, estado) {
    const data = new FormData(); data.append('id', id); data.append('estado', estado);
    fetch(location.href, {method:'POST', body:data}).then(() => location.reload());
}
</script>
