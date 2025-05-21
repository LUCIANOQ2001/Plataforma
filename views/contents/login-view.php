<div class="full-box cover containerLogin">
    <form action="" method="POST" autocomplete="off" class="full-box logInForm">
        <figure class="full-box logo-container">
            <img src="<?php echo SERVERURL; ?>views/assets/img/LOGO_CIP.png" alt="<?php echo COMPANY; ?>" class="img-responsive logo">
        </figure>
        <p class="text-center text-muted text-uppercase company-name"><?php echo COMPANY; ?></p>
        <div class="form-group label-floating">
            <label class="control-label" for="loginUserName">Nombre de usuario</label>
            <input class="form-control input-field" id="loginUserName" type="text" name="loginUserName">
        </div>
        <div class="form-group label-floating">
            <label class="control-label" for="loginUserPass">Contraseña</label>
            <input class="form-control input-field" id="loginUserPass" type="password" name="loginUserPass">
        </div>
        <div class="form-group text-center">
            <input type="submit" value="Iniciar sesión" class="btn btn-raised btn-login">
        </div>
    </form>
</div>

<?php 
    if(isset($_POST['loginUserName'])){
        require_once "./controllers/loginController.php";
        $log = new loginController();
        echo $log->login_session_start_controller();
    }
?>
