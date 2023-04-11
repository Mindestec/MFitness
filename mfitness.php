<?php
/*
Plugin Name: MFitness
Plugin URI: https://wordpress.org/plugins/mfitness/
Description: Permite a los suscriptores registrar sus puntuaciones de pruebas fisicas y mostrarlas en su perfil de forma gráfica.
			 Utiliza los shortcode [mfitnessTabla] en la pagina donde quieras recolectar y mostrar las estadisticas y [mfitnessGraf] para insertar el gráfico tipo radar sobre las ultimas estadisticas introducidas.
Version: 0.1
Requires PHP: 7.2
Author: Mindestec
Author URI: https://mindestec.com
License: GPLv2 o anterior
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: Mindestec Fitness

MFitness es software gratuito: puede redistribuirlo y/o modificarlo
bajo los términos de la Licencia Pública General GNU publicada por
la Free Software Foundation, ya sea la versión 2 de la Licencia, o
cualquier versión posterior.

MFitness se distribuye con la esperanza de que sea útil,
pero SIN NINGUNA GARANTIA;
sin siquiera la garantía implícita de
COMERCIABILIDAD o IDONEIDAD PARA UN FIN DETERMINADO. Ver el
Licencia Pública General GNU para más detalles.

Debería haber recibido una copia de la Licencia Pública General GNU
junto con MFitness. De lo contrario, consulte https://www.gnu.org/licenses/gpl-2.0.html

*/
include (plugin_dir_path(__FILE__).'includes/function.php');
include (plugin_dir_path(__FILE__).'includes/forms.php');
include (plugin_dir_path(__FILE__).'includes/options.php');
//include (plugin_dir_path(__FILE__).'includes/insert.php');
include('uninstall.php');
register_uninstall_hook(__FILE__, 'mfDesinstalar');