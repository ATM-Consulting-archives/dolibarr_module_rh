<?php if ( !defined('BASEPATH')) exit('No direct script access allowed');
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
*     $Id$
*/

/**
* Tokens Controller
*
* This controller performs token actions
*
* @package        LimeSurvey
* @subpackage    Backend
*/
class tokens extends Survey_Common_Action
{

    /**
    * Show token index page, handle token database
    */
    function index($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $clang = $this->getController()->lang;
        $thissurvey = getSurveyInfo($iSurveyId);
        if (!hasSurveyPermission($iSurveyId, 'tokens', 'read') && !hasSurveyPermission($iSurveyId, 'tokens', 'create') && !hasSurveyPermission($iSurveyId, 'tokens', 'update')
            && !hasSurveyPermission($iSurveyId, 'tokens', 'export') && !hasSurveyPermission($iSurveyId, 'tokens', 'import')
            && !hasSurveyPermission($iSurveyID, 'surveysettings', 'update')
            ) 
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        Yii::app()->loadHelper("surveytranslator");

        $aData['surveyprivate'] = $thissurvey['anonymized'];

        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        else
        {
            //Check that the tokens table has the required fields
            Tokens_dynamic::model($iSurveyId)->checkColumns();
            $aData['thissurvey'] = $thissurvey;
            $aData['surveyid'] = $iSurveyId;
            $aData['queries'] = Tokens_dynamic::model($iSurveyId)->summary();

            $this->_renderWrappedTemplate('token', array('tokenbar', 'tokensummary'), $aData);
        }
    }

    /**
    * tokens::bounceprocessing()
    *
    * @return void
    */
    function bounceprocessing($iSurveyId)
    {
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            $clang->eT("No token table.");
            return;
        }
        $iSurveyId = sanitize_int($iSurveyId);
        $clang = $this->getController()->lang;
        $thissurvey = getSurveyInfo($iSurveyId);

        if (!hasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            $clang->eT("We are sorry but you don't have permissions to do this.");
            return;
        }
        if ($thissurvey['bounceprocessing'] != 'N' ||  ($thissurvey['bounceprocessing'] == 'G' && getGlobalSetting('bounceaccounttype') != 'off'))
        {
            if (!function_exists('imap_open'))
            {
                   $clang->eT("The imap PHP library is not installed. Please contact your system administrator.");
                   return;
            }
            $bouncetotal = 0;
            $checktotal = 0;
            if ($thissurvey['bounceprocessing'] == 'G')
            {
                $accounttype=strtoupper(getGlobalSetting('bounceaccounttype'));
                $hostname = getGlobalSetting('bounceaccounthost');
                $username = getGlobalSetting('bounceaccountuser');
                $pass = getGlobalSetting('bounceaccountpass');
                $hostencryption=strtoupper(getGlobalSetting('bounceencryption'));
            }
            else
            {
                $accounttype=strtoupper($thissurvey['bounceaccounttype']);
                $hostname = $thissurvey['bounceaccounthost'];
                $username = $thissurvey['bounceaccountuser'];
                $pass = $thissurvey['bounceaccountpass'];
                $hostencryption=strtoupper($thissurvey['bounceaccountencryption']);
            }

            @list($hostname, $port) = split(':', $hostname);
            if (empty($port))
            {
                if ($accounttype == "IMAP")
                {
                    switch ($hostencryption)
                    {
                        case "OFF":
                            $hostname = $hostname . ":143";
                            break;
                        case "SSL":
                            $hostname = $hostname . ":993";
                            break;
                        case "TLS":
                            $hostname = $hostname . ":993";
                            break;
                    }
                }
                else
                {
                    switch ($hostencryption)
                    {
                        case "OFF":
                            $hostname = $hostname . ":110";
                            break;
                        case "SSL":
                            $hostname = $hostname . ":995";
                            break;
                        case "TLS":
                            $hostname = $hostname . ":995";
                            break;
                    }
                }
            }

            $flags = "";
            switch ($accounttype)
            {
                case "IMAP":
                    $flags.="/imap";
                    break;
                case "POP":
                    $flags.="/pop3";
                    break;
            }
            switch ($hostencryption) // novalidate-cert to have personal CA , maybe option.
            {
                case "OFF":
                    $flags.="/notls"; // Really Off
                    break;
                case "SSL":
                    $flags.="/ssl/novalidate-cert";
                    break;
                case "TLS":
                    $flags.="/tls/novalidate-cert";
                    break;
            }

            if ($mbox = @imap_open('{' . $hostname . $flags . '}INBOX', $username, $pass))
            {
                imap_errors();
                $count = imap_num_msg($mbox);
                if ($count>0)
                {
                    $lasthinfo = imap_headerinfo($mbox, $count);
                    $datelcu = strtotime($lasthinfo->date);
                    $datelastbounce = $datelcu;
                    $lastbounce = $thissurvey['bouncetime'];
                    while ($datelcu > $lastbounce)
                    {
                        @$header = explode("\r\n", imap_body($mbox, $count, FT_PEEK)); // Don't mark messages as read
                        foreach ($header as $item)
                        {
                            if (preg_match('/^X-surveyid/', $item))
                            {
                                $iSurveyIdBounce = explode(": ", $item);
                            }
                            if (preg_match('/^X-tokenid/', $item))
                            {
                                $tokenBounce = explode(": ", $item);
                                if ($iSurveyId == $iSurveyIdBounce[1])
                                {
                                    $aData = array(
                                    'emailstatus' => 'bounced'
                                    );
                                    $condn = array('token' => $tokenBounce[1]);

                                    $record = Tokens_dynamic::model($iSurveyId)->findByAttributes($condn);
                                    foreach ($aData as $k => $v)
                                        $record->$k = $v;
                                    $record->save();

                                    $readbounce = imap_body($mbox, $count); // Put read
                                    if (isset($thissurvey['bounceremove']) && $thissurvey['bounceremove']) // TODO Y or just true, and a imap_delete
                                    {
                                        $deletebounce = imap_delete($mbox, $count); // Put delete
                                    }
                                    $bouncetotal++;
                                }
                            }
                        }
                        $count--;
                        @$lasthinfo = imap_headerinfo($mbox, $count);
                        @$datelc = $lasthinfo->date;
                        $datelcu = strtotime($datelc);
                        $checktotal++;
                    }
                }
                @imap_close($mbox);
                $condn = array('sid' => $iSurveyId);
                $survey = Survey::model()->findByAttributes($condn);
                $survey->bouncetime = $datelastbounce;
                $survey->save();

                if ($bouncetotal > 0)
                {
                    printf($clang->gT("%s messages were scanned out of which %s were marked as bounce by the system."), $checktotal, $bouncetotal);
                }
                else
                {
                    printf($clang->gT("%s messages were scanned, none were marked as bounce by the system."), $checktotal);
                }
            }
            else
            {
                $clang->eT("Please check your settings");
            }
        }
        else
        {
            $clang->eT("Bounce processing is deactivated either application-wide or for this survey in particular.");
            return;
        }


        exit; // if bounceprocessing : javascript : no more todo
    }

    /**
    * Browse Tokens
    */
    function browse($iSurveyId, $limit = 50, $start = 0, $order = false, $searchstring = false)
    {
        $clang = $this->getController()->lang;
        $iSurveyId = sanitize_int($iSurveyId);
        /* Check permissions */
        if (!hasSurveyPermission($iSurveyId, 'tokens', 'read'))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/tokens/sa/index/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        // Javascript
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts') . "tokens.js");
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . "jquery/jquery.multiselect.min.js");
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . "jquery/jqGrid/js/i18n/grid.locale-en.js");
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . "jquery/jqGrid/js/jquery.jqGrid.min.js");
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . "jquery/jquery-ui-timepicker-addon.js");
        // CSS
