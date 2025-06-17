<?php


$alert = "";

if (isset($_POST['loginUserName'], $_POST['loginUserRole'], $_POST['loginUserPass'])) {
    require_once "./controllers/loginController.php";
    require_once "./models/loginModel.php";

    $role     = $_POST['loginUserRole'];
    $username = $_POST['loginUserName'];
    $password = $_POST['loginUserPass'];

    $log   = new loginController();
    $model = new loginModel();
    $pdo   = $model->connect();

    // 1) Obtenemos la fila
    $stmt = $pdo->prepare("
        SELECT Usuario, Clave, Tipo, Codigo, Privilegio, Genero 
          FROM cuenta
         WHERE Usuario = ?
    ");
    $stmt->execute([$username]);

    if ($stmt->rowCount() !== 1) {
        // si el usuario no existe o no lo encuentra
        $alert = $log->sweet_alert_single([
            "title" => "Acceso denegado",
            "text"  => "Usuario o contraseña incorrectos.",
            "type"  => "error"
        ]);
    } else {
        $row   = $stmt->fetch(PDO::FETCH_ASSOC);
        $hash  = $row['Clave'];
        $actual= $row['Tipo'];

        // 2) se verifica la contraseña
        if (substr($hash,0,4) === '$2y$') {
            $passOk = password_verify($password,$hash);
        } else {
            $passOk = ($hash === $model->encryption($password));
        }

        if (!$passOk) {
            // si ingresamos una contraseña que no es 
            $alert = $log->sweet_alert_single([
                "title" => "Acceso denegado",
                "text"  => "Usuario o contraseña incorrectos.",
                "type"  => "error"
            ]);
        } else {
            // 3) validamos el rol xd
            $isStudentRole = $role === 'Estudiante' && $actual !== 'Estudiante';
            $isTeacherRole = $role === 'Docente'    && !in_array($actual, ['Docente','Administrador']);

            if ($isStudentRole || $isTeacherRole) {
                $alert = $log->sweet_alert_single([
                    "title" => "Permisos insuficientes",
                    "text"  => "No tienes permisos para ingresar como {$role}.",
                    "type"  => "error"
                ]);
            } else {
                // 4) aquí delegamos al controlador y terminamos
                echo $log->login_session_start_controller();
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>  
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>AULA VIRTUAL CIP – Login</title>
<style>
    :root {
      --primary-bg:rgb(67, 52, 52);
      --primary-accent:   #D1B16E;
      --secondary-bg:     rgba(174,12,12,0.61);
      --text-light:       #FFFFFF;
      --hover-accent:     rgba(209,177,110,0.2);
    }
    /* Reset */
    * { box-sizing:border-box; margin:0; padding:0; }
    html, body { width:100%; height:100%; font-family:'RobotoCondensed',sans-serif; background:#111; }
    .containerLogin {
      display:flex; align-items:center; justify-content:center;
      height:100vh;
      background: url('<?php echo SERVERURL; ?>views/assets/img/index1.jpg') center/cover no-repeat;
    }

    /* Caja con neón animado */
    .box {
      --b:4px;
      position:relative;
      width:360px; max-width:90%;
      height:70px; /* teaser */
      background: var(--primary-bg);
      border-radius:20px;
      overflow:hidden;
      box-shadow: 0 0 15px var(--primary-accent), 0 0 30px var(--secondary-bg);
      transition:height .6s ease;
      margin:auto;
    }
    .box::before {
      content:"";
      position:absolute;
      top:calc(-1*var(--b)); left:calc(-1*var(--b));
      right:calc(-1*var(--b)); bottom:calc(-1*var(--b));
      background: linear-gradient(
        120deg,
        var(--primary-accent) 0%,
        var(--text-light)      25%,
        var(--secondary-bg)    50%,
        var(--primary-accent)  75%,
        var(--text-light)     100%
      );
      background-size:400% 400%;
      border-radius:calc(20px + var(--b));
      animation: neonBorder 4s ease infinite;
      z-index:0;
    }
    .box::after {
      content:""; position:absolute; inset:0;
      background: var(--primary-bg); border-radius:20px;
      z-index:1;
    }
    .box > * { position:relative; z-index:2; }

    @keyframes neonBorder {
      0%   { background-position:   0%   50%; }
      50%  { background-position: 100%   50%; }
      100% { background-position:   0%   50%; }
    }

    /* Header teaser / login */
    .login-header {
      text-align:center;
      color: var(--primary-accent);
      font-size:24px;
      padding:12px 0;
    }
    .teaser-text { display:block; }
    .login-text { display:none; }
    .login-logo {
      display:block; margin:0 auto 8px;
      width:64px; opacity:0;
      transition:opacity .4s ease;
    }

    /* Contenido oculto */
    .login-content {
      opacity:0; pointer-events:none;
      transition:opacity .4s ease;
      padding:0 20px 20px;
    }

    /* Al hover: expandir y revelar */
    .box:hover { height:420px; }
    .box:hover .login-logo {
      opacity:1; transition-delay:.5s;
    }
    .box:hover .teaser-text { display:none; }
    .box:hover .login-text  { display:block; }
    .box:hover .login-content {
      opacity:1; pointer-events:auto;
      transition-delay:.6s;
    }

/* Toggle de rol estilo “pill” */
.role-toggle {
  width: 240px;
  margin: 0 auto 16px;
}
.role-toggle input {
  display: none;
}
.role-toggle .labels {
  display: flex;
}
.role-toggle label {
  flex: 1;
  text-align: center;
  padding: 10px 0;
  border-radius: 20px;
  cursor: pointer;
  font-weight: bold;
  transition: background 0.3s, color 0.3s;
  /* estilo neutro inicial */
  background: rgba(255,255,255,0.1);
  color: var(--text-light);
}
/* redondear esquinas exteriores */
.role-toggle label:first-child  { border-top-left-radius:20px; border-bottom-left-radius:20px; }
.role-toggle label:last-child   { border-top-right-radius:20px; border-bottom-right-radius:20px; }

/* cuando “Estudiante” está marcado */
#role-estudiante:checked ~ .labels label[for="role-estudiante"] {
  background: var(--primary-accent);
  color: var(--primary-bg);
}

/* cuando “Docente” está marcado */
#role-docente:checked ~ .labels label[for="role-docente"] {
  background: var(--secondary-bg);
  color: var(--text-light);
}


    /* Inputs */
    .input-field {
      width:100%; padding:12px; margin-bottom:16px;
      border:2px solid rgba(255,255,255,0.3);
      border-radius:30px; background:transparent;
      color: var(--text-light); font-size:16px;
      transition:border-color .3s ease;
    }
    .input-field:focus {
      border-color: var(--primary-accent); outline:none;
    }

    /* Botón */
    .btn-login {
      width:100%; padding:12px; margin-top:8px;
      background: var(--primary-accent);
      color: var(--primary-bg); font-size:16px; font-weight:bold;
      border:none; border-radius:30px;
      cursor:pointer; transition:background .3s,transform .3s;
    }
    .btn-login:hover {
      background: var(--hover-accent); transform:scale(1.05);
    }

    /* Links */
    .login-links {
      display:flex; justify-content:space-between; margin-top:12px; font-size:14px;
    }
    .login-links a {
      color: var(--text-light); text-decoration:none; transition:color .3s ease;
    }
    .login-links a:hover { color: var(--primary-accent); }
    .signup-link { color: var(--primary-accent); }
    .signup-link:hover { color: var(--secondary-bg); }
  </style>
</head>
<body>
  <!-- 1) Si hubo alerta, la mostramos -->
  <?= $alert ?>

  <!-- 2) Luego siempre renderizamos el formulario -->
  <div class="containerLogin">
    <form action="" method="POST" class="box">
      <!-- HEADER -->
      <div class="login-header">
        <span class="teaser-text">AULA VIRTUAL CIP</span>
        <span class="login-text">LOGIN</span>
        <img
          src="<?php echo SERVERURL; ?>views/assets/img/LOGO_CIP.png"
          alt="Logo CIP"
          class="login-logo"
        >
      </div>

      <!-- FORMULARIO -->
      <div class="login-content">
        <!-- Toggle de rol -->
        <div class="role-toggle">
          <input type="radio" name="loginUserRole" id="role-estudiante" value="Estudiante"
            <?= (!isset($role) || $role==='Estudiante')?'checked':'' ?>>
          <input type="radio" name="loginUserRole" id="role-docente" value="Docente"
            <?= (isset($role) && $role==='Docente')?'checked':'' ?>>
          <div class="labels">
            <label for="role-estudiante">Estudiante</label>
            <label for="role-docente">Docente</label>
          </div>
        </div>

        <input
          id="loginUserName" name="loginUserName" type="text"
          class="input-field" placeholder="Usuario" required
          value="<?= isset($username)?htmlspecialchars($username):'' ?>"
        >
        <input
          id="loginUserPass" name="loginUserPass" type="password"
          class="input-field" placeholder="Contraseña" required
        >
        <button type="submit" class="btn-login">Iniciar Sesión</button>

        <div class="login-links">
          <a href="<?php echo SERVERURL; ?>forgot-password/" class="forgot">Forgot Password</a>
          <a href="<?php echo SERVERURL; ?>signup/" class="signup-link">Sign up</a>
        </div>
      </div>
    </form>
  </div>
</body>
</html>
