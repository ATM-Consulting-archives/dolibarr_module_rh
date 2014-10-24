<?php

	class TRH_Numero_special extends TObjetStd {
		
		function __construct() { /* declaration */
			parent::set_table(MAIN_DB_PREFIX.'rh_numero_special');
			parent::add_champs('numero','type=chaine;');
			
			parent::_init_vars();
			parent::start();
			
		}
		
		static function getAllNumbers(&$db) {
				
			$TNumerosSpeciaux = array();
			
			$sql = "SELECT numero";
			$sql.= " FROM ".MAIN_DB_PREFIX."rh_numero_special";
			$sql.= " ORDER BY rowid";
			
			$resql = $db->query($sql);
			
			while($res = $db->fetch_object($resql)) {
				$TNumerosSpeciaux[] = $res->numero;
			}
			
			return $TNumerosSpeciaux;
			
		}
		
		static function existeNumber(&$doli_db, $num) {
			
			$sql = "SELECT numero";
			$sql.= " FROM ".MAIN_DB_PREFIX."rh_numero_special";
			$sql.= ' WHERE numero = "'.$num.'"';
			
			$resql = $doli_db->query($sql);
			
			while($res = $doli_db->fetch_object($resql)) {
				return true;
			}

			return false;
			
		}
		
		static function deleteNumber(&$doli_db, $num) {
			
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."rh_numero_special";
			$sql.= " WHERE numero = ".$num;
			
			$doli_db->query($sql);
			
			return true;
			
		}
		
	}
