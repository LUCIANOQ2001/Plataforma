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
<style>
    /* Si los tres puntos usan la clase .btn-options o .dropdown-toggle: */
  .btn-options,
  .dropdown-toggle {
    display: none !important;
  }
  

/*============ Partes del dashboard*/
/* Sidebar */
.dashboard-sideBar {
    position: fixed;
    width: 270px;
    height: 100vh;
    background: #1e1e2f;
    color: rgb(219, 109, 13);
    box-shadow: 4px 0px 10px rgba(173, 90, 90, 0.5);
    overflow-y: auto;
    transition: all 0.3s ease-in-out;
}

/* Título */
.dashboard-sideBar-title {
    font-size: 22px;
    font-weight: bold;
    padding: 20px;
    text-align: center;
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
}
/* Paleta de colores basada en el logo */
:root {
  --sidebar-red:      #2B2B2B;
  --sidebar-gold:     #D1B16E;
  --sidebar-black:    #e00a0a9b;
  --sidebar-light:    #FFFFFF;
  --sidebar-hover:    rgba(209, 177, 110, 0.2);
}

/* Contenedor principal */
.dashboard-sideBar {
  background: var(--sidebar-red);
  color: var(--sidebar-light);
  box-shadow: 4px 0 10px rgba(0,0,0,0.5);
}

/* Título */
.dashboard-sideBar-title {
  background: var(--sidebar-black);
  color: var(--sidebar-gold);
  font-size: 24px;
  padding: 20px 0;
  border-bottom: 2px solid var(--sidebar-gold);
}

/* Información de usuario */
.dashboard-sideBar-UserInfo {
  background: var(--sidebar-black);
  padding: 15px;
  border-bottom: 1px solid var(--sidebar-gold);
}
.dashboard-sideBar-UserInfo img {
  border: 3px solid var(--sidebar-gold);
}

/* Menú */
.dashboard-sideBar-Menu a {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  color: var(--sidebar-light);
  text-decoration: none;
  font-size: 16px;
  transition: background 0.3s ease;
  border-left: 4px solid transparent;
}
.dashboard-sideBar-Menu a:hover {
  background: var(--sidebar-hover);
  border-left-color: var(--sidebar-gold);
}

/* Íconos */
.dashboard-sideBar-Menu i {
  margin-right: 12px;
  font-size: 20px;
  color: var(--sidebar-gold);
}

/* Submenús */
.submenu {
  background: var(--sidebar-black);
  border-top: 1px solid var(--sidebar-gold);
}
.submenu li a {
  padding-left: 40px;
  font-size: 14px;
}

/* Oculta los tres puntos (mantener inline con tu estilo) */
.btn-options,
.dropdown-toggle {
  display: none !important;
}

/* Usuario */
.dashboard-sideBar-UserInfo {
    text-align: center;
    padding: 15px;
    border-top: 1px solid rgba(255, 255, 255, .3);
    border-bottom: 1px solid rgba(255, 255, 255, .3);
}

.dashboard-sideBar-UserInfo img {
    width: 100px;
    border-radius: 50%;
    box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.3);
}

/* Menú */
.dashboard-sideBar-Menu {
    list-style: none;
    padding: 0;
}

.dashboard-sideBar-Menu li {
    width: 100%;
}

.dashboard-sideBar-Menu a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color:rgb(255, 255, 255);
    text-decoration: none;
    font-size: 16px;
    transition: background 0.3s ease-in-out;
    border-radius: 8px;
}

.dashboard-sideBar-Menu a:hover {
    background: rgba(62, 234, 4, 0.15);
}

/* Submenú oculto */
.submenu {
    display: none;
    padding-left: 20px;
    transition: max-height 0.3s ease-in-out;
}


