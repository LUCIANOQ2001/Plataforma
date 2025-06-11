<?php
require_once "./controllers/cursoController.php";
require_once "./controllers/materialController.php";

$codigoEstudiante = $_SESSION['userKey'] ?? '';

$insCurso     = new cursoController();
$insMaterial  = new materialController();

$totalCursosEstudiante    = $insCurso->count_cursos_by_estudiante($codigoEstudiante);
$materialDisponible       = $insMaterial->count_material_by_estudiante($codigoEstudiante);
?>
<style>
  /* == Paleta de colores basada en tu actualización == */
  :root {
    --primary-bg:       #2B2B2B;     /* antes sidebar-red */
    --primary-accent:   #D1B16E;     /* antes sidebar-gold */
    --secondary-bg:rgba(174, 12, 12, 0.61);   /* antes sidebar-black */
    --text-light:       #FFFFFF;     /* antes sidebar-light */
    --hover-accent:     rgba(209,177,110,0.2);
  }

  /* Ocultar iconos de búsqueda */
  .btn-search,
  i.zmdi.zmdi-search {
    display: none !important;
  }

  /* Contenedor principal */
  .dashboard-container {
    margin-left: 270px; /* coincide con ancho del sidebar */
    padding: 20px;
    min-height: 100vh;
    box-sizing: border-box;
    color: var(--text-light);
    background-color: var(--primary-bg);
    /* fondo con logo */
    background-image: url('<?= SERVERURL ?>views/assets/img/LOGO_CIP.png');
    background-repeat: no-repeat;
    background-position: center;
    background-size: 60%;
    background-blend-mode: overlay;
    overflow-x: hidden;
  }

  /* Título */
  .dashboard-header h1 {
    text-align: center;
    font-size: 2rem;
    margin-bottom: 2.5rem;
    color: var(--primary-accent);
    text-shadow: 2px 2px 6px rgba(0,0,0,0.7);
  }

  /* Fila de estadísticas */
  .stats-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 4rem;
  }
  .stats-card {
    background: var(--secondary-bg);
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    text-align: center;
    flex: 1 1 300px;
    max-width: 320px;
    transition: transform .3s ease;
    border: 1px solid var(--primary-accent);
  }
  .stats-card:hover {
    transform: translateY(-5px);
  }
  .stats-icon {
    font-size: 2.5rem;
    color: var(--primary-accent);
    margin-bottom: .5rem;
  }
  .stats-title {
    font-size: 1.1rem;
    color: var(--primary-accent);
    font-weight: bold;
    margin-bottom: .5rem;
  }
  .stats-number {
    font-size: 2.5rem;
    color: var(--primary-accent);
    font-weight: bold;
    margin-bottom: .5rem;
  }
  .stats-description {
    font-size: .9rem;
    color: rgba(255,255,255,0.7);
  }

  /* Dos columnas */
  .two-columns {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 2.5rem;
    margin-bottom: 2rem;
  }
  .column {
    flex: 1 1 420px;
    max-width: 500px;
  }
  .column h3 {
    text-align: center;
    font-size: 1.4rem;
    color: var(--primary-accent);
    margin-bottom: 1rem;
  }

  /* Slider horizontal */
  .slider-hor {
    position: relative;
    overflow: hidden;
    border-radius: .75rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.5);
    height: 600px;
  }
  .slider-hor .slides {
    display: flex;
    transition: transform .6s ease;
  }
  .slider-hor .slide {
    flex: 0 0 100%;
    height: 100%;
  }
  .slider-hor img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .nav-hor {
    position: absolute;
    top: 50%;
    left: 0; right: 0;
    display: flex;
    justify-content: space-between;
    pointer-events: none;
  }
  .nav-hor button {
    pointer-events: auto;
    background: var(--secondary-bg);
    border: none;
    color: var(--text-light);
    font-size: 1.5rem;
    width: 2.5rem; height: 2.5rem;
    border-radius: 50%;
    cursor: pointer;
    transition: background .3s;
  }
  .nav-hor button:hover {
    background: var(--hover-accent);
  }

  .dots {
    text-align: center;
    margin-top: .5rem;
  }
  .dots .dot {
    display: inline-block;
    width: .6rem; height: .6rem;
    background: rgba(255,255,255,0.4);
    margin: 0 .3rem;
    border-radius: 50%;
    cursor: pointer;
    transition: background .3s;
  }
  .dots .dot.active {
    background: var(--primary-accent);
  }

  /* Responsivo */
  @media (max-width: 768px) {
    .dashboard-container {
      margin-left: 0;
      padding: 1rem;
      background-size: 40%;
    }
    .slider-hor { height: 240px; }
    .column { max-width: 90%; }
  }
