<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Salón Virtual</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>

  <!-- Aplica el margen para no tapar el sidebar -->
  <div class="content-wrapper">

    <!-- Cabecera -->
    <div class="page-header welcome-header">
      <h1 class="text-titles text-center">
        <i class="zmdi zmdi-store zmdi-hc-fw"></i>
        Bienvenido a <small><?php echo COMPANY; ?></small>
      </h1>
    </div>

    <!-- 1) SLIDER VERTICAL -->
    <div class="virtual-classroom">
      <div class="slider-wrapper-vertical">
        <button class="slider-btn-vert prev-vert">▲</button>
        <div class="slider-container-vertical">
          <!-- Tus 4 slides idénticos a antes -->
          <div class="slide-vert active-vert" style="background: linear-gradient(135deg, #3a3a5f, #4b4b6f);">
            <h2 class="classroom-title">📌 PIZARRA VIRTUAL</h2>
            <p class="classroom-text">Aquí encontrarás las últimas anotaciones de la clase en curso.</p>
            <div class="board-content"><p id="board-text">Bienvenidos a la clase de hoy. 🌎</p></div>
          </div>
          <div class="slide-vert" style="background: linear-gradient(135deg, #007bff, #00c6ff);">
            <h2 class="classroom-title">🧑‍🎓 Estudiantes Conectados</h2>
            <ul class="student-list">
              <li>Juan Pérez ✅</li>
              <li>María López ✅</li>
              <li>Carlos Sánchez ✅</li>
            </ul>
          </div>
          <div class="slide-vert" style="background: linear-gradient(135deg, #ff9800, #f44336);">
            <h2 class="classroom-title">📂 Material de Clase</h2>
            <p class="classroom-text">Descarga recursos para la clase actual.</p>
            <a href="<?php echo SERVERURL; ?>downloads/material.pdf" class="class-download">📥 Descargar Material</a>
          </div>
          <div class="slide-vert" style="background: linear-gradient(135deg, #0288d1, #26c6da);">
            <h2 class="classroom-title">🎥 Clase en Vivo</h2>
            <p class="classroom-text">Únete a la sesión en vivo ahora.</p>
            <a href="<?php echo SERVERURL; ?>videonow/" class="class-join">▶️ Ingresar a la Clase</a>
          </div>
        </div>
        <button class="slider-btn-vert next-vert">▼</button>
      </div>
    </div>

    <!-- 2) SLIDERS HORIZONTALES -->
    <div class="container-fluid">
      <div class="row">

        <!-- Cursos -->
        <div class="col-md-6">
          <h3 class="text-center">Cursos</h3>
          <div class="slider-wrapper">
            <button class="slider-btn prev">&#10094;</button>
            <div class="slider-container">
              <div class="slide">
                <img src="<?php echo SERVERURL; ?>views/assets/img/curso1.jpg" alt="Curso 1">
              </div>
              <div class="slide">
                <img src="<?php echo SERVERURL; ?>views/assets/img/curso2.jpg" alt="Curso 2">
              </div>
              <div class="slide">
                <img src="<?php echo SERVERURL; ?>views/assets/img/curso3.jpg" alt="Curso 3">
              </div>
            </div>
            <button class="slider-btn next">&#10095;</button>
            <div class="slider-dots"></div>
          </div>
        </div>

        <!-- Noticias -->
        <div class="col-md-6">
          <h3 class="text-center">Noticias</h3>
          <div class="slider-wrapper">
            <button class="slider-btn prev">&#10094;</button>
            <div class="slider-container">
              <div class="slide">
                <img src="<?php echo SERVERURL; ?>views/assets/img/noticia1.jpg" alt="Noticia 1">
              </div>
              <div class="slide">
                <img src="<?php echo SERVERURL; ?>views/assets/img/noticia2.jpg" alt="Noticia 2">
              </div>
              <div class="slide">
                <img src="<?php echo SERVERURL; ?>views/assets/img/noticia3.jpg" alt="Noticia 3">
              </div>              
            </div>
            <button class="slider-btn next">&#10095;</button>
            <div class="slider-dots"></div>
          </div>
        </div>

      </div>
    </div>

  </div> <!-- /content-wrapper -->

  <!-- JS Slider -->
  <script>
  document.addEventListener("DOMContentLoaded", function(){

    // Vertical
    (function(){
      const slidesV = document.querySelectorAll(".slide-vert");
      let idxV = 0;
      function updV(i){ slidesV.forEach((s,n)=>s.classList.toggle("active-vert",n===i)); idxV=i; }
      document.querySelector(".prev-vert").onclick = ()=> updV((idxV-1+slidesV.length)%slidesV.length);
      document.querySelector(".next-vert").onclick = ()=> updV((idxV+1)%slidesV.length);
      setInterval(()=> updV((idxV+1)%slidesV.length), 7000);
    })();

    // Horizontales
    document.querySelectorAll('.slider-wrapper').forEach(wrapper=>{
      const cont = wrapper.querySelector('.slider-container');
      const slides = wrapper.querySelectorAll('.slide');
      const prev   = wrapper.querySelector('.slider-btn.prev');
      const next   = wrapper.querySelector('.slider-btn.next');
      const dotsC  = wrapper.querySelector('.slider-dots');
      let idx=0;

      // crea puntos
      slides.forEach((_,i)=>{
        const dot = document.createElement('span');
        dot.className = i===0?'dot active':'dot';
        dot.onclick = ()=>{ idx=i; upd(); };
        dotsC.appendChild(dot);
      });

      function upd(){
        cont.style.transform = `translateX(${-idx * wrapper.clientWidth}px)`;
        dotsC.querySelectorAll('.dot').forEach((d,n)=>d.classList.toggle('active',n===idx));
      }

      prev.onclick = ()=>{ idx=(idx-1+slides.length)%slides.length; upd(); };
      next.onclick = ()=>{ idx=(idx+1)%slides.length; upd(); };
      setInterval(()=>{ idx=(idx+1)%slides.length; upd(); }, 7000);
    });

  });
  </script>

</body>
</html>
