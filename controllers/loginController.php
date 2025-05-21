<?php
// dependiendo de desde dónde se llame, ajusta la ruta al modelo
if($actionsRequired){
    require_once "../models/loginModel.php";
}else{
    require_once "./models/loginModel.php";
}

class loginController extends loginModel {

    /**
     * Controlador para iniciar sesión
     */
    public function login_session_start_controller(){
        // sanitizar entradas
        $userName = self::clean_string($_POST['loginUserName']);
        $userPass = self::clean_string($_POST['loginUserPass']);
        $userPass = self::encryption($userPass);

        $data = [
            "Usuario" => $userName,
            "Clave"   => $userPass
        ];

        // pedimos al modelo que intente iniciar sesión
        if($dataAccount = self::login_session_start_model($data)){
            if($dataAccount->rowCount() === 1){
                $row = $dataAccount->fetch();
                session_start();
                $_SESSION['userName']      = $row['Usuario'];
                $_SESSION['userType']      = $row['Tipo'];
                $_SESSION['userKey']       = $row['Codigo'];
                $_SESSION['userPrivilege'] = $row['Privilegio'];
                $_SESSION['userToken']     = md5(uniqid(mt_rand(), true));

                // Asignar avatar y URL de redirección según tipo
                if($row['Tipo'] === "Administrador"){
                    $_SESSION['Avatar'] = "avatar-chef.png";
                    $url = SERVERURL . "dashboard/";
                }
                elseif($row['Tipo'] === "Docente"){
                    // puedes personalizar avatares distintos para docentes
                    if($row['Genero'] === "Masculino"){
                        $_SESSION['Avatar'] = "avatar-user-male.png";
                    } else {
                        $_SESSION['Avatar'] = "avatar-user-female.png";
                    }
                    $url = SERVERURL . "dashboard/"; /* falta arreglar esto*/
                }
                elseif($row['Tipo'] === "Estudiante"){
                    if($row['Genero'] === "Masculino"){
                        $_SESSION['Avatar'] = "avatar-user-male.png";
                    } else {
                        $_SESSION['Avatar'] = "avatar-user-female.png";
                    }
                    $url = SERVERURL . "home/";
                } else {
                    // por defecto
                    $_SESSION['Avatar'] = "avatar-user-male.png";
                    $url = SERVERURL . "login/";
                }

                // redirige en JavaScript
                return '<script type="text/javascript">'
                     . 'window.location="' . $url . '";'
                     . '</script>';
            } else {
                // credenciales incorrectas
                $dataAlert = [
                    "title" => "Error de inicio de sesión",
                    "text"  => "El nombre de usuario o la contraseña no son correctos.",
                    "type"  => "error"
                ];
                return self::sweet_alert_single($dataAlert);
            }
        } else {
            // falla la petición al modelo
            $dataAlert = [
                "title" => "Error inesperado",
                "text"  => "No se pudo procesar la petición.",
                "type"  => "error"
            ];
            return self::sweet_alert_single($dataAlert);
        }
    }

    /**
     * Controlador para cerrar sesión (logout normal)
     */
    public function login_session_destroy_controller(){
        $token = $_POST['token'];
        $data  = [
            "userName"  => $_SESSION['userName'],
            "userToken" => $_SESSION['userToken'],
            "token"     => $token
        ];
        if(self::login_session_destroy_model($data)){
            return '<script type="text/javascript">'
                 . 'window.location="'.SERVERURL.'login/";'
                 . '</script>';
        } else {
            $dataAlert = [
                "title" => "Error al cerrar sesión",
                "text"  => "No se pudo cerrar la sesión.",
                "type"  => "error"
            ];
            return self::sweet_alert_single($dataAlert);
        }
    }

    /**
     * Controlador para forzar cierre de sesión
     */
    public function login_session_force_destroy_controller(){
        $token = $_SESSION['userToken'];
        $data  = [
            "userName"  => $_SESSION['userName'],
            "userToken" => $_SESSION['userToken'],
            "token"     => $token
        ];
        if(self::login_session_destroy_model($data)){
            return '<script type="text/javascript">'
                 . 'window.location="'.SERVERURL.'login/";'
                 . '</script>';
        } else {
            $dataAlert = [
                "title" => "Error al cerrar sesión",
                "text"  => "No se pudo cerrar la sesión forzada.",
                "type"  => "error"
            ];
            return self::sweet_alert_single($dataAlert);
        }
    }

    /**
     * Verifica acceso a vistas protegidas
     */
    public function check_access($userToken, $userVar){
        if(!isset($userToken) || !isset($userVar)){
            session_start();
            session_destroy();
            header("Location: ".SERVERURL."login/");
            exit;
        }
    }
}
