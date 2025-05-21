<?php
// views/contents/consultaslist-view.php

// 1) Inicia sesión SIEMPRE al principio (antes de usar $_SESSION)
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
    // Redirige para limpiar POST
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

<!-- Estilos inline para transparencia y margen -->
<style>
.dashboard-contentPage {
    margin-left: 170px;         /* Ajusta al ancho real de tu sidebar */
    padding: 20px;
    width: calc(100% - 270px);
    box-sizing: border-box;
    overflow: auto;
}
.dashboard-contentPage.full-box { width:auto; }

.dashboard-contentPage .container-fluid,
.dashboard-contentPage .panel,
.dashboard-contentPage .panel-heading,
.dashboard-contentPage .panel-body,
.dashboard-contentPage .table-responsive,
.dashboard-contentPage .table-responsive .table {
    background: transparent !important;
    color: #fff           !important;
}
.dashboard-contentPage .panel-heading {
    background: rgb(16,196,121) !important;
    border-color: rgb(16,196,121) !important;
    color: #fff               !important;
}
.dashboard-contentPage .table-responsive .table th,
.dashboard-contentPage .table-responsive .table td {
    border-color: #444 !important;
}
/* Anular hover blanco */
.dashboard-contentPage .table-hover tbody tr:hover {
    background-color: transparent !important;
}
.consultas-panel {
    max-width: 950px;
    margin: 30px auto;
    padding: 0;
}
.consultas-heading {
    background: rgb(16,196,121);
    color: #fff;
    padding: 5px 15px;
}
.table-responsive {
    max-height: 400px;
    overflow-y: auto;
}
.pagination {
    display: inline-flex;
    padding: 10px 0;
    justify-content: center;
    width: 100%;
}
</style>

<section class="dashboard-contentPage full-box">
    <?php echo $alert; ?>
    <div class="dashboard-container">
        <div class="consultas-panel">
            <div class="consultas-heading">
                <h3 class="panel-title">Consultas de Estudiantes</h3>
            </div>
            <div class="table-responsive table-hover">
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
                        <?php if($consultas): $i=$start+1; foreach($consultas as $row): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo "{$row['Nombres']} {$row['Apellidos']}"; ?></td>
                            <td><?php echo htmlspecialchars($row['Asunto']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($row['Mensaje'])); ?></td>
                            <td><?php echo $row['Fecha']; ?></td>
                            <td>
                                <select class="form-control"
                                        onchange="updateStatus(<?php echo $row['id']; ?>,this.value)">
                                    <option value="pendiente" <?php if($row['Estado']=='pendiente') echo 'selected'; ?>>
                                        pendiente
                                    </option>
                                    <option value="respondido" <?php if($row['Estado']=='respondido') echo 'selected'; ?>>
                                        respondido
                                    </option>
                                </select>
                            </td>
                            <td>
                                <button class="btn btn-danger btn-xs"
                                        onclick="deleteConsulta(<?php echo $row['id']; ?>)">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="7">No hay consultas registradas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Paginación -->
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php for($p=1;$p<=$totalPages;$p++): ?>
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
function deleteConsulta(id){
    if(confirm('¿Eliminar esta consulta?')){
        let f=new FormData(); f.append('delete_id',id);
        fetch(location.href,{method:'POST',body:f})
          .then(()=>location.reload());
    }
}
function updateStatus(id,estado){
    let f=new FormData(); f.append('id',id); f.append('estado',estado);
    fetch(location.href,{method:'POST',body:f})
      .then(()=>location.reload());
}
</script>
