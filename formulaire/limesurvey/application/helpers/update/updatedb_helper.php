<?PHP
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
*    $Id$
*/

// There will be a file for each database (accordingly named to the dbADO scheme)
// where based on the current database version the database is upgraded
// For this there will be a settings table which holds the last time the database was upgraded

function db_upgrade_all($oldversion) {
    /// This function does anything necessary to upgrade
    /// older versions to match current functionality
    global $modifyoutput, $usertemplaterootdir, $standardtemplaterootdir;
    Yii::app()->loadHelper('database');

    $usertemplaterootdir = Yii::app()->getConfig('usertemplaterootdir');
    $standardtemplaterootdir = Yii::app()->getConfig('standardtemplaterootdir');
    $clang = Yii::app()->lang;
    echo str_pad($clang->gT('The LimeSurvey database is being upgraded').' ('.date('Y-m-d H:i:s').')',14096).".<br /><br />". $clang->gT('Please be patient...')."<br /><br />\n";

    $sDBDriverName=setsDBDriverName();
    setVarchar($sDBDriverName);
    $sVarchar = Yii::app()->getConfig('varchar');
    $sAutoIncrement  = Yii::app()->getConfig('autoincrement');

    $oTransaction=Yii::app()->db->beginTransaction();
    try
    {
        if ($oldversion < 111)
        {
            // Language upgrades from version 110 to 111 because the language names did change

            $aOldNewLanguages=array('german_informal'=>'german-informal',
            'cns'=>'cn-Hans',
            'cnt'=>'cn-Hant',
            'pt_br'=>'pt-BR',
            'gr'=>'el',
            'jp'=>'ja',
            'si'=>'sl',
            'se'=>'sv',
            'vn'=>'vi');
            foreach  ($aOldNewLanguages as $sOldLanguageCode=>$sNewLanguageCode)
            {
                alterLanguageCode($sOldLanguageCode,$sNewLanguageCode);
            }
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>111),"stg_name='DBVersion'");
        }

        if ($oldversion < 112) {
            // New size of the username field (it was previously 20 chars wide)
            Yii::app()->db->createCommand()->alterColumn('{{users}}','users_name',"{$sVarchar}(64) NOT NULL");
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>112),"stg_name='DBVersion'");
        }

        if ($oldversion < 113) {
            //Fixes the collation for the complete DB, tables and columns

            if ($sDBDriverName=='mysql')
            {
                $databasename=getDBConnectionStringProperty('dbname');
                fixMySQLCollations();
                modifyDatabase("","ALTER DATABASE `$databasename` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;");echo $modifyoutput; flush();@ob_flush();
            }
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>113),"stg_name='DBVersion'");
        }

        if ($oldversion < 114) {
            Yii::app()->db->createCommand()->alterColumn('{{saved_control}}','email',"{$sVarchar}(320) NOT NULL");
            Yii::app()->db->createCommand()->alterColumn('{{surveys}}','adminemail',"{$sVarchar}(320) NOT NULL");
            Yii::app()->db->createCommand()->alterColumn('{{users}}','email',"{$sVarchar}(320) NOT NULL");
            Yii::app()->db->createCommand()->insert('{{settings_global}}',array('stg_name'=>'SessionName','stg_value'=>randomChars(64,'ABCDEFGHIJKLMNOPQRSTUVWXYZ!"$%&/()=?`+*~#",;.:abcdefghijklmnopqrstuvwxyz123456789')));
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>114),"stg_name='DBVersion'");
        }

        if ($oldversion < 126) {

            addColumn('{{surveys}}','printanswers',"{$sVarchar}(1) default 'N'");
            addColumn('{{surveys}}','listpublic',"{$sVarchar}(1) default 'N'");

            upgradeSurveyTables126();
            upgradeTokenTables126();

            // Create quota table
            createTable('{{quota}}',array(
            'id' => 'pk',
            'sid' => 'integer',
            'qlimit' => 'integer',
            'name' => 'string',
            'action' => 'integer',
            'active' => 'integer NOT NULL DEFAULT 1'
            ));

            // Create quota_members table
            createTable('{{quota_members}}',array(
            'id' => 'pk',
            'sid' => 'integer',
            'qid' => 'integer',
            'quota_id' => 'integer',
            'code' => $sVarchar.'(5)'
            ));
            Yii::app()->db->createCommand()->createIndex('sid','{{quota_members}}','sid,qid,quota_id,code',true);


            // Create templates_rights table
            createTable('{{templates_rights}}',array(
            'uid' => 'integer NOT NULL',
            'folder' => 'string NOT NULL',
            'use' => 'integer',
            'PRIMARY KEY (uid, folder)'
            ));

            // Create templates table
            createTable('{{templates}}',array(
            'folder' => 'string NOT NULL',
            'creator' => 'integer NOT NULL',
            'PRIMARY KEY (folder)'
            ));

            // Rename Norwegian language codes
            alterLanguageCode('no','nb');

            addColumn('{{surveys}}','htmlemail',"{$sVarchar}(1) default 'N'");
            addColumn('{{surveys}}','tokenanswerspersistence',"{$sVarchar}(1) default 'N'");
            addColumn('{{surveys}}','usecaptcha',"{$sVarchar}(1) default 'N'");
            addColumn('{{surveys}}','bounce_email','text');
            addColumn('{{users}}','htmleditormode',"{$sVarchar}(7) default 'default'");
            addColumn('{{users}}','superadmin',"integer NOT NULL default '0'");
            addColumn('{{questions}}','lid1',"integer NOT NULL default '0'");

            alterColumn('{{conditions}}','value',"string",false,'');
            alterColumn('{{labels}}','title',"text");

            Yii::app()->db->createCommand()->update('{{users}}',array('superadmin'=>1),"create_survey=1 AND create_user=1 AND move_user=1 AND delete_user=1 AND configurator=1");
            Yii::app()->db->createCommand()->update('{{conditions}}',array('method'=>'=='),"(method is null) or method='' or method='0'");

            dropColumn('{{users}}','move_user');

            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>126),"stg_name='DBVersion'");
        }

        if ($oldversion < 127) {
            modifyDatabase("","create index answers_idx2 on {{answers}} (sortorder)"); echo $modifyoutput;
            modifyDatabase("","create index assessments_idx2 on {{assessments}} (sid)"); echo $modifyoutput;
            modifyDatabase("","create index assessments_idx3 on {{assessments}} (gid)"); echo $modifyoutput;
            modifyDatabase("","create index conditions_idx2 on {{conditions}} (qid)"); echo $modifyoutput;
            modifyDatabase("","create index conditions_idx3 on {{conditions}} (cqid)"); echo $modifyoutput;
            modifyDatabase("","create index groups_idx2 on {{groups}} (sid)"); echo $modifyoutput;
            modifyDatabase("","create index question_attributes_idx2 on {{question_attributes}} (qid)"); echo $modifyoutput;
            modifyDatabase("","create index questions_idx2 on {{questions}} (sid)"); echo $modifyoutput;
            modifyDatabase("","create index questions_idx3 on {{questions}} (gid)"); echo $modifyoutput;
            modifyDatabase("","create index questions_idx4 on {{questions}} (type)"); echo $modifyoutput;
            modifyDatabase("","create index quota_idx2 on {{quota}} (sid)"); echo $modifyoutput;
            modifyDatabase("","create index saved_control_idx2 on {{saved_control}} (sid)"); echo $modifyoutput;
            modifyDatabase("","create index user_in_groups_idx1 on {{user_in_groups}} (ugid, uid)"); echo $modifyoutput;
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>127),"stg_name='DBVersion'");
        }

        if ($oldversion < 128) {
            upgradeTokens128();
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>128),"stg_name='DBVersion'");
        }

        if ($oldversion < 129) {
            addColumn('{{surveys}}','startdate',"datetime");
            addColumn('{{surveys}}','usestartdate',"{$sVarchar}(1) NOT NULL default 'N'");
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>129),"stg_name='DBVersion'");
        }

        if ($oldversion < 130)
        {
            addColumn('{{conditions}}','scenario',"integer NOT NULL default '1'");
            Yii::app()->db->createCommand()->update('{{conditions}}',array('scenario'=>'1'),"(scenario is null) or scenario=0");
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>130),"stg_name='DBVersion'");
        }

        if ($oldversion < 131)
        {
            addColumn('{{surveys}}','publicstatistics',"{$sVarchar}(1) NOT NULL default 'N'");
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>131),"stg_name='DBVersion'");
        }

        if ($oldversion < 132)
        {
            addColumn('{{surveys}}','publicgraphs',"{$sVarchar}(1) NOT NULL default 'N'");
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>132),"stg_name='DBVersion'");
        }

        if ($oldversion < 133)
        {
            addColumn('{{users}}','one_time_pw','binary');
            // Add new assessment setting
            addColumn('{{surveys}}','assessments',"{$sVarchar}(1) NOT NULL default 'N'");
            // add new assessment value fields to answers & labels
            addColumn('{{answers}}','assessment_value',"integer NOT NULL default '0'");
            addColumn('{{labels}}','assessment_value',"integer NOT NULL default '0'");
            // copy any valid codes from code field to assessment field
            switch ($sDBDriverName){
                case 'mysql':
                    Yii::app()->db->createCommand("UPDATE {{answers}} SET assessment_value=CAST(`code` as SIGNED) where `code` REGEXP '^-?[0-9]+$'")->execute();
                    Yii::app()->db->createCommand("UPDATE {{labels}} SET assessment_value=CAST(`code` as SIGNED) where `code` REGEXP '^-?[0-9]+$'")->execute();
                    // copy assessment link to message since from now on we will have HTML assignment messages
                    Yii::app()->db->createCommand("UPDATE {{assessments}} set message=concat(replace(message,'/''',''''),'<br /><a href=\"',link,'\">',link,'</a>')")->execute();
                    break;
                case 'mssql':
                    try{
                    Yii::app()->db->createCommand("UPDATE {{answers}} SET assessment_value=CAST([code] as int) WHERE ISNUMERIC([code])=1")->execute();
                    Yii::app()->db->createCommand("UPDATE {{labels}} SET assessment_value=CAST([code] as int) WHERE ISNUMERIC([code])=1")->execute();
                    } catch(Exception $e){};
                    // copy assessment link to message since from now on we will have HTML assignment messages
                    alterColumn('{{assessments}}','link',"varchar(max)",false);
                    alterColumn('{{assessments}}','message',"varchar(max)",false);
                    Yii::app()->db->createCommand("UPDATE {{assessments}} set message=replace(message,'/''','''')+'<br /><a href=\"'+link+'\">'+link+'</a>'")->execute();
                    break;
                case 'pgsql':
                    Yii::app()->db->createCommand("UPDATE {{answers}} SET assessment_value=CAST(code as integer) where code ~ '^[0-9]+'")->execute();
                    Yii::app()->db->createCommand("UPDATE {{labels}} SET assessment_value=CAST(code as integer) where code ~ '^[0-9]+'")->execute();
                    // copy assessment link to message since from now on we will have HTML assignment messages
                    Yii::app()->db->createCommand("UPDATE {{assessments}} set message=replace(message,'/''','''')||'<br /><a href=\"'||link||'\">'||link||'</a>'")->execute();
                    break;
                default: die('Unkown database type');
            }
            // activate assessment where assessment rules exist
            Yii::app()->db->createCommand("UPDATE {{surveys}} SET assessments='Y' where sid in (SELECT sid FROM {{assessments}} group by sid)")->execute();
            // add language field to assessment table
            addColumn('{{assessments}}','language',"{$sVarchar}(20) NOT NULL default 'en'");
            // update language field with default language of that particular survey
            Yii::app()->db->createCommand("UPDATE {{assessments}} SET language=(select language from {{surveys}} where sid={{assessments}}.sid)")->execute();
            // drop the old link field
            dropColumn('{{assessments}}','link');

            // Add new fields to survey language settings
            addColumn('{{surveys_languagesettings}}','surveyls_url',"string");
            addColumn('{{surveys_languagesettings}}','surveyls_endtext','text');
            // copy old URL fields ot language specific entries
            Yii::app()->db->createCommand("UPDATE {{surveys_languagesettings}} set surveyls_url=(select url from {{surveys}} where sid={{surveys_languagesettings}}.surveyls_survey_id)")->execute();
            // drop old URL field
            dropColumn('{{surveys}}','url');
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>133),"stg_name='DBVersion'");
        }

        if ($oldversion < 134)
        {
            // Add new tokens setting
            addColumn('{{surveys}}','usetokens',"{$sVarchar}(1) NOT NULL default 'N'");
            addColumn('{{surveys}}','attributedescriptions','text');
            dropColumn('{{surveys}}','attribute1');
            dropColumn('{{surveys}}','attribute2');
            upgradeTokenTables134();
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>134),"stg_name='DBVersion'");
        }

        if ($oldversion < 135)
        {
            alterColumn('{{question_attributes}}','value','text');
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>135),"stg_name='DBVersion'");
        }

        if ($oldversion < 136) //New Quota Functions
        {
            addColumn('{{quota}}','autoload_url',"integer NOT NULL default 0");
            // Create quota table
            $fields = array(
            'quotals_id' => 'pk',
            'quotals_quota_id' => 'integer NOT NULL DEFAULT 0',
            'quotals_language' => "{$sVarchar}(45) NOT NULL default 'en'",
            'quotals_name' => 'string',
            'quotals_message' => 'text NOT NULL',
            'quotals_url' => 'string',
            'quotals_urldescrip' => 'string',
            );
            createTable('{{quota_languagesettings}}',$fields);
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>136),"stg_name='DBVersion'");
        }

        if ($oldversion < 137) //New Quota Functions
        {
            addColumn('{{surveys_languagesettings}}','surveyls_dateformat',"integer NOT NULL default 1");
            addColumn('{{users}}','dateformat',"integer NOT NULL default 1");
            Yii::app()->db->createCommand()->update('{{surveys}}',array('startdate'=>NULL),"usestartdate='N'");
            Yii::app()->db->createCommand()->update('{{surveys}}',array('expires'=>NULL),"useexpiry='N'");
            dropColumn('{{surveys}}','useexpiry');
            dropColumn('{{surveys}}','usestartdate');
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>137),"stg_name='DBVersion'");
        }

        if ($oldversion < 138) //Modify quota field
        {
            alterColumn('{{quota_members}}','code',"{$sVarchar}(11)");
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>138),"stg_name='DBVersion'");
        }

        if ($oldversion < 139) //Modify quota field
        {
            upgradeSurveyTables139();
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>139),"stg_name='DBVersion'");
        }

        if ($oldversion < 140) //Modify surveys table
        {
            addColumn('{{surveys}}','emailresponseto','text');
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>140),"stg_name='DBVersion'");
        }

        if ($oldversion < 141) //Modify surveys table
        {
            addColumn('{{surveys}}','tokenlength','integer NOT NULL default 15');
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>141),"stg_name='DBVersion'");
        }

        if ($oldversion < 142) //Modify surveys table
        {
            upgradeQuestionAttributes142();
            Yii::app()->db->createCommand()->alterColumn('{{surveys}}','expires',"datetime");
            Yii::app()->db->createCommand()->alterColumn('{{surveys}}','startdate',"datetime");
            Yii::app()->db->createCommand()->update('{{question_attributes}}',array('value'=>0),"value='false'");
            Yii::app()->db->createCommand()->update('{{question_attributes}}',array('value'=>1),"value='true'");
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>142),"stg_name='DBVersion'");
        }

        if ($oldversion < 143)
        {
            addColumn('{{questions}}','parent_qid','integer NOT NULL default 0');
            addColumn('{{answers}}','scale_id','integer NOT NULL default 0');
            addColumn('{{questions}}','scale_id','integer NOT NULL default 0');
            addColumn('{{questions}}','same_default','integer NOT NULL default 0');
            dropPrimaryKey('answers');
            addPrimaryKey('answers', array('qid','code','language','scale_id'));

            $fields = array(
            'qid' => "integer NOT NULL default 0",
            'scale_id' => 'integer NOT NULL default 0',
            'sqid' => 'integer  NOT NULL default 0',
            'language' => $sVarchar.'(20) NOT NULL',
            'specialtype' => $sVarchar."(20) NOT NULL default ''",
            'defaultvalue' => 'text',
            );
            createTable('{{defaultvalues}}',$fields);
            addPrimaryKey('defaultvalues', array('qid','specialtype','language','scale_id','sqid'));

            // -Move all 'answers' that are subquestions to the questions table
            // -Move all 'labels' that are answers to the answers table
            // -Transscribe the default values where applicable
            // -Move default values from answers to questions
            upgradeTables143();

            dropColumn('{{answers}}','default_value');
            dropColumn('{{questions}}','lid');
            dropColumn('{{questions}}','lid1');

            $fields = array(
            'sesskey' => "{$sVarchar}(64) NOT NULL DEFAULT ''",
            'expiry' => "datetime NOT NULL",
            'expireref' => "{$sVarchar}(250) DEFAULT ''",
            'created' => "datetime NOT NULL",
            'modified' => "datetime NOT NULL",
            'sessdata' => 'text'
            );
            createTable('{{sessions}}',$fields);
            addPrimaryKey('sessions',array('sesskey'));
            Yii::app()->db->createCommand()->createIndex('sess2_expiry','{{sessions}}','expiry');
            Yii::app()->db->createCommand()->createIndex('sess2_expireref','{{sessions}}','expireref');
            // Move all user templates to the new user template directory
            echo sprintf($clang->gT("Moving user templates to new location at %s..."),$usertemplaterootdir)."<br />";
            $myDirectory = opendir($standardtemplaterootdir);
            $aFailedTemplates=array();
            // get each entry
            while($entryName = readdir($myDirectory)) {
                if (!in_array($entryName,array('.','..','.svn')) && is_dir($standardtemplaterootdir.DIRECTORY_SEPARATOR.$entryName) && !isStandardTemplate($entryName))
                {
                    if (!rename($standardtemplaterootdir.DIRECTORY_SEPARATOR.$entryName,$usertemplaterootdir.DIRECTORY_SEPARATOR.$entryName))
                    {
                        $aFailedTemplates[]=$entryName;
                    };
                }
            }
            if (count($aFailedTemplates)>0)
            {
                echo "The following templates at {$standardtemplaterootdir} could not be moved to the new location at {$usertemplaterootdir}:<br /><ul>";
                foreach ($aFailedTemplates as $sFailedTemplate)
                {
                    echo "<li>{$sFailedTemplate}</li>";
                }
                echo "</ul>Please move these templates manually after the upgrade has finished.<br />";
            }
            // close directory
            closedir($myDirectory);
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>143),"stg_name='DBVersion'");
        }

        if ($oldversion < 145)
        {
            addColumn('{{surveys}}','savetimings',"{$sVarchar}(1) NULL default 'N'");
            addColumn('{{surveys}}','showXquestions',"{$sVarchar}(1) NULL default 'Y'");
            addColumn('{{surveys}}','showgroupinfo',"{$sVarchar}(1) NULL default 'B'");
            addColumn('{{surveys}}','shownoanswer',"{$sVarchar}(1) NULL default 'Y'");
            addColumn('{{surveys}}','showqnumcode',"{$sVarchar}(1) NULL default 'X'");
            addColumn('{{surveys}}','bouncetime','integer');
            addColumn('{{surveys}}','bounceprocessing',"{$sVarchar}(1) NULL default 'N'");
            addColumn('{{surveys}}','bounceaccounttype',"{$sVarchar}(4)");
            addColumn('{{surveys}}','bounceaccounthost',"{$sVarchar}(200)");
            addColumn('{{surveys}}','bounceaccountpass',"{$sVarchar}(100)");
            addColumn('{{surveys}}','bounceaccountencryption',"{$sVarchar}(3)");
            addColumn('{{surveys}}','bounceaccountuser',"{$sVarchar}(200)");
            addColumn('{{surveys}}','showwelcome',"{$sVarchar}(1) default 'Y'");
            addColumn('{{surveys}}','showprogress',"{$sVarchar}(1) default 'Y'");
            addColumn('{{surveys}}','allowjumps',"{$sVarchar}(1) default 'N'");
            addColumn('{{surveys}}','navigationdelay',"integer default 0");
            addColumn('{{surveys}}','nokeyboard',"{$sVarchar}(1) default 'N'");
            addColumn('{{surveys}}','alloweditaftercompletion',"{$sVarchar}(1) default 'N'");


            $fields = array(
            'sid' => "integer NOT NULL",
            'uid' => "integer NOT NULL",
            'permission' => $sVarchar.'(20) NOT NULL',
            'create_p' => "integer NOT NULL default 0",
            'read_p' => "integer NOT NULL default 0",
            'update_p' => "integer NOT NULL default 0",
            'delete_p' => "integer NOT NULL default 0",
            'import_p' => "integer NOT NULL default 0",
            'export_p' => "integer NOT NULL default 0"
            );
            createTable('{{survey_permissions}}',$fields);
            addPrimaryKey('survey_permissions', array('sid','uid','permission'));

            upgradeSurveyPermissions145();

            // drop the old survey rights table
            Yii::app()->db->createCommand()->dropTable('{{surveys_rights}}');

            // Add new fields for email templates
            addColumn('{{surveys_languagesettings}}','email_admin_notification_subj',"string");
            addColumn('{{surveys_languagesettings}}','email_admin_responses_subj',"string");
            addColumn('{{surveys_languagesettings}}','email_admin_notification',"text");
            addColumn('{{surveys_languagesettings}}','email_admin_responses',"text");

            //Add index to questions table to speed up subquestions
            Yii::app()->db->createCommand()->createIndex('parent_qid_idx','{{questions}}','parent_qid');

            addColumn('{{surveys}}','emailnotificationto',"text");

            upgradeSurveys145();
            dropColumn('{{surveys}}','notification');
            alterColumn('{{conditions}}','method',"{$sVarchar}(5)",false,'');

            Yii::app()->db->createCommand()->renameColumn('{{surveys}}','private','anonymized');
            Yii::app()->db->createCommand()->update('{{surveys}}',array('anonymized'=>'N'),"anonymized is NULL");
            alterColumn('{{surveys}}','anonymized',"{$sVarchar}(1)",false,'N');

            //now we clean up things that were not properly set in previous DB upgrades
            Yii::app()->db->createCommand()->update('{{answers}}',array('answer'=>''),"answer is NULL");
            Yii::app()->db->createCommand()->update('{{assessments}}',array('scope'=>''),"scope is NULL");
            Yii::app()->db->createCommand()->update('{{assessments}}',array('name'=>''),"name is NULL");
            Yii::app()->db->createCommand()->update('{{assessments}}',array('message'=>''),"message is NULL");
            Yii::app()->db->createCommand()->update('{{assessments}}',array('minimum'=>''),"minimum is NULL");
            Yii::app()->db->createCommand()->update('{{assessments}}',array('maximum'=>''),"maximum is NULL");
            Yii::app()->db->createCommand()->update('{{groups}}',array('group_name'=>''),"group_name is NULL");
            Yii::app()->db->createCommand()->update('{{labels}}',array('code'=>''),"code is NULL");
            Yii::app()->db->createCommand()->update('{{labelsets}}',array('label_name'=>''),"label_name is NULL");
            Yii::app()->db->createCommand()->update('{{questions}}',array('type'=>'T'),"type is NULL");
            Yii::app()->db->createCommand()->update('{{questions}}',array('title'=>''),"title is NULL");
            Yii::app()->db->createCommand()->update('{{questions}}',array('question'=>''),"question is NULL");
            Yii::app()->db->createCommand()->update('{{questions}}',array('other'=>'N'),"other is NULL");

            alterColumn('{{answers}}','answer',"text",false);
            alterColumn('{{answers}}','assessment_value','integer',false , '0');
            alterColumn('{{assessments}}','scope',"{$sVarchar}(5)",false , '');
            alterColumn('{{assessments}}','name',"text",false);
            alterColumn('{{assessments}}','message',"text",false);
            alterColumn('{{assessments}}','minimum',"{$sVarchar}(50)",false , '');
            alterColumn('{{assessments}}','maximum',"{$sVarchar}(50)",false , '');
            // change the primary index to include language
            if ($sDBDriverName=='mysql') // special treatment for mysql because this needs to be in one step since an AUTOINC field is involved
            {
                Yii::app()->db->createCommand("ALTER TABLE {{assessments}} DROP PRIMARY KEY, ADD PRIMARY KEY (`id`, `language`)")->execute();
            }
            else
            {
                dropPrimaryKey('assessments');
                addPrimaryKey('assessments',array('id','language'));
            }


            alterColumn('{{conditions}}','cfieldname',"{$sVarchar}(50)",false , '');
            dropPrimaryKey('defaultvalues');
            alterColumn('{{defaultvalues}}','specialtype',"{$sVarchar}(20)",false , '');
            addPrimaryKey('defaultvalues', array('qid','specialtype','language','scale_id','sqid'));

            alterColumn('{{groups}}','group_name',"{$sVarchar}(100)",false , '');
            alterColumn('{{labels}}','code',"{$sVarchar}(5)",false , '');
            alterColumn('{{labels}}','language',"{$sVarchar}(20)",false , 'en');
            alterColumn('{{labelsets}}','label_name',"{$sVarchar}(100)",false , '');
            alterColumn('{{questions}}','parent_qid','integer',false ,'0');
            alterColumn('{{questions}}','title',"{$sVarchar}(20)",false , '');
            alterColumn('{{questions}}','question',"text",false);
            alterColumn('{{questions}}','type',"{$sVarchar}(1)",false , 'T');
            try{ Yii::app()->db->createCommand()->createIndex('questions_idx4','{{questions}}','type');} catch(Exception $e){};
            alterColumn('{{questions}}','other',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{questions}}','mandatory',"{$sVarchar}(1)");
            alterColumn('{{question_attributes}}','attribute',"{$sVarchar}(50)");
            alterColumn('{{quota}}','qlimit','integer');

            Yii::app()->db->createCommand()->update('{{saved_control}}',array('identifier'=>''),"identifier is NULL");
            alterColumn('{{saved_control}}','identifier',"text",false);
            Yii::app()->db->createCommand()->update('{{saved_control}}',array('access_code'=>''),"access_code is NULL");
            alterColumn('{{saved_control}}','access_code',"text",false);
            alterColumn('{{saved_control}}','email',"{$sVarchar}(320)");
            Yii::app()->db->createCommand()->update('{{saved_control}}',array('ip'=>''),"ip is NULL");
            alterColumn('{{saved_control}}','ip',"text",false);
            Yii::app()->db->createCommand()->update('{{saved_control}}',array('saved_thisstep'=>''),"saved_thisstep is NULL");
            alterColumn('{{saved_control}}','saved_thisstep',"text",false);
            Yii::app()->db->createCommand()->update('{{saved_control}}',array('status'=>''),"status is NULL");
            alterColumn('{{saved_control}}','status',"{$sVarchar}(1)",false , '');
            Yii::app()->db->createCommand()->update('{{saved_control}}',array('saved_date'=>'1980-01-01 00:00:00'),"saved_date is NULL");
            alterColumn('{{saved_control}}','saved_date',"datetime",false);
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>''),"stg_value is NULL");
            alterColumn('{{settings_global}}','stg_value',"string",false , '');

            alterColumn('{{surveys}}','admin',"{$sVarchar}(50)");
            Yii::app()->db->createCommand()->update('{{surveys}}',array('active'=>'N'),"active is NULL");

            alterColumn('{{surveys}}','active',"{$sVarchar}(1)",false , 'N');

            alterColumn('{{surveys}}','startdate',"datetime");
            alterColumn('{{surveys}}','adminemail',"{$sVarchar}(320)");
            alterColumn('{{surveys}}','anonymized',"{$sVarchar}(1)",false , 'N');

            alterColumn('{{surveys}}','faxto',"{$sVarchar}(20)");
            alterColumn('{{surveys}}','format',"{$sVarchar}(1)");
            alterColumn('{{surveys}}','language',"{$sVarchar}(50)");
            alterColumn('{{surveys}}','additional_languages',"string");
            alterColumn('{{surveys}}','printanswers',"{$sVarchar}(1)",true , 'N');
            alterColumn('{{surveys}}','publicstatistics',"{$sVarchar}(1)",true , 'N');
            alterColumn('{{surveys}}','publicgraphs',"{$sVarchar}(1)",true , 'N');
            alterColumn('{{surveys}}','assessments',"{$sVarchar}(1)",true , 'N');
            alterColumn('{{surveys}}','usetokens',"{$sVarchar}(1)",true , 'N');
            alterColumn('{{surveys}}','bounce_email',"{$sVarchar}(320)");
            alterColumn('{{surveys}}','tokenlength','integer',true , 15);

            Yii::app()->db->createCommand()->update('{{surveys_languagesettings}}',array('surveyls_title'=>''),"surveyls_title is NULL");
            alterColumn('{{surveys_languagesettings}}','surveyls_title',"{$sVarchar}(200)",false);
            alterColumn('{{surveys_languagesettings}}','surveyls_endtext',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_url',"string");
            alterColumn('{{surveys_languagesettings}}','surveyls_urldescription',"string");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_invite_subj',"string");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_remind_subj',"string");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_register_subj',"string");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_confirm_subj',"string");
            alterColumn('{{surveys_languagesettings}}','surveyls_dateformat','integer',false , 1);

            Yii::app()->db->createCommand()->update('{{users}}',array('users_name'=>''),"users_name is NULL");
            Yii::app()->db->createCommand()->update('{{users}}',array('full_name'=>''),"full_name is NULL");
            alterColumn('{{users}}','users_name',"{$sVarchar}(64)",false , '');
            alterColumn('{{users}}','full_name',"{$sVarchar}(50)",false);
            alterColumn('{{users}}','lang',"{$sVarchar}(20)");
            alterColumn('{{users}}','email',"{$sVarchar}(320)");
            alterColumn('{{users}}','superadmin','integer',false , 0);
            alterColumn('{{users}}','htmleditormode',"{$sVarchar}(7)",true,'default');
            alterColumn('{{users}}','dateformat','integer',false , 1);
            try{
                Yii::app()->db->createCommand()->dropIndex('email','{{users}}');
            }
            catch(Exception $e)
            {
                // do nothing
            }

            Yii::app()->db->createCommand()->update('{{user_groups}}',array('name'=>''),"name is NULL");
            Yii::app()->db->createCommand()->update('{{user_groups}}',array('description'=>''),"description is NULL");
            alterColumn('{{user_groups}}','name',"{$sVarchar}(20)",false);
            alterColumn('{{user_groups}}','description',"text",false);

            try { Yii::app()->db->createCommand()->dropIndex('user_in_groups_idx1','{{user_in_groups}}'); } catch(Exception $e) {}        
            try { addPrimaryKey('user_in_groups', array('ugid','uid')); } catch(Exception $e) {}        

            addColumn('{{surveys_languagesettings}}','surveyls_numberformat',"integer NOT NULL DEFAULT 0");

            createTable('{{failed_login_attempts}}',array(
            'id' => "pk",
            'ip' => $sVarchar.'(37) NOT NULL',
            'last_attempt' => $sVarchar.'(20) NOT NULL',
            'number_attempts' => "integer NOT NULL"
            ));
            upgradeTokens145();
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>145),"stg_name='DBVersion'");
        }

        if ($oldversion < 146) //Modify surveys table
        {
            upgradeSurveyTimings146();
            // Fix permissions for new feature quick-translation
            try { setTransactionBookmark(); Yii::app()->db->createCommand("INSERT into {{survey_permissions}} (sid,uid,permission,read_p,update_p) SELECT sid,owner_id,'translations','1','1' from {{surveys}}")->execute(); echo $modifyoutput; flush();@ob_flush();} catch(Exception $e) { rollBackToTransactionBookmark();}        
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>146),"stg_name='DBVersion'");
        }

        if ($oldversion < 147)
        {
            addColumn('{{users}}','templateeditormode',"{$sVarchar}(7) NOT NULL default 'default'");
            addColumn('{{users}}','questionselectormode',"{$sVarchar}(7) NOT NULL default 'default'");
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>147),"stg_name='DBVersion'");
        }

        if ($oldversion < 148)
        {
            addColumn('{{users}}','participant_panel',"integer NOT NULL default 0");

            createTable('{{participants}}',array(
            'participant_id' => $sVarchar.'(50) NOT NULL',
            'firstname' => $sVarchar.'(40) default NULL',
            'lastname' => $sVarchar.'(40) default NULL',
            'email' => $sVarchar.'(80) default NULL',
            'language' => $sVarchar.'(40) default NULL',
            'blacklisted' => $sVarchar.'(1) NOT NULL',
            'owner_uid' => "integer NOT NULL"
            ));
            addPrimaryKey('participants', array('participant_id'));

            createTable('{{participant_attribute}}',array(
            'participant_id' => $sVarchar.'(50) NOT NULL',
            'attribute_id' => "integer NOT NULL",
            'value' => $sVarchar.'(50) NOT NULL'
            ));
            addPrimaryKey('participant_attribute', array('participant_id','attribute_id'));

            createTable('{{participant_attribute_names}}',array(
            'attribute_id' => $sAutoIncrement,
            'attribute_type' => $sVarchar.'(4) NOT NULL',
            'visible' => $sVarchar.'(5) NOT NULL',
            'PRIMARY KEY (attribute_id,attribute_type)'
            ));

            createTable('{{participant_attribute_names_lang}}',array(
            'attribute_id' => 'integer NOT NULL',
            'attribute_name' => $sVarchar.'(30) NOT NULL',
            'lang' => $sVarchar.'(20) NOT NULL'
            ));
            addPrimaryKey('participant_attribute_names_lang', array('attribute_id','lang'));

            createTable('{{participant_attribute_values}}',array(
            'attribute_id' => 'integer NOT NULL',
            'value_id' => 'pk',
            'value' => $sVarchar.'(20) NOT NULL'
            ));

            createTable('{{participant_shares}}',array(
            'participant_id' => $sVarchar.'(50) NOT NULL',
            'share_uid' => 'integer NOT NULL',
            'date_added' => 'datetime NOT NULL',
            'can_edit' => $sVarchar.'(5) NOT NULL'
            ));
            addPrimaryKey('participant_shares', array('participant_id','share_uid'));

            createTable('{{survey_links}}',array(
            'participant_id' => $sVarchar.'(50) NOT NULL',
            'token_id' => 'integer NOT NULL',
            'survey_id' => 'integer NOT NULL',
            'date_created' => 'datetime NOT NULL'
            ));
            addPrimaryKey('survey_links', array('participant_id','token_id','survey_id'));

            // Add language field to question_attributes table
            addColumn('{{question_attributes}}','language',"{$sVarchar}(20)");

            upgradeQuestionAttributes148();
            upgradeTokens148();
            fixSubquestions();
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>148),"stg_name='DBVersion'");
        }

        if ($oldversion < 149)
        {
            $fields = array(
            'id' => 'integer',
            'sid' => 'integer',
            'parameter' => $sVarchar.'(50)',
            'targetqid' => 'integer',
            'targetsqid' => 'integer'
            );
            createTable('{{survey_url_parameters}}',$fields);
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>149),"stg_name='DBVersion'");
        }

        if ($oldversion < 150)
        {
            addColumn('{{questions}}','relevance','TEXT');
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>150),"stg_name='DBVersion'");
        }

        if ($oldversion < 151)
        {
            addColumn('{{groups}}','randomization_group',"{$sVarchar}(20) NOT NULL default ''");
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>151),"stg_name='DBVersion'");
        }

        if ($oldversion < 152)
        {
            Yii::app()->db->createCommand()->createIndex('question_attributes_idx3','{{question_attributes}}','attribute');
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>152),"stg_name='DBVersion'");
        }

        if ($oldversion < 153)
        {
            createTable('{{expression_errors}}',array(
            'id' => 'pk',
            'errortime' => $sVarchar.'(50)',
            'sid' => 'integer',
            'gid' => 'integer',
            'qid' => 'integer',
            'gseq' => 'integer',
            'qseq' => 'integer',
            'type' => $sVarchar.'(50)',
            'eqn' => 'text',
            'prettyprint' => 'text'
            ));
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>153),"stg_name='DBVersion'");
        }

        if ($oldversion < 154)
        {
            Yii::app()->db->createCommand()->addColumn('{{groups}}','grelevance',"text");
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>154),"stg_name='DBVersion'");
        }

        if ($oldversion < 155)
        {
            addColumn('{{surveys}}','googleanalyticsstyle',"{$sVarchar}(1)");
            addColumn('{{surveys}}','googleanalyticsapikey',"{$sVarchar}(25)");
            try { setTransactionBookmark(); Yii::app()->db->createCommand()->renameColumn('{{surveys}}','showXquestions','showxquestions');} catch(Exception $e) { rollBackToTransactionBookmark();}        
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>155),"stg_name='DBVersion'");
        }


        if ($oldversion < 156)
        {
            try
            {
                Yii::app()->db->createCommand()->dropTable('{{survey_url_parameters}}');
            }
            catch(Exception $e)
            {
                // do nothing
            }
            createTable('{{survey_url_parameters}}',array(
            'id' => 'pk',
            'sid' => 'integer NOT NULL',
            'parameter' => $sVarchar.'(50) NOT NULL',
            'targetqid' => 'integer',
            'targetsqid' => 'integer'
            ));

            Yii::app()->db->createCommand()->dropTable('{{sessions}}');
            if ($sDBDriverName=='mysql')
            {
                createTable('{{sessions}}',array(
                'id' => $sVarchar.'(32) NOT NULL',
                'expire' => 'integer',
                'data' => 'longtext'
                ));
            }
            else
            {
                createTable('{{sessions}}',array(
                'id' => $sVarchar.'(32) NOT NULL',
                'expire' => 'integer',
                'data' => 'text'
                ));
            }

            addPrimaryKey('sessions', array('id'));
            addColumn('{{surveys_languagesettings}}','surveyls_attributecaptions',"TEXT");
            addColumn('{{surveys}}','sendconfirmation',"{$sVarchar}(1) default 'Y'");

            upgradeSurveys156();

            // If a survey has an deleted owner, re-own the survey to the superadmin
            Yii::app()->db->schema->refresh();
            Survey::model()->refreshMetaData();
            $surveys = Survey::model();
            $surveys = $surveys->with(array('owner'))->findAll();
            foreach ($surveys as $row)
            {
                if (!isset($row->owner->attributes))
                {
                    Survey::model()->updateByPk($row->sid,array('owner_id'=>1));
                }
            }
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>156),"stg_name='DBVersion'");
            $oTransaction->commit();
            $oTransaction=Yii::app()->db->beginTransaction();
        }

        if ($oldversion < 157)
        {
            // MySQL DB corrections
            try { setTransactionBookmark(); Yii::app()->db->createCommand()->dropIndex('questions_idx4','{{questions}}'); } catch(Exception $e) { rollBackToTransactionBookmark();}        

            alterColumn('{{answers}}','assessment_value','integer',false , '0');
            dropPrimaryKey('answers');
            alterColumn('{{answers}}','scale_id','integer',false , '0');
            addPrimaryKey('answers', array('qid','code','language','scale_id'));
            alterColumn('{{conditions}}','method',"{$sVarchar}(5)",false , '');
            alterColumn('{{participants}}','owner_uid','integer',false);
            alterColumn('{{participant_attribute_names}}','visible',$sVarchar.'(5)',false);
            alterColumn('{{questions}}','type',"{$sVarchar}(1)",false , 'T');
            alterColumn('{{questions}}','other',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{questions}}','mandatory',"{$sVarchar}(1)");
            alterColumn('{{questions}}','scale_id','integer',false , '0');
            alterColumn('{{questions}}','parent_qid','integer',false ,'0');

            alterColumn('{{questions}}','same_default','integer',false , '0');
            alterColumn('{{quota}}','qlimit','integer');
            alterColumn('{{quota}}','action','integer');
            alterColumn('{{quota}}','active','integer',false , '1');
            alterColumn('{{quota}}','autoload_url','integer',false , '0');
            alterColumn('{{saved_control}}','status',"{$sVarchar}(1)",false , '');
            try { setTransactionBookmark(); alterColumn('{{sessions}}','id',"{$sVarchar}(32)",false); } catch(Exception $e) { rollBackToTransactionBookmark();}        
            alterColumn('{{surveys}}','active',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','anonymized',"{$sVarchar}(1)",false,'N');
            alterColumn('{{surveys}}','format',"{$sVarchar}(1)");
            alterColumn('{{surveys}}','savetimings',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','datestamp',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','usecookie',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','allowregister',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','allowsave',"{$sVarchar}(1)",false , 'Y');
            alterColumn('{{surveys}}','autonumber_start','integer' ,false, '0');
            alterColumn('{{surveys}}','autoredirect',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','allowprev',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','printanswers',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','ipaddr',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','refurl',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','publicstatistics',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','publicgraphs',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','listpublic',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','htmlemail',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','sendconfirmation',"{$sVarchar}(1)",false , 'Y');
            alterColumn('{{surveys}}','tokenanswerspersistence',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','assessments',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','usecaptcha',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','usetokens',"{$sVarchar}(1)",false , 'N');
            alterColumn('{{surveys}}','tokenlength','integer',false, '15');
            alterColumn('{{surveys}}','showxquestions',"{$sVarchar}(1)", true , 'Y');
            alterColumn('{{surveys}}','showgroupinfo',"{$sVarchar}(1) ", true , 'B');
            alterColumn('{{surveys}}','shownoanswer',"{$sVarchar}(1) ", true , 'Y');
            alterColumn('{{surveys}}','showqnumcode',"{$sVarchar}(1) ", true , 'X');
            alterColumn('{{surveys}}','bouncetime','integer');
            alterColumn('{{surveys}}','showwelcome',"{$sVarchar}(1)", true , 'Y');
            alterColumn('{{surveys}}','showprogress',"{$sVarchar}(1)", true , 'Y');
            alterColumn('{{surveys}}','allowjumps',"{$sVarchar}(1)", true , 'N');
            alterColumn('{{surveys}}','navigationdelay','integer', false , '0');
            alterColumn('{{surveys}}','nokeyboard',"{$sVarchar}(1)", true , 'N');
            alterColumn('{{surveys}}','alloweditaftercompletion',"{$sVarchar}(1)", true , 'N');
            alterColumn('{{surveys}}','googleanalyticsstyle',"{$sVarchar}(1)");

            alterColumn('{{surveys_languagesettings}}','surveyls_dateformat','integer',false , 1);
            try { setTransactionBookmark(); alterColumn('{{survey_permissions}}','sid',"integer",false); } catch(Exception $e) { rollBackToTransactionBookmark();}        
            try { setTransactionBookmark(); alterColumn('{{survey_permissions}}','uid',"integer",false); } catch(Exception $e) { rollBackToTransactionBookmark();}        
            alterColumn('{{survey_permissions}}','create_p', 'integer',false , '0');
            alterColumn('{{survey_permissions}}','read_p', 'integer',false , '0');
            alterColumn('{{survey_permissions}}','update_p','integer',false , '0');
            alterColumn('{{survey_permissions}}','delete_p' ,'integer',false , '0');
            alterColumn('{{survey_permissions}}','import_p','integer',false , '0');
            alterColumn('{{survey_permissions}}','export_p' ,'integer',false , '0');

            alterColumn('{{survey_url_parameters}}','targetqid' ,'integer');
            alterColumn('{{survey_url_parameters}}','targetsqid' ,'integer');

            alterColumn('{{templates_rights}}','use','integer',false );

            alterColumn('{{users}}','create_survey','integer',false, '0');
            alterColumn('{{users}}','create_user','integer',false, '0');
            alterColumn('{{users}}','participant_panel','integer',false, '0');
            alterColumn('{{users}}','delete_user','integer',false, '0');
            alterColumn('{{users}}','superadmin','integer',false, '0');
            alterColumn('{{users}}','configurator','integer',false, '0');
            alterColumn('{{users}}','manage_template','integer',false, '0');
            alterColumn('{{users}}','manage_label','integer',false, '0');
            alterColumn('{{users}}','dateformat','integer',false, 1);
            alterColumn('{{users}}','participant_panel','integer',false , '0');
            alterColumn('{{users}}','parent_id','integer',false);
            try { setTransactionBookmark(); alterColumn('{{surveys_languagesettings}}','surveyls_survey_id',"integer",false); } catch(Exception $e) { rollBackToTransactionBookmark(); }        
            alterColumn('{{user_groups}}','owner_id',"integer",false);
            alterColumn('{{user_in_groups}}','ugid',"integer",false);
            alterColumn('{{user_in_groups}}','uid',"integer",false);

            // Additional corrections for Postgres
            try{ setTransactionBookmark(); Yii::app()->db->createCommand()->createIndex('questions_idx3','{{questions}}','gid');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); Yii::app()->db->createCommand()->createIndex('conditions_idx3','{{conditions}}','cqid');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); Yii::app()->db->createCommand()->createIndex('questions_idx4','{{questions}}','type');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); Yii::app()->db->createCommand()->dropIndex('user_in_groups_idx1','{{user_in_groups}}');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); Yii::app()->db->createCommand()->dropIndex('{{user_name_key}}','{{users}}');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); Yii::app()->db->createCommand()->createIndex('users_name','{{users}}','users_name',true);} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); addPrimaryKey('user_in_groups', array('ugid','uid'));} catch(Exception $e) { rollBackToTransactionBookmark(); };

            alterColumn('{{participant_attribute}}','value',"{$sVarchar}(50)", false);
            try{ setTransactionBookmark(); alterColumn('{{participant_attribute_names}}','attribute_type',"{$sVarchar}(4)", false);} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); dropColumn('{{participant_attribute_names_lang}}','id');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); addPrimaryKey('participant_attribute_names_lang',array('attribute_id','lang'));} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); Yii::app()->db->createCommand()->renameColumn('{{participant_shares}}','shared_uid','share_uid');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            alterColumn('{{participant_shares}}','date_added',"datetime", false);
            alterColumn('{{participants}}','firstname',"{$sVarchar}(40)");
            alterColumn('{{participants}}','lastname',"{$sVarchar}(40)");
            alterColumn('{{participants}}','email',"{$sVarchar}(80)");
            alterColumn('{{participants}}','language',"{$sVarchar}(40)");
            alterColumn('{{quota_languagesettings}}','quotals_name',"string");
            try{ setTransactionBookmark(); alterColumn('{{survey_permissions}}','sid','integer',false); } catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); alterColumn('{{survey_permissions}}','uid','integer',false); } catch(Exception $e) { rollBackToTransactionBookmark(); };
            alterColumn('{{users}}','htmleditormode',"{$sVarchar}(7)",true,'default');
            
            // Sometimes the survey_links table was deleted before this step, if so
            // we recreate it (copied from line 663)
            if (!tableExists('{survey_links}')) {
                createTable('{{survey_links}}',array(
                    'participant_id' => $sVarchar.'(50) NOT NULL',
                    'token_id' => 'integer NOT NULL',
                    'survey_id' => 'integer NOT NULL',
                    'date_created' => 'datetime NOT NULL'
                    ));
                addPrimaryKey('survey_links', array('participant_id','token_id','survey_id'));
            }
            alterColumn('{{survey_links}}','date_created',"datetime",true);
            alterColumn('{{saved_control}}','identifier',"text",false);
            alterColumn('{{saved_control}}','email',"{$sVarchar}(320)");
            alterColumn('{{surveys}}','adminemail',"{$sVarchar}(320)");
            alterColumn('{{surveys}}','bounce_email',"{$sVarchar}(320)");
            alterColumn('{{users}}','email',"{$sVarchar}(320)");

            try{ setTransactionBookmark(); Yii::app()->db->createCommand()->dropIndex('assessments_idx','{{assessments}}');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); Yii::app()->db->createCommand()->createIndex('assessments_idx3','{{assessments}}','gid');} catch(Exception $e) { rollBackToTransactionBookmark(); };

            try{ setTransactionBookmark(); Yii::app()->db->createCommand()->dropIndex('ixcode','{{labels}}');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); Yii::app()->db->createCommand()->dropIndex('{{labels_ixcode_idx}}','{{labels}}');} catch(Exception $e) { rollBackToTransactionBookmark(); };
            try{ setTransactionBookmark(); Yii::app()->db->createCommand()->createIndex('labels_code_idx','{{labels}}','code');} catch(Exception $e) { rollBackToTransactionBookmark(); };



            if ($sDBDriverName=='pgsql')
            {
                try{ setTransactionBookmark(); Yii::app()->db->createCommand("ALTER TABLE ONLY {{user_groups}} ADD PRIMARY KEY (ugid); ")->execute;} catch(Exception $e) { rollBackToTransactionBookmark(); };
                try{ setTransactionBookmark(); Yii::app()->db->createCommand("ALTER TABLE ONLY {{users}} ADD PRIMARY KEY (uid); ")->execute;} catch(Exception $e) { rollBackToTransactionBookmark(); };
            }

            // Additional corrections for MSSQL
            alterColumn('{{answers}}','answer',"text",false);
            alterColumn('{{assessments}}','name',"text",false);
            alterColumn('{{assessments}}','message',"text",false);
            alterColumn('{{defaultvalues}}','defaultvalue',"text");
            alterColumn('{{expression_errors}}','eqn',"text");
            alterColumn('{{expression_errors}}','prettyprint',"text");
            alterColumn('{{groups}}','description',"text");
            alterColumn('{{groups}}','grelevance',"text");
            alterColumn('{{labels}}','title',"text");
            alterColumn('{{question_attributes}}','value',"text");
            alterColumn('{{questions}}','preg',"text");
            alterColumn('{{questions}}','help',"text");
            alterColumn('{{questions}}','relevance',"text");
            alterColumn('{{questions}}','question',"text",false);
            alterColumn('{{quota_languagesettings}}','quotals_quota_id',"integer",false);
            alterColumn('{{quota_languagesettings}}','quotals_message',"text",false);
            alterColumn('{{saved_control}}','refurl',"text");
            alterColumn('{{saved_control}}','access_code',"text",false);
            alterColumn('{{saved_control}}','ip',"text",false);
            alterColumn('{{saved_control}}','saved_thisstep',"text",false);
            alterColumn('{{saved_control}}','saved_date',"datetime",false);
            alterColumn('{{surveys}}','attributedescriptions',"text");
            alterColumn('{{surveys}}','emailresponseto',"text");
            alterColumn('{{surveys}}','emailnotificationto',"text");

            alterColumn('{{surveys_languagesettings}}','surveyls_description',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_welcometext',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_invite',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_remind',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_register',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_email_confirm',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_attributecaptions',"text");
            alterColumn('{{surveys_languagesettings}}','email_admin_notification',"text");
            alterColumn('{{surveys_languagesettings}}','email_admin_responses',"text");
            alterColumn('{{surveys_languagesettings}}','surveyls_endtext',"text");
            alterColumn('{{user_groups}}','description',"text",false);



            alterColumn('{{conditions}}','value','string',false,'');
            alterColumn('{{participant_shares}}','can_edit',"{$sVarchar}(5)",false);

            alterColumn('{{users}}','password',"binary",false);
            dropColumn('{{users}}','one_time_pw');
            addColumn('{{users}}','one_time_pw','binary');


            Yii::app()->db->createCommand()->update('{{question_attributes}}',array('value'=>'1'),"attribute = 'random_order' and value = '2'");

            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>157),"stg_name='DBVersion'");
        }

        if ($oldversion < 158)
        {
            LimeExpressionManager::UpgradeConditionsToRelevance();
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>158),"stg_name='DBVersion'");
        }
        if ($oldversion < 159)
        {
            alterColumn('{{failed_login_attempts}}', 'ip', "{$sVarchar}(40)",false);
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>159),"stg_name='DBVersion'");
        }

        if ($oldversion < 160)
        {
            alterLanguageCode('it','it-informal');
            alterLanguageCode('it-formal','it');
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>160),"stg_name='DBVersion'");
        }
        if ($oldversion < 161)
        {
            addColumn('{{survey_links}}','date_invited','datetime NULL default NULL');
            addColumn('{{survey_links}}','date_completed','datetime NULL default NULL');
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>161),"stg_name='DBVersion'");
        }
        if ($oldversion < 162)
        {
            /* Fix participant db types */
            alterColumn('{{participant_attribute}}', 'value', "text", false);
            alterColumn('{{participant_attribute_names_lang}}', 'attribute_name', "{$sVarchar}(255)", false);
            alterColumn('{{participant_attribute_values}}', 'value', "text", false);
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>162),"stg_name='DBVersion'");
        }
        if ($oldversion < 163)
        {
            //Replace  by <script type="text/javascript" src="{TEMPLATEURL}template.js"></script> by {TEMPLATEJS}

            $replacedTemplate=replaceTemplateJS();
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>163),"stg_name='DBVersion'");

        }
        
        if ($oldversion < 164)
        {
            // fix survey tables for missing or incorrect token field
            upgradeSurveyTables164();
            Yii::app()->db->createCommand()->update('{{settings_global}}',array('stg_value'=>164),"stg_name='DBVersion'");
            
            // Not updating settings table as upgrade process takes care of that step now
        }
        $oTransaction->commit();
    }
    catch(Exception $e)
    {
       $oTransaction->rollback();
       echo '<br /><br />'.$clang->gT('An non-recoverable error happened during the update. Error details:')."<p>".htmlspecialchars($e->getMessage()).'</p><br />';
       return false;
    }        
    fixLanguageConsistencyAllSurveys();
    echo '<br /><br />'.sprintf($clang->gT('Database update finished (%s)'),date('Y-m-d H:i:s')).'<br /><br />';
    return true;
}

