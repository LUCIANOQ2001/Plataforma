<?php 
// Sólo Administrador y Docente pueden ver esta página
if(in_array($_SESSION['userType'], ['Administrador','Docente'])): 
?>

<!-- Estilos inline para no tapar el sidebar, fondo transparente y hover neutro -->
<style>
  .dashboard-contentPage {
    margin-left: 170px;            /* ancho real de tu sidebar */
    padding: 20px;
    width: calc(100% - 270px);
    box-sizing: border-box;
    overflow: auto;
  }
  .dashboard-contentPage.full-box { width: auto; }

  .dashboard-contentPage .container-fluid,
  .dashboard-contentPage .panel,
  .dashboard-contentPage .panel-heading,
  .dashboard-contentPage .panel-body,
  .dashboard-contentPage .table-responsive,
  .dashboard-contentPage .table-responsive .table {
    background: transparent !important;
    color: #fff      !important;
  }

  .dashboard-contentPage .panel-success .panel-heading {
    background-color: #5cb85c !important;
    color: #fff                !important;
  }

  .dashboard-contentPage .table-responsive .table th,
  .dashboard-contentPage .table-responsive .table td {
    border-color: #444 !important;
  }

  /* Anular el hover blanco en filas */
  .dashboard-contentPage .table-hover tbody tr:hover {
    background-color: transparent !important;
  }
</style>

<section class="dashboard-contentPage">
  <?php
    // Controlador y conexión para clases
    require_once __DIR__ . '/../../controllers/asistenciaController.php';
    $insAsist = new asistenciaController();

    $pdo = new PDO(
      'mysql:host=127.0.0.1;dbname=plataformavirtual;charset=utf8',
      'root','',
      [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
    );
    $classes = $pdo
      ->query("SELECT id, Titulo FROM clase ORDER BY Fecha DESC")
      ->fetchAll(PDO::FETCH_ASSOC);

    // ID de la clase (GET ?class=)
    $classId = isset($_GET['class']) ? intval($_GET['class']) : 0;

    // Si no hay clase, muestro selector
    if($classId <= 0):
  ?>
    <div class="container-fluid text-center">
      <div class="page-header">
        <h1 class="text-titles">
          <i class="zmdi zmdi-check-circle zmdi-hc-fw"></i> Seleccione Clase
        </h1>
      </div>
      <p class="lead">
        Elija la clase para la cual desea registrar asistencia.
      </p>
      <form method="GET">
        <select name="class"
                class="form-control"
                style="display:inline-block; width:auto; color:#000;">
          <option value="">-- Seleccionar --</option>
          <?php foreach($classes as $c): ?>
            <option value="<?php echo $c['id']; ?>">
              <?php echo htmlspecialchars($c['Titulo']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-success btn-raised btn-sm">
          <i class="zmdi zmdi-arrow-right"></i> Continuar
        </button>
      </form>
    </div>

  <?php else: 
    // Procesar POST de asistencia
    $alert = '';
    if($_SERVER['REQUEST_METHOD']==='POST') {
      $alert = $insAsist->save_attendance_controller($classId, $_POST);
    }

    // Traer datos de clase y estudiantes
    $data         = $insAsist->get_students_by_class_controller($classId);
    $title        = $data['classTitle'];
    $studentsList = $data['rows'];
  ?>

    <!-- Título y descripción -->
    <div class="container-fluid">
      <div class="page-header">
        <h1 class="text-titles">
          <i class="zmdi zmdi-check-circle zmdi-hc-fw"></i>
          Asistencia <small>Clase: <?php echo htmlspecialchars($title); ?></small>
        </h1>
      </div>
      <p class="lead">
        Aquí puedes registrar la asistencia de cada estudiante para esta clase.
      </p>
    </div>

    <!-- Alerta -->
    <div class="container-fluid">
      <?php echo $alert; ?>
    </div>

    <!-- Formulario de asistencia -->
    <div class="container-fluid">
      <form action="?class=<?php echo $classId; ?>" method="POST">
        <div class="panel panel-success">
          <div class="panel-heading">
            <h3 class="panel-title">
              <i class="zmdi zmdi-format-list-bulleted"></i> Registro de Asistencia
            </h3>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-hover text-center">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Código</th>
                    <th>Estudiante</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $i = 1; foreach($studentsList as $row): ?>
                  <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($row['Codigo']); ?></td>
                    <td><?php echo htmlspecialchars("{$row['Nombres']} {$row['Apellidos']}"); ?></td>
                    <td>
                      <select name="asistencia[<?php echo $row['Codigo']; ?>]"
                              class="form-control">
                        <option value="presente"<?php   if($row['estado']=='presente')   echo ' selected'; ?>>Presente</option>
                        <option value="ausente"<?php     if($row['estado']=='ausente')     echo ' selected'; ?>>Ausente</option>
                        <option value="justificado"<?php if($row['estado']=='justificado') echo ' selected'; ?>>Justificado</option>
                      </select>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <p class="text-center">
              <button type="submit" class="btn btn-info btn-raised btn-sm">
                <i class="zmdi zmdi-floppy"></i> Guardar Asistencia
              </button>
            </p>
          </div>
        </div>
      </form>
    </div>

  <?php endif; ?>

</section>

<?php 
else:
  // Si no es Admin ni Docente, forzamos logout
  $logout2 = new loginController();
  echo $logout2->login_session_force_destroy_controller();
endif;
?>