//        $this->getController()->_css_admin_includes(Yii::app()->getConfig('generalscripts') . "jquery/css/jquery.multiselect.css");
//        $this->getController()->_css_admin_includes(Yii::app()->getConfig('generalscripts') . "jquery/css/jquery.multiselect.filter.css");
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('generalscripts') . "jquery/jqGrid/css/ui.jqgrid.css");
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('generalscripts') . "jquery/jqGrid/css/jquery.ui.datepicker.css");
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('adminstyleurl') . "displayParticipants.css");
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('adminstyleurl') . "jquery-ui/jquery-timepicker.css");

        Yii::app()->loadHelper('surveytranslator');
        Yii::import('application.libraries.Date_Time_Converter', true);
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

        $limit = (int) $limit;
        $start = (int) $start;
        $tkcount = Tokens_dynamic::model($iSurveyId)->count();
        $next = $start + $limit;
        $last = $start - $limit;
        $end = $tkcount - $limit;

        if ($end < 0)
        {
            $end = 0;
        }
        if ($last < 0)
        {
            $last = 0;
        }
        if ($next >= $tkcount)
        {
            $next = $tkcount - $limit;
        }
        if ($end < 0)
        {
            $end = 0;
        }

        $sBaseLanguage = Survey::model()->findByPk($iSurveyId)->language;
        $aData['next'] = $next;
        $aData['last'] = $last;
        $aData['end'] = $end;
        $limit = Yii::app()->request->getPost('limit');
        $start = Yii::app()->request->getPost('start');
        $searchstring = Yii::app()->request->getPost('searchstring');
        $order = Yii::app()->request->getPost('order');
        $order = preg_replace('/[^_ a-z0-9-]/i', '', $order);
        if ($order == '')
        {
            $order = 'tid';
        }

        $iquery = '';
        if (!empty($searchstring))
        {
            $idata = array("firstname", "lastname", "email", "emailstatus", "token");
            $iquery = array();
            foreach ($idata as $k)
                $iquery[] = $k . ' LIKE "' . $searchstring . '%"';
            $iquery = '(' . implode(' OR ', $iquery) . ')';
        }

        $tokens = Tokens_dynamic::model($iSurveyId)->findAll(array('condition' => $iquery, 'limit' => $limit, 'offset' => $start, 'order' => $order));
        $aData['bresult'] = array();
        foreach ($tokens as $token)
        {
            $aData['bresult'][] = $token->attributes;
        }

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['searchstring'] = $searchstring;
        $aData['surveyid'] = $iSurveyId;
        $aData['bgc'] = "";
        $aData['limit'] = $limit;
        $aData['start'] = $start;
        $aData['order'] = $order;
        $aData['surveyprivate'] = $aData['thissurvey']['anonymized'];
        $aData['dateformatdetails'] = $dateformatdetails;

        $this->_renderWrappedTemplate('token', array('tokenbar', 'browse'), $aData);
    }

    /**
    * This function sends the shared participant info to the share panel using JSON encoding
    * This function is called after the share panel grid is loaded
    * This function returns the json depending on the user logged in by checking it from the session
    * @param it takes the session user data loginID
    * @return JSON encoded string containg sharing information
    */
    function getTokens_json($iSurveyId, $search = null)
    {
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            $clang->eT("No token table.");// return json ? error not treated in js.
            return;
        }
        $clang = $this->getController()->lang;
        if (!hasSurveyPermission($iSurveyId, 'tokens', 'read'))
        {
            $clang->eT("We are sorry but you don't have permissions to do this.");// return json ? error not treated in js.
            return;
        }
        $page  = Yii::app()->request->getPost('page', 1);
        $sidx = Yii::app()->request->getPost('sidx', 'lastname');
        $sord = Yii::app()->request->getPost('sord', 'asc');
        $limit = Yii::app()->request->getPost('rows', 25);
        
        $aData = new stdClass;
        $aData->page = $page;
        
        if (!empty($search)) {
            $condition = Tokens_dynamic::model($iSurveyId)->getSearchMultipleCondition($search);
        } else { 
            $condition = new CDbCriteria();
        }
                
        $condition->order = $sidx. " ". $sord;
        $condition->offset = ($page - 1) * $limit;
        $condition->limit = $limit;
        $tokens = Tokens_dynamic::model($iSurveyId)->findAll($condition);
        
        $condition->offset=0;
        $condition->limit=0;        
        $aData->records = Tokens_dynamic::model($iSurveyId)->count($condition);
        
        if ($limit>$aData->records)
        {
            $limit=$aData->records;
        }
        if ($limit!=0)
        {
            $aData->total = ceil($aData->records / $limit);
        }
        else
        {
            $aData->total = 0;
        }

        Yii::app()->loadHelper("surveytranslator");

        $format = getDateFormatData(Yii::app()->session['dateformat']);

        $aSurveyInfo = Survey::model()->findByPk($iSurveyId)->getAttributes(); //Get survey settings
        $attributes  = getAttributeFieldNames($iSurveyId);
        
        // Now find all responses for the visible tokens
        $visibleTokens = array();
        $answeredTokens = array();
        if ($aSurveyInfo['anonymized'] == "N" && $aSurveyInfo['active'] == "Y") {
            foreach ($tokens as $token) {
                if(isset($token['token']) && $token['token'])
                    $visibleTokens[] = $token['token'];
            }
            $answers = Survey_dynamic::model($iSurveyId)->findAllByAttributes(array('token'=>$visibleTokens));
            foreach($answers as $answer) {
                $answeredTokens[$answer['token']] = $answer['token'];
            }
        }
        
        foreach ($tokens as $token)
        {
            $aRowToAdd = array();
            if ((int) $token['validfrom']) {
                $token['validfrom'] = date($format['phpdate'] . ' H:i', strtotime(trim($token['validfrom'])));
            } else {
                $token['validfrom'] = '';
            }
            if ((int) $token['validuntil']) {
                $token['validuntil'] = date($format['phpdate'] . ' H:i', strtotime(trim($token['validuntil'])));
            } else {
                $token['validuntil'] = '';
            }

            $aRowToAdd['id'] = $token['tid'];

            $action="";
            $action .= "<div class='inputbuttons'>";    // so we can hide this when edit is clicked
            // Check is we have an answer
            if (in_array($token['token'], $answeredTokens) && hasSurveyPermission($iSurveyId, 'responses', 'read')) {
                // @@TODO change link
                $url = $this->getController()->createUrl("admin/responses/sa/browse/surveyid/{$iSurveyId}", array('token'=>$token['token']));
                $title = $clang->gT("View response details");
                $action .= CHtml::link(CHtml::image(Yii::app()->getConfig('adminimageurl') . 'token_viewanswer.png', $title, array('title'=>$title)), $url, array('class'=>'imagelink'));
            } else {
                    $action .= '<div style="width: 20px; height: 16px; float: left;"></div>';
            }
            // Check if the token can be taken
            if ($token['token'] != "" && ($token['completed'] == "N" || $token['completed'] == "") && hasSurveyPermission($iSurveyId, 'responses', 'create')) {
                $action .= viewHelper::getImageLink('do_16.png', "survey/index/sid/{$iSurveyId}/token/{$token['token']}/newtest/Y", $clang->gT("Do survey"), '_blank');
            } else {
                $action .= '<div style="width: 20px; height: 16px; float: left;"></div>';
            }
            if(hasSurveyPermission($iSurveyId, 'tokens', 'delete')){
                $attribs = array('onclick' => 'if (confirm("' . $clang->gT("Are you sure you want to delete this entry?") . ' (' . $token['tid'] . ')")) {$("#displaytokens").delRowData(' . $token['tid'] . ');$.post(delUrl,{tid:' . $token['tid'] . '});}');
                $action .= viewHelper::getImageLink('token_delete.png', null, $clang->gT("Delete token entry"), null, 'imagelink btnDelete', $attribs);
            }
            if (strtolower($token['emailstatus']) == 'ok' && hasSurveyPermission($iSurveyId, 'tokens', 'update')) {
                if ($token['completed'] == 'N' && $token['usesleft'] > 0) {
                    if ($token['sent'] == 'N') {
                        $action .= viewHelper::getImageLink('token_invite.png', "admin/tokens/sa/email/surveyid/{$iSurveyId}/tokenids/" . $token['tid'], $clang->gT("Send invitation email to this person (if they have not yet been sent an invitation email)"), "_blank");
                    } else {
                        $action .= viewHelper::getImageLink('token_remind.png', "admin/tokens/sa/email/action/remind/surveyid/{$iSurveyId}/tokenids/" . $token['tid'], $clang->gT("Send reminder email to this person (if they have already received the invitation email)"), "_blank");
                    }
                } else {
                    $action .= '<div style="width: 20px; height: 16px; float: left;"></div>';
                }
            } else {
                $action .= '<div style="width: 20px; height: 16px; float: left;"></div>';
            }
            if(hasSurveyPermission($iSurveyId, 'tokens', 'update'))
                $action .= viewHelper::getImageLink('edit_16.png', null, $clang->gT("Edit token entry"), null, 'imagelink token_edit');
            if(!empty($token['participant_id']) && $token['participant_id'] != "" && hasGlobalPermission('USER_RIGHT_PARTICIPANT_PANEL')) {
                $action .= viewHelper::getImageLink('cpdb_16.png', "admin/participants/sa/displayParticipants/searchurl/participant_id||equal||" . $token['participant_id'], $clang->gT("View this person in the central participants database"), '_top');
            } else {
                $action .= '<div style="width: 20px; height: 16px; float: left;"></div>';
            }
            $action .= '</div>';
            $aRowToAdd['cell'] = array($token['tid'], $action, $token['firstname'], $token['lastname'], $token['email'], $token['emailstatus'], $token['token'], $token['language'], $token['sent'], $token['remindersent'], $token['remindercount'], $token['completed'], $token['usesleft'], $token['validfrom'], $token['validuntil']);
            foreach ($attributes as $attribute) {
                $aRowToAdd['cell'][] = $token[$attribute];
            }
            $aData->rows[] = $aRowToAdd;
        }

        echo ls_json_encode($aData);
    }

    function getSearch_json($iSurveyId)
    {
        $searchcondition = Yii::app()->request->getQuery('search');
        $searchcondition = urldecode($searchcondition);
        $finalcondition = array();
        $condition = explode("||", $searchcondition);
        
        return $this->getTokens_json($iSurveyId, $condition);
    }

    function editToken($iSurveyId) // Used ? 2013-01-29
    {
        $clang = $this->getController()->lang;
        if (!hasSurveyPermission($iSurveyId, 'tokens', 'update') && !hasSurveyPermission($iSurveyId, 'tokens', 'create'))
        {
            $clang->eT("We are sorry but you don't have permissions to do this.");// return json ? error not treated in js.
            return;
        }

        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        $sOperation = Yii::app()->request->getPost('oper');

        if (trim(Yii::app()->request->getPost('validfrom')) == '')
            $from = null;
        else
            $from = date('Y-m-d H:i:s', strtotime(trim($_POST['validfrom'])));

        if (trim(Yii::app()->request->getPost('validuntil')) == '')
            $until = null;
        else
            $until = date('Y-m-d H:i:s', strtotime(trim($_POST['validuntil'])));

        // if edit it will update the row
        if ($sOperation == 'edit' && hasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            //            if (Yii::app()->request->getPost('language') == '')
            //            {
            //                $sLang = Yii::app()->session['adminlang'];
            //            }
            //            else
            //            {
            //                $sLang = Yii::app()->request->getPost('language');
            //            }
            Tokens_dynamic::model($iSurveyId);
            

            echo $from . ',' . $until;
            $aData = array(
            'firstname' => Yii::app()->request->getPost('firstname'),
            'lastname' => Yii::app()->request->getPost('lastname'),
            'email' => Yii::app()->request->getPost('email'),
            'emailstatus' => Yii::app()->request->getPost('emailstatus'),
            'token' => Yii::app()->request->getPost('token'),
            'language' => Yii::app()->request->getPost('language'),
            'sent' => Yii::app()->request->getPost('sent'),
            'remindersent' => Yii::app()->request->getPost('remindersent'),
            'remindercount' => Yii::app()->request->getPost('remindercount'),
            'completed' => Yii::app()->request->getPost('completed'),
            'usesleft' => Yii::app()->request->getPost('usesleft'),
            'validfrom' => $from,
            'validuntil' => $until);
            $attrfieldnames = GetParticipantAttributes($iSurveyId);
            foreach ($attrfieldnames as $attr_name => $desc)
            {
                $value = Yii::app()->request->getPost($attr_name);
                if ($desc['mandatory'] == 'Y' && trim($value) == '')
                    $this->getController()->error(sprintf($this->controller->lang->gT('%s cannot be left empty'), $desc['description']));
                $aData[$attr_name] = Yii::app()->request->getPost($attr_name);
            }
            $token = Tokens_dynamic::model()->find('tid=' . Yii::app()->getRequest()->getPost('id'));

            foreach ($aData as $k => $v)
                $token->$k = $v;
            echo $token->update();
        }
        // if add it will insert a new row
        elseif ($sOperation == 'add'  && hasSurveyPermission($iSurveyId, 'tokens', 'create'))
        {
            if (Yii::app()->request->getPost('language') == '')
                $aData = array('firstname' => Yii::app()->request->getPost('firstname'),
                'lastname' => Yii::app()->request->getPost('lastname'),
                'email' => Yii::app()->request->getPost('email'),
                'emailstatus' => Yii::app()->request->getPost('emailstatus'),
                'token' => Yii::app()->request->getPost('token'),
                'language' => Yii::app()->request->getPost('language'),
                'sent' => Yii::app()->request->getPost('sent'),
                'remindersent' => Yii::app()->request->getPost('remindersent'),
                'remindercount' => Yii::app()->request->getPost('remindercount'),
                'completed' => Yii::app()->request->getPost('completed'),
                'usesleft' => Yii::app()->request->getPost('usesleft'),
                'validfrom' => $from,
                'validuntil' => $until);
            $attrfieldnames = Survey::model()->findByPk($iSurveyId)->tokenAttributes;
            foreach ($attrfieldnames as $attr_name => $desc)
            {
                $value = Yii::app()->request->getPost($attr_name);
                if ($desc['mandatory'] == 'Y' && trim($value) == '')
                    $this->getController()->error(sprintf($clang->gT('%s cannot be left empty'), $desc['description']));
                $aData[$attr_name] = Yii::app()->request->getPost($attr_name);
            }
            echo ls_json_encode(var_export($aData));
            $token = new Tokens_dynamic;
            foreach ($aData as $k => $v)
                $token->$k = $v;
            echo $token->save();
        }
        elseif ($sOperation == 'del' && hasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            $_POST['tid'] = Yii::app()->request->getPost('id');
            $this->delete($iSurveyId);
        }
        else
        {
            $clang->eT("We are sorry but you don't have permissions to do this.");// return json ? error not treated in js.
            return;
        }
    }

    /**
    * Add new token form
    */
    function addnew($iSurveyId)
    {
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $iSurveyId = sanitize_int($iSurveyId);
        $clang = $this->getController()->lang;
        if (!hasSurveyPermission($iSurveyId, 'tokens', 'create'))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        Yii::app()->loadHelper("surveytranslator");

        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

        if (Yii::app()->request->getPost('subaction') == 'inserttoken')
        {

            Yii::import('application.libraries.Date_Time_Converter');
            //Fix up dates and match to database format
            if (trim(Yii::app()->request->getPost('validfrom')) == '')
            {
                $validfrom = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(trim(Yii::app()->request->getPost('validfrom')), $dateformatdetails['phpdate'] . ' H:i');
                $validfrom = $datetimeobj->convert('Y-m-d H:i:s');
            }
            if (trim(Yii::app()->request->getPost('validuntil')) == '')
            {
                $validuntil = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(trim(Yii::app()->request->getPost('validuntil')), $dateformatdetails['phpdate'] . ' H:i');
                $validuntil = $datetimeobj->convert('Y-m-d H:i:s');
            }

            $sanitizedtoken = sanitize_token(Yii::app()->request->getPost('token'));

            /* Mdekker: commented out this block as it doesn't respect tokenlength
             * or existing tokens and was always handled by the tokenify action as
             * the ui still suggests
            if (empty($sanitizedtoken))
            {
                $isvalidtoken = false;
                while ($isvalidtoken == false)
                {
                    $newtoken = randomChars(15);
                    if (!isset($existingtokens[$newtoken]))
                    {
                        $isvalidtoken = true;
                        $existingtokens[$newtoken] = null;
                    }
                }
                $sanitizedtoken = $newtoken;
            }
            */



            $aData = array(
            'firstname' => Yii::app()->request->getPost('firstname'),
            'lastname' => Yii::app()->request->getPost('lastname'),
            'email' => sanitize_email(Yii::app()->request->getPost('email')),
            'emailstatus' => Yii::app()->request->getPost('emailstatus'),
            'token' => $sanitizedtoken,
            'language' => sanitize_languagecode(Yii::app()->request->getPost('language')),
            'sent' => Yii::app()->request->getPost('sent'),
            'remindersent' => Yii::app()->request->getPost('remindersent'),
            'completed' => Yii::app()->request->getPost('completed'),
            'usesleft' => Yii::app()->request->getPost('usesleft'),
            'validfrom' => $validfrom,
            'validuntil' => $validuntil,
            );

            // add attributes
            $attrfieldnames = Survey::model()->findByPk($iSurveyId)->tokenAttributes;
            $aTokenFieldNames=Yii::app()->db->getSchema()->getTable("{{tokens_$iSurveyId}}",true);
            $aTokenFieldNames=array_keys($aTokenFieldNames->columns);
            foreach ($attrfieldnames as $attr_name => $desc)
            {
                if(!in_array($attr_name,$aTokenFieldNames)) continue;
                $value = Yii::app()->getRequest()->getPost($attr_name);
                if ($desc['mandatory'] == 'Y' && trim($value) == '')
                    $this->getController()->error(sprintf($clang->gT('%s cannot be left empty'), $desc['description']));
                $aData[$attr_name] = Yii::app()->getRequest()->getPost($attr_name);
            }

            $udresult = Tokens_dynamic::model($iSurveyId)->findAll("token <> '' and token = '$sanitizedtoken'");
            if (count($udresult) == 0)
            {
                // AutoExecute
                $token = new Tokens_dynamic;
                foreach ($aData as $k => $v)
                    $token->$k = $v;
                $inresult = $token->save();
                $aData['success'] = true;
            }
            else
            {
                $aData['success'] = false;
            }

            $aData['thissurvey'] = getSurveyInfo($iSurveyId);
            $aData['surveyid'] = $iSurveyId;

            $this->_renderWrappedTemplate('token', array('tokenbar', 'addtokenpost'), $aData);
        }
        else
        {
            self::_handletokenform($iSurveyId, "addnew");
        }
    }

    /**
    * Edit Tokens
    */
    function edit($iSurveyId, $iTokenId)
    {
        $clang = $this->getController()->lang;
        $iSurveyId = sanitize_int($iSurveyId);
        $iTokenId = sanitize_int($iTokenId);
        if (!hasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }

        Yii::app()->loadHelper("surveytranslator");
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

        if (Yii::app()->request->getPost('subaction'))
        {

            Yii::import('application.libraries.Date_Time_Converter', true);
            if (trim(Yii::app()->request->getPost('validfrom')) == '')
            {
                $_POST['validfrom'] = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(trim(Yii::app()->request->getPost('validfrom')), $dateformatdetails['phpdate'] . ' H:i');
                $_POST['validfrom'] = $datetimeobj->convert('Y-m-d H:i:s');
            }
            if (trim(Yii::app()->request->getPost('validuntil')) == '')
            {
                $_POST['validuntil'] = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(trim(Yii::app()->request->getPost('validuntil')), $dateformatdetails['phpdate'] . ' H:i');
                $_POST['validuntil'] = $datetimeobj->convert('Y-m-d H:i:s');
            }

            $aData['thissurvey'] = getSurveyInfo($iSurveyId);
            $aData['surveyid'] = $iSurveyId;

            $aTokenData['firstname'] = Yii::app()->request->getPost('firstname');
            $aTokenData['lastname'] = Yii::app()->request->getPost('lastname');
            $aTokenData['email'] = sanitize_email(Yii::app()->request->getPost('email'));
            $aTokenData['emailstatus'] = Yii::app()->request->getPost('emailstatus');
            $santitizedtoken = sanitize_token(Yii::app()->request->getPost('token'));
            $aTokenData['token'] = $santitizedtoken;
            $aTokenData['language'] = sanitize_languagecode(Yii::app()->request->getPost('language'));
            $aTokenData['sent'] = Yii::app()->request->getPost('sent');
            $aTokenData['completed'] = Yii::app()->request->getPost('completed');
            $aTokenData['usesleft'] = Yii::app()->request->getPost('usesleft');
            $aTokenData['validfrom'] = Yii::app()->request->getPost('validfrom');
            $aTokenData['validuntil'] = Yii::app()->request->getPost('validuntil');
            $aTokenData['remindersent'] = Yii::app()->request->getPost('remindersent');
            $aTokenData['remindercount'] = intval(Yii::app()->request->getPost('remindercount'));

            $udresult = Tokens_dynamic::model($iSurveyId)->findAll("tid <> '$iTokenId' and token <> '' and token = '$santitizedtoken'");

            if (count($udresult) == 0)
            {
                //$aTokenData = array();
                $attrfieldnames = Survey::model()->findByPk($iSurveyId)->tokenAttributes;
                foreach ($attrfieldnames as $attr_name => $desc)
                {

                    $value = Yii::app()->request->getPost($attr_name);
                    if ($desc['mandatory'] == 'Y' && trim($value) == '')
                        $this->getController()->error(sprintf($clang->gT('%s cannot be left empty'), $desc['description']));
                    $aTokenData[$attr_name] = Yii::app()->request->getPost($attr_name);
                }

                $token = Tokens_dynamic::model($iSurveyId)->findByPk($iTokenId);
                foreach ($aTokenData as $k => $v)
                    $token->$k = $v;
                $token->save();

                $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
                'title' => $clang->gT("Success"),
                'message' => $clang->gT("The token entry was successfully updated.") . "<br /><br />\n"
                . "\t\t<input type='button' value='" . $clang->gT("Display tokens") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/browse/surveyid/$iSurveyId/") . "', '_top')\" />\n"
                )), $aData);
            }
            else
            {
                $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
                'title' => $clang->gT("Failed"),
                'message' => $clang->gT("There is already an entry with that exact token in the table. The same token cannot be used in multiple entries.") . "<br /><br />\n"
                . "\t\t<input type='button' value='" . $clang->gT("Show this token entry") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/edit/surveyid/$iSurveyId/tokenid/$iTokenId") . "', '_top')\" />\n"
                )));
            }
        }
        else
        {
            $this->_handletokenform($iSurveyId, "edit", $iTokenId);
        }
    }

    /**
    * Delete tokens
    */
    function delete($iSurveyID)
    {
        $clang = $this->getController()->lang;
        $iSurveyID = sanitize_int($iSurveyID);
        $sTokenIDs = Yii::app()->request->getPost('tid');
        /* Check permissions */
        if (!hasSurveyPermission($iSurveyID, 'tokens', 'update'))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyID}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyID . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyID);
        }

        if (hasSurveyPermission($iSurveyID, 'tokens', 'delete'))
        {
            $aTokenIds = explode(',', $sTokenIDs); //Make the tokenids string into an array

            //Delete any survey_links
            Survey_links::model()->deleteTokenLink($aTokenIds, $iSurveyID);

            //Then delete the tokens
            Tokens_dynamic::model($iSurveyID)->deleteRecords($aTokenIds);
        }
    }

    /**
    * Add dummy tokens form
    */
    function addDummies($iSurveyId, $subaction = '')
    {
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        $iSurveyId = sanitize_int($iSurveyId);
        $clang = $this->getController()->lang;
        if (!hasSurveyPermission($iSurveyId, 'tokens', 'create'))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }

        $this->getController()->loadHelper("surveytranslator");

        if (!empty($subaction) && $subaction == 'add')
        {
            $this->getController()->loadLibrary('Date_Time_Converter');
            $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

            //Fix up dates and match to database format
            if (trim(Yii::app()->request->getPost('validfrom')) == '')
            {
                $_POST['validfrom'] = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(trim(Yii::app()->request->getPost('validfrom')), $dateformatdetails['phpdate'] . ' H:i');
                $_POST['validfrom'] = $datetimeobj->convert('Y-m-d H:i:s');
            }
            if (trim(Yii::app()->request->getPost('validuntil')) == '')
            {
                $_POST['validuntil'] = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(trim(Yii::app()->request->getPost('validuntil')), $dateformatdetails['phpdate'] . ' H:i');
                $_POST['validuntil'] = $datetimeobj->convert('Y-m-d H:i:s');
            }

            $santitizedtoken = '';

            $aData = array('firstname' => Yii::app()->request->getPost('firstname'),
            'lastname' => Yii::app()->request->getPost('lastname'),
            'email' => sanitize_email(Yii::app()->request->getPost('email')),
            'emailstatus' => 'OK',
            'token' => $santitizedtoken,
            'language' => sanitize_languagecode(Yii::app()->request->getPost('language')),
            'sent' => 'N',
            'remindersent' => 'N',
            'completed' => 'N',
            'usesleft' => Yii::app()->request->getPost('usesleft'),
            'validfrom' => Yii::app()->request->getPost('validfrom'),
            'validuntil' => Yii::app()->request->getPost('validuntil'));

            // add attributes
            $attrfieldnames = getTokenFieldsAndNames($iSurveyId,true);
            foreach ($attrfieldnames as $attr_name => $desc)
            {
                $value = Yii::app()->request->getPost($attr_name);
                if ($desc['mandatory'] == 'Y' && trim($value) == '')
                    $this->getController()->error(sprintf($clang->gT('%s cannot be left empty'), $desc['description']));
                $aData[$attr_name] = Yii::app()->request->getPost($attr_name);
            }

            $amount = sanitize_int(Yii::app()->request->getPost('amount'));
            $tokenlength = sanitize_int(Yii::app()->request->getPost('tokenlen'));

            // Fill an array with all existing tokens
            $criteria = Tokens_dynamic::model($iSurveyId)->getDbCriteria();
            $criteria->select = 'token';
            $ntresult = Tokens_dynamic::model($iSurveyId)->findAllAsArray($criteria);   //Use AsArray to skip active record creation
            $existingtokens=array();
            foreach ($ntresult as $tkrow)
            {
                $existingtokens[$tkrow['token']] = true ;
            }
            $invalidtokencount=0;
            $newDummyToken=0;
            while ($newDummyToken<$amount && $invalidtokencount<50)
            {
                $aDataToInsert = $aData;
                $aDataToInsert['firstname'] = str_replace('{TOKEN_COUNTER}', $newDummyToken, $aDataToInsert['firstname']);
                $aDataToInsert['lastname'] = str_replace('{TOKEN_COUNTER}', $newDummyToken, $aDataToInsert['lastname']);
                $aDataToInsert['email'] = str_replace('{TOKEN_COUNTER}', $newDummyToken, $aDataToInsert['email']);

                $isvalidtoken = false;
                while ($isvalidtoken == false && $invalidtokencount<50)
                {
                    $newtoken = randomChars($tokenlength);
                    if (!isset($existingtokens[$newtoken]))
                    {
                        $isvalidtoken = true;
                        $existingtokens[$newtoken] = true;
                        $invalidtokencount=0;
                    }
                    else
                    {
                        $invalidtokencount ++;
                    }
                }
                if($isvalidtoken)
                {
                    $aDataToInsert['token'] = $newtoken;
                    Tokens_dynamic::model()->insertToken($iSurveyId, $aDataToInsert);
                    $newDummyToken ++;
                }

            }
            $aData['thissurvey'] = getSurveyInfo($iSurveyId);
            $aData['surveyid'] = $iSurveyId;
            if(!$invalidtokencount)
            {
                $aData['success'] = false;
                $message=array('title' => $clang->gT("Success"),
                'message' => $clang->gT("New dummy tokens were added.") . "<br /><br />\n<input type='button' value='"
                . $clang->gT("Display tokens") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/browse/surveyid/$iSurveyId") . "', '_top')\" />\n"
                );
            }
            else
            {
                $aData['success'] = true;
                $message= array(
                'title' => $clang->gT("Failed"),
                'message' => "<p>".sprintf($clang->gT("Only %s new dummy tokens were added after %s trials."),$newDummyToken,$invalidtokencount)
                .$clang->gT("Try with a bigger token length.")."</p>"
                ."\n<input type='button' value='"
                . $clang->gT("Display tokens") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/browse/surveyid/$iSurveyId") . "', '_top')\" />\n"
                );
            }
            $this->_renderWrappedTemplate('token',  array('tokenbar','message' => $message),$aData);

        }
        else
        {
            $tkcount = Tokens_dynamic::model($iSurveyId)->count();
            $tokenlength = Yii::app()->db->createCommand()->select('tokenlength')->from('{{surveys}}')->where('sid=:sid')->bindParam(":sid", $iSurveyId, PDO::PARAM_INT)->query()->readColumn(0);

            if (empty($tokenlength))
                $tokenlength = 15;

            $thissurvey = getSurveyInfo($iSurveyId);
            $aData['thissurvey'] = $thissurvey;
            $aData['surveyid'] = $iSurveyId;
            $aData['tokenlength'] = $tokenlength;
            $aData['dateformatdetails'] = getDateFormatData(Yii::app()->session['dateformat'],$clang->langcode);
            $aData['aAttributeFields']=GetParticipantAttributes($iSurveyId);
            $this->_renderWrappedTemplate('token', array('tokenbar', 'dummytokenform'), $aData);
        }
    }

    /**
    * Handle managetokenattributes action
    */
    function managetokenattributes($iSurveyId)
    {
        $clang = $this->getController()->lang;
        $iSurveyId = sanitize_int($iSurveyId);
        if (!hasSurveyPermission($iSurveyId, 'tokens', 'update') && !hasSurveyPermission($iSurveyID, 'surveysettings', 'update'))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        Yii::app()->loadHelper("surveytranslator");

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;
        $aData['tokenfields'] = getAttributeFieldNames($iSurveyId);
        $aData['tokenfielddata'] = $aData['thissurvey']['attributedescriptions'];
        $languages = array_merge((array) Survey::model()->findByPk($iSurveyId)->language, Survey::model()->findByPk($iSurveyId)->additionalLanguages);
        $captions = array();
        foreach ($languages as $language)
            $captions[$language] = Surveys_languagesettings::model()->findByAttributes(array('surveyls_survey_id' => $iSurveyId, 'surveyls_language' => $language))->attributeCaptions;
        $aData['languages'] = $languages;
        $aData['tokencaptions'] = $captions;
        $aData['nrofattributes'] = 0;
        $aData['examplerow'] = Tokens_dynamic::model($iSurveyId)->find();

        $this->_renderWrappedTemplate('token', array('tokenbar', 'managetokenattributes'), $aData);
    }

    /**
    * Update token attributes
    */
    function updatetokenattributes($iSurveyId)
    {
        $clang = $this->getController()->lang;
        $iSurveyId = sanitize_int($iSurveyId);
        if (!hasSurveyPermission($iSurveyId, 'tokens', 'update') && !hasSurveyPermission($iSurveyID, 'surveysettings', 'update'))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }

        $number2add = sanitize_int(Yii::app()->request->getPost('addnumber'), 1, 100);
        $tokenattributefieldnames = getAttributeFieldNames($iSurveyId);
        $i = 1;

        for ($b = 0; $b < $number2add; $b++)
        {
            while (in_array('attribute_' . $i, $tokenattributefieldnames) !== false)
            {
                $i++;
            }
            $tokenattributefieldnames[] = 'attribute_' . $i;
            Yii::app()->db->createCommand(Yii::app()->db->getSchema()->addColumn("{{tokens_".intval($iSurveyId)."}}", 'attribute_' . $i, 'VARCHAR(255)'))->execute();
            $fields['attribute_' . $i] = array('type' => 'VARCHAR', 'constraint' => '255');
        }

        LimeExpressionManager::SetDirtyFlag();  // so that knows that token tables have changed

        Yii::app()->session['flashmessage'] = sprintf($clang->gT("%s field(s) were successfully added."), $number2add);
        Yii::app()->getController()->redirect(Yii::app()->getController()->createUrl("/admin/tokens/sa/managetokenattributes/surveyid/$iSurveyId"));

    }

    /**
    * updatetokenattributedescriptions action
    */
    function updatetokenattributedescriptions($iSurveyId)
    {
        $clang = $this->getController()->lang;
        $iSurveyId = sanitize_int($iSurveyId);
        if (!hasSurveyPermission($iSurveyId, 'tokens', 'update') && !hasSurveyPermission($iSurveyID, 'surveysettings', 'update'))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }

        // find out the existing token attribute fieldnames
        $tokenattributefieldnames = getAttributeFieldNames($iSurveyId);
        $languages = array_merge((array) Survey::model()->findByPk($iSurveyId)->language, Survey::model()->findByPk($iSurveyId)->additionalLanguages);
        $fieldcontents = array();
        $captions = array();
        foreach ($tokenattributefieldnames as $fieldname)
        {
            $fieldcontents[$fieldname] = array(
            'description' => strip_tags(Yii::app()->request->getPost('description_' . $fieldname)),
            'mandatory' => Yii::app()->request->getPost('mandatory_' . $fieldname) == 'Y' ? 'Y' : 'N',
            'show_register' => Yii::app()->request->getPost('show_register_' . $fieldname) == 'Y' ? 'Y' : 'N',
            );
            foreach ($languages as $language)
                $captions[$language][$fieldname] = $_POST["caption_{$fieldname}_$language"];
        }

        Survey::model()->updateByPk($iSurveyId, array('attributedescriptions' => serialize($fieldcontents)));
        foreach ($languages as $language)
        {
            $ls = Surveys_languagesettings::model()->findByAttributes(array('surveyls_survey_id' => $iSurveyId, 'surveyls_language' => $language));
            $ls->surveyls_attributecaptions = serialize($captions[$language]);
            $ls->save();
        }
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;
        $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
        'title' => $clang->gT('Token attribute descriptions were successfully updated.'),
        'message' => "<br /><input type='button' value='" . $clang->gT('Back to attribute field management.') . "' onclick=\"window.open('" . $this->getController()->createUrl("/admin/tokens/sa/managetokenattributes/surveyid/$iSurveyId") . "', '_top')\" />"
        )), $aData);
    }

    /**
    * Handle email action
    */
    function email($iSurveyId, $tokenids = null)     
    {
        $clang = $this->getController()->lang;
        $iSurveyId = sanitize_int($iSurveyId);

        if (!hasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        $aTokenIds=$tokenids;
        if (empty($tokenids))
        {
            $aTokenIds = Yii::app()->request->getPost('tokenids', false);
        }
        if (!empty($aTokenIds))
        {
            $aTokenIds = explode('|', $aTokenIds);
            $aTokenIds = array_filter($aTokenIds);
            $aTokenIds = array_map('sanitize_int', $aTokenIds);
        }
        $aTokenIds=array_unique(array_filter((array) $aTokenIds));        

        $sSubAction = Yii::app()->request->getParam('action');
        $sSubAction = !in_array($sSubAction, array('email', 'remind')) ? 'email' : $sSubAction;
        $bEmail = $sSubAction == 'email';

        Yii::app()->loadHelper('surveytranslator');
        Yii::app()->loadHelper('/admin/htmleditor');
        Yii::app()->loadHelper('replacements');

        $query = Tokens_dynamic::model($iSurveyId)->find();
        $aExampleRow = empty($query) ? array() : $query->attributes;
        $aSurveyLangs = Survey::model()->findByPk($iSurveyId)->additionalLanguages;
        $sBaseLanguage = Survey::model()->findByPk($iSurveyId)->language;
        array_unshift($aSurveyLangs, $sBaseLanguage);
        $aTokenFields = getTokenFieldsAndNames($iSurveyId, true);
        $iAttributes = 0;
        $bHtml = (getEmailFormat($iSurveyId) == 'html');

        $timeadjust = Yii::app()->getConfig("timeadjust");

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        foreach($aSurveyLangs as $sSurveyLanguage)
        {
            $aData['thissurvey'][$sSurveyLanguage] = getSurveyInfo($iSurveyId, $sSurveyLanguage);    
        }
        $aData['surveyid'] = $iSurveyId;
        $aData['sSubAction'] = $sSubAction;
        $aData['bEmail'] = $bEmail;
        $aData['aSurveyLangs'] = $aData['surveylangs'] = $aSurveyLangs;
        $aData['baselang'] = $sBaseLanguage;
        $aData['tokenfields'] = array_keys($aTokenFields);
        $aData['nrofattributes'] = $iAttributes;
        $aData['examplerow'] = $aExampleRow;
        $aData['tokenids'] = $aTokenIds;
        $aData['ishtml'] = $bHtml;
        $iMaxEmails = Yii::app()->getConfig('maxemails');

        if (Yii::app()->request->getPost('bypassbademails') == 'Y')
        {
            $SQLemailstatuscondition = "emailstatus = 'OK'";
        }
        else
        {
            $SQLemailstatuscondition = "emailstatus <> 'OptOut'";
        }

        if (!Yii::app()->request->getPost('ok'))
        {
            if (empty($aData['tokenids']))
            {
                $aTokens = Tokens_dynamic::model($iSurveyId)->findUninvited($aTokenIds, 0, $bEmail, $SQLemailstatuscondition);
                foreach($aTokens as $aToken)
                {
                    $aData['tokenids'][] = $aToken['tid'];
                }
            }
            $this->_renderWrappedTemplate('token', array('tokenbar', $sSubAction), $aData);
        }
        else
        {
            $SQLremindercountcondition = "";
            $SQLreminderdelaycondition = "";

            if (!$bEmail)
            {
                if (Yii::app()->request->getPost('maxremindercount') &&
                Yii::app()->request->getPost('maxremindercount') != '' &&
                intval(Yii::app()->request->getPost('maxremindercount')) != 0)
                {
                    $SQLremindercountcondition = "remindercount < " . intval(Yii::app()->request->getPost('maxremindercount'));
                }

                if (Yii::app()->request->getPost('minreminderdelay') &&
                Yii::app()->request->getPost('minreminderdelay') != '' &&
                intval(Yii::app()->request->getPost('minreminderdelay')) != 0)
                {
                    // Yii::app()->request->getPost('minreminderdelay') in days (86400 seconds per day)
                    $compareddate = dateShift(
                    date("Y-m-d H:i:s", time() - 86400 * intval(Yii::app()->request->getPost('minreminderdelay'))), "Y-m-d H:i", $timeadjust);
                    $SQLreminderdelaycondition = " ( "
                    . " (remindersent = 'N' AND sent < '" . $compareddate . "') "
                    . " OR "
                    . " (remindersent < '" . $compareddate . "'))";
                }
            }

            $ctresult = Tokens_dynamic::model($iSurveyId)->findUninvited($aTokenIds, 0, $bEmail, $SQLemailstatuscondition, $SQLremindercountcondition, $SQLreminderdelaycondition);
            $ctcount = count($ctresult);

            $emresult = Tokens_dynamic::model($iSurveyId)->findUninvited($aTokenIds, $iMaxEmails, $bEmail, $SQLemailstatuscondition, $SQLremindercountcondition, $SQLreminderdelaycondition);
            $emcount = count($emresult);

            foreach ($aSurveyLangs as $language)
            {
                if ($bHtml)
                    $_POST['message_' . $language] = html_entity_decode(Yii::app()->request->getPost('message_' . $language), ENT_QUOTES, Yii::app()->getConfig("emailcharset"));
            }

            $attributes = array_keys(getTokenFieldsAndNames($iSurveyId));
            $tokenoutput = "";

            if ($emcount > 0)
            {
                foreach ($emresult as $emrow)
                {
                    $to = array();
                    $aEmailaddresses = explode(';', $emrow['email']);
                    foreach ($aEmailaddresses as $sEmailaddress)
                    {
                        $to[] = ($emrow['firstname'] . " " . $emrow['lastname'] . " <{$sEmailaddress}>");
                    }
                    $fieldsarray["{EMAIL}"] = $emrow['email'];
                    $fieldsarray["{FIRSTNAME}"] = $emrow['firstname'];
                    $fieldsarray["{LASTNAME}"] = $emrow['lastname'];
                    $fieldsarray["{TOKEN}"] = $emrow['token'];
                    $fieldsarray["{LANGUAGE}"] = $emrow['language'];

                    foreach ($attributes as $attributefield)
                    {
                        $fieldsarray['{' . strtoupper($attributefield) . '}'] = $emrow[$attributefield];
                        $fieldsarray['{TOKEN:'.strtoupper($attributefield).'}']=$emrow[$attributefield];
                    }

                    $emrow['language'] = trim($emrow['language']);
                    $found = array_search($emrow['language'], $aSurveyLangs);
                    if ($emrow['language'] == '' || $found == false)
                    {
                        $emrow['language'] = $sBaseLanguage;
                    }

                    $from = Yii::app()->request->getPost('from_' . $emrow['language']);

                    $fieldsarray["{OPTOUTURL}"] = $this->getController()
                                                       ->createAbsoluteUrl("/optout/tokens/langcode/" . trim($emrow['language']) . "/surveyid/{$iSurveyId}/token/{$emrow['token']}");
                    $fieldsarray["{OPTINURL}"] = $this->getController()
                                                      ->createAbsoluteUrl("/optin/tokens/langcode/" . trim($emrow['language']) . "/surveyid/{$iSurveyId}/token/{$emrow['token']}");
                    $fieldsarray["{SURVEYURL}"] = $this->getController()
                                                       ->createAbsoluteUrl("/survey/index/sid/{$iSurveyId}/token/{$emrow['token']}/lang/" . trim($emrow['language']) . "/");

                    foreach(array('OPTOUT', 'OPTIN', 'SURVEY') as $key)
                    {
                        $url = $fieldsarray["{{$key}URL}"];
                        if ($bHtml) $fieldsarray["{{$key}URL}"] = "<a href='{$url}'>" . htmlspecialchars($url) . '</a>';
                        if ($key == 'SURVEY')
                        {
                            $barebone_link = $url;
                        }
                    }

                    $customheaders = array('1' => "X-surveyid: " . $iSurveyId,
                    '2' => "X-tokenid: " . $fieldsarray["{TOKEN}"]);
                    
                    global $maildebug;
                    $modsubject = Replacefields(Yii::app()->request->getPost('subject_' . $emrow['language']), $fieldsarray);
                    $modmessage = Replacefields(Yii::app()->request->getPost('message_' . $emrow['language']), $fieldsarray);

                    if (isset($barebone_link))
                    {
                        $modsubject = str_replace("@@SURVEYURL@@", $barebone_link, $modsubject);
                        $modmessage = str_replace("@@SURVEYURL@@", $barebone_link, $modmessage);
                    }

                    if (trim($emrow['validfrom']) != '' && convertDateTimeFormat($emrow['validfrom'], 'Y-m-d H:i:s', 'U') * 1 > date('U') * 1)
                    {
                        $tokenoutput .= $emrow['tid'] . " " . ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) delayed: Token is not yet valid.") . "<br />", $fieldsarray);
                    }
                    elseif (trim($emrow['validuntil']) != '' && convertDateTimeFormat($emrow['validuntil'], 'Y-m-d H:i:s', 'U') * 1 < date('U') * 1)
                    {
                        $tokenoutput .= $emrow['tid'] . " " . ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) skipped: Token is not valid anymore.") . "<br />", $fieldsarray);
                    }
                    else
                    {
                        if (SendEmailMessage($modmessage, $modsubject, $to, $from, Yii::app()->getConfig("sitename"), $bHtml, getBounceEmail($iSurveyId), null, $customheaders))
                        {
                            // Put date into sent
                            $udequery = Tokens_dynamic::model($iSurveyId)->findByPk($emrow['tid']);
                            if ($bEmail)
                            {
                                $tokenoutput .= $clang->gT("Invitation sent to:");
                                $udequery->sent = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));
                            }
                            else
                            {
                                $tokenoutput .= $clang->gT("Reminder sent to:");
                                $udequery->remindersent = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));
                                $udequery->remindercount = $udequery->remindercount + 1;
                            }
                            $udequery->save();
                            //Update central participant survey_links
                            if(!empty($emrow['participant_id']))
                            {
                                $slquery = Survey_links::model()->find('participant_id = :pid AND survey_id = :sid AND token_id = :tid',array(':pid'=>$emrow['participant_id'],':sid'=>$iSurveyId,':tid'=>$emrow['tid']));
                                $slquery->date_invited = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));
                                $slquery->save();
                            }
                            $tokenoutput .= "{$emrow['tid']}: {$emrow['firstname']} {$emrow['lastname']} ({$emrow['email']})<br />\n";
                            if (Yii::app()->getConfig("emailsmtpdebug") == 2)
                            {
                                $tokenoutput .= $maildebug;
                            }
                        } else {
                            $tokenoutput .= ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) failed. Error Message:") . " " . $maildebug . "<br />", $fieldsarray);
                        }
                    }
                    unset($fieldsarray);
                }

                $aViewUrls = array('tokenbar', 'emailpost');
                $aData['tokenoutput']=$tokenoutput;

                if ($ctcount > $emcount)
                {
                    $i = 0;
                    if (isset($aTokenIds))
                    {
                        while ($i < $iMaxEmails)
                        {
                            array_shift($aTokenIds);
                            $i++;
                        }
                        $aData['tids'] = implode('|', $aTokenIds);
                    }

                    $aData['lefttosend'] = $ctcount - $iMaxEmails;
                    $aViewUrls[] = 'emailwarning';
                }

                $this->_renderWrappedTemplate('token', $aViewUrls, $aData);
            }
            else
            {
                $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
                'title' => $clang->gT("Warning"),
                'message' => $clang->gT("There were no eligible emails to send. This will be because none satisfied the criteria of:")
                . "<br/>&nbsp;<ul><li>" . $clang->gT("having a valid email address") . "</li>"
                . "<li>" . $clang->gT("not having been sent an invitation already") . "</li>"
                . "<li>" . $clang->gT("having already completed the survey") . "</li>"
                . "<li>" . $clang->gT("having a token") . "</li></ul>"
                )), $aData);
            }
        }
    }

    /**
    * Export Dialog
    */
    function exportdialog($iSurveyId)
    {
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $clang = $this->getController()->lang;
        $iSurveyId = sanitize_int($iSurveyId);
        if (!hasSurveyPermission($iSurveyId, 'tokens', 'export'))//EXPORT FEATURE SUBMITTED BY PIETERJAN HEYSE
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }

        if (Yii::app()->request->getPost('submit'))
        {
            Yii::app()->loadHelper("export");
            tokensExport($iSurveyId);
        }
        else
        {
            $aData['resultr'] = Tokens_dynamic::model($iSurveyId)->findAll(array('select' => 'language', 'group' => 'language'));
            $aData['thissurvey'] = getSurveyInfo($iSurveyId);
            $aData['surveyid'] = $iSurveyId;

            $this->_renderWrappedTemplate('token', array('tokenbar', 'exportdialog'), $aData);
        }
    }

    /**
    * Performs a ldap import
    *
    * @access public
    * @param int $iSurveyId
    * @return void
    */
    public function importldap($iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        $clang = $this->getController()->lang;
        if (!hasSurveyPermission($iSurveyId, 'tokens', 'import'))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }

        Yii::app()->loadConfig('ldap');
        Yii::app()->loadHelper('ldap');

        $tokenoutput = '';

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['iSurveyId'] = $aData['surveyid'] = $iSurveyId;
        $aData['ldap_queries'] = Yii::app()->getConfig('ldap_queries');

        if (!Yii::app()->request->getPost('submit'))
        {
            $this->_renderWrappedTemplate('token', array('tokenbar', 'ldapform'), $aData);
        }
        else
        {
            $filterduplicatetoken = (Yii::app()->request->getPost('filterduplicatetoken') && Yii::app()->request->getPost('filterduplicatetoken') == 'on');
            $filterblankemail = (Yii::app()->request->getPost('filterblankemail') && Yii::app()->request->getPost('filterblankemail') == 'on');
            
            $ldap_queries = Yii::app()->getConfig('ldap_queries');
            $ldap_server = Yii::app()->getConfig('ldap_server');

            $duplicatelist = array();
            $invalidemaillist = array();
            $tokenoutput .= "\t<tr><td colspan='2' height='4'><strong>"
            . $clang->gT("Uploading LDAP Query") . "</strong></td></tr>\n"
            . "\t<tr><td align='center'>\n";
            $ldapq = Yii::app()->request->getPost('ldapQueries'); // the ldap query id

            $ldap_server_id = $ldap_queries[$ldapq]['ldapServerId'];
            $ldapserver = $ldap_server[$ldap_server_id]['server'];
            $ldapport = $ldap_server[$ldap_server_id]['port'];
            if (isset($ldap_server[$ldap_server_id]['encoding']) &&
            $ldap_server[$ldap_server_id]['encoding'] != 'utf-8' &&
            $ldap_server[$ldap_server_id]['encoding'] != 'UTF-8')
            {
                $ldapencoding = $ldap_server[$ldap_server_id]['encoding'];
            }
            else
            {
                $ldapencoding = '';
            }

            // define $attrlist: list of attributes to read from users' entries
            $attrparams = array('firstname_attr', 'lastname_attr',
            'email_attr', 'token_attr', 'language');

            $aTokenAttr = getAttributeFieldNames($iSurveyId);
            foreach ($aTokenAttr as $thisattrfieldname)
            {
                $attridx = substr($thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                $attrparams[] = "attr" . $attridx;
            }

            foreach ($attrparams as $id => $attr)
            {
                if (array_key_exists($attr, $ldap_queries[$ldapq]) &&
                $ldap_queries[$ldapq][$attr] != '')
                {
                    $attrlist[] = $ldap_queries[$ldapq][$attr];
                }
            }

            // Open connection to server
            $ds = ldap_getCnx($ldap_server_id);

            if ($ds)
            {
                // bind to server
                $resbind = ldap_bindCnx($ds, $ldap_server_id);

                if ($resbind)
                {
                    $ResArray = array();
                    $resultnum = ldap_doTokenSearch($ds, $ldapq, $ResArray, $iSurveyId);
                    $xz = 0; // imported token count
                    $xv = 0; // meet minim requirement count
                    $xy = 0; // check for duplicates
                    $duplicatecount = 0; // duplicate tokens skipped count
                    $invalidemailcount = 0;

                    if ($resultnum >= 1)
                    {
                        foreach ($ResArray as $responseGroupId => $responseGroup)
                        {
                            for ($j = 0; $j < $responseGroup['count']; $j++)
                            {
                                // first let's initialize everything to ''
                                $myfirstname = '';
                                $mylastname = '';
                                $myemail = '';
                                $mylanguage = '';
                                $mytoken = '';
                                $myattrArray = array();

                                // The first 3 attrs MUST exist in the ldap answer
                                // ==> send PHP notice msg to apache logs otherwise
                                $meetminirequirements = true;
                                if (isset($responseGroup[$j][$ldap_queries[$ldapq]['firstname_attr']]) &&
                                isset($responseGroup[$j][$ldap_queries[$ldapq]['lastname_attr']])
                                )
                                {
                                    // minimum requirement for ldap
                                    // * at least a firstanme
                                    // * at least a lastname
                                    // * if filterblankemail is set (default): at least an email address
                                    $myfirstname = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['firstname_attr']]);
                                    $mylastname = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['lastname_attr']]);
                                    if (isset($responseGroup[$j][$ldap_queries[$ldapq]['email_attr']]))
                                    {
                                        $myemail = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['email_attr']]);
                                        $myemail = sanitize_email($myemail);
                                        ++$xv;
                                    }
                                    elseif ($filterblankemail !== true)
                                    {
                                        $myemail = '';
                                        ++$xv;
                                    }
                                    else
                                    {
                                        $meetminirequirements = false;
                                    }
                                }
                                else
                                {
                                    $meetminirequirements = false;
                                }

                                // The following attrs are optionnal
                                if (isset($responseGroup[$j][$ldap_queries[$ldapq]['token_attr']]))
                                    $mytoken = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['token_attr']]);

                                foreach ($aTokenAttr as $thisattrfieldname)
                                {
                                    $attridx = substr($thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                    if (isset($ldap_queries[$ldapq]['attr' . $attridx]) &&
                                    isset($responseGroup[$j][$ldap_queries[$ldapq]['attr' . $attridx]]))
                                        $myattrArray[$attridx] = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['attr' . $attridx]]);
                                }

                                if (isset($responseGroup[$j][$ldap_queries[$ldapq]['language']]))
                                    $mylanguage = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['language']]);

                                // In case Ldap Server encoding isn't UTF-8, let's translate
                                // the strings to UTF-8
                                if ($ldapencoding != '')
                                {
                                    $myfirstname = @mb_convert_encoding($myfirstname, "UTF-8", $ldapencoding);
                                    $mylastname = @mb_convert_encoding($mylastname, "UTF-8", $ldapencoding);
                                    foreach ($aTokenAttr as $thisattrfieldname)
                                    {
                                        $attridx = substr($thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                        @mb_convert_encoding($myattrArray[$attridx], "UTF-8", $ldapencoding);
                                    }
                                }

                                // Now check for duplicates or bad formatted email addresses
                                $dupfound = false;
                                $invalidemail = false;
                                if ($filterduplicatetoken)
                                {
                                    $dupquery = "SELECT count(tid) from {{tokens_".intval($iSurveyId)."}} where email=:email and firstname=:firstname and lastname=:lastname";
                                    $dupresult = Yii::app()->db->createCommand($dupquery)->bindParam(":email", $myemail, PDO::PARAM_STR)->bindParam(":firstname", $myfirstname, PDO::PARAM_STR)->bindParam(":lastname", $mylastname, PDO::PARAM_STR)->queryScalar();
                                    if ($dupresult > 0)
                                    {
                                        $dupfound = true;
                                        $duplicatelist[] = $myfirstname . " " . $mylastname . " (" . $myemail . ")";
                                        $xy++;
                                    }
                                }
                                if ($filterblankemail && $myemail == '')
                                {
                                    $invalidemail = true;
                                    $invalidemaillist[] = $myfirstname . " " . $mylastname . " ( )";
                                }
                                elseif ($myemail != '' && !validateEmailAddress($myemail))
                                {
                                    $invalidemail = true;
                                    $invalidemaillist[] = $myfirstname . " " . $mylastname . " (" . $myemail . ")";
                                }

                                if ($invalidemail)
                                {
                                    ++$invalidemailcount;
                                }
                                elseif ($dupfound)
                                {
                                    ++$duplicatecount;
                                }
                                elseif ($meetminirequirements === true)
                                {
                                    // No issue, let's import
                                    $iq = "INSERT INTO {{tokens_".intval($iSurveyId)."}} \n"
                                    . "(firstname, lastname, email, emailstatus, token, language";

                                    foreach ($aTokenAttr as $thisattrfieldname)
                                    {
                                        $attridx = substr($thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                        if (!empty($myattrArray[$attridx]))
                                        {
                                            $iq .= ", ".Yii::app()->db->quoteColumnName($thisattrfieldname);
                                        }
                                    }
                                    $iq .=") \n"
                                    . "VALUES (" . Yii::app()->db->quoteValue($myfirstname) . ", " . Yii::app()->db->quoteValue($mylastname) . ", " . Yii::app()->db->quoteValue($myemail) . ", 'OK', " . Yii::app()->db->quoteValue($mytoken) . ", " . Yii::app()->db->quoteValue($mylanguage) . "";

                                    foreach ($aTokenAttr as $thisattrfieldname)
                                    {
                                        $attridx = substr($thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                        if (!empty($myattrArray[$attridx]))
                                        {
                                            $iq .= ", " . Yii::app()->db->quoteValue($myattrArray[$attridx]) . "";
                                        }// dbquote_all encloses str with quotes
                                    }
                                    $iq .= ")";
                                    $ir = Yii::app()->db->createCommand($iq)->execute();
                                    if (!$ir)
                                        $duplicatecount++;
                                    $xz++;
                                    // or die ("Couldn't insert line<br />\n$buffer<br />\n".htmlspecialchars($connect->ErrorMsg())."<pre style='text-align: left'>$iq</pre>\n");
                                }
                            } // End for each entry
                        } // End foreach responseGroup
                    } // End of if resnum >= 1

                    $aData['duplicatelist'] = $duplicatelist;
                    $aData['invalidemaillist'] = $invalidemaillist;
                    $aData['invalidemailcount'] = $invalidemailcount;
                    $aData['resultnum'] = $resultnum;
                    $aData['xv'] = $xv;
                    $aData['xy'] = $xy;
                    $aData['xz'] = $xz;

                    $this->_renderWrappedTemplate('token', array('tokenbar', 'ldappost'), $aData);
                }
                else
                {
                    $aData['sError'] = $clang->gT("Can't bind to the LDAP directory");
                    $this->_renderWrappedTemplate('token', array('tokenbar', 'ldapform'), $aData);
                }
                @ldap_close($ds);
            }
            else
            {
                $aData['sError'] = $clang->gT("Can't connect to the LDAP directory");
                $this->_renderWrappedTemplate('token', array('tokenbar', 'ldapform'), $aData);
            }
        }
    }

    /**
    * import from csv
    */
    function import($iSurveyId)
    {
        $clang = $this->getController()->lang;
        $iSurveyId = (int) $iSurveyId;
        if (!hasSurveyPermission($iSurveyId, 'tokens', 'import'))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }

        $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts') . 'tokens.js');

        $aEncodings = array(
        "armscii8" => $clang->gT("ARMSCII-8 Armenian")
        , "ascii" => $clang->gT("US ASCII")
        , "auto" => $clang->gT("Automatic")
        , "big5" => $clang->gT("Big5 Traditional Chinese")
        , "binary" => $clang->gT("Binary pseudo charset")
        , "cp1250" => $clang->gT("Windows Central European")
        , "cp1251" => $clang->gT("Windows Cyrillic")
        , "cp1256" => $clang->gT("Windows Arabic")
        , "cp1257" => $clang->gT("Windows Baltic")
        , "cp850" => $clang->gT("DOS West European")
        , "cp852" => $clang->gT("DOS Central European")
        , "cp866" => $clang->gT("DOS Russian")
        , "cp932" => $clang->gT("SJIS for Windows Japanese")
        , "dec8" => $clang->gT("DEC West European")
        , "eucjpms" => $clang->gT("UJIS for Windows Japanese")
        , "euckr" => $clang->gT("EUC-KR Korean")
        , "gb2312" => $clang->gT("GB2312 Simplified Chinese")
        , "gbk" => $clang->gT("GBK Simplified Chinese")
        , "geostd8" => $clang->gT("GEOSTD8 Georgian")
        , "greek" => $clang->gT("ISO 8859-7 Greek")
        , "hebrew" => $clang->gT("ISO 8859-8 Hebrew")
        , "hp8" => $clang->gT("HP West European")
        , "keybcs2" => $clang->gT("DOS Kamenicky Czech-Slovak")
        , "koi8r" => $clang->gT("KOI8-R Relcom Russian")
        , "koi8u" => $clang->gT("KOI8-U Ukrainian")
        , "latin1" => $clang->gT("cp1252 West European")
        , "latin2" => $clang->gT("ISO 8859-2 Central European")
        , "latin5" => $clang->gT("ISO 8859-9 Turkish")
        , "latin7" => $clang->gT("ISO 8859-13 Baltic")
        , "macce" => $clang->gT("Mac Central European")
        , "macroman" => $clang->gT("Mac West European")
        , "sjis" => $clang->gT("Shift-JIS Japanese")
        , "swe7" => $clang->gT("7bit Swedish")
        , "tis620" => $clang->gT("TIS620 Thai")
        , "ucs2" => $clang->gT("UCS-2 Unicode")
        , "ujis" => $clang->gT("EUC-JP Japanese")
        , "utf8" => $clang->gT("UTF-8 Unicode"));

        if (Yii::app()->request->getPost('submit'))
        {
            if (Yii::app()->request->getPost('csvcharset') && Yii::app()->request->getPost('csvcharset'))  //sanitize charset - if encoding is not found sanitize to 'auto'
            {
                $uploadcharset = Yii::app()->request->getPost('csvcharset');
                if (!array_key_exists($uploadcharset, $aEncodings))
                {
                    $uploadcharset = 'auto';
                }
                $filterduplicatetoken = (Yii::app()->request->getPost('filterduplicatetoken') && Yii::app()->request->getPost('filterduplicatetoken') == 'on');
                $filterblankemail = (Yii::app()->request->getPost('filterblankemail') && Yii::app()->request->getPost('filterblankemail') == 'on');
            }

            $attrfieldnames = getAttributeFieldNames($iSurveyId);
            $duplicatelist = array();
            $invalidemaillist = array();
            $invalidformatlist = array();
            $firstline = array();

            $sPath = Yii::app()->getConfig('tempdir');
            $sFileName = $_FILES['the_file']['name'];
            $sFileTmpName = $_FILES['the_file']['tmp_name'];
            $sFilePath = $sPath . '/' . $sFileName;

            if (!@move_uploaded_file($sFileTmpName, $sFilePath))
            {
                $aData['sError'] = $clang->gT("Upload file not found. Check your permissions and path ({$sFilePath}) for the upload directory");
                $aData['aEncodings'] = $aEncodings;
                $aData['iSurveyId'] = $aData['surveyid'] = $iSurveyId;
                $aData['thissurvey'] = getSurveyInfo($iSurveyId);
                $this->_renderWrappedTemplate('token', array('tokenbar', 'csvupload'), $aData);
            }
            else
            {
                $xz = 0;
                $recordcount = 0;
                $xv = 0;
                // This allows to read file with MAC line endings too
                @ini_set('auto_detect_line_endings', true);
                // open it and trim the ednings
                $tokenlistarray = file($sFilePath);
                $sBaseLanguage = Survey::model()->findByPk($iSurveyId)->language;
                if (!Yii::app()->request->getPost('filterduplicatefields') || (Yii::app()->request->getPost('filterduplicatefields') && count(Yii::app()->request->getPost('filterduplicatefields')) == 0))
                {
                    $filterduplicatefields = array('firstname', 'lastname', 'email');
                }
                else
                {
                    $filterduplicatefields = Yii::app()->request->getPost('filterduplicatefields');
                }
                $separator = returnGlobal('separator');
                foreach ($tokenlistarray as $buffer)
                {
                    $buffer = @mb_convert_encoding($buffer, "UTF-8", $uploadcharset);
                    $firstname = "";
                    $lastname = "";
                    $email = "";
                    $emailstatus = "OK";
                    $token = "";
                    $language = "";
                    $attribute1 = "";
                    $attribute2 = ""; //Clear out values from the last path, in case the next line is missing a value
                    if ($recordcount == 0)
                    {
                        // Pick apart the first line
                        $buffer = removeBOM($buffer);
                        $allowedfieldnames = array('firstname', 'lastname', 'email', 'emailstatus', 'token', 'language', 'validfrom', 'validuntil', 'usesleft');
                        $allowedfieldnames = array_merge($attrfieldnames, $allowedfieldnames);

                        switch ($separator)
                        {
                            case 'comma':
                                $separator = ',';
                                break;
                            case 'semicolon':
                                $separator = ';';
                                break;
                            default:
                                $comma = substr_count($buffer, ',');
                                $semicolon = substr_count($buffer, ';');
                                if ($semicolon > $comma)
                                    $separator = ';'; else
                                    $separator = ',';
                        }
                        $firstline = convertCSVRowToArray($buffer, $separator, '"');
                        $firstline = array_map('trim', $firstline);
                        $ignoredcolumns = array();
                        //now check the first line for invalid fields
                        foreach ($firstline as $index => $fieldname)
                        {
                            $firstline[$index] = preg_replace("/(.*) <[^,]*>$/", "$1", $fieldname);
                            $fieldname = $firstline[$index];
                            if (!in_array($fieldname, $allowedfieldnames))
                            {
                                $ignoredcolumns[] = $fieldname;
                            }
                        }
                        if (!in_array('firstname', $firstline) || !in_array('lastname', $firstline) || !in_array('email', $firstline))
                        {
                            $recordcount = count($tokenlistarray);
                            break;
                        }
                    }
                    else
                    {

                        $line = convertCSVRowToArray($buffer, $separator, '"');

                        if (count($firstline) != count($line))
                        {
                            $invalidformatlist[] = $recordcount;
                            $recordcount++;
                            continue;
                        }
                        $writearray = array_combine($firstline, $line);

                        //kick out ignored columns
                        foreach ($ignoredcolumns as $column)
                        {
                            unset($writearray[$column]);
                        }
                        $dupfound = false;
                        $invalidemail = false;

                        if ($filterduplicatetoken != false)
                        {
                            $dupquery = "SELECT count(tid) from {{tokens_".intval($iSurveyId)."}} where 1=1";
                            foreach ($filterduplicatefields as $field)
                            {
                                if (isset($writearray[$field]))
                                {
                                    $dupquery.= " and ".Yii::app()->db->quoteColumnName($field)." = " . Yii::app()->db->quoteValue($writearray[$field]);
                                }
                            }
                            $dupresult = Yii::app()->db->createCommand($dupquery)->queryScalar();
                            if ($dupresult > 0)
                            {
                                $dupfound = true;
                                $duplicatelist[] = Yii::app()->db->quoteValue($writearray['firstname']) . " " . Yii::app()->db->quoteValue($writearray['lastname']) . " (" . Yii::app()->db->quoteValue($writearray['email']) . ")";
                            }
                        }


                        $writearray['email'] = trim($writearray['email']);

                        //treat blank emails
                        if ($filterblankemail && $writearray['email'] == '')
                        {
                            $invalidemail = true;
                            $invalidemaillist[] = $line[0] . " " . $line[1] . " ( )";
                        }
                        if ($writearray['email'] != '')
                        {
                            $aEmailAddresses = explode(';', $writearray['email']);
                            foreach ($aEmailAddresses as $sEmailaddress)
                            {
                                if (!validateEmailAddress($sEmailaddress))
                                {
                                    $invalidemail = true;
                                    $invalidemaillist[] = $line[0] . " " . $line[1] . " (" . $line[2] . ")";
                                }
                            }
                        }

                        if (!isset($writearray['token']))
                        {
                            $writearray['token'] = '';
                        }
                        else
                        {
                            $writearray['token'] = sanitize_token($writearray['token']);
                        }

                        if (!$dupfound && !$invalidemail)
                        {
                            if (!isset($writearray['emailstatus']) || $writearray['emailstatus'] == '')
                                $writearray['emailstatus'] = "OK";
                            if (!isset($writearray['usesleft']) || $writearray['usesleft'] == '')
                                $writearray['usesleft'] = 1;
                            if (!isset($writearray['language']) || $writearray['language'] == "")
                                $writearray['language'] = $sBaseLanguage;
                            if (isset($writearray['validfrom']) && trim($writearray['validfrom'] == ''))
                            {
                                unset($writearray['validfrom']);
                            }
                            if (isset($writearray['validuntil']) && trim($writearray['validuntil'] == ''))
                            {
                                unset($writearray['validuntil']);
                            }

                            // sanitize it before writing into table
                            foreach ($writearray as $key => $value)
                            {
                                if (substr($value, 0, 1)=='"' && substr($value, -1)=='"')
                                    $value = substr($value, 1, -1);
                                $sanitizedArray[Yii::app()->db->quoteColumnName($key)]= Yii::app()->db->quoteValue($value);
                            }
                            $iq = "INSERT INTO {{tokens_$iSurveyId}} \n"
                            . "(" . implode(',', array_keys($writearray)) . ") \n"
                            . "VALUES (" . implode(",", $sanitizedArray) . ")";
                            $ir = Yii::app()->db->createCommand($iq)->execute();

                            if (!$ir)
                            {
                                $duplicatelist[] = $writearray['firstname'] . " " . $writearray['lastname'] . " (" . $writearray['email'] . ")";
                            }
                            else
                            {
                                $xz++;
                            }
                        }
                        $xv++;
                    }
                    $recordcount++;
                }
                $recordcount = $recordcount - 1;

                unlink($sFilePath);

                $aData['tokenlistarray'] = $tokenlistarray;
                $aData['xz'] = $xz;
                $aData['xv'] = $xv;
                $aData['recordcount'] = $recordcount;
                $aData['firstline'] = $firstline;
                $aData['duplicatelist'] = $duplicatelist;
                $aData['invalidformatlist'] = $invalidformatlist;
                $aData['invalidemaillist'] = $invalidemaillist;
                $aData['thissurvey'] = getSurveyInfo($iSurveyId);
                $aData['iSurveyId'] = $aData['surveyid'] = $iSurveyId;

                $this->_renderWrappedTemplate('token', array('tokenbar', 'csvpost'), $aData);
            }
        }
        else
        {
            $aData['aEncodings'] = $aEncodings;
            $aData['iSurveyId'] = $iSurveyId;
            $aData['thissurvey'] = getSurveyInfo($iSurveyId);
            $aData['surveyid'] = $iSurveyId;
            $this->_renderWrappedTemplate('token', array('tokenbar', 'csvupload'), $aData);
        }
    }

    /**
    * Generate tokens
    */
    function tokenify($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $clang = $this->getController()->lang;
        if (!hasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;

        if (!Yii::app()->request->getParam('ok'))
        {
            $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
            'title' => $clang->gT("Create tokens"),
            'message' => $clang->gT("Clicking 'Yes' will generate tokens for all those in this token list that have not been issued one. Continue?") . "<br /><br />\n"
            . "<input type='submit' value='"
            . $clang->gT("Yes") . "' onclick=\"" . convertGETtoPOST($this->getController()->createUrl("admin/tokens/sa/tokenify/surveyid/$iSurveyId", array('ok'=>'Y'))) . "\" />\n"
            . "<input type='submit' value='"
            . $clang->gT("No") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/index/surveyid/$iSurveyId") . "', '_top')\" />\n"
            . "<br />\n"
            )), $aData);
        }
        else
        {
            //get token length from survey settings
            $newtoken = Tokens_dynamic::model($iSurveyId)->createTokens($iSurveyId);
            $newtokencount = $newtoken['0'];
            $neededtokencount = $newtoken['1'];
            if($neededtokencount>$newtokencount)
            {
                $aData['success'] = false;
                $message = sprintf($clang->ngT('Only %s token has been created.','Only %s tokens have been created.',$newtokencount),$newtokencount)
                         .sprintf($clang->ngT('Need %s token.','Need %s tokens.',$neededtokencount),$neededtokencount);
            }
            else
            {
                $aData['success'] = true;
                $message = sprintf($clang->ngT('%s token has been created.','%s tokens have been created.',$newtokencount),$newtokencount);
            }
            $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
            'title' => $clang->gT("Create tokens"),
            'message' => $message
            )), $aData);
        }
    }

    /**
    * Remove Token Database
    */
    function kill($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $clang = $this->getController()->lang;
        if (!hasSurveyPermission($iSurveyId, 'surveysettings', 'update') && !hasSurveyPermission($iSurveyId, 'tokens', 'delete'))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;

        $date = date('YmdHis');
        /* If there is not a $_POST value of 'ok', then ask if the user is sure they want to
           delete the tokens table */
        $oldtable = "tokens_$iSurveyId";
        $newtable = "old_tokens_{$iSurveyId}_$date";
        $newtableDisplay =  Yii::app()->db->tablePrefix . $newtable;
        if (!Yii::app()->request->getQuery('ok'))
        {
            $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
            'title' => $clang->gT("Delete Tokens Table"),
            'message' => $clang->gT("If you delete this table tokens will no longer be required to access this survey.") . "<br />" . $clang->gT("A backup of this table will be made if you proceed. Your system administrator will be able to access this table.") . "<br />\n"
            . sprintf('("%s")<br /><br />', $newtableDisplay)
            . "<input type='submit' value='"
            . $clang->gT("Delete Tokens") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/kill/surveyid/{$iSurveyId}/ok/Y") . "', '_top')\" />\n"
            . "<input type='submit' value='"
            . $clang->gT("Cancel") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/index/surveyid/{$iSurveyId}") . "', '_top')\" />\n"
            )), $aData);
        }
        else
        /* The user has confirmed they want to delete the tokens table */
        {
            Yii::app()->db->createCommand()->renameTable("{{{$oldtable}}}", "{{{$newtable}}}");
            Survey::model()->updateByPk($iSurveyId, array('attributedescriptions' => "a:0:{}"));

            //Remove any survey_links to the CPDB
            Survey_links::model()->deleteLinksBySurvey($iSurveyId);

            $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
            'title' => $clang->gT("Delete Tokens Table"),
            'message' => '<br />' . $clang->gT("The tokens table has now been removed and tokens are no longer required to access this survey.") . "<br /> " . $clang->gT("A backup of this table has been made and can be accessed by your system administrator.") . "<br />\n"
            . sprintf('("%s")<br /><br />', $newtableDisplay)
            . "<input type='submit' value='"
            . $clang->gT("Main Admin Screen") . "' onclick=\"window.open('" . Yii::app()->getController()->createUrl("admin/survey/sa/view/surveyid/".$iSurveyId) . "', '_top')\" />"
            )), $aData);

            LimeExpressionManager::SetDirtyFlag();  // so that knows that token tables have changed
        }
    }

    function bouncesettings($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $clang = $this->getController()->lang;
        if (!hasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        $aData['thissurvey'] = $aData['settings'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;

        if (!empty($_POST))
        {
            $fieldvalue = array(
            "bounceprocessing" => Yii::app()->request->getPost('bounceprocessing'),
            "bounce_email" => Yii::app()->request->getPost('bounce_email'),
            );

            if (Yii::app()->request->getPost('bounceprocessing') == 'L')
            {
                $fieldvalue['bounceaccountencryption'] = Yii::app()->request->getPost('bounceaccountencryption');
                $fieldvalue['bounceaccountuser'] = Yii::app()->request->getPost('bounceaccountuser');
                $fieldvalue['bounceaccountpass'] = Yii::app()->request->getPost('bounceaccountpass');
                $fieldvalue['bounceaccounttype'] = Yii::app()->request->getPost('bounceaccounttype');
                $fieldvalue['bounceaccounthost'] = Yii::app()->request->getPost('bounceaccounthost');
            }

            $survey = Survey::model()->findByAttributes(array('sid' => $iSurveyId));
            foreach ($fieldvalue as $k => $v)
                $survey->$k = $v;
            $test=$survey->save();

            $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
            'title' => $clang->gT("Bounce settings"),
            'message' => $clang->gT("Bounce settings have been saved."),
            'class' => 'successheader'
            )), $aData);
        }
        else
        {
            $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts') . "tokenbounce.js");
            $this->_renderWrappedTemplate('token', array('tokenbar', 'bounce'), $aData);
        }
    }

    /**
    * Handle token form for addnew/edit actions
    */
    function _handletokenform($iSurveyId, $subaction, $iTokenId="")
    {
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        $clang = $this->getController()->lang;

        Yii::app()->loadHelper("surveytranslator");

        if ($subaction == "edit")
        {
            $aData['tokenid'] = $iTokenId;
            $aData['tokendata'] = Tokens_dynamic::model($iSurveyId)->findByPk($iTokenId);
        }

        $thissurvey = getSurveyInfo($iSurveyId);
        $aAdditionalAttributeFields = $thissurvey['attributedescriptions'];
        $aTokenFieldNames=Yii::app()->db->getSchema()->getTable("{{tokens_$iSurveyId}}",true);
        $aTokenFieldNames=array_keys($aTokenFieldNames->columns);
        $aData['attrfieldnames']=array();
        foreach ($aAdditionalAttributeFields as $sField=>$aAttrData)
        {
            if (in_array($sField,$aTokenFieldNames))
            {
                if ($aAttrData['description']=='')
                {
                    $aAttrData['description']=$sField;
                }
                $aData['attrfieldnames'][(string)$sField]=$aAttrData;
            }
        }
        foreach ($aTokenFieldNames as $sTokenFieldName)
        {
            if (strpos($sTokenFieldName,'attribute_')===0 && (!isset($aData['attrfieldnames']) || !isset($aData['attrfieldnames'][$sTokenFieldName])))
            {
                $aData['attrfieldnames'][$sTokenFieldName]=array('description'=>$sTokenFieldName,'mandatory'=>'N');    
            }
        } 
        
        $aData['thissurvey'] = $thissurvey;
        $aData['surveyid'] = $iSurveyId;
        $aData['subaction'] = $subaction;
        $aData['dateformatdetails'] = getDateFormatData(Yii::app()->session['dateformat']);

        $this->_renderWrappedTemplate('token', array('tokenbar', 'tokenform'), $aData);
    }

    /**
    * Show dialogs and create a new tokens table
    */
    function _newtokentable($iSurveyId)
    {
        $clang = $this->getController()->lang;
        $aSurveyInfo = getSurveyInfo($iSurveyId);
        if (!hasSurveyPermission($iSurveyId, 'surveysettings', 'update') && !HasSurveyPermission($iSurveyId, 'tokens','create'))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("Tokens have not been initialised for this survey.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if ($bTokenExists) //The token table already exist ?
        {
            Yii::app()->session['flashmessage'] = $clang->gT("Tokens already exist for this survey.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // The user have rigth to create token, then don't test right after
        Yii::import('application.helpers.admin.token_helper', true);
        if (Yii::app()->request->getQuery('createtable') == "Y")
        {
            createTokenTable($iSurveyId);
            $this->_renderWrappedTemplate('token', array('message' =>array(
            'title' => $clang->gT("Token control"),
            'message' => $clang->gT("A token table has been created for this survey.") . " (\"" . Yii::app()->db->tablePrefix . "tokens_$iSurveyId\")<br /><br />\n"
            . "<input type='submit' value='"
            . $clang->gT("Continue") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/index/surveyid/$iSurveyId") . "', '_top')\" />\n"
            )));
        }
        /* Restore a previously deleted tokens table */
        elseif (returnGlobal('restoretable') == "Y" && Yii::app()->request->getPost('oldtable'))
        {
            //Rebuild attributedescription value for the surveys table
            $table = Yii::app()->db->schema->getTable(Yii::app()->request->getPost('oldtable'));
            $fields=array_filter(array_keys($table->columns), 'filterForAttributes');
            $fieldcontents = $aSurveyInfo['attributedescriptions'];        
            if (!is_array($fieldcontents)) $fieldcontents=array();
            foreach ($fields as $fieldname)
            {
                $name=$fieldname;
                if($fieldname[10]=='c') { //This belongs to a cpdb attribute
                    $cpdbattid=substr($fieldname,15);
                    $data=ParticipantAttributeNames::model()->getAttributeName($cpdbattid, Yii::app()->session['adminlang']);
                    $name=$data['attribute_name'];
                }
                if (!isset($fieldcontents[$fieldname]))
                {
                    $fieldcontents[$fieldname] = array(
                                'description' => $name,
                                'mandatory' => 'N',
                                'show_register' => 'N'
                                );
                }
            }
            Survey::model()->updateByPk($iSurveyId, array('attributedescriptions' => serialize($fieldcontents)));


            Yii::app()->db->createCommand()->renameTable(Yii::app()->request->getPost('oldtable'), Yii::app()->db->tablePrefix."tokens_".intval($iSurveyId));
            //Check that the tokens table has the required fields
            Tokens_dynamic::model($iSurveyId)->checkColumns();

            //Add any survey_links from the renamed table
            Survey_links::model()->rebuildLinksFromTokenTable($iSurveyId);

            $this->_renderWrappedTemplate('token', array('message' => array(
            'title' => $clang->gT("Import old tokens"),
            'message' => $clang->gT("A token table has been created for this survey and the old tokens were imported.") . " (\"" . Yii::app()->db->tablePrefix . "tokens_$iSurveyId" . "\")<br /><br />\n"
            . "<input type='submit' value='"
            . $clang->gT("Continue") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/index/surveyid/$iSurveyId") . "', '_top')\" />\n"
            )));

            LimeExpressionManager::SetDirtyFlag();  // so that knows that token tables have changed
        }
        else
        {
            $this->getController()->loadHelper('database');
            $result = Yii::app()->db->createCommand(dbSelectTablesLike("{{old_tokens_".intval($iSurveyId)."_%}}"))->queryAll();
            $tcount = count($result);
            if ($tcount > 0)
            {
                foreach ($result as $rows)
                {
                    $oldlist[] = reset($rows);
                }
                $aData['oldlist'] = $oldlist;
            }

            $thissurvey = getSurveyInfo($iSurveyId);
            $aData['thissurvey'] = $thissurvey;
            $aData['surveyid'] = $iSurveyId;
            $aData['tcount'] = $tcount;
            $aData['databasetype'] = Yii::app()->db->getDriverName();

            $this->_renderWrappedTemplate('token', 'tokenwarning', $aData);
        }
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'token', $aViewUrls = array(), $aData = array())
    {
        $aData['imageurl'] = Yii::app()->getConfig('adminimageurl');
        $aData['display']['menu_bars'] = false;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

}
