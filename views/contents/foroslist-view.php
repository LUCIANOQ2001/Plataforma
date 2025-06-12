<?php
// views/contents/foroslist-view.php

if (!in_array($_SESSION['userType'] ?? '', ['Docente','Administrador'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/sesionController.php';
$insS = new sesionController();

// extraer session ID de la URL: /foro/{sesionId}/
$parts    = explode('/', trim($_GET['views'], '/'));
$sesionId = intval($parts[1] ?? 0);

// manejar POST de creación de hilo
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alert = $insS->add_foro_controller($sesionId, $_POST);
    echo "<script>location.reload();</script>";
    exit;
}

// obtener lista de hilos
$foros = $insS->list_foros_by_sesion_controller($sesionId);
?>

<style>
  :root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(174,12,12,0.61);
    --text-light:       #FFFFFF;
    --hover-accent:     rgba(209,177,110,0.2);
  }
  /* Reset y fondo con logo watermark */
  html, body {
    margin: 0; padding: 0;
    background: var(--primary-bg);
    color: var(--text-light);
    width: 100%; height: 100%;
    overflow-x: hidden;
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
    margin-left: 170px;
    width: calc(100% - 270px);
    padding: 2rem 1.5rem;
    box-sizing: border-box;
  }
  /* Ocultar buscador y menú */
  .btn-search,
  i.zmdi.zmdi-search,
  .btn-options,
  .dropdown-toggle,
  .zmdi-more-vert,
  .btn-menu-dashboard {
    display: none !important;
  }
  .page-header h1 {
    font-size: 2rem;
    color: var(--primary-accent);
    text-shadow: 2px 2px 6px rgba(0,0,0,0.7);
    margin-bottom: 1rem;
    text-align: center;
  }
  /* Formulario inline */
  .new-foro-form {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    justify-content: center;
  }
  .new-foro-form .form-control {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    color: var(--text-light);
    padding: 0.5rem;
    border-radius: 0.3rem;
    flex: 1 1 200px;
    min-width: 180px;
  }
  .new-foro-form button {
    background: var(--primary-accent);
    color: var(--primary-bg);
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.3rem;
    cursor: pointer;
    transition: background 0.3s;
  }
  .new-foro-form button:hover {
    background: var(--hover-accent);
  }
  /* Lista de foros */
  .foros-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
  }
  .foro-item {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    border-radius: 0.5rem;
    padding: 1rem;
    transition: background 0.3s, transform 0.2s;
    text-decoration: none;
    color: var(--text-light);
    display: block;
  }
  .foro-item:hover {
    background: var(--hover-accent);
    transform: translateY(-3px);
  }
  .foro-item h5 {
    margin: 0 0 0.5rem;
    color: var(--primary-accent);
    font-size: 1.1rem;
  }
  .foro-item small {
    display: block;
    margin-bottom: 0.75rem;
    color: rgba(255,255,255,0.7);
    font-size: 0.85rem;
  }
  .foro-item p {
    margin: 0;
    font-size: 0.95rem;
    color: rgba(255,255,255,0.9);
  }
  .alert-custom {
    background: rgba(255,255,255,0.1);
    border: 1px solid var(--primary-accent);
    color: var(--text-light);
    padding: 1rem;
    border-radius: 0.5rem;
    text-align: center;
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="page-header">
    <h1><i class="zmdi zmdi-comment-text"></i> Foros – Sesión <?= $sesionId ?></h1>
    <?php if($alert): ?>
      <div class="alert-custom"><?= $alert ?></div>
    <?php endif; ?>
  </div>

  <form method="POST" class="new-foro-form">
    <input type="text" name="titulo" class="form-control" placeholder="Título del foro" required>
    <input type="text" name="fecha_cierre" class="form-control" placeholder="Fecha cierre (YYYY-MM-DD)">
    <button type="submit"><i class="zmdi zmdi-plus"></i> Crear Foro</button>
  </form>

  <?php if (empty($foros)): ?>
    <div class="alert-custom">No hay foros en esta sesión.</div>
  <?php else: ?>
    <div class="foros-list">
      <?php foreach ($foros as $f): ?>
        <a href="<?= SERVERURL."foro/{$sesionId}/{$f['id']}/" ?>"
           class="foro-item">
          <h5><?= htmlspecialchars($f['Titulo']) ?></h5>
          <small>
            Creado: <?= htmlspecialchars($f['FechaSubida']) ?>
            <?php if ($f['FechaCierre']): ?>
              – Cierre: <?= htmlspecialchars($f['FechaCierre']) ?>
            <?php endif; ?>
          </small>
          <p><?= htmlspecialchars($f['Descripcion']) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
