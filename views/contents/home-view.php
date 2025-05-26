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
.dashboard-container {
    margin-left: 170px;
    padding: 30px 40px;
    max-width: calc(100vw - 170px);  /* ← evitar desbordar el sidebar */
    overflow-x: hidden;
    box-sizing: border-box;
    color: #fff;
    background-color: #1e1f28;
}

.dashboard-header h1 {
    text-align: center;
    font-size: 28px;
    margin-bottom: 40px; /* ← Aumentado para que no se pegue */
    color: #00e5ff;
    text-shadow: 1px 1px 5px #000;
}

.stats-row {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 30px;
    margin-bottom: 60px; /* ← Más separación con los sliders */
}

.stats-card {
    background: linear-gradient(145deg, #2a2a3a, #3a3a4a);
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    text-align: center;
    flex: 1;
    min-width: 300px;
    max-width: 320px;
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
}

.stats-icon {
    font-size: 40px;
    color: #ff9800;
    margin-bottom: 10px;
}

.stats-title {
    font-size: 18px;
    color: #29b6f6;
    margin-bottom: 5px;
    font-weight: bold;
}

.stats-number {
    font-size: 34px;
    color: #00ff00;
    font-weight: bold;
}

.stats-description {
    font-size: 13px;
    color: #ccc;
}

.two-columns {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 40px; /* Espacio entre columnas */
}

.column {
    flex: 1;
    min-width: 420px;
    max-width: 500px;
    margin-bottom: 40px; /* ← Separación inferior si hay varias filas */
}

.column h3 {
    text-align: center;
    margin-bottom: 15px;
    font-size: 1.4rem;
    color: #03a9f4;
}

.slider-hor {
    position: relative;
    overflow: hidden;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,.4);
    height: 700px;  /* ← Altura controlada */
}

.slider-hor .slides {
    display: flex;
    transition: transform .6s ease;
}

.slider-hor .slide {
    min-width: 100%;
    height: 100%;
    overflow: hidden;
}

.slider-hor img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* ← Acomoda las imágenes al tamaño del slide */
    display: block;
}

.nav-hor {
    position: absolute;
    top: 50%;
    width: 100%;
    display: flex;
    justify-content: space-between;
    pointer-events: none;
}

.nav-hor button {
    background: rgba(0,0,0,.4);
    border: none;
    color: #fff;
    font-size: 1.5rem;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    pointer-events: auto;
    transition: background .3s;
}

.nav-hor button:hover {
    background: rgba(0,0,0,.6);
}

.dots {
    text-align: center;
    margin-top: 10px;
}

.dots .dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    background: rgba(255,255,255,.4);
    margin: 0 4px;
    border-radius: 50%;
    cursor: pointer;
    transition: background .3s;
}

.dots .dot.active {
    background: #fff;
}

@media(max-width: 768px) {
    .dashboard-container {
        margin-left: 0;
        padding: 20px;
    }
    .two-columns {
        flex-direction: column;
        align-items: center;
    }
    .slider-hor, .slider-hor .slide {
        height: 240px;
    }
}
</style>

<div class="dashboard-container">
  <header class="dashboard-header">
    <h1><i class="zmdi zmdi-account-circle"></i> Bienvenido a <?php echo COMPANY; ?></h1>
  </header>

  <div class="stats-row">
    <div class="stats-card">
      <div class="stats-icon"><i class="zmdi zmdi-library"></i></div>
      <div class="stats-title">Cursos Matriculados</div>
      <div class="stats-number"><?php echo $totalCursosEstudiante; ?></div>
      <div class="stats-description">Total de cursos donde estás inscrito.</div>
    </div>
    <div class="stats-card">
      <div class="stats-icon"><i class="zmdi zmdi-collection-text"></i></div>
      <div class="stats-title">Material Disponible</div>
      <div class="stats-number"><?php echo $materialDisponible; ?></div>
      <div class="stats-description">Archivos disponibles en sesiones activas.</div>
    </div>
  </div>

  <div class="two-columns">
    <div class="column">
      <h3>Cursos</h3>
      <div class="slider-hor">
        <div class="slides">
          <div class="slide"><img src="<?php echo SERVERURL;?>views/assets/img/curso1.jpg" alt=""></div>
          <div class="slide"><img src="<?php echo SERVERURL;?>views/assets/img/curso2.jpg" alt=""></div>
          <div class="slide"><img src="<?php echo SERVERURL;?>views/assets/img/curso3.jpg" alt=""></div>
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
          <div class="slide"><img src="<?php echo SERVERURL;?>views/assets/img/noticia1.jpg" alt=""></div>
          <div class="slide"><img src="<?php echo SERVERURL;?>views/assets/img/noticia2.jpg" alt=""></div>
          <div class="slide"><img src="<?php echo SERVERURL;?>views/assets/img/noticia3.jpg" alt=""></div>
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
      if(i === 0) dot.classList.add('active');
      dot.onclick = () => { index = i; update(); };
      dotsContainer.append(dot);
    });

    const update = () => {
      container.style.transform = `translateX(-${index * 100}%)`;
      dotsContainer.querySelectorAll('.dot').forEach((d,n) => d.classList.toggle('active', n === index));
    };

    prev.onclick = () => update(index = (index - 1 + slides.length) % slides.length);
    next.onclick = () => update(index = (index + 1) % slides.length);
    setInterval(() => update(index = (index + 1) % slides.length), 7000);
  });
});
</script>
