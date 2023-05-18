<?php
/*
Author: Mindestec
Author URI: https://mindestec.com
License: GPLv2 o anterior
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: Mindestec Fitness
*/
// Crear variable edad y nivel para la oposicion ejercito
// 

//Arrays de las pruebas a superar
function mdtf_SolicitarBaremos($genero, $oposiciones, $edad){
	$array_principal=array();
	if($oposiciones=="Policía Nacional"){
		
		define("mfPuntHPolNac", array( array(11.7,11.5,11.3,11,10.6,10.2,9.8,9.4,8.9,8.3,8.2), array(4,5,6,7,9,11,13,14,15,16,17), array(3.49,3.43,3.37,3.31,3.25,3.19,3.13,3.07,3.01,2.55,2.54)));
		
		define("mfPuntMPolNac", array( array(12.8,12.6,12.4,12.1,11.7,11.3,10.9,10.4,9.9,9.4,9.3), array(35,40,45,51,56,62,69,77,85,94,95), array(4.46,4.37,4.28,4.19,4.1,4.01,3.52,3.43,3.34,3.25,3.24)));
		
		if($genero=="Hombre"){
			$array_principal=mfPuntHPolNac;
		}
		else{
			$array_principal=mfPuntMPolNac;
		}
	}
	
	elseif($oposiciones=="Policía Local"){
		//Constantes
		define("mfPuntH18a24PolLoc", array(8,8,26,48,4,26));
		define("mfPuntH25a29PolLoc", array(8.5,6,23,44,4.1,29));
		define("mfPuntH30a34PolLoc", array(9,4,20,40,4.2,32));
		define("mfPuntM18a24PolLoc", array(9,5.5,26,35,4.3,30));
		define("mfPuntM25a29PolLoc", array(9.5,5.25,23,33,4.4,33));
		define("mfPuntM30a34PolLoc", array(10,5,20,31,4.5,36));
		
		if($edad>17 and $edad<25){
			if($genero=="Hombre"){
				$array_principal=mfPuntH18a24PolLoc;
			}
			else{
				$array_principal=mfPuntM18a24PolLoc;
			}  
		}
		elseif($edad>24 and $edad<30){
			if($genero=="Hombre"){
				$array_principal=mfPuntH25a29PolLoc;
			} 
			else{
				$array_principal=mfPuntM25a29PolLoc;
			}
		}
		elseif($edad>29 and $edad<35){
			if($genero=="Hombre"){
				$array_principal=mfPuntH30a34PolLoc;   
			} 
			else{
				$array_principal=mfPuntM30a34PolLoc;
			}
		}	
	}
	
	elseif ($oposiciones=="Seguridad Privada"){
		
		//Constantes
		define("mfPuntH18a25SegPriv", array(4,44,1.12));
		define("mfPuntH26a32SegPriv", array(3,42,1.14));
		define("mfPuntH33a39SegPriv", array(2,40,1.20));
		define("mfPuntH40a50SegPriv", array(7,36,1.30));
		define("mfPuntHMAS51SegPriv", array(6.5,34,1.45));
		define("mfPuntM18a25SegPriv", array(4.75,36,1.33));
		define("mfPuntM26a32SegPriv", array(4.25,34,1.40));
		define("mfPuntM33a39SegPriv", array(4,32,1.49));
		define("mfPuntM40a50SegPriv", array(3.75,28,1.56));
		define("mfPuntMMAS51SegPriv", array(3.5,25,2.06));
		
		if($edad>17 and $edad<26){
			if($genero=="Hombre"){
				$array_principal=mfPuntH18a25SegPriv;
			}
			else{
				$array_principal=mfPuntM18a25SegPriv;
			}
		}		
		elseif($edad>25 and $edad<33){
			if($genero=="Hombre"){
				$array_principal=mfPuntH26a32SegPriv;
			}
			else{
				$array_principal=mfPuntM26a32SegPriv;
			}
		}
		elseif($edad>32 and $edad<40){
			if($genero=="Hombre"){
				$array_principal=mfPuntH33a39SegPriv;
			}
			else{
				$array_principal=mfPuntM33a39SegPriv;
			}
		}
		elseif($edad>39 and $edad<51){
			if($genero=="Hombre"){
				$array_principal=mfPuntH40a50SegPriv;
			}
			else{
				$array_principal=mfPuntM40a50SegPriv;
			}
		}
		elseif($edad>50){
			if($genero=="Hombre"){
				$array_principal=mfPuntHMAS51SegPriv;
			}
			else{
				$array_principal=mfPuntMMAS51SegPriv;
			}
		}
	}
	
	elseif($oposiciones=="Ejercito"){
		list($edad, $nivel)=obtener_edad_nivel();
		define("mfPuntHNivAEjerc", array(145,15,5,5));
		define("mfPuntHNivBEjerc", array(163,21,8,5.5));
		define("mfPuntHNivCEjerc", array(187,27,10,6.5));
		define("mfPuntHNivDEjerc", array(205,33,13,7.5));
		define("mfPuntMNivAEjerc", array(121,10,3,3.5));
		define("mfPuntMNivBEjerc", array(136,14,5,4));
		define("mfPuntMNivCEjerc", array(156,18,6,5));
		define("mfPuntMNivDEjerc", array(171,22,8,7));
		
		switch($nivel){
			case "Nivel A":
				if($genero=="Hombre"){
					$array_principal=mfPuntHNivAEjerc;
				}
				else{
					$array_principal=mfPuntMNivAEjerc;
				}
				break;
				
			case "Nivel B":
				if($genero=="Hombre"){
					$array_principal=mfPuntHNivBEjerc;
				}
				else{
					$array_principal=mfPuntMNivBEjerc;
				}
				break;
				
			case "Nivel C":
				if($genero=="Hombre"){
					$array_principal=mfPuntHNivCEjerc;
				}
				else{
					$array_principal=mfPuntMNivCEjerc;
				}
				break;
			case "Nivel D":
				if($genero=="Hombre"){
					$array_principal=mfPuntHNivDEjerc;
				}
				else{
					$array_principal=mfPuntMNivDEjerc;
				}
				break;
			default:
				echo "Necesitas seleccionar el nivel al que optas";
		}
		
	}
	elseif($oposiciones=="Guardia Civil"){
		define("mfPuntHMEN35GuarCiv", array(10,9.25,16,70));
		define("mfPuntH35a39GuarCiv", array(10.30,9.48,16,71));
		define("mfPuntHMAS40GuarCiv", array(10.80,10.33,14,73));
		define("mfPuntMMEN35GuarCiv", array(11.20,11.14,11,81));
		define("mfPuntM35a39GuarCiv", array(11.50,11.35,11,83));
		define("mfPuntMMAS40GuarCiv", array(12.50,12.49,9,88));
		
		if($edad>17 and $edad<35){
			if($genero=="Hombre"){
				$array_principal=mfPuntHMEN35GuarCiv;
			}
			else{
				$array_principal=mfPuntMMEN35GuarCiv;
			}
		}
		elseif($edad>34 and $edad<40){
			if($genero=="Hombre"){
				$array_principal=mfPuntH35a39GuarCiv;
			}
			else{
				$array_principal=mfPuntM35a39GuarCiv;
			}
		}
		elseif($edad>39){
			if($genero=="Hombre"){
				$array_principal=mfPuntHMAS40GuarCiv;
			}
			else{
				$array_principal=mfPuntMMAS40GuarCiv;
			}
		}
	}
	return $array_principal;
}

