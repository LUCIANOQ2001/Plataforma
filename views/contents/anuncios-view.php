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
  /* === Paleta de colores actualizada === */
  :root {
    --primary-bg:       #2B2B2B;
    --primary-accent:   #D1B16E;
    --secondary-bg:     rgba(174,12,12,0.61);
    --text-light:       #FFFFFF;
    --hover-accent:     rgba(209,177,110,0.2);
  }

  /* Reseteo global */
  html, body {
    margin: 0; padding: 0;
    background: var(--primary-bg);
    color: var(--text-light);
    width: 100%; height: 100%;
    overflow-x: hidden;
    font-family: 'RobotoCondensed', sans-serif;
  }

  /* Banner con logo de fondo */
  .dashboard-banner {
    position: fixed;
    top: 0; left: 270px;  /* deja libre el sidebar */
    width: calc(100% - 270px); height: 100%;
    background-image: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png');
    background-repeat: no-repeat;
    background-position: center;
    background-size: 60%;
    opacity: 0.05;
    pointer-events: none;
    z-index: 0;
  }

  /* Contenido principal */
  .dashboard-contentPage {
    
    position: relative;
    z-index: 1;
    margin-left: 180px;
    width: calc(100% - 270px);
    padding: 0 30px auto;
    min-height: 100vh;
    box-sizing: border-box;
  }

  /* Ocultar iconos indeseados */
  .zmdi-more-vert,
  .zmdi-search,
  .btn-menu-dashboard {
    display: none !important;
  }

  /* Encabezado */
  .page-header h1 {
    color: var(--primary-accent);
    font-size: 2rem;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
    margin-bottom: .5rem;
  }
  .page-header hr {
    width: 120px;
    border: none;
    border-top: 2px solid rgba(255,255,255,0.3);
    margin: .5rem 0 1.5rem 0;
  }

  /* Botón Volver */
  .btn-back-home {
    background: var(--primary-accent) !important;
    color: var(--text-light) !important;
    border: none !important;
    border-radius: .3rem;
    padding: .5rem 1rem;
    font-size: .9rem;
    display: inline-block;
    margin-bottom: 1.5rem;
    transition: background .3s;
  }
  .btn-back-home:hover {
    background: var(--hover-accent) !important;
    text-decoration: none;
  }

  /* Panel de anuncios */
  .panel {
    background: var(--secondary-bg);
    border: 1px solid var(--primary-accent);
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    max-width: 900px;
    margin: 0 auto 2rem;
    overflow: hidden;
  }
  .panel-heading {
    background: var(--primary-accent) !important;
    color: var(--text-light) !important;
    padding: .75rem 1rem;
    font-weight: bold;
    font-size: 1.1rem;
    text-align: center;
  }
  .panel-body {
    padding: 1.5rem;
  }

  /* Lista de anuncios */
  .anuncios-list {
    list-style: none;
    margin: 0; padding: 0;
  }
  .anuncios-list li {
    padding: 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.2);
  }
  .anuncios-list li:last-child {
    border-bottom: none;
  }
  .anuncio-titulo {
    font-weight: bold;
    color: var(--primary-accent);
    font-size: 1.1rem;
  }
  .anuncio-curso {
    font-size: .9rem;
    color: rgba(255,255,255,0.7);
    margin-left: .5rem;
  }
  .anuncio-fecha {
    float: right;
    font-size: .8rem;
    color: rgba(255,255,255,0.5);
  }
  .anuncio-contenido {
    clear: both;
    margin-top: .75rem;
    line-height: 1.4;
    color: rgba(255,255,255,0.9);
  }

  /* Mensaje vacío */
  .empty {
    text-align: center;
    color: rgba(255,255,255,0.5);
    padding: 2rem 0;
  }

  /* Responsivo */
  @media (max-width: 768px) {
    .dashboard-contentPage {
      margin-left: 0;
      width: 100%;
      padding: 1rem;
    }
    .dashboard-banner {
      left: 0;
      width: 100%;
    }
    .panel { max-width: 100%; }
  }
</style>

<div class="dashboard-banner"></div>

<section class="dashboard-contentPage">
  <div class="container-fluid">
    <p class="text-center">
      <a href="<?= SERVERURL ?>home/" class="btn btn-back-home">
        <i class="zmdi zmdi-long-arrow-return"></i> Volver
      </a>
    </p>

    <div class="page-header">
      <h1><i class="zmdi zmdi-notifications-active"></i> Anuncios recientes</h1>
      <hr>
    </div>
  </div>

  <div class="container-fluid">
    <div class="panel">
      <div class="panel-heading">
        Últimos <?= count($recent) ?> anuncios
      </div>
      <div class="panel-body">
        <?php if (empty($recent)): ?>
          <p class="empty">No hay anuncios recientes para mostrar.</p>
        <?php else: ?>
          <ul class="anuncios-list">
            <?php foreach($recent as $a): ?>
              <li>
                <span class="anuncio-titulo">
                  <?= htmlspecialchars($a['Titulo']) ?>
                </span>
                <span class="anuncio-curso">
                  (<?= htmlspecialchars($a['Curso']) ?>)
                </span>
                <span class="anuncio-fecha">
                  <?= date('d/m/Y H:i', strtotime($a['Fecha'])) ?>
                </span>
                <div class="anuncio-contenido">
                  <?= nl2br(htmlspecialchars($a['Contenido'])) ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