function upgradeSurveys156()
{
    global $modifyoutput;
    $sSurveyQuery = "SELECT * FROM {{surveys_languagesettings}}";
    $oSurveyResult = Yii::app()->db->createCommand($sSurveyQuery)->queryAll();
    foreach ( $oSurveyResult as $aSurveyRow )
    {

        Yii::app()->loadLibrary('Limesurvey_lang',array("langcode"=>$aSurveyRow['surveyls_language']));
        $oLanguage = Yii::app()->lang;
        $aDefaultTexts=templateDefaultTexts($oLanguage,'unescaped');
        unset($oLanguage);

        if (trim(strip_tags($aSurveyRow['surveyls_email_confirm'])) == '')
        {
            $sSurveyUpdateQuery= "update {{surveys}} set sendconfirmation='N' where sid=".$aSurveyRow['surveyls_survey_id'];
            Yii::app()->db->createCommand($sSurveyUpdateQuery)->execute();

            $aValues=array('surveyls_email_confirm_subj'=>$aDefaultTexts['confirmation_subject'],
            'surveyls_email_confirm'=>$aDefaultTexts['confirmation']);
            Surveys_languagesettings::model()->updateAll($aValues,'surveyls_survey_id=:sid',array(':sid'=>$aSurveyRow['surveyls_survey_id']));
        }
    }
}

