<section class="full-box cover dashboard-sideBar">
  <div class="full-box dashboard-sideBar-bg btn-menu-dashboard"></div>
  <div class="full-box dashboard-sideBar-ct">
    <div class="full-box text-uppercase text-center text-titles dashboard-sideBar-title">
      <?php echo COMPANY; ?> <i class="zmdi zmdi-close btn-menu-dashboard visible-xs"></i>
    </div>
    <div class="full-box dashboard-sideBar-UserInfo">
      <figure class="full-box">
        <img src="<?php echo SERVERURL; ?>views/assets/img/logo.png" alt="UserIcon">
        <figcaption class="text-center text-titles"><?php echo $_SESSION['userName']; ?></figcaption>
      </figure>
      <ul class="full-box list-unstyled text-center">
        <?php if(in_array($_SESSION['userType'], ['Administrador','Docente'])): ?>
        <li>
          <a href="<?php echo SERVERURL; ?>account/<?php echo $_SESSION['userKey']; ?>/">
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
      <form action="" id="form-logout" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?php echo $_SESSION['userToken']; ?>">
      </form>
    </div>

    <ul class="list-unstyled full-box dashboard-sideBar-Menu">

      <?php if($_SESSION['userType']==="Administrador"): ?>
        <!-- Menú Administrador -->
        <li><a href="<?php echo SERVERURL; ?>dashboard/"><i class="zmdi zmdi-view-dashboard zmdi-hc-fw"></i> Inicio</a></li>

        <li>
          <a href="#!" class="btn-sideBar-SubMenu">
            <i class="zmdi zmdi-account zmdi-hc-fw"></i> Docentes <i class="zmdi zmdi-caret-down pull-right"></i>
          </a>
          <ul class="list-unstyled full-box submenu">
            <li><a href="<?php echo SERVERURL; ?>admin/">Nuevo Docente</a></li>
            <li><a href="<?php echo SERVERURL; ?>adminlist/">Lista de Docentes</a></li>
            <li><a href="<?php echo SERVERURL; ?>consultaslist/">Historial de Consultas</a></li>
          </ul>
        </li>

        <li>
          <a href="#!" class="btn-sideBar-SubMenu">
            <i class="zmdi zmdi-face zmdi-hc-fw"></i> Estudiantes <i class="zmdi zmdi-caret-down pull-right"></i>
          </a>
          <ul class="list-unstyled full-box submenu">
            <li><a href="<?php echo SERVERURL; ?>student/">Nuevo Estudiante</a></li>
            <li><a href="<?php echo SERVERURL; ?>studentlist/">Listado de Estudiantes</a></li>
            <li><a href="<?php echo SERVERURL; ?>asistencia/">Registro de Asistencias</a></li>
          </ul>
        </li>

        <li>
          <a href="#!" class="btn-sideBar-SubMenu">
            <i class="zmdi zmdi-videocam zmdi-hc-fw"></i> Clases <i class="zmdi zmdi-caret-down pull-right"></i>
          </a>
          <ul class="list-unstyled full-box submenu">
            <li><a href="<?php echo SERVERURL; ?>class/">Nueva Clase</a></li>
            <li><a href="<?php echo SERVERURL; ?>classlist/">Lista de Clases</a></li>
          </ul>
        </li>

      <?php elseif($_SESSION['userType']==="Docente"): ?>
        <!-- Menú Docente (igual al de Admin, sin “Nuevo Docente”) -->
        <li><a href="<?php echo SERVERURL; ?>dashboard/"><i class="zmdi zmdi-view-dashboard zmdi-hc-fw"></i> Inicio</a></li>

        <li>
          <a href="#!" class="btn-sideBar-SubMenu">
            <i class="zmdi zmdi-account zmdi-hc-fw"></i> Docentes <i class="zmdi zmdi-caret-down pull-right"></i>
          </a>
          <ul class="list-unstyled full-box submenu">
            <li><a href="<?php echo SERVERURL; ?>consultaslist/">Historial de Consultas</a></li>
          </ul>
        </li>

        <li>
          <a href="#!" class="btn-sideBar-SubMenu">
            <i class="zmdi zmdi-face zmdi-hc-fw"></i> Estudiantes <i class="zmdi zmdi-caret-down pull-right"></i>
          </a>
          <ul class="list-unstyled full-box submenu">
            <li><a href="<?php echo SERVERURL; ?>studentlist/">Listado de Estudiantes</a></li>
            <li><a href="<?php echo SERVERURL; ?>asistencia/">Registro de Asistencias</a></li>
          </ul>
        </li>

        <li>
          <a href="#!" class="btn-sideBar-SubMenu">
            <i class="zmdi zmdi-videocam zmdi-hc-fw"></i> Clases <i class="zmdi zmdi-caret-down pull-right"></i>
          </a>
          <ul class="list-unstyled full-box submenu">
            <li><a href="<?php echo SERVERURL; ?>class/">Nueva Clase</a></li>
            <li><a href="<?php echo SERVERURL; ?>classlist/">Lista de Clases</a></li>
          </ul>
        </li>

      <?php else: ?>
        <!-- Menú Estudiante -->
        <li><a href="<?php echo SERVERURL; ?>home/"><i class="zmdi zmdi-view-dashboard zmdi-hc-fw"></i> Inicio</a></li>
        <li><a href="<?php echo SERVERURL; ?>videonow/"><i class="zmdi zmdi-tv-play zmdi-hc-fw"></i> Clases de hoy</a></li>
        <li><a href="<?php echo SERVERURL; ?>videolist/"><i class="zmdi zmdi-tv-list zmdi-hc-fw"></i> Listado de Clases</a></li>
        <li><a href="<?php echo SERVERURL; ?>asistencialist/"><i class="zmdi zmdi-check-circle zmdi-hc-fw"></i> Historial de Asistencias</a></li>
        <li>
          <a href="#!" class="btn-sideBar-SubMenu">
            <i class="zmdi zmdi-comment-text zmdi-hc-fw"></i> Consultas <i class="zmdi zmdi-caret-down pull-right"></i>
          </a>
          <ul class="list-unstyled full-box submenu">
            <li><a href="<?php echo SERVERURL; ?>consultas/">Nueva Consulta</a></li>
            <li><a href="<?php echo SERVERURL; ?>consultaslist/">Historial de Consultas</a></li>
          </ul>
        </li>
      <?php endif; ?>

    </ul>
  </div>
</section>

<script>
  document.addEventListener("DOMContentLoaded", function(){
    document.querySelectorAll(".btn-sideBar-SubMenu").forEach(item => {
      item.addEventListener("click", () => {
        const sub = item.nextElementSibling;
        document.querySelectorAll(".submenu").forEach(s => { if(s!==sub) s.style.display = "none"; });
        sub.style.display = sub.style.display==="block"?"none":"block";
      });
    });
  });
</script>
