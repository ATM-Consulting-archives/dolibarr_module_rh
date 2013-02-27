<?php

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
 * 	$Id$
 */

/**
 * This is the model class for table "{{participants}}".
 *
 * The followings are the available columns in table '{{participants}}':
 * @property string $participant_id
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string $language
 * @property string $blacklisted
 * @property integer $owner_uid
 */
class Participants extends CActiveRecord
{

	/**
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
     * @param string $class
	 * @return Participants
	 */
	public static function model($class = __CLASS__)
	{
		return parent::model($class);
	}

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{participants}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('participant_id, blacklisted, owner_uid', 'required'),
            array('owner_uid', 'numerical', 'integerOnly' => true),
            array('participant_id', 'length', 'max' => 50),
            array('firstname, lastname, language', 'length', 'max' => 40),
            array('email', 'length', 'max' => 80),
            array('blacklisted', 'length', 'max' => 1),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('participant_id, firstname, lastname, email, language, blacklisted, owner_uid', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'participant_id' => 'Participant',
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'email' => 'Email',
            'language' => 'Language',
            'blacklisted' => 'Blacklisted',
            'owner_uid' => 'Owner Uid',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('participant_id', $this->participant_id, true);
        $criteria->compare('firstname', $this->firstname, true);
        $criteria->compare('lastname', $this->lastname, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('language', $this->language, true);
        $criteria->compare('blacklisted', $this->blacklisted, true);
        $criteria->compare('owner_uid', $this->owner_uid);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /*
     * funcion for generation of unique id
     */

    function gen_uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /*
     * This function is responsible for adding the participant to the database
     * Parameters : participant data
     * Return Data : none
     */

    function insertParticipant($data)
    {
        Yii::app()->db->createCommand()->insert('{{participants}}', $data);
    }
    
    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey() {
        return 'participant_id';
    }

    /**
     * This function updates the data edited in the jqgrid
     * 
     * @param aray $data
     */
    function updateRow($data)
    {
        $record = $this->findByPk($data['participant_id']);
        foreach ($data as $key => $value)
        {
            $record->$key = $value;
        }
        $record->save();
    }

    /*
     * This function returns a list of participants who are either owned or shared
     * with a specific user
     *
     * @params int $userid The ID of the user that we are listing participants for
     *
     * @return object containing all the users
     */
    function getParticipantsOwner($userid)
    {
        $subquery = Yii::app()->db->createCommand()
            ->select('{{participants}}.participant_id,{{participant_shares}}.can_edit')
            ->from('{{participants}}')
            ->leftJoin('{{participant_shares}}', ' {{participants}}.participant_id={{participant_shares}}.participant_id')
            ->where('owner_uid = :userid1 OR share_uid = :userid2')
            ->group('{{participants}}.participant_id,{{participant_shares}}.can_edit');
        
        $command = Yii::app()->db->createCommand()
                ->select('p.*, ps.can_edit')
                ->from('{{participants}} p')
                ->join('(' . $subquery->getText() . ') ps', 'ps.participant_id = p.participant_id')
                ->bindParam(":userid1", $userid, PDO::PARAM_INT)
                ->bindParam(":userid2", $userid, PDO::PARAM_INT);
        
        return $command->queryAll();
    }

    function getParticipantsOwnerCount($userid)
    {
        $command = Yii::app()->db->createCommand()
                        ->select('count(*)')
                        ->from('{{participants}} p')
                        ->leftJoin('{{participant_shares}} ps', 'ps.participant_id = p.participant_id')
                        ->where('p.owner_uid = :userid1 OR ps.share_uid = :userid2')
                        ->bindParam(":userid1", $userid, PDO::PARAM_INT)
                        ->bindParam(":userid2", $userid, PDO::PARAM_INT);
        return $command->queryScalar();
    }

    /**
     * Get the number of participants, no restrictions
     * 
     * @return int
     */
    function getParticipantsCountWithoutLimit()
    {
        return Participants::model()->count();    
    }
    
    function getParticipantsWithoutLimit()
    {
        return Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
    }

    /**
     * This function combines the shared participant and the central participant
     * table and searches for any reference of owner id in the combined record
     * of the two tables
     * 
     * @param  int $userid The id of the owner
     * @return int The number of participants owned by $userid who are shared
     */  
    function getParticipantsSharedCount($userid)
    {
        $count = Yii::app()->db->createCommand()->select('count(*)')->from('{{participants}}')->join('{{participant_shares}}', '{{participant_shares}}.participant_id = {{participants}}.participant_id')->where('owner_uid = :userid')->bindParam(":userid", $userid, PDO::PARAM_INT)->queryScalar();
        return $count;
    }

    function getParticipants($page, $limit,$attid, $order = null, $search = null, $userid = null)
    {
        $data = $this->getParticipantsSelectCommand(false, $attid, $search, $userid, $page, $limit, $order);
        
        $allData = $data->queryAll();

        return $allData;
    }
    
    /**
     * Duplicated from getparticipants, only to have a count
     * 
     * @param type $attid
     * @param type $order
     * @param CDbCriteria $search
     * @param type $userid
     * @return type
     */    
    function getParticipantsCount($attid, $search = null, $userid = null) {
        $data = $this->getParticipantsSelectCommand(true, $attid, $search, $userid);
        
        return $data->queryScalar();
    }
    
    private function getParticipantsSelectCommand($count = false, $attid, $search = null, $userid = null, $page = null, $limit = null, $order = null)
    {
        $selectValue = array();
        $joinValue = array();
        
        $selectValue[] = "p.*";
        $selectValue[] = "luser.full_name as ownername";
        
        // Add survey count subquery
        $subQuery = Yii::app()->db->createCommand()
                ->select('count(*) survey')
                ->from('{{survey_links}} sl')
                ->where('sl.participant_id = p.participant_id');
        $selectValue[] = sprintf('(%s) survey',$subQuery->getText());
        array_push($joinValue,"left join {{users}} luser ON luser.uid=p.owner_uid");
        foreach($attid as $key=>$attid)
        {
            $attid = $attid['attribute_id'];
            $databasetype = Yii::app()->db->getDriverName();
            if ($databasetype=='mssql' || $databasetype=="sqlsrv")
            {
                $selectValue[]= "cast(attribute".$attid.".value as varchar(max)) as a".$attid;
            } else {
                $selectValue[]= "attribute".$attid.".value as a".$attid;
            }
            array_push($joinValue,"LEFT JOIN {{participant_attribute}} attribute".$attid." ON attribute".$attid.".participant_id=p.participant_id AND attribute".$attid.".attribute_id=".$attid);
        }
        
        $aConditions = array(); // this wil hold all conditions
        $aParams = array();
        if (!is_null($userid)) {
            // We are not superadmin so we need to limit to our own or shared with us
            $selectValue[] = '{{participant_shares}}.can_edit';
            $joinValue[]   = 'LEFT JOIN {{participant_shares}} ON p.participant_id={{participant_shares}}.participant_id';
            $aConditions[] = 'p.owner_uid = :userid1 OR {{participant_shares}}.share_uid = :userid2';
        }
              
        if ($count) {
            $selectValue = 'count(*) as cnt';
        }
        
        $data = Yii::app()->db->createCommand()
                ->select($selectValue)
                ->from('{{participants}} p');
        $data->setJoin($joinValue);
        
        if (!empty($search)) {
            /* @var $search CDbCriteria */
             $aSearch = $search->toArray();
             $aConditions[] = $aSearch['condition'];
             $aParams = $aSearch['params'];
        }
        $condition = ''; // This will be the final condition
        foreach ($aConditions as $idx => $newCondition) {
            if ($idx>0) { 
                $condition .= ' AND ';
            }
            $condition .= '(' . $newCondition . ')';
        }
                
        if (!empty($condition)) {
            $data->setWhere($condition);
        }
        
        if (!$count) {
            // Apply order and limits
            if (!empty($order)) {
                $data->setOrder($order);
            }

            if ($page <> 0) {
                $offset = ($page - 1) * $limit;
                $data->offset($offset)
                     ->limit($limit);
            }
        }
        
        $data->bindValues($aParams);
        
        if (!is_null($userid)) {
            $data->bindParam(":userid1", $userid, PDO::PARAM_INT)
                 ->bindParam(":userid2", $userid, PDO::PARAM_INT);
        }
        
        return $data;
    }

    function getSurveyCount($participant_id)
    {
        $count = Yii::app()->db->createCommand()->select('count(*)')->from('{{survey_links}}')->where('participant_id = :participant_id')->bindParam(":participant_id", $participant_id, PDO::PARAM_INT)->queryScalar();
        return $count;
    }

    /*
     * These functions delete the row marked in the navigator
     * Parameters : row id's
     * Return Data : None
     */

    function deleteParticipant($rows)
    {
    	/* This function deletes the participant from the participants table,
    	   references in the survey_links table (but not in matching tokens tables)
    	   and then all the participants attributes. */

		// Converting the comma seperated id's to an array to delete multiple rows
        $rowid = explode(",", $rows);
        foreach ($rowid as $row)
        {
            Yii::app()->db->createCommand()->delete(Participants::model()->tableName(), array('in', 'participant_id', $row));
        	Yii::app()->db->createCommand()->delete(Survey_links::model()->tableName(), array('in', 'participant_id', $row));
        	Yii::app()->db->createCommand()->delete(Participant_attribute::model()->tableName(), array('in', 'participant_id', $row));
        }
    }

    function deleteParticipantToken($rows)
    {
    	/* This function deletes the participant from the participants table,
    	   the participant from any tokens table they're in (using the survey_links table to find them)
    	   and then all the participants attributes. */
        $rowid = explode(",", $rows);
        foreach ($rowid as $row)
        {
            $tokens = Yii::app()->db->createCommand()->select('*')->from('{{survey_links}}')->where('participant_id = :pid')->bindParam(":pid", $row, PDO::PARAM_INT)->queryAll();

			foreach ($tokens as $key => $value)
            {
                $tokentable='{{tokens_'.intval($value['survey_id']).'}}';

                if (Yii::app()->db->schema->getTable($tokentable))
                {
                    Yii::app()->db->createCommand()
                                  ->delete('{{tokens_' . intval($value['survey_id']) . '}}', 'participant_id = :pid',array(':pid'=>$row)); // Deletes matching token table entries
                    //Yii::app()->db->createCommand()->delete(Tokens::model()->tableName(), array('in', 'participant_id', $row));
				}
            }
        	Yii::app()->db->createCommand()->delete(Participants::model()->tableName(), array('in', 'participant_id', $row));
        	Yii::app()->db->createCommand()->delete(Survey_links::model()->tableName(), array('in', 'participant_id', $row));
        	Yii::app()->db->createCommand()->delete(Participant_attribute::model()->tableName(), array('in', 'participant_id', $row));

        }
    }

	function deleteParticipantTokenAnswer($rows)
	{
		/* This function deletes the participant from the participants table,
		   the participant from any tokens table they're in (using the survey_links table to find them),
		   all responses in surveys they've been linked to,
		   and then all the participants attributes. */
		$rowid = explode(",", $rows);
		foreach ($rowid as $row)
		{
			$tokens = Yii::app()->db->createCommand()
                                    ->select('*')
                                    ->from('{{survey_links}}')
                                    ->where('participant_id = :row')
                                    ->bindParam(":row", $row, PDO::PARAM_INT)
                                    ->queryAll();

			foreach ($tokens as $key => $value)
			{
                $tokentable='{{tokens_'.intval($value['survey_id']).'}}';
				if (Yii::app()->db->schema->getTable($tokentable))
				{
					$tokenid = Yii::app()->db->createCommand()
                                             ->select('token')
                                             ->from('{{tokens_' . intval($value['survey_id']) . '}}')
                                             ->where('participant_id = :pid')
                                             ->bindParam(":pid", $value['participant_id'], PDO::PARAM_INT)
                                             ->queryAll();
					$token = $tokenid[0];
                    $surveytable='{{survey_'.intval($value['survey_id']).'}}';
					if ($datas=Yii::app()->db->schema->getTable($surveytable))
					{
						if (!empty($token['token']) && isset($datas->columns['token'])) //Make sure we have a token value, and that tokens are used to link to the survey
						{
							$gettoken = Yii::app()->db->createCommand()
                                                      ->select('*')
                                                      ->from('{{survey_' . intval($value['survey_id']) . '}}')
                                                      ->where('token = :token')
                                                      ->bindParam(":token", $token['token'], PDO::PARAM_STR)
                                                      ->queryAll();
							$gettoken = $gettoken[0];
							Yii::app()->db->createCommand()
                                          ->delete('{{survey_' . intval($value['survey_id']) . '}}', 'token = :token')
                                          ->bindParam(":token", $gettoken['token'], PDO::PARAM_STR); // Deletes matching responses from surveys
						}
					}
					Yii::app()->db->createCommand()
                                  ->delete('{{tokens_' . intval($value['survey_id']) . '}}', 'participant_id = :pid' , array(':pid'=>$value['participant_id'])); // Deletes matching token table entries
				}
			}
			Yii::app()->db->createCommand()->delete(Participants::model()->tableName(), array('in', 'participant_id', $row));
			Yii::app()->db->createCommand()->delete(Survey_links::model()->tableName(), array('in', 'participant_id', $row));
			Yii::app()->db->createCommand()->delete(Participant_attribute::model()->tableName(), array('in', 'participant_id', $row));
		}
	}

    /*
     * Function builds a select query for searches through participants using the $condition field passed
     * which is in the format "firstfield||sqloperator||value||booleanoperator||secondfield||sqloperator||value||booleanoperator||etc||etc||etc"
     * for example: "firstname||equal||Jason||and||lastname||equal||Cleeland" will produce SQL along the lines of "WHERE firstname = 'Jason' AND lastname=='Cleeland'"
     *
     * @param array $condition an array containing the search string exploded using || so that "firstname||equal||jason" is $condition(1=>'firstname', 2=>'equal', 3=>'jason')
     * @param int $page Which page number to display
     * @param in $limit The limit/number of reords to return
     *
     * @returns array $output
     *
     *
     * */
    function getParticipantsSearchMultiple($condition, $page, $limit)
    {
    	//http://localhost/limesurvey_yii/admin/participants/getParticipantsResults_json/search/email||contains||gov||and||firstname||contains||AL
    	//First contains fieldname, second contains method, third contains value, fourth contains BOOLEAN SQL and, or

        //As we iterate through the conditions we build up the $command query by adding conditions to it
        //
        $i = 0;
        $tobedonelater = array();
        $start = $limit * $page - $limit;
        $command = new CDbCriteria;
        $command->condition = '';

        //The following code performs an IN-SQL order, but this only works for standard participant fields
        //For the time being, lets stick to just sorting the collected results, some thinking
        //needs to be done about how we can sort the actual fullo query when combining with calculated
        //or attribute based fields. I've switched this off, but left the code for future reference. JC
        if(1==2)
        {
            $sord = Yii::app()->request->getPost('sord'); //Sort order
            $sidx = Yii::app()->request->getPost('sidx'); //Sort index
            if(is_numeric($sidx) || $sidx=="survey") {
                $sord=""; $sidx="";
            }
            if(!empty($sidx)) {
                $sortorder="$sidx $sord";
            } else {
                $sortorder="";
            }
            if(!empty($sortorder))
            {
                $command->order=$sortorder;
            }
        }

        $con = count($condition);
        while ($i < $con && $con > 2)
        {
            if ($i < 3) //Special set just for the first query/condition
            {
                if(is_numeric($condition[2])) $condition[2]=intval($condition[2]);
                switch($condition[1])
                {
                    case 'equal':
                        $operator="=";
                        break;
                    case 'contains':
                        $operator="LIKE";
                        $condition[2]="%".$condition[2]."%";
                        break;
                    case 'beginswith':
                        $operator="LIKE";
                        $condition[2]=$condition[2]."%";
                        break;
                    case 'notequal':
                        $operator="!=";
                        break;
                    case 'notcontains':
                        $operator="NOT LIKE";
                        $condition[2]="%".$condition[2]."%";
                        break;
                    case 'greaterthan':
                        $operator=">";
                        break;
                    case 'lessthan':
                        $operator="<";
                }
                if($condition[0]=="survey")
                {
                    $lang = Yii::app()->session['adminlang'];
                    $command->addCondition('participant_id IN (SELECT distinct {{survey_links}}.participant_id FROM {{survey_links}}, {{surveys_languagesettings}} WHERE {{survey_links}}.survey_id = {{surveys_languagesettings}}.surveyls_survey_id AND {{surveys_languagesettings}}.surveyls_language=:lang AND {{survey_links}}.survey_id '.$operator.' :param)');
                    $command->params=array(':lang'=>$lang,  ':param'=>$condition[2]);
                }
                elseif($condition[0]=="surveys") //Search by quantity of linked surveys
                {
                    $addon = ($operator == "<") ? " OR participant_id NOT IN (SELECT distinct participant_id FROM {{survey_links}})" : "";
                    $command->addCondition('participant_id IN (SELECT participant_id FROM {{survey_links}} GROUP BY participant_id HAVING count(*) '.$operator.' :param2 ORDER BY count(*))'.$addon);
                    $command->params=array(':param2'=>$condition[2]);
                }
                elseif($condition[0]=="owner_name")
                {
                    $userid = Yii::app()->db->createCommand()
                                        ->select('uid')
                                        ->where('full_name '.$operator.' :condition_2')
                                        ->from('{{users}}')
                                        ->bindParam("condition_2", $condition[2], PDO::PARAM_STR)
                                        ->queryAll();
                    $uid = $userid[0];
                    $command->addCondition('owner_uid = :uid');
                    $command->params=array(':uid'=>$uid['uid']);
                }
                elseif (is_numeric($condition[0])) //Searching for an attribute
                {
                    $command->addCondition('participant_id IN (SELECT distinct {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = :condition_0 AND {{participant_attribute}}.value '.$operator.' :condition_2)');
                    $command->params=array(':condition_0'=>$condition[0], ':condition_2'=>$condition[2]);
                }
                else
                {
                    $command->addCondition($condition[0] . ' '.$operator.' :condition_2');
                    $command->params=array(':condition_2'=>$condition[2]);
                }
                 $i+=3;
            }
            else if ($condition[$i] != '') //This section deals with subsequent filter conditions that have boolean joiner
            {
                if(is_numeric($condition[$i+3])) $condition[$i+3]=intval($condition[$i+3]); //Force the type of numeric values to be numeric
                $booloperator=strtoupper($condition[$i]);
                $condition1name=":condition_".($i+1);
                $condition2name=":condition_".($i+3);
                switch($condition[$i+2])
                    {
                        case 'equal':
                            $operator="=";
                            break;
                        case 'contains':
                            $operator="LIKE";
                            $condition[$i+3]="%".$condition[$i+3]."%";
                            break;
                        case 'beginswith':
                            $operator="LIKE";
                            $condition[$i+3]=$condition[$i+3]."%";
                            break;
                        case 'notequal':
                            $operator="!=";
                            break;
                        case 'notcontains':
                            $operator="NOT LIKE";
                            $condition[$i+3]="%".$condition[$i+3]."%";
                            break;
                        case 'greaterthan':
                            $operator=">";
                            break;
                        case 'lessthan':
                            $operator="<";
                    }
                if($condition[$i+1]=="survey")
                {
                    $lang = Yii::app()->session['adminlang'];
                    $command->addCondition('participant_id IN (SELECT distinct {{survey_links}}.participant_id FROM {{survey_links}}, {{surveys_languagesettings}} WHERE {{survey_links}}.survey_id = {{surveys_languagesettings}}.surveyls_survey_id AND {{surveys_languagesettings}}.surveyls_language=:lang AND ({{surveys_languagesettings}}.surveyls_title '.$operator.' '.$condition2name.'1 OR {{survey_links}}.survey_id '.$operator.' '.$condition2name.'2))', $booloperator);
                    $command->params=array_merge($command->params, array(':lang'=>$lang, $condition2name.'1'=>$condition[$i+3], $condition2name.'2'=>$condition[$i+3]));
                } elseif ($condition[$i+1]=="surveys") //search by quantity of linked surveys
                {
                    $addon = ($operator == "<") ? " OR participant_id NOT IN (SELECT distinct participant_id FROM {{survey_links}})" : "";
                    $command->addCondition('participant_id IN (SELECT participant_id FROM {{survey_links}} GROUP BY participant_id HAVING count(*) '.$operator.' '.$condition2name.' ORDER BY count(*))'.$addon);
                    $command->params=array_merge($command->params, array($condition2name=>$condition[$i+3]));
                }
                elseif($condition[$i+1]=="owner_name")
                {
                    $userid = Yii::app()->db->createCommand()
                                        ->select('uid')
                                        ->where('full_name '.$operator.' '.$condition2name)
                                        ->from('{{users}}')
                                        ->bindParam($condition2name, $condition[$i+3], PDO::PARAM_STR)
                                        ->queryAll();
                    $uid=array();
                    foreach($userid as $row) {$uid[]=$row['uid'];}
                    $command->addInCondition('owner_uid', $uid, $booloperator);
                }
                elseif (is_numeric($condition[$i+1])) //Searching for an attribute
                {
                    $command->addCondition('participant_id IN (SELECT distinct {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition1name.' AND {{participant_attribute}}.value '.$operator.' '.$condition2name.')', $booloperator);
                    $command->params=array_merge($command->params, array($condition1name=>$condition[$i+1], $condition2name=>$condition[$i+3]));
                }
                else
                {
                    $command->addCondition($condition[$i+1] . ' '.$operator.' '.$condition2name, $booloperator);
                    $command->params=array_merge($command->params, array($condition2name=>$condition[$i+3]));
                }
                $i = $i + 4;
            }
            else
            {
                $i = $i + 4;
            }
        }
        
    	if ($page == 0 && $limit == 0)
        {
            $arr = Participants::model()->findAll($command);
            $data = array();
            foreach ($arr as $t)
            {
                $data[$t->participant_id] = $t->attributes;
            }
        }
        else
        {
            $command->limit = $limit;
            $command->offset = $start;
            $arr = Participants::model()->findAll($command);
            $data = array();
            foreach ($arr as $t)
            {
                $data[$t->participant_id] = $t->attributes;
            }
        }

        return $data;
    }
    
    /** 
     * Function builds a select query for searches through participants using the $condition field passed
     * which is in the format "firstfield||sqloperator||value||booleanoperator||secondfield||sqloperator||value||booleanoperator||etc||etc||etc"
     * for example: "firstname||equal||Jason||and||lastname||equal||Cleeland" will produce SQL along the lines of "WHERE firstname = 'Jason' AND lastname=='Cleeland'"
     *
     * @param array $condition an array containing the search string exploded using || so that "firstname||equal||jason" is $condition(1=>'firstname', 2=>'equal', 3=>'jason')
     * @param int $page Which page number to display
     * @param in $limit The limit/number of reords to return
     *
     * @returns CDbCriteria $output
     */
    function getParticipantsSearchMultipleCondition($condition)
    {
    	//http://localhost/limesurvey_yii/admin/participants/getParticipantsResults_json/search/email||contains||gov||and||firstname||contains||AL
    	//First contains fieldname, second contains method, third contains value, fourth contains BOOLEAN SQL and, or

        //As we iterate through the conditions we build up the $command query by adding conditions to it
        //
        $i = 0;
        $command = new CDbCriteria;
        $command->condition = '';
        $aParams = array();

        $iNumberOfConditions = (count($condition)+1)/4;
        while ($i < $iNumberOfConditions)
        {
            $sFieldname=$condition[$i*4];
            $sOperator=$condition[($i*4)+1];
            $sValue=$condition[($i*4)+2];
            if(is_numeric($sValue)) $sValue=intval($sValue);
            $param = ':condition_'.$i;
            switch ($sOperator)
            {
                case 'equal': 
                    $operator = '=';
                    $aParams[$param] = $sValue;
                    break;
                case 'contains': 
                    $operator = 'LIKE';
                    $aParams[$param] = '%'.$sValue.'%';
                    break;
                case 'beginswith':
                    $operator = 'LIKE';
                    $aParams[$param] = $sValue.'%';
                    break;
                case 'notequal': 
                    $operator = '!=';
                    $aParams[$param] = $sValue;
                    break;
                case 'notcontains': 
                    $operator = 'NOT LIKE';
                    $aParams[$param] = '%'.$sValue.'%';
                    break;
                case 'greaterthan': 
                    $operator = '>';
                    $aParams[$param] = $sValue;
                    break;
                case 'lessthan': 
                    $operator = '<';
                    $aParams[$param] = $sValue;
                    break;
            }
            if (isset($condition[(($i-1)*4)+3]))
            {
                $booloperator=  strtoupper($condition[(($i-1)*4)+3]);
            }
            else
            {
                $booloperator='AND';
            }
            
            if($sFieldname=="email")
            {
                $command->addCondition('p.email ' . $operator . ' '.$param, $booloperator);
            } 
            elseif($sFieldname=="survey")
            {
                $subQuery = Yii::app()->db->createCommand()
                ->select('sl.participant_id')
                ->from('{{survey_links}} sl')
                ->join('{{surveys_languagesettings}} sls', 'sl.survey_id = sls.surveyls_survey_id')
                ->where('sls.surveyls_title '. $operator.' '.$param)
                ->group('sl.participant_id');
                $command->addCondition('p.participant_id IN ('.$subQuery->getText().')', $booloperator);             
            }
            elseif($sFieldname=="surveyid")
            {
                $subQuery = Yii::app()->db->createCommand()
                ->select('sl.participant_id')
                ->from('{{survey_links}} sl')
                ->where('sl.survey_id '. $operator.' '.$param)
                ->group('sl.participant_id');
                $command->addCondition('p.participant_id IN ('.$subQuery->getText().')', $booloperator);             
            }
            elseif($sFieldname=="surveys") //Search by quantity of linked surveys
            {
                $subQuery = Yii::app()->db->createCommand()
                ->select('sl.participant_id')
                ->from('{{survey_links}} sl')
                ->having('count(*) '. $operator.' '.$param)
                ->group('sl.participant_id');
                $command->addCondition('p.participant_id IN ('.$subQuery->getText().')', $booloperator);
            }
            elseif($sFieldname=="owner_name")
            {
                $command->addCondition('full_name ' . $operator . ' '.$param, $booloperator);
            }
            elseif($sFieldname=="participant_id")
            {
                $command->addCondition('p.participant_id ' . $operator . ' '.$param, $booloperator);
            }
            elseif (is_numeric($sFieldname)) //Searching for an attribute
            {
                $command->addCondition('attribute'. $sFieldname . '.value ' . $operator . ' '.$param, $booloperator);
            }
            else
            {
                $command->addCondition($sFieldname . ' '.$operator.' '.$param, $booloperator);
            }
                       
            $i++;
        }
        
        if (count($aParams)>0)
        {
            $command->params = $aParams;
        }
                
        return $command;
    }

    /**
    * Returns true if participant_id has ownership or shared rights over this participant false if not
    *
    * @param mixed $participant_id
    * @returns bool true/false
    */
    function is_owner($participant_id)
    {
        $userid = Yii::app()->session['loginID'];
        $is_owner = Yii::app()->db->createCommand()->select('count(*)')->where('participant_id = :participant_id AND owner_uid = :userid')->from('{{participants}}')->bindParam(":participant_id", $participant_id, PDO::PARAM_STR)->bindParam(":userid", $userid, PDO::PARAM_INT)->queryScalar();
        $is_shared = Yii::app()->db->createCommand()->select('count(*)')->where('participant_id = :participant_id AND share_uid = :userid')->from('{{participant_shares}}')->bindParam(":participant_id", $participant_id, PDO::PARAM_STR)->bindParam(":userid", $userid, PDO::PARAM_INT)->queryScalar();
        if ($is_shared > 0 || $is_owner > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /*
     * This funciton is responsible for showing all the participant's shared by a particular user based on the user id
     */

    function getParticipantShared($userid)
    {
        return Yii::app()->db->createCommand()->select('{{participants}}.*, {{participant_shares}}.*')->from('{{participants}}')->join('{{participant_shares}}', '{{participant_shares}}.participant_id = {{participants}}.participant_id')->where('owner_uid = :userid')->bindParam(":userid", $userid, PDO::PARAM_INT)->queryAll();
    }

    /*
     * This funciton is responsible for showing all the participant's shared to the superadmin
     */

    function getParticipantSharedAll()
    {
        return Yii::app()->db->createCommand()->select('{{participants}}.*,{{participant_shares}}.*')->from('{{participants}}')->join('{{participant_shares}}', '{{participant_shares}}.participant_id = {{participants}}.participant_id')->queryAll();
    }

    /*
     * Copies central attributes/participants to an individual survey token table
     *
     * @param int $surveyid The survey id
     * @param array $mapped An array containing a list of already existing/mapped attributes in the form of "token_field_name"=>"participant_attribute_id"
     * @param array $newcreate An array containing new attributes to create in the tokens table
     * @param string $participantid A comma seperated string containing the participant ids of the participants we are adding
     * @param bool $overwriteauto If true, overwrite automatically mapped data
     * @param bool $overwriteman If true, overwrite manually mapped data
     * @param bool $overwritest If true, overwrite standard fields (ie: names, email, participant_id, token)
     * @param bool $createautomap If true, rename the fieldnames of automapped attributes so that in future they are automatically mapped
     * */
    function copytosurveyatt($surveyid, $mapped, $newcreate, $participantid, $overwriteauto=false, $overwriteman=false, $overwritest=false, $createautomap=true)
    {
        Yii::app()->loadHelper('common');
        $duplicate = 0;
        $sucessfull = 0;
        $participantid = explode(",", $participantid); //List of participant ids to add to tokens table
        if ($participantid[0] == "") { $participantid = array_slice($participantid, 1); }
        $number2add = sanitize_int(count($newcreate)); //Number of tokens being created
        $tokenattributefieldnames=array(); //Will contain descriptions of existing token attribute fields
        $tokenfieldnames=array(); //Will contain the actual field names of existing token attribute fields
        $attributesadded = array(); //Will contain the actual field name of any new token attribute fields
        $attributeidadded = array(); //Will contain the description of any new token attribute fields
        $fieldcontents = array(); //Will contain serialised info for the surveys.attributedescriptions field
        $surveyinfo=getSurveyInfo($surveyid);
        $defaultsurveylang=$surveyinfo['surveyls_language'];

        $arr = Yii::app()->db
                         ->createCommand()
                         ->select('*')
                         ->from("{{tokens_$surveyid}}")
                         ->queryRow();
        if (is_array($arr))
        {
            $tokenfieldnames = array_keys($arr);
            $tokenattributefieldnames = array_filter($tokenfieldnames, 'filterForAttributes');
        }

        /* Rename an existing attribute in the token table to this attribute so
         * it maps automatically in future.
         *
         * Fieldname renamed from the $key of $mappeed to the $val of $mapped
         * */
        if($createautomap=="true") {
            foreach($mapped as $key=>$val) {
                if(is_numeric($val)) {
                    $newname = 'attribute_cpdb_' . intval($val);
                    //Rename the field in the tokens_[sid] table
                    Yii::app()->db
                              ->createCommand()
                              ->renameColumn('{{tokens_' . intval($surveyid) . '}}', $key, $newname);
                    //Make the field a TEXT field
                    Yii::app()->db
                              ->createCommand()
                              ->alterColumn('{{tokens_' . intval($surveyid) . '}}', $newname, 'TEXT');

                    /* Update the attributedescriptions info */
                    $previousatt = Yii::app()->db
                                             ->createCommand()
                                             ->select('attributedescriptions')
                                             ->from('{{surveys}}')
                                             ->where("sid = ".$surveyid);
                    $patt=$previousatt->queryRow();
                    $previousattribute = unserialize($patt['attributedescriptions']);
                    $previousattribute[$newname]=$previousattribute[$key];
                    unset($previousattribute[$key]);
                    //Rename the token field the name of the participant_attribute
                    $attributedetails=ParticipantAttributeNames::model()->getAttributeNames($val);
                    $previousattribute[$newname]['description']=$attributedetails[0]['attribute_name'];
                    $previousattribute=serialize($previousattribute);

                    Yii::app()->db
                              ->createCommand()
                              ->update('{{surveys}}',
                                        array("attributedescriptions" => $previousattribute),
                                        'sid = '.$surveyid); //load description in the surveys table
                    //Finally, update the $mapped key/val pair to reflect the new keyname of $newname
                    $mapped[$newname]=$mapped[$key];
                    unset($mapped[$key]);
                }

            }

        }
        foreach ($tokenattributefieldnames as $key => $value)
        {
            if ($value[10] == 'c') /* Existence of 'c' as 11th letter assume it is a CPDB link */
            {
                $attid = substr($value, 15);
                $mapped[$value] = $attid;
            } elseif (is_numeric($value[10]))
            {
                $mapped[$key]=$value;
            }
        }

        if (!empty($newcreate)) //Create new fields in the tokens table
        {
            foreach ($newcreate as $key => $value)
            {
                $newfieldname='attribute_cpdb_'.$value;
                $fields[$newfieldname] = array('type' => 'VARCHAR', 'constraint' => '255');
                $attname = Yii::app()->db
                                     ->createCommand()
                                     ->select('{{participant_attribute_names_lang}}.attribute_name, {{participant_attribute_names_lang}}.lang')
                                     ->from('{{participant_attribute_names}}')
                                     ->join('{{participant_attribute_names_lang}}', '{{participant_attribute_names}}.attribute_id = {{participant_attribute_names_lang}}.attribute_id')
                                     ->where('{{participant_attribute_names}}.attribute_id = :attrid ')
                                     ->bindParam(":attrid", $value, PDO::PARAM_INT);
                $attributename = $attname->queryAll();
                foreach($attributename as $att) {
                    $languages[$att['lang']]=$att['attribute_name'];
                }
                //Check first for the default survey language
                if(isset($languages[$defaultsurveylang])) {
                    $newname=$languages[$defaultsurveylang];
                } elseif (isset($language[Yii::app()->session['adminlang']])) {
                    $newname=$languages[Yii::app()->session['adminlang']];
                } else {
                    $newname=$attributename[0]['attribute_name']; //Choose the first item in the list
                }
                $tokenattributefieldnames[] = $newfieldname;
                $fieldcontents[$newfieldname]=array("description"=>$newname,
                                                    "mandatory"=>"N",
                                                    "show_register"=>"N");
                array_push($attributeidadded, 'attribute_cpdb_' . $value);
                array_push($attributesadded, $value);
            }
            //Update the attributedescriptions in the survey table to include the newly created attributes
            $previousatt = Yii::app()->db
                                     ->createCommand()
                                     ->select('attributedescriptions')
                                     ->where("sid = :sid")
                                     ->from('{{surveys}}')
                                     ->bindParam(":sid", $surveyid, PDO::PARAM_INT);
            $previousattribute = $previousatt->queryRow();
            $previousattribute = unserialize($previousattribute['attributedescriptions']);
            foreach($fieldcontents as $key=>$val) {
                $previousattribute[$key]=$val;
            }
            $previousattribute = serialize($previousattribute);
            Yii::app()->db
                      ->createCommand()
                      ->update('{{surveys}}',
                                array("attributedescriptions" => $previousattribute), 'sid = '.intval($surveyid)); // load description in the surveys table

            //Actually create the fields in the tokens table
            foreach ($fields as $key => $value)
            {
                Yii::app()->db
                          ->createCommand("ALTER TABLE {{tokens_$surveyid}} ADD COLUMN ". Yii::app()->db->quoteColumnName($key) ." ". $value['type'] ." ( ".intval($value['constraint'])." )")
                          ->query(); // add columns in token's table
            }
        }

        //Write each participant to the survey token table
        foreach ($participantid as $key => $participant)
        {
            $writearray = array();
            $participantdata = Yii::app()->db->createCommand()->select('firstname,lastname,email,language,blacklisted')->where('participant_id = :pid')->from('{{participants}}')->bindParam(":pid", $participant, PDO::PARAM_INT);
            $tobeinserted = $participantdata->queryRow();
            /* Search for matching participant name/email in the survey token table */
            $query = Yii::app()->db->createCommand()->select('*')->from('{{tokens_' . $surveyid . '}}')
                    ->where('firstname = :firstname AND lastname = :lastname AND email = :email')
                    ->bindParam(":firstname", $tobeinserted['firstname'], PDO::PARAM_STR)->bindParam(":lastname", $tobeinserted['lastname'], PDO::PARAM_STR)->bindParam(":email", $tobeinserted['email'], PDO::PARAM_STR)->queryAll();
            if (count($query) > 0)
            {
                //Participant already exists in token table - don't copy
                $duplicate++;
                // Here is where we can put code for overwriting the attribute data if so required
                if($overwriteauto=="true") {
                    //If there are new attributes created, add those values to the token entry for this participant
                    if (!empty($newcreate))
                    {
                        $numberofattributes = count($attributesadded);
                        for ($a = 0; $a < $numberofattributes; $a++)
                        {
                            Participants::model()->updateTokenAttributeValue($surveyid, $participant,$attributesadded[$a],$attributeidadded[$a]);
                        }
                    }
                    //If there are automapped attributes, add those values to the token entry for this participant
                    if (!empty($mapped))
                    {
                        foreach ($mapped as $key => $value)
                        {
                            if ($key[10] == 'c') { //We know it's automapped because the 11th letter is 'c'
                                Participants::model()->updateTokenAttributeValue($surveyid, $participant, $value, $key);
                            }
                        }
                    }
                }
                if($overwriteman=="true") {
                    //If there are any manually mapped attributes, add those values to the token entry for this participant
                    if (!empty($mapped))
                    {
                        foreach ($mapped as $key => $value)
                        {
                            if ($key[10] != 'c' && $key[9]=='_') { //It's not an auto field because it's 11th character isn't 'c'
                                Participants::model()->updateTokenAttributeValue($surveyid, $participant, $value, $key);
                            }
                        }
                    }
                }
                if($overwritest=="true") {
                    if(!empty($mapped))
                    {
                        foreach($mapped as $key=>$value)
                        {
                            if((strlen($key) > 8 && $key[10] != 'c' && $key[9] !='_') || strlen($key) < 9) {
                                Participants::model()->updateTokenAttributeValue($surveyid, $participant, $value, $key);
                            }
                        }
                    }
                }
            }
            else
            {
                //Create a new token entry for this participant
                $writearray = array('participant_id' => $participant,
                                    'firstname' => $tobeinserted['firstname'],
                                    'lastname' => $tobeinserted['lastname'],
                                    'email' => $tobeinserted['email'],
                                    'emailstatus' => 'OK',
                                    'language' => $tobeinserted['language']);
                Yii::app()->db
                          ->createCommand()
                          ->insert('{{tokens_' . $surveyid . '}}', $writearray);
                $insertedtokenid = getLastInsertID('{{tokens_' . $surveyid . '}}');

                $time = time();

                //Create a survey link for the new token entry
                $data = array(
                    'participant_id' => $participant,
                    'token_id' => $insertedtokenid,
                    'survey_id' => $surveyid,
                    'date_created' => date('Y-m-d H:i:s', $time));
                Yii::app()->db->createCommand()->insert('{{survey_links}}', $data);

                //If there are new attributes created, add those values to the token entry for this participant
                if (!empty($newcreate))
                {
                    $numberofattributes = count($attributesadded);
                    for ($a = 0; $a < $numberofattributes; $a++)
                    {
                        Participants::model()->updateTokenAttributeValue($surveyid, $participant,$attributesadded[$a],$attributeidadded[$a]);
                    }
                }
                //If there are any automatically mapped attributes, add those values to the token entry for this participant
                if (!empty($mapped))
                {
                    foreach ($mapped as $key => $value)
                    {
                        Participants::model()->updateTokenAttributeValue($surveyid, $participant, $value, $key);
                    }
                }
                $sucessfull++;
            }
        }
        $returndata = array('success' => $sucessfull, 'duplicate' => $duplicate, 'overwriteauto'=>$overwriteauto, 'overwriteman'=>$overwriteman);
        return $returndata;
    }

    /**
     * Updates a field in the token table with a value from the participant attributes table
     *
     * @param int $surveyId Survey ID number
     * @param int $participantId unique key for the participant
     * @param int $participantAttributeId the unique key for the participant_attribute table
     * @param int $tokenFieldName fieldname in the token table
     *
     * @return bool true/false
     */
    function updateTokenAttributeValue($surveyId, $participantId, $participantAttributeId, $tokenFieldname) {
        //Get the value from the participant_attribute field
        $val = Yii::app()->db
                         ->createCommand()
                         ->select('value')
                         ->where('participant_id = :participant_id AND attribute_id = :attrid')
                         ->from('{{participant_attribute}}')
                         ->bindParam("participant_id", $participantId, PDO::PARAM_STR)
                         ->bindParam("attrid", $participantAttributeId, PDO::PARAM_INT);
        $value = $val->queryRow();
        //Update the token entry with those values
        if (isset($value['value']))
        {
            $data = array($tokenFieldname => $value['value']);
            Yii::app()->db
                      ->createCommand()
                      ->update("{{tokens_$surveyId}}", $data, "participant_id = '$participantId'");
        }
        return true;
    }

    /**
     * Updates or creates a field in the token table with a value from the participant attributes table
     *
     * @param int $surveyId Survey ID number
     * @param int $participantId unique key for the participant
     * @param int $participantAttributeId the unique key for the participant_attribute table
     * @param int $tokenFieldName fieldname in the token table
     *
     * @return bool true/false
     */
     function updateAttributeValueToken($surveyId, $participantId, $participantAttributeId, $tokenFieldname) {
        $val = Yii::app()->db
                         ->createCommand()
                         ->select($tokenFieldname)
                         ->where('participant_id = :participant_id')
                         ->from('{{tokens_' . intval($surveyId) . '}}')
                         ->bindParam("participant_id", $participantId, PDO::PARAM_STR);
        $value2 = $val->queryRow();

        if (!empty($value2[$tokenFieldname]))
        {
            $data = array('participant_id' => $participantId,
                          'value' => $value2[$tokenFieldname],
                          'attribute_id' => $participantAttributeId
                          );
            //Check if value already exists
            $test=Yii::app()->db
                            ->createCommand()
                            ->select('count(*) as count')
                            ->from('{{participant_attribute}}')
                            ->where('participant_id = :participant_id AND attribute_id= :attribute_id')
                            ->bindParam(":participant_id", $participantId, PDO::PARAM_STR)
                            ->bindParam(":attribute_id", $participantAttributeId, PDO::PARAM_INT)
                            ->queryRow();
            if($test['count'] > 0) {
                $sql=Yii::app()->db
                               ->createCommand()
                               ->update('{{participant_attribute}}', array("value"=>$value2[$tokenFieldname]), "participant_id='$participantId' AND attribute_id=$participantAttributeId");
            } else {
                $sql=Yii::app()->db
                               ->createCommand()
                               ->insert('{{participant_attribute}}', $data);
            }
        }
    }

    /*
     * Copies token participants to the central participants table, and also copies
     * token attribute values where applicable. It checks for matching entries using
     * firstname/lastname/email combination.
     *
     * TODO: Most of this belongs in the participantsaction.php controller file, not
     *       here in the model file. Portions of this should be moved out at some stage.
     *
     * @param int $surveyid The id of the survey, used to find the appropriate tokens table
     * @param array $newarr An array containing the names of token attributes that have to be created in the cpdb
     * @param array $mapped An array containing the names of token attributes that are to be mapped to an existing cpdb attribute
     * @param bool $overwriteauto If true, overwrites existing automatically mapped attribute values (where token fieldname=attribute_cpdb_n)
     * @param bool $overwriteman If true, overwrites manually mapped attribute values (where token fieldname=attribute_n)
     * @param bool $createautomap If true, updates manuall mapped token fields to fieldname=attribute_cpdb_n from fieldname=attribute_n, s in future mapping is automatic
     * @param array $tokenid is assumed, saved by an earlier script as a session string called "participantid". It holds a list of token_ids
     *                       for the token participants we are copying to the central db
     *
     * @return array An array contaning list of successful and list of failed ids
     */

    function copyToCentral($surveyid, $newarr, $mapped, $overwriteauto=false, $overwriteman=false, $createautomap=true)
    {
        $tokenid = Yii::app()->session['participantid']; //List of token_id's to add to participants table
        $duplicate = 0;
        $sucessfull = 0;
        $writearray = array();
        $attid = array(); //Will store the CPDB attribute_id of new or existing attributes keyed by CPDB at
        $pid = "";

        /* Grab all the existing attribute field names from the tokens table */
        $arr = Yii::app()->db->createCommand()->select('*')->from("{{tokens_$surveyid}}")->queryRow();
        if (is_array($arr))
        {
            $tokenfieldnames = array_keys($arr);
            $tokenattributefieldnames = array_filter($tokenfieldnames, 'filterForAttributes');
        }
        else
        {
            $tokenattributefieldnames = array();
        }
        /* Automatically mapped attribute names are named "attribute_cpdb_[some_number]" */
        foreach ($tokenattributefieldnames as $key => $value) //mapping the automatically mapped
        {
            if ($value[10] == 'c') /* This is going to cause a problem one day! It's deciding that an item is an automatically mapped because the 10th letter is "c"*/
            {
                $autoattid = substr($value, 15);
                $mapped[$autoattid] = $value;
            }
        }

        /* Create new CPDB attributes */
        if (!empty($newarr))
        {
            foreach ($newarr as $key => $value) //creating new central attribute
            {
                /* $key is the fieldname from the token table (ie "attribute_1")
                 * $value is the 'friendly name' for the attribute (ie "Gender")
                 */
                $insertnames = array('attribute_type' => 'TB', 'visible' => 'Y');
                Yii::app()->db
                          ->createCommand()
                          ->insert('{{participant_attribute_names}}', $insertnames);
                $attid[$key] = getLastInsertID('{{participant_attribute_names}}'); /* eg $attid['attribute_1']='8372' */
                $insertnameslang = array(
                                         'attribute_id' => $attid[$key],
                                         'attribute_name' => urldecode($value),
                                         'lang' => Yii::app()->session['adminlang']
                                         );
                Yii::app()->db
                          ->createCommand()
                          ->insert('{{participant_attribute_names_lang}}', $insertnameslang);
            }
        }

        /* Add the participants to the CPDB = Iterate through each $tokenid and create the new CPDB id*/
        foreach ($tokenid as $key => $tid)
        {
            if (is_numeric($tid) && $tid != "")
            {
                /* Get the data for this participant from the tokens table */
                $tobeinserted = Yii::app()->db
                                          ->createCommand()
                                          ->select('participant_id,firstname,lastname,email,language')
                                          ->where('tid = :tid')
                                          ->from('{{tokens_' . intval($surveyid) . '}}')
                                          ->bindParam(":tid", $tid, PDO::PARAM_INT)
                                          ->queryRow();
                /* See if there are any existing CPDB entries that match on firstname,lastname and email */
                $query = Yii::app()->db
                                   ->createCommand()
                                   ->select('*')
                                   ->from('{{participants}}')
                                   ->where('firstname = :firstname AND lastname = :lastname AND email = :email')
                                   ->bindParam(":firstname", $tobeinserted['firstname'], PDO::PARAM_STR)
                                   ->bindParam(":lastname", $tobeinserted['lastname'], PDO::PARAM_STR)
                                   ->bindParam(":email", $tobeinserted['email'], PDO::PARAM_STR)
                                   ->queryAll();
                /* If there is already an existing entry, add to the duplicate count */
                if (count($query) > 0)
                {
                    $duplicate++;
                    //HERE is where we can add "overwrite" feature to update attribute values for existing participants
                    if($overwriteauto == "true") {
                        if (!empty($newarr))
                        {
                            foreach ($newarr as $key => $value)
                            {
                                Participants::model()->updateAttributeValueToken($surveyid, $query[0]['participant_id'], $attid[$key], $key);
                            }
                        }
                    }
                    if($overwriteman == "true") {
                        /* Now add mapped attribute values */
                        if (!empty($mapped))
                        {
                            foreach ($mapped as $cpdbatt => $tatt)
                            {
                                Participants::model()->updateAttributeValueToken($surveyid, $query[0]['participant_id'], $cpdbatt, $tatt);
                            }
                        }
                    }
                }
                /* If there isn't an existing entry, create one! */
                else
                {
                    /* Create entry in participants table */
                    $black = !empty($tobeinserted['blacklisted']) ? $tobeinserted['blacklised'] : 'N';
                    $pid=!empty($tobeinserted['participant_id']) ? $tobeinserted['participant_id'] : $this->gen_uuid();
                    $writearray = array('participant_id' => $pid,
                                        'firstname' => $tobeinserted['firstname'],
                                        'lastname' => $tobeinserted['lastname'],
                                        'email' => $tobeinserted['email'],
                                        'language' => $tobeinserted['language'],
                                        'blacklisted' => $black,
                                        'owner_uid' => Yii::app()->session['loginID']);
                    Yii::app()->db
                              ->createCommand()
                              ->insert('{{participants}}', $writearray);
                    //Update token table and insert the new UUID
                    $data=array("participant_id"=>$pid);
                    Yii::app()->db
                              ->createCommand()
                              ->update('{{tokens_'.intval($surveyid).'}}', $data, "tid = $tid");

                    /* Now add any new attribute values */
                    if (!empty($newarr))
                    {
                        foreach ($newarr as $key => $value)
                        {
                            Participants::model()->updateAttributeValueToken($surveyid, $pid, $attid[$key], $key);
                        }
                    }
                    /* Now add mapped attribute values */
                    if (!empty($mapped))
                    {
                        foreach ($mapped as $cpdbatt => $tatt)
                        {
                            Participants::model()->updateAttributeValueToken($surveyid,$pid,$cpdbatt,$tatt);
                        }
                    }
                    $sucessfull++;

                    /* Create a survey_link */
                    $data = array (
                            'participant_id' => $pid,
                            'token_id' => $tid,
                            'survey_id' => $surveyid,
                            'date_created' => date('Y-m-d H:i:s', time())
                        );
                    Yii::app()->db
                              ->createCommand()
                              ->insert('{{survey_links}}', $data);
                }
            }
        }

        if (!empty($newarr))
        {
            /* Rename the token attribute fields to a cpdb field, so in future
             * we know that it belongs to a CPDB field */
            foreach ($newarr as $key => $value)
            {
                $newname = 'attribute_cpdb_' . intval($attid[$key]);

                $fields = array($value => array('name' => $newname, 'type' => 'TEXT'));
                //Rename the field in the tokens_[sid] table
                Yii::app()->db
                          ->createCommand()
                          ->renameColumn('{{tokens_' . intval($surveyid) . '}}', $key, $newname);
                //Make the field a TEXT field
                Yii::app()->db
                          ->createCommand()
                          ->alterColumn('{{tokens_' . intval($surveyid) . '}}', $newname, 'TEXT');

                $previousatt = Yii::app()->db
                                         ->createCommand()
                                         ->select('attributedescriptions')
                                         ->from('{{surveys}}')
                                         ->where("sid = ".$surveyid);
                $patt=$previousatt->queryRow();
                $previousattribute = unserialize($patt['attributedescriptions']);
                $previousattribute[$newname]=$previousattribute[$key];
                unset($previousattribute[$key]);
                $previousattribute=serialize($previousattribute);
                Yii::app()->db
                          ->createCommand()
                          ->update('{{surveys}}',
                                    array("attributedescriptions" => $previousattribute),
                                    'sid = '.$surveyid); //load description in the surveys table
            }
        }
        if (!empty($mapped))
        {
            foreach ($mapped as $cpdbatt => $tatt)
            {
                if ($tatt[10] != 'c' && $createautomap=="true") //This attribute is not already mapped
                {
                    // Change the fieldname in the token table to attribute_cpdb_[participant_attribute_id]
                    // so future mapping is done automatically
                    $newname = 'attribute_cpdb_' . $cpdbatt;
                    $fields = array($tatt => array('name' => $newname, 'type' => 'TEXT'));
                    Yii::app()->db
                              ->createCommand()
                              ->renameColumn('{{tokens_' . intval($surveyid) . '}}', $tatt, $newname);
                    Yii::app()->db
                              ->createCommand()
                              ->alterColumn('{{tokens_' . intval($surveyid) . '}}', $newname, 'TEXT');
                    $previousatt = Yii::app()->db
                                             ->createCommand()
                                             ->select('attributedescriptions')
                                             ->from('{{surveys}}')
                                             ->where("sid = :sid")
                                             ->bindParam(":sid", $surveyid, PDO::PARAM_INT);
                    $previousattribute = $previousatt->queryRow();
                    $previousattribute = unserialize($previousattribute['attributedescriptions']);
                    $previousattribute[$newname]=$previousattribute[$tatt];
                    unset($previousattribute[$tatt]);
                    //Rename the token field the name of the participant_attribute
                    $attributedetails=ParticipantAttributeNames::model()->getAttributeNames($cpdbatt);
                    $previousattribute[$newname]['description']=$attributedetails[0]['attribute_name'];
                    $previousattribute = serialize($previousattribute);
                    //$newstring = str_replace($tatt, $newname, $previousattribute['attributedescriptions']);
                    Yii::app()->db
                              ->createCommand()
                              ->update('{{surveys}}', array("attributedescriptions" => $previousattribute), 'sid = '.$surveyid);
                }
            }
        }
        $returndata = array('success' => $sucessfull, 'duplicate' => $duplicate, 'overwriteauto'=>$overwriteauto, 'overwriteman'=>$overwriteman);
        return $returndata;
    }

	/*
     * The purpose of this function is to check for duplicate in participants
     */

    function checkforDuplicate($fields, $output="bool")
    {
        $query = Yii::app()->db->createCommand()->select('participant_id')->where($fields)->from('{{participants}}')->queryAll();
        if (count($query) > 0)
        {
            if($output=="bool") {return true;}
            return $query[0][$output];
        }
        else
        {
            return false;
        }
    }

    function insertParticipantCSV($data)
    {
        $insertData = array(
            'participant_id' => $data['participant_id'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'language' => $data['language'],
            'blacklisted' => $data['blacklisted'],
            'owner_uid' => $data['owner_uid']);
        Yii::app()->db->createCommand()->insert('{{participants}}', $insertData);
    }
}
