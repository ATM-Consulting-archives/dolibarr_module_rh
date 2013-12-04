<?php
/* Copyright (C) 2006-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *       \file       htdocs/webservices/server_agefodd.php
 *       \brief      File that is entry point to call Dolibarr WebServices
 *       \version    $Id: server_agefodd.php,v 1.7 2010/12/19 11:49:37 eldy Exp $
 */

// This is to make Dolibarr working with Plesk
set_include_path($_SERVER['DOCUMENT_ROOT'].'/htdocs');

require_once("../master.inc.php");
require_once(NUSOAP_PATH.'/nusoap.php');		// Include SOAP
require_once(DOL_DOCUMENT_ROOT."/core/lib/ws.lib.php");
require_once(DOL_DOCUMENT_ROOT."/agefodd/class/agefodd.class.php");


dol_syslog("Call Agefodd webservices interfaces");

// Enable and test if module web services is enabled
if (empty($conf->global->MAIN_MODULE_WEBSERVICES))
{
    $langs->load("admin");
    dol_syslog("Call Dolibarr webservices interfaces with module webservices disabled");
    print $langs->trans("WarningModuleNotActive",'WebServices').'.<br><br>';
    print $langs->trans("ToActivateModule");
    exit;
}

// Create the soap Object
$server = new nusoap_server();
$server->soap_defencoding='UTF-8';
$server->decode_utf8=false;
$ns='http://www.dolibarr.org/ns/';
$server->configureWSDL('WebServicesDolibarrAgefodd',$ns);
$server->wsdl->schemaTargetNamespace=$ns;


// Define WSDL Authentication object
$server->wsdl->addComplexType(
    'authentication',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'dolibarrkey' => array('name'=>'dolibarrkey','type'=>'xsd:string'),
    	'sourceapplication' => array('name'=>'sourceapplication','type'=>'xsd:string'),
    	'login' => array('name'=>'login','type'=>'xsd:string'),
    	'password' => array('name'=>'password','type'=>'xsd:string'),
        'entity' => array('name'=>'entity','type'=>'xsd:string'),
    )
);

// Define WSDL Return object
$server->wsdl->addComplexType(
    'result',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'result_code' => array('name'=>'result_code','type'=>'xsd:string'),
        'result_label' => array('name'=>'result_label','type'=>'xsd:string'),
    )
);

// Define other specific objects
$server->wsdl->addComplexType(
    'agefodd',
    'complexType',
    'struct',
    'all',
    '',
    array(
	    
'id' => array('name'=>'id','type'=>'xsd:string'),
'entity' => array('name'=>'entity','type'=>'xsd:string'),
'ref' => array('name'=>'ref','type'=>'xsd:string'),
'ref_obj' => array('name'=>'ref_obj','type'=>'xsd:string'),
'ref_interne' => array('name'=>'ref_interne','type'=>'xsd:string'),
'intitule' => array('name'=>'intitule','type'=>'xsd:string'),
'duree' => array('name'=>'duree','type'=>'xsd:string'),
'public' => array('name'=>'public','type'=>'xsd:string'),
'methode' => array('name'=>'methode','type'=>'xsd:string'),
'prerequis' => array('name'=>'prerequis','type'=>'xsd:string'),
'but' => array('name'=>'but','type'=>'xsd:string'),
'programme' => array('name'=>'programme','type'=>'xsd:string'),
'note1' => array('name'=>'note1','type'=>'xsd:string'),
'note2' => array('name'=>'note2','type'=>'xsd:string'),
'archive' => array('name'=>'archive','type'=>'xsd:string'),
'note_private' => array('name'=>'note_private','type'=>'xsd:string'),
'note_public' => array('name'=>'note_public','type'=>'xsd:string'),
'fk_product' => array('name'=>'fk_product','type'=>'xsd:string'),
'nb_subscribe_min' => array('name'=>'nb_subscribe_min','type'=>'xsd:string'),
'fk_formation_catalogue' => array('name'=>'fk_formation_catalogue','type'=>'xsd:string'),
'priorite' => array('name'=>'priorite','type'=>'xsd:string'),
'lines' => array('name'=>'lines','type'=>'xsd:string'),
'canvas' => array('name'=>'canvas','type'=>'xsd:string'),
'lastname' => array('name'=>'lastname','type'=>'xsd:string'),
'firstname' => array('name'=>'firstname','type'=>'xsd:string'),
'name' => array('name'=>'name','type'=>'xsd:string'),
'nom' => array('name'=>'nom','type'=>'xsd:string'),
'civility_id' => array('name'=>'civility_id','type'=>'xsd:string'),
'array_options' => array('name'=>'array_options','type'=>'xsd:string'),
'linkedObjectsIds' => array('name'=>'linkedObjectsIds','type'=>'xsd:string'),
'linkedObjects' => array('name'=>'linkedObjects','type'=>'xsd:string')

		
    //...
    )
);



