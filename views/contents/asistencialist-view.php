<?php if($_SESSION['userType'] === "Estudiante"): ?>

<!-- estilos inline: margen del sidebar, transparencia y bordes visibles -->
<style>
  .dashboard-contentPage {
    margin-left: 170px;            /* ajuste al ancho real de tu sidebar */
    padding: 20px;
    width: calc(100% - 270px);
    box-sizing: border-box;
    overflow: auto;
  }
  .dashboard-contentPage.full-box { width: auto; }

  /* contenedores transparentes */
  .dashboard-contentPage .container-fluid,
  .dashboard-contentPage .panel,
  .dashboard-contentPage .panel-heading,
  .dashboard-contentPage .panel-body,
  .dashboard-contentPage .table-responsive,
  .dashboard-contentPage .table-responsive .table {
    background: transparent !important;
    color:     #fff        !important;
  }

  /* encabezado verde intacto */
  .dashboard-contentPage .panel-success .panel-heading {
    background-color: #5cb85c !important;
    color:            #fff    !important;
  }

  /* bordes de celdas para fondo oscuro */
  .dashboard-contentPage .table-responsive .table th,
  .dashboard-contentPage .table-responsive .table td {
    border-color: #444 !important;
  }

  /* anular hover blanco en filas */
  .dashboard-contentPage .table-hover tbody tr:hover {
    background-color: transparent !important;
  }
</style>

<section class="dashboard-contentPage">
  <?php
    // incluimos el controlador
    require_once __DIR__ . '/../../controllers/asistenciaController.php';
    $insAsist   = new asistenciaController();
    $codigoEst   = $_SESSION['userKey'] ?? '';

    // obtenemos el historial de este estudiante
    // asume que has agregado este método al controller:
    // public function get_history_by_student_controller(string $codigo): array { ... }
    $records = $insAsist->get_history_by_student_controller($codigoEst);
  ?>

  <div class="container-fluid">
    <div class="page-header">
      <h1 class="text-titles">
        <i class="zmdi zmdi-time zmdi-hc-fw"></i> Mi Historial <small>Asistencias</small>
      </h1>
    </div>
    <p class="lead">
      Aquí puedes revisar todas tus asistencias registradas.
    </p>
  </div>

  <div class="container-fluid">
    <div class="panel panel-success">
      <div class="panel-heading">
        <h3 class="panel-title">
          <i class="zmdi zmdi-format-list-bulleted"></i> Registro de Asistencias
        </h3>
      </div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-hover text-center">
            <thead>
              <tr>
                <th>#</th>
                <th>Clase</th>
                <th>Fecha</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php if($records): $i = 1; foreach($records as $row): ?>
              <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($row['Titulo']); ?></td>
                <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                <td>
                  <span class="label label-<?php 
                    echo $row['estado']=='presente'   ? 'success'
                         : ($row['estado']=='ausente'  ? 'danger'
                         :                                'warning');
                  ?>">
                    <?php echo ucfirst($row['estado']); ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; else: ?>
              <tr>
                <td colspan="4">No se encontraron registros de asistencia.</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</section>

<?php else: 
    // Si no es estudiante, fuerza logout
    $logout2 = new loginController();
    echo $logout2->login_session_force_destroy_controller();
endif; ?>