</style>
<section class="full-box cover dashboard-sideBar">
  <div class="full-box dashboard-sideBar-bg btn-menu-dashboard"></div>
  <div class="full-box dashboard-sideBar-ct">
    <div class="full-box text-uppercase text-center text-titles dashboard-sideBar-title">
      <?= COMPANY ?> <i class="zmdi zmdi-close btn-menu-dashboard visible-xs"></i>
    </div>
    <div class="full-box dashboard-sideBar-UserInfo">
      <figure class="full-box">
        <img src="<?= SERVERURL ?>views/assets/img/LOGO_CIP.png" alt="UserIcon">
        <figcaption class="text-center text-titles">
          <?= htmlspecialchars($_SESSION['displayName'] ?? $_SESSION['userName']) ?>
        </figcaption>
      </figure>
      <ul class="full-box list-unstyled text-center">
        <li>
          <a href="<?= SERVERURL ?>account/<?= $_SESSION['userKey'] ?>/">
            <i class="zmdi zmdi-settings"></i>
          </a>
        </li>
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
      <li><a href="<?= SERVERURL ?>dashboard/"><i class="zmdi zmdi-view-dashboard"></i> Inicio</a></li>
      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-account"></i> Docentes <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="submenu list-unstyled full-box">
          <li><a href="<?= SERVERURL ?>admin/">Nuevo Docente</a></li>
          <li><a href="<?= SERVERURL ?>adminlist/">Lista de Docentes</a></li>
          <li><a href="<?= SERVERURL ?>consultaslist/">Consultas</a></li>
        </ul>
      </li>
      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-face"></i> Estudiantes <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="submenu list-unstyled full-box">
          <li><a href="<?= SERVERURL ?>student/">Nuevo Estudiante</a></li>
          <li><a href="<?= SERVERURL ?>studentlist/">Lista de Estudiantes</a></li>
          <li><a href="<?= SERVERURL ?>asistencia/">Asistencias</a></li>
        </ul>
      </li>
      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-videocam"></i> Clases <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="submenu list-unstyled full-box">
          <li><a href="<?= SERVERURL ?>class/">Nueva Clase</a></li>
          <li><a href="<?= SERVERURL ?>classlist/">Lista de Clases</a></li>
        </ul>
      </li>
      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-book"></i> Cursos <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="submenu list-unstyled full-box">
          <li><a href="<?= SERVERURL ?>curso/">Nuevo Curso</a></li>
          <li><a href="<?= SERVERURL ?>cursolist/">Lista de Cursos</a></li>
        </ul>
      </li>

    <?php elseif ($_SESSION['userType'] === "Docente"): ?>

      <!-- NUEVO: enlace “Mis datos” -->
      <li>
        <a href="<?= SERVERURL ?>teacherinfo/<?= $_SESSION['userKey'] ?>/">
          <i class="zmdi zmdi-account-box"></i> Mis datos
        </a>
      </li>

      <li><a href="<?= SERVERURL ?>dashboard/"><i class="zmdi zmdi-view-dashboard"></i> Inicio</a></li>
      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-comment-text"></i> Consultas <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="submenu list-unstyled full-box">
          <li><a href="<?= SERVERURL ?>consultaslist/">Historial de Consultas</a></li>
        </ul>
      </li>
      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-face"></i> Estudiantes <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="submenu list-unstyled full-box">
          <li><a href="<?= SERVERURL ?>teacher-students/">Lista de Estudiantes</a></li>
        </ul>
      </li>

      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-book"></i> Cursos <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="submenu list-unstyled full-box">
          <li><a href="<?= SERVERURL ?>miscursos/">Mis Cursos</a></li>
        </ul>
      </li>

    <?php else: ?>

      <!-- Menú Estudiante -->
      <li>
        <a href="<?= SERVERURL ?>studentinfo/<?= $_SESSION['userKey'] ?>/">
          <i class="zmdi zmdi-account-box"></i> Mis datos
        </a>
      </li>
      <li><a href="<?= SERVERURL ?>home/"><i class="zmdi zmdi-view-dashboard"></i> Inicio</a></li>
      <li><a href="<?= SERVERURL ?>anuncio/"><i class="zmdi zmdi-notifications"></i> Anuncios recientes</a></li>
      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-book"></i> Cursos <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="submenu list-unstyled full-box">
          <li><a href="<?= SERVERURL ?>miscursos/">Mis Cursos</a></li>
        </ul>
      </li>
      <li>
        <a href="#!" class="btn-sideBar-SubMenu">
          <i class="zmdi zmdi-comment-text"></i> Consultas <i class="zmdi zmdi-caret-down pull-right"></i>
        </a>
        <ul class="submenu list-unstyled full-box">
          <li><a href="<?= SERVERURL ?>consultas/">Nueva Consulta</a></li>
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
        document.querySelectorAll(".submenu").forEach(menu=>menu.style.display="none");
        let sib = btn.nextElementSibling;
        sib.style.display = sib.style.display==="block"?"none":"block";
      });
    });
  });
</script>