// 5 styles: RPC/encoded, RPC/literal, Document/encoded (not WS-I compliant), Document/literal, Document/literal wrapped
// Style merely dictates how to translate a WSDL binding to a SOAP message. Nothing more. You can use either style with any programming model.
// http://www.ibm.com/developerworks/webservices/library/ws-whichwsdl/
$styledoc='rpc';       // rpc/document (document is an extend into SOAP 1.0 to support unstructured messages)
$styleuse='encoded';   // encoded/literal/literal wrapped
// Better choice is document/literal wrapped but literal wrapped not supported by nusoap.


// Register WSDL
$server->register(
    'getAgefodd',
    // Entry values
    array('authentication'=>'tns:authentication','id'=>'xsd:string','ref'=>'xsd:string','ref_ext'=>'xsd:string'),
    // Exit values
    array('result'=>'tns:result','agefodd'=>'tns:agefodd'),
    $ns,
    $ns.'#getAgefodd',
    $styledoc,
    $styleuse,
    'WS to get agefodd'
);

// Register WSDL
$server->register(
	'createAgefodd',
	// Entry values
	array('authentication'=>'tns:authentication','agefodd'=>'tns:agefodd'),
	// Exit values
	array('result'=>'tns:result','id'=>'xsd:string'),
	$ns,
	$ns.'#createAgefodd',
	$styledoc,
	$styleuse,
	'WS to create a agefodd'
);




/**
 * Get Agefodd
 *
 * @param	array		$authentication		Array of authentication information
 * @param	int			$id					Id of object
 * @param	string		$ref				Ref of object
 * @param	ref_ext		$ref_ext			Ref external of object
 * @return	mixed
 */
function getAgefodd($authentication,$id,$ref='',$ref_ext='')
{
    global $db,$conf,$langs;

    dol_syslog("Function: getAgefodd login=".$authentication['login']." id=".$id." ref=".$ref." ref_ext=".$ref_ext);

    if ($authentication['entity']) $conf->entity=$authentication['entity'];

    // Init and check authentication
    $objectresp=array();
    $errorcode='';$errorlabel='';
    $error=0;
    $fuser=check_authentication($authentication,$error,$errorcode,$errorlabel);
    // Check parameters
    if (! $error && (($id && $ref) || ($id && $ref_ext) || ($ref && $ref_ext)))
    {
        $error++;
        $errorcode='BAD_PARAMETERS'; $errorlabel="Parameter id, ref and ref_ext can't be both provided. You must choose one or other but not both.";
    }

    if (! $error)
    {
        $fuser->getrights();

        if ($fuser->rights->agefodd->read)
        {
            $agefodd=new Agefodd($db);
            $result=$agefodd->fetch($id,$ref,$ref_ext);
            if ($result > 0)
            {
                // Create
                $objectresp = array(
			    	'result'=>array('result_code'=>'OK', 'result_label'=>''),
			        'agefodd'=>array(
				    
'id' => $agefodd->id,
'entity' => $agefodd->entity,
'ref' => $agefodd->ref,
'ref_obj' => $agefodd->ref_obj,
'ref_interne' => $agefodd->ref_interne,
'intitule' => $agefodd->intitule,
'duree' => $agefodd->duree,
'public' => $agefodd->public,
'methode' => $agefodd->methode,
'prerequis' => $agefodd->prerequis,
'but' => $agefodd->but,
'programme' => $agefodd->programme,
'note1' => $agefodd->note1,
'note2' => $agefodd->note2,
'archive' => $agefodd->archive,
'note_private' => $agefodd->note_private,
'note_public' => $agefodd->note_public,
'fk_product' => $agefodd->fk_product,
'nb_subscribe_min' => $agefodd->nb_subscribe_min,
'fk_formation_catalogue' => $agefodd->fk_formation_catalogue,
'priorite' => $agefodd->priorite,
'lines' => $agefodd->lines,
'canvas' => $agefodd->canvas,
'lastname' => $agefodd->lastname,
'firstname' => $agefodd->firstname,
'name' => $agefodd->name,
'nom' => $agefodd->nom,
'civility_id' => $agefodd->civility_id,
'array_options' => $agefodd->array_options,
'linkedObjectsIds' => $agefodd->linkedObjectsIds,
'linkedObjects' => $agefodd->linkedObjects

				    
                    //...
                    )
                );
            }
            else
            {
                $error++;
                $errorcode='NOT_FOUND'; $errorlabel='Object not found for id='.$id.' nor ref='.$ref.' nor ref_ext='.$ref_ext;
            }
        }
        else
        {
            $error++;
            $errorcode='PERMISSION_DENIED'; $errorlabel='User does not have permission for this request';
        }
    }

    if ($error)
    {
        $objectresp = array('result'=>array('result_code' => $errorcode, 'result_label' => $errorlabel));
    }

    return $objectresp;
}