// Add the usesleft field to all existing token tables
function upgradeTokens148()
{
    $aTables = dbGetTablesLike("tokens%");
    $sVarchar=Yii::app()->getConfig('varchar');
    foreach ( $aTables as $sTable )
    {
        addColumn($sTable, 'participant_id', "{$sVarchar}(50)");
        addColumn($sTable, 'blacklisted', "{$sVarchar}(17)");
    }
}



function upgradeQuestionAttributes148()
{
    global $modifyoutput;
    $sSurveyQuery = "SELECT sid FROM {{surveys}}";
    $oSurveyResult = dbExecuteAssoc($sSurveyQuery);
    foreach ( $oSurveyResult->readAll()  as $aSurveyRow)
    {
        $surveyid=$aSurveyRow['sid'];
        $languages=array_merge(array(Survey::model()->findByPk($surveyid)->language), Survey::model()->findByPk($surveyid)->additionalLanguages);

        $sAttributeQuery = "select q.qid,attribute,value from {{question_attributes}} qa , {{questions}} q where q.qid=qa.qid and sid={$surveyid}";
        $oAttributeResult = dbExecuteAssoc($sAttributeQuery);
        $aAllAttributes=questionAttributes(true);
        foreach ( $oAttributeResult->readAll() as $aAttributeRow)
        {
            if (isset($aAllAttributes[$aAttributeRow['attribute']]['i18n']) && $aAllAttributes[$aAttributeRow['attribute']]['i18n'])
            {
                Yii::app()->db->createCommand("delete from {{question_attributes}} where qid={$aAttributeRow['qid']} and attribute='{$aAttributeRow['attribute']}'")->execute();
                foreach ($languages as $language)
                {
                    $sAttributeInsertQuery="insert into {{question_attributes}} (qid,attribute,value,language) VALUES({$aAttributeRow['qid']},'{$aAttributeRow['attribute']}','{$aAttributeRow['value']}','{$language}' )";
                    modifyDatabase("",$sAttributeInsertQuery); echo $modifyoutput; flush();@ob_flush();
                }
            }
        }
    }
}


