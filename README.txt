=== MFitness ===
Contributors: mindestec
Tags: fitness, gym, exercise, workout, training, sports, classes, nutrition, diet, intructor, coaching
Requires at least: 5.0
Tested up to: 6.2
Stable tag: 0.1.2
Requires PHP: 7.2 o superior
License: GPLv2 o anterior
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Icon: ./assets/LogoGif.gif
Text Domain: Mindestec Fitness
Author: Mindestec
Author URI: https://mindestec.com

El plugin MFitness permite hacer un seguimiento de las estadísticas de los usuarios registrados en una oposición, para saber si han superado la nota mínima requerida.

== Descriptión ==
Permite hacer un seguimiento de las estadísticas de los usuarios registrados en una oposición, para saber si han superado la nota mínima requerida.
Se pueden visualizar las últimas estadísticas en un gráfico, y los datos de los usuarios se pueden exportar en formato CSV. Además, el plugin permite personalizar los colores del gráfico para adaptarse a las preferencias del sitio.  
Utiliza los shortcode [mfitnessTabla] en la pagina donde quieras recolectar y mostrar las estadisticas y [mfitnessGraf] para insertar el gráfico tipo radar sobre las ultimas estadisticas introducidas.

Requisitos:
Se requiere la instalación del plugin Ultimate Member para crear un formulario específico.
  * Introducir los siguientes campos en el formulario de registro:
      * Username
      * E-mail Address
      * Password
      * Género: Clave meta: genero; Valores: Hombre, Mujer
      * Fecha de nacimiento: Clave meta: birth_date
      * Oposiciones: Clave meta: oposiciones; Valores: Policía Nacional, Policía Local, Guardia Civil, Ejercito, Vigilante de Seguridad
      * Nivel: Clave meta: nivel-ejercito; Valores: Nivel A, Nivel B, Nivel C, Nivel D (Agregar condicion para que se muestre, si en oposiciones selecciona la opcion Ejercito.)
    
Funcionalidades:
  * Seguimiento de estadísticas de los usuarios registrados en una oposición.
  * Visualización de las últimas estadísticas en un gráfico tipo radar.
  * Exportación de los datos de los usuarios en formato CSV con filtrado.
  * Personalización de los colores del gráfico.

Instrucciones de uso:
  1. Instala y activa el plugin MFitness.
  2. Crea un formulario en Ultimate Member con los campos necesarios para recopilar los datos de los usuarios en la oposición. Formulario de los requisitos.
  3. Visualiza las últimas estadísticas en el gráfico.
  4. Exporta los datos de los usuarios en formato CSV si lo deseas.
  5. Personaliza los colores del gráfico para adaptarse a tus preferencias.
  6. Utiliza los shortcode [mfitnessTabla] en la pagina donde quieras recolectar y mostrar las estadisticas y [mfitnessGraf] para insertar el gráfico tipo radar sobre las ultimas estadisticas introducidas.
  
Nota:
  * Se recomienda mantener todos plugins mencionados actualizados para evitar conflictos.

== Frequently Asked Questions ==
= ¿En que regiones se puede utilizar este complemento? =
Actualmente solo en España, ya que este complemento recolecta todas los baremos y puntuaciones referente a diversas oposiciones oficiales españolas.
Aunque se añadirá nuevas regiones en las siguientes versiones.

= ¿Cuales son las oposiciones disponibles en este complemento? =
Policía Naciona, Policía Local, Guadia Civil, Ejercito Español y Seguridad Privada.

== Screenshots ==
1. Página de cambio de color del grafico.
2. Exportacion de datos de los usuarios con filtrado
3. Visualizacion de los shortcodes mediante un ejemplo.

== Changelog ==
= 0.1.3 =
* Iconos indicativos para mostrar el avance comparado con el primer simulacro registrado.
* Probado hasta 6.2.2

= 0.1.2 =
* Nuevo filreado de exportación añadido (Ciudad).

= 0.1.1 =
* Se corrigió una vulnerabilidad de seguridad XSS en el área de Configuración. Actualización muy recomendable y necesaria.
* Se modificó las referencias de los shortcodes.
* Se modificó las funciones genericas del complemento.
* Probado hasta 6.2

= 0.1.0 =
* Probado hasta 6.1.1