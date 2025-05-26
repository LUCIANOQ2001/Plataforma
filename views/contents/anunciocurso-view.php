<?php
// Sólo Administradores y Docentes pueden ver esta página
if(!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente'])){
  echo (new loginController())->login_session_force_destroy_controller();
  exit;
}

require_once __DIR__ . '/../../controllers/anuncioController.php';
$ac      = new anuncioController();

$views   = $_GET['views'] ?? '';
$parts   = explode('/', trim($views,'/'));
$cursoId = intval($parts[1] ?? 0);

$alert = '';

// 1) Procesar POST: crear, editar o borrar
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['delete_id'])){
    $alert = $ac->delete_anuncio_controller(intval($_POST['delete_id']));
  }
  elseif(isset($_POST['edit_id'])){
    $alert = $ac->update_anuncio_controller(intval($_POST['edit_id']), $_POST);
  }
  else {
    $alert = $ac->add_anuncio_controller($cursoId, $_POST);
  }
  // PRG para evitar reenvío
  echo "<script>location.href=location.href;</script>";
  exit;
}

// 2) Cargar lista
$anuncios = $ac->list_anuncios_by_curso_controller($cursoId);
?>

<style>
  /* ===== Ajustes generales ===== */
.dashboard-contentPage {
  margin-top:    -20px;  /* ↑ sube */
  margin-right:    150px;
  margin-bottom:   0px;
  margin-left:   120px;   /* → mueve a la derecha */
  padding:        20px;  /* tu padding actual */
}



  /* ===== Contenedor de la tabla ===== */
  .table-responsive {
    max-height: 400px;         /* ← Cambia aquí la altura máxima de la tabla */
    overflow-y: auto;
    margin-top: 1rem;
  }

  /* ===== Estilo de la tabla ===== */
  .anuncio-table {
    width: 80%;                /* ← Ajusta aquí el ancho de la tabla (p.ej. 100%, 90%, 800px) */
    margin: 0 auto;            /* Centra la tabla horizontalmente */
    border-collapse: collapse;
    background: #2a2c3b;       /* Fondo oscuro para toda la tabla */
  }

  .anuncio-table th,
  .anuncio-table td {
    padding: .75rem;
    border-bottom: 1px solid #444;
    color: #fff;
  }

  /* Cabecera con fondo ligeramente diferente */
  .anuncio-table thead th {
    background: #333;
    color: #ddd;
  }

  .anuncio-table th {
    text-align: left;
  }

  /* Botones pequeños */
  .btn-sm {
    padding: 15px 15px;
    font-size: .95rem;
  }

  /* Formulario de edición inline */
  .edit-form {
    display: none;
    background: #333;
    padding: 10px;
    margin-bottom: 1rem;
    border-radius: 4px;
  }
  .edit-btn, .delete-btn {
    margin-right: 5px;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-notifications"></i> Anuncios – Curso <?php echo $cursoId; ?>
      </h1>
    </div>
    <?php echo $alert; ?>
  </div>

  <div class="container-fluid">
    <!-- Formulario para crear nuevo anuncio -->
    <div class="panel panel-info">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="zmdi zmdi-plus"></i> Nuevo anuncio</h3>
      </div>
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

    <!-- Tabla de anuncios -->
    <div class="table-responsive">
      <table class="anuncio-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Título</th>
            <th>Contenido</th>
            <th>Fecha</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php if($anuncios): foreach($anuncios as $i => $a): ?>
          <tr>
            <td><?php echo $i+1; ?></td>
            <td><?php echo htmlspecialchars($a['Titulo']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($a['Contenido'])); ?></td>
            <td><?php echo $a['Fecha']; ?></td>
            <td>
              <!-- Editar -->
              <button class="btn btn-info btn-xs edit-btn"
                      onclick="toggleEdit(<?php echo $a['id']; ?>)">
                <i class="zmdi zmdi-edit"></i>
              </button>
              <!-- Eliminar -->
              <form method="POST" style="display:inline">
                <input type="hidden" name="delete_id" value="<?php echo $a['id']; ?>">
                <button class="btn btn-danger btn-xs delete-btn"
                        onclick="return confirm('¿Eliminar este anuncio?')">
                  <i class="zmdi zmdi-delete"></i>
                </button>
              </form>
            </td>
          </tr>
          <!-- Formulario de edición inline -->
          <tr id="edit-form-<?php echo $a['id']; ?>" class="edit-form">
            <td colspan="5">
              <form method="POST" autocomplete="off">
                <input type="hidden" name="edit_id" value="<?php echo $a['id']; ?>">
                <div class="form-group">
                  <label>Título *</label>
                  <input type="text" name="titulo" class="form-control"
                         value="<?php echo htmlspecialchars($a['Titulo']); ?>" required>
                </div>
                <div class="form-group">
                  <label>Contenido *</label>
                  <textarea name="contenido" class="form-control" rows="2" required><?php
                    echo htmlspecialchars($a['Contenido']);
                  ?></textarea>
                </div>
                <button type="submit" class="btn btn-success btn-raised btn-sm">
                  <i class="zmdi zmdi-refresh"></i> Actualizar
                </button>
                <button type="button" class="btn btn-default btn-xs"
                        onclick="toggleEdit(<?php echo $a['id']; ?>)">
                  Cancelar
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="5">No hay anuncios registrados para este curso.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
  function toggleEdit(id){
    var row = document.getElementById('edit-form-'+id);
    row.style.display = row.style.display==='table-row' ? 'none' : 'table-row';
  }
</script>
