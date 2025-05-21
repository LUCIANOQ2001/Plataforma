<?php
	if($actionsRequired){
		require_once "../models/adminModel.php";
	}else{ 
		require_once "./models/adminModel.php";
	}

	class adminController extends adminModel{

		/*----------  Add Admin/Docente Controller  ----------*/
		public function add_admin_controller(){
			// 1) Limpiar entradas
			$name      = self::clean_string($_POST['name']);
			$lastname  = self::clean_string($_POST['lastname']);
			$gender    = self::clean_string($_POST['gender']);
			$username  = self::clean_string($_POST['username']);
			$password1 = $_POST['password1'];
			$password2 = $_POST['password2'];
			$type      = self::clean_string($_POST['type'] ?? 'Administrador');

			// 2) Validar contraseñas
			if(trim($password1) === '' || trim($password2) === ''){
				return self::sweet_alert_single([
					"title" => "Error",
					"text"  => "Debes llenar ambos campos de contraseña",
					"type"  => "error"
				]);
			}
			if($password1 !== $password2){
				return self::sweet_alert_single([
					"title" => "Error",
					"text"  => "Las contraseñas no coinciden",
					"type"  => "error"
				]);
			}

			// 3) Validar tipo
			if(!in_array($type, ['Administrador','Docente'])){
				return self::sweet_alert_single([
					"title" => "Error",
					"text"  => "Tipo de usuario inválido",
					"type"  => "error"
				]);
			}

			// 4) Revisar que el usuario no exista
			$exists = self::execute_single_query("SELECT Usuario FROM cuenta WHERE Usuario='$username'");
			if($exists->rowCount() > 0){
				return self::sweet_alert_single([
					"title" => "Error",
					"text"  => "El nombre de usuario ya está registrado",
					"type"  => "error"
				]);
			}

			// 5) Generar correlativo y código
			$count = self::execute_single_query("SELECT id FROM cuenta")->rowCount() + 1;
			$code  = self::generate_code('AC', 7, $count);

			// 6) Encriptar contraseña
			$encrypted = self::encryption($password1);

			// 7) Determinar privilegio
			$privilegio = $type === 'Administrador' ? 1 : 2;

			// 8) Preparar arrays de inserción
			$dataAccount = [
				"Privilegio" => $privilegio,
				"Usuario"    => $username,
				"Clave"      => $encrypted,
				"Tipo"       => $type,
				"Genero"     => $gender,
				"Codigo"     => $code
			];
			$dataAdmin = [
				"Codigo"    => $code,
				"Nombres"   => $name,
				"Apellidos" => $lastname
			];

			// 9) Guardar en ambas tablas
			if(self::save_account($dataAccount) && self::add_admin_model($dataAdmin)){
				return self::sweet_alert_single([
					"title" => "$type registrado!",
					"text"  => "El $type se registró con éxito en el sistema",
					"type"  => "success"
				]);
			}else{
				return self::sweet_alert_single([
					"title" => "Error inesperado",
					"text"  => "No se pudo registrar el $type. Intenta de nuevo.",
					"type"  => "error"
				]);
			}
		}


		/*----------  Data Admin Controller  ----------*/
		public function data_admin_controller($Type,$Code){
			$Type=self::clean_string($Type);
			$Code=self::clean_string($Code);

			$data=[
				"Tipo"=>$Type,
				"Codigo"=>$Code
			];

			if($admindata=self::data_admin_model($data)){
				return $admindata;
			}else{
				return self::sweet_alert_single([
					"title"=>"¡Ocurrió un error!",
					"text"=>"No pudimos obtener datos del administrador",
					"type"=>"error"
				]);
			}
		}

		/*----------  Pagination Admin Controller  ----------*/
		public function pagination_admin_controller($Pagina,$Registros){
			$Pagina    = max(1, (int)$Pagina);
			$Registros = max(1, (int)$Registros);
			$Inicio    = ($Pagina - 1) * $Registros;

			$Datos = self::execute_single_query("
				SELECT * FROM admin WHERE Codigo!='AC03576381'
				ORDER BY Nombres ASC LIMIT $Inicio,$Registros
			")->fetchAll();

			$Total = self::execute_single_query("SELECT * FROM admin")->rowCount();
			$Npaginas = ceil($Total/$Registros);

			$table = '
			<table class="table text-center">
				<thead>
					<tr>
						<th>#</th><th>Nombres</th><th>Apellidos</th>
						<th>A. Datos</th><th>A. Cuenta</th><th>Eliminar</th>
					</tr>
				</thead><tbody>
			';

			if($Total > 0){
				$nt = $Inicio + 1;
				foreach($Datos as $rows){
					$table .= "
					<tr>
						<td>{$nt}</td>
						<td>{$rows['Nombres']}</td>
						<td>{$rows['Apellidos']}</td>
						<td>
						  <a href='".SERVERURL."admininfo/{$rows['Codigo']}/' class='btn btn-success btn-xs'>
						    <i class='zmdi zmdi-refresh'></i>
						  </a>
						</td>
						<td>
						  <a href='".SERVERURL."account/{$rows['Codigo']}/' class='btn btn-success btn-xs'>
						    <i class='zmdi zmdi-refresh'></i>
						  </a>
						</td>
						<td>
						  <a href='#!' class='btn btn-danger btn-xs btnFormsAjax' data-action='delete' data-id='del-{$rows['Codigo']}'>
						    <i class='zmdi zmdi-delete'></i>
						  </a>
						  <form id='del-{$rows['Codigo']}' method='POST'>
						    <input type='hidden' name='adminCode' value='{$rows['Codigo']}'>
						  </form>
						</td>
					</tr>";
					$nt++;
				}
			}else{
				$table .= "<tr><td colspan='6'>No hay registros</td></tr>";
			}

			$table .= '</tbody></table>';

			// Paginación
			if($Total > $Registros){
				$table .= "<nav class='text-center'><ul class='pagination pagination-sm'>";
				// Anterior
				$table .= $Pagina > 1
					? "<li><a href='".SERVERURL."adminlist/".($Pagina-1)."/'>«</a></li>"
					: "<li class='disabled'><a>«</a></li>";
				// Números
				for($i=1;$i<=$Npaginas;$i++){
					$table .= $i==$Pagina
						? "<li class='active'><a>{$i}</a></li>"
						: "<li><a href='".SERVERURL."adminlist/{$i}/'>{$i}</a></li>";
				}
				// Siguiente
				$table .= $Pagina < $Npaginas
					? "<li><a href='".SERVERURL."adminlist/".($Pagina+1)."/'>»</a></li>"
					: "<li class='disabled'><a>»</a></li>";
				$table .= '</ul></nav>';
			}

			return $table;
		}


		/*----------  Delete Admin Controller  ----------*/
		public function delete_admin_controller($code){
			$code=self::clean_string($code);

			if(self::delete_account($code) && self::delete_admin_model($code)){
				return self::sweet_alert_single([
					"title"=>"¡Eliminado!",
					"text"=>"Administrador eliminado con éxito",
					"type"=>"success"
				]);
			}else{
				return self::sweet_alert_single([
					"title"=>"Error",
					"text"=>"No pudimos eliminar al administrador",
					"type"=>"error"
				]);
			}
		}


		/*----------  Update Admin Controller  ----------*/
		public function update_admin_controller(){
			$code     = self::clean_string($_POST['code']);
			$name     = self::clean_string($_POST['name']);
			$lastname = self::clean_string($_POST['lastname']);

			$data = [
				"Codigo"    => $code,
				"Nombres"   => $name,
				"Apellidos" => $lastname
			];

			if(self::update_admin_model($data)){
				return self::sweet_alert_single([
					"title"=>"¡Actualizado!",
					"text"=>"Datos del administrador actualizados",
					"type"=>"success"
				]);
			}else{
				return self::sweet_alert_single([
					"title"=>"Error",
					"text"=>"No pudimos actualizar los datos",
					"type"=>"error"
				]);
			}
		}
	}