</style>

<div class="dashboard-container">
  <header class="dashboard-header">
    <h1>
      <i class="zmdi zmdi-account-circle"></i>
      Bienvenido <?= htmlspecialchars($_SESSION['displayName'] ?? $_SESSION['userName']) ?> a tu Aula Virtual CIP
</h1>

  </header>

  <div class="stats-row">
    <div class="stats-card">
      <div class="stats-icon"><i class="zmdi zmdi-library"></i></div>
      <div class="stats-title">Cursos Matriculados</div>
      <div class="stats-number"><?= $totalCursosEstudiante ?></div>
      <div class="stats-description">Total de cursos donde estás inscrito.</div>
    </div>
    <div class="stats-card">
      <div class="stats-icon"><i class="zmdi zmdi-collection-text"></i></div>
      <div class="stats-title">Material Disponible</div>
      <div class="stats-number"><?= $materialDisponible ?></div>
      <div class="stats-description">Archivos disponibles en sesiones activas.</div>
    </div>
  </div>

  <div class="two-columns">
    <div class="column">
      <h3>Cursos</h3>
      <div class="slider-hor">
        <div class="slides">
          <div class="slide"><img src="<?= SERVERURL ?>views/assets/img/curso1.jpg" alt=""></div>
          <div class="slide"><img src="<?= SERVERURL ?>views/assets/img/curso2.jpg" alt=""></div>
          <div class="slide"><img src="<?= SERVERURL ?>views/assets/img/curso3.jpg" alt=""></div>
        </div>
        <div class="nav-hor">
          <button class="prev-hor">&#10094;</button>
          <button class="next-hor">&#10095;</button>
        </div>
        <div class="dots"></div>
      </div>
    </div>
    <div class="column">
      <h3>Noticias</h3>
      <div class="slider-hor">
        <div class="slides">
          <div class="slide"><img src="<?= SERVERURL ?>views/assets/img/noticia1.jpg" alt=""></div>
          <div class="slide"><img src="<?= SERVERURL ?>views/assets/img/noticia2.jpg" alt=""></div>
          <div class="slide"><img src="<?= SERVERURL ?>views/assets/img/noticia3.jpg" alt=""></div>
        </div>
        <div class="nav-hor">
          <button class="prev-hor">&#10094;</button>
          <button class="next-hor">&#10095;</button>
        </div>
        <div class="dots"></div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll('.slider-hor').forEach(slider => {
    const slides = slider.querySelectorAll('.slide');
    const container = slider.querySelector('.slides');
    const prev = slider.querySelector('.prev-hor');
    const next = slider.querySelector('.next-hor');
    const dotsContainer = slider.querySelector('.dots');
    let index = 0;

    slides.forEach((_, i) => {
      const dot = document.createElement('span');
      dot.classList.add('dot');
      if (i === 0) dot.classList.add('active');
      dot.onclick = () => { index = i; update(); };
      dotsContainer.append(dot);
    });

    const update = () => {
      container.style.transform = `translateX(-${index * 100}%)`;
      dotsContainer.querySelectorAll('.dot')
        .forEach((d,n) => d.classList.toggle('active', n === index));
    };

    prev.onclick = () => update(index = (index - 1 + slides.length) % slides.length);
    next.onclick = () => update(index = (index + 1) % slides.length);
    setInterval(() => update(index = (index + 1) % slides.length), 7000);
  });
});
</script>
