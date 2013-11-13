<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *	$Id$
 */

abstract class LSYii_Controller extends CController
{
	/**
	 * Basic initialiser to the base controller class
	 *
	 * @access public
	 * @param string $id
	 * @param CWebModule $module
	 * @return void
	 */
	public function __construct($id, $module = null)
	{
		parent::__construct($id, $module);
		$this->_checkInstallation();

        Yii::app()->session->init();
		$this->loadLibrary('LS.LS');
		$this->loadHelper('globalsettings');
		$this->loadHelper('common');
		$this->loadHelper('expressions.em_manager');
		$this->loadHelper('replacements');
		$this->_init();
	}

	/**
	 * Check that installation was already done by looking for config.php
	 * Will redirect to the installer script if not exists.
	 *
	 * @access protected
	 * @return void
	 */
	protected function _checkInstallation()
	{
		$file_name = Yii::app()->getConfig('rootdir').'/application/config/config.php';
		if (!file_exists($file_name))
        {
			$this->redirect($this->createUrl('/installer'));
        }
	}

	/**
	 * Loads a helper
	 *
	 * @access public
	 * @param string $helper
	 * @return void
	 */
	public function loadHelper($helper)
	{
		Yii::app()->loadHelper($helper);
	}

	/**
	 * Loads a library
	 *
	 * @access public
	 * @param string $helper
	 * @return void
	 */
	public function loadLibrary($library)
	{
		Yii::app()->loadLibrary($library);
	}

	protected function _init()
	{
		// Check for most necessary requirements
		// Now check for PHP & db version
		// Do not localize/translate this!

		$dieoutput='';
		if (version_compare(PHP_VERSION, '5.1.6', '<'))
			$dieoutput .= 'This script can only be run on PHP version 5.1.6 or later! Your version: '.PHP_VERSION.'<br />';

		if (!function_exists('mb_convert_encoding'))
			$dieoutput .= "This script needs the PHP Multibyte String Functions library installed: See <a href='http://docs.limesurvey.org/tiki-index.php?page=Installation+FAQ'>FAQ</a> and <a href='http://de.php.net/manual/en/ref.mbstring.php'>PHP documentation</a><br />";

		if ($dieoutput != '')
			throw new CException($dieoutput);

   		if (ini_get("max_execution_time") < 1200) @set_time_limit(1200); // Maximum execution time - works only if safe_mode is off
        if ((int)substr(ini_get("memory_limit"),0,-1) < (int) Yii::app()->getConfig('memory_limit')) @ini_set("memory_limit",Yii::app()->getConfig('memory_limit').'M'); // Set Memory Limit for big surveys

		// The following function (when called) includes FireBug Lite if true
		defined('FIREBUG') or define('FIREBUG' , Yii::app()->getConfig('use_firebug_lite'));

		// Deal with server systems having not set a default time zone
		if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
			@date_default_timezone_set(@date_default_timezone_get());

		//Every 50th time clean up the temp directory of old files (older than 1 day)
		//depending on the load the  probability might be set higher or lower
		if (rand(1,50)==1)
		{
			cleanTempDirectory();
		}

		//GlobalSettings Helper
		Yii::import("application.helpers.globalsettings");

		enforceSSLMode();// This really should be at the top but for it to utilise getGlobalSetting() it has to be here

        if (Yii::app()->getConfig('debug')==1) {//For debug purposes - switch on in config.php
            @ini_set("display_errors", 1);
            error_reporting(E_ALL);
        }
        elseif (Yii::app()->getConfig('debug')==2) {//For debug purposes - switch on in config.php
            @ini_set("display_errors", 1);
            error_reporting(E_ALL | E_STRICT);
        }
        else {
            @ini_set("display_errors", 0);
            error_reporting(0);
        }
        
		//SET LOCAL TIME
		$timeadjust = Yii::app()->getConfig("timeadjust");
		if (substr($timeadjust,0,1)!='-' && substr($timeadjust,0,1)!='+') {$timeadjust='+'.$timeadjust;}
		if (strpos($timeadjust,'hours')===false && strpos($timeadjust,'minutes')===false && strpos($timeadjust,'days')===false)
		{
			Yii::app()->setConfig("timeadjust",$timeadjust.' hours');
		}

	}
}
