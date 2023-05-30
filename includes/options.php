<?php
/*
Author: Mindestec
Author URI: https://mindestec.com
License: GPLv2 o anterior
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: Mindestec Fitness
*/
// Añadir menu y submenus al panel de administracion del sitio
function mdtf_AgregarPagOpc() {
  add_menu_page( 'Mindestec Fitness', 'MFitness', 'manage_options', 'opciones_de_mi_plugin', null, plugins_url('../assets/Logo.svg', __FILE__) );
  add_submenu_page( 'opciones_de_mi_plugin', 'Colores del Gráfico', 'Colores del Gráfico', 'manage_options', 'opciones_de_mi_plugin', 'mdtf_GenPagOpc');
  add_submenu_page('opciones_de_mi_plugin', 'Exportar Usuarios', 'Exportar Usuarios', 'manage_options', 'mdtf_ExpDatUsu', 'mdtf_ExpDatUsu');
}

add_action( 'admin_menu', 'mdtf_AgregarPagOpc' );

//------------------------------------------------------------------------------------------------------------------------------------------------------

// Añadir un icono al enlace principal del menu
function mdtf_IconoMenuAdmin() {
    echo '<style>
        #adminmenu #toplevel_page_opciones_de_mi_plugin .wp-menu-image img {
            width: 20px;
            height: 20px;        
		}
    </style>';
}
add_action('admin_head', 'mdtf_IconoMenuAdmin');
//------------------------------------------------------------------------------------------------------------------------------------------------------

//Exportar datos de los usuarios para su futuro uso externo
add_action('admin_post_exportar_usuarios', 'mdtf_ExpUsuCsv');