/**
 * Create Agefodd
 *
 * @param	array		$authentication		Array of authentication information
 * @param	Agefodd	$agefodd		    $agefodd
 * @return	array							Array result
 */
function createAgefodd($authentication,$agefodd)
{
	global $db,$conf,$langs;

	$now=dol_now();

	dol_syslog("Function: createAgefodd login=".$authentication['login']);

	if ($authentication['entity']) $conf->entity=$authentication['entity'];

	// Init and check authentication
	$objectresp=array();
	$errorcode='';$errorlabel='';
	$error=0;
	$fuser=check_authentication($authentication,$error,$errorcode,$errorlabel);
	// Check parameters


	if (! $error)
	{
		$newobject=new Agefodd($db);
		
		$newobject->id=$agefodd->id;
		$newobject->entity=$agefodd->entity;
		$newobject->ref=$agefodd->ref;
		$newobject->ref_obj=$agefodd->ref_obj;
		$newobject->ref_interne=$agefodd->ref_interne;
		$newobject->intitule=$agefodd->intitule;
		$newobject->duree=$agefodd->duree;
		$newobject->public=$agefodd->public;
		$newobject->methode=$agefodd->methode;
		$newobject->prerequis=$agefodd->prerequis;
		$newobject->but=$agefodd->but;
		$newobject->programme=$agefodd->programme;
		$newobject->note1=$agefodd->note1;
		$newobject->note2=$agefodd->note2;
		$newobject->archive=$agefodd->archive;
		$newobject->note_private=$agefodd->note_private;
		$newobject->note_public=$agefodd->note_public;
		$newobject->fk_product=$agefodd->fk_product;
		$newobject->nb_subscribe_min=$agefodd->nb_subscribe_min;
		$newobject->fk_formation_catalogue=$agefodd->fk_formation_catalogue;
		$newobject->priorite=$agefodd->priorite;
		$newobject->lines=$agefodd->lines;
		$newobject->canvas=$agefodd->canvas;
		$newobject->lastname=$agefodd->lastname;
		$newobject->firstname=$agefodd->firstname;
		$newobject->name=$agefodd->name;
		$newobject->nom=$agefodd->nom;
		$newobject->civility_id=$agefodd->civility_id;
		$newobject->array_options=$agefodd->array_options;
		$newobject->linkedObjectsIds=$agefodd->linkedObjectsIds;
		$newobject->linkedObjects=$agefodd->linkedObjects;

		
		//...

		$db->begin();

		$result=$newobject->create($fuser);
		if ($result <= 0)
		{
			$error++;
		}

		if (! $error)
		{
			$db->commit();
			$objectresp=array('result'=>array('result_code'=>'OK', 'result_label'=>''),'id'=>$newobject->id,'ref'=>$newobject->ref);
		}
		else
		{
			$db->rollback();
			$error++;
			$errorcode='KO';
			$errorlabel=$newobject->error;
		}
	}

	if ($error)
	{
		$objectresp = array('result'=>array('result_code' => $errorcode, 'result_label' => $errorlabel));
	}

	return $objectresp;
}

// Return the results.
$server->service($HTTP_RAW_POST_DATA);

?>
