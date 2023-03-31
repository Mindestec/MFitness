<?php
/*
Author: Mindestec
Author URI: https://mindestec.com
License: GPLv2 o anterior
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: Mindestec Fitness
*/
//crear formularios de inicio y de registro

add_shortcode('mfitnessTabla', 'contenido');

function contenido(){
	if(is_user_logged_in()){
		ob_start();
		crear_ver_estadisticas();
		return ob_get_clean();
	}
	else{
		return "Inicia sesion para empezar tu camino.";
	}
}


function crear_ver_estadisticas(){
	global $wpdb;
	global $current_user;
	$user_id=absint($current_user->ID);
	$mprefix="mf";
	list($genero,$oposiciones)=obtener_gen_opos();
	$table_name=$wpdb->prefix.$mprefix;//.$user_id
	$tablas=$wpdb->get_var("SHOW TABLES LIKE '$table_name'");
	if($tablas!=$table_name){
		crear_tabla_mfuser($wpdb, $table_name);
	}
	mostrar_pruebas();
	dibujar_tabla($wpdb, $table_name, $user_id, $genero, $oposiciones);
}

function obtener_gen_opos(){
	global $current_user;
	$user_id=absint($current_user->ID);
	$genero = sanitize_text_field(get_user_meta( $user_id, 'genero', true ));
	$oposiciones=sanitize_text_field(get_user_meta($user_id, 'oposiciones', true));
	return [$genero, $oposiciones];
}

function obtener_edad_nivel(){
	global $current_user;
	$user_id=absint($current_user->ID);
	$fecha=sanitize_text_field(get_user_meta( $user_id, 'birth_date', true ));
	$edad= date("Y") - date("Y", strtotime($fecha));
	if(date("md")<date("md", strtotime($fecha))){
		$edad--;
	}
	
	$nivel=sanitize_text_field(get_user_meta( $user_id, 'nivel_ejercito', true ));
	return[$edad, $nivel];
}