function upgradeSurveyTimings146()
{
    global $modifyoutput;
    $aTables = dbGetTablesLike("%timings");
    foreach ($aTables as $sTable) {
        Yii::app()->db->createCommand()->renameColumn($sTable,'interviewTime','interviewtime');
    }
}


// Add the usesleft field to all existing token tables
function upgradeTokens145()
{
    global $modifyoutput;
    $aTables = dbGetTablesLike("tokens%");
    foreach ( $aTables as $sTable )
    {
        addColumn($sTable,'usesleft',"integer NOT NULL default 1");
        Yii::app()->db->createCommand()->update($sTable,array('usesleft'=>'0'),"completed<>'N'");
    }
}


function upgradeSurveys145()
{
    global $modifyoutputt;
    $sSurveyQuery = "SELECT * FROM {{surveys}} where notification<>'0'";
    $oSurveyResult = dbExecuteAssoc($sSurveyQuery);
    foreach ( $oSurveyResult->readAll() as $aSurveyRow )
    {
        if ($aSurveyRow['notification']=='1' && trim($aSurveyRow['adminemail'])!='')
        {
            $aEmailAddresses=explode(';',$aSurveyRow['adminemail']);
            $sAdminEmailAddress=$aEmailAddresses[0];
            $sEmailnNotificationAddresses=implode(';',$aEmailAddresses);
            $sSurveyUpdateQuery= "update {{surveys}} set adminemail='{$sAdminEmailAddress}', emailnotificationto='{$sEmailnNotificationAddresses}' where sid=".$aSurveyRow['sid'];
            Yii::app()->db->createCommand($sSurveyUpdateQuery)->execute();
        }
        else
        {
            $aEmailAddresses=explode(';',$aSurveyRow['adminemail']);
            $sAdminEmailAddress=$aEmailAddresses[0];
            $sEmailDetailedNotificationAddresses=implode(';',$aEmailAddresses);
            if (trim($aSurveyRow['emailresponseto'])!='')
            {
                $sEmailDetailedNotificationAddresses=$sEmailDetailedNotificationAddresses.';'.trim($aSurveyRow['emailresponseto']);
            }
            $sSurveyUpdateQuery= "update {{surveys}} set adminemail='{$sAdminEmailAddress}', emailnotificationto='{$sEmailDetailedNotificationAddresses}' where sid=".$aSurveyRow['sid'];
            Yii::app()->db->createCommand($sSurveyUpdateQuery)->execute();
        }
    }
    $sSurveyQuery = "SELECT * FROM {{surveys_languagesettings}}";
    $oSurveyResult = Yii::app()->db->createCommand($sSurveyQuery)->queryAll();
    foreach ( $oSurveyResult as $aSurveyRow )
    {
        $oLanguage = new Limesurvey_lang($aSurveyRow['surveyls_language']);
        $oLanguage = Yii::app()->lang;
        $aDefaultTexts=templateDefaultTexts($oLanguage,'unescaped');
        unset($oLanguage);
        $aDefaultTexts['admin_detailed_notification']=$aDefaultTexts['admin_detailed_notification'].$aDefaultTexts['admin_detailed_notification_css'];
        $sSurveyUpdateQuery = "update {{surveys_languagesettings}} set
        email_admin_responses_subj=".$aDefaultTexts['admin_detailed_notification_subject'].",
        email_admin_responses=".$aDefaultTexts['admin_detailed_notification'].",
        email_admin_notification_subj=".$aDefaultTexts['admin_notification_subject'].",
        email_admin_notification=".$aDefaultTexts['admin_notification']."
        where surveyls_survey_id=".$aSurveyRow['surveyls_survey_id'];
        Yii::app()->db->createCommand()->update('{{surveys_languagesettings}}',array('email_admin_responses_subj'=>$aDefaultTexts['admin_detailed_notification_subject'],
        'email_admin_responses'=>$aDefaultTexts['admin_detailed_notification'],
        'email_admin_notification_subj'=>$aDefaultTexts['admin_notification_subject'],
        'email_admin_notification'=>$aDefaultTexts['admin_notification']
        ),"surveyls_survey_id={$aSurveyRow['surveyls_survey_id']}");
    }

}


