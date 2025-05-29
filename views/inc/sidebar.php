<?php
// views/contents/sidebar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!in_array($_SESSION['userType'] ?? '', ['Administrador','Docente','Estudiante'])) {
    echo (new loginController())->login_session_force_destroy_controller();
    exit;
}
?>
<section class="full-box cover dashboard-sideBar">
  <div class="full-box dashboard-sideBar-bg btn-menu-dashboard"></div>
  <div class="full-box dashboard-sideBar-ct">
    <div class="full-box text-uppercase text-center text-titles dashboard-sideBar-title">
      <?= COMPANY ?> <i class="zmdi zmdi-close btn-menu-dashboard visible-xs"></i>
    </div>
    <div class="full-box dashboard-sideBar-UserInfo">
      <figure class="full-box">
        <img src="<?= SERVERURL ?>views/assets/img/logo.png" alt="UserIcon">
        <figcaption class="text-center text-titles"><?= htmlspecialchars($_SESSION['userName']) ?></figcaption>
      </figure>
      <ul class="full-box list-unstyled text-center">
        <?php if (in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
        <li>
          <a href="<?= SERVERURL ?>account/<?= $_SESSION['userKey'] ?>/">
            <i class="zmdi zmdi-settings"></i>
          </a>
        </li>
        <?php endif; ?>
        <li>
          <a href="#!" class="btnFormsAjax" data-action="logout" data-id="form-logout">
            <i class="zmdi zmdi-power"></i>
          </a>
        </li>
      </ul>
      <form id="form-logout" method="POST" action="">
        <input type="hidden" name="token" value="<?= $_SESSION['userToken'] ?>">
      </form>
    </div>

    <ul class="list-unstyled full-box dashboard-sideBar-Menu">

    <?php if ($_SESSION['userType'] === "Administrador"): ?>

      <!-- Menú Administrador -->
      <li>
        <a href="<?= SERVERURL ?>dashboard/">
          <i class="zmdi zmdi-view-dashboard zmdi-hc-fw"></i> Inicio
        </a>
      </li>

      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-account zmdi-hc-fw"></i> Docentes
          <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="list-unstyled full-box submenu">
          <li><a href="<?= SERVERURL ?>admin/">Nuevo Docente</a></li>
          <li><a href="<?= SERVERURL ?>adminlist/">Lista de Docentes</a></li>
          <li><a href="<?= SERVERURL ?>consultaslist/">Historial de Consultas</a></li>
        </ul>
      </li>

      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-face zmdi-hc-fw"></i> Estudiantes
          <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="list-unstyled full-box submenu">
          <li><a href="<?= SERVERURL ?>student/">Nuevo Estudiante</a></li>
          <li><a href="<?= SERVERURL ?>studentlist/">Listado de Estudiantes</a></li>
          <li><a href="<?= SERVERURL ?>asistencia/">Registro de Asistencias</a></li>
        </ul>
      </li>

      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-videocam zmdi-hc-fw"></i> Clases
          <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="list-unstyled full-box submenu">
          <li><a href="<?= SERVERURL ?>class/">Nueva Clase</a></li>
          <li><a href="<?= SERVERURL ?>classlist/">Lista de Clases</a></li>
        </ul>
      </li>

      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-book zmdi-hc-fw"></i> Cursos
          <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="list-unstyled full-box submenu">
          <li><a href="<?= SERVERURL ?>curso/">Nuevo Curso</a></li>
          <li><a href="<?= SERVERURL ?>cursolist/">Lista de Cursos</a></li>
        </ul>
      </li>

    <?php elseif ($_SESSION['userType'] === "Docente"): ?>

      <!-- Menú Docente -->
      <li>
        <a href="<?= SERVERURL ?>dashboard/">
          <i class="zmdi zmdi-view-dashboard zmdi-hc-fw"></i> Inicio
        </a>
      </li>

      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-comment-text zmdi-hc-fw"></i> Consultas
          <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="list-unstyled full-box submenu">
          <li><a href="<?= SERVERURL ?>consultaslist/">Historial de Consultas</a></li>
        </ul>
      </li>

      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-face zmdi-hc-fw"></i> Estudiantes
          <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="list-unstyled full-box submenu">
          <li><a href="<?= SERVERURL ?>studentlist/">Listado de Estudiantes</a></li>
        </ul>
      </li>

      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-videocam zmdi-hc-fw"></i> Clases
          <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="list-unstyled full-box submenu">
          <li><a href="<?= SERVERURL ?>class/">Nueva Clase</a></li>
          <li><a href="<?= SERVERURL ?>classlist/">Lista de Clases</a></li>
        </ul>
      </li>

      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-book zmdi-hc-fw"></i> Cursos
          <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="list-unstyled full-box submenu">
          <li><a href="<?= SERVERURL ?>miscursos/">Mis Cursos</a></li>
        </ul>
      </li>

    <?php else: ?>

      <!-- Menú Estudiante -->
      <li>
        <a href="<?= SERVERURL ?>home/">
          <i class="zmdi zmdi-view-dashboard zmdi-hc-fw"></i> Inicio
        </a>
      </li>

      <!-- Opción A: Anuncios recientes y Material de curso -->
      <li>
        <a href="<?= SERVERURL ?>anuncio/">
          <i class="zmdi zmdi-notifications"></i> Anuncios recientes
        </a>
      </li>
      <li>
        <a href="<?php echo SERVERURL; ?>materialcurso/<?php echo $c['id']; ?>/">
          <i class="zmdi zmdi-collection-text zmdi-hc-fw"></i> Material de curso
        </a>
      </li>

      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-book zmdi-hc-fw"></i> Cursos
          <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="list-unstyled full-box submenu">
          <li><a href="<?= SERVERURL ?>miscursos/">Mis Cursos</a></li>
        </ul>
      </li>

      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-comment-text zmdi-hc-fw"></i> Consultas Generales
          <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="list-unstyled full-box submenu">
          <li><a href="<?= SERVERURL ?>consultas/">Nueva Consulta</a></li>
          <li><a href="<?= SERVERURL ?>consultaslist/">Historial de Consultas</a></li>
        </ul>
      </li>

    <?php endif; ?>

    </ul>
  </div>
</section>

<script>
  document.addEventListener("DOMContentLoaded", function(){
    document.querySelectorAll(".btn-sideBar-SubMenu").forEach(btn => {
      btn.addEventListener("click", function(e){
        e.preventDefault();
        // cerramos todos los submenus
        document.querySelectorAll(".submenu").forEach(menu => {
          if (menu !== btn.nextElementSibling) {
            menu.style.display = "none";
          }
        });
        // alternamos el submenu actual
        let sibling = btn.nextElementSibling;
        sibling.style.display = sibling.style.display === "block" ? "none" : "block";
      });
    });
  });
</script>
