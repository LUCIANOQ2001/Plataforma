<?php
// Solo arrancamos la sesión si aún no existe
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$actionsRequired = false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include "./views/inc/links.php"; ?>
</head>
<body>
    <?php 
        $getViews = new viewsController();
        $response = $getViews->get_views_controller();

        if ($response === "login"):
            require_once "./views/contents/login-view.php";
        else:
            require_once "./controllers/loginController.php";
            $sc = new loginController();
            echo $sc->check_access($_SESSION['userToken'] ?? null, $_SESSION['userName'] ?? null);

            if (isset($_POST['token'])) {
                echo $sc->login_session_destroy_controller();
            }

            include "./views/inc/sidebar.php";
    ?>
    <section class="full-box dashboard-contentPage">
        <?php
            include "./views/inc/navbar.php";
            require_once $response;
        ?>
    </section>
    <?php endif; ?>

    <?php include "./views/inc/scripts.php"; ?>
</body>
</html>