function upgradeSurveyPermissions145()
{
    global $modifyoutput;
    $sPermissionQuery = "SELECT * FROM {{surveys_rights}}";
    $oPermissionResult = Yii::app()->db->createCommand($sPermissionQuery)->queryAll();
    if (empty($oPermissionResult)) {return "Database Error";}
    else
    {
        $tablename = '{{survey_permissions}}';
        foreach ( $oPermissionResult as $aPermissionRow )
        {

            $sPermissionInsertQuery=Yii::app()->db->createCommand()->insert($tablename, array('permission'=>'assessments',
            'create_p'=>$aPermissionRow['define_questions'],
            'read_p'=>$aPermissionRow['define_questions'],
            'update_p'=>$aPermissionRow['define_questions'],
            'delete_p'=>$aPermissionRow['define_questions'],
            'sid'=>$aPermissionRow['sid'],
            'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->db->createCommand()->insert($tablename,array('permission'=>'quotas',
            'create_p'=>$aPermissionRow['define_questions'],
            'read_p'=>$aPermissionRow['define_questions'],
            'update_p'=>$aPermissionRow['define_questions'],
            'delete_p'=>$aPermissionRow['define_questions'],
            'sid'=>$aPermissionRow['sid'],
            'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->db->createCommand()->insert($tablename,array('permission'=>'responses',
            'create_p'=>$aPermissionRow['browse_response'],
            'read_p'=>$aPermissionRow['browse_response'],
            'update_p'=>$aPermissionRow['browse_response'],
            'delete_p'=>$aPermissionRow['delete_survey'],
            'export_p'=>$aPermissionRow['export'],
            'import_p'=>$aPermissionRow['browse_response'],
            'sid'=>$aPermissionRow['sid'],
            'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->db->createCommand()->insert($tablename,array('permission'=>'statistics',
            'read_p'=>$aPermissionRow['browse_response'],
            'sid'=>$aPermissionRow['sid'],
            'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->db->createCommand()->insert($tablename,array('permission'=>'survey',
            'read_p'=>1,
            'delete_p'=>$aPermissionRow['delete_survey'],
            'sid'=>$aPermissionRow['sid'],
            'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->db->createCommand()->insert($tablename,array('permission'=>'surveyactivation',
            'update_p'=>$aPermissionRow['activate_survey'],
            'sid'=>$aPermissionRow['sid'],
            'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->db->createCommand()->insert($tablename,array('permission'=>'surveycontent',
            'create_p'=>$aPermissionRow['define_questions'],
            'read_p'=>$aPermissionRow['define_questions'],
            'update_p'=>$aPermissionRow['define_questions'],
            'delete_p'=>$aPermissionRow['define_questions'],
            'export_p'=>$aPermissionRow['export'],
            'import_p'=>$aPermissionRow['define_questions'],
            'sid'=>$aPermissionRow['sid'],
            'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->db->createCommand()->insert($tablename,array('permission'=>'surveylocale',
            'read_p'=>$aPermissionRow['edit_survey_property'],
            'update_p'=>$aPermissionRow['edit_survey_property'],
            'sid'=>$aPermissionRow['sid'],
            'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->db->createCommand()->insert($tablename,array('permission'=>'surveysettings',
            'read_p'=>$aPermissionRow['edit_survey_property'],
            'update_p'=>$aPermissionRow['edit_survey_property'],
            'sid'=>$aPermissionRow['sid'],
            'uid'=>$aPermissionRow['uid']));

            $sPermissionInsertQuery=Yii::app()->db->createCommand()->insert($tablename,array('permission'=>'tokens',
            'create_p'=>$aPermissionRow['activate_survey'],
            'read_p'=>$aPermissionRow['activate_survey'],
            'update_p'=>$aPermissionRow['activate_survey'],
            'delete_p'=>$aPermissionRow['activate_survey'],
            'export_p'=>$aPermissionRow['export'],
            'import_p'=>$aPermissionRow['activate_survey'],
            'sid'=>$aPermissionRow['sid'],
            'uid'=>$aPermissionRow['uid']));
        }
    }
}

