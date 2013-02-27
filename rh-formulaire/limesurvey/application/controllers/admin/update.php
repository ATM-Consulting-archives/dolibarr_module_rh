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
*/

/**
* Update Controller
*
* This controller performs updates
*
* @package		LimeSurvey
* @subpackage	Backend
*/
class update extends Survey_Common_Action
{
    /**
    * Default Controller Action
    */
    function index($sSubAction = null)
    {
        $this->_RunUpdaterUpdate();
        Yii::import('application.libraries.admin.http.httpRequestIt');

        $clang = $this->getController()->lang;
        $iCurrentBuildnumber = Yii::app()->getConfig("buildnumber");
        $tempdir = Yii::app()->getConfig("tempdir");
        $iDestinationBuild= getGlobalSetting("updatebuild");

        $updatekey = $this->_getUpdateKey($sSubAction);
        $error = false;

        if ( $updatekey != '' ) {
            if (!is_writable($tempdir)) {
                $error = true;
            }
            if (!is_writable(APPPATH . 'config/version.php')) {
                $error = true;
            }

            list($httperror, $changelog, $cookies) = $this->_getChangelog($iCurrentBuildnumber, $iDestinationBuild, $updatekey);
        }

        $aData['error'] = $error;
        $aData['tempdir'] = $tempdir;
        $aData['updatekey'] = $updatekey;
        $aData['changelog'] = isset($changelog) ? $changelog : '';
        $aData['httperror'] = isset($httperror) ? $httperror : '';

        $this->_renderWrappedTemplate('update', 'update', $aData);
    }

    private function _getChangedFiles($buildnumber, $updaterversion, $updatekey)
    {
        Yii::import('application.libraries.admin.http.httpRequestIt');
        $http = new httpRequestIt;
        $httperror = $this->_requestChangedFiles($http, $buildnumber, $updaterversion, $updatekey);

        if ($httperror != '') {
            return array($httperror, null);
        }
        return $this->_readChangelog($http);
    }
    
    private function _getChangelog($buildnumber, $updaterversion, $updatekey)
    {
        Yii::import('application.libraries.admin.http.httpRequestIt');
        $http = new httpRequestIt;
        $httperror = $this->_requestChangelog($http, $buildnumber, $updaterversion, $updatekey);

        if ($httperror != '') {
            return array($httperror, null);
        }
        return $this->_readChangelog($http);
    }

    private function _readChangelog(httpRequestIt $http)
    {
        $szLines = '';
        $szResponse = '';
        for (; ;) {
            $httperror = $http->ReadReplyBody($szLines, 10000);
            if ($httperror != "" || strlen($szLines) == 0) {
                $changelog = json_decode($szResponse, true);
                $http->SaveCookies($cookies);
                return array($httperror, $changelog, $cookies);
            }
            $szResponse .= $szLines;
        }
    }

    private function _requestChangelog(httpRequestIt $http, $buildnumber, $updaterversion, $updatekey)
    {
        $http->timeout = 0;
        $http->data_timeout = 0;
        $http->user_agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)';
        $http->GetRequestArguments('http://update.limesurvey.org/updates/changelog/' . $buildnumber . '/' . $updaterversion . '/' . $updatekey, $arguments);

        $http->Open($arguments);

