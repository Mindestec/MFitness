<?php
/*
Author: Mindestec
Author URI: https://mindestec.com
License: GPLv2 o anterior
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: Mindestec Fitness*/

// Insertar datos del formulario a la base de datos
function mdtf_InsertarDatos($wpdb, $table_name, $user_id, $oposiciones, $pruebas){
	$nextId=$wpdb->get_var($wpdb->prepare("SELECT MAX(oposicion_id) FROM $table_name WHERE oposicion = %s",$oposiciones));
	$nextId++;
	// Insertar datos en la bd
	$wpdb->insert($table_name, array(
		'user_id'=> $user_id,
		'oposicion'=>$oposiciones,
		'oposicion_id'=>$nextId,
		'prueba1'=>$pruebas['prueba1'],
		'prueba2'=>$pruebas['prueba2'],
		'prueba3'=>$pruebas['prueba3'],
		'prueba4'=>$pruebas['prueba4'] ?? null,
		'prueba5'=>$pruebas['prueba5'] ?? null,
		'prueba6'=>$pruebas['prueba6'] ?? null,

	), array(
		'%d',
		'%s',
		'%f',
		'%f',
		'%f',
		'%f',
		'%f',
		'%f'
	));
	wp_redirect('/user/');
	exit;
}
