<?php
// views/contents/anuncios-view.php

// 1) Control de acceso (cualquiera que esté logueado)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente','Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}

require_once __DIR__ . '/../../controllers/anuncioController.php';
$ac       = new anuncioController();
$userKey  = $_SESSION['userKey'];
$userType = $_SESSION['userType'];

// 2) Recuperar los 10 anuncios más recientes
$recent = $ac->list_recent_by_user_controller($userKey, $userType, 10);
?>

<style>
  html, body {
    margin: 0; padding: 0;
    background: #1e1f28; color: #fff;
    width: 100%; height: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
  }
  .dashboard-contentPage {
    margin-left: 170px;
    padding: 30px;
    min-height: 100vh;
    background: #1e1f28;
  }

  /* Encabezado */
  .page-header h1 {
    color: #00e5ff;
    font-size: 28px;
    text-shadow: 1px 1px 6px #000;
    margin-bottom: 0.5rem;
  }
  .page-header hr {
    /* línea bajo el título */
    width: 120px;                /* longitud fija */
    border: none;
    border-top: 2px solid #444;  /* grosor y color */
    margin: 0.5rem 0 1.5rem 0;    /* separaciones */
  }

  /* Panel centrado y con ancho máximo */
  .panel {
    background: #2c2d3f;
    border: 1px solid #3c3d4f;
    border-radius: 8px;
    max-width: 900px;     /* ancho máximo */
    margin: 0 auto 2rem;  /* centrado y margen inferior */
    box-shadow: 0 4px 15px rgba(0,0,0,0.5);
  }
  .panel-heading {
    background: #333;
    color: #ddd;
    padding: 12px 20px;
    font-weight: bold;
    border-radius: 8px 8px 0 0;
  }
  .panel-body {
    padding: 20px;
  }

  /* Lista de anuncios */
  .anuncios-list {
    list-style: none;
    margin: 0; padding: 0;
  }
  .anuncios-list li {
    padding: 15px 10px;
    border-bottom: 1px solid #444;
  }
  .anuncios-list li:last-child {
    border-bottom: none;
  }
  .anuncio-titulo {
    font-weight: bold;
    color: #29b6f6;
    font-size: 1.1rem;
  }
  .anuncio-curso {
    font-size: 0.9rem;
    color: #bbb;
    margin-left: 8px;
  }
  .anuncio-fecha {
    float: right;
    font-size: 0.8rem;
    color: #888;
  }
  .anuncio-contenido {
    clear: both;
    margin-top: 8px;
    line-height: 1.4;
  }

  .empty {
    text-align: center;
    color: #ccc;
    padding: 30px 0;
  }
</style>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <div class="page-header">
      <h1><i class="zmdi zmdi-notifications-active"></i> Anuncios recientes</h1>
      <hr>
    </div>
  </div>

  <div class="container-fluid">
    <div class="panel">
      <div class="panel-heading">
        Últimos <?php echo count($recent); ?> anuncios
      </div>
      <div class="panel-body">
        <?php if (empty($recent)): ?>
          <p class="empty">No hay anuncios recientes para mostrar.</p>
        <?php else: ?>
          <ul class="anuncios-list">
            <?php foreach($recent as $a): ?>
              <li>
                <span class="anuncio-titulo">
                  <?php echo htmlspecialchars($a['Titulo']); ?>
                </span>
                <span class="anuncio-curso">
                  (<?php echo htmlspecialchars($a['Curso']); ?>)
                </span>
                <span class="anuncio-fecha">
                  <?php echo date('d/m/Y H:i', strtotime($a['Fecha'])); ?>
                </span>
                <div class="anuncio-contenido">
                  <?php echo nl2br(htmlspecialchars($a['Contenido'])); ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