// Crear nueva tabla de estadisticas para cada usuario *
function crear_tabla_mfuser($wpdb, $table_name){
	$charset_collate=$wpdb->get_charset_collate();
	$sql=$wpdb->prepare("CREATE TABLE IF NOT EXISTS $table_name(
		id int NOT NULL AUTO_INCREMENT,
		user_id bigint(20) unsigned NOT NULL,
		oposicion VARCHAR(25) NOT NULL,
		prueba1 double NOT NULL,
		prueba2 double NOT NULL,
		prueba3 double NOT NULL,
		prueba4 double,
		prueba5 double,
		prueba6 double,
		PRIMARY KEY (id),
		CONSTRAINT FK_user_id FOREIGN KEY(user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
	)$charset_collate;");
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}

//* Recolectar las pruebas introducidas de cada oposicion
function recolectar_pruebas($wpdb, $table_name, $user_id, $oposiciones){
	$result=array();
	if($oposiciones=="Policía Local"){
		$query = $wpdb->prepare("SELECT prueba1, prueba2, prueba3, prueba4, prueba5, prueba6 FROM `$table_name` WHERE oposicion = %s AND user_id = %d ORDER BY id DESC LIMIT 3", $oposiciones, $user_id);
    	$result = $wpdb->get_results($query);
		if(empty($result)){
			$result=array_fill(0, 6, "");
		}
	}
	elseif($oposiciones=="Policía Nacional" || $oposiciones=="Seguridad Privada"){
			$query = $wpdb->prepare("SELECT prueba1, prueba2, prueba3 FROM `$table_name` WHERE oposicion = %s AND user_id = %d ORDER BY id DESC LIMIT 3", $oposiciones, $user_id);
			$result=$wpdb->get_results($query);
		if(empty($result)){
			$result=array_fill(0, 3, "");
		}
	}
	elseif($oposiciones=="Ejercito" || $oposiciones=="Guardia Civil"){
		$query = $wpdb->prepare("SELECT prueba1, prueba2, prueba3, prueba4 FROM `$table_name` WHERE oposicion = %s AND user_id = %d ORDER BY id DESC LIMIT 3", $oposiciones, $user_id);
    	$result = $wpdb->get_results($query);
		if(empty($result)){
			$result=array_fill(0, 4, "");
		}
	}
	return $result;
}

// Dibuja una tabla de los resultados dependiendo de la longitud del resultado de la funcion recolectar_pruebas()
function dibujar_tabla($wpdb, $table_name, $user_id, $genero, $oposiciones){
	$result=recolectar_pruebas($wpdb, $table_name, $user_id, $oposiciones);
	list($edad, $nivel)=obtener_edad_nivel();
	if(empty($result[0])){
		$cantResult=count($result);
	}else{
		$cantResult=count(get_object_vars($result[0]));
	}
	
	echo '<table>';
	dibujar_form($wpdb, $table_name, $user_id);
		echo '<tr>';
		for($i=1;$i<=$cantResult;$i++){
			echo '<th style="border: 1px solid black">Prueba'.($i).'</th>';
		}
		if($oposiciones=='Policía Nacional'){
			echo '<th style="border: 1px solid black"> Puntos </th>';
		}
		echo '</tr>';

	include_once('baremos.php');
	$baremos=solicitar_baremos($genero, $oposiciones, $edad);
	if(!empty($result[0])){
		foreach($result as $results){
			echo '<tr>';
			if($oposiciones=='Policía Nacional'){
			for($j=1;$j<=$cantResult;$j++){
				
				echo '<td style="border: 1px solid black">'.$results->{"prueba".$j}.'</td>';
			}
			
				$puntos=puntos_Pol_Nac($results, $baremos);
				if($puntos>=15){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($puntos, 0, '.',',').'</td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($puntos, 0, '.',',').'</td>';
				}
			}
			else{
				for($j=1;$j<=$cantResult;$j++){
					$pruebas=apto_noApto($results, $baremos, $j, $oposiciones);
					echo $pruebas;
				}
			}
			
			echo '</tr>';
			
		}
	}
	else{
			echo 'Introduce tus primeras estadisticas';
	}
	
	echo '</table>';
	
}

function apto_noApto($results, $baremos, $j, $oposiciones){
	$prueba=$results->{"prueba".$j};
	if($oposiciones=='Policía Local'){
		if($j==1 || $j==5 || $j==6){
			if($prueba<=$baremos[$j-1]){
				echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
			}
			else{
				echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
			}
		}
		else{
			if($prueba>=$baremos[$j-1]){
				echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
			}
			else{
				echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
			}
		}
	}
	elseif($oposiciones=='Seguridad Privada'){
		if($j==4){
			if($prueba<=$baremos[$j-1]){
				echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
			}
			else{
				echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
			}
		}
		else{
			if($prueba>=$baremos[$j-1]){
				echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
			}
			else{
				echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
			}
		}
	}
	elseif($oposiciones=='Guardia Civil'){
		if($j==3){
			if($prueba>=$baremos[$j-1]){
				echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
			}
			else{
				echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
			}
		}
		else{
			if($prueba<=$baremos[$j-1]){
				echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
			}
			else{
				echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
			}
		}
	}
	elseif($oposiciones=='Ejercito'){
		
		if($prueba>=$baremos[$j-1]){
			echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
		}
		else{
			echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
		}
	}

}

function puntos_Pol_Nac($results, $baremos){
	$puntos=0;
	
	$u=$results->{"prueba2"};
	
	for($i=0; $i<count(get_object_vars($results));$i++){
		$indice_sumar=0;
		$valor_anterior=0;
		if($i==1){
			foreach($baremos[$i] as $indice=>$valor){
				if($u>=$valor or ($u<=$valor and $u>$valor_anterior)){
						$indice_sumar=$indice;
						$valor_anterior=$valor;
				}
			}
		}
		else{
			foreach($baremos[$i] as $indice=>$valor){
				if($results->{"prueba".($i+1)}<=$valor or ($results->{"prueba".($i+1)}>=$valor and $results->{"prueba".($i+1)}<$valor_anterior)){
					if($indice_sumar<$indice){
						$indice_sumar=$indice;
						$valor_anterior=$valor;
					}
					
				}
			}
		}
		$puntos+=$indice_sumar;
	}

	return $puntos;
}


function dibujar_form($wpdb, $table_name, $user_id){
	global $current_user;
	list($genero, $oposiciones)=obtener_gen_opos();
	$user_id=$current_user->ID;
	$result=recolectar_pruebas($wpdb, $table_name, $user_id, $oposiciones);
	if(empty($result[0])){
		$cantResult=count($result);
	}else{
		$cantResult=count(get_object_vars($result[0]));
	}
	
?>
	<tr>
		<form action="" method="POST"> <?php
		for($i=1;$i<=$cantResult;$i++){ ?>
			<td style="border: 1px solid black"><input type="number" name="prueba<?php echo $i?>" min="0.01" max="206" step="0.01" pattern="[0-9]+(\.[0-9]{1,2})?" required></td>
		<?php } ?>
		<td style="border: 1px solid black"><input type="submit" name="submit" value="enviar"></td>
		</form>
	</tr>
	<?php
	
	if (isset($_POST['submit'])) {
		for($i=1; $i<=$cantResult; $i++){
			$prueba=sanitize_text_field($_POST['prueba'.$i]);
			if(empty($prueba)){
				echo "Debe ingresar su marca en la prueba $i";
				return;
			}
			
			$pruebas["prueba".$i]=$prueba;
			
		}
		include('insert.php');
		insertar_datos($wpdb, $table_name, $user_id, $oposiciones, $pruebas);
	}
}

function mostrar_pruebas(){
	list($genero, $oposiciones)=obtener_gen_opos();
	list($edad, $nivel)=obtener_edad_nivel();
	
	if($oposiciones=='Policía Nacional' || $oposiciones=='Policia Nacional'){
		echo 'Prueba 1: Circuito de agilidad (Medición en segundos). <br>';
		if($genero=='Hombre'){
			echo 'Prueba 2: Dominadas.<br>';
		} else {
			echo 'Prueba 2: Suspension en barra (Medición en segundos).<br>';	
		}
		echo 'Prueba 3: Carrera de 1000 metros (Medición en minutos).<br>';
	}
	
	elseif($oposiciones=='Policía Local' || $oposiciones=='Policia Local'){
		echo 'Prueba 1: Prueba de velocidad de 50 metros (Medición en segundos).<br>';
		if($genero=='Hombre'){
			echo 'Prueba 2: Flexiones de brazo en suspension pura.<br>';
		} else{
			echo 'Prueba 2: Lanzamiento de balon medicial de 3Kg (Medición en metros).<br>';
		}
		echo 'Prueba 3: Prueba de flexibilidad (Medición en centímetros).<br>';
		echo 'Prueba 4: Salto vertical (Medición en centímetros).<br>';
		echo 'Prueba 5: Carrera de 1000m (Medición en minutos).<br>';
		echo 'Prueba 6: Prueba de natación de 25m (Opcional).<br>';
		
	}
	
	elseif($oposiciones=='Seguridad Privada'){
		if($edad>17 && $edad<40){
			if($genero=='Hombre'){
				echo 'Prueba 1: Flexiones.<br>';
			}
			else{
				echo 'Prueba 1: Balon Medicinal (Medición en metros).<br>';
			}
		}
		else{
			echo 'Prueba 1: Balon Medicinal (Medición en metros).<br>';
		}
		
		echo 'Prueba 2: Salto vertical (Medición en centimetros).<br>';
		echo 'Prueba 3: Carrera 400m (Medición en minutos).<br>';
	}
	
	elseif($oposiciones=='Guardia Civil'){
		echo 'Prueba 1: Velocidad de 60m (Medición en segundos).<br>';
		echo 'Prueba 2: Resistencia de 2Km (Medición en minutos).<br>';
		echo 'Prueba 3: Flexiones de brazo en suspension pura.<br>';
		echo 'Prueba 4: Soltura acuatica (Medición en segundos).<br>';
	}
	
	elseif($oposiciones=='Ejercito' || $oposiciones=='Ejército'){
		echo 'Prueba 1: Salto de longitud sin carrera (Medición en centimetros).<br>';
		echo 'Prueba 2: Abdominales.<br>';
		echo 'Prueba 3: Flexo-extensiones de brazos.<br>';
		echo 'Prueba 4: Carrera de ida y vuelta de 20m (Medición en periodos).<br>';
	}

}