function mdtf_ExpUsuCsv() {
  try{
	  function sanitize_csv_field($field) {
		// Eliminar caracteres de nueva línea y retorno de carro
		$field = str_replace(array("\r", "\n"), '', $field);
		// Escapar comillas dobles
		$field = str_replace('"', '""', $field);
		return '"' . $field . '"';
	  }
	  // Obtener los datos de usuario y metadatos seleccionados por el usuario
	  $datos_usuario = isset($_POST['datos_usuario']) ? array_map('sanitize_text_field', $_POST['datos_usuario']) : array();
	  $metadatos_usuario = isset($_POST['metadatos_usuario']) ? array_map('sanitize_text_field', $_POST['metadatos_usuario']) : array();

	  // Construir la consulta para obtener los usuarios y sus metadatos
	  $args = array(
		'fields' => array('ID', 'user_login', 'user_email', 'display_name', 'user_registered'),
		'meta_query' => array(),
	  );

	  // Agregar las metas seleccionadas por el usuario a la consulta
	  foreach ($metadatos_usuario as $meta_key) {
		array_push($args['fields'], $meta_key);
		array_push($args['meta_query'], array('key' => $meta_key));
	  }
		if($_POST['filtro_genero']!='null'){
			if (in_array('genero', $metadatos_usuario) && $_POST['filtro_genero']) {
				array_push($args['meta_query'], array(
					'key' => 'genero',
					'value' => sanitize_text_field($_POST['filtro_genero']),
					'compare' => '='
				));
			}
		}

		if($_POST['filtro_oposiciones']!='null'){
		  if (in_array('oposiciones', $metadatos_usuario) && $_POST['filtro_oposiciones']) {
			array_push($args['meta_query'], array(
				'key' => 'oposiciones',
				'value' => sanitize_text_field($_POST['filtro_oposiciones']),
				'compare' => '='
			));
		  }
		}
	  
		if (isset($_POST['filtro_ciudad']) && $_POST['filtro_ciudad'] != 'null') {
    		if (in_array('ciudad', $metadatos_usuario) && $_POST['filtro_ciudad']) {
        	  array_push($args['meta_query'], array(
            	'key' => 'ciudad',
            	'value' => sanitize_text_field($_POST['filtro_ciudad']),
            	'compare' => '='
        	  ));
    		}
		}
	  
	  // Obtener los usuarios que coinciden con la consulta
	  $usuarios = get_users($args);

	  // Convertir los datos de usuario a formato CSV
	  $data = array();
	  foreach ($usuarios as $usuario) {
		$row = array(
		  'Nombre de usuario' => $usuario->user_login,
		  'Correo electrónico' => $usuario->user_email,
		  'Nombre para mostrar' => $usuario->display_name,
		  'Fecha de registro' => $usuario->user_registered,
		);

		// Agregar los metadatos seleccionados por el usuario a la fila
		foreach ($metadatos_usuario as $meta_key) {
			$meta_value = get_user_meta($usuario->ID, $meta_key, true);
			// Aplicar la codificación de caracteres adecuada al valor del metadato
        	$meta_value = mb_convert_encoding($meta_value, 'UTF-8', 'UTF-8');
		  	$row[$meta_key] = $meta_value;
		}

		// Agregar la fila a los datos
		array_push($data, $row);
	  }
		if(empty($row)){
			throw new Exception('Error al exporta con el filtrado seleccionado: No hay usuarios con ese filtrado.');
		}
	  // Convertir los datos a formato CSV
	  $csv = '';
	  $headers = array_keys($data[0]);
	  $csv .= implode(';', $headers) . "\n";
	  foreach ($data as $row) {
		$csv .= implode(';', array_map('sanitize_csv_field', array_values($row))) . "\n";
	  }

	  // Generar el archivo CSV y descargarlo
	  header('Content-Type: text/csv; charset=UTF-8');
	  header('Content-Disposition: attachment; filename=usuarios.csv');
	  echo "\xEF\xBB\xBF";
	  echo esc_html(html_entity_decode($csv));
	  exit();
	  
	  
  }
	catch(Exception $e){

		echo '<h1>'.esc_html($e->getMessage()).'</h1>';
		?>

		<style>
			.button {
				display: inline-block;
				padding: 0.3em 1em;
				font-size: 1rem;
				font-weight: 500;
				line-height: 1.5;
				text-align: center;
				white-space: nowrap;
				vertical-align: middle;
				cursor: pointer;
				border: 1px solid transparent;
				border-radius: 0.25em;
				background-color: #007cba;
				color: #fff;
				text-decoration: none;
			}

			/* Estilo de los botones de WordPress cuando se les pasa el cursor encima */
			.button:hover, .button:focus, .button:active {
				background-color: #006799;
				border-color: transparent;
				color: #fff;
				text-decoration: none;
			}
		</style>
		<button class="button" id="button button-primary" onclick="window.history.back();">Volver</button>
<?php
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------------

// Formulario para filtar la exportacion de los datos de los usuarios 
function mdtf_ExpDatUsu(){ ?>

	<style>
		#mensaje{
			display: none;
			color: green;
		}
	</style>

	<h1>Exportar Datos de los Alumnos</h1>
		<div class="wrap">
			<h1>Exportar usuarios</h1>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php?action=mdtf_ExpUsuCsv')); ?>" id="exportar_usuarios_form">
				<?php wp_nonce_field('exportar_usuarios'); ?>
				<input type="hidden" name="action" value="exportar_usuarios">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="datos_usuario">Datos de usuario:</label></th>
							<td>
								<input type="checkbox" name="datos_usuario[]" value="user_login" checked> Nombre de usuario<br>
								<input type="checkbox" name="datos_usuario[]" value="user_email" checked> Correo electrónico<br>
								<input type="checkbox" name="datos_usuario[]" value="display_name" checked> Nombre para mostrar<br>
								<input type="checkbox" name="datos_usuario[]" value="user_registered" checked> Fecha de registro<br>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="metadatos_usuario">Metadatos de usuario:</label></th>
							<td>
								<input type="checkbox" name="metadatos_usuario[]" value="genero" onclick="mostrarFiltro('filtro_genero', this)" checked> Genero
								<select name="filtro_genero" id="filtro_genero">
									<option value="null"></option>
									<option value="Hombre">Hombre</option>
									<option value="Mujer">Mujer</option>
								</select>
								<br><input type="checkbox" name="metadatos_usuario[]" value="oposiciones" onclick="mostrarFiltro('filtro_oposiciones', this)" checked> Oposición
								<select name="filtro_oposiciones" id="filtro_oposiciones">
									<option value="null"></option>
									<option value="Policía Nacional">Policía Nacional</option>
									<option value="Policía Local">Policía Local</option>
									<option value="Guardia Civil">Guardia Civil</option>
									<option value="Ejercito">Ejercito</option>
									<option value="Seguridad Privada">Seguridad Privada</option>
								</select><br>
								<br>
                            	<input type="checkbox" name="metadatos_usuario[]" value="ciudad" onclick="mostrarFiltro('filtro_cuidad', this)" checked> Admitir noticias
                            	<select name="filtro_cuidad" id="filtro_ciudad">
                                	    <option value="null"></option>
    										<?php
    											$ciudades = array( 'A Coruña', 'Albacete', 'Alicante', 'Almería', 'Araba', 'Asturias','Ávila','Badajoz', 'Barcelona', 'Bizkaia', 'Burgos', 'Cantabria', 'Castellón', 'Ceuta', 'Ciudad Real', 'Cuenca', 'Cáceres', 'Cádiz', 'Córdoba', 'Gipuzkoa', 'Girona', 'Granada', 'Guadalajara', 'Huelva', 'Huesca', 'Islas Baleares', 'Jaén', 'La Rioja', 'Las Palmas', 'León', 'Lleida', 'Lugo', 'Madrid', 'Melilla', 'Murcia', 'Málaga', 'Navarra', 'Ourense', 'Palencia', 'Pontevedra', 'Salamanca', 'Santa Cruz de Tenerife', 'Segovia', 'Sevilla', 'Soria', 'Tarragona', 'Teruel', 'Toledo', 'Valencia', 'Valladolid', 'Zamora', 'Zaragoza', 
    											);

    											foreach ($ciudades as $ciudad) {
        											echo '<option value="' . esc_html($ciudad) . '">' . esc_html($ciudad) . '</option>';
    											}
    										?>
								</select>
								<script>
									
   									var filtroCiudad = document.getElementById('filtro_ciudad');
    								var opcionesCiudad = Array.from(filtroCiudad.options);

    								filtroCiudad.addEventListener('input', function() {
        								var busqueda = filtroCiudad.value.toLowerCase();

        								opcionesCiudad.forEach(function(opcion) {
            								var ciudad = opcion.value.toLowerCase();

            								if (ciudad.includes(busqueda)) {
                								opcion.style.display = 'block';
            								} else {
                								opcion.style.display = 'none';
            								}
        								});
    								});
									
									var filtroNews = document.getElementById('filtro_admit_news');
									var filtroCiudad = document.getElementById('filtro_ciudad');
									filtroNews.addEventListener('change', function(){
										if(filtroNews.checked){
											filtroCiudad.style.display='block';
										} else {
											filtroCiudad.style.display='none';
										}
									});
								</script>
							</td>
						</tr>
					</tbody>
				</table>
				<?php submit_button('Exportar usuarios'); ?>
			</form>
			<div id="mensaje">¡Archivo descargado correctamente!</div>
		</div>

	<script>
		function mostrarFiltro(id_selector, checkbox){
			var selector = document.getElementById(id_selector);
				if(checkbox.checked){
					selector.style.display="block";
				} else {
					selector.style.display="none";
				}
			
		}
		
		document.addEventListener("DOMContentLoaded", function() {
		  var exportarForm = document.getElementById('exportar_usuarios_form');
		  exportarForm.addEventListener('submit', function() {
			document.getElementById('mensaje').style.display = 'block';
		  });
		});
	</script>
<?php
}

//------------------------------------------------------------------------------------------------------------------------------------------------------

//Seccion Cambiar Colores de Grafico
add_option( 'ultimo_color_seleccionado', '#FFFFFF' );
function mdtf_ActUltColor() {
  //Definir variables de colores
  $color_de_fondo = '';
  $color_de_lineas = '';
  $color_de_puntos = '';
  $color_bordes_puntos = '';
  $color_puntosHover = '';
  $color_bordes_puntosHover = '';
	
	// Guardar colores
  if ( isset( $_POST['color_de_fondo'] ) ) {
    $color_de_fondo = sanitize_hex_color( $_POST['color_de_fondo'] );
  }

  if ( isset( $_POST['color_de_lineas'] ) ) {
    $color_de_lineas = sanitize_hex_color( $_POST['color_de_lineas'] );
  }

  if ( isset( $_POST['color_de_puntos'] ) ) {
    $color_de_puntos = sanitize_hex_color( $_POST['color_de_puntos'] );
  }

  if ( isset( $_POST['color_bordes_puntos'] ) ) {
    $color_bordes_puntos = sanitize_hex_color( $_POST['color_bordes_puntos'] );
  }

  if ( isset( $_POST['color_puntosHover'] ) ) {
    $color_puntosHover = sanitize_hex_color( $_POST['color_puntosHover'] );
  }

  if ( isset( $_POST['color_bordes_puntosHover'] ) ) {
    $color_bordes_puntosHover = sanitize_hex_color( $_POST['color_bordes_puntosHover'] );
  }

  //Definir el color si esta vacio
  if ( ! empty( $color_de_fondo ) ) {
    update_option( 'color_de_fondo', $color_de_fondo );
  }

  if ( ! empty( $color_de_lineas ) ) {
    update_option( 'color_de_lineas', $color_de_lineas );
  }
	
  if ( ! empty( $color_de_puntos ) ) {
    update_option( 'color_de_puntos', $color_de_puntos );
  }
	
  if ( ! empty( $color_bordes_puntos ) ) {
    update_option( 'color_bordes_puntos', $color_bordes_puntos );
  }

  if ( ! empty( $color_puntosHover ) ) {
    update_option( 'color_puntosHover', $color_puntosHover );
  }
  if ( ! empty( $color_bordes_puntosHover ) ) {
    update_option( 'color_bordes_puntosHover', $color_bordes_puntosHover );
  }
}

add_action( 'admin_init', 'mdtf_ActUltColor' );

//------------------------------------------------------------------------------------------------------------------------------------------------------

// Función para generar la página de opciones
function mdtf_GenPagOpc() {
	
	$color_de_fondo = get_option( 'color_de_fondo', '#FFFFFF' );
 	$color_de_lineas = get_option( 'color_de_lineas', '#9A0E1C' );
	$color_de_puntos = get_option( 'color_de_puntos', '#9A0E1C' );
	$color_bordes_puntos = get_option( 'color_bordes_puntos', '#9A0E1C' );
	$color_puntosHover = get_option( 'color_puntosHover', '#FABC75' );
	$color_bordes_puntosHover = get_option( 'color_bordes_puntosHover', '#9A0E1C' ); ?>

  <div class="wrap" style="width: 70%">
    <h1>Selecciona los colores del grafico de Estadisticas</h1>
    <form method="post" action="">
      <?php
      // Generar los campos ocultos necesarios para procesar los datos
      settings_fields( 'opciones_de_mi_plugin' );
      do_settings_sections( 'opciones_de_mi_plugin' );
      ?>
      <table class="form-table">
        <tr>
          <th scope="row">Color de fondo del gráfico</th>
          <td><input type="color" name="color_de_fondo" value="<?php echo esc_attr( $color_de_fondo ); ?>" ></td>
          <th scope="row">Color de las líneas del gráfico</th>
          <td><input type="color" name="color_de_lineas" value="<?php echo esc_attr( $color_de_lineas ); ?>"></td>
        </tr>
        <tr>
          <th scope="row">Color de los puntos</th>
          <td><input type="color" name="color_de_puntos" value="<?php echo esc_attr( $color_de_puntos ); ?>"></td>
          <th scope="row">Color de borde de los puntos</th>
          <td><input type="color" name="color_bordes_puntos" value="<?php echo esc_attr( $color_bordes_puntos ); ?>"></td>
        </tr>
		<tr>
          <th scope="row">Color de los puntos al pasar el raton</th>
          <td><input type="color" name="color_puntosHover" value="<?php echo esc_attr( $color_puntosHover ); ?>"></td>
          <th scope="row">Color de borde de los puntos al pasar el raton</th>
          <td><input type="color" name="color_bordes_puntosHover" value="<?php echo esc_attr( $color_bordes_puntosHover ); ?>"></td>
        </tr>
      </table>
      <?php
      // Agregar botón de guardar opciones
      submit_button( 'Guardar opciones' );
      ?>
    </form>
  </div>
  <?php

}

//------------------------------------------------------------------------------------------------------------------------------------------------------

// Agregar opciones de color a la base de datos
function mdtf_AgregarOpcColor() {
  // Agregar la opción de color de fondo
  add_option( 'color_de_fondo', '#FABC75' );

  // Agregar la opción de color de líneas
  add_option( 'color_de_lineas', '#9A0E1C' );
	
  // Agregar la opción de color de puntos
  add_option( 'color_de_puntos', '#9A0E1C' );

  // Agregar la opción de color del borde de los puntos
  add_option( 'color_bordes_puntos', '#9A0E1C' );
	
  // Agregar la opción de color de los puntos al pasar el raton
  add_option( 'color_puntosHover', '#FABC75' );
	
  // Agregar la opción de color de los puntos al pasar el raton
  add_option( 'color_bordes_puntosHover', '#9A0E1C' );
	
}
add_action( 'admin_init', 'mdtf_AgregarOpcColor' );

//------------------------------------------------------------------------------------------------------------------------------------------------------

// Reglas de validación y sanitización para las opciones de color
function mdtf_ValidarOpcColor( $input ) {
  // Validar y sanitizar la opción de color de fondo
  $nuevo_color_de_fondo = sanitize_hex_color( $input['color_de_fondo'] );
  if ( ! empty( $nuevo_color_de_fondo ) ) {
    $input['color_de_fondo'] = $nuevo_color_de_fondo;
  }

  // Validar y sanitizar la opción de color de líneas
  $nuevo_color_de_lineas = sanitize_hex_color( $input['color_de_lineas'] );
  if ( ! empty( $nuevo_color_de_lineas ) ) {
    $input['color_de_lineas'] = $nuevo_color_de_lineas;
  }

	// Validar y sanitizar la opción de color de los puntos
  $nuevo_color_de_puntos = sanitize_hex_color( $input['color_de_puntos'] );
  if ( ! empty( $nuevo_color_de_puntos ) ) {
    $input['color_de_puntos'] = $nuevo_color_de_puntos;
  }
	
  $nuevo_color_bordes_puntos = sanitize_hex_color( $input['color_bordes_puntos'] );
  if ( ! empty( $nuevo_color_bordes_puntos ) ) {
    $input['color_bordes_puntos'] = $nuevo_color_bordes_puntos;
  }
	
  $nuevo_color_puntosHover = sanitize_hex_color( $input['color_puntosHover'] );
  if ( ! empty( $nuevo_color_puntosHover ) ) {
    $input['color_puntosHover'] = $nuevo_color_puntosHover;
  }

  $nuevo_color_bordes_puntosHover = sanitize_hex_color( $input['color_bordes_puntosHover'] );
  if ( ! empty( $nuevo_color_bordes_puntosHover ) ) {
    $input['color_bordes_puntosHover'] = $nuevo_color_bordes_puntosHover;
  }
  return $input;
}
add_filter( 'sanitize_option_opciones_de_mi_plugin', 'mdtf_ValidarOpcColor' );


