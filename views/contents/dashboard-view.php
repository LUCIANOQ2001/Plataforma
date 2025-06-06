<?php
require_once "./controllers/studentController.php";
require_once "./controllers/adminController.php";
require_once "./controllers/cursoController.php";

$insEstudiante = new studentController();
$insDocente    = new adminController();
$insCurso      = new cursoController();

$totalEstudiantes = $insEstudiante->count_estudiantes();
$totalDocentes    = $insDocente->count_docentes();
$totalCursos      = $insCurso->count_cursos();
?>

<style>
      /* Si los tres puntos usan la clase .btn-options o .dropdown-toggle: */
  .btn-options,
  .dropdown-toggle {
    display: none !important;
  }

.dashboard-container {
    margin-left: 170px;         /* Mueve el contenido hacia la derecha para no tapar el sidebar */
    padding: 30px;              /* Espaciado interior general */
    box-sizing: border-box;
    color: #fff;
    background-color: #2d2d3f;
}

.dashboard-header h1 {
    text-align: center;
    font-size: 28px;
    margin-bottom: 40px;        /* Espacio inferior del título */
    color: #00e5ff;
    text-shadow: 1px 1px 5px #000;
}

.stats-row {
    display: flex;
    justify-content: space-around; /* Distribuye las tarjetas horizontalmente */
    flex-wrap: wrap;
    gap: 25px;                     /* Espacio entre tarjetas (horizontal y vertical) */
    margin-top: 80px;             /* ← AUMENTA ESTA LÍNEA para bajar todo el bloque */
    /* Para mover este bloque:
       - Más abajo  => aumenta margin-top
       - Más arriba => reduce margin-top
       - Más a la derecha => usa padding-left
       - Más a la izquierda => usa padding-right */
}

.stats-card {
    background: linear-gradient(145deg, #1e1f2f, #2e2f40);
    padding: 30px 25px;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.4);
    text-align: center;
    flex: 1;
    min-width: 250px;
    max-width: 300px;
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-8px); /* Sube al hacer hover */
}

.stats-icon {
    font-size: 45px;
    color: #ff5722;
    margin-bottom: 10px;
}

.stats-title {
    font-size: 20px;
    color: #03a9f4;
    margin-bottom: 10px;
    font-weight: bold;
}

.stats-number {
    font-size: 36px;
    color: #00ff00;
    font-weight: bold;
}

.stats-description {
    font-size: 14px;
    color: #ccc;
    margin-top: 5px;
}
</style>

<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>
            <i class="zmdi zmdi-graduation-cap zmdi-hc-fw"></i> Aula Virtual CIP Lambayeque
        </h1>
    </header>

    <div class="stats-row">
        <!-- Estudiantes -->
        <div class="stats-card">
            <div class="stats-icon"><i class="zmdi zmdi-accounts"></i></div>
            <div class="stats-title">Estudiantes Registrados</div>
            <div class="stats-number"><?php echo $totalEstudiantes; ?></div>
            <div class="stats-description">Total de estudiantes inscritos.</div>
        </div>

        <!-- Docentes -->
        <div class="stats-card">
            <div class="stats-icon"><i class="zmdi zmdi-account-box-mail"></i></div>
            <div class="stats-title">Docentes Activos</div>
            <div class="stats-number"><?php echo $totalDocentes; ?></div>
            <div class="stats-description">Docentes habilitados para clases.</div>
        </div>

        <!-- Cursos -->
        <div class="stats-card">
            <div class="stats-icon"><i class="zmdi zmdi-book"></i></div>
            <div class="stats-title">Cursos Creados</div>
            <div class="stats-number"><?php echo $totalCursos; ?></div>
            <div class="stats-description">Cantidad de cursos registrados.</div>
        </div>
    </div>
</div>
