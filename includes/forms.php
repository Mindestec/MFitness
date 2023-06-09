<?php
/*
Author: Mindestec
Author URI: https://mindestec.com
License: GPLv2 o anterior
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: Mindestec Fitness
*/
//crear formularios de inicio y de registro
// Funcion Inicial
add_shortcode('mfitnessTabla', 'mdtf_Contenido');

function mdtf_Contenido(){
	if(is_user_logged_in()){
		ob_start();
		mdtf_CrearVerEstadisticas();
		return ob_get_clean();
	}
	else{
		return "Inicia sesion para empezar tu camino.";
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------------

//Funcion Principal
function mdtf_CrearVerEstadisticas(){
	global $wpdb;
	global $current_user;
	$user_id=absint($current_user->ID);
	$mprefix="mdtf";
	list($genero,$oposiciones)=mdtf_ObtGenOpos();
	$table_name=$wpdb->prefix.$mprefix;//.$user_id
	$tablas=$wpdb->get_var("SHOW TABLES LIKE '$table_name'");
	if($tablas!=$table_name){
		mdtf_CrearTablaUser($wpdb, $table_name);
	}
	mdtf_MostrarPruebas();
	mdtf_DibujarTabla($wpdb, $table_name, $user_id, $genero, $oposiciones);
}

//------------------------------------------------------------------------------------------------------------------------------------------------------

// Funcion para obtener el genero y la oposicion seleccionada por el usuario
function mdtf_ObtGenOpos(){
	global $current_user;
	$user_id=absint($current_user->ID);
	$genero = sanitize_text_field(get_user_meta( $user_id, 'genero', true ));
	$oposiciones=sanitize_text_field(get_user_meta($user_id, 'oposiciones', true));
	return [$genero, $oposiciones];
}

//------------------------------------------------------------------------------------------------------------------------------------------------------

// Funcion para obtener la edad y el nivel del Ejercito seleccionada por el usuario
function mdtf_ObtEdadNivel(){
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

//------------------------------------------------------------------------------------------------------------------------------------------------------

// Crear nueva tabla de estadisticas para cada usuario 
function mdtf_CrearTablaUser($wpdb, $table_name){
	$charset_collate=$wpdb->get_charset_collate();
	$sql=$wpdb->prepare("CREATE TABLE IF NOT EXISTS $table_name(
		id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id bigint(20) UNSIGNED NOT NULL,
		oposicion VARCHAR(25) NOT NULL,
		oposicion_id int(11) UNSIGNED NOT NULL,
		prueba1 double NOT NULL,
		prueba2 double NOT NULL,
		prueba3 double NOT NULL,
		prueba4 double,
		prueba5 double,
		prueba6 double,
		PRIMARY KEY (id),
		UNIQUE KEY unique_oposicion(oposicion, oposicion_id),
		CONSTRAINT FK_user_id FOREIGN KEY(user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
	)$charset_collate;");
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}

// Recolectar las pruebas introducidas de cada oposicion
function mdtf_RecolectarPruebas($wpdb, $table_name, $user_id, $oposiciones){
	$result=array();
	if($oposiciones=="Policía Local"){
		$query = $wpdb->prepare("SELECT prueba1, prueba2, prueba3, prueba4, prueba5, prueba6 FROM `$table_name` WHERE oposicion = %s AND user_id = %d ORDER BY oposicion_id DESC LIMIT 3", $oposiciones, $user_id);
    	$result = $wpdb->get_results($query);
		if(empty($result)){
			$result=array_fill(0, 6, "");
		}
	}
	elseif($oposiciones=="Policía Nacional" || $oposiciones=="Seguridad Privada"){
			$query = $wpdb->prepare("SELECT prueba1, prueba2, prueba3 FROM `$table_name` WHERE oposicion = %s AND user_id = %d ORDER BY oposicion_id DESC LIMIT 3", $oposiciones, $user_id);
			$result=$wpdb->get_results($query);
		if(empty($result)){
			$result=array_fill(0, 3, "");
		}
	}
	elseif($oposiciones=="Ejercito" || $oposiciones=="Guardia Civil"){
		$query = $wpdb->prepare("SELECT prueba1, prueba2, prueba3, prueba4 FROM `$table_name` WHERE oposicion = %s AND user_id = %d ORDER BY oposicion_id DESC LIMIT 3", $oposiciones, $user_id);
    	$result = $wpdb->get_results($query);
		if(empty($result)){
			$result=array_fill(0, 4, "");
		}
	}
	return $result;
}

//------------------------------------------------------------------------------------------------------------------------------------------------------

// Dibuja una tabla de los resultados dependiendo de la longitud del resultado de la funcion recolectar_pruebas()
function mdtf_DibujarTabla($wpdb, $table_name, $user_id, $genero, $oposiciones){
	$result=mdtf_RecolectarPruebas($wpdb, $table_name, $user_id, $oposiciones);
	list($edad, $nivel)=mdtf_ObtEdadNivel();
	if(empty($result[0])){
		$cantResult=count($result);
	}else{
		$cantResult=count(get_object_vars($result[0]));
	}
	
	echo '<table>';
	mdtf_DibujarForm($wpdb, $table_name, $user_id);
		echo '<tr>';
		for($i=1;$i<=$cantResult;$i++){
			echo '<th style="border: 1px solid black; text-align:center;">Prueba'.intval($i).'</th>';
		}
		if($oposiciones=='Policía Nacional'){
			echo '<th style="border: 1px solid black; text-align:center;; text-align:center;"> Puntos </th>';
		}
		echo '</tr>';

	// Recolectar baremos
	include_once('baremos.php');
	$baremos=mdtf_SolicitarBaremos($genero, $oposiciones, $edad);
	// Dibujar tabla
	if(!empty($result[0])){
		$contResult=1;
		$primResult=array();
		$ultimaKey=array_key_last($result);
		foreach($result as $key => $results){
			echo '<tr>';
			if($oposiciones=='Policía Nacional'){
			$primResulQuery = $wpdb->prepare("SELECT prueba1, prueba2, prueba3 FROM wp_mdtf WHERE oposicion = %s AND user_id = %d AND oposicion_id = 1", $oposiciones, $user_id);
			$primResult=$wpdb->get_results($primResulQuery);
				
			// Ver si solo hay un campo o hay mas
			$cantidadResultQuery = $wpdb->prepare("SELECT prueba1, prueba2, prueba3 FROM wp_mdtf WHERE oposicion = %s AND user_id = %d ORDER BY oposicion_id ASC LIMIT 2", $oposiciones, $user_id);
			$cantidadResult=$wpdb->get_results($cantidadResultQuery);
			
			if(!empty($cantidadResult[1])){
				for($j=1;$j<=$cantResult;$j++){
					if($contResult==1){
						if($j===1 || $j===3){
							if($results->{"prueba".$j}<$primResult[0]->{"prueba".$j}){
								echo '<td style="border: 1px solid black; text-align:center;">'.esc_attr($results->{"prueba".intval($j)}).'<i class="fa fa-arrow-up" style="color:green; margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
							}
							elseif($results->{"prueba".$j}>$primResult[0]->{"prueba".$j}){
								echo '<td style="border: 1px solid black; text-align:center;">'.esc_attr($results->{"prueba".intval($j)}).'<i class="fa fa-arrow-down" style="color:red; margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
							}
							else{
								echo '<td style="border: 1px solid black; text-align:center;">'.esc_attr($results->{"prueba".intval($j)}).'<i class="fa fa-equals" style="color: orange; margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
							}
						}
						else{
							if($results->{"prueba".$j}>$primResult[0]->{"prueba".$j}){
							echo '<td style="border: 1px solid black; text-align:center;">'.esc_attr($results->{"prueba".intval($j)}).'<i class="fa fa-arrow-up" style="color:green; margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
						}
						elseif($results->{"prueba".$j}<$primResult[0]->{"prueba".$j}){
							echo '<td style="border: 1px solid black; text-align:center;">'.esc_attr($results->{"prueba".intval($j)}).'<i class="fa fa-arrow-down" style="color:red; margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
						}
						else{
							echo '<td style="border: 1px solid black; text-align:center;">'.esc_attr($results->{"prueba".intval($j)}).'<i class="fa fa-equals" style="color: orange; margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
						}
						}
						
					}
					else{
						echo '<td style="border: 1px solid black; text-align:center;">'.esc_attr($results->{"prueba".intval($j)}).'</td>';
					}

				}
				$puntos = mdtf_PuntosPolNac($results, $baremos);
				$primPuntos = mdtf_PuntosPolNac($primResult[0], $baremos);
				if($puntos>=15){
					if($contResult===1){
						if($puntos>$primPuntos){
							echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($puntos, 0, '.',',').'<i class="fa fa-arrow-up" style="color:green; margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
						}
						elseif($puntos<$primPuntos){
							echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($puntos, 0, '.',',').'<i class="fa fa-arrow-down" style="color:red; margin-left:10%; background-color:white;border-radius:99px;padding:2px;"></i></td>';
						}
						else{
							echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($puntos, 0, '.',',').'<i class="fa fa-equals" style="color:orange; margin-left:10%;background-color:white;border-radius:99px; padding:2px;"></i></td>';
						}

					}
					else{
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($puntos, 0, '.',',').'</td>';
					}
				}
				else{
					if($contResult===1){if($puntos>$primPuntos){
							echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($puntos, 0, '.',',').'<i class="fa fa-arrow-up" style="color:green; margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
						}
						elseif($puntos<$primPuntos){
							echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($puntos, 0, '.',',').'<i class="fa fa-arrow-down" style="color:red; margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
						}
						else{
							echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($puntos, 0, '.',',').'</td>';
						}}
					else{
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($puntos, 0, '.',',').'</td>';
					}
				}
				}
				else{ //si es el primer registro
					for($j=1;$j<=$cantResult;$j++){
						echo '<td style="border: 1px solid black; text-align:center;">'.esc_attr($results->{"prueba".intval($j)}).'</td>';
				}
				$puntos = mdtf_PuntosPolNac($results, $baremos);
				if($puntos>=15){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($puntos, 0, '.',',').'</td>';
						
				}
				else{
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($puntos, 0, '.',',').'</td>';	
				}
					echo "Esta vacio";
				} // termina el primer registro
}
				
			
			
			else{
				for($j=1;$j<=$cantResult;$j++){
					$pruebas=mdtf_AptoNoApto($results, $baremos, $j, $oposiciones, $wpdb, $user_id, $key, $ultimaKey);
					echo esc_html($pruebas);
				}
				
			}
			$contResult++;
			echo '</tr>';
		}
		}
	
	else{
			echo 'Introduce tus primeras estadisticas';
	}
	
	echo '</table>';
}

//------------------------------------------------------------------------------------------------------------------------------------------------------

// Funcion para determinar si cada prueba es apta (Aprobado, fondo verde) o no apto (Reprobado, fondo rojo)
function mdtf_AptoNoApto($results, $baremos, $j, $oposiciones, $wpdb, $user_id, $key, $ultimaKey){
	$prueba=$results->{"prueba".$j};
	$primResulQuery = $wpdb->prepare("SELECT prueba1 FROM wp_mdtf WHERE oposicion = %s AND user_id = %d ORDER BY oposicion_id ASC LIMIT 2", $oposiciones, $user_id);
	$primResult=$wpdb->get_results($primResulQuery);
	// Si hay mas de un registro y solo lo hace en el ultimo registro introducido
	if(count($primResult)>1 && $key===0){
	  if($oposiciones=='Policía Local'){
		  // Recolectar primera prueba introducida
		$primResulQuery = $wpdb->prepare("SELECT prueba1, prueba2, prueba3, prueba4, prueba5, prueba6 FROM wp_mdtf WHERE oposicion = %s AND user_id = %d AND oposicion_id = 1", $oposiciones, $user_id);
		$primResult=$wpdb->get_row($primResulQuery);
		$primPrueba=$primResult->{"prueba".$j};
		if($j==1 || $j==5 || $j==6){
			if($prueba<=$baremos[$j-1]){
				if($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				
			}
			else{
				if($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
			}
		}
		else{
			if($prueba>=$baremos[$j-1]){
				if($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
			}
			else{
				if($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
			}
		}
	}
	
	elseif($oposiciones=='Seguridad Privada'){
		$primResulQuery = $wpdb->prepare("SELECT prueba1, prueba2, prueba3, prueba4 FROM wp_mdtf WHERE oposicion = %s AND user_id = %d AND oposicion_id = 1", $oposiciones, $user_id);
		$primResult=$wpdb->get_row($primResulQuery);
		$primPrueba=$primResult->{"prueba".$j};
		if($j==4){
			if($prueba<=$baremos[$j-1]){
				if($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
			}
			else{
				if($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
			}
		}
		else{
			if($prueba>=$baremos[$j-1]){
				if($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
			}
			else{
				if($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
			}
		}
	}
	
	elseif($oposiciones=='Guardia Civil'){
		$primResulQuery = $wpdb->prepare("SELECT prueba1, prueba2, prueba3, prueba4 FROM wp_mdtf WHERE oposicion = %s AND user_id = %d AND oposicion_id = 1", $oposiciones, $user_id);
		$primResult=$wpdb->get_row($primResulQuery);
		$primPrueba=$primResult->{"prueba".$j};
		if($j==3){
			if($prueba>=$baremos[$j-1]){
				if($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
			}
			else{
				if($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
			}
		}
		else{
			if($prueba<=$baremos[$j-1]){
				if($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
			}
			else{
				if($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
			}
		}
	}
	
	elseif($oposiciones=='Ejercito'){
		$primResulQuery = $wpdb->prepare("SELECT prueba1, prueba2, prueba3, prueba4 FROM wp_mdtf WHERE oposicion = %s AND user_id = %d AND oposicion_id = 1", $oposiciones, $user_id);
		$primResult=$wpdb->get_row($primResulQuery);
		$primPrueba=$primResult->{"prueba".$j};
		
		if($j!==4){		
			if($prueba>=$baremos[$j-1]){
				if($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
			}
			else{
				if($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
			}
		}
		else{
			if($prueba<=$baremos[$j-1]){
				if($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
			}
			else{
				if($prueba<$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-up" style="color: green;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				elseif($prueba>$primPrueba){
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-arrow-down" style="color: red;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
				else{
					echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'<i class="fa fa-equals" style="color: orange;margin-left:10%;background-color:white;border-radius:99px;padding:2px;"></i></td>';
				}
			}
		}
		}
	}
	// Si es el primer registro introducido y solo en los campos que no son del ultimo registro
	else{
		if($oposiciones=='Policía Local'){
			$primResulQuery = $wpdb->prepare("SELECT prueba1, prueba2, prueba3, prueba4, prueba5, prueba6 FROM wp_mdtf WHERE oposicion = %s AND user_id = %d AND oposicion_id = 1", $oposiciones, $user_id);
			$primResult=$wpdb->get_row($primResulQuery);
			$primPrueba=$primResult->{"prueba".$j};
			if($j==1 || $j==5 || $j==6){
				$pruebaBaremo=$baremos[$j-1];
				if($prueba<=$pruebaBaremo){
					if($prueba>$primPrueba){
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					elseif($prueba<$primPrueba){
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					else{
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
				
				}
				else{
					if($prueba>$primPrueba){
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					elseif($prueba<$primPrueba){
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					else{
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
				}
			}
			else{
				if($prueba>=$baremos[$j-1]){
					if($prueba>$primPrueba){
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					elseif($prueba<$primPrueba){
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					else{
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
				}
				else{
					if($prueba>$primPrueba){
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					elseif($prueba<$primPrueba){
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					else{
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
				}
			}
		}
	
		elseif($oposiciones=='Seguridad Privada'){
			$primResulQuery = $wpdb->prepare("SELECT prueba1, prueba2, prueba3, prueba4 FROM wp_mdtf WHERE oposicion = %s AND user_id = %d AND oposicion_id = 1", $oposiciones, $user_id);
			$primResult=$wpdb->get_row($primResulQuery);
			$primPrueba=$primResult->{"prueba".$j};
			if($j==4){
				if($prueba<=$baremos[$j-1]){
					if($prueba>$primPrueba){
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					elseif($prueba<$primPrueba){
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					else{
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
				}
				else{
					if($prueba>$primPrueba){
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					elseif($prueba<$primPrueba){
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					else{
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
				}
			}
			else{
				if($prueba>=$baremos[$j-1]){
					if($prueba>$primPrueba){
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					elseif($prueba<$primPrueba){
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					else{
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
				}
				else{
					if($prueba>$primPrueba){
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					elseif($prueba<$primPrueba){
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					else{
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
				}
			}
		}

		elseif($oposiciones=='Guardia Civil'){
			$primResulQuery = $wpdb->prepare("SELECT prueba1, prueba2, prueba3, prueba4 FROM wp_mdtf WHERE oposicion = %s AND user_id = %d AND oposicion_id = 1", $oposiciones, $user_id);
			$primResult=$wpdb->get_row($primResulQuery);
			$primPrueba=$primResult->{"prueba".$j};
			if($j==3){
				if($prueba>=$baremos[$j-1]){
					if($prueba>$primPrueba){
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					elseif($prueba<$primPrueba){
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					else{
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
				}
				else{
					if($prueba>$primPrueba){
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					elseif($prueba<$primPrueba){
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					else{
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
				}
			}
			else{
				if($prueba<=$baremos[$j-1]){
					if($prueba>$primPrueba){
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					elseif($prueba<$primPrueba){
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					else{
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
				}
				else{
					if($prueba>$primPrueba){
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					elseif($prueba<$primPrueba){
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					else{
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
				}
			}
		}
	
		elseif($oposiciones=='Ejercito'){
			$primResulQuery = $wpdb->prepare("SELECT prueba1, prueba2, prueba3, prueba4 FROM wp_mdtf WHERE oposicion = %s AND user_id = %d AND oposicion_id = 1", $oposiciones, $user_id);
			$primResult=$wpdb->get_row($primResulQuery);
			$primPrueba=$primResult->{"prueba".$j};
			if($prueba>=$baremos[$j-1]){
				if($prueba>$primPrueba){
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					elseif($prueba<$primPrueba){
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					else{
						echo '<td style="border: 1px solid black; background-color: green; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
			}
			else{
				if($prueba>$primPrueba){
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					elseif($prueba<$primPrueba){
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
					else{
						echo '<td style="border: 1px solid black; background-color: red; color: white;">'.number_format($prueba, 2, '.',',').'</td>';
					}
			}
		}
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------------

// Carcular los puntos obtenidos dependiendo de las pruebas introducidas
function mdtf_PuntosPolNac($results, $baremos){
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

//------------------------------------------------------------------------------------------------------------------------------------------------------

// Dibujar formulario de insercion de los valores sacados en las pruebas fisicas
function mdtf_DibujarForm($wpdb, $table_name, $user_id){
	global $current_user;
	list($genero, $oposiciones)=mdtf_ObtGenOpos();
	$user_id=$current_user->ID;
	$result=mdtf_RecolectarPruebas($wpdb, $table_name, $user_id, $oposiciones);
	if(empty($result[0])){
		$cantResult=count($result);
	}else{
		$cantResult=count(get_object_vars($result[0]));
	}
	
?>
	<tr>
		<form action="" method="POST"> <?php
		for($i=1;$i<=$cantResult;$i++){ ?>
			<td style="border: 1px solid black"><input type="number" name="prueba<?php echo intval($i)?>" min="0.01" max="206" step="0.01" pattern="[0-9]+(\.[0-9]{1,2})?" required></td>
		<?php } ?>
		<td style="border: 1px solid black"><input type="submit" name="submit" value="enviar"></td>
		</form>
	</tr>
	<?php
	
	if (isset($_POST['submit'])) {
		for($i=1; $i<=$cantResult; $i++){
			$prueba=sanitize_text_field($_POST['prueba'.$i]);
			if(empty($prueba)){
				echo "Debe ingresar su marca en la prueba ".intval($i);
				return;
			}
			
			$pruebas["prueba".$i]=$prueba;
			
		}
		include('insert.php');
		mdtf_InsertarDatos($wpdb, $table_name, $user_id, $oposiciones, $pruebas);
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------------

// Mostrar el tipo de prueba que es de forma grafica
function mdtf_MostrarPruebas(){
	list($genero, $oposiciones)=mdtf_ObtGenOpos();
	list($edad, $nivel)=mdtf_ObtEdadNivel();
	
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