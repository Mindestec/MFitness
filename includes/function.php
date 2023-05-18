<?php
/*
Author: Mindestec
Author URI: https://mindestec.com
License: GPLv2 o anterior
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: Mindestec Fitness
*/

//Cargar los archivos necesarios
function mdtf_CargarScript(){
	//Directorio raiz del plugin
	$plugin_dir_uri = plugin_dir_url( 'MFitness/mfitness.php');
	
	//Cargar Chartjs v=4.2.1
	wp_register_script( 'Chart', $plugin_dir_uri.'node_modules/chart.js/dist/chart.umd.js', array(), '4.2.1', false);
	wp_enqueue_script('Chart');
}
add_action('wp_enqueue_scripts', 'mdtf_CargarScript');

add_shortcode('mfitnessGraf', 'mdtf_DibujarGrafico');

function mdtf_DibujarGrafico(){

	global $wpdb;
	global $current_user;
	$user_id=absint($current_user->ID);
	$mprefix='mdtf';
	$table_name=$wpdb->prefix.$mprefix;
	include_once('forms.php');
	list($genero,$oposiciones)=mdtf_ObtGenOpos();
	$result=mdtf_RecolectarPruebas($wpdb, $table_name, $user_id, $oposiciones);
	
	if(empty($result[0])){
		$cantResult=count($result);
	}else{
		$cantResult=count(get_object_vars($result[0]));
	}
	$labels=array();
	for($i=1; $i<=$cantResult; $i++){
		$labels[]="Prueba ".$i;
	}
		
	$primerData = array();
	$segData = array();
	$primerResult=array_shift($result);
	$maxEstadistica=0.0;
	
	//Recolestar los datos del ultimo simulacro
	if($primerResult==null){
		$maxEstadisticas=0.0;
	}else{
	foreach ($primerResult as $propiedad => $valor) {
			if(is_numeric($valor)){
				$primerData[]=$valor;
				if($maxEstadistica<$valor){
					$maxEstadistica=$valor;
				}
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

	<canvas id='grafico'></canvas>

	<script>
		
		var datos = {
			labels: <?php echo json_encode($labels) ?>,
		        datasets: [{
		            label: 'Últimas Estadísticas',
		            data: <?php echo json_encode($primerData) ?>,
		            fill: true,
		            backgroundColor: '<?php echo esc_html($color_fondo) ?>',
		            borderColor: '<?php echo esc_html($color_de_lineas) ?>',
		            pointBackgroundColor: '<?php echo esc_html($color_de_puntos) ?>',
		            pointBorderColor: '<?php echo esc_html($color_bordes_puntos) ?>',
		            pointHoverBackgroundColor: '<?php echo esc_html($color_puntosHover) ?>',
		            pointHoverBorderColor: '<?php echo esc_html($color_bordes_puntosHover) ?>',
		            pointRadius: 5,
		            pointHoverRadius: 5,
		            hitRadius: 5,
					spanGaps:true
		        }]
		};
		
		var config = {
			type: 'radar',
			data: datos,
			 options: {
				plugins: {
					legend: {
						labels: {
							font: {
								size: 16
							}
						}
					}
			    },
		        responsive: true,
		        maintainAspectRatio: true,
		        scales: {
				  r: {
					angleLines: {
						  display:true,
						  color: 'rgba(27,28,34,0.9)',
					  	  lineWidth: 1

					  },
					grid: {
						  display: true,
					  	  color: 'rgba(27,28,34,0.9)',
					  	  lineWidth: 1,
						  circular: true

					  },
					pointLabels: {
						  display: true,
					  	  font: {
						    	size: 20
					  	  }
					  },
		            ticks: {
		                  beginAtZero: true,
						  suggestedMin: 0,
		                  suggestedMax:<?php echo esc_html($maxEstadistica) ?>,
						  stepSize: <?php echo esc_html($lineasInter) ?>,
					  	  maxTicksLimit: 6,
						  z: 2
		            }
			 	  }
		        },
		        elements: {
		            line: {
						tension: 0,
		                borderWidth: 3,
		                borderColor: 'blue'
		               
					  }
				  }
		    }
		};
		
		const grafico = new Chart(
			document.getElementById("grafico"),
			config
		);
		
	</script>

	<?php	
	}
