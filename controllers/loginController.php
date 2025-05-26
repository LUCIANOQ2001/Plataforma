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
        // 1) Sanitizar entradas
        $userNameRaw = $_POST['loginUserName']  ?? '';
        $userPassRaw = $_POST['loginUserPass']  ?? '';

        $userName = self::clean_string($userNameRaw);
        $userPass = trim($userPassRaw);

        // 2) Buscamos al usuario solo por su nombre
        $pdo = self::connect(); // asumiendo que loginModel provee connect()
        $stmt = $pdo->prepare("SELECT Usuario, Clave, Tipo, Codigo, Privilegio, Genero 
                                 FROM cuenta
                                WHERE Usuario = ?");
        $stmt->execute([$userName]);

        if($stmt->rowCount() === 1){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $hash = $row['Clave'];

            // 3) Verificamos la contraseña
            $isValid = false;
            if(substr($hash, 0, 4) === '$2y$') {
                // es bcrypt
                $isValid = password_verify($userPass, $hash);
            } else {
                // tu cifrado legacy
                $isValid = ($hash === self::encryption($userPass));
            }

            if($isValid){
                // 4) Creamos la sesión
                session_start();
                $_SESSION['userName']      = $row['Usuario'];
                $_SESSION['userType']      = $row['Tipo'];
                $_SESSION['userKey']       = $row['Codigo'];
                $_SESSION['userPrivilege'] = $row['Privilegio'];
                $_SESSION['userToken']     = md5(uniqid(mt_rand(), true));

                // 5) Avatar y URL según tipo
                switch($row['Tipo']){
                    case 'Administrador':
                        $_SESSION['Avatar'] = 'avatar-chef.png';
                        $url = SERVERURL . 'dashboard/';
                        break;
                    case 'Docente':
                        $_SESSION['Avatar'] = ($row['Genero']==='Masculino')
                                             ? 'avatar-user-male.png'
                                             : 'avatar-user-female.png';
                        $url = SERVERURL . 'dashboard/';
                        break;
                    case 'Estudiante':
                        $_SESSION['Avatar'] = ($row['Genero']==='Masculino')
                                             ? 'avatar-user-male.png'
                                             : 'avatar-user-female.png';
                        $url = SERVERURL . 'home/';
                        break;
                    default:
                        $_SESSION['Avatar'] = 'avatar-user-male.png';
                        $url = SERVERURL . 'login/';
                }

                return '<script>window.location="' . $url . '";</script>';
            } else {
                // contraseña incorrecta
                $dataAlert = [
                    "title" => "Acceso denegado",
                    "text"  => "Usuario o contraseña incorrectos.",
                    "type"  => "error"
                ];
                return self::sweet_alert_single($dataAlert);
            }

        } else {
            // usuario no existe
            $dataAlert = [
                "title" => "Acceso denegado",
                "text"  => "Usuario o contraseña incorrectos.",
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
