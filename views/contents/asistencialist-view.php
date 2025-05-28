<?php if($_SESSION['userType'] === "Estudiante"): ?>

<style>
  html, body {
    margin: 0;
    padding: 0;
    background-color: #1e1f28;
    color: #fff;
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
  }

  .dashboard-contentPage {
    margin-left: 170px;
    padding: 30px 40px;
    background-color: #1e1f28;
    min-height: 100vh;
    box-sizing: border-box;
    max-width: calc(100vw - 170px);
  }

  .page-header h1 {
    font-size: 28px;
    color: #00e5ff;
    text-shadow: 1px 1px 6px #000;
    margin-bottom: 10px;
  }

  .lead {
    font-size: 1.1rem;
    color: #ccc;
    text-align: center;
    margin: 0 auto 30px auto;
    max-width: 760px;
  }

  .panel {
    background: #2c2d3f;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
    border: 1px solid #3c3d4f;
    max-width: 960px;
    margin: 0 auto;
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

  .table-responsive {
    border-radius: 8px;
    overflow: hidden;
  }

  .table {
    width: 100%;
    margin-bottom: 0;
  }

  .table th, .table td {
    text-align: center;
    vertical-align: middle;
    background-color: transparent !important;
    color: #fff;
    border: 1px solid #444;
    padding: 10px;
  }

  .table-hover tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.05);
  }

  .label-success {
    background-color: #4caf50;
  }

  .label-danger {
    background-color: #f44336;
  }

  .label-warning {
    background-color: #ff9800;
  }
</style>

<section class="dashboard-contentPage">
  <?php
    require_once __DIR__ . '/../../controllers/asistenciaController.php';
    $insAsist   = new asistenciaController();
    $codigoEst  = $_SESSION['userCode'] ?? $_SESSION['userKey'] ?? '';
    $records    = $insAsist->get_history_by_student_controller($codigoEst);
  ?>
      <!-- Volver -->
      <a href="<?php echo SERVERURL."asistencialist/{$sesionId}/"; ?>" class="btn btn-secondary btn-sm btn-back">
        <i class="zmdi zmdi-arrow-left"></i> Volver
      </a>

  <div class="container-fluid">
    <div class="page-header text-center">
      <h1 class="text-titles">
        <i class="zmdi zmdi-time zmdi-hc-fw"></i> Mi Historial <small>Asistencias</small>
      </h1>
    </div>
    <p class="lead">
      Aquí puedes revisar todas tus asistencias registradas, con su curso y sesión correspondientes.
    </p>
  </div>

  <div class="container-fluid">
    <div class="panel panel-success">
      <div class="panel-heading">
        <i class="zmdi zmdi-format-list-bulleted"></i> Registro de Asistencias
      </div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-hover text-center">
            <thead>
              <tr>
                <th>#</th>
                <th>Curso</th>
                <th>Sesión</th>
                <th>Fecha</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php if(!empty($records)): $i = 1; foreach($records as $row): ?>
              <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($row['Curso']); ?></td>
                <td><?php echo htmlspecialchars($row['Sesion']); ?></td>
                <td><?php echo htmlspecialchars($row['Fecha']); ?></td>
                <td>
                  <span class="label label-<?php 
                    echo $row['Estado']=='presente'   ? 'success' 
                         : ($row['Estado']=='ausente'     ? 'danger' : 'warning');
                  ?>">
                    <?php echo ucfirst($row['Estado']); ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; else: ?>
              <tr>
                <td colspan="5">No se encontraron registros de asistencia.</td>
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
  echo (new loginController())->login_session_force_destroy_controller();
endif; ?>