function upgradeTables143()
{
    global $modifyoutput;

    $aQIDReplacements=array();
    $answerquery = "select a.*, q.sid, q.gid from {{answers}} a,{{questions}} q where a.qid=q.qid and q.type in ('L','O','!') and a.default_value='Y'";
    $answerresult = Yii::app()->db->createCommand($answerquery)->queryAll();
    foreach ( $answerresult as $row )
    {
        modifyDatabase("","INSERT INTO {{defaultvalues}} (qid, scale_id,language,specialtype,defaultvalue) VALUES ({$row['qid']},0,".dbQuoteAll($row['language']).",'',".dbQuoteAll($row['code']).")"); echo $modifyoutput; flush();@ob_flush();
    }

    // Convert answers to subquestions

    $answerquery = "select a.*, q.sid, q.gid, q.type from {{answers}} a,{{questions}} q where a.qid=q.qid and a.language=q.language and q.type in ('1','A','B','C','E','F','H','K',';',':','M','P','Q')";
    $answerresult = Yii::app()->db->createCommand($answerquery)->queryAll();
    foreach ( $answerresult as $row )
    {

        $aInsert=array();
        if (isset($aQIDReplacements[$row['qid'].'_'.$row['code']]))
        {
            $aInsert['qid']=$aQIDReplacements[$row['qid'].'_'.$row['code']];
        }
        $aInsert['sid']=$row['sid'];
        $aInsert['gid']=$row['gid'];
        $aInsert['parent_qid']=$row['qid'];
        $aInsert['type']=$row['type'];
        $aInsert['title']=$row['code'];
        $aInsert['question']=$row['answer'];
        $aInsert['question_order']=$row['sortorder'];
        $aInsert['language']=$row['language'];

        $iLastInsertID=Questions::model()->insertRecords($aInsert);
        if (!isset($aInsert['qid']))
        {
            $aQIDReplacements[$row['qid'].'_'.$row['code']]=$iLastInsertID;
            $iSaveSQID=$aQIDReplacements[$row['qid'].'_'.$row['code']];
        }
        else
        {
            $iSaveSQID=$aInsert['qid'];
        }
        if (($row['type']=='M' || $row['type']=='P') && $row['default_value']=='Y')
        {
            modifyDatabase("","INSERT INTO {{defaultvalues}} (qid, sqid, scale_id,language,specialtype,defaultvalue) VALUES ({$row['qid']},{$iSaveSQID},0,".dbQuoteAll($row['language']).",'','Y')"); echo $modifyoutput; flush();@ob_flush();
        }
    }
    // Sanitize data
    $sDBDriverName=Yii::app()->db->getDriverName();
    if ($sDBDriverName=='pgsql')
    {
        modifyDatabase("","delete from {{answers}} USING {{questions}} WHERE {{answers}}.qid={{questions}}.qid AND {{questions}}.type in ('1','F','H','M','P','W','Z')"); echo $modifyoutput; flush();@ob_flush();
    }
    else
    {
        modifyDatabase("","delete {{answers}} from {{answers}} LEFT join {{questions}} ON {{answers}}.qid={{questions}}.qid where {{questions}}.type in ('1','F','H','M','P','W','Z')"); echo $modifyoutput; flush();@ob_flush();
    }

    // Convert labels to answers
    $answerquery = "select qid ,type ,lid ,lid1, language from {{questions}} where parent_qid=0 and type in ('1','F','H','M','P','W','Z')";
    $answerresult = Yii::app()->db->createCommand($answerquery)->queryAll();
    foreach ( $answerresult as $row )
    {
        $labelquery="Select * from {{labels}} where lid={$row['lid']} and language=".dbQuoteAll($row['language']);
        $labelresult = Yii::app()->db->createCommand($labelquery)->queryAll();
        foreach ( $labelresult as $lrow )
        {
            modifyDatabase("","INSERT INTO {{answers}} (qid, code, answer, sortorder, language, assessment_value) VALUES ({$row['qid']},".dbQuoteAll($lrow['code']).",".dbQuoteAll($lrow['title']).",{$lrow['sortorder']},".dbQuoteAll($lrow['language']).",{$lrow['assessment_value']})"); echo $modifyoutput; flush();@ob_flush();
            //$labelids[]
        }
        if ($row['type']=='1')
        {
            $labelquery="Select * from {{labels}} where lid={$row['lid1']} and language=".dbQuoteAll($row['language']);
            $labelresult = Yii::app()->db->createCommand($labelquery)->queryAll();
            foreach ( $labelresult as $lrow )
            {
                modifyDatabase("","INSERT INTO {{answers}} (qid, code, answer, sortorder, language, scale_id, assessment_value) VALUES ({$row['qid']},".dbQuoteAll($lrow['code']).",".dbQuoteAll($lrow['title']).",{$lrow['sortorder']},".dbQuoteAll($lrow['language']).",1,{$lrow['assessment_value']})"); echo $modifyoutput; flush();@ob_flush();
            }
        }
    }

    // Convert labels to subquestions
    $answerquery = "select * from {{questions}} where parent_qid=0 and type in (';',':')";
    $answerresult = Yii::app()->db->createCommand($answerquery)->queryAll();
    foreach ( $answerresult as $row )
    {
        $labelquery="Select * from {{labels}} where lid={$row['lid']} and language=".dbQuoteAll($row['language']);
        $labelresult = Yii::app()->db->createCommand($labelquery)->queryAll();
        foreach ( $labelresult as $lrow )
        {
            $aInsert=array();
            if (isset($aQIDReplacements[$row['qid'].'_'.$lrow['code'].'_1']))
            {
                $aInsert['qid']=$aQIDReplacements[$row['qid'].'_'.$lrow['code'].'_1'];
            }
            $aInsert['sid']=$row['sid'];
            $aInsert['gid']=$row['gid'];
            $aInsert['parent_qid']=$row['qid'];
            $aInsert['type']=$row['type'];
            $aInsert['title']=$lrow['code'];
            $aInsert['question']=$lrow['title'];
            $aInsert['question_order']=$lrow['sortorder'];
            $aInsert['language']=$lrow['language'];
            $aInsert['scale_id']=1;
            $iLastInsertID=Questions::model()->insertRecords($aInsert);

            if (isset($aInsert['qid']))
            {
                $aQIDReplacements[$row['qid'].'_'.$lrow['code'].'_1']=$iLastInsertID;
            }
        }
    }



    $updatequery = "update {{questions}} set type='!' where type='W'";
    modifyDatabase("",$updatequery); echo $modifyoutput; flush();@ob_flush();
    $updatequery = "update {{questions}} set type='L' where type='Z'";
    modifyDatabase("",$updatequery); echo $modifyoutput; flush();@ob_flush();

    // Now move all non-standard templates to the /upload dir
    global $usertemplaterootdir, $standardtemplates,$standardtemplaterootdir;

    if (!$usertemplaterootdir) {die("getTemplateList() no template directory");}
    if ($handle = opendir($standardtemplaterootdir))
    {
        while (false !== ($file = readdir($handle)))
        {
            if (!is_file("$standardtemplaterootdir/$file") && $file != "." && $file != ".." && $file!=".svn" && !isStandardTemplate($file))
            {
                if (!rename($standardtemplaterootdir.DIRECTORY_SEPARATOR.$file,$usertemplaterootdir.DIRECTORY_SEPARATOR.$file))
                {
                    echo "There was a problem moving directory '".$standardtemplaterootdir.DIRECTORY_SEPARATOR.$file."' to '".$usertemplaterootdir.DIRECTORY_SEPARATOR.$file."' due to missing permissions. Please do this manually.<br />";
                };
            }
        }
        closedir($handle);
    }

}


