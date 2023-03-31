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
function solicitar_baremos($genero, $oposiciones, $edad){
	$array_principal=array();
	if($oposiciones=="Policía Nacional"){
		
		define("PUN_H_PolNac", array( array(11.7,11.5,11.3,11,10.6,10.2,9.8,9.4,8.9,8.3,8.2), array(4,5,6,7,9,11,13,14,15,16,17), array(3.49,3.43,3.37,3.31,3.25,3.19,3.13,3.07,3.01,2.55,2.54)));
		
		define("PUN_M_PolNac", array( array(12.8,12.6,12.4,12.1,11.7,11.3,10.9,10.4,9.9,9.4,9.3), array(35,40,45,51,56,62,69,77,85,94,95), array(4.46,4.37,4.28,4.19,4.1,4.01,3.52,3.43,3.34,3.25,3.24)));
		
		if($genero=="Hombre"){
			$array_principal=PUN_H_PolNac;
		}
		else{
			$array_principal=PUN_M_PolNac;
		}
	}
	
	elseif($oposiciones=="Policía Local"){
		//Constantes
		define("PUN_H_18a24", array(8,8,26,48,4,26));
		define("PUN_H_25a29", array(8.5,6,23,44,4.1,29));
		define("PUN_H_30a34", array(9,4,20,40,4.2,32));
		define("PUN_M_18a24", array(9,5.5,26,35,4.3,30));
		define("PUN_M_25a29", array(9.5,5.25,23,33,4.4,33));
		define("PUN_M_30a34", array(10,5,20,31,4.5,36));
		
		if($edad>17 and $edad<25){
			if($genero=="Hombre"){
				$array_principal=PUN_H_18a24;
			}
			else{
				$array_principal=PUN_M_18a24;
			}  
		}
		elseif($edad>24 and $edad<30){
			if($genero=="Hombre"){
				$array_principal=PUN_H_25a29;
			} 
			else{
				$array_principal=PUN_M_25a29;
			}
		}
		elseif($edad>29 and $edad<35){
			if($genero=="Hombre"){
				$array_principal=PUN_H_30a34;   
			} 
			else{
				$array_principal=PUN_M_30a34;
			}
		}	
	}
	
	elseif ($oposiciones=="Seguridad Privada"){
		
		//Constantes
		define("PUN_H_18a25", array(4,44,1.12));
		define("PUN_H_26a32", array(3,42,1.14));
		define("PUN_H_33a39", array(2,40,1.20));
		define("PUN_H_40a50", array(7,36,1.30));
		define("PUN_H_MAS51", array(6.5,34,1.45));
		define("PUN_M_18a25", array(4.75,36,1.33));
		define("PUN_M_26a32", array(4.25,34,1.40));
		define("PUN_M_33a39", array(4,32,1.49));
		define("PUN_M_40a50", array(3.75,28,1.56));
		define("PUN_M_MAS51", array(3.5,25,2.06));
		
		if($edad>17 and $edad<26){
			if($genero=="Hombre"){
				$array_principal=PUN_H_18a25;
			}
			else{
				$array_principal=PUN_M_18a25;
			}
		}		
		elseif($edad>25 and $edad<33){
			if($genero=="Hombre"){
				$array_principal=PUN_H_26a32;
			}
			else{
				$array_principal=PUN_M_26a32;
			}
		}
		elseif($edad>32 and $edad<40){
			if($genero=="Hombre"){
				$array_principal=PUN_H_33a39;
			}
			else{
				$array_principal=PUN_M_33a39;
			}
		}
		elseif($edad>39 and $edad<51){
			if($genero=="Hombre"){
				$array_principal=PUN_H_40a50;
			}
			else{
				$array_principal=PUN_M_40a50;
			}
		}
		elseif($edad>50){
			if($genero=="Hombre"){
				$array_principal=PUN_H_MAS51;
			}
			else{
				$array_principal=PUN_M_MAS51;
			}
		}
	}
	
	elseif($oposiciones=="Ejercito"){
		list($edad, $nivel)=obtener_edad_nivel();
		define("PUN_H_NivA", array(145,15,5,5));
		define("PUN_H_NivB", array(163,21,8,5.5));
		define("PUN_H_NivC", array(187,27,10,6.5));
		define("PUN_H_NivD", array(205,33,13,7.5));
		define("PUN_M_NivA", array(121,10,3,3.5));
		define("PUN_M_NivB", array(136,14,5,4));
		define("PUN_M_NivC", array(156,18,6,5));
		define("PUN_M_NivD", array(171,22,8,7));
		
		switch($nivel){
			case "Nivel A":
				if($genero=="Hombre"){
					$array_principal=PUN_H_NivA;
				}
				else{
					$array_principal=PUN_M_NivA;
				}
				break;
				
			case "Nivel B":
				if($genero=="Hombre"){
					$array_principal=PUN_H_NivB;
				}
				else{
					$array_principal=PUN_H_NivB;
				}
				break;
				
			case "Nivel C":
				if($genero=="Hombre"){
					$array_principal=PUN_H_NivC;
				}
				else{
					$array_principal=PUN_M_NivC;
				}
				break;
			case "Nivel D":
				if($genero=="Hombre"){
					$array_principal=PUN_H_NivD;
				}
				else{
					$array_principal=PUN_M_NivD;
				}
				break;
			default:
				echo "Necesitas seleccionar el nivel al que optas";
		}
		
	}
	elseif($oposiciones=="Guardia Civil"){
		define("PUN_H_MEN35", array(10,9.25,16,70));
		define("PUN_H_35a39", array(10.30,9.48,16,71));
		define("PUN_H_MAS40", array(10.80,10.33,14,73));
		define("PUN_M_MEN35", array(11.20,11.14,11,81));
		define("PUN_M_35a39", array(11.50,11.35,11,83));
		define("PUN_M_MAS40", array(12.50,12.49,9,88));
		
		if($edad>17 and $edad<35){
			if($genero=="Hombre"){
				$array_principal=PUN_H_MEN35;
			}
			else{
				$array_principal=PUN_M_MEN35;
			}
		}
		elseif($edad>34 and $edad<40){
			if($genero=="Hombre"){
				$array_principal=PUN_H_35a39;
			}
			else{
				$array_principal=PUN_M_35a39;
			}
		}
		elseif($edad>39){
			if($genero=="Hombre"){
				$array_principal=PUN_H_MAS40;
			}
			else{
				$array_principal=PUN_M_MAS40;
			}
		}
	}
	return $array_principal;
}

