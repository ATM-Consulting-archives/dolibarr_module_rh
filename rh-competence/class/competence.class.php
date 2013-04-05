<?php

//TRH_LIGNE_CV
//définition de la classe décrivant les lignes de CV d'un utilisateur
class TRH_ligne_cv extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_ligne_cv');
		parent::add_champs('date_debut,date_fin','type=date;');		//dates de début et de fin de la formation suivie
		parent::add_champs('libelleExperience','type=chaine'); 	//formation suivie
		parent::add_champs('descriptionExperience','type=chaine'); 	//formation suivie
		parent::add_champs('lieuExperience','type=chaine'); 	//formation suivie
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}
}

//TRH_FORMATION
//définition de la classe pour rentrer les compétences d'un utilisateur
class TRH_formation_cv extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation_cv');
		parent::add_champs('date_debut,date_fin','type=date;');		//dates de début et de fin de la formation suivie
		parent::add_champs('libelleFormation','type=chaine;');		
		parent::add_champs('competenceFormation','type=chaine;');	
		parent::add_champs('commentaireFormation','type=chaine;');		//commentaire associé	
		parent::add_champs('lieuFormation','type=chaine;');		
		parent::add_champs('date_formationEcheance','type=date;');	
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('entity','type=entier;');
		parent::_init_vars();
		parent::start();
	}
}

//TRH_COMPETENCES
//définition de la classe pour rentrer les compétences d'un utilisateur
class TRH_competence_cv extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_competence_cv');
		
		parent::add_champs('libelleCompetence','type=chaine;');		
		parent::add_champs('fk_user_formation','type=entier;');	
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('entity','type=entier;');
		parent::_init_vars();
		parent::start();
	}
	
	function deleteEspace($competence){
		$compSansEspace=str_replace(' ','%',$competence);
		return "%".$compSansEspace."%";
	}
	
	//mise en forme de la recherche : suppression des espaces, rajout des %
	function miseEnForme($competence){
		$Tcompetence=array();
		foreach ($competence as $comp){
			
			$Tcompetence[]="%".$comp."%";
		}
		return $Tcompetence;
	}
	
	
	//fonction permettant de donner les utilisateurs ayant une compétence recherchée
	/*function separerOu(&$ATMdb, $competenceInit){

			global $conf;
			
			$competenceOu=$this->separerOu($competenceInit);
			//print_r($competenceOu);
			return $competenceOu;
			
		}*/
	
	//fonction permettant de donner les utilisateurs ayant une compétence recherchée
	function findProfile(&$ATMdb, $competenceOu){

			global $conf;
			
			
			$TUser=array();
			
			  $sql="SELECT * FROM llx_rh_competence_cv WHERE entity=".$conf->entity." AND ";
			  $k=0;
			 foreach($competenceOu as $comp){
			 	if($k==0){
			 		$sql.=" libelleCompetence LIKE '".$comp."'";
			 	}else{
			 		$sql.=" OR libelleCompetence LIKE '".$comp."'";
			 	}
				$k++;
			 	
			 }
			//echo $sql;
			$ATMdb->Execute($sql);
			$TUser=array();
			$k=0;
			while($ATMdb->Get_line()) {
						$TUser[]=$ATMdb->Get_field('fk_user');
			}
			
			return $TUser;

		}

	function separerOu($competenceOu){
		$competenceOu=explode("%ou%",$competenceOu); 
		return $competenceOu=$this->miseEnForme($competenceOu);
	}
	
}