function upgradeQuestionAttributes142()
{
    global $modifyoutput;
    $attributequery="Select qid from {{question_attributes}} where attribute='exclude_all_other'  group by qid having count(qid)>1 ";
    $questionids = Yii::app()->db->createCommand($attributequery)->queryRow();
    if(!is_array($questionids)) { return "Database Error"; }
    else
    {
        foreach ($questionids as $questionid)
        {
            //Select all affected question attributes
            $attributevalues=Yii::app()->db->createCommand("SELECT value from {{question_attributes}} where attribute='exclude_all_other' and qid=".$questionid)->queryColumn();
            modifyDatabase("","delete from {{question_attributes}} where attribute='exclude_all_other' and qid=".$questionid); echo $modifyoutput; flush();@ob_flush();
            $record['value']=implode(';',$attributevalues);
            $record['attribute']='exclude_all_other';
            $record['qid']=$questionid;
            Yii::app()->db->createCommand()->insert('{{question_attributes}}', $record)->execute();
        }
    }
}

function upgradeSurveyTables139()
{
    global $modifyoutput;
    $dbprefix = Yii::app()->db->tablePrefix;
    $aTables = dbGetTablesLike("survey\_%");
    foreach ( $aTables as $sTable )
    {
        addColumn($sTable,'lastpage','integer');
    }
}


// Add the reminders tracking fields
function upgradeTokenTables134()
{
    global $modifyoutput;
    $aTables = dbGetTablesLike("tokens%");
    foreach ( $aTables as $sTable )
    {
        addColumn($sTable,'validfrom',"datetime");
        addColumn($sTable,'validuntil',"datetime");
    }
}

// Add the reminders tracking fields
function upgradeTokens128()
{
    global $modifyoutput;
    $sVarchar=Yii::app()->getConfig('varchar');
    $aTables = dbGetTablesLike("tokens%");
    foreach ( $aTables as $sTable )
    {
        addColumn($sTable,'remindersent',"{$sVarchar}(17) DEFAULT 'N'");
        addColumn($sTable,'remindercount',"integer DEFAULT '0'");
    }
}


function fixMySQLCollations()
{
    global $modifyoutput;
    $sql = 'SHOW TABLE STATUS';
    $dbprefix = Yii::app()->db->tablePrefix;
    $result = Yii::app()->db->createCommand($sql)->queryAll();
    foreach ( $result as $tables ) {
        // Loop through all tables in this database
        $table = $tables['Name'];
        $tablecollation=$tables['Collation'];
        if (strpos($table,'old_')===false  && ($dbprefix==''  || ($dbprefix!='' && strpos($table,$dbprefix)!==false)))
        {
            if ($tablecollation!='utf8_unicode_ci')
            {
                modifyDatabase("","ALTER TABLE $table COLLATE utf8_unicode_ci");
                echo $modifyoutput; flush();@ob_flush();
            }

            # Now loop through all the fields within this table
            $result2 = Yii::app()->db->createCommand("SHOW FULL COLUMNS FROM ".$table)->queryAll();
            foreach ( $result2 as $column )
            {
                if ($column['Collation']!= 'utf8_unicode_ci' )
                {
                    $field_name = $column['Field'];
                    $field_type = $column['Type'];
                    $field_default = $column['Default'];
                    if ($field_default!='NULL') {$field_default="'".$field_default."'";}
                    # Change text based fields
                    $skipped_field_types = array('char', 'text', 'enum', 'set');

                    foreach ( $skipped_field_types as $type )
                    {
                        if ( strpos($field_type, $type) !== false )
                        {
                            $modstatement="ALTER TABLE $table CHANGE `$field_name` `$field_name` $field_type CHARACTER SET utf8 COLLATE utf8_unicode_ci";
                            if ($type!='text') {$modstatement.=" DEFAULT $field_default";}
                            modifyDatabase("",$modstatement);
                            echo $modifyoutput; flush();@ob_flush();
                        }
                    }
                }
            }
        }
    }
}

function upgradeSurveyTables126()
{
    $surveyidquery = "SELECT sid FROM {{surveys}} WHERE active='Y' and datestamp='Y'";
    $surveyidresult = Yii::app()->db->createCommand($surveyidquery)->queryAll();
    if (!$surveyidresult) {return "Database Error";}
    else
    {
        foreach ( $surveyidresult as $sv )
        {
            addColumn('{{survey_'.$sv['sid'].'}}','startdate','datetime');
        }
    }
}

function upgradeTokenTables126()
{
    global $modifyoutput;
    $sVarchar=Yii::app()->getConfig('varchar');
    $aTables = dbGetTablesLike("tokens%");
    foreach ( $aTables as $sTable )
    {
        Yii::app()->db->createCommand()->alterColumn($sTable,'token',"{$sVarchar}(15)");
        addColumn($sTable,'emailstatus',"{$sVarchar}(300) NOT NULL DEFAULT 'OK'");
    }
}

function alterLanguageCode($sOldLanguageCode,$sNewLanguageCode)
{
    Yii::app()->db->createCommand()->update('{{answers}}',array('language'=>$sNewLanguageCode),'language=:lang',array(':lang'=>$sOldLanguageCode));
    Yii::app()->db->createCommand()->update('{{questions}}',array('language'=>$sNewLanguageCode),'language=:lang',array(':lang'=>$sOldLanguageCode));
    Yii::app()->db->createCommand()->update('{{groups}}',array('language'=>$sNewLanguageCode),'language=:lang',array(':lang'=>$sOldLanguageCode));
    Yii::app()->db->createCommand()->update('{{labels}}',array('language'=>$sNewLanguageCode),'language=:lang',array(':lang'=>$sOldLanguageCode));
    Yii::app()->db->createCommand()->update('{{surveys}}',array('language'=>$sNewLanguageCode),'language=:lang',array(':lang'=>$sOldLanguageCode));
    Yii::app()->db->createCommand()->update('{{surveys_languagesettings}}',array('surveyls_language'=>$sNewLanguageCode),'surveyls_language=:lang',array(':lang'=>$sOldLanguageCode));
    Yii::app()->db->createCommand()->update('{{users}}',array('lang'=>$sNewLanguageCode),'lang=:language',array(':language'=>$sOldLanguageCode));

    $resultdata=Yii::app()->db->createCommand("select * from {{labelsets}}");
    foreach ($resultdata->queryAll() as $datarow){
        $aLanguages=explode(' ',$datarow['languages']);
        foreach ($aLanguages as &$sLanguage)
        {
            if ($sLanguage==$sOldLanguageCode) $sLanguage=$sNewLanguageCode; 
        }
        $toreplace=implode(' ',$aLanguages);
        Yii::app()->db->createCommand()->update('{{labelsets}}',array('languages'=>$toreplace),'lid=:lid',array(':lid'=>$datarow['lid']));
    }

    $resultdata=Yii::app()->db->createCommand("select * from {{surveys}}");
    foreach ($resultdata->queryAll() as $datarow){
        $aLanguages=explode(' ',$datarow['additional_languages']);
        foreach ($aLanguages as &$sLanguage)
        {
            if ($sLanguage==$sOldLanguageCode) $sLanguage=$sNewLanguageCode; 
        }
        $toreplace=implode(' ',$aLanguages);
        Yii::app()->db->createCommand()->update('{{surveys}}',array('additional_languages'=>$toreplace),'sid=:sid',array(':sid'=>$datarow['sid']));
    }
}