        return $http->SendRequest($arguments);
    }
    
    private function _requestChangedFiles(httpRequestIt $http, $buildnumber, $updaterversion, $updatekey)
    {
        $http->timeout = 0;
        $http->data_timeout = 0;
        $http->user_agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)';
        $http->GetRequestArguments('http://update.limesurvey.org/updates/update/' . $buildnumber . '/' . $updaterversion . '/' . $updatekey, $arguments);

        $http->Open($arguments);

        return $http->SendRequest($arguments);
    }
    

    private function _getUpdateKey($sSubAction)
    {
        $updatekey = getGlobalSetting("updatekey");
        if ($sSubAction == 'keyupdate') {
            $updatekey = sanitize_paranoid_string($_POST['updatekey']);
            setGlobalSetting('updatekey', $updatekey);
            Yii::app()->setConfig("updatekey", $updatekey);
            return $updatekey;
        }
        return $updatekey;
    }


    function step2()
    {
        
        $clang = $this->getController()->lang;
        $buildnumber = Yii::app()->getConfig("buildnumber");
        $updatebuild = getGlobalSetting("updatebuild");
        $updatekey =getGlobalSetting("updatekey");

        list($error, $updateinfo, $cookies) = $this->_getChangedFiles($buildnumber, $updatebuild, $updatekey);
        $aData = $this->_getFileStatus($updateinfo);
        $aReadOnlyFiles=array_unique($aData['readonlyfiles']);
        sort($aReadOnlyFiles);
        $aData['readonlyfiles']=$aReadOnlyFiles;
        Yii::app()->session['updateinfo'] = $updateinfo;
        Yii::app()->session['updatesession'] = $cookies;

        $aData['error'] = $error;
        $aData['updateinfo'] = $updateinfo;
        $this->_renderWrappedTemplate('update', 'step2', $aData);
    }

    private function _getFileStatus($updateinfo)
    {
        // okay, updateinfo now contains all necessary updateinformation
        // Now check if the existing files have the mentioned checksum

        if (!isset($updateinfo['files'])) {
            return array();
        }

        $rootdir = Yii::app()->getConfig("rootdir");
        $existingfiles = array();
        $modifiedfiles = array();
        $readonlyfiles = array();

        foreach ($updateinfo['files'] as $afile)
        {
            $this->_checkFile($afile, $rootdir, $readonlyfiles, $existingfiles, $modifiedfiles);
        }
        return array('readonlyfiles'=>$readonlyfiles,
        'modifiedfiles'=>$modifiedfiles,
        'existingfiles'=>$existingfiles)
        ;
    }

    private function _checkFile($file, $rootdir, &$readonlyfiles, &$existingfiles, &$modifiedfiles)
    {
        $this->_checkReadOnlyFile($file, $rootdir, $readonlyfiles);


        if ($file['type'] == 'A' && file_exists($rootdir . $file['file'])) {
            //A new file, check if this already exists
            $existingfiles[] = $file;
        }
        elseif (($file['type'] == 'D' || $file['type'] == 'M') && is_file($rootdir . $file['file']) && sha1_file($rootdir . $file['file']) != $file['checksum']) {
            // A deleted or modified file - check if it is unmodified
            $modifiedfiles[] = $file;
        }
    }

    private function _checkReadOnlyFile($file, $rootdir, &$readonlyfiles)
    {
        if ($file['type'] == 'A' && !file_exists($rootdir . $file['file']) || ($file['type'] == 'D' && file_exists($rootdir . $file['file']))) {
            $searchpath = $rootdir . $file['file'];
            $is_writable = is_writable(dirname($searchpath));
            while (!$is_writable && strlen($searchpath) > strlen($rootdir))
            {
                $searchpath = dirname($searchpath);
                if (file_exists($searchpath)) {
                    $is_writable = is_writable($searchpath);
                    break;

                }
            }

            if (!$is_writable) {
                $readonlyfiles[] = $searchpath;
            }
        }
        elseif (file_exists($rootdir . $file['file']) && !is_writable($rootdir . $file['file'])) {
            $readonlyfiles[] = $rootdir . $file['file'];
        }
    }

    function step3()
    {
        $clang = $this->getController()->lang;
        $buildnumber = Yii::app()->getConfig("buildnumber");
        $tempdir = Yii::app()->getConfig("tempdir");
        $updatekey = getGlobalSetting("updatekey");
        $updatebuild = getGlobalSetting("updatebuild");
        //$_POST=$this->input->post();
        $rootdir = Yii::app()->getConfig("rootdir");
        $publicdir = Yii::app()->getConfig("publicdir");
        $tempdir = Yii::app()->getConfig("tempdir");
        $aDatabasetype = Yii::app()->db->getDriverName();
        $aData = array('clang' => $clang);
        // Request the list with changed files from the server

        if (!isset( Yii::app()->session['updateinfo']))
        {
            if ($updateinfo['error']==1)
            {
                setGlobalSetting('updatekey','');
            }
        }
        else
        {
            $updateinfo=Yii::app()->session['updateinfo'];
        }

        $aData['updateinfo'] = $updateinfo;

        // okay, updateinfo now contains all necessary updateinformation
        // Create DB and file backups now

        $basefilename = dateShift(date("Y-m-d H:i:s"), "Y-m-d", Yii::app()->getConfig('timeadjust')).'_'.md5(uniqid(rand(), true));
        //Now create a backup of the files to be delete or modified

        $filestozip=array();

        foreach ($updateinfo['files'] as $file)
        {
            if (is_file($publicdir.$file['file'])===true) // Sort out directories
            {
                $filestozip[]=$publicdir.$file['file'];
            }
        }

        Yii::app()->loadLibrary("admin/pclzip/pclzip");
        $archive = new PclZip($tempdir.DIRECTORY_SEPARATOR.'LimeSurvey_files_backup_'.$basefilename.'.zip');

        $v_list = $archive->add($filestozip, PCLZIP_OPT_REMOVE_PATH, $publicdir);

        if ($v_list == 0) {
            die("Error : ".$archive->errorInfo(true));
        }
        $aData['sFilesArchive']=$tempdir.DIRECTORY_SEPARATOR.'LimeSurvey_files_backup_'.$basefilename.'.zip';

        $aData['databasetype'] = $aDatabasetype;

        //TODO: Yii provides no function to backup the database. To be done after dumpdb is ported
        if (in_array($aDatabasetype, array('mysql', 'mysqli')))
        {
            if ((in_array($aDatabasetype, array('mysql', 'mysqli'))) && Yii::app()->getConfig('demoMode') != true) {
                Yii::app()->loadHelper("admin/backupdb");
                $sfilename = $tempdir.DIRECTORY_SEPARATOR."backup_db_".randomChars(20)."_".dateShift(date("Y-m-d H:i:s"), "Y-m-d", Yii::app()->getConfig('timeadjust')).".sql";
                $dfilename = $tempdir.DIRECTORY_SEPARATOR."LimeSurvey_database_backup_".$basefilename.".sql.gz";

                outputDatabase('',false,$sfilename);

                $archive = new PclZip($dfilename);
                $v_list = $archive->add(array($sfilename), PCLZIP_OPT_REMOVE_PATH, $tempdir);
                unlink($sfilename);
                if ($v_list == 0) {
                    die("Error : ".$archive->errorInfo(true));
                }
                $aData['sSQLArchive']=$dfilename;
            }
        }

        $this->_renderWrappedTemplate('update', 'step3', $aData);
    }


    function step4()
    {
        $clang = $this->getController()->lang;
        $buildnumber = Yii::app()->getConfig("buildnumber");
        $tempdir = Yii::app()->getConfig("tempdir");
        $updatekey = getGlobalSetting("updatekey");
        $updatebuild = getGlobalSetting("updatebuild");

        $rootdir = Yii::app()->getConfig("rootdir");
        $publicdir = Yii::app()->getConfig("publicdir");
        $tempdir = Yii::app()->getConfig("tempdir");
        $aDatabasetype = Yii::app()->db->getDriverName();
        // Request the list with changed files from the server
        $aData = array();

        if (!isset( Yii::app()->session['updateinfo']))
        {
            if ($updateinfo['error']==1)
            {
                setGlobalSetting('updatekey','');
            }
        }
        else
        {
            $updateinfo=Yii::app()->session['updateinfo'];
        }
        // this is the last step - Download the zip file, unpack it and replace files accordingly
        // Create DB and file backups now

        $downloaderror=false;
        Yii::import('application.libraries.admin.http.httpRequestIt');
        $http=new httpRequestIt;

        // Allow redirects
        $http->follow_redirect=1;
        /* Connection timeout */
        $http->timeout=0;
        /* Data transfer timeout */
        $http->data_timeout=0;
        $http->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
        $http->GetRequestArguments("http://update.limesurvey.org/updates/download/{$updateinfo['downloadid']}",$arguments);
        $http->RestoreCookies(Yii::app()->session['updatesession']);

        $error=$http->Open($arguments);
        $error=$http->SendRequest($arguments);
        $http->ReadReplyHeaders($headers);
        if ($headers['content-type']=='text/html')
        {
            @unlink($tempdir.'/update.zip');
        }
        else if($error=='') {
                $body='';
                $pFile = fopen($tempdir.'/update.zip', 'w');
                for(;;){
                    $error = $http->ReadReplyBody($body,100000);
                    if($error != "" || strlen($body)==0) break;
                    fwrite($pFile, $body);
            }
            fclose($pFile);
        }
        else
        {
            print( $error );
        }

        // Now remove all files that are to be deleted according to update process
        foreach ($updateinfo['files'] as $afile)
        {
            if ($afile['type']=='D' && file_exists($rootdir.$afile['file']))
            {
                if (is_file($rootdir.$afile['file']))
                {
                    @unlink($rootdir.$afile['file']);
                }
                else{
                    rmdirr($rootdir.$afile['file']);
                }
            }
        }

        //Now unzip the new files over the existing ones.
        $new_files = false;
        if (file_exists($tempdir.'/update.zip')){
            Yii::app()->loadLibrary("admin/pclzip/pclzip");
            $archive = new PclZip($tempdir.'/update.zip');
            if ($archive->extract(PCLZIP_OPT_PATH, $rootdir.'/', PCLZIP_OPT_REPLACE_NEWER)== 0) {
                die("Error : ".$archive->errorInfo(true));
            }
            else
            {
                $new_files = true;
                unlink($tempdir.'/update.zip');
            }
        }
        else
        {
            $downloaderror=true;
        }

        $aData['new_files'] = $new_files;
        $aData['downloaderror'] = $downloaderror;

        //  PclTraceDisplay();

        // Now we have to update version.php
        if (!$downloaderror)
        {
            @ini_set('auto_detect_line_endings', true);
            $versionlines=file($rootdir.'/application/config/version.php');
            $handle = fopen($rootdir.'/application/config/version.php', "w");
            foreach ($versionlines as $line)
            {
                if(strpos($line,'buildnumber')!==false)
                {
                    $line='$config[\'buildnumber\'] = '.Yii::app()->session['updateinfo']['toversion'].';'."\r\n";
                }
                fwrite($handle,$line);
            }
            fclose($handle);
        }
        setGlobalSetting('updatelastcheck','1980-01-01 00:00');
        setGlobalSetting('updateavailable','0');
        setGlobalSetting('updatebuild','');
        setGlobalSetting('updateversion','');
        // We create this new language object here because the language files might have been overwritten earlier
        // and the pointers to the file from the application language are not valid anymore 
        $aLanguage = new Limesurvey_lang(Yii::app()->session['adminlang']);
        $aData['clang'] = $aLanguage;

        $this->_renderWrappedTemplate('update', 'step4', $aData);
    }

    private function _RunUpdaterUpdate()
    {
        $clang = $this->getController()->lang;
        $versionnumber = Yii::app()->getConfig("versionnumber");
        $buildnumber = Yii::app()->getConfig("buildnumber");
        $tempdir = Yii::app()->getConfig("tempdir");

        Yii::import('application.libraries.admin.http.httpRequestIt');
        $oHTTPRequest=new httpRequestIt;
        
        /* Connection timeout */
        $oHTTPRequest->timeout=0;
        /* Data transfer timeout */
        $oHTTPRequest->data_timeout=0;
        $oHTTPRequest->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
        $oHTTPRequest->GetRequestArguments("http://update.limesurvey.org?updaterbuild={$buildnumber}",$arguments);

        $updateinfo=false;
        $error=$oHTTPRequest->Open($arguments);
        $error=$oHTTPRequest->SendRequest($arguments);

        $oHTTPRequest->ReadReplyHeaders($headers);


        if($error=="") {
            $body=''; $full_body='';
            for(;;){
                $error = $oHTTPRequest->ReadReplyBody($body,10000);
                if($error != "" || strlen($body)==0) break;
                $full_body .= $body;
            }
            $updateinfo=json_decode($full_body,true);
            if ($oHTTPRequest->response_status!='200')
            {
                $updateinfo['errorcode']=$oHTTPRequest->response_status;
                $updateinfo['errorhtml']=$full_body;
            }
        }
        else
        {
            $updateinfo['errorcode']=$error;
            $updateinfo['errorhtml']=$error;
        }
        unset( $oHTTPRequest );
        if ((int)$updateinfo['UpdaterRevision']<=$buildnumber)
        {
            // There is no newer updater version on the server
            return true;
        }

        if (!is_writable($tempdir) || !is_writable(APPPATH.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'update.php'))
        {
            $error=true;
        }

        //  Download the zip file, unpack it and replace the updater file accordingly
        // Create DB and file backups now

        $downloaderror=false;
        Yii::import('application.libraries.admin.http.httpRequestIt');
        $oHTTPRequest=new httpRequestIt;

        // Allow redirects
        $oHTTPRequest->follow_redirect=1;
        /* Connection timeout */
        $oHTTPRequest->timeout=0;
        /* Data transfer timeout */
        $oHTTPRequest->data_timeout=0;
        $oHTTPRequest->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
        $oHTTPRequest->GetRequestArguments("http://update.limesurvey.org/updates/downloadupdater/{$this->updaterversion}",$arguments);

        $oHTTPRequesterror=$oHTTPRequest->Open($arguments);
        $oHTTPRequesterror=$oHTTPRequest->SendRequest($arguments);
        $oHTTPRequest->ReadReplyHeaders($headers);
        if ($headers['content-type']=='text/html')
        {
            @unlink($tempdir.'/updater.zip');
        }
        elseif($oHTTPRequesterror=='') {
            $body=''; $full_body='';
            for(;;){
                $oHTTPRequesterror = $oHTTPRequest->ReadReplyBody($body,100000);
                if($oHTTPRequesterror != "" || strlen($body)==0) break;
                $full_body .= $body;
            }
            file_put_contents($tempdir.'/updater.zip',$full_body);
        }
        $aData['httperror'] = $oHTTPRequesterror;

        //Now unzip the new updater over the existing ones.
        if (file_exists($tempdir.'/updater.zip')){
            Yii::app()->loadLibrary("admin/pclzip/pclzip",array('p_zipname' => $tempdir.'/updater.zip'));
            $archive = new PclZip(array('p_zipname' => $tempdir.'/updater.zip'));
            if ($archive->extract(PCLZIP_OPT_PATH, APPPATH.'/controllers/admin/', PCLZIP_OPT_REPLACE_NEWER)== 0) {
                die("Error : ".$archive->errorInfo(true));
            }
            else
            {
                unlink($tempdir.'/updater.zip');
            }
            $updater_exists = true;
        }
        else
        {
            $updater_exists = false;
            $error=true;
        }
        $aData['updater_exists'] = $updater_exists;
    }

    /**
    * Update database
    */
    function db($continue = null)
    {
        $clang = $this->getController()->lang;
        Yii::app()->loadHelper("update/update");
        if(isset($continue) && $continue=="yes")
        {
            $aViewUrls['output'] = CheckForDBUpgrades($continue);
            updateCheck();
            $aData['display']['header'] = false;
        }
        else
        {
            $aData['display']['header'] = true;
            $aViewUrls['output'] = CheckForDBUpgrades();
        }

        $this->_renderWrappedTemplate('update', $aViewUrls, $aData);
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'update', $aViewUrls = array(), $aData = array())
    {
        $aData['display']['menu_bars'] = false;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

}
