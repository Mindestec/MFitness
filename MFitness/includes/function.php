<?php
/*
Author: Mindestec
Author URI: https://mindestec.com
License: GPLv2 o anterior
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: Mindestec Fitness*/


add_shortcode('mfitnessGraf', 'dibujar_grafico');

function dibujar_grafico(){
	
	global $wpdb;
	global $current_user;
	$user_id=absint($current_user->ID);
	$mprefix='mf';
	$table_name=$wpdb->prefix.$mprefix;
	include_once('forms.php');
	list($genero,$oposiciones)=obtener_gen_opos();
	$result=recolectar_pruebas($wpdb, $table_name, $user_id, $oposiciones);
	
	if(empty($result[0])){
		$cantResult=count($result);
	}else{
		$cantResult=count(get_object_vars($result[0]));
	
	$labels=array();
	for($i=1; $i<=$cantResult; $i++){
		$labels[]="Prueba ".$i;
	}
		
	$primerData = array();
	$segData = array();
	$primerResult=array_shift($result);
	$maxEstadistica=0.0;
	
	//Recolestar los datos del primer y segundo resultado
	foreach ($primerResult as $propiedad => $valor) {
			if(is_numeric($valor)){
				$primerData[]=$valor;
				if($maxEstadistica<$valor){
					$maxEstadistica=$valor;
				}
			}
	}
	$lineasInter = floor($maxEstadistica/4);
	
	//Recolectar colores elegidos
	$color_de_fondo = get_option( 'color_de_fondo', '#FFFFFF80' );
	if(!($color_de_fondo=='#FFFFFF80')){
		$color_fondo=$color_de_fondo.'80';
	}
 	$color_de_lineas = get_option( 'color_de_lineas', '#9A0E1C' );
	$color_de_puntos = get_option( 'color_de_puntos', '#9A0E1C' );
	$color_bordes_puntos = get_option( 'color_bordes_puntos', '#DDE0E3' );
	$color_puntosHover = get_option('color_puntosHover', '#FABC75');
	$color_bordes_puntosHover = get_option('color_bordes_puntosHover', '#FFFFFF');
	// Convertir los datos a JSON
	$labels_json = json_encode($labels);
	$primerData_json = json_encode($primerData);
	
	?>

	<script type="text/javascript" src="../js/Chart.js"></script>
	<canvas id='grafico'></canvas>
	<script>
		var canvas = document.getElementById("grafico");
		var ctx = canvas.getContext("2d");
		var chart = new Chart(ctx, {
	    	type: 'radar',
		    data: {
	        labels: <?php echo $labels_json ?>,
		        datasets: [{
		            label: 'Últimas Estadísticas',
		            data: <?php echo $primerData_json ?>,
		            fill: true,
		            backgroundColor: '<?php echo $color_fondo ?>',
		            borderColor: '<?php echo $color_de_lineas ?>',
		            pointBackgroundColor: '<?php echo $color_de_puntos ?>',
		            pointBorderColor: '<?php echo $color_bordes_puntos ?>',
		            pointHoverBackgroundColor: '<?php echo $color_puntosHover ?>',
		            pointHoverBorderColor: '<?php echo $color_bordes_puntosHover ?>',
		            pointRadius: 5,
		            pointHoverRadius: 5,
		            hitRadius: 5,
		        }]
		    },
		    options: {
					  legend: {
						  labels: {
						  	fontSize: 16
						  }
					  },
		        responsive: true,
		        maintainAspectRatio: true,
		        scale: {
		            gridLines: {
					      circular: true,
					  	  color: 'rgba(27,28,34,0.6)',
					  	  lineWidth: 1,
					  },
		            angleLines: {
					      circular: true,
						  color: 'rgba(27,28,34,0.6)',
					  	  lineWidth: 1,
					  },
					  pointLabels: {
					  	  fontFamily: 'Arial',
					  	  fontSize: 20
					  },
		            ticks: {
		                beginAtZero: true,
						  suggestedMin: 0,
		                suggestedMax: $maxEstadistica,
						  stepSize: $lineasInter,
					  	  maxTicksLimit: 6,
						  z: 2
		            }
		        },
		        elements: {
		            line: {
						tension: 0,
		              borderWidth: 3,
		              borderColor: 'blue',
		              backgroundColor: 'rgba(255,0,0,0.5)',
		              fill: true,
		              borderCapStyle: 'round',
		              borderJoinStyle: 'round',
		              capBezierPoints: true
					  }
				  }
		    }
		});
	</script>
	<?php
	}
}


/*
function contenido(){
	if(is_user_logged_in()){
		ob_start();
		include('includes/forms.php');
		include('includes/insert.php');
		crear_ver_tablas_estadisticas();
		return ob_get_clean();
	}
	else{
		return "Inicia sesion para empezar tu camino.";
	}
}
add_shortcode('prueba1', 'contenido');


?>*/