function addPrimaryKey($sTablename, $aColumns)
{
    Yii::app()->db->createCommand("ALTER TABLE {{".$sTablename."}} ADD PRIMARY KEY (".implode(',',$aColumns).")")->execute();
}


function dropPrimaryKey($sTablename)
{
    $sDBDriverName=Yii::app()->db->getDriverName();
    if ($sDBDriverName=='mysqli') $sDBDriverName='mysql';
    if ($sDBDriverName=='sqlsrv') $sDBDriverName='mssql';

    global $modifyoutput;
    switch ($sDBDriverName){
        case 'mysql':
            $sQuery="ALTER TABLE {{".$sTablename."}} DROP PRIMARY KEY";
            Yii::app()->db->createCommand($sQuery)->execute();
            break;
        case 'pgsql':
        case 'mssql':
            $pkquery = "SELECT CONSTRAINT_NAME "
            ."FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS "
            ."WHERE (TABLE_NAME = '{{{$sTablename}}}') AND (CONSTRAINT_TYPE = 'PRIMARY KEY')";

            $primarykey = Yii::app()->db->createCommand($pkquery)->queryRow(false);
            if ($primarykey!==false)
            {
                $sQuery="ALTER TABLE {{".$sTablename."}} DROP CONSTRAINT ".$primarykey[0];
                Yii::app()->db->createCommand($sQuery)->execute();
            }
            break;
        default: die('Unkown database type');
    }

    // find out the constraint name of the old primary key
}

function fixLanguageConsistencyAllSurveys()
{
    $surveyidquery = "SELECT sid,additional_languages FROM ".dbQuoteID('{{surveys}}');
    $surveyidresult = Yii::app()->db->createCommand($surveyidquery)->queryAll();
    foreach ( $surveyidresult as $sv )
    {
        fixLanguageConsistency($sv['sid'],$sv['additional_languages']);
    }
}

function alterColumn($sTable, $sColumn, $sFieldType, $bAllowNull=true, $sDefault='NULL')
{
    $sDBDriverName=Yii::app()->db->getDriverName();
    if ($sDBDriverName=='mysqli') $sDBDriverName='mysql';
    if ($sDBDriverName=='sqlsrv') $sDBDriverName='mssql';
    switch ($sDBDriverName){
        case 'mysql':

            $sType=$sFieldType;
            if ($bAllowNull!=true)
            {
                $sType.=' NOT NULL';
            }
            if ($sDefault!='NULL')
            {
                $sType.=" DEFAULT '{$sDefault}'";
            }
            Yii::app()->db->createCommand()->alterColumn($sTable,$sColumn,$sType);
            break;
        case 'mssql':
            dropDefaultValueMSSQL($sColumn,$sTable);
            $sType=$sFieldType;
            if ($sType=='text') {
                $sType='varchar(max)';
            }
            if ($sType=='binary') {
                $sType='text';
            }
            if ($bAllowNull!=true)
            {
                $sType.=' NOT NULL';
                if ($sDefault!='NULL')
                {
                   Yii::app()->db->createCommand("UPDATE {$sTable} SET [{$sColumn}]='{$sDefault}' where [{$sColumn}] is NULL;")->execute();
                }
            }
            Yii::app()->db->createCommand()->alterColumn($sTable,$sColumn,$sType);
            if ($sDefault!='NULL')
            {
                Yii::app()->db->createCommand("ALTER TABLE {$sTable} ADD default '{$sDefault}' FOR [{$sColumn}];")->execute();
            }
            break;
        case 'pgsql':
        $sType=$sFieldType;
        Yii::app()->db->createCommand()->alterColumn($sTable,$sColumn,$sType);
        try{ Yii::app()->db->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} DROP DEFAULT")->execute();} catch(Exception $e) {};
        try{ Yii::app()->db->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} DROP NOT NULL")->execute();} catch(Exception $e) {};

        if ($bAllowNull!=true)
        {
            Yii::app()->db->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} SET NOT NULL")->execute();
        }
        if ($sDefault!='NULL')
        {
            Yii::app()->db->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} SET DEFAULT '{$sDefault}'")->execute();
        }
        Yii::app()->db->createCommand()->alterColumn($sTable,$sColumn,$sType);
        break;
        default: die('Unkown database type');
    }

}


function dropColumn($sTableName, $sColumnName)
{
    $sDBDriverName=Yii::app()->db->getDriverName();
    if ($sDBDriverName=='mysqli') $sDBDriverName='mysql';
    if ($sDBDriverName=='sqlsrv') $sDBDriverName='mssql';
    if ($sDBDriverName=='mssql')
    {
        dropDefaultValueMSSQL($sColumnName,$sTableName);
    }
    Yii::app()->db->createCommand()->dropColumn($sTableName,$sColumnName);
}





function addColumn($sTableName, $sColumn, $sType)
{
    $sDBDriverName=Yii::app()->db->getDriverName();
    if ($sDBDriverName=='mysqli') $sDBDriverName='mysql';
    if ($sDBDriverName=='sqlsrv') $sDBDriverName='mssql';
    if ($sDBDriverName=='mssql')
    {
        $sType=str_replace('text','varchar(max)',$sType);
        $sType=str_replace('binary','text',$sType);
    }
    Yii::app()->db->createCommand()->addColumn($sTableName,$sColumn,$sType);
}


function setTransactionBookmark($sBookmark='limesurvey')
{
    $sDBDriverName=Yii::app()->db->getDriverName();
    if ($sDBDriverName=='pgsql')
    {
        Yii::app()->db->createCommand("SAVEPOINT {$sBookmark};")->execute();
    }
}

function rollBackToTransactionBookmark($sBookmark='limesurvey')
{
    $sDBDriverName=Yii::app()->db->getDriverName();
    if ($sDBDriverName=='pgsql')
    {
        Yii::app()->db->createCommand("ROLLBACK TO SAVEPOINT {$sBookmark};")->execute();
    }
}


function dropDefaultValueMSSQL($fieldname, $tablename)
{
    // find out the name of the default constraint
    // Did I already mention that this is the most suckiest thing I have ever seen in MSSQL database?
    $dfquery ="SELECT c_obj.name AS constraint_name
    FROM  sys.sysobjects AS c_obj INNER JOIN
    sys.sysobjects AS t_obj ON c_obj.parent_obj = t_obj.id INNER JOIN
    sys.sysconstraints AS con ON c_obj.id = con.constid INNER JOIN
    sys.syscolumns AS col ON t_obj.id = col.id AND con.colid = col.colid
    WHERE (c_obj.xtype = 'D') AND (col.name = '$fieldname') AND (t_obj.name='{$tablename}')";
    $defaultname = Yii::app()->db->createCommand($dfquery)->queryRow();
    if ($defaultname!=false)
    {
        Yii::app()->db->createCommand("ALTER TABLE {$tablename} DROP CONSTRAINT {$defaultname['constraint_name']}")->execute();
    }

}

/**
* Returns the name of the DB Driver - used for other functions that make db specific calls
*
*/
function setsDBDriverName() {
    $sDBDriverName=Yii::app()->db->getDriverName();
    if ($sDBDriverName=='mysqli') $sDBDriverName='mysql';
    if ($sDBDriverName=='sqlsrv') $sDBDriverName='mssql';
    return $sDBDriverName;
}

/**
* Special customisation because Yii is limited in its ability to handle varchar fields of lenghts other than 255 in a cross-db
* compatible way. see http://www.yiiframework.com/forum/index.php/topic/32289-cross-db-compatible-varchar-field-length-definitions/
* and http://github.com/yiisoft/yii/issues/765
*
* Note that it sets values for the config files for use later, and does not return any values.
* Access the set values using Yii::app()->getConfig('varchar') or Yii::app()->getConfigu('autoincrement');
*
* @param mixed $sDBDriverName The name of the db driver being used. If the parameter is forgotten, the
*                             function is capable of retrieving it itself.
*/
function setVarchar($sDBDriverName=null) {

    if(!$sDBDriverName) {
        $sDBDriverName=setsDBDriverName();
    }

    if ($sDBDriverName=='pgsql')
    {
        Yii::app()->setConfig('varchar',$sVarchar='character varying');
        Yii::app()->setConfig('autoincrement', $sAutoIncrement='serial');
    }
    elseif ($sDBDriverName=='mssql')
    {
        Yii::app()->setConfig('varchar',$sVarchar='varchar');
        Yii::app()->setConfig('autoincrement', $sAutoIncrement='integer NOT NULL IDENTITY (1,1)');
    }
    else
    {
        Yii::app()->setConfig('varchar',$sVarchar='varchar');
        Yii::app()->setConfig('autoincrement', $sAutoIncrement='int(11) NOT NULL AUTO_INCREMENT');
    }
}

function replaceTemplateJS(){
    $usertemplaterootdir=Yii::app()->getConfig("usertemplaterootdir");
    $clang = Yii::app()->lang;
    if (!$usertemplaterootdir) {return false;}
    $countstartpage=0;
    $counterror=0;
    $errortemplate=array();
    if ($handle = opendir($usertemplaterootdir))
    {
        while (false !== ($file = readdir($handle)))
        {
            if ($file != "." && $file != ".." && is_dir("{$usertemplaterootdir}/{$file}")) {
                $fname = "$usertemplaterootdir/$file/startpage.pstpl";
                if (is_file($fname))
                {
                    if(is_writable($fname)){
                        $fhandle = fopen($fname,"r");
                        $content = fread($fhandle,filesize($fname));
                        $content = str_replace("<script type=\"text/javascript\" src=\"{TEMPLATEURL}template.js\"></script>", "{TEMPLATEJS}", $content);
                        $fhandle = fopen($fname,"w");
                        fwrite($fhandle,$content);
                        fclose($fhandle);
                        if(strpos($content, "{TEMPLATEJS}")===false)
                        {
                            $counterror++;
                            $errortemplate[]=$file;
                        }
                    }else{
                        $counterror++;
                    }
                    $countstartpage++;
                }
            }
        }
        closedir($handle);
    }
        if($counterror)
        {
            echo $clang->gT("Some user templates can not be updated, please add the placeholder {TEMPLATEJS} in your startpage.pstpl manually.");
            echo "<br />";
            echo $clang->gT("Template(s) to be verified :");
            echo implode(",",$errortemplate);
        }
        else
        {
            if($countstartpage){
                echo sprintf($clang->gT("All %s user templates updated."),$countstartpage);
            }
        }
    if($counterror){
        return false;
    }else{
        return $countstartpage;
    }
}

/**
 *  Make sure all active tables have the right sized token field
 * 
 *  During a small period in the 2.0 cycle some survey tables got no
 *  token field or a token field that was too small. This patch makes
 *  sure all surveys that are not anonymous have a token field with the
 *  right size
 * 
 * @return void
 */
function upgradeSurveyTables164()
{
    $surveyidquery = "SELECT sid FROM {{surveys}} WHERE active='Y' and anonymized='N'";
    $surveyidresult = Yii::app()->db->createCommand($surveyidquery)->queryAll();
    if (!$surveyidresult) {
        return "Database Error";
    } else {
        foreach ( $surveyidresult as $sv )
        {
            $token = Survey_dynamic::model($sv['sid'])->getTableSchema()->getColumn('token');
            if (is_null($token)) {
                addColumn('{{survey_'.$sv['sid'].'}}','token','varchar(36)');
            } elseif ($token->size < 36) {
                alterColumn('{{survey_'.$sv['sid'].'}}','token','varchar(36)');
            }
        }
    }
}